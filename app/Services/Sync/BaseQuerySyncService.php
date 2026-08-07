<?php

namespace App\Services\Sync;

use App\Models\Office;
use App\Models\SyncLog;
use App\Services\OpenDental\QueryService;
use Exception;
use Illuminate\Support\Facades\DB;

abstract class BaseQuerySyncService
{
    protected ?Office $office = null;

    protected int $batchSize = 1000;

    protected int $maxRetries = 5;

    protected int $sleepSeconds = 1;

    protected int $overlapSeconds = 300;

    protected ?string $windowStart = null;

    protected ?string $windowEnd = null;

    public function __construct(
        protected QueryService $queryService
    ) {}

    public function forOffice(?Office $office): static
    {
        $this->office = $office;

        return $this;
    }

    public function getOffice(): Office
    {
        return $this->office ?? Office::getActiveOffice() ?? Office::first() ?? new Office(['id' => 1]);
    }

    abstract protected function table(): string;

    abstract protected function model(): string;

    abstract protected function primaryKey(): string;

    protected function syncColumn(): ?string
    {
        return null;
    }

    /**
     * Business-date column the optional sync window filters on (e.g.
     * procedurelog.ProcDate). Null — the default — means the table cannot be
     * windowed and withDateWindow() will be rejected.
     *
     * This is deliberately NOT syncColumn(): syncColumn() is the *change*
     * timestamp driving incremental runs, while this is the *business* date
     * that decides whether a row belongs to the period we care about.
     */
    protected function dateColumn(): ?string
    {
        return null;
    }

    /**
     * Restrict this sync to rows whose dateColumn() falls inside the window.
     *
     * Both bounds are inclusive and either may be null (open-ended). A windowed
     * sync gets its OWN sync_logs row (see module()), so a backfill can never
     * overwrite the cursor or watermark of the full-table sync.
     *
     * @param  string|null  $start  'Y-m-d'
     * @param  string|null  $end  'Y-m-d'
     */
    public function withDateWindow(?string $start, ?string $end = null): static
    {
        if ($this->dateColumn() === null) {
            throw new Exception(static::class.' does not support a date window (no dateColumn() defined).');
        }

        if ($start !== null && $end !== null && $start > $end) {
            throw new Exception("Invalid sync window: start ({$start}) is after end ({$end}).");
        }

        $this->windowStart = $start;
        $this->windowEnd = $end;

        return $this;
    }

    protected function module(): string
    {
        $officeId = $this->getOffice()->id ?? 1;

        return "office_{$officeId}:".$this->table().$this->windowSuffix();
    }

    /**
     * Suffix that keeps a windowed run's cursor separate from the full sync's.
     *
     * Derived from the window bounds only, so re-running the same backfill
     * resumes the same sync_logs row instead of restarting from scratch.
     */
    protected function windowSuffix(): string
    {
        if ($this->windowStart === null && $this->windowEnd === null) {
            return '';
        }

        return ':'.($this->windowStart ?? 'min').'..'.($this->windowEnd ?? 'max');
    }

    /**
     * SQL fragment (leading " AND ...") bounding a query to the window.
     * Empty string when no window is set.
     */
    protected function windowClause(): string
    {
        $col = $this->dateColumn();

        if ($col === null) {
            return '';
        }

        $clause = '';

        if ($this->windowStart !== null) {
            $clause .= " AND {$col} >= '".addslashes($this->windowStart)."'";
        }

        if ($this->windowEnd !== null) {
            $clause .= " AND {$col} <= '".addslashes($this->windowEnd)."'";
        }

        return $clause;
    }

    /**
     * Hook to massage a raw OpenDental API row before it is persisted
     * locally. Base implementation is a pass-through; subclasses override
     * to normalize values (e.g. datetimes) so they land cleanly in typed
     * local columns.
     */
    protected function transformRow(array $row): array
    {
        return $row;
    }

