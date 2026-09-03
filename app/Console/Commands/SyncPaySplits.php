<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SyncsForOffices;
use App\Services\Sync\PaySplitSyncService;
use Illuminate\Console\Command;

class SyncPaySplits extends Command
{
    use SyncsForOffices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:paysplits
                            {--office-id= : Specific office ID to target (defaults to all active offices)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync PaySplits from OpenDental to local database (supports optional --office-id)';

    /**
     * Execute the console command.
     */
    public function handle(PaySplitSyncService $service): int
    {
        return $this->syncEachOffice('PaySplits', function ($office) use ($service) {
            $service->forOffice($office)->sync();
        });
    }
}
