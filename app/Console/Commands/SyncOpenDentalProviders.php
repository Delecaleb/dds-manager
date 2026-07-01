<?php

namespace App\Console\Commands;

use App\Services\Sync\ProviderSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalProviders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:providers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync providers from OpenDental to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncService = app(ProviderSyncService::class);
        $syncService->sync();
    }
}
