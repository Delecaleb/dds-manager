<?php

namespace App\Console\Commands;

use App\Services\Sync\ClaimPaymentSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalClaimPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:claimpayments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync OpenDental claim payments to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncService = app(ClaimPaymentSyncService::class);
        $syncService->sync();
    }
}
