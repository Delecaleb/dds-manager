<?php

namespace App\Console\Commands;

use App\Models\SyncLog;
use App\Services\Sync\PatientSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalPatients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:patients
                            {--fresh : Reset the sync cursor and backfill all patient records from Open Dental}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync patients from OpenDental to local database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('fresh')) {
            SyncLog::where('module', 'like', '%:patient')->delete();
            $this->info('Reset sync cursor for patients. Running full backfill...');
        }

        // call sync service
        $syncService = app(PatientSyncService::class);
        $syncService->sync();

        $this->info('Patient sync complete.');

        return self::SUCCESS;
    }
}
