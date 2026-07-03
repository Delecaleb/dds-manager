<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\Sync\PaySplitSyncService;
class SyncPaySplits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:paysplits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(PaySplitSyncService $service)
    {
        $this->info('Syncing PaySplits...');

        $service->sync();

        $this->info('Done.');
    }
}
