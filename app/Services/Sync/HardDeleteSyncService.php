<?php

namespace App\Services\Sync;

use App\Models\Office;
use App\Models\SyncLogPrune;
use App\Services\OpenDental\QueryService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Throwable;

/**
 * Removes local rows that were hard-deleted in OpenDental.
 *
 * For each office + table + window, local primary keys are walked in chunks;
 * every chunk is looked up in OpenDental by primary key, and only keys
 * OpenDental does not return are deleted locally.
 *
 * Failure handling:
 *  - chunked keyset walk: constant memory, even for multi-million-row tables;
 *  - progress is saved per chunk (sync_logs cursor) under a SyncLease, so a
 *    time-budgeted or killed run resumes and two runs never overlap;
 *  - an API error aborts before that chunk is deleted;
 *  - a chunk with an implausible share of "missing" rows is refused
 *    (PruneSafetyException) — a wrong API key looks exactly like mass deletion.
 */
class HardDeleteSyncService
{
    public const MODE_ROLLING = 'rolling';

    public const MODE_TODAY = 'today';

    public const MODE_CURRENT_MONTH = 'current_month';

    public const MODE_CURRENT_YEAR = 'current_year';

    public const MODE_RANGE = 'range';

    public const MODE_FULL = 'full';

    /**
     * Prune-specific date columns per local table. A row is inside a window when
     * ANY of these (or its local created_at) falls in it. OpenDental table name
     * and primary key come from OpenDentalTableCatalog.
     *
     * @var array<string, array{date_cols: list<string>, is_datetime: bool}>
     */
    private const DATE_COLUMNS = [
        'od_procedure_logs' => ['date_cols' => ['ProcDate', 'DateEntryC', 'DateTP'], 'is_datetime' => false],
        'od_appointments' => ['date_cols' => ['AptDateTime'], 'is_datetime' => true],
        'od_adjustments' => ['date_cols' => ['AdjDate', 'DateEntry', 'SecDateTEdit'], 'is_datetime' => false],
        'od_claim_procs' => ['date_cols' => ['ProcDate', 'DateCP', 'DateEntry'], 'is_datetime' => false],
        'od_pay_splits' => ['date_cols' => ['DatePay', 'DateEntry', 'SecDateTEdit'], 'is_datetime' => false],
        'od_payments' => ['date_cols' => ['PayDate', 'DateEntry', 'SecDateTEdit'], 'is_datetime' => false],
        'od_claim_payments' => ['date_cols' => ['CheckDate', 'DateIssued', 'SecDateTEdit'], 'is_datetime' => false],
        'od_recalls' => ['date_cols' => ['DateDue'], 'is_datetime' => false],
        'od_schedules' => ['date_cols' => ['SchedDate'], 'is_datetime' => false],
        'treatment_plans' => ['date_cols' => ['DateTP'], 'is_datetime' => false],
        'od_patients' => ['date_cols' => ['SecDateEntry', 'DateFirstVisit'], 'is_datetime' => false],
        'od_pay_plan_charges' => ['date_cols' => ['ChargeDate', 'SecDateTEdit'], 'is_datetime' => false],
        'od_treatment_plan_attachments' => ['date_cols' => ['SecDateTEdit'], 'is_datetime' => false],
        'od_deposits' => ['date_cols' => ['DateDeposit'], 'is_datetime' => false],
        'od_statements' => ['date_cols' => ['DateSent'], 'is_datetime' => false],
        'od_histappointments' => ['date_cols' => ['HistDate', 'AptDateTime'], 'is_datetime' => false],
        'od_insplans' => ['date_cols' => ['SecDateTEdit'], 'is_datetime' => false],
    ];

    public function __construct(
        protected QueryService $queryService,
        private readonly OpenDentalTableCatalog $catalog,
    ) {}

    /**
     * Local tables that can be pruned.
     *
     * @return list<string>
     */
    public function getSupportedTables(): array
    {
        return array_values(array_filter(
            array_keys(self::DATE_COLUMNS),
            fn (string $table) => $this->catalog->resolve($table)?->existsInOpenDental === true
        ));
    }

