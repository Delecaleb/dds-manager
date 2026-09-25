<?php

namespace App\Services\Sync\Explorer;

use App\Models\Office;
use App\Services\OpenDental\QueryService;
use App\Services\Sync\OpenDentalTable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Read-only querying of one catalog table, either live from OpenDental or from
 * the local synced copy scoped to one office.
 *
 * There is deliberately no silent fallback between the two sources: a live
 * query that fails throws, so a user is never shown local data labelled live.
 */
class ExplorerQueryService
{
    public const MAX_LIMIT = 5000;

    public function __construct(
        private readonly QueryService $queryService,
        private readonly OpenDentalSqlBuilder $sql,
    ) {}

    /**
     * @return list<string>
     */
    public function columns(OpenDentalTable $table): array
    {
        return Schema::getColumnListing($table->localTable);
    }

    /**
     * Keep only conditions on real columns with allowlisted operators, and add
     * the table's business-date range when both dates are given.
     *
     * @param  mixed  $conditions  raw request input
     * @return list<array{column: string, operator: string, value: string, logical: string}>
     */
    public function normalizeConditions(OpenDentalTable $table, mixed $conditions, ?string $startDate = null, ?string $endDate = null): array
    {
        $columns = $this->columns($table);
        $normalized = [];

        foreach (is_array($conditions) ? $conditions : [] as $condition) {
            if (! is_array($condition) || ! is_string($condition['column'] ?? null)) {
                continue;
            }

            $operator = strtoupper(trim((string) ($condition['operator'] ?? '=')));

            if (! in_array($condition['column'], $columns, true) || ! in_array($operator, OpenDentalSqlBuilder::OPERATORS, true)) {
                continue;
            }

            $normalized[] = [
                'column' => $condition['column'],
                'operator' => $operator,
                'value' => is_scalar($condition['value'] ?? null) ? (string) $condition['value'] : '',
                'logical' => strtolower((string) ($condition['logical'] ?? 'and')) === 'or' ? 'or' : 'and',
            ];
        }

        if ($startDate && $endDate && $table->dateColumn !== null && in_array($table->dateColumn, $columns, true)) {
            $normalized[] = [
                'column' => $table->dateColumn,
                'operator' => 'BETWEEN',
                'value' => "{$startDate} 00:00:00, {$endDate} 23:59:59",
                'logical' => 'and',
            ];
        }

        return $normalized;
    }

    /**
     * @param  mixed  $requested  raw request input
     * @return list<string> validated columns, or ['*']
     */
    public function selectColumns(OpenDentalTable $table, mixed $requested): array
    {
        if (! is_array($requested) || $requested === [] || in_array('*', $requested, true)) {
            return ['*'];
        }

        $valid = array_values(array_intersect($requested, $this->columns($table)));

        return $valid === [] ? ['*'] : $valid;
    }

    public function validOrderColumn(OpenDentalTable $table, mixed $orderBy): ?string
    {
        return is_string($orderBy) && in_array($orderBy, $this->columns($table), true) ? $orderBy : null;
    }

    /**
     * Query OpenDental live. Throws when the API fails.
     *
     * @param  list<string>  $columns
     * @param  list<array{column: string, operator: string, value: string, logical: string}>  $conditions
     * @return array{sql: string, rows: list<array<string, mixed>>}
     */
    public function live(OpenDentalTable $table, Office $office, array $columns, array $conditions, ?string $orderBy, string $direction, int $limit): array
    {
        if (! $table->existsInOpenDental) {
            throw new \InvalidArgumentException("'{$table->key}' is a local rollup and does not exist in OpenDental.");
        }

        $sql = $this->sql->select($table->key, $columns, $conditions, $orderBy, $direction, $this->clampLimit($limit), $orderBy !== null ? $table->primaryKey : null);
        $rows = $this->queryService->forOffice($office)->shortQuery($sql);

        return ['sql' => $sql, 'rows' => array_map(fn ($row) => (array) $row, is_array($rows) ? $rows : [])];
    }

    /**
     * Local synced rows for one office.
     *
     * @param  list<array{column: string, operator: string, value: string, logical: string}>  $conditions
     */
    public function localBuilder(OpenDentalTable $table, int $officeId, array $conditions): Builder
    {
        $builder = DB::table($table->localTable)->where("{$table->localTable}.office_id", $officeId);

        if ($conditions === []) {
            return $builder;
        }

        // Grouped so an OR condition can never escape the office scope.
        return $builder->where(function (Builder $query) use ($conditions) {
            foreach ($conditions as $condition) {
                $this->applyCondition($query, $condition);
            }
        });
    }

    public function clampLimit(int $limit): int
    {
        return min(max($limit, 1), self::MAX_LIMIT);
    }

    /**
     * @param  array{column: string, operator: string, value: string, logical: string}  $condition
     */
    private function applyCondition(Builder $query, array $condition): void
    {
        $column = $condition['column'];
        $value = $condition['value'];
        $boolean = $condition['logical'];

        match ($condition['operator']) {
            '=', '!=', '>', '>=', '<', '<=' => $query->where($column, $condition['operator'], $value, $boolean),
            'LIKE', 'NOT LIKE' => $query->where($column, $condition['operator'], str_contains($value, '%') ? $value : "%{$value}%", $boolean),
            'IN' => $query->whereIn($column, array_map('trim', explode(',', $value)), $boolean),
            'NOT IN' => $query->whereNotIn($column, array_map('trim', explode(',', $value)), $boolean),
            'IS NULL' => $query->whereNull($column, $boolean),
            'IS NOT NULL' => $query->whereNotNull($column, $boolean),
            'BETWEEN' => count($range = array_map('trim', explode(',', $value, 2))) === 2
                ? $query->whereBetween($column, $range, $boolean)
                : null,
        };
    }
}
