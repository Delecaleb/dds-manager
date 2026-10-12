<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SyncsForOffices;
use App\Services\Sync\OperatorySyncService;
use Illuminate\Console\Command;

class SyncOpenDentalOperatories extends Command
{
    use SyncsForOffices;

    protected $signature = 'sync:operatories
                            {--office-id= : Specific office ID to target (defaults to all active offices)}';

    protected $description = 'Sync operatories (calendar chairs) from OpenDental to local database (supports optional --office-id)';

    public function handle(): int
    {
        return $this->syncEachOffice('operatories', function ($office) {
            app(OperatorySyncService::class)->forOffice($office)->sync();
        });
    }
}
