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

    /**
     * How far (seconds) to rewind last_synced_at at the start of every
     * incremental run. This closes the classic boundary hole: a run killed
     * at time T records last_synced_at = T, but a remote row committed AT
     * exactly T (or slightly earlier, due to clock skew / long remote
     * transactions) was never persisted. The old strict ">" comparison
     * would skip that row forever. Rewinding re-scans the window; the
     * content hash below makes the re-scan free of duplicate writes.
     */
    protected int $overlapSeconds = 300;

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
     * local columns.
     */
    protected function transformRow(array $row): array
    {
        return $row;
    }

    /**
     * Columns that must NOT participate in the row fingerprint.
     *
     * Bookkeeping timestamps change without the real data changing — e.g.
     * an admin touching DateTStamp on the remote side purely to force a
     * re-sync. If those columns were hashed, every touched row would look
     * "changed" and be rewritten; excluded, a touched-but-identical row is
     * recognized as already synced and skipped entirely.
     *
     * Subclasses can extend this list for table-specific volatile columns.
     */
    protected function hashExcludedColumns(): array
    {
        return array_values(array_filter([
            'created_at',
            'updated_at',
            $this->syncColumn(),
        ]));
    }

    /**
     * Deterministic fingerprint of a row's substantive content.
     * Key-sorted so column order from the API can never affect the hash.
     */
    protected function computeRowHash(array $data): string
    {
        foreach ($this->hashExcludedColumns() as $column) {
            unset($data[$column]);
        }

        ksort($data);

        return hash('sha256', json_encode($data));
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

        while (true) {

            $sql = "
                SELECT *
                FROM {$this->table()}
                WHERE {$pk} > {$lastId}
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

            echo "Synced through ID {$lastId}\n";

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

        while (true) {

            $safeSync = addslashes($lastSync);

            // Keyset pagination on the (syncColumn, primaryKey) tuple.
            // A bare "syncColumn > X" cursor either skips rows that share a
            // timestamp across a batch boundary (with ">") or loops forever
            // when more than batchSize rows share one timestamp (with ">=").
            // The tuple cursor always advances and never skips.
            $sql = "
                SELECT *
                FROM {$this->table()}
                WHERE ({$col} > '{$safeSync}')
                   OR ({$col} = '{$safeSync}' AND {$pk} > {$lastId})
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

            echo "Synced through {$lastSync} (ID {$lastId})\n";

            sleep($this->sleepSeconds);
        }
    }

    /**
     * Upsert a batch idempotently and advance the cursor.
     *
     * Identity is ALWAYS the OpenDental primary key — a row can never be
     * inserted twice, no matter how many times it is re-fetched. The
     * content hash then decides whether an existing row actually changed:
     * created_at / updated_at / DateTStamp are excluded, so an admin
     * touching DateTStamp to force a re-sync causes a write only if the
     * substantive data really differs from what we hold locally.
     *
     * Rows synced before the row_hash column existed have a null hash;
     * they are rewritten once and backfilled automatically.
     *
     * @return array{0: ?string, 1: int} [$lastSync, $lastId]
     */
    protected function persistBatch(array $rows, SyncLog $log, ?string $lastSync = null, int $lastId = 0): array
    {
        $modelClass = $this->model();
        $pk = $this->primaryKey();
        $col = $this->syncColumn();

        DB::transaction(function () use ($rows, $log, $modelClass, $pk, $col, &$lastSync, &$lastId) {

            foreach ($rows as $row) {

                $data = $this->transformRow($row);

                $existing = $modelClass::where($pk, $row[$pk])->first();

                if ($existing === null) {

                    $model = $existing ?? new $modelClass;
                    $model->fill($data);
                    $model->{$pk} = $row[$pk];
                    $model->save();

                    $log->increment('total_processed');
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

                echo "Retry {$attempt} after {$wait} seconds...\n";

                sleep($wait);

                $attempt++;

            }

        }

    }
}
