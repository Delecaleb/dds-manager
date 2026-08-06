<?php

namespace App\Services\Sync;

<<<<<<< Updated upstream
=======
use App\Models\Office;
use App\Models\SyncLog;
use App\Services\OpenDental\QueryService;
>>>>>>> Stashed changes
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\SyncLog;
use App\Services\OpenDental\QueryService;

abstract class BaseQuerySyncService
{
    protected ?Office $office = null;

    protected int $batchSize = 1000;

    protected int $maxRetries = 5;

    protected int $sleepSeconds = 1;

<<<<<<< Updated upstream
=======
    protected int $overlapSeconds = 300;

    protected ?string $windowStart = null;

    protected ?string $windowEnd = null;

>>>>>>> Stashed changes
    public function __construct(
        protected QueryService $queryService
    ) {
    }

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

    protected function module(): string
    {
<<<<<<< Updated upstream
        return $this->table();
=======
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
>>>>>>> Stashed changes
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
                'total_processed' => 0
            ]
        );

        $log->update([
            'office_id' => $office->id ?? 1,
            'status' => 'running',
            'started_at' => now(),
            'last_error' => null
        ]);

        try {

            if ($log->last_primary_key === null) {

                $this->runInitialSync($log);

            } else {

                $this->runIncrementalSync($log);

            }

            $log->update([
                'status' => 'completed',
                'finished_at' => now(),
                'retry_count' => 0
            ]);

        } catch (Exception $e) {

            $log->increment('retry_count');

            $log->update([
                'status' => 'failed',
                'last_error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    protected function runInitialSync(SyncLog $log): void
    {
        $lastId = $log->last_primary_key ?? 0;

        while (true) {

            $sql = "
                SELECT *
                FROM {$this->table()}
                WHERE {$this->primaryKey()} > {$lastId}
                ORDER BY {$this->primaryKey()}
                LIMIT {$this->batchSize}
            ";

            $rows = $this->executeWithRetry($sql);

            if (empty($rows)) {
                break;
            }

            DB::transaction(function () use ($rows, $log, &$lastId) {

                $model = $this->model();

                foreach ($rows as $row) {

                    $model::updateOrCreate(

                        [
                            $this->primaryKey() => $row[$this->primaryKey()]
                        ],

                        $row

                    );

                    $lastId = $row[$this->primaryKey()];

                    $log->increment('total_processed');
                }

            });

            $log->update([
                'last_primary_key' => $lastId
            ]);

            $this->logOutput("Synced through ID {$lastId}\n");

            sleep($this->sleepSeconds);
        }
    }

    protected function runIncrementalSync(SyncLog $log): void
    {
        $lastSync = $log->last_synced_at;

        while (true) {

            $sql = "

                SELECT *

                FROM {$this->table()}

                WHERE {$this->syncColumn()} > '{$lastSync}'

                ORDER BY {$this->syncColumn()},{$this->primaryKey()}

                LIMIT {$this->batchSize}

            ";

            $rows = $this->executeWithRetry($sql);

            if (empty($rows)) {
                break;
            }

            DB::transaction(function () use ($rows, $log, &$lastSync) {

<<<<<<< Updated upstream
                $model = $this->model();

                foreach ($rows as $row) {
=======
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
>>>>>>> Stashed changes

                    $model::updateOrCreate(

                        [
                            $this->primaryKey() => $row[$this->primaryKey()]
                        ],

                        $row

<<<<<<< Updated upstream
                    );
=======
                $data['office_id'] = $officeId;

                // Safeguard non-note string attributes from exceeding MySQL VARCHAR limits
                foreach ($data as $key => $val) {
                    if (is_string($val) && strlen($val) > 255 && ! str_contains(strtolower($key), 'note')) {
                        $data[$key] = mb_substr($val, 0, 255);
                    }
                }

                $existing = $modelClass::where('office_id', $officeId)->where($pk, $row[$pk])->first();
>>>>>>> Stashed changes

                    if (isset($row[$this->syncColumn()])) {
                        $lastSync = $row[$this->syncColumn()];
                    }

                    $log->increment('total_processed');
                }

            });

            $log->update([
                'last_synced_at' => $lastSync
            ]);

            echo "Synced through {$lastSync}\n";

            sleep($this->sleepSeconds);

        }
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

<<<<<<< Updated upstream
}
=======
    protected function logOutput(string $msg): void
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            echo $msg;
        }
    }
}
>>>>>>> Stashed changes
