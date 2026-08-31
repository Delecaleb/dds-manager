<?php

namespace App\Console\Commands;

use App\Services\Sync\StatementSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalStatements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:statements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync statements from OpenDental to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncService = app(StatementSyncService::class);
        $syncService->sync();
    }
}
