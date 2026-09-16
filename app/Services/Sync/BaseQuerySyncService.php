<?php

namespace App\Services\Sync;

use App\Models\Office;
use App\Models\SyncLog;
use App\Services\OpenDental\QueryService;
use Exception;
use Illuminate\Database\DetectsConcurrencyErrors;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

abstract class BaseQuerySyncService
{
    use DetectsConcurrencyErrors;

    protected ?Office $office = null;

    protected int $batchSize = 1000;

    protected int $sleepSeconds = 1;

    protected int $overlapSeconds = 300;

    protected ?string $windowStart = null;

    protected ?string $windowEnd = null;

    protected ?int $timeBudgetSeconds = null;

    protected float $runStartedAt = 0.0;

    protected bool $interrupted = false;

    protected bool $skipped = false;

    protected ?SyncLease $lease = null;

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
     * Public description of the table this service syncs. This is the single
     * source for OpenDental table metadata (see OpenDentalTableCatalog), so
     * tools like the OD Data Explorer never keep their own copies.
     *
     * @return array{od_table: string, local_table: string, primary_key: string, date_column: ?string, sync_column: ?string}
     */
    public function describe(): array
    {
        $modelClass = $this->model();

        return [
            'od_table' => $this->table(),
            'local_table' => (new $modelClass)->getTable(),
            'primary_key' => $this->primaryKey(),
            'date_column' => $this->dateColumn(),
            'sync_column' => $this->syncColumn(),
        ];
    }