    /**
     * Resolve the date window for a mode.
     *
     * @return array{0: ?string, 1: ?string} [start, end] — both null for a full scan
     */
    public function windowFor(string $mode, ?string $startDate = null, ?string $endDate = null): array
    {
        return match ($mode) {
            self::MODE_FULL => [null, null],
            self::MODE_TODAY => [now()->toDateString(), now()->toDateString()],
            self::MODE_CURRENT_MONTH => [now()->startOfMonth()->toDateString(), now()->toDateString()],
            self::MODE_CURRENT_YEAR => [now()->startOfYear()->toDateString(), now()->toDateString()],
            self::MODE_ROLLING => [
                now()->subDays((int) config('sync.prune.rolling_past_days', 7))->toDateString(),
                now()->addDays((int) config('sync.prune.rolling_future_days', 90))->toDateString(),
            ],
            self::MODE_RANGE => [$startDate ?? now()->startOfMonth()->toDateString(), $endDate ?? now()->toDateString()],
            default => throw new InvalidArgumentException("Unknown prune mode '{$mode}'."),
        };
    }

    /**
     * Prune one table for one office.
     *
     * @return array{
     *     table: string, office_id: int, mode: string, range: string,
     *     start_date: ?string, end_date: ?string,
     *     local_count: int, remote_count: int, orphan_count: int, orphan_keys: list<int>,
     *     deleted: bool, interrupted: bool, skipped: bool, log_id: ?int
     * }
     *
     * @throws PruneSafetyException when OpenDental reports an implausible share of rows missing
     */
    public function prune(
        string $tableKey,
        Office $office,
        string $mode,
        ?string $startDate = null,
        ?string $endDate = null,
        bool $dryRun = false,
        ?int $timeBudgetSeconds = null,
        bool $allowMassDelete = false,
    ): array {
        $table = $this->resolveTable($tableKey);
        [$startDate, $endDate] = $this->windowFor($mode, $startDate, $endDate);
        $officeId = (int) $office->id;
        $range = $startDate === null ? 'FULL SCAN' : ($startDate === $endDate ? $startDate : "{$startDate} to {$endDate}");
        $deadline = $timeBudgetSeconds !== null ? microtime(true) + $timeBudgetSeconds : null;

        $result = [
            'table' => $tableKey,
            'office_id' => $officeId,
            'mode' => $mode,
            'range' => $range,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'local_count' => 0,
            'remote_count' => 0,
            'orphan_count' => 0,
            'orphan_keys' => [],
            'deleted' => false,
            'interrupted' => false,
            'skipped' => false,
            'log_id' => null,
        ];

        // A dry run changes nothing, so it needs no lease or saved cursor.
        $lease = null;
        $cursor = 0;
        $baseTotal = 0;

        if (! $dryRun) {
            // Each mode keeps its own resumable cursor; explicit ranges are keyed by their dates too.
            $cursorName = $mode === self::MODE_RANGE ? "{$mode}:{$startDate}..{$endDate}" : $mode;
            $lease = SyncLease::acquire("office_{$officeId}:prune-deleted:{$tableKey}:{$cursorName}", $officeId);

            if ($lease === null) {
                return ['skipped' => true] + $result;
            }

            $cursor = (int) ($lease->log()->last_primary_key ?? 0);
            $baseTotal = $cursor > 0 ? (int) $lease->log()->total_processed : 0;
        }

        $pruneLog = $this->createPruneLog($officeId, $tableKey, $mode, $range);
        $result['log_id'] = $pruneLog?->id;
        $chunkSize = max(1, (int) config('sync.prune.chunk_size', 500));

        try {
            $this->queryService->forOffice($office);

            while (true) {
                $chunk = $this->localKeysChunk($tableKey, $table->primaryKey, $officeId, $startDate, $endDate, $cursor, $chunkSize);

                if ($chunk === []) {
                    break;
                }

                $remoteKeys = $this->fetchRemotePrimaryKeys($table->key, $table->primaryKey, $chunk, $officeId);
                $orphans = array_values(array_diff($chunk, $remoteKeys));

                if (! $dryRun && ! $allowMassDelete) {
                    $this->assertPlausible(count($chunk), count($orphans), $tableKey, $officeId);
                }

                if (! $dryRun && $orphans !== []) {
                    DB::table($tableKey)->where('office_id', $officeId)->whereIn($table->primaryKey, $orphans)->delete();
                }

                $result['local_count'] += count($chunk);
                $result['remote_count'] += count($remoteKeys);
                $result['orphan_count'] += count($orphans);
                $result['orphan_keys'] = [...$result['orphan_keys'], ...$orphans];
                // Last key in the walk order (not max): some local key columns are varchar,
                // where "10" sorts before "9". Resuming after the last *ordered* key never skips rows.
                $cursor = $chunk[array_key_last($chunk)];

                $lease?->heartbeat([
                    'last_primary_key' => $cursor,
                    'total_processed' => $baseTotal + $result['orphan_count'],
                ]);

                if (count($chunk) < $chunkSize) {
                    break;
                }

                if ($deadline !== null && microtime(true) >= $deadline) {
                    $result['interrupted'] = true;
                    break;
                }
            }

            $result['deleted'] = ! $dryRun && $result['orphan_count'] > 0;
            $this->finishPruneLog($pruneLog, $result['local_count'], $result['remote_count'], $result['orphan_count'], $dryRun);

            if ($result['interrupted']) {
                $lease?->pause();
            } else {
                // Pass complete: next run starts from the beginning of its window.
                $lease?->complete([
                    'last_primary_key' => 0,
                    'total_processed' => $baseTotal + $result['orphan_count'],
                    'last_synced_at' => now(),
                ]);
            }

            return $result;
        } catch (Throwable $e) {
            $this->failPruneLog($pruneLog, $e->getMessage());
            $lease?->fail($e);

            throw $e;
        }
    }

