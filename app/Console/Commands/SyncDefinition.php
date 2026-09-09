<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SyncsForOffices;
use App\Services\Sync\SyncDefinitionsService;
use Illuminate\Console\Command;

class SyncDefinition extends Command
{
    use SyncsForOffices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:definition
                            {--office-id= : Specific office ID to target (defaults to all active offices)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync definitions from OpenDental to local database (supports optional --office-id)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->syncEachOffice('definitions', function ($office) {
            app(SyncDefinitionsService::class)->forOffice($office)->sync();
        });
    }
}
