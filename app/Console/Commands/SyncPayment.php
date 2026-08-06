<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sync\PaymentSyncService;
class SyncPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync payments from OpenDental to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncService = app(PaymentSyncService::class);
        $syncService->sync();
    }
}
