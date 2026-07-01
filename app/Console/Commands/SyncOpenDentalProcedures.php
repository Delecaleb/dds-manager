<?php

namespace App\Console\Commands;

use App\Services\Sync\ProcedureSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalProcedures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:procedures';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync procedure codes from OpenDental to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncService = app(ProcedureSyncService::class);
        $syncService->sync();
    }
}
