<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\SyncLog;
use App\Models\SyncRequest;
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
        'claimpayment' => 'od_claim_payments',
        'adjustment' => 'od_adjustments',
        'payplan' => 'od_pay_plans',
        'payplancharge' => 'od_pay_plan_charges',
        'payment' => 'od_payments',
        'deposit' => 'od_deposits',
        'recall' => 'od_recalls',
        'recalltype' => 'od_recall_types',
        'schedule' => 'od_schedules',
        'insplan' => 'od_ins_plans',
        'carrier' => 'od_carriers',
        'definition' => 'od_definitions',
        'clinic' => 'od_clinics',
        'operatory' => 'od_operatories',
        'userod' => 'od_user_ods',
    ];

    /**
     * Map of common table aliases to native OpenDental table names.
     */
    protected array $tableAliases = [
        'od_claim_proc' => 'claimproc',
        'od_claim_procs' => 'claimproc',
        'claim_proc' => 'claimproc',
        'claim_procs' => 'claimproc',
        'claimprocs' => 'claimproc',
        'od_claimproc' => 'claimproc',

        'od_claim_payment' => 'claimpayment',
        'od_claim_payments' => 'claimpayment',
        'claim_payment' => 'claimpayment',
        'claim_payments' => 'claimpayment',
        'claimpayments' => 'claimpayment',

        'od_patient' => 'patient',
        'od_patients' => 'patient',
        'patients' => 'patient',

        'od_procedure_log' => 'procedurelog',
        'od_procedure_logs' => 'procedurelog',
        'procedurelogs' => 'procedurelog',
        'procedure_log' => 'procedurelog',

        'od_procedure' => 'procedurecode',
        'od_procedures' => 'procedurecode',
        'procedurecodes' => 'procedurecode',

        'od_appointment' => 'appointment',
        'od_appointments' => 'appointment',
        'appointments' => 'appointment',

        'od_provider' => 'provider',
        'od_providers' => 'provider',
        'providers' => 'provider',

        'od_pay_split' => 'paysplit',
        'od_pay_splits' => 'paysplit',
        'pay_splits' => 'paysplit',

        'od_treatment_plan' => 'treatmentplan',
        'od_treatment_plans' => 'treatmentplan',
        'treatment_plans' => 'treatmentplan',
        'treatmentplans' => 'treatmentplan',

        'od_claim' => 'claim',
        'od_claims' => 'claim',
        'claims' => 'claim',

        'od_adjustment' => 'adjustment',
        'od_adjustments' => 'adjustment',
        'adjustments' => 'adjustment',

        'od_pay_plan' => 'payplan',
        'od_pay_plans' => 'payplan',
        'pay_plans' => 'payplan',

        'od_pay_plan_charge' => 'payplancharge',
        'od_pay_plan_charges' => 'payplancharge',
        'payplancharges' => 'payplancharge',

        'od_payment' => 'payment',
        'od_payments' => 'payment',
        'payments' => 'payment',

        'od_deposit' => 'deposit',
        'od_deposits' => 'deposit',
        'deposits' => 'deposit',

        'od_recall' => 'recall',
        'od_recalls' => 'recall',
        'recalls' => 'recall',

        'od_recall_type' => 'recalltype',
        'od_recall_types' => 'recalltype',
        'recalltypes' => 'recalltype',

        'od_schedule' => 'schedule',
        'od_schedules' => 'schedule',
        'schedules' => 'schedule',

        'od_ins_plan' => 'insplan',
        'od_ins_plans' => 'insplan',
        'ins_plans' => 'insplan',

        'od_carrier' => 'carrier',
        'od_carriers' => 'carrier',
        'carriers' => 'carrier',

        'od_definition' => 'definition',
        'od_definitions' => 'definition',
        'definitions' => 'definition',

        'od_clinic' => 'clinic',
        'od_clinics' => 'clinic',
        'clinics' => 'clinic',

        'od_operatory' => 'operatory',
        'od_operatories' => 'operatory',
        'operatories' => 'operatory',

        'od_user_od' => 'userod',
        'od_user_ods' => 'userod',
        'user_ods' => 'userod',
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
                $odTableName = $this->tableAliases[$table]
                    ?? (isset($this->openDentalNativeTables[$table])
                        ? $table
                        : ($foundKey !== false ? $foundKey : $table));

                $sql = $this->buildRawSqlString($odTableName, $colsToSelect, $conditions, $orderBy, $orderDir, $limit, $tableColumns);
                $rows = $this->queryService->forOffice(Office::getActiveOffice())->shortQuery($sql);

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

        $nativeKey = $this->tableAliases[$table] ?? null;
        if ($nativeKey && isset($this->openDentalNativeTables[$nativeKey])) {
            $mapped = $this->openDentalNativeTables[$nativeKey];
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

    public function syncToLocal(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        $table = (string) $request->input('table');
        $rows = $request->input('rows', []);

        if (empty($rows) || ! is_array($rows)) {
            return response()->json(['error' => 'No records provided to sync.'], 400);
        }

        $resolvedTable = $this->resolveTableName($table);
        if (! $resolvedTable) {
            return response()->json(['error' => 'Invalid or unauthorized target table.'], 400);
        }

        $tableColumns = DB::getSchemaBuilder()->getColumnListing($resolvedTable);
        if (empty($tableColumns)) {
            return response()->json(['error' => "Target local table '{$resolvedTable}' does not exist or has no columns."], 400);
        }

        $primaryKey = $this->getPrimaryKeyForTable($resolvedTable, $tableColumns);
        $syncedCount = 0;
        $activeOfficeId = Office::getActiveOfficeId() ?? 1;
        $hasOfficeCol = in_array('office_id', $tableColumns, true);

        $validRecords = [];
        foreach ($rows as $row) {
            $rowArr = (array) $row;
            $cleanRow = [];
            foreach ($tableColumns as $col) {
                if (array_key_exists($col, $rowArr)) {
                    $val = $rowArr[$col];
                    if (is_string($val) && (str_contains(strtolower($col), 'date') || str_contains(strtolower($col), 'time'))) {
                        $val = str_replace('T', ' ', trim($val));
                        if ($val === '' || $val < '1000-01-01' || $val > '9999-12-31') {
                            $val = null;
                        }
                    }
                    $cleanRow[$col] = $val;
                }
            }

            if ($hasOfficeCol) {
                $cleanRow['office_id'] = $activeOfficeId;
            }

            if (! empty($cleanRow)) {
                $validRecords[] = $cleanRow;
            }
        }

        if (empty($validRecords)) {
            return response()->json(['error' => 'No matching valid columns to sync into local database.'], 400);
        }

        DB::transaction(function () use ($resolvedTable, $validRecords, $primaryKey, $hasOfficeCol, $activeOfficeId, &$syncedCount) {
            if ($primaryKey && isset($validRecords[0][$primaryKey])) {
                foreach ($validRecords as $rec) {
                    $pkVal = $rec[$primaryKey];
                    $matchCond = [$primaryKey => $pkVal];
                    if ($hasOfficeCol) {
                        $matchCond['office_id'] = $activeOfficeId;
                    }
                    $updateData = $rec;
                    unset($updateData[$primaryKey]);
                    DB::table($resolvedTable)->updateOrInsert($matchCond, $updateData);
                    $syncedCount++;
                }
            } else {
                foreach (array_chunk($validRecords, 500) as $chunk) {
                    DB::table($resolvedTable)->insertOrIgnore($chunk);
                    $syncedCount += count($chunk);
                }
            }
        });

        $executionTimeMs = round((microtime(true) - $startTime) * 1000, 2);

        return response()->json([
            'success' => true,
            'table' => $resolvedTable,
            'synced_count' => $syncedCount,
            'execution_time_ms' => $executionTimeMs,
            'message' => "Successfully synced {$syncedCount} record(s) from OpenDental into local table '{$resolvedTable}'.",
        ]);
    }

    private function getPrimaryKeyForTable(string $table, array $columns): string
    {
        $primaryKeyMap = [
            'od_patients' => 'PatNum',
            'od_procedure_logs' => 'ProcNum',
            'od_procedures' => 'CodeNum',
            'od_appointments' => 'AptNum',
            'od_providers' => 'ProvNum',
            'od_pay_splits' => 'SplitNum',
            'treatment_plans' => 'TreatPlanNum',
            'od_claims' => 'ClaimNum',
            'od_claim_procs' => 'ClaimProcNum',
            'od_adjustments' => 'AdjNum',
            'od_pay_plans' => 'PayPlanNum',
            'od_pay_plan_charges' => 'PayPlanChargeNum',
            'od_payments' => 'PayNum',
            'od_deposits' => 'DepositNum',
            'od_claim_payments' => 'ClaimPaymentNum',
            'od_recalls' => 'RecallNum',
            'od_recall_types' => 'RecallTypeNum',
            'od_schedules' => 'ScheduleNum',
            'od_ins_plans' => 'PlanNum',
            'od_carriers' => 'CarrierNum',
            'od_definitions' => 'DefNum',
            'od_clinics' => 'ClinicNum',
            'od_operatories' => 'OperatoryNum',
            'od_user_ods' => 'UserNum',
        ];

        if (isset($primaryKeyMap[$table])) {
            return $primaryKeyMap[$table];
        }

        if (in_array('id', $columns, true)) {
            return 'id';
        }

        foreach ($columns as $col) {
            if (str_ends_with($col, 'Num') || str_ends_with($col, '_id')) {
                return $col;
            }
        }

        return $columns[0] ?? 'id';
    }

    public function syncCheckpoints(): JsonResponse
    {
        $logs = SyncLog::orderBy('module')->get();

        return response()->json([
            'logs' => $logs,
        ]);
    }

    public function resetSyncCheckpoint(Request $request): JsonResponse
    {
        $module = (string) $request->input('module');
        $lastSyncedAt = $request->input('last_synced_at');
        $lastPrimaryKey = (int) $request->input('last_primary_key', 0);

        if (empty($module)) {
            return response()->json(['error' => 'Module is required.'], 400);
        }

        $formattedDate = null;
        if (! empty($lastSyncedAt)) {
            $ts = strtotime((string) $lastSyncedAt);
            if ($ts !== false) {
                $formattedDate = date('Y-m-d H:i:s', $ts);
            }
        }

        if ($module === 'all') {
            SyncLog::query()->update([
                'last_synced_at' => $formattedDate,
                'last_primary_key' => $lastPrimaryKey,
                'status' => 'idle',
                'last_error' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully reset sync checkpoints for ALL modules.',
            ]);
        }

        $log = SyncLog::firstOrCreate(
            ['module' => $module],
            ['status' => 'idle', 'total_processed' => 0]
        );

        $log->update([
            'last_synced_at' => $formattedDate,
            'last_primary_key' => $lastPrimaryKey,
            'status' => 'idle',
            'last_error' => null,
        ]);

        return response()->json([
            'success' => true,
            'module' => $module,
            'message' => "Successfully reset sync checkpoint for module '{$module}'.",
        ]);
    }

    public function getSyncRequests(): JsonResponse
    {
        $requests = SyncRequest::with(['user:id,name'])
            ->orderBy('id', 'desc')
            ->take(50)
            ->get();

        return response()->json([
            'requests' => $requests,
        ]);
    }

    public function triggerDateSync(Request $request): JsonResponse
    {
        $request->validate([
            'module' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'prune_deleted' => 'nullable|boolean',
        ]);

        $module = strtolower(trim((string) $request->input('module')));
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $pruneDeleted = (bool) $request->input('prune_deleted', false);

        if ($startDate && $endDate && $startDate > $endDate) {
            return response()->json(['error' => 'Start date cannot be after end date.'], 422);
        }

        $syncReq = SyncRequest::create([
            'office_id' => Office::getActiveOffice()->id ?? 1,
            'module' => $module,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'prune_deleted' => $pruneDeleted,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        // Attempt background execution immediately so server-to-server sync starts instantly
        try {
            if (str_contains(PHP_OS_FAMILY, 'Windows')) {
                pclose(popen('start /B php '.base_path('artisan')." sync:process-pending --id={$syncReq->id} > NUL 2>&1", 'r'));
            } else {
                exec('php '.base_path('artisan')." sync:process-pending --id={$syncReq->id} > /dev/null 2>&1 &");
            }
        } catch (Exception $e) {
            // Log warning, background schedule cron will pick it up
        }

        return response()->json([
            'success' => true,
            'message' => "Server-to-server sync request created for '{$module}' module.",
            'sync_request' => $syncReq,
        ]);
    }

    public function cancelSyncRequest(Request $request): JsonResponse
    {
        $id = (int) $request->input('id');
        $syncReq = SyncRequest::find($id);

        if (! $syncReq) {
            return response()->json(['error' => 'Sync request not found.'], 404);
        }

        if (in_array($syncReq->status, ['completed', 'failed', 'cancelled'])) {
            return response()->json(['error' => "Cannot cancel a sync request with status '{$syncReq->status}'."], 422);
        }

        $syncReq->update([
            'status' => 'cancelled',
            'completed_at' => now(),
            'error_message' => 'Cancelled by user.',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Sync request #{$id} has been cancelled.",
        ]);
    }

    public function reconcileDiff(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        $table = (string) $request->input('table', 'appointment');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $limit = min(max((int) $request->input('limit', 500), 10), 2000);
        $conditions = $request->input('conditions', []);

        $resolvedTable = $this->resolveTableName($table);
        if (! $resolvedTable) {
            return response()->json(['error' => 'Invalid or unauthorized table selected.'], 400);
        }

        $odTableName = $this->tableAliases[$table]
            ?? (isset($this->openDentalNativeTables[$table])
                ? $table
                : (array_search($table, $this->openDentalNativeTables, true) ?: $table));

        $tableColumns = DB::getSchemaBuilder()->getColumnListing($resolvedTable);
        $primaryKey = $this->getPrimaryKeyForTable($resolvedTable, $tableColumns);
        $dateCol = $this->getDateColumnForTable($resolvedTable, $tableColumns);

        $targetOffice = Office::getActiveOffice() ?? Office::first() ?? new Office(['id' => 1]);
        $officeId = (int) ($targetOffice->id ?? 1);

        // Build Conditions Array: add date range if provided
        $allConditions = is_array($conditions) ? $conditions : [];
        if ($startDate && $endDate && $dateCol) {
            $allConditions[] = [
                'column' => $dateCol,
                'operator' => 'BETWEEN',
                'value' => "{$startDate} 00:00:00, {$endDate} 23:59:59",
                'logical' => 'and',
            ];
        }

        // 1. Fetch from OpenDental Live API
        $liveKeys = [];
        $liveRowsByPk = [];
        $liveError = null;

        try {
            $odSql = $this->buildRawSqlString($odTableName, ['*'], $allConditions, $dateCol ?: $primaryKey, 'ASC', $limit, $tableColumns);
            $liveRowsRaw = $this->queryService->forOffice($targetOffice)->shortQuery($odSql);
            foreach ($liveRowsRaw as $row) {
                $r = (array) $row;
                if (isset($r[$primaryKey])) {
                    $pkVal = (string) $r[$primaryKey];
                    $liveKeys[] = $pkVal;
                    $liveRowsByPk[$pkVal] = $r;
                }
            }
        } catch (Exception $e) {
            $liveError = $e->getMessage();
        }

        // 2. Fetch from Local DB Snapshot
        $localQuery = DB::table($resolvedTable);
        if (in_array('office_id', $tableColumns, true)) {
            $localQuery->where('office_id', $officeId);
        }

        if ($startDate && $endDate && $dateCol) {
            $localQuery->whereBetween($dateCol, ["{$startDate} 00:00:00", "{$endDate} 23:59:59"]);
        }

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
                if ($op === '=') {
                    $localQuery->$whereMethod($col, $op, $val);
                } elseif ($op === 'IN') {
                    $localQuery->whereIn($col, array_map('trim', explode(',', (string) $val)));
                }
            }
        }

        $localRowsRaw = $localQuery->orderBy($dateCol ?: $primaryKey, 'asc')->limit($limit)->get();
        $localKeys = [];
        $localRowsByPk = [];
        foreach ($localRowsRaw as $row) {
            $r = (array) $row;
            if (isset($r[$primaryKey])) {
                $pkVal = (string) $r[$primaryKey];
                $localKeys[] = $pkVal;
                $localRowsByPk[$pkVal] = $r;
            }
        }

        // 3. Compute Diff Sets
        $matchedKeys = array_values(array_intersect($localKeys, $liveKeys));
        $orphanKeys = array_values(array_diff($localKeys, $liveKeys)); // in local, deleted in live OD
        $missingKeys = array_values(array_diff($liveKeys, $localKeys)); // in live OD, missing in local

        // Build structured diff rows
        $diffRows = [];

        // Orphans (Present in Local DB only - deleted in OpenDental)
        foreach ($orphanKeys as $k) {
            $diffRows[] = [
                'status' => 'orphan',
                'status_label' => 'Deleted in OpenDental (Orphan in Local DB)',
                'status_badge' => 'red',
                'pk' => $k,
                'primary_key_name' => $primaryKey,
                'source' => 'local_only',
                'data' => $localRowsByPk[$k] ?? null,
            ];
        }

        // Missing (Present in Live OD only - not synced to Local DB)
        foreach ($missingKeys as $k) {
            $diffRows[] = [
                'status' => 'missing',
                'status_label' => 'Missing from Local DB (Sync Needed)',
                'status_badge' => 'amber',
                'pk' => $k,
                'primary_key_name' => $primaryKey,
                'source' => 'live_only',
                'data' => $liveRowsByPk[$k] ?? null,
            ];
        }

        // Matched (In Both)
        foreach ($matchedKeys as $k) {
            $diffRows[] = [
                'status' => 'matched',
                'status_label' => 'Synced & Matched',
                'status_badge' => 'emerald',
                'pk' => $k,
                'primary_key_name' => $primaryKey,
                'source' => 'both',
                'data' => $liveRowsByPk[$k] ?? $localRowsByPk[$k] ?? null,
            ];
        }

        $executionTimeMs = round((microtime(true) - $startTime) * 1000, 2);
        $totalCombined = count($diffRows);
        $matchRate = $totalCombined > 0 ? round((count($matchedKeys) / $totalCombined) * 100, 1) : 100;

        return response()->json([
            'success' => true,
            'table' => $odTableName,
            'local_table' => $resolvedTable,
            'primary_key' => $primaryKey,
            'date_column' => $dateCol,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'execution_time_ms' => $executionTimeMs,
            'live_error' => $liveError,
            'columns' => $tableColumns,
            'summary' => [
                'live_count' => count($liveKeys),
                'local_count' => count($localKeys),
                'matched_count' => count($matchedKeys),
                'orphan_count' => count($orphanKeys),
                'missing_count' => count($missingKeys),
                'match_rate_pct' => $matchRate,
            ],
            'orphan_keys' => $orphanKeys,
            'missing_keys' => $missingKeys,
            'diff_rows' => $diffRows,
        ]);
    }

    public function pruneOrphans(Request $request): JsonResponse
    {
        $table = (string) $request->input('table');
        $keys = $request->input('keys', []);

        $resolvedTable = $this->resolveTableName($table);
        if (! $resolvedTable) {
            return response()->json(['error' => 'Invalid or unauthorized table selected.'], 400);
        }

        $tableColumns = DB::getSchemaBuilder()->getColumnListing($resolvedTable);
        $primaryKey = $this->getPrimaryKeyForTable($resolvedTable, $tableColumns);
        $targetOffice = Office::getActiveOffice() ?? Office::first() ?? new Office(['id' => 1]);
        $officeId = (int) ($targetOffice->id ?? 1);

        if (empty($keys) || ! is_array($keys)) {
            return response()->json(['error' => 'No record keys provided to prune.'], 400);
        }

        $cleanKeys = array_map('intval', $keys);
        $deletedCount = 0;

        foreach (array_chunk($cleanKeys, 500) as $chunk) {
            $query = DB::table($resolvedTable)->whereIn($primaryKey, $chunk);
            if (in_array('office_id', $tableColumns, true)) {
                $query->where('office_id', $officeId);
            }
            $deletedCount += $query->delete();
        }

        return response()->json([
            'success' => true,
            'deleted_count' => $deletedCount,
            'message' => "Successfully pruned {$deletedCount} orphan record(s) from local table '{$resolvedTable}'.",
        ]);
    }

    private function getDateColumnForTable(string $table, array $columns): ?string
    {
        $map = [
            'od_appointments' => 'AptDateTime',
            'appointment' => 'AptDateTime',
            'od_procedure_logs' => 'ProcDate',
            'procedurelog' => 'ProcDate',
            'od_adjustments' => 'AdjDate',
            'adjustment' => 'AdjDate',
            'od_claim_procs' => 'ProcDate',
            'claimproc' => 'ProcDate',
            'od_pay_splits' => 'DatePay',
            'paysplit' => 'DatePay',
            'od_payments' => 'PayDate',
            'payment' => 'PayDate',
            'treatment_plans' => 'DateTP',
            'treatmentplan' => 'DateTP',
            'od_schedules' => 'SchedDate',
            'schedule' => 'SchedDate',
            'od_recalls' => 'DateDue',
            'recall' => 'DateDue',
            'od_patients' => 'DateFirstVisit',
            'patient' => 'DateFirstVisit',
            'od_claim_payments' => 'SecDateTEdit',
            'claimpayment' => 'SecDateTEdit',
            'od_pay_plan_charges' => 'ChargeDate',
            'payplancharge' => 'ChargeDate',
            'od_deposits' => 'DateDeposit',
            'deposit' => 'DateDeposit',
        ];

        if (isset($map[$table])) {
            return $map[$table];
        }

        foreach (['AptDateTime', 'ProcDate', 'AdjDate', 'PayDate', 'DatePay', 'DateTP', 'DateTStamp', 'created_at'] as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }

        return null;
    }
}
