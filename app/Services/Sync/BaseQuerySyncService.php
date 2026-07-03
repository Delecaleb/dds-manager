<?php

namespace App\Services\Sync;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\SyncLog;
use App\Services\OpenDental\QueryService;

abstract class BaseQuerySyncService
{
    protected int $batchSize = 1000;

    protected int $maxRetries = 5;

    protected int $sleepSeconds = 1;

    public function __construct(
        protected QueryService $queryService
    ) {
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
        return $this->table();
    }

    public function sync(): void
    {
        $log = SyncLog::firstOrCreate(
            ['module' => $this->module()],
            [
                'status' => 'idle',
                'total_processed' => 0
            ]
        );

        $log->update([
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
                            $this->primaryKey() => $row[$this->primaryKey()]
                        ],

                        $row

                    );

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

                echo "Retry {$attempt} after {$wait} seconds...\n";

                sleep($wait);

                $attempt++;

            }

        }

    }

}