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
    protected $signature = 'sync:appointments
                            {--start-date= : Start date for sync window (Y-m-d)}
                            {--end-date= : End date for sync window (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync appointments from OpenDental to local database (supports optional --start-date and --end-date)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncService = app(AppointmentSyncService::class);
        $start = $this->option('start-date');
        $end = $this->option('end-date');

        if ($start || $end) {
            $syncService->withDateWindow($start, $end);
        }

        $syncService->sync();
    }
}
