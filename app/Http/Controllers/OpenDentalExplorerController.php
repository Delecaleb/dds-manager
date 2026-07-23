<?php

namespace App\Http\Controllers;

use App\Services\OpenDental\QueryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OpenDentalExplorerController extends Controller
{
    /**
     * Map of native OpenDental table names to local fallback tables.
     */
    protected array $openDentalNativeTables = [
        'patient' => 'od_patients',
        'procedurelog' => 'od_procedure_logs',
        'procedurecode' => 'od_procedures',
        'appointment' => 'od_appointments',
        'provider' => 'od_providers',
        'paysplit' => 'od_pay_splits',
        'treatmentplan' => 'treatment_plans',
        'claim' => 'od_claims',
        'claimproc' => 'od_claim_procs',
        'adjustment' => 'od_adjustments',
        'payplan' => 'od_pay_plans',
        'payment' => 'od_payments',
        'recall' => 'od_recalls',
        'insplan' => 'od_ins_plans',
        'clinic' => 'od_clinics',
        'operatory' => 'od_operatories',
        'userod' => 'od_user_ods',
    ];

    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(): View
    {
        return view('od-explorer.index', [
            'openDentalTables' => array_keys($this->openDentalNativeTables),
            'localTables' => $this->getLocalTables(),
        ]);
    }

    public function tables(): JsonResponse
    {
        return response()->json([
            'opendental_tables' => array_keys($this->openDentalNativeTables),
            'local_tables' => $this->getLocalTables(),
        ]);
    }

    public function columns(Request $request): JsonResponse
    {
        $table = (string) $request->input('table');
        $resolvedTable = $this->resolveTableName($table);

        if (! $resolvedTable) {
            return response()->json(['error' => 'Invalid or unauthorized table selected.'], 400);
        }

        $columns = DB::getSchemaBuilder()->getColumnListing($resolvedTable);

        return response()->json([
            'table' => $table,
            'resolved_table' => $resolvedTable,
            'columns' => $columns,
        ]);
    }

    public function query(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        $table = (string) $request->input('table');
        $source = (string) $request->input('source', 'opendental_live');
        $resolvedTable = $this->resolveTableName($table);

        if (! $resolvedTable) {
            return response()->json(['error' => 'Invalid or unauthorized table selected.'], 400);
        }

        $tableColumns = DB::getSchemaBuilder()->getColumnListing($resolvedTable);

        // Column Projection
        $selectedColumns = $request->input('columns', []);
        if (! is_array($selectedColumns) || empty($selectedColumns) || in_array('*', $selectedColumns, true)) {
            $colsToSelect = ['*'];
        } else {
            $colsToSelect = array_values(array_intersect($selectedColumns, $tableColumns));
            if (empty($colsToSelect)) {
                $colsToSelect = ['*'];
            }
        }

        $orderBy = (string) $request->input('order_by');
        $orderDir = strtolower((string) $request->input('order_direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $limit = min(max((int) $request->input('limit', 50), 1), 2000);
        $conditions = $request->input('conditions', []);

        // 1. Try OpenDental Realtime Query via API if source == opendental_live
        if ($source === 'opendental_live') {
            try {
                $foundKey = array_search($table, $this->openDentalNativeTables, true);
                $odTableName = isset($this->openDentalNativeTables[$table])
                    ? $table
                    : ($foundKey !== false ? $foundKey : $table);

                $sql = $this->buildRawSqlString($odTableName, $colsToSelect, $conditions, $orderBy, $orderDir, $limit, $tableColumns);
                $rows = $this->queryService->shortQuery($sql);

                $executionTimeMs = round((microtime(true) - $startTime) * 1000, 2);
                $actualColumns = ($colsToSelect === ['*'])
                    ? (! empty($rows) ? array_keys((array) $rows[0]) : $tableColumns)
                    : $colsToSelect;

                return response()->json([
                    'source_type' => 'OpenDental Realtime API',
                    'table' => $odTableName,
                    'count' => count($rows),
                    'execution_time_ms' => $executionTimeMs,
                    'columns' => $actualColumns,
                    'sql' => $sql,
                    'rows' => $rows,
                ]);
            } catch (Exception $e) {
                // If live API is not configured or fails, fallback gracefully to Local DB with notice
                $fallbackError = $e->getMessage();
            }
        }

        // 2. Query via Local DB QueryBuilder
        $builder = DB::table($resolvedTable)->select($colsToSelect);

        if (is_array($conditions)) {
            foreach ($conditions as $cond) {
                if (! is_array($cond) || empty($cond['column']) || ! in_array($cond['column'], $tableColumns, true)) {
                    continue;
                }

                $col = $cond['column'];
                $op = strtoupper((string) ($cond['operator'] ?? '='));
                $val = $cond['value'] ?? '';
                $logical = strtolower((string) ($cond['logical'] ?? 'and'));

                $whereMethod = ($logical === 'or') ? 'orWhere' : 'where';

                switch ($op) {
                    case '=':
                    case '!=':
                    case '>':
                    case '>=':
                    case '<':
                    case '<=':
                        $builder->$whereMethod($col, $op, $val);
                        break;
                    case 'LIKE':
                    case 'NOT LIKE':
                        $valPattern = str_contains((string) $val, '%') ? $val : '%'.$val.'%';
                        $builder->$whereMethod($col, $op, $valPattern);
                        break;
                    case 'IN':
                        $vals = array_map('trim', explode(',', (string) $val));
                        $inMethod = ($logical === 'or') ? 'orWhereIn' : 'whereIn';
                        $builder->$inMethod($col, $vals);
                        break;
                    case 'NOT IN':
                        $vals = array_map('trim', explode(',', (string) $val));
                        $notInMethod = ($logical === 'or') ? 'orWhereNotIn' : 'whereNotIn';
                        $builder->$notInMethod($col, $vals);
                        break;
                    case 'IS NULL':
                        $nullMethod = ($logical === 'or') ? 'orWhereNull' : 'whereNull';
                        $builder->$nullMethod($col);
                        break;
                    case 'IS NOT NULL':
                        $notNullMethod = ($logical === 'or') ? 'orWhereNotNull' : 'whereNotNull';
                        $builder->$notNullMethod($col);
                        break;
                    case 'BETWEEN':
                        $range = array_map('trim', explode(',', (string) $val, 2));
                        if (count($range) === 2) {
                            $betweenMethod = ($logical === 'or') ? 'orWhereBetween' : 'whereBetween';
                            $builder->$betweenMethod($col, $range);
                        }
                        break;
                }
            }
        }

        if ($orderBy && in_array($orderBy, $tableColumns, true)) {
            $builder->orderBy($orderBy, $orderDir);
        }

        $builder->limit($limit);

        $sql = $builder->toSql();
        $bindings = $builder->getBindings();
        $rows = $builder->get();
        $executionTimeMs = round((microtime(true) - $startTime) * 1000, 2);

        $actualColumns = ($colsToSelect === ['*']) ? $tableColumns : $colsToSelect;

        return response()->json([
            'source_type' => 'Local Synced Database',
            'table' => $resolvedTable,
            'count' => count($rows),
            'execution_time_ms' => $executionTimeMs,
            'columns' => $actualColumns,
            'sql' => $sql,
            'bindings' => $bindings,
            'rows' => $rows,
            'notice' => isset($fallbackError) ? 'OpenDental Live API unavailable: '.$fallbackError.' (Used Local DB Fallback)' : null,
        ]);
    }

    private function resolveTableName(string $table): ?string
    {
        if (isset($this->openDentalNativeTables[$table])) {
            $mapped = $this->openDentalNativeTables[$table];
            if (DB::getSchemaBuilder()->hasTable($mapped)) {
                return $mapped;
            }
        }

        $localTables = $this->getLocalTables();
        if (in_array($table, $localTables, true)) {
            return $table;
        }

        return null;
    }

    private function getLocalTables(): array
    {
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' AND name NOT LIKE 'migrations'");

            return array_values(array_filter(array_map(fn ($t) => $t->name, $tables)));
        }

        $dbName = DB::getDatabaseName();
        $tables = DB::select('SHOW TABLES');
        $key = 'Tables_in_'.$dbName;

        $list = array_map(function ($t) use ($key) {
            if (isset($t->$key)) {
                return $t->$key;
            }
            $arr = (array) $t;

            return reset($arr);
        }, $tables);

        return array_values(array_filter($list, fn ($t) => $t !== 'migrations'));
    }

    private function buildRawSqlString(string $table, array $columns, array $conditions, ?string $orderBy, string $orderDir, int $limit, array $validCols): string
    {
        $colStr = ($columns === ['*']) ? '*' : implode(', ', array_map(fn ($c) => "`{$c}`", $columns));
        $sql = "SELECT {$colStr} FROM {$table}";

        $whereClauses = [];
        if (is_array($conditions)) {
            foreach ($conditions as $idx => $cond) {
                if (! is_array($cond) || empty($cond['column']) || ! in_array($cond['column'], $validCols, true)) {
                    continue;
                }

                $col = "`{$cond['column']}`";
                $op = strtoupper((string) ($cond['operator'] ?? '='));
                $val = (string) ($cond['value'] ?? '');
                $logical = ($idx > 0 && strtolower((string) ($cond['logical'] ?? 'and')) === 'or') ? 'OR' : 'AND';

                $escapedVal = "'".addslashes($val)."'";

                switch ($op) {
                    case '=':
                    case '!=':
                    case '>':
                    case '>=':
                    case '<':
                    case '<=':
                        $clause = "{$col} {$op} {$escapedVal}";
                        break;
                    case 'LIKE':
                    case 'NOT LIKE':
                        $valPattern = str_contains($val, '%') ? $val : '%'.$val.'%';
                        $clause = "{$col} {$op} '".addslashes($valPattern)."'";
                        break;
                    case 'IN':
                    case 'NOT IN':
                        $vals = array_map(fn ($v) => "'".addslashes(trim($v))."'", explode(',', $val));
                        $clause = "{$col} {$op} (".implode(', ', $vals).')';
                        break;
                    case 'IS NULL':
                    case 'IS NOT NULL':
                        $clause = "{$col} {$op}";
                        break;
                    case 'BETWEEN':
                        $range = array_map(fn ($r) => "'".addslashes(trim($r))."'", explode(',', $val, 2));
                        if (count($range) === 2) {
                            $clause = "{$col} BETWEEN {$range[0]} AND {$range[1]}";
                        } else {
                            $clause = '1=1';
                        }
                        break;
                    default:
                        $clause = "{$col} = {$escapedVal}";
                }

                $whereClauses[] = count($whereClauses) === 0 ? "WHERE {$clause}" : "{$logical} {$clause}";
            }
        }

        if (! empty($whereClauses)) {
            $sql .= ' '.implode(' ', $whereClauses);
        }

        if ($orderBy && in_array($orderBy, $validCols, true)) {
            $upperDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
            $sql .= " ORDER BY `{$orderBy}` {$upperDir}";
        }

        $sql .= " LIMIT {$limit}";

        return $sql;
    }
}
