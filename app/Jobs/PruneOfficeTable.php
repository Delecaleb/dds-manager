<?php

namespace App\Jobs;

use App\Models\Office;
use App\Services\Sync\HardDeleteSyncService;
use App\Services\Sync\PruneSafetyException;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Prune hard-deleted OpenDental rows for one office + table + window.
 *
 * Time-budgeted like SyncOfficeModule: a large scan saves its cursor and
 * re-queues itself. A mass-delete safety refusal fails immediately — retrying
 * would get the same answer from the same (probably misconfigured) API.
 */
class PruneOfficeTable implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /** Continuations are releases, not failures — bounded by retryUntil(). */
    public int $tries = 0;

    public int $maxExceptions;

    public int $timeout;

    public function __construct(
        public int $officeId,
        public string $table,
        public string $mode,
        public ?string $startDate = null,
        public ?string $endDate = null,
    ) {
        $this->maxExceptions = (int) config('sync.queue.max_exceptions', 3);
        $this->timeout = (int) config('sync.queue.job_timeout', 600);

        $this->onConnection(config('sync.queue.connection'));
        $this->onQueue(config('sync.queue.name'));
    }

    public function handle(HardDeleteSyncService $pruner): void
    {
        $office = Office::find($this->officeId);

        if ($office === null) {
            return;
        }

        try {
            $result = $pruner->prune(
                $this->table,
                $office,
                $this->mode,
                $this->startDate,
                $this->endDate,
                timeBudgetSeconds: (int) config('sync.queue.time_budget', 240),
            );
        } catch (PruneSafetyException $e) {
            Log::warning($e->getMessage(), ['office_id' => $this->officeId, 'table' => $this->table, 'mode' => $this->mode]);
            $this->fail($e);

            return;
        }

        if ($result['interrupted']) {
            $this->release((int) config('sync.queue.continue_delay', 5));
        }
    }

    public function uniqueId(): string
    {
        return "{$this->officeId}:{$this->table}:{$this->mode}";
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
        Log::error("Prune job failed for office #{$this->officeId} table [{$this->table}] mode [{$this->mode}].", [
            'exception' => $e,
        ]);
    }
}