    /**
     * Convert an OpenDental datetime string into a MySQL-storable
     * "Y-m-d H:i:s" value, or null when the source is blank/sentinel/
     * out-of-range. OpenDental emits ISO-8601 with a 'T' separator and uses
     * placeholder dates (e.g. 0001-01-01) for "no value"; both must be
     * normalized before they reach a real DATETIME column.
     */
    protected function normalizeDateTime($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = str_replace('T', ' ', trim((string) $value));

        if ($value === '') {
            return null;
        }

        // Reject OpenDental sentinels and anything outside MySQL's DATETIME
        // range (1000-01-01 .. 9999-12-31) — lexical compare is valid for
        // zero-padded ISO strings.
        if ($value < '1000-01-01 00:00:00' || $value > '9999-12-31 23:59:59') {
            return null;
        }

        $timestamp = strtotime($value);

        return $timestamp !== false ? date('Y-m-d H:i:s', $timestamp) : null;
    }

    /**
     * Convert an OpenDental date string into a MySQL-storable "Y-m-d" value,
     * or null when the source is blank/sentinel/out-of-range.
     */
    protected function normalizeDate($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = str_replace('T', ' ', trim((string) $value));

        if ($value === '' || $value < '1000-01-01' || $value > '9999-12-31') {
            return null;
        }

        $timestamp = strtotime($value);

        return $timestamp !== false ? date('Y-m-d', $timestamp) : null;
    }

