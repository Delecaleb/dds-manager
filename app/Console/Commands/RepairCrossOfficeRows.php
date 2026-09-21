<?php

namespace App\Console\Commands;

use App\Models\Office;
use App\Services\Sync\Explorer\CrossOfficeCollisionDetector;
use App\Services\Sync\Explorer\RowRepairService;
use App\Services\Sync\OpenDentalTableCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Finds rows overwritten with another office's data and re-reads each one from
 * its own office's OpenDental (that office's API connection).
 *
 * Only local rows change. Records an office's OpenDental no longer has are
 * reported, never deleted — review them in the OD Data Explorer.
 */
class RepairCrossOfficeRows extends Command
{
    protected $signature = 'sync:repair-cross-office
                            {table=all : OpenDental or local table name (e.g. appointment, od_claim_procs), or "all"}
                            {--since= : Only rows written locally on/after this date (Y-m-d; default 60 days ago)}
                            {--office-id= : Only repair this office}
                            {--dry-run : Report what would be repaired without changing anything}';

    protected $description = 'Repair local rows that were overwritten with another office\'s data by re-fetching them from their own OpenDental.';

    public function handle(OpenDentalTableCatalog $catalog, CrossOfficeCollisionDetector $detector, RowRepairService $repair): int
    {
        $since = (string) ($this->option('since') ?: now()->subDays(60)->toDateString());
        $officeIds = $this->option('office-id') !== null ? [(int) $this->option('office-id')] : [];
        $dryRun = (bool) $this->option('dry-run');

        $tableArg = (string) $this->argument('table');

        if ($tableArg === 'all') {
            $tables = array_filter($catalog->all(), fn ($table) => $table->isRepairable());
        } else {
            $table = $catalog->resolve($tableArg);

            if ($table === null || ! $table->isRepairable()) {
                $this->error("Unknown or non-repairable table '{$tableArg}'.");

                return Command::FAILURE;
            }

            $tables = [$table];
        }

        $offices = Office::whereIn('id', $officeIds ?: Office::pluck('id'))->get()->keyBy('id');
        $this->info(($dryRun ? '[DRY RUN] ' : '')."Scanning rows written since {$since}…");

        $failed = false;
        $summary = [];

        foreach ($tables as $table) {
            if ($detector->detectableColumn($table) === null) {
                $summary[] = [$table->localTable, '-', '-', '-', 'no change timestamp; cannot detect'];

                continue;
            }

            foreach ($detector->suspects($table, $since, $officeIds) as $officeId => $keys) {
                $office = $offices->get($officeId);

                if ($office === null) {
                    $summary[] = [$table->localTable, $officeId, count($keys), '-', 'office not found'];

                    continue;
                }

                if ($dryRun) {
                    $summary[] = [$table->localTable, $office->name, count($keys), '-', 'would repair'];

                    continue;
                }

                $synced = 0;
                $notFound = [];

                try {
                    foreach (array_chunk($keys, RowRepairService::MAX_KEYS) as $chunk) {
                        $result = $repair->syncFromOpenDental($table, $office, $chunk);
                        $synced += $result['synced'];
                        $notFound = array_merge($notFound, $result['not_found']);
                    }
                } catch (Throwable $e) {
                    $failed = true;
                    $summary[] = [$table->localTable, $office->name, count($keys), $synced, 'FAILED: '.$e->getMessage()];
                    Log::error('Cross-office repair failed', ['table' => $table->localTable, 'office_id' => $officeId, 'error' => $e->getMessage()]);

                    continue;
                }

                $note = $notFound === [] ? 'ok' : count($notFound).' not in OpenDental (review in Explorer)';
                $summary[] = [$table->localTable, $office->name, count($keys), $synced, $note];

                Log::info('Cross-office repair', [
                    'table' => $table->localTable,
                    'office_id' => $officeId,
                    'suspects' => count($keys),
                    'synced' => $synced,
                    'not_found' => $notFound,
                ]);
            }
        }

        $this->table(['Table', 'Office', 'Suspect rows', 'Re-synced', 'Result'], $summary);

        return $failed ? Command::FAILURE : Command::SUCCESS;
    }
}
