<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SyncsForOffices;
use App\Services\Sync\PayPlanChargeSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalPayPlanCharges extends Command
{
    use SyncsForOffices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:payplancharges
                            {--office-id= : Specific office ID to target (defaults to all active offices)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync OpenDental pay plan charges to local database (supports optional --office-id)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->syncEachOffice('pay plan charges', function ($office) {
            app(PayPlanChargeSyncService::class)->forOffice($office)->sync();
        });
    }
}
