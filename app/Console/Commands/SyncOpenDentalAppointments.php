<?php

namespace App\Console\Commands;

use App\Services\Sync\AppointmentSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:appointments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync appointments from OpenDental to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncService = app(AppointmentSyncService::class);
        $syncService->sync();
    }
}
