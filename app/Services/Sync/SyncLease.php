<?php

namespace App\Services\Sync;

use App\Models\SyncLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Exclusive, crash-safe ownership of one sync_logs row (one office + module).
 *
 * Acquisition is a single conditional UPDATE, so two processes can never both
 * win — no Redis or cache lock required. A lease is kept alive by heartbeats;
 * if the owning process is killed (common on shared hosting) the heartbeat
 * goes stale and the next run takes over. Every write is guarded by
 * run_token, so a process that lost its lease can never clobber the new
 * owner's cursor.
 */
final class SyncLease
{
    private function __construct(
        private readonly SyncLog $log,
        private readonly string $token,
    ) {}

    /**
     * Try to take the lease. Returns null when another live run holds it.
     */
    public static function acquire(string $module, int $officeId, ?int $staleAfterSeconds = null): ?self
    {
        $staleAfterSeconds ??= (int) config('sync.stale_after_seconds', 600);

        $log = SyncLog::withoutGlobalScopes()->firstOrCreate(
            ['module' => $module],
            [
                'office_id' => $officeId,
                'status' => 'idle',
                'total_processed' => 0,
            ]
        );

        $token = (string) Str::uuid();
        $now = now();

        $claimed = SyncLog::withoutGlobalScopes()
            ->whereKey($log->getKey())
            ->where(function ($query) use ($now, $staleAfterSeconds) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'running')
                    ->orWhereNull('updated_at')
                    ->orWhere('updated_at', '<', $now->copy()->subSeconds($staleAfterSeconds));
            })
            ->update([
                'office_id' => $officeId,
                'status' => 'running',
                'run_token' => $token,
                'started_at' => $now,
                'last_error' => null,
                'updated_at' => $now,
            ]);

        if ($claimed === 0) {
            return null;
        }

        return new self($log->refresh(), $token);
    }

    public function log(): SyncLog
    {
        return $this->log;
    }

    /**
     * Persist progress and prove the process is alive.
     *
     * @param  array<string, mixed>  $attributes
     *
     * @throws SyncLeaseLostException when another run has taken over
     */
    public function heartbeat(array $attributes = []): void
    {
        $this->guardedUpdate($attributes + ['updated_at' => now()]);
    }

    /**
     * Finish successfully and release the lease.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function complete(array $attributes = []): void
    {
        $this->guardedUpdate($attributes + [
            'status' => 'completed',
            'run_token' => null,
            'finished_at' => now(),
            'retry_count' => 0,
            'updated_at' => now(),
        ]);
    }

    /**
     * Stop early with the cursor saved (time budget reached); the next run resumes.
     */
    public function pause(): void
    {
        $this->guardedUpdate([
            'status' => 'paused',
            'run_token' => null,
            'updated_at' => now(),
        ]);
    }

    /**
     * Record the failure and release the lease. Never throws, so the original
     * exception is what propagates.
     */
    public function fail(Throwable $e): void
    {
        try {
            SyncLog::withoutGlobalScopes()
                ->whereKey($this->log->getKey())
                ->where('run_token', $this->token)
                ->update([
                    'status' => 'failed',
                    'run_token' => null,
                    'last_error' => mb_substr($e->getMessage(), 0, 65000),
                    // retry_count is an unsigned TINYINT — cap it instead of overflowing.
                    'retry_count' => DB::raw('CASE WHEN retry_count >= 255 THEN 255 ELSE COALESCE(retry_count, 0) + 1 END'),
                    'updated_at' => now(),
                ]);
        } catch (Throwable) {
            // The database itself may be the failure; the stale heartbeat frees the lease.
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function guardedUpdate(array $attributes): void
    {
        $affected = SyncLog::withoutGlobalScopes()
            ->whereKey($this->log->getKey())
            ->where('run_token', $this->token)
            ->update($attributes);

        // MySQL reports 0 affected rows when values are unchanged (e.g. two
        // heartbeats in the same second), so confirm ownership explicitly.
        if ($affected === 0 && ! $this->stillOwned()) {
            throw new SyncLeaseLostException("Sync lease for [{$this->log->module}] was taken over by another run.");
        }
    }

    private function stillOwned(): bool
    {
        return SyncLog::withoutGlobalScopes()
            ->whereKey($this->log->getKey())
            ->where('run_token', $this->token)
            ->exists();
    }
}
