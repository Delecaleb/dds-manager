<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SyncsForOffices;
use App\Services\Sync\PatientBalanceSyncService;
use Illuminate\Console\Command;

class SyncPatientBalance extends Command
{
    use SyncsForOffices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:patient-balance
                            {--office-id= : Specific office ID to target (defaults to all active offices)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync guarantor-level patient balance rollups (supports optional --office-id)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->syncEachOffice('patient balance', function ($office) {
            app(PatientBalanceSyncService::class)->forOffice($office)->sync();
        });
    }
}
