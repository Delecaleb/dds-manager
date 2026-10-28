<?php

namespace App\Jobs;

use App\Domain\Patient\PatientVisitDays;
use App\Domain\Support\MetricCache;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Full rebuild of one office's patient_visit_days rows. Queued when a change cannot be
 * applied per patient (procedure codes changed), and nightly as a safety net. Until it
 * finishes, a stale office reads the raw tables. Unique only until it starts, so a change
 * that lands mid-rebuild queues one more.
 */
class RebuildPatientVisitDays implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public int $timeout;

    public function __construct(public int $officeId)
    {
        $this->timeout = (int) config('sync.queue.job_timeout', 600);

        $this->onConnection(config('sync.queue.connection'));
        $this->onQueue(config('sync.queue.name'));
    }

    public function handle(PatientVisitDays $visitDays, MetricCache $metricCache): void
    {
        $visitDays->rebuildOffice($this->officeId);
        $metricCache->invalidateOffice($this->officeId);
    }

    public function uniqueId(): string
    {
        return (string) $this->officeId;
    }
}
