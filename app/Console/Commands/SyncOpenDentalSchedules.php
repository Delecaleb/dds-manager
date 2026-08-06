<?php

namespace App\Console\Commands;

use App\Services\Sync\ScheduleSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync OpenDental schedules to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncService = app(ScheduleSyncService::class);
        $syncService->sync();
    }
}
