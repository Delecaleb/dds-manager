<?php

namespace App\Services\Sync;

use App\Models\SyncLog;
use App\Services\OpenDental\QueryService;
use Exception;
use Illuminate\Support\Facades\DB;

abstract class BaseQuerySyncService
{
    protected int $batchSize = 1000;

    protected int $maxRetries = 5;

    protected int $sleepSeconds = 1;

    /** Rows processed during the current sync() invocation (for reporting). */
    protected int $processedThisRun = 0;

    public function __construct(
        protected QueryService $queryService
    ) {}

    abstract protected function table(): string;

    abstract protected function model(): string;

    abstract protected function primaryKey(): string;

    protected function syncColumn(): ?string
    {
        return null;
    }

    protected function module(): string
    {
        return $this->table();
    }

    /**
     * Hook to massage a raw OpenDental API row before it is persisted
     * locally. Base implementation is a pass-through; subclasses override
     * to normalize values (e.g. datetimes) so they land cleanly in typed
     * local columns. Keeping this in the base — rather than each caller —
     * means every current and future sync gets a single, consistent place
     * to sanitize source data.
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

    public function sync(): void
    {
        $log = SyncLog::firstOrCreate(
            ['module' => $this->module()],
            [
                'status' => 'idle',
                'total_processed' => 0,
            ]
        );

        $log->update([
            'status' => 'running',
            'started_at' => now(),
            'last_error' => null,
        ]);

        $incremental = $log->last_primary_key !== null && $this->syncColumn() !== null;
        echo "[{$this->module()}] ".($incremental ? 'incremental' : 'initial')." sync started...\n";

        try {

            if ($incremental) {

                $this->runIncrementalSync($log);

            } else {

                $this->runInitialSync($log);

            }

            $log->update([
                'status' => 'completed',
                'finished_at' => now(),
                'retry_count' => 0,
            ]);

            $summary = $this->processedThisRun === 0
                ? 'already up to date (0 new rows)'
                : "{$this->processedThisRun} row(s) processed";
            echo "[{$this->module()}] sync complete — {$summary}\n";

        } catch (Exception $e) {

            $log->increment('retry_count');

            $log->update([
                'status' => 'failed',
                'last_error' => $e->getMessage(),
            ]);

            echo "[{$this->module()}] sync FAILED after {$this->processedThisRun} row(s): {$e->getMessage()}\n";

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
                            $this->primaryKey() => $row[$this->primaryKey()],
                        ],

                        $this->transformRow($row)

                    );

                    $lastId = $row[$this->primaryKey()];

                    $log->increment('total_processed');
                    $this->processedThisRun++;
                }

            });

            $log->update([
                'last_primary_key' => $lastId,
            ]);

            echo "Synced through ID {$lastId}\n";

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

                $model = $this->model();

                foreach ($rows as $row) {

                    $model::updateOrCreate(

                        [
                            $this->primaryKey() => $row[$this->primaryKey()],
                        ],

                        $this->transformRow($row)

                    );

                    if (isset($row[$this->syncColumn()])) {
                        $lastSync = $row[$this->syncColumn()];
                    }

                    $log->increment('total_processed');
                    $this->processedThisRun++;
                }

            });

            $log->update([
                'last_synced_at' => $lastSync,
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

                echo "Retry {$attempt} after {$wait} seconds...\n";

                sleep($wait);

                $attempt++;

            }

        }

    }
}
