<?php

namespace App\Console\Commands;

use App\Services\Sync\HistAppointmentSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalHistAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:histappointments
                            {--start-date= : Start date for sync window (Y-m-d)}
                            {--end-date= : End date for sync window (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync historical appointments from OpenDental to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncService = app(HistAppointmentSyncService::class);
        $start = $this->option('start-date');
        $end = $this->option('end-date');

        if ($start || $end) {
            $syncService->withDateWindow($start, $end);
        }

        $syncService->sync();
    }
}
