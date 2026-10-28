<?php

namespace App\Console\Commands;

use App\Domain\Patient\PatientVisitDays;
use App\Domain\Support\MetricCache;
use App\Models\Office;
use Illuminate\Console\Command;

/**
 * Full rebuild of the summary tables (speed plan Phase 4), office by office.
 * Used for the first build, repairs, and the nightly safety net.
 */
class RebuildSummariesCommand extends Command
{
    protected $signature = 'summaries:rebuild {offices?* : Office ids; default all}';

    protected $description = 'Rebuild the patient_visit_days summary for one or more offices';

    public function handle(PatientVisitDays $visitDays, MetricCache $metricCache): int
    {
        $officeIds = $this->argument('offices') ?: Office::query()->orderBy('id')->pluck('id')->all();

        foreach ($officeIds as $officeId) {
            $started = microtime(true);
            $rows = $visitDays->rebuildOffice((int) $officeId);
            $metricCache->invalidateOffice((int) $officeId);

            $this->line(sprintf('office %d: %d rows in %.1fs', $officeId, $rows, microtime(true) - $started));
        }

        return self::SUCCESS;
    }
}
