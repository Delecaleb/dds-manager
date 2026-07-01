<?php

namespace App\Console\Commands;

use App\Services\Sync\ProcedureLogSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalProcedureLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:procedurelogs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync procedure logs from OpenDental to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncService = app(ProcedureLogSyncService::class);
        $syncService->sync();
    }
}
