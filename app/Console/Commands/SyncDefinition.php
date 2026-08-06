<?php

namespace App\Console\Commands;

use App\Services\Sync\SyncDefinitionsService;
use Illuminate\Console\Command;

class SyncDefinition extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:definition';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = ' Sync definitions from OpenDental to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncService = app(SyncDefinitionsService::class);
        $syncService->sync();
    }
}
