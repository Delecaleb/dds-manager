<?php

namespace App\Services\Sync;

use App\Models\SyncLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use RuntimeException;

/**
 * Viewing and resetting sync cursors (sync_logs) for one office.
 *
 * A reset never touches a run that is in progress: rewinding a cursor under a
 * live sync would either be overwritten by its next heartbeat or, by clearing
 * the lease, let a second run start. Resets are a single conditional UPDATE
 * against the same staleness rule SyncLease uses.
 */
class SyncCheckpointService
{
    /**
     * @return Collection<int, SyncLog>
     */
    public function forOffice(int $officeId): Collection
    {
        return SyncLog::withoutGlobalScopes()
            ->where('office_id', $officeId)
            ->orderBy('module')
            ->get()
            ->each(fn (SyncLog $log) => $log->makeHidden('run_token'));
    }

    /**
     * Reset one module's cursor.
     *
     * @throws ModelNotFoundException when the module is not a sync log of this office
     * @throws RuntimeException when that sync is currently running
     */
    public function reset(int $officeId, string $module, ?string $lastSyncedAt, int $lastPrimaryKey): SyncLog
    {
        $log = SyncLog::withoutGlobalScopes()
            ->where('office_id', $officeId)
            ->where('module', $module)
            ->firstOrFail();

        if ($this->resetQuery($officeId)->whereKey($log->getKey())->update($this->resetValues($lastSyncedAt, $lastPrimaryKey)) === 0) {
            throw new RuntimeException("Sync '{$module}' is running right now. Try again after it finishes.");
        }

        return $log->refresh()->makeHidden('run_token');
    }

    /**
     * Reset every idle module of the office; running ones are left untouched.
     *
     * @return array{reset: int, skipped_running: int}
     */
    public function resetAll(int $officeId, ?string $lastSyncedAt, int $lastPrimaryKey): array
    {
        $total = SyncLog::withoutGlobalScopes()->where('office_id', $officeId)->count();
        $reset = $this->resetQuery($officeId)->update($this->resetValues($lastSyncedAt, $lastPrimaryKey));

        return ['reset' => $reset, 'skipped_running' => $total - $reset];
    }

    private function resetQuery(int $officeId)
    {
        $staleBefore = now()->subSeconds((int) config('sync.stale_after_seconds', 600));

        return SyncLog::withoutGlobalScopes()
            ->where('office_id', $officeId)
            ->where(fn ($query) => $query->whereNull('status')
                ->orWhere('status', '!=', 'running')
                ->orWhere('updated_at', '<', $staleBefore));
    }

    /**
     * @return array<string, mixed>
     */
    private function resetValues(?string $lastSyncedAt, int $lastPrimaryKey): array
    {
        $timestamp = $lastSyncedAt !== null && $lastSyncedAt !== '' ? strtotime($lastSyncedAt) : false;

        return [
            'last_synced_at' => $timestamp !== false ? date('Y-m-d H:i:s', $timestamp) : null,
            'last_primary_key' => max(0, $lastPrimaryKey),
            'cycle_started_at' => null,
            'status' => 'idle',
            'run_token' => null,
            'last_error' => null,
        ];
    }
}
