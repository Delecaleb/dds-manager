<?php

namespace App\Console\Commands;

use App\Models\Office;
use App\Services\Sync\HardDeleteSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Throwable;

class PruneDeletedSyncedData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:prune-deleted
                            {table=all : Table to check for hard-deleted records (e.g., od_procedure_logs, od_appointments, or "all")}
                            {--today : Prune orphan records added today (incremental daily run)}
                            {--current-month : Prune orphan records added this month (initial monthly run)}
                            {--initial : Alias for --current-month}
                            {--current-year : Prune orphan records for the current year}
                            {--full : Loop through ALL existing local records and purge orphans regardless of date}
                            {--start-date= : Start date for range check (Y-m-d)}
                            {--end-date= : End date for range check (Y-m-d)}
                            {--office-id= : Specific office ID to target (defaults to all active offices)}
                            {--dry-run : Report hard-deleted records without removing them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find and remove local records that were hard-deleted from OpenDental across offices (supports --today, --current-month, --full, logs to sync_log_prune).';

    /**
     * Execute the console command.
     */
    public function handle(HardDeleteSyncService $syncDeleter): int
    {
        $tableArg = $this->argument('table');
        $isToday = (bool) $this->option('today');
        $isCurrentMonth = (bool) ($this->option('current-month') || $this->option('initial'));
        $isCurrentYear = (bool) $this->option('current-year');
        $isFull = (bool) $this->option('full');
        $officeIdOption = $this->option('office-id');
        $dryRun = (bool) $this->option('dry-run');

        $startDate = $this->option('start-date');
        $endDate = $this->option('end-date');

        $mode = 'range';

        if ($isFull) {
            $mode = 'full';
        } elseif ($isCurrentMonth) {
            $mode = 'current_month';
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->toDateString();
        } elseif ($isCurrentYear) {
            $mode = 'current_year';
            $startDate = now()->startOfYear()->toDateString();
            $endDate = now()->toDateString();
        } elseif ($isToday || ($startDate === null && $endDate === null)) {
            // Default to today (incremental daily pruning)
            $mode = 'today';
            $startDate = now()->toDateString();
            $endDate = now()->toDateString();
        } else {
            $mode = 'range';
            if ($startDate === null) {
                $startDate = now()->startOfMonth()->toDateString();
            }
            if ($endDate === null) {
                $endDate = now()->toDateString();
            }
        }

        if (! $isFull && $startDate > $endDate) {
            $this->error("Invalid date range: start_date ({$startDate}) cannot be after end_date ({$endDate}).");

            return Command::FAILURE;
        }

        $offices = $this->resolveTargetOffices($officeIdOption);
        if ($offices->isEmpty()) {
            $this->warn('No offices found to prune orphan data.');

            return Command::SUCCESS;
        }

        $tablesToProcess = $tableArg === 'all'
            ? $syncDeleter->getSupportedTables()
            : [$tableArg];

        $rangeDescription = match ($mode) {
            'full' => 'FULL SCAN',
            'current_month' => "CURRENT MONTH ({$startDate} to {$endDate})",
            'current_year' => "CURRENT YEAR ({$startDate} to {$endDate})",
            'today' => "TODAY ({$startDate})",
            default => "{$startDate} to {$endDate}",
        };

        $modeHeader = $dryRun
            ? "[DRY RUN] Checking orphan records for {$offices->count()} office(s) [{$rangeDescription}]..."
            : "Pruning orphan records for {$offices->count()} office(s) [{$rangeDescription}]...";

        $this->info($modeHeader);

        $results = [];
        $totalOrphans = 0;

        foreach ($offices as $office) {
            $this->line("<comment>Processing Office [{$office->id}] {$office->name}...</comment>");

            foreach ($tablesToProcess as $tableKey) {
                try {
                    if ($mode === 'full') {
                        $res = $syncDeleter->pruneAllRecords($tableKey, $office, $dryRun);
                    } elseif ($mode === 'today') {
                        $res = $syncDeleter->pruneToday($tableKey, $office, $dryRun);
                    } elseif ($mode === 'current_month') {
                        $res = $syncDeleter->pruneCurrentMonth($tableKey, $office, $dryRun);
                    } elseif ($mode === 'current_year') {
                        $res = $syncDeleter->pruneCurrentYear($tableKey, $office, $dryRun);
                    } else {
                        $res = $syncDeleter->pruneTable($tableKey, $startDate, $endDate, $office, $dryRun, 'range');
                    }

                    $results[] = [
                        'table' => $res['table'],
                        'office' => "[{$office->id}] {$office->name}",
                        'range' => $res['range'] ?? $rangeDescription,
                        'local_count' => $res['local_count'],
                        'remote_count' => $res['remote_count'] ?? 'N/A',
                        'orphan_count' => $res['orphan_count'],
                        'status' => $dryRun ? ($res['orphan_count'] > 0 ? 'Would Delete' : 'Clean') : ($res['orphan_count'] > 0 ? 'Deleted' : 'Clean'),
                    ];

                    $totalOrphans += $res['orphan_count'];
                } catch (Throwable $e) {
                    $this->error("Error processing table {$tableKey} for office [{$office->id}]: {$e->getMessage()}");
                    $results[] = [
                        'table' => $tableKey,
                        'office' => "[{$office->id}] {$office->name}",
                        'range' => $rangeDescription,
                        'local_count' => 'ERR',
                        'remote_count' => 'ERR',
                        'orphan_count' => 0,
                        'status' => 'Failed: '.$e->getMessage(),
                    ];
                }
            }
        }

        $this->table(
            ['Table', 'Office', 'Range', 'Local Count', 'OpenDental Count', 'Orphans Found', 'Status'],
            $results
        );

        if ($dryRun) {
            $this->warn("Dry run completed. Total orphan records found across offices: {$totalOrphans}");
            $this->info('To actually remove these records, run without the --dry-run option.');
        } else {
            $this->info("Pruning completed. Total orphan records removed across offices: {$totalOrphans}");
        }

        return Command::SUCCESS;
    }

    /**
     * Resolve target offices collection.
     *
     * @return Collection<int, Office>
     */
    protected function resolveTargetOffices(?string $officeIdOption): Collection
    {
        if ($officeIdOption !== null) {
            $office = Office::find((int) $officeIdOption);
            if (! $office) {
                $this->error("Office with ID {$officeIdOption} not found.");

                return new Collection;
            }

            return new Collection([$office]);
        }

        $activeOffices = Office::where('is_active', true)->get();

        if ($activeOffices->isEmpty()) {
            $first = Office::first();

            return $first ? new Collection([$first]) : new Collection;
        }

        return $activeOffices;
    }
}
