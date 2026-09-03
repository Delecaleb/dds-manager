<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SyncsForOffices;
use App\Services\Sync\ClaimPaymentSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalClaimPayments extends Command
{
    use SyncsForOffices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:claimpayments
                            {--office-id= : Specific office ID to target (defaults to all active offices)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync OpenDental claim payments to local database (supports optional --office-id)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->syncEachOffice('claim payments', function ($office) {
            app(ClaimPaymentSyncService::class)->forOffice($office)->sync();
        });
    }
}
