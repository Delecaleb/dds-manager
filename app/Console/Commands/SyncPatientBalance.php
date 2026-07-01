<?php

namespace App\Console\Commands;

use App\Services\Sync\PatientBalanceSyncService;
use Illuminate\Console\Command;

class SyncPatientBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:patient-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncService = app(PatientBalanceSyncService::class);
        $syncService->sync();
    }
}