    /**
     * Re-fetch specific rows from OpenDental by primary key and upsert them
     * through the normal persistence path (transformRow, truncation, upsert).
     *
     * The cursor is never touched, so this is safe alongside a running sync.
     * Rows are always taken from OpenDental — never from caller-supplied data.
     *
     * @param  list<int|string>  $primaryKeys
     * @return array{requested: int, synced: int, not_found: list<int>}
     */
    public function syncRowsByPrimaryKeys(array $primaryKeys): array
    {
        $keys = array_values(array_unique(array_filter(array_map('intval', $primaryKeys), fn (int $key) => $key > 0)));
        $office = $this->getOffice();
        $this->queryService->forOffice($office);

        $log = SyncLog::withoutGlobalScopes()->firstOrCreate(
            ['module' => $this->module()],
            ['office_id' => (int) ($office->id ?? 1), 'status' => 'idle', 'total_processed' => 0]
        );

        $pk = $this->primaryKey();
        $found = [];

        foreach (array_chunk($keys, $this->batchSize) as $chunk) {
            $rows = $this->executeWithRetry(
                "SELECT * FROM {$this->table()} WHERE {$pk} IN (".implode(',', $chunk).')'
            );

            if ($rows === []) {
                continue;
            }

            $this->persistBatch($rows, $log);

            foreach ($rows as $row) {
                $found[(int) ($row[$pk] ?? 0)] = true;
            }
        }

        return [
            'requested' => count($keys),
            'synced' => count($found),
            'not_found' => array_values(array_filter($keys, fn (int $key) => ! isset($found[$key]))),
        ];
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

    /**
     * Stop cleanly after this many seconds (cursor saved, lease paused) so a
     * queued job can re-queue itself instead of being killed by the host.
     * Null — the default for CLI/web callers — means run to completion.
     */
    public function withTimeBudget(?int $seconds): static
    {
        $this->timeBudgetSeconds = $seconds !== null && $seconds > 0 ? $seconds : null;

        return $this;
    }

    /**
     * True when the last sync() stopped at the time budget with work remaining.
     */
    public function wasInterrupted(): bool
    {
        return $this->interrupted;
    }

    /**
     * True when the last sync() did nothing because another live run holds the lease.
     */
    public function wasSkipped(): bool
    {
        return $this->skipped;
    }

    public function sync(): void
    {
        $this->interrupted = false;
        $this->skipped = false;
        $this->runStartedAt = microtime(true);

        $office = $this->getOffice();
        $this->queryService->forOffice($office);

        // Atomic: two processes can never both hold the same office+module.
        $lease = SyncLease::acquire($this->module(), (int) ($office->id ?? 1));

        if ($lease === null) {
            $this->skipped = true;
            $this->logOutput("Sync is already running for {$this->module()}. Skipping duplicate process.\n");

            return;
        }

        $this->lease = $lease;
        $log = $lease->log();

        try {

            // Initial mode until a full pass has completed at least once
            // (last_synced_at is only stamped on initial completion), or
            // always when the table has no incremental sync column.
            if ($this->syncColumn() === null || $log->last_synced_at === null) {

                // A full pass may span several time-budgeted runs, so its
                // start is persisted rather than taken from this run.
                $cycleStartedAt = $log->cycle_started_at
                    ? (string) $log->cycle_started_at
                    : now()->format('Y-m-d H:i:s');

                if ($log->cycle_started_at === null) {
                    $lease->heartbeat(['cycle_started_at' => $cycleStartedAt]);
                }

                $this->runInitialSync($log);

                if ($this->interrupted) {
                    $lease->pause();

                    return;
                }

                // Watermark for the first incremental run. Anything that
                // changed remotely while the initial sync was running is
                // covered by overlapSeconds + the hash-skip on re-scan.
                $completion = ['cycle_started_at' => null];

                if ($this->syncColumn() !== null) {
                    $completion['last_synced_at'] = $cycleStartedAt;
                }

                $lease->complete($completion);

            } else {

                $this->runIncrementalSync($log);

                $this->interrupted ? $lease->pause() : $lease->complete();

            }

        } catch (SyncLeaseLostException $e) {

            // Another run owns the row now; leave it untouched.
            throw $e;
        } catch (Throwable $e) {

            $lease->fail($e);

            throw $e;
        } finally {

            $this->lease = null;

        }
    }

    /**
     * Save cursor progress through the lease (aborts if the lease was lost).
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function heartbeat(array $attributes = []): void
    {
        $this->lease?->heartbeat($attributes);
    }

    /**
     * Whether the time budget is spent and the batch loop should stop.
     */
    protected function timeBudgetExhausted(): bool
    {
        return $this->timeBudgetSeconds !== null
            && (microtime(true) - $this->runStartedAt) >= $this->timeBudgetSeconds;
    }

    /**
     * Cache table columns to avoid repetitive schema reflection.
     *
     * @var array<string, array<string, bool>>
     */
    protected static array $tableColumnsCache = [];

    protected function runInitialSync(SyncLog $log): void
    {
        $pk = $this->primaryKey();
        $lastId = (int) ($log->last_primary_key ?? 0);
        $window = $this->windowClause();

        while (true) {
            $t0 = microtime(true);

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

            $fetchTime = round(microtime(true) - $t0, 2);
            $t1 = microtime(true);

            [, $lastId] = $this->persistBatch($rows, $log);

            $persistTime = round(microtime(true) - $t1, 2);

            $this->heartbeat([
                'last_primary_key' => $lastId,
            ]);

            $this->logOutput(sprintf(
                "[%s] Synced %d records through ID %d (API: %ss, DB: %ss)\n",
                $this->table(),
                count($rows),
                $lastId,
                $fetchTime,
                $persistTime
            ));

            // A short page means the table is exhausted — no need to come back.
            if (count($rows) >= $this->batchSize && $this->timeBudgetExhausted()) {
                $this->interrupted = true;

                return;
            }

            if ($this->sleepSeconds > 0) {
                usleep(50000); // 50ms light throttle
            }
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
            $t0 = microtime(true);
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

            $fetchTime = round(microtime(true) - $t0, 2);
            $t1 = microtime(true);

            [$lastSync, $lastId] = $this->persistBatch($rows, $log, $lastSync, $lastId);

            $persistTime = round(microtime(true) - $t1, 2);

            // Persist both halves of the cursor so a kill mid-run resumes
            // exactly where it left off (minus the overlap window).
            $this->heartbeat([
                'last_synced_at' => $lastSync,
                'last_primary_key' => $lastId,
            ]);

            $this->logOutput(sprintf(
                "[%s] Synced %d records through %s (ID %d) (API: %ss, DB: %ss)\n",
                $this->table(),
                count($rows),
                $lastSync,
                $lastId,
                $fetchTime,
                $persistTime
            ));

            if (count($rows) >= $this->batchSize && $this->timeBudgetExhausted()) {
                $this->interrupted = true;

                return;
            }

            if ($this->sleepSeconds > 0) {
                usleep(50000); // 50ms light throttle
            }
        }
    }

    /**
     * Persist a batch of rows idempotently and advance the cursor.
     *
     * Identity is ALWAYS the OpenDental primary key scoped by office_id.
     * Uses bulk upsert (INSERT ... ON DUPLICATE KEY UPDATE) for extreme performance.
     *
     * @return array{0: ?string, 1: int} [$lastSync, $lastId]
     */
    protected function persistBatch(array $rows, SyncLog $log, ?string $lastSync = null, int $lastId = 0): array
    {
        if (empty($rows)) {
            return [$lastSync, $lastId];
        }

        $modelClass = $this->model();
        $pk = $this->primaryKey();
        $col = $this->syncColumn();
        $officeId = $this->getOffice()->id ?? 1;

        $model = new $modelClass;
        $tableName = $model->getTable();

        // Cache column listing to avoid repeated schema reflections
        if (! isset(static::$tableColumnsCache[$tableName])) {
            static::$tableColumnsCache[$tableName] = array_flip(Schema::getColumnListing($tableName));
        }
        $validColumns = static::$tableColumnsCache[$tableName];
        $hasCreatedAt = isset($validColumns['created_at']);
        $hasUpdatedAt = isset($validColumns['updated_at']);
        $nowString = now()->format('Y-m-d H:i:s');

        $preparedRows = [];
        $updateKeys = [];

        foreach ($rows as $row) {
            $data = $this->transformRow($row);

            // Strip local-only auto-generated / transient fields
            unset($data['id'], $data['row_hash']);

            $data['office_id'] = $officeId;
            $data[$pk] = $row[$pk];

            if ($hasCreatedAt && ! isset($data['created_at'])) {
                $data['created_at'] = $nowString;
            }
            if ($hasUpdatedAt) {
                $data['updated_at'] = $nowString;
            }

            // Safeguard non-note string attributes from exceeding MySQL VARCHAR limits
            $cleanRow = [];
            foreach ($data as $key => $val) {
                if (! isset($validColumns[$key])) {
                    continue;
                }

                if (is_string($val) && strlen($val) > 255 && ! str_contains(strtolower($key), 'note')) {
                    $val = mb_substr($val, 0, 255);
                }

                $cleanRow[$key] = $val;

                if ($key !== 'id' && $key !== 'office_id' && $key !== $pk && $key !== 'created_at') {
                    $updateKeys[$key] = true;
                }
            }

            // Deduplicate by primary key within the batch to prevent unique constraint violations
            $preparedRows[$cleanRow[$pk]] = $cleanRow;
            $lastId = (int) $row[$pk];

            if ($col !== null && isset($row[$col])) {
                $lastSync = $this->normalizeDateTime($row[$col]) ?? $lastSync;
            }
        }

        if (! empty($preparedRows)) {
            $rowsToUpsert = array_values($preparedRows);
            $updateColumns = array_keys($updateKeys);

            try {
                // High-performance single multi-row UPSERT query
                $this->retryOnLockContention(
                    fn () => DB::table($tableName)->upsert($rowsToUpsert, ['office_id', $pk], $updateColumns)
                );
            } catch (QueryException $e) {
                // Lock contention that outlived its retries is a real failure;
                // the queue backoff retries the whole batch later.
                if ($this->causedByConcurrencyError($e)) {
                    throw $e;
                }

                // Row-level fallback for data a multi-row upsert rejects.
                // Logged so a recurring data problem is visible, not silent.
                Log::warning("Bulk upsert failed for {$this->module()}; falling back to per-row writes.", [
                    'table' => $tableName,
                    'rows' => count($rowsToUpsert),
                    'error' => $e->getMessage(),
                ]);

                $this->retryOnLockContention(fn () => DB::transaction(function () use ($tableName, $rowsToUpsert, $pk, $officeId) {
                    foreach ($rowsToUpsert as $cleanRow) {
                        $matchCond = [
                            'office_id' => $officeId,
                            $pk => $cleanRow[$pk],
                        ];
                        $updateData = $cleanRow;
                        unset($updateData['office_id'], $updateData[$pk]);

                        DB::table($tableName)->updateOrInsert($matchCond, $updateData);
                    }
                }));
            }

            $log->increment('total_processed', count($rowsToUpsert));
        }

        return [$lastSync, $lastId];
    }

    /**
     * Run a write, retrying MySQL deadlocks / lock wait timeouts with jitter.
     * Offices share tables, so concurrent upserts can briefly contend.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    protected function retryOnLockContention(callable $callback): mixed
    {
        $maxAttempts = max(1, (int) config('sync.db_lock_attempts', 3));

        for ($attempt = 1; ; $attempt++) {
            try {
                return $callback();
            } catch (QueryException $e) {
                if ($attempt >= $maxAttempts || ! $this->causedByConcurrencyError($e)) {
                    throw $e;
                }

                usleep(random_int(100, 400) * 1000 * $attempt);
            }
        }
    }

    /**
     * Call the OpenDental API with a few quick retries for transient failures.
     * Longer outages are left to the queue backoff so a worker is never held
     * for minutes by one unreachable office.
     */
    protected function executeWithRetry(string $sql): array
    {
        $maxAttempts = max(1, (int) config('sync.api_max_attempts', 3));

        for ($attempt = 1; ; $attempt++) {

            try {

                return $this->queryService->shortQuery($sql);

            } catch (Exception $e) {

                if ($attempt >= $maxAttempts || ! $this->isRetryableApiError($e)) {
                    throw $e;
                }

                $wait = 2 ** $attempt;

                $this->logOutput("Retry {$attempt} after {$wait} seconds...\n");

                // Keep the lease alive across a slow retry cycle.
                $this->heartbeat();

                sleep($wait);

            }

        }
    }

    /**
     * Client errors (bad SQL, bad credentials) will fail identically on retry;
     * only timeouts, throttling and server errors are worth another attempt.
     */
    protected function isRetryableApiError(Exception $e): bool
    {
        if ($e instanceof RequestException && $e->response !== null) {
            $status = $e->response->status();

            return $status >= 500 || in_array($status, [408, 425, 429], true);
        }

        return true;
    }

    protected function logOutput(string $msg): void
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            echo $msg;
        }
    }
}
