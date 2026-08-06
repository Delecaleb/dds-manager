<?php

namespace App\Console\Commands;

use App\Services\Sync\SyncInsplanService;
use Illuminate\Console\Command;

class SyncInsplan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:insplan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = ' Sync insplan from OpenDental to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncService = app(SyncInsplanService::class);
        $syncService->sync();
    }
}