    public function sync(): void
    {
        $office = $this->getOffice();
        $this->queryService->forOffice($office);

        $log = SyncLog::firstOrCreate(
            ['module' => $this->module()],
            [
                'office_id' => $office->id ?? 1,
                'status' => 'idle',
                'total_processed' => 0,
            ]
        );

        $log->update([
            'office_id' => $office->id ?? 1,
            'status' => 'running',
            'started_at' => now(),
            'last_error' => null,
        ]);

        try {

            // Initial mode until a full pass has completed at least once
            // (last_synced_at is only stamped on initial completion), or
            // always when the table has no incremental sync column.
            if ($this->syncColumn() === null || $log->last_synced_at === null) {

                $runStartedAt = now()->format('Y-m-d H:i:s');

                $this->runInitialSync($log);

                // Watermark for the first incremental run. Anything that
                // changed remotely while the initial sync was running is
                // covered by overlapSeconds + the hash-skip on re-scan.
                if ($this->syncColumn() !== null) {
                    $log->update(['last_synced_at' => $runStartedAt]);
                }

            } else {

                $this->runIncrementalSync($log);

            }

            $log->update([
                'status' => 'completed',
                'finished_at' => now(),
                'retry_count' => 0,
            ]);

        } catch (Exception $e) {

            $log->increment('retry_count');

            $log->update([
                'status' => 'failed',
                'last_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function runInitialSync(SyncLog $log): void
    {
        $pk = $this->primaryKey();
        $lastId = (int) ($log->last_primary_key ?? 0);
        $window = $this->windowClause();

        while (true) {

            $sql = "
                SELECT *
                FROM {$this->table()}
                WHERE {$pk} > {$lastId}{$window}
                ORDER BY {$pk}
                LIMIT {$this->batchSize}
            ";

            $rows = $this->executeWithRetry($sql);

            if (empty($rows)) {
                break;
            }

            [, $lastId] = $this->persistBatch($rows, $log);

            $log->update([
                'last_primary_key' => $lastId,
            ]);

            $this->logOutput("Synced through ID {$lastId}\n");

            sleep($this->sleepSeconds);
        }
    }

    protected function runIncrementalSync(SyncLog $log): void
    {
        $pk = $this->primaryKey();
        $col = $this->syncColumn();

        // Rewind the cursor to re-scan the boundary window. Rows already
        // stored identically are skipped by hash, so this costs reads only.
        $lastSync = date(
            'Y-m-d H:i:s',
            strtotime((string) $log->last_synced_at) - $this->overlapSeconds
        );

        $lastId = 0;
        $window = $this->windowClause();

        while (true) {

            $safeSync = addslashes($lastSync);

            // Keyset pagination on the (syncColumn, primaryKey) tuple.
            // A bare "syncColumn > X" cursor either skips rows that share a
            // timestamp across a batch boundary (with ">") or loops forever
            // when more than batchSize rows share one timestamp (with ">=").
            // The tuple cursor always advances and never skips.
            // The keyset OR-group is wrapped in its own parentheses so an
            // appended window clause ANDs against the whole cursor, not just
            // the second branch of the OR.
            $sql = "
                SELECT *
                FROM {$this->table()}
                WHERE (
                          ({$col} > '{$safeSync}')
                       OR ({$col} = '{$safeSync}' AND {$pk} > {$lastId})
                      ){$window}
                ORDER BY {$col}, {$pk}
                LIMIT {$this->batchSize}
            ";

            $rows = $this->executeWithRetry($sql);

            if (empty($rows)) {
                break;
            }

            [$lastSync, $lastId] = $this->persistBatch($rows, $log, $lastSync, $lastId);

            // Persist both halves of the cursor so a kill mid-run resumes
            // exactly where it left off (minus the overlap window).
            $log->update([
                'last_synced_at' => $lastSync,
                'last_primary_key' => $lastId,
            ]);

            $this->logOutput("Synced through {$lastSync} (ID {$lastId})\n");

            sleep($this->sleepSeconds);
        }
    }

    /**
     * Persist a batch of rows idempotently and advance the cursor.
     *
     * Identity is ALWAYS the OpenDental primary key — a row can never be
     * inserted twice, no matter how many times it is re-synced.
     *
     * - New records (not in local DB) are inserted.
     * - Existing records are updated if any substantive columns have changed.
     * - Local auto-generated columns (id, created_at, updated_at) are ignored.
     *
     * @return array{0: ?string, 1: int} [$lastSync, $lastId]
     */
    protected function persistBatch(array $rows, SyncLog $log, ?string $lastSync = null, int $lastId = 0): array
    {
        $modelClass = $this->model();
        $pk = $this->primaryKey();
        $col = $this->syncColumn();
        $officeId = $this->getOffice()->id ?? 1;

        DB::transaction(function () use ($rows, $log, $modelClass, $pk, $col, $officeId, &$lastSync, &$lastId) {

            foreach ($rows as $row) {

                $data = $this->transformRow($row);

                // Ignore local-only auto-generated columns
                unset($data['id'], $data['created_at'], $data['updated_at'], $data['row_hash']);

                $data['office_id'] = $officeId;

                // Safeguard non-note string attributes from exceeding MySQL VARCHAR limits
                foreach ($data as $key => $val) {
                    if (is_string($val) && strlen($val) > 255 && ! str_contains(strtolower($key), 'note')) {
                        $data[$key] = mb_substr($val, 0, 255);
                    }
                }

                $existing = $modelClass::where('office_id', $officeId)->where($pk, $row[$pk])->first();

                if ($existing === null) {

                    $model = new $modelClass;
                    $model->fill($data);
                    $model->{$pk} = $row[$pk];
                    $model->save();

                    $log->increment('total_processed');
                } else {

                    $existing->fill($data);

                    if ($existing->isDirty()) {
                        $existing->save();
                        $log->increment('total_processed');
                    }
                }

                $lastId = (int) $row[$pk];

                if ($col !== null && isset($row[$col])) {
                    $lastSync = $this->normalizeDateTime($row[$col]) ?? $lastSync;
                }
            }

        });

        return [$lastSync, $lastId];
    }

    protected function executeWithRetry(string $sql): array
    {
        $attempt = 1;

        while (true) {

            try {

                return $this->queryService->shortQuery($sql);

            } catch (Exception $e) {

                if ($attempt >= $this->maxRetries) {

                    throw $e;
                }

                $wait = pow(2, $attempt);

                $this->logOutput("Retry {$attempt} after {$wait} seconds...\n");

                sleep($wait);

                $attempt++;

            }

        }

    }

    protected function logOutput(string $msg): void
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            echo $msg;
        }
    }
}
