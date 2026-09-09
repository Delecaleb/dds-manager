<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SyncsForOffices;
use App\Models\SyncLog;
use App\Services\Sync\PatientSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalPatients extends Command
{
    use SyncsForOffices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:patients
                            {--fresh : Reset the sync cursor and backfill all patient records from Open Dental}
                            {--office-id= : Specific office ID to target (defaults to all active offices)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync patients from OpenDental to local database (supports optional --fresh and --office-id)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $fresh = (bool) $this->option('fresh');

        return $this->syncEachOffice('patients', function ($office) use ($fresh) {
            if ($fresh) {
                SyncLog::withoutGlobalScopes()->where('module', "office_{$office->id}:patient")->delete();
                $this->info("Reset sync cursor for office [{$office->id}]. Running full backfill...");
            }

            app(PatientSyncService::class)->forOffice($office)->sync();
        });
    }
}
