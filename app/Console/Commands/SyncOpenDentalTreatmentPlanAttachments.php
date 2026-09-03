<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SyncsForOffices;
use App\Services\Sync\TreatmentPlanAttachmentSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalTreatmentPlanAttachments extends Command
{
    use SyncsForOffices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:treatment-plan-attachments
                            {--office-id= : Specific office ID to target (defaults to all active offices)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync treatment plan attachments from OpenDental to local database (supports optional --office-id)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->syncEachOffice('treatment plan attachments', function ($office) {
            app(TreatmentPlanAttachmentSyncService::class)->forOffice($office)->sync();
        });
    }
}
