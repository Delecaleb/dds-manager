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

    protected bool $throttle = false;

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

    protected function transformRow(array $row): array
    {
        return $row;
    }

    protected function normalizeDateTime($value): ?string
    {
        if (! $value) {
            return null;
        }

        $value = str_replace('T', ' ', trim($value));

        if (
            $value < '1000-01-01 00:00:00' ||
            $value > '9999-12-31 23:59:59'
        ) {
            return null;
        }

        return date('Y-m-d H:i:s', strtotime($value));
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
            'finished_at' => null,
            'last_error' => null,
        ]);

        $incremental = $log->last_primary_key !== null && $this->syncColumn() !== null;
        echo "[{$this->module()}] ".($incremental ? 'incremental' : 'initial')." sync started...\n";

        try {

            if ($this->syncColumn()) {
                $this->incrementalSync($log);
            } else {
                $this->initialSync($log);
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

    protected function initialSync(SyncLog $log): void
    {
        $lastId = $log->last_primary_key ?? 0;

        while (true) {

            $rows = $this->executeWithRetry("
                SELECT *
                FROM {$this->table()}
                WHERE {$this->primaryKey()} > {$lastId}
                ORDER BY {$this->primaryKey()}
                LIMIT {$this->batchSize}
            ");

            if (empty($rows)) {
                break;
            }

            $this->bulkUpsert($rows);

            $lastId = end($rows)[$this->primaryKey()];

            $log->update([
                'last_primary_key' => $lastId,
            ]);

            $log->increment('total_processed', count($rows));

            echo "Synced {$log->total_processed} rows (Last ID {$lastId})\n";

            if ($this->throttle) {
                sleep($this->sleepSeconds);
            }
        }
    }

    protected function incrementalSync(SyncLog $log): void
    {
        $lastDate = $log->last_synced_at ?? '1900-01-01 00:00:00';
        $lastId = $log->last_primary_key ?? 0;

        while (true) {

            $rows = $this->executeWithRetry("
            SELECT *
            FROM {$this->table()}
            WHERE
                {$this->syncColumn()} > '{$lastDate}'
            OR (
                {$this->syncColumn()} = '{$lastDate}'
                AND {$this->primaryKey()} > {$lastId}
            )
            ORDER BY
                {$this->syncColumn()},
                {$this->primaryKey()}
            LIMIT {$this->batchSize}
        ");

            if (empty($rows)) {
                break;
            }

            $this->bulkUpsert($rows);

            $lastRow = end($rows);

            $lastDate = $lastRow[$this->syncColumn()];
            $lastId = $lastRow[$this->primaryKey()];

            $log->update([
                'last_synced_at' => $lastDate,
                'last_primary_key' => $lastId,
            ]);

            $log->increment('total_processed', count($rows));

            echo sprintf(
                "Processed %d rows | Checkpoint: %s | PK: %s\n",
                count($rows),
                $lastDate,
                $lastId
            );

            if ($this->throttle) {
                sleep($this->sleepSeconds);
            }
        }
    }

    protected function bulkUpsert(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $model = $this->model();

        $records = array_map(
            fn ($row) => $this->transformRow($row),
            $rows
        );

        $updateColumns = array_values(array_diff(
            array_keys($records[0]),
            [$this->primaryKey()]
        ));

        DB::transaction(function () use ($model, $records, $updateColumns) {
            $model::upsert(
                $records,
                [$this->primaryKey()],
                $updateColumns
            );
        }, 3);
    }

    protected function executeWithRetry(string $sql): array
    {
        $attempt = 1;

        while (true) {

            try {

                return $this->queryService->shortQuery($sql);

            } catch (Exception $e) {

                if (
                    ! str_contains($e->getMessage(), 'Deadlock')
                    &&
                    ! str_contains($e->getMessage(), 'Lock wait timeout')
                    &&
                    ! str_contains($e->getMessage(), 'server has gone away')
                ) {
                    throw $e;
                }

                if ($attempt >= $this->maxRetries) {
                    throw $e;
                }

                $wait = 2 ** $attempt;

                echo "Retry {$attempt} after {$wait}s\n";

                sleep($wait);

                $attempt++;
            }
        }
    }
}
