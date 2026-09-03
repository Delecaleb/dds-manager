<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SyncsForOffices;
use App\Services\Sync\ProcedureLogSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalProcedureLogs extends Command
{
    use SyncsForOffices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:procedurelogs
                            {--office-id= : Specific office ID to target (defaults to all active offices)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync procedure logs from OpenDental to local database (supports optional --office-id)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->syncEachOffice('procedure logs', function ($office) {
            app(ProcedureLogSyncService::class)->forOffice($office)->sync();
        });
    }
}