    /*
    | Mode shortcuts (kept for existing callers).
    */

    public function pruneAllRecords(string $tableKey, ?Office $office = null, bool $dryRun = false): array
    {
        return $this->prune($tableKey, $this->officeOrFail($office), self::MODE_FULL, dryRun: $dryRun);
    }

    public function pruneToday(string $tableKey, ?Office $office = null, bool $dryRun = false): array
    {
        return $this->prune($tableKey, $this->officeOrFail($office), self::MODE_TODAY, dryRun: $dryRun);
    }

    public function pruneCurrentMonth(string $tableKey, ?Office $office = null, bool $dryRun = false): array
    {
        return $this->prune($tableKey, $this->officeOrFail($office), self::MODE_CURRENT_MONTH, dryRun: $dryRun);
    }

    public function pruneCurrentYear(string $tableKey, ?Office $office = null, bool $dryRun = false): array
    {
        return $this->prune($tableKey, $this->officeOrFail($office), self::MODE_CURRENT_YEAR, dryRun: $dryRun);
    }

    public function pruneTable(string $tableKey, string $startDate, string $endDate, ?Office $office = null, bool $dryRun = false, string $mode = self::MODE_RANGE): array
    {
        if ($startDate > $endDate) {
            throw new InvalidArgumentException("Invalid date range: {$startDate} is after {$endDate}.");
        }

        return $this->prune($tableKey, $this->officeOrFail($office), $mode, $startDate, $endDate, $dryRun);
    }

    private function resolveTable(string $tableKey): OpenDentalTable
    {
        $table = isset(self::DATE_COLUMNS[$tableKey]) ? $this->catalog->resolve($tableKey) : null;

        if ($table === null || ! $table->existsInOpenDental || $table->localTable !== $tableKey) {
            throw new Exception("Unsupported table for hard-delete reconciliation: {$tableKey}. Supported tables: ".implode(', ', $this->getSupportedTables()));
        }

        return $table;
    }

    private function officeOrFail(?Office $office): Office
    {
        return $office ?? throw new InvalidArgumentException('An office is required for pruning.');
    }

    /**
     * Refuse chunks where "missing in OpenDental" is implausibly common.
     */
    private function assertPlausible(int $checked, int $missing, string $tableKey, int $officeId): void
    {
        if ($missing === 0) {
            return;
        }

        $ratio = $missing / $checked;
        $allMissing = $checked >= 10 && $missing === $checked;
        $mostlyMissing = $checked >= (int) config('sync.prune.max_missing_min_rows', 20)
            && $ratio > (float) config('sync.prune.max_missing_ratio', 0.5);

        if ($allMissing || $mostlyMissing) {
            throw new PruneSafetyException(sprintf(
                "Refused to prune '%s' for office [%d]: OpenDental reported %d of %d records missing (%d%%). ".
                'This usually means a wrong API key or database, not real deletions. Nothing in this batch was deleted. '.
                'If the deletions are genuine, run: php artisan sync:prune-deleted %s --office-id=%d --force',
                $tableKey, $officeId, $missing, $checked, (int) round($ratio * 100), $tableKey, $officeId
            ));
        }
    }

