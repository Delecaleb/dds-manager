<?php

namespace App\Console\Commands;

use App\Models\Office;
use App\Services\Sync\HardDeleteSyncService;
use Illuminate\Console\Command;

class PruneDeletedSyncedData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:prune-deleted
                            {table=all : Table to check for hard-deleted records (e.g., od_procedure_logs, od_appointments, or "all")}
                            {--full : Loop through ALL existing local records and purge orphans regardless of date}
                            {--current-year : Prune orphan records for the current year (Jan 1 of current year to today)}
                            {--start-date= : Start date for range check (Y-m-d). Defaults to start of current month}
                            {--end-date= : End date for range check (Y-m-d). Defaults to today}
                            {--office-id= : Office ID to target. Defaults to active or default office}
                            {--dry-run : Report hard-deleted records without removing them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find and remove local records that were hard-deleted from OpenDental (supports --full or --current-year).';

    /**
     * Execute the console command.
     */
    public function handle(HardDeleteSyncService $syncDeleter): int
    {
        $tableArg = $this->argument('table');
        $isFull = (bool) $this->option('full');
        $isCurrentYear = (bool) $this->option('current-year');
        $officeIdOption = $this->option('office-id');
        $dryRun = (bool) $this->option('dry-run');

        $startDate = $this->option('start-date');
        $endDate = $this->option('end-date');

        if ($isCurrentYear) {
            $startDate = now()->startOfYear()->toDateString();
            $endDate = now()->toDateString();
        } elseif ($startDate === null) {
            $startDate = now()->startOfMonth()->toDateString();
        }

        if ($endDate === null) {
            $endDate = now()->toDateString();
        }

        if (! $isFull && $startDate > $endDate) {
            $this->error("Invalid date range: start_date ({$startDate}) cannot be after end_date ({$endDate}).");

            return Command::FAILURE;
        }

        $office = null;
        if ($officeIdOption !== null) {
            $office = Office::find((int) $officeIdOption);
            if (! $office) {
                $this->error("Office with ID {$officeIdOption} not found.");

                return Command::FAILURE;
            }
        }

        $tablesToProcess = $tableArg === 'all'
            ? $syncDeleter->getSupportedTables()
            : [$tableArg];

        if ($isFull) {
            $modeHeader = $dryRun ? '[DRY RUN] Checking ALL existing local records...' : 'Pruning ALL existing local records...';
        } else {
            $modeHeader = $dryRun ? "[DRY RUN] Checking records for range ({$startDate} to {$endDate})..." : "Pruning records for range ({$startDate} to {$endDate})...";
        }

        $this->info($modeHeader);

        $results = [];
        $totalOrphans = 0;

        foreach ($tablesToProcess as $tableKey) {
            try {
                if ($isFull) {
                    $res = $syncDeleter->pruneAllRecords($tableKey, $office, $dryRun);
                    $results[] = [
                        'table' => $res['table'],
                        'office_id' => $res['office_id'],
                        'range' => 'FULL SCAN',
                        'local_count' => $res['local_count'],
                        'remote_count' => 'N/A',
                        'orphan_count' => $res['orphan_count'],
                        'status' => $dryRun ? ($res['orphan_count'] > 0 ? 'Would Delete' : 'Clean') : ($res['orphan_count'] > 0 ? 'Deleted' : 'Clean'),
                    ];
                } else {
                    $res = $syncDeleter->pruneTable($tableKey, $startDate, $endDate, $office, $dryRun);
                    $results[] = [
                        'table' => $res['table'],
                        'office_id' => $res['office_id'],
                        'range' => "{$startDate} to {$endDate}",
                        'local_count' => $res['local_count'],
                        'remote_count' => $res['remote_count'],
                        'orphan_count' => $res['orphan_count'],
                        'status' => $dryRun ? ($res['orphan_count'] > 0 ? 'Would Delete' : 'Clean') : ($res['orphan_count'] > 0 ? 'Deleted' : 'Clean'),
                    ];
                }

                $totalOrphans += $res['orphan_count'];
            } catch (\Exception $e) {
                $this->error("Error processing table {$tableKey}: {$e->getMessage()}");
            }
        }

        $this->table(
            ['Table', 'Office ID', 'Range', 'Local Count', 'OpenDental Count', 'Hard-Deleted Found', 'Status'],
            $results
        );

        if ($dryRun) {
            $this->warn("Dry run completed. Total hard-deleted records found across tables: {$totalOrphans}");
            $this->info('To actually remove these records, run without the --dry-run option.');
        } else {
            $this->info("Pruning completed. Total hard-deleted records removed: {$totalOrphans}");
        }

        return Command::SUCCESS;
    }
}
