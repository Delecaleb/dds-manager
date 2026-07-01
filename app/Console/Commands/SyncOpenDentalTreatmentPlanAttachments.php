<?php

namespace App\Console\Commands;

use App\Services\Sync\TreatmentPlanAttachmentSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalTreatmentPlanAttachments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:treatment-plan-attachments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync treatment plan attachments from OpenDental to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncService = app(TreatmentPlanAttachmentSyncService::class);
        $syncService->sync();
    }
}
