<?php

namespace App\Console\Commands;

use App\Services\Sync\RecallSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalRecalls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:recalls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync OpenDental recalls to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncService = app(RecallSyncService::class);
        $syncService->sync();
    }
}
