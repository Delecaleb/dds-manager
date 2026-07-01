<?php

namespace App\Console\Commands;

use App\Services\Sync\AdjustmentSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalAdjustments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:adjustments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync adjustments from OpenDental to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncService = app(AdjustmentSyncService::class);
        $syncService->sync();
    }
}
