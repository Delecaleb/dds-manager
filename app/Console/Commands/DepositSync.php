<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sync\DepositSyncService;

class DepositSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:deposit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync deposits from opendental to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = app(DepositSyncService::class);
        $service->sync();
    }
}
