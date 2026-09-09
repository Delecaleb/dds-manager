<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SyncsForOffices;
use App\Services\Sync\ScheduleSyncService;
use Illuminate\Console\Command;

class SyncOpenDentalSchedules extends Command
{
    use SyncsForOffices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:schedules
                            {--office-id= : Specific office ID to target (defaults to all active offices)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync OpenDental schedules to local database (supports optional --office-id)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->syncEachOffice('schedules', function ($office) {
            app(ScheduleSyncService::class)->forOffice($office)->sync();
        });
    }
}
