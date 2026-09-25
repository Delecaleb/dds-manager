<?php

namespace App\Jobs;

use App\Models\Office;
use App\Services\Sync\SyncLeaseLostException;
use App\Services\Sync\SyncReportService;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sync one module for one office. One job per (office, module) keeps offices
 * isolated: a slow or unreachable OpenDental server only delays its own jobs.
 *
 * Built for the database queue on shared hosting:
 *  - the service stops at a time budget and the job re-queues itself, so no
 *    single run is long enough to be killed by the host;
 *  - a killed run resumes from the cursor saved in sync_logs;
 *  - SyncLease guarantees no two runs of the same office+module overlap,
 *    including manual CLI / web-triggered syncs.
 */
class SyncOfficeModule implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /** Continuations are releases, not failures — bounded by retryUntil(). */
    public int $tries = 0;

    public int $maxExceptions;

    public int $timeout;

    public function __construct(
        public int $officeId,
        public string $module,
        public ?string $windowStart = null,
        public ?string $windowEnd = null,
    ) {
        $this->maxExceptions = (int) config('sync.queue.max_exceptions', 3);
        $this->timeout = (int) config('sync.queue.job_timeout', 600);

        $this->onConnection(config('sync.queue.connection'));
        $this->onQueue(config('sync.queue.name'));
    }

    public function handle(SyncReportService $modules): void
    {
        $office = Office::find($this->officeId);

        if ($office === null) {
            Log::warning("Sync job dropped: office #{$this->officeId} no longer exists.", ['module' => $this->module]);

            return;
        }

        $service = app($modules->serviceClassFor($this->module))->forOffice($office);

        if ($this->windowStart !== null || $this->windowEnd !== null) {
            $service->withDateWindow($this->windowStart, $this->windowEnd);
        }

        if (method_exists($service, 'withTimeBudget')) {
            $service->withTimeBudget((int) config('sync.queue.time_budget', 240));
        }

        try {
            $service->sync();
        } catch (SyncLeaseLostException $e) {
            // Another run took over after this one looked dead; it owns the work now.
            Log::warning($e->getMessage(), ['office_id' => $this->officeId, 'module' => $this->module]);

            return;
        }

        if (method_exists($service, 'wasInterrupted') && $service->wasInterrupted()) {
            $this->release((int) config('sync.queue.continue_delay', 5));
        }
    }

    public function uniqueId(): string
    {
        return "{$this->officeId}:{$this->module}:".($this->windowStart ?? 'min').'..'.($this->windowEnd ?? 'max');
    }

    public function uniqueFor(): int
    {
        return (int) config('sync.queue.unique_for', 3600);
    }

    public function retryUntil(): DateTimeInterface
    {
        return now()->addHours((int) config('sync.queue.retry_until_hours', 24));
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return array_map('intval', (array) config('sync.queue.backoff', [60, 300, 900]));
    }

    public function failed(?Throwable $e): void
    {
        Log::error("Sync job failed permanently for office #{$this->officeId} module [{$this->module}].", [
            'office_id' => $this->officeId,
            'module' => $this->module,
            'window' => [$this->windowStart, $this->windowEnd],
            'exception' => $e,
        ]);
    }

    /**
     * @return list<string>
     */
    public function tags(): array
    {
        return ['sync', "office:{$this->officeId}", "module:{$this->module}"];
    }
}
