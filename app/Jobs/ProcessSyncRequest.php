<?php

namespace App\Jobs;

use App\Models\SyncRequest;
use App\Services\Sync\SyncRequestRunner;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Process one date-range SyncRequest on the sync queue. A request that hits
 * its time budget re-queues itself and resumes from the saved cursor.
 */
class ProcessSyncRequest implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /** Continuations are releases, not failures — bounded by retryUntil(). */
    public int $tries = 0;

    public int $maxExceptions = 3;

    public int $timeout;

    public function __construct(public int $syncRequestId)
    {
        $this->timeout = (int) config('sync.queue.job_timeout', 600);

        $this->onConnection(config('sync.queue.connection'));
        $this->onQueue(config('sync.queue.name'));
    }

    public function handle(SyncRequestRunner $runner): void
    {
        $request = SyncRequest::find($this->syncRequestId);

        if ($request === null) {
            return;
        }

        $outcome = $runner->run($request, (int) config('sync.queue.time_budget', 240));

        if ($outcome === SyncRequestRunner::OUTCOME_CONTINUING) {
            $this->release((int) config('sync.queue.continue_delay', 5));
        }
    }

    public function uniqueId(): string
    {
        return (string) $this->syncRequestId;
    }

    public function uniqueFor(): int
    {
        return (int) config('sync.queue.unique_for', 3600);
    }

    public function retryUntil(): DateTimeInterface
    {
        return now()->addHours((int) config('sync.queue.retry_until_hours', 24));
    }
}
