<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SyncsForOffices;
use App\Services\Sync\AppointmentSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalAppointments extends Command
{
    use SyncsForOffices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:appointments
                            {--start-date= : Start date for sync window (Y-m-d)}
                            {--end-date= : End date for sync window (Y-m-d)}
                            {--office-id= : Specific office ID to target (defaults to all active offices)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync appointments from OpenDental to local database (supports optional --start-date, --end-date, and --office-id)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $start = $this->option('start-date');
        $end = $this->option('end-date');

        return $this->syncEachOffice('appointments', function ($office) use ($start, $end) {
            $syncService = app(AppointmentSyncService::class)->forOffice($office);

            if ($start || $end) {
                $syncService->withDateWindow($start, $end);
            }

            $syncService->sync();
        });
    }
}
