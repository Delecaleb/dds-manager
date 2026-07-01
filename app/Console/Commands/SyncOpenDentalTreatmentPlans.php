<?php

namespace App\Console\Commands;

use App\Services\Sync\TreatmentPlanSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalTreatmentPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:treatment-plans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync treatment plans from OpenDental to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncService = app(TreatmentPlanSyncService::class);
        $syncService->sync();
    }
}
