<?php

namespace App\Console\Commands;

use App\Services\Sync\PayPlanChargeSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalPayPlanCharges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:payplancharges';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync OpenDental pay plan charges to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncService = app(PayPlanChargeSyncService::class);
        $syncService->sync();
    }
}
