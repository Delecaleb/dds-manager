<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SyncsForOffices;
use App\Services\Sync\SyncInsplanService;
use Illuminate\Console\Command;

class SyncInsplan extends Command
{
    use SyncsForOffices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:insplan
                            {--office-id= : Specific office ID to target (defaults to all active offices)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync insplan from OpenDental to local database (supports optional --office-id)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->syncEachOffice('insplan', function ($office) {
            app(SyncInsplanService::class)->forOffice($office)->sync();
        });
    }
}
