<?php

namespace App\Console\Commands;

use App\Services\Sync\PatientSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalPatients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:patients';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync patients from OpenDental to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //call sync service
        $syncService = app(PatientSyncService::class);
        $syncService->sync();
    }
}
