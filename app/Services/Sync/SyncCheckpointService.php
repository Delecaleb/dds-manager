<?php

namespace App\Services\Sync;

use App\Models\SyncLog;
use Illuminate\Database\Eloquent\Collection;

/**
 * Read-only view of sync cursors (sync_logs) for one office.
 *
 * Cursors are intentionally not editable from the UI: moving one backwards
 * re-downloads whole tables, and moving one forwards silently skips data. To
 * re-pull a period, create a date-range sync request — it uses its own cursor
 * and never touches the scheduled sync's position.
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
}
