<?php

namespace App\Console\Commands;

use App\Services\Sync\SyncCarrierService;
use Illuminate\Console\Command;

class SyncCarrierMap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:carriers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = ' Sync carrier from OpenDental to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncService = app(SyncCarrierService::class);
        $syncService->sync();
    }
}
