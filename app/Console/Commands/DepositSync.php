<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SyncsForOffices;
use App\Services\Sync\DepositSyncService;
use Illuminate\Console\Command;

class DepositSync extends Command
{
    use SyncsForOffices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:deposit
                            {--office-id= : Specific office ID to target (defaults to all active offices)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync deposits from OpenDental to local database (supports optional --office-id)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->syncEachOffice('deposits', function ($office) {
            app(DepositSyncService::class)->forOffice($office)->sync();
        });
    }
}
