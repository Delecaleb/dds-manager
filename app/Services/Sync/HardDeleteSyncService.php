<?php

namespace App\Services\Sync;

use App\Models\Office;
use App\Models\SyncLog;
use App\Models\SyncLogPrune;
use App\Services\OpenDental\QueryService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class HardDeleteSyncService
{
    /**
     * Configuration for supported OpenDental synced tables.
     *
     * @var array<string, array{
     *     od_table: string,
     *     pk: string,
     *     date_col: string,
     *     is_datetime: bool
     * }>
     */
    protected array $tableConfigs = [
        'od_procedure_logs' => [
            'od_table' => 'procedurelog',
            'pk' => 'ProcNum',
            'date_col' => 'ProcDate',
            'is_datetime' => false,
        ],
        'od_appointments' => [
            'od_table' => 'appointment',
            'pk' => 'AptNum',
            'date_col' => 'AptDateTime',
            'is_datetime' => true,
        ],
        'od_adjustments' => [
            'od_table' => 'adjustment',
            'pk' => 'AdjNum',
            'date_col' => 'AdjDate',
            'is_datetime' => false,
        ],
        'od_claim_procs' => [
            'od_table' => 'claimproc',
            'pk' => 'ClaimProcNum',
            'date_col' => 'ProcDate',
            'is_datetime' => false,
        ],
        'od_pay_splits' => [
            'od_table' => 'paysplit',
            'pk' => 'SplitNum',
            'date_col' => 'DatePay',
            'is_datetime' => false,
        ],
        'od_payments' => [
            'od_table' => 'payment',
            'pk' => 'PayNum',
            'date_col' => 'PayDate',
            'is_datetime' => false,
        ],
        'od_claim_payments' => [
            'od_table' => 'claimpayment',
            'pk' => 'ClaimPaymentNum',
            'date_col' => 'SecDateTEdit',
            'is_datetime' => false,
        ],
        'od_recalls' => [
            'od_table' => 'recall',
            'pk' => 'RecallNum',
            'date_col' => 'DateDue',
            'is_datetime' => false,
        ],
        'od_schedules' => [
            'od_table' => 'schedule',
            'pk' => 'ScheduleNum',
            'date_col' => 'SchedDate',
            'is_datetime' => false,
        ],
        'treatment_plans' => [
            'od_table' => 'treatplan',
            'pk' => 'TreatPlanNum',
            'date_col' => 'DateTP',
            'is_datetime' => false,
        ],
        'od_patients' => [
            'od_table' => 'patient',
            'pk' => 'PatNum',
            'date_col' => 'SecDateEntry',
            'is_datetime' => false,
        ],
        'od_pay_plan_charges' => [
            'od_table' => 'payplancharge',
            'pk' => 'PayPlanChargeNum',
            'date_col' => 'ChargeDate',
            'is_datetime' => false,
        ],
        'od_treatment_plan_attachments' => [
            'od_table' => 'treatplanattach',
            'pk' => 'TreatPlanAttachNum',
            'date_col' => 'SecDateTEdit',
            'is_datetime' => false,
        ],
        'od_deposits' => [
            'od_table' => 'deposit',
            'pk' => 'DepositNum',
            'date_col' => 'DateDeposit',
            'is_datetime' => false,
        ],
        'od_statements' => [
            'od_table' => 'statement',
            'pk' => 'StatementNum',
            'date_col' => 'DateSent',
            'is_datetime' => false,
        ],
        'od_histappointments' => [
            'od_table' => 'histappointment',
            'pk' => 'HistAptNum',
            'date_col' => 'HistDate',
            'is_datetime' => false,
        ],
        'od_insplans' => [
            'od_table' => 'insplan',
            'pk' => 'PlanNum',
            'date_col' => 'SecDateTEdit',
            'is_datetime' => false,
        ],
    ];

    public function __construct(
        protected QueryService $queryService
    ) {}

    /**
     * Get all supported table keys.
     *
     * @return array<string>
     */
    public function getSupportedTables(): array
    {
        return array_keys($this->tableConfigs);
    }

    /**
     * Reconcile ALL local records for a table against OpenDental in primary key batches,
     * removing any records in local DB that no longer exist in OpenDental.
     *
     * @return array{
     *     table: string,
     *     office_id: int,
     *     mode: string,
     *     range: string,
     *     local_count: int,
     *     remote_count: ?int,
     *     orphan_count: int,
     *     orphan_keys: array<int>,
     *     deleted: bool,
     *     log_id: ?int
     * }
     */
    public function pruneAllRecords(
        string $tableKey,
        ?Office $office = null,
        bool $dryRun = false
    ): array {
        if (! isset($this->tableConfigs[$tableKey])) {
            throw new Exception("Unsupported table for hard-delete reconciliation: {$tableKey}. Supported tables: ".implode(', ', $this->getSupportedTables()));
        }

        $config = $this->tableConfigs[$tableKey];
        $targetOffice = $office ?? Office::getActiveOffice() ?? Office::first() ?? new Office(['id' => 1]);
        $officeId = (int) ($targetOffice->id ?? 1);

        $module = "office_{$officeId}:prune-deleted:{$tableKey}:full";
        $pruneLog = $this->createPruneLog($officeId, $tableKey, 'full', 'FULL SCAN');

        if (! $dryRun) {
            $this->startSyncLog($officeId, $module);
        }

        try {
            $this->queryService->forOffice($targetOffice);

            $pk = $config['pk'];
            $odTable = $config['od_table'];

            if (! Schema::hasTable($tableKey)) {
                $this->finishPruneLog($pruneLog, 0, 0, 0, $dryRun);

                return [
                    'table' => $tableKey,
                    'office_id' => $officeId,
                    'mode' => 'full',
                    'range' => 'FULL SCAN',
                    'local_count' => 0,
                    'remote_count' => 0,
                    'orphan_count' => 0,
                    'orphan_keys' => [],
                    'deleted' => false,
                    'log_id' => $pruneLog?->id,
                ];
            }

            // Get all local primary keys strictly for this office
            $localKeys = array_map('intval', DB::table($tableKey)->where('office_id', $officeId)->pluck($pk)->toArray());
            $localCount = count($localKeys);

            $orphanKeys = [];
            $remoteCount = 0;

            if (! empty($localKeys)) {
                // Process in chunks of 500 keys to avoid SQL query length limits
                foreach (array_chunk($localKeys, 500) as $chunk) {
                    $inClause = implode(',', $chunk);
                    $odSql = "SELECT {$pk} FROM {$odTable} WHERE {$pk} IN ({$inClause})";

                    $odRows = $this->queryService->shortQuery($odSql);
                    $chunkRemoteKeys = array_map('intval', array_column($odRows, $pk));
                    $remoteCount += count($chunkRemoteKeys);

                    $chunkOrphans = array_values(array_diff($chunk, $chunkRemoteKeys));
                    if (! empty($chunkOrphans)) {
                        $orphanKeys = array_merge($orphanKeys, $chunkOrphans);
                    }
                }
            }

            // Strictly delete orphan records belonging to this office
            if (! $dryRun && ! empty($orphanKeys)) {
                foreach (array_chunk($orphanKeys, 500) as $chunk) {
                    DB::table($tableKey)
                        ->where('office_id', $officeId)
                        ->whereIn($pk, $chunk)
                        ->delete();
                }
            }

            $this->finishPruneLog($pruneLog, $localCount, $remoteCount, count($orphanKeys), $dryRun);

            if (! $dryRun) {
                $this->completeSyncLog($officeId, $module, count($orphanKeys));
            }

            return [
                'table' => $tableKey,
                'office_id' => $officeId,
                'mode' => 'full',
                'range' => 'FULL SCAN',
                'local_count' => $localCount,
                'remote_count' => $remoteCount,
                'orphan_count' => count($orphanKeys),
                'orphan_keys' => $orphanKeys,
                'deleted' => ! $dryRun && ! empty($orphanKeys),
                'log_id' => $pruneLog?->id,
            ];
        } catch (Throwable $e) {
            $this->failPruneLog($pruneLog, $e->getMessage());

            if (! $dryRun) {
                $this->failSyncLog($officeId, $module, $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Incremental pruning for data added that very day (today).
     */
    public function pruneToday(
        string $tableKey,
        ?Office $office = null,
        bool $dryRun = false
    ): array {
        $today = now()->toDateString();

        return $this->pruneTable($tableKey, $today, $today, $office, $dryRun, 'today');
    }

    /**
     * Initial pruning for data added this month (e.g. September).
     */
    public function pruneCurrentMonth(
        string $tableKey,
        ?Office $office = null,
        bool $dryRun = false
    ): array {
        $startDate = now()->startOfMonth()->toDateString();
        $endDate = now()->toDateString();

        return $this->pruneTable($tableKey, $startDate, $endDate, $office, $dryRun, 'current_month');
    }

    /**
     * Reconcile local records for the current year (Jan 1 of current year to today).
     */
    public function pruneCurrentYear(
        string $tableKey,
        ?Office $office = null,
        bool $dryRun = false
    ): array {
        $startDate = now()->startOfYear()->toDateString();
        $endDate = now()->toDateString();

        return $this->pruneTable($tableKey, $startDate, $endDate, $office, $dryRun, 'current_year');
    }

    /**
     * Reconcile local records against OpenDental for a specific table and date range.
     * Pruning is ONLY carried out if and only if the data does not exist in OpenDental.
     *
     * @return array{
     *     table: string,
     *     office_id: int,
     *     mode: string,
     *     range: string,
     *     start_date: string,
     *     end_date: string,
     *     remote_count: int,
     *     local_count: int,
     *     orphan_count: int,
     *     orphan_keys: array<int>,
     *     deleted: bool,
     *     log_id: ?int
     * }
     */
    public function pruneTable(
        string $tableKey,
        string $startDate,
        string $endDate,
        ?Office $office = null,
        bool $dryRun = false,
        string $mode = 'range'
    ): array {
        if (! isset($this->tableConfigs[$tableKey])) {
            throw new Exception("Unsupported table for hard-delete reconciliation: {$tableKey}. Supported tables: ".implode(', ', $this->getSupportedTables()));
        }

        $config = $this->tableConfigs[$tableKey];
        $targetOffice = $office ?? Office::getActiveOffice() ?? Office::first() ?? new Office(['id' => 1]);
        $officeId = (int) ($targetOffice->id ?? 1);

        $rangeString = ($startDate === $endDate) ? $startDate : "{$startDate} to {$endDate}";
        $module = "office_{$officeId}:prune-deleted:{$tableKey}";
        $pruneLog = $this->createPruneLog($officeId, $tableKey, $mode, $rangeString);

        if (! $dryRun) {
            $this->startSyncLog($officeId, $module);
        }

        try {
            $this->queryService->forOffice($targetOffice);

            $pk = $config['pk'];
            $odTable = $config['od_table'];
            $dateCol = $config['date_col'];
            $isDateTime = $config['is_datetime'];

            if (! Schema::hasTable($tableKey)) {
                $this->finishPruneLog($pruneLog, 0, 0, 0, $dryRun);

                return [
                    'table' => $tableKey,
                    'office_id' => $officeId,
                    'mode' => $mode,
                    'range' => $rangeString,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'remote_count' => 0,
                    'local_count' => 0,
                    'orphan_count' => 0,
                    'orphan_keys' => [],
                    'deleted' => false,
                    'log_id' => $pruneLog?->id,
                ];
            }

            // 1. Fetch local primary keys added/dated within the window for this office
            $localQuery = DB::table($tableKey)->where('office_id', $officeId);
            $hasDateCol = Schema::hasColumn($tableKey, $dateCol);
            $hasCreatedAt = Schema::hasColumn($tableKey, 'created_at');

            if ($tableKey === 'od_patients') {
                // For patient table: capture records by SecDateEntry, DateFirstVisit, or created_at
                $localQuery->where(function ($q) use ($dateCol, $startDate, $endDate, $hasCreatedAt) {
                    $q->whereBetween($dateCol, [$startDate, $endDate]);
                    if (Schema::hasColumn('od_patients', 'DateFirstVisit')) {
                        $q->orWhereBetween('DateFirstVisit', [$startDate, $endDate]);
                    }
                    if ($hasCreatedAt) {
                        $q->orWhereBetween('created_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"]);
                    }
                });
            } elseif ($hasDateCol) {
                if ($isDateTime) {
                    $localQuery->whereBetween($dateCol, ["{$startDate} 00:00:00", "{$endDate} 23:59:59"]);
                } else {
                    $localQuery->whereBetween($dateCol, [$startDate, $endDate]);
                }
            } elseif ($hasCreatedAt) {
                $localQuery->whereBetween('created_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"]);
            }

            $localKeys = array_map('intval', $localQuery->pluck($pk)->toArray());
            $localCount = count($localKeys);

            // 2. Query OpenDental directly by primary keys (WHERE pk IN (...))
            // This ensures we check if the data exists anywhere in OpenDental, preventing false positives
            $orphanKeys = [];
            $remoteCount = 0;

            if (! empty($localKeys)) {
                foreach (array_chunk($localKeys, 500) as $chunk) {
                    $inClause = implode(',', $chunk);
                    $odSql = "SELECT {$pk} FROM {$odTable} WHERE {$pk} IN ({$inClause})";

                    $odRows = $this->queryService->shortQuery($odSql);
                    $chunkRemoteKeys = array_map('intval', array_column($odRows, $pk));
                    $remoteCount += count($chunkRemoteKeys);

                    // Any key not returned by OpenDental does NOT exist in OpenDental (True Orphan)
                    $chunkOrphans = array_values(array_diff($chunk, $chunkRemoteKeys));
                    if (! empty($chunkOrphans)) {
                        $orphanKeys = array_merge($orphanKeys, $chunkOrphans);
                    }
                }
            }

            // 3. Delete orphan records strictly scoped to this office
            if (! $dryRun && ! empty($orphanKeys)) {
                foreach (array_chunk($orphanKeys, 500) as $chunk) {
                    DB::table($tableKey)
                        ->where('office_id', $officeId)
                        ->whereIn($pk, $chunk)
                        ->delete();
                }
            }

            $this->finishPruneLog($pruneLog, $localCount, $remoteCount, count($orphanKeys), $dryRun);

            if (! $dryRun) {
                $this->completeSyncLog($officeId, $module, count($orphanKeys));
            }

            return [
                'table' => $tableKey,
                'office_id' => $officeId,
                'mode' => $mode,
                'range' => $rangeString,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'remote_count' => $remoteCount,
                'local_count' => $localCount,
                'orphan_count' => count($orphanKeys),
                'orphan_keys' => $orphanKeys,
                'deleted' => ! $dryRun && ! empty($orphanKeys),
                'log_id' => $pruneLog?->id,
            ];
        } catch (Throwable $e) {
            $this->failPruneLog($pruneLog, $e->getMessage());

            if (! $dryRun) {
                $this->failSyncLog($officeId, $module, $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Create initial record in sync_log_prune.
     */
    protected function createPruneLog(int $officeId, string $tableKey, string $mode, string $range): ?SyncLogPrune
    {
        try {
            if (! Schema::hasTable('sync_log_prune')) {
                return null;
            }

            return SyncLogPrune::create([
                'office_id' => $officeId,
                'table_name' => $tableKey,
                'mode' => $mode,
                'range' => $range,
                'local_count' => 0,
                'remote_count' => null,
                'orphan_count' => 0,
                'status' => 'running',
                'started_at' => now(),
            ]);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Finalize sync_log_prune record upon success.
     */
    protected function finishPruneLog(?SyncLogPrune $log, int $localCount, ?int $remoteCount, int $orphanCount, bool $dryRun): void
    {
        if (! $log) {
            return;
        }

        try {
            $log->update([
                'local_count' => $localCount,
                'remote_count' => $remoteCount,
                'orphan_count' => $orphanCount,
                'status' => $dryRun ? 'dry_run' : 'completed',
                'completed_at' => now(),
            ]);
        } catch (Throwable) {
            // Safe fallback
        }
    }

    /**
     * Finalize sync_log_prune record upon failure.
     */
    protected function failPruneLog(?SyncLogPrune $log, string $errorMessage): void
    {
        if (! $log) {
            return;
        }

        try {
            $log->update([
                'status' => 'failed',
                'error_message' => $errorMessage,
                'completed_at' => now(),
            ]);
        } catch (Throwable) {
            // Safe fallback
        }
    }

    /**
     * Start sync_logs record for hard-delete prune run.
     */
    protected function startSyncLog(int $officeId, string $module): void
    {
        try {
            if (! Schema::hasTable('sync_logs')) {
                return;
            }

            $log = SyncLog::firstOrCreate(
                ['module' => $module],
                [
                    'office_id' => $officeId,
                    'status' => 'idle',
                    'total_processed' => 0,
                ]
            );

            $log->update([
                'office_id' => $officeId,
                'status' => 'running',
                'started_at' => now(),
                'last_error' => null,
            ]);
        } catch (Throwable) {
            // Safe fallback
        }
    }

    /**
     * Mark sync_logs record as completed.
     */
    protected function completeSyncLog(int $officeId, string $module, int $totalProcessed): void
    {
        try {
            if (! Schema::hasTable('sync_logs')) {
                return;
            }

            $log = SyncLog::firstOrCreate(
                ['module' => $module],
                [
                    'office_id' => $officeId,
                    'status' => 'idle',
                    'total_processed' => 0,
                ]
            );

            $log->update([
                'office_id' => $officeId,
                'status' => 'completed',
                'finished_at' => now(),
                'total_processed' => $totalProcessed,
                'last_synced_at' => now(),
                'retry_count' => 0,
            ]);
        } catch (Throwable) {
            // Safe fallback
        }
    }

    /**
     * Mark sync_logs record as failed.
     */
    protected function failSyncLog(int $officeId, string $module, string $error): void
    {
        try {
            if (! Schema::hasTable('sync_logs')) {
                return;
            }

            $log = SyncLog::firstOrCreate(
                ['module' => $module],
                [
                    'office_id' => $officeId,
                    'status' => 'idle',
                    'total_processed' => 0,
                ]
            );

            $log->increment('retry_count');
            $log->update([
                'office_id' => $officeId,
                'status' => 'failed',
                'last_error' => $error,
            ]);
        } catch (Throwable) {
            // Safe fallback
        }
    }
}
