<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SyncsForOffices;
use App\Jobs\SyncOfficeModule;
use App\Services\Sync\SyncReportService;
use Illuminate\Console\Command;
use InvalidArgumentException;

/**
 * Queue one SyncOfficeModule job per (module, office). This is what the
 * scheduler runs; it only inserts rows into `jobs` and returns immediately.
 * Cron-started workers (see routes/console.php) do the actual syncing.
 */
class DispatchSyncJobs extends Command
{
    use SyncsForOffices;

    protected $signature = 'sync:dispatch
                            {modules* : Module keys from the sync registry (e.g. appointments procedurelogs)}
                            {--office-id= : Specific office ID to target (defaults to all active offices)}';

    protected $description = 'Queue per-office sync jobs for the given modules';

    public function handle(SyncReportService $registry): int
    {
        $modules = array_values(array_unique((array) $this->argument('modules')));

        foreach ($modules as $module) {
            try {
                $registry->serviceClassFor($module);
            } catch (InvalidArgumentException $e) {
                $this->error($e->getMessage());

                return Command::INVALID;
            }
        }

        $offices = $this->getTargetOffices();

        if ($offices->isEmpty()) {
            $this->warn('No offices found to sync.');

            return Command::SUCCESS;
        }

        // Module-first ordering: every office gets fresh appointments before
        // any office moves on to the next module.
        $queued = 0;

        foreach ($modules as $module) {
            foreach ($offices as $office) {
                SyncOfficeModule::dispatch((int) $office->id, $module);
                $queued++;
            }
        }

        $this->info("Dispatched {$queued} sync job(s) (already-queued duplicates are skipped): ".implode(', ', $modules).' × '.$offices->count().' office(s).');

        return Command::SUCCESS;
    }
}
