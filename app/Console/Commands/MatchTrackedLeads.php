<?php

namespace App\Console\Commands;

use App\Domain\Marketing\TrackedLeadPatientMatcher;
use Illuminate\Console\Command;

/**
 * Credit leads from websites shared by several locations to the location where they are an
 * OpenDental patient. Scheduled in routes/console.php; safe to run by hand at any time.
 */
class MatchTrackedLeads extends Command
{
    protected $signature = 'marketing:match-leads
                            {--days='.TrackedLeadPatientMatcher::WINDOW_DAYS.' : Only leads identified within this many days}';

    protected $description = 'Credit shared-website leads to the location where they are an OpenDental patient';

    public function handle(TrackedLeadPatientMatcher $matcher): int
    {
        $credited = $matcher->run(max(1, (int) $this->option('days')));

        $this->info("{$credited} lead(s) credited to a location from OpenDental.");

        return Command::SUCCESS;
    }
}
