<?php

namespace App\Services\Sync\Explorer;

/**
 * Builds read-only SELECT statements for the OpenDental ShortQuery API.
 *
 * The API accepts raw SQL, so nothing user-supplied is ever interpolated
 * unchecked: the table comes from OpenDentalTableCatalog, columns are validated
 * against the synced table's schema by the caller, operators come from an
 * allowlist, and values go through quote().
 */
class OpenDentalSqlBuilder
{
    public const OPERATORS = ['=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'IS NULL', 'IS NOT NULL', 'BETWEEN'];

    /**
     * @param  list<string>  $columns  validated column names, or ['*']
     * @param  list<array{column: string, operator: string, value: string, logical: string}>  $conditions  validated conditions
     */
    public function select(
        string $table,
        array $columns,
        array $conditions,
        ?string $orderBy,
        string $orderDirection,
        int $limit,
        ?string $tieBreaker = null,
    ): string {
        $columnSql = $columns === ['*'] ? '*' : implode(', ', array_map($this->identifier(...), $columns));
        $sql = "SELECT {$columnSql} FROM {$this->identifier($table)}";

        $where = '';

        foreach ($conditions as $condition) {
            $clause = $this->clause($condition);

            if ($clause === null) {
                continue;
            }

            $where .= $where === ''
                ? $clause
                : ' '.($condition['logical'] === 'or' ? 'OR' : 'AND').' '.$clause;
        }

        if ($where !== '') {
            $sql .= " WHERE {$where}";
        }

        if ($orderBy !== null) {
            $direction = $orderDirection === 'desc' ? 'DESC' : 'ASC';
            $sql .= " ORDER BY {$this->identifier($orderBy)} {$direction}";

            if ($tieBreaker !== null && $tieBreaker !== $orderBy) {
                $sql .= ", {$this->identifier($tieBreaker)} {$direction}";
            }
        }

        return $sql.' LIMIT '.max(1, $limit);
    }

    /**
     * @param  list<int>  $keys
     */
    public function selectByKeys(string $table, string $primaryKey, array $keys, string $columns = '*'): string
    {
        $ids = implode(',', array_map('intval', $keys));
        $columnSql = $columns === '*' ? '*' : $this->identifier($columns);

        return "SELECT {$columnSql} FROM {$this->identifier($table)} WHERE {$this->identifier($primaryKey)} IN ({$ids})";
    }

    /**
     * Quote a value as a MySQL string literal (OpenDental runs on MySQL).
     */
    public function quote(string $value): string
    {
        return "'".strtr($value, [
            '\\' => '\\\\',
            "'" => "\\'",
            "\0" => '\\0',
            "\n" => '\\n',
            "\r" => '\\r',
            "\x1a" => '\\Z',
        ])."'";
    }

    /**
     * @param  array{column: string, operator: string, value: string, logical: string}  $condition
     */
    private function clause(array $condition): ?string
    {
        $column = $this->identifier($condition['column']);
        $value = $condition['value'];

        return match ($condition['operator']) {
            '=', '!=', '>', '>=', '<', '<=' => "{$column} {$condition['operator']} {$this->quote($value)}",
            'LIKE', 'NOT LIKE' => "{$column} {$condition['operator']} ".$this->quote(str_contains($value, '%') ? $value : "%{$value}%"),
            'IN', 'NOT IN' => "{$column} {$condition['operator']} (".implode(', ', array_map(fn ($v) => $this->quote(trim($v)), explode(',', $value))).')',
            'IS NULL', 'IS NOT NULL' => "{$column} {$condition['operator']}",
            'BETWEEN' => $this->between($column, $value),
            default => null,
        };
    }

    private function between(string $column, string $value): ?string
    {
        $range = array_map('trim', explode(',', $value, 2));

        return count($range) === 2
            ? "{$column} BETWEEN {$this->quote($range[0])} AND {$this->quote($range[1])}"
            : null;
    }

    private function identifier(string $name): string
    {
        // Callers pass schema-validated names; this is defence in depth.
        return '`'.str_replace('`', '', $name).'`';
    }
}
