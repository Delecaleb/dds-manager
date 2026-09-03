<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SyncsForOffices;
use App\Services\Sync\TreatmentPlanSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalTreatmentPlans extends Command
{
    use SyncsForOffices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:treatment-plans
                            {--office-id= : Specific office ID to target (defaults to all active offices)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync treatment plans from OpenDental to local database (supports optional --office-id)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->syncEachOffice('treatment plans', function ($office) {
            app(TreatmentPlanSyncService::class)->forOffice($office)->sync();
        });
    }
}
