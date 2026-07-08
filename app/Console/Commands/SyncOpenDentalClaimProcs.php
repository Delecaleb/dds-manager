<?php

namespace App\Console\Commands;

use App\Services\Sync\ClaimProcSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalClaimProcs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:claimprocs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync claim procedures from OpenDental to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncService = app(ClaimProcSyncService::class);
        $syncService->sync();
    }
}
