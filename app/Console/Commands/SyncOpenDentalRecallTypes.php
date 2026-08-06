<?php

namespace App\Console\Commands;

use App\Services\Sync\RecallTypeSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalRecallTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:recall-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync OpenDental recall types to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncService = app(RecallTypeSyncService::class);
        $syncService->sync();
    }
}
