<?php

namespace App\Services\Sync;

use App\Models\Office;
use App\Services\OpenDental\QueryService;
use Exception;
use Illuminate\Support\Facades\DB;

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
            'date_col' => 'PayDate',
            'is_datetime' => false,
        ],
        'od_recalls' => [
            'od_table' => 'recall',
            'pk' => 'RecallNum',
            'date_col' => 'DateDue',
            'is_datetime' => false,
        ],
        'od_schedule' => [
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
     *     local_count: int,
     *     orphan_count: int,
     *     orphan_keys: array<int>,
     *     deleted: bool
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

        $this->queryService->forOffice($targetOffice);

        $pk = $config['pk'];
        $odTable = $config['od_table'];

        // Get all local primary keys for this table and office
        $localKeys = array_map('intval', DB::table($tableKey)->where('office_id', $officeId)->pluck($pk)->toArray());
        $localCount = count($localKeys);

        $orphanKeys = [];

        if (! empty($localKeys)) {
            // Process in chunks of 500 keys to avoid SQL query length limits
            foreach (array_chunk($localKeys, 500) as $chunk) {
                $inClause = implode(',', $chunk);
                $odSql = "SELECT {$pk} FROM {$odTable} WHERE {$pk} IN ({$inClause})";

                $odRows = $this->queryService->shortQuery($odSql);
                $remoteKeys = array_map('intval', array_column($odRows, $pk));

                $chunkOrphans = array_values(array_diff($chunk, $remoteKeys));
                if (! empty($chunkOrphans)) {
                    $orphanKeys = array_merge($orphanKeys, $chunkOrphans);
                }
            }
        }

        // Delete orphan records if not dry run
        if (! $dryRun && ! empty($orphanKeys)) {
            foreach (array_chunk($orphanKeys, 500) as $chunk) {
                DB::table($tableKey)
                    ->where('office_id', $officeId)
                    ->whereIn($pk, $chunk)
                    ->delete();
            }
        }

        return [
            'table' => $tableKey,
            'office_id' => $officeId,
            'mode' => 'full',
            'local_count' => $localCount,
            'orphan_count' => count($orphanKeys),
            'orphan_keys' => $orphanKeys,
            'deleted' => ! $dryRun && ! empty($orphanKeys),
        ];
    }

    /**
     * Reconcile local records for the current year (Jan 1 of current year to today).
     *
     * @return array{
     *     table: string,
     *     office_id: int,
     *     start_date: string,
     *     end_date: string,
     *     remote_count: int,
     *     local_count: int,
     *     orphan_count: int,
     *     orphan_keys: array<int>,
     *     deleted: bool
     * }
     */
    public function pruneCurrentYear(
        string $tableKey,
        ?Office $office = null,
        bool $dryRun = false
    ): array {
        $startDate = now()->startOfYear()->toDateString();
        $endDate = now()->toDateString();

        return $this->pruneTable($tableKey, $startDate, $endDate, $office, $dryRun);
    }

    /**
     * Reconcile local records against OpenDental for a specific table and date range,
     * removing any records in local DB that no longer exist in OpenDental.
     *
     * @return array{
     *     table: string,
     *     office_id: int,
     *     start_date: string,
     *     end_date: string,
     *     remote_count: int,
     *     local_count: int,
     *     orphan_count: int,
     *     orphan_keys: array<int>,
     *     deleted: bool
     * }
     */
    public function pruneTable(
        string $tableKey,
        string $startDate,
        string $endDate,
        ?Office $office = null,
        bool $dryRun = false
    ): array {
        if (! isset($this->tableConfigs[$tableKey])) {
            throw new Exception("Unsupported table for hard-delete reconciliation: {$tableKey}. Supported tables: ".implode(', ', $this->getSupportedTables()));
        }

        $config = $this->tableConfigs[$tableKey];
        $targetOffice = $office ?? Office::getActiveOffice() ?? Office::first() ?? new Office(['id' => 1]);
        $officeId = (int) ($targetOffice->id ?? 1);

        $this->queryService->forOffice($targetOffice);

        $pk = $config['pk'];
        $odTable = $config['od_table'];
        $dateCol = $config['date_col'];
        $isDateTime = $config['is_datetime'];

        // 1. Prepare OpenDental SQL query
        if ($isDateTime) {
            $startBound = "{$startDate} 00:00:00";
            $endBound = "{$endDate} 23:59:59";
            $odSql = "SELECT {$pk} FROM {$odTable} WHERE {$dateCol} >= '{$startBound}' AND {$dateCol} <= '{$endBound}'";
        } else {
            $startBound = $startDate;
            $endBound = $endDate;
            $odSql = "SELECT {$pk} FROM {$odTable} WHERE {$dateCol} >= '{$startBound}' AND {$dateCol} <= '{$endBound}'";
        }

        // 2. Fetch primary keys present in OpenDental
        $odRows = $this->queryService->shortQuery($odSql);
        $remoteKeys = array_map('intval', array_column($odRows, $pk));

        // 3. Fetch primary keys present in local database
        $localQuery = DB::table($tableKey)->where('office_id', $officeId);
        if ($isDateTime) {
            $localQuery->whereBetween($dateCol, ["{$startDate} 00:00:00", "{$endDate} 23:59:59"]);
        } else {
            $localQuery->whereBetween($dateCol, [$startDate, $endDate]);
        }
        $localKeys = array_map('intval', $localQuery->pluck($pk)->toArray());

        // 4. Identify local keys missing from OpenDental (hard-deleted in OpenDental)
        $orphanKeys = array_values(array_diff($localKeys, $remoteKeys));

        // 5. Delete orphan records if not dry run
        if (! $dryRun && ! empty($orphanKeys)) {
            foreach (array_chunk($orphanKeys, 500) as $chunk) {
                DB::table($tableKey)
                    ->where('office_id', $officeId)
                    ->whereIn($pk, $chunk)
                    ->delete();
            }
        }

        return [
            'table' => $tableKey,
            'office_id' => $officeId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'remote_count' => count($remoteKeys),
            'local_count' => count($localKeys),
            'orphan_count' => count($orphanKeys),
            'orphan_keys' => $orphanKeys,
            'deleted' => ! $dryRun && ! empty($orphanKeys),
        ];
    }
}
