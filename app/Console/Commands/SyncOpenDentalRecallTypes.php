<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SyncsForOffices;
use App\Services\Sync\RecallTypeSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalRecallTypes extends Command
{
    use SyncsForOffices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:recall-types
                            {--office-id= : Specific office ID to target (defaults to all active offices)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync OpenDental recall types to local database (supports optional --office-id)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->syncEachOffice('recall types', function ($office) {
            app(RecallTypeSyncService::class)->forOffice($office)->sync();
        });
    }
}