    /**
     * Next chunk of local primary keys in the window, after the cursor (keyset walk).
     *
     * @return list<int>
     */
    private function localKeysChunk(string $tableKey, string $pk, int $officeId, ?string $startDate, ?string $endDate, int $afterKey, int $limit): array
    {
        $query = DB::table($tableKey)
            ->where('office_id', $officeId)
            // Bound as a string so the comparison matches ORDER BY on both int and varchar key columns.
            ->where($pk, '>', (string) $afterKey)
            ->orderBy($pk)
            ->limit($limit);

        if ($startDate !== null && $endDate !== null) {
            $this->applyWindow($query, $tableKey, $startDate, $endDate);
        }

        return array_map('intval', $query->pluck($pk)->all());
    }

    private function applyWindow($query, string $tableKey, string $startDate, string $endDate): void
    {
        $config = self::DATE_COLUMNS[$tableKey];
        $columns = array_values(array_filter($config['date_cols'], fn (string $col) => Schema::hasColumn($tableKey, $col)));

        if (Schema::hasColumn($tableKey, 'created_at')) {
            $columns[] = 'created_at';
        }

        if ($columns === []) {
            return;
        }

        $query->where(function ($q) use ($columns, $config, $startDate, $endDate) {
            foreach ($columns as $column) {
                $isDateTime = $config['is_datetime'] || $column === 'created_at' || preg_match('/time|stamp/i', $column);

                $q->orWhereBetween($column, $isDateTime
                    ? ["{$startDate} 00:00:00", "{$endDate} 23:59:59"]
                    : [$startDate, $endDate]);
            }
        });
    }

    /**
     * Primary keys of the chunk that still exist in OpenDental.
     *
     * @param  list<int>  $chunk
     * @return list<int>
     *
     * @throws Exception when the API fails — the caller deletes nothing
     */
    protected function fetchRemotePrimaryKeys(string $odTable, string $pk, array $chunk, int $officeId): array
    {
        if ($chunk === []) {
            return [];
        }

        $odRows = $this->queryService->shortQuery("SELECT {$pk} FROM {$odTable} WHERE {$pk} IN (".implode(',', $chunk).')');

        if (! is_array($odRows)) {
            throw new Exception("OpenDental API query failed or returned non-array response for table '{$odTable}' (Office ID: {$officeId}). Response: ".json_encode($odRows));
        }

        if (isset($odRows['Error']) || isset($odRows['error']) || isset($odRows['Message'])) {
            $error = $odRows['Error'] ?? $odRows['error'] ?? $odRows['Message'];
            throw new Exception("OpenDental API error for table '{$odTable}' (Office ID: {$officeId}): {$error}");
        }

        $remoteKeys = [];

        foreach ($odRows as $row) {
            foreach ((array) $row as $column => $value) {
                if (strcasecmp((string) $column, $pk) === 0 && is_numeric($value)) {
                    $remoteKeys[] = (int) $value;
                    break;
                }
            }
        }

        return array_values(array_unique($remoteKeys));
    }

    private function createPruneLog(int $officeId, string $tableKey, string $mode, string $range): ?SyncLogPrune
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

    private function finishPruneLog(?SyncLogPrune $log, int $localCount, int $remoteCount, int $orphanCount, bool $dryRun): void
    {
        try {
            $log?->update([
                'local_count' => $localCount,
                'remote_count' => $remoteCount,
                'orphan_count' => $orphanCount,
                'status' => $dryRun ? 'dry_run' : 'completed',
                'completed_at' => now(),
            ]);
        } catch (Throwable) {
            // Audit log only; never fail a prune because of it.
        }
    }

    private function failPruneLog(?SyncLogPrune $log, string $errorMessage): void
    {
        try {
            $log?->update([
                'status' => 'failed',
                'error_message' => mb_substr($errorMessage, 0, 65000),
                'completed_at' => now(),
            ]);
        } catch (Throwable) {
            // Audit log only.
        }
    }
}
