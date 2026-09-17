<?php

namespace App\Console\Commands;

use App\Jobs\PruneOfficeTable;
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
                            {table=all : Local table to check (e.g. od_procedure_logs, od_appointments, or "all")}
                            {--rolling : Recent past + upcoming window (default; see config sync.prune)}
                            {--today : Only records dated or first synced today}
                            {--current-month : Records dated this month}
                            {--initial : Alias for --current-month}
                            {--current-year : Records dated this year}
                            {--full : Every local record, regardless of date (chunked)}
                            {--start-date= : Start date for a custom range (Y-m-d)}
                            {--end-date= : End date for a custom range (Y-m-d)}
                            {--office-id= : Specific office ID (defaults to all active offices)}
                            {--queue : Queue one job per office and table instead of running here}
                            {--priority : With --queue: run ahead of regular sync jobs (for deadlines like the 08:00 snapshot)}
                            {--dry-run : Report hard-deleted records without removing them}
                            {--force : Delete even when OpenDental reports most records missing (verify the API connection first)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove local records that were hard-deleted in OpenDental (rolling window by default; logs to sync_log_prune).';

    public function handle(HardDeleteSyncService $pruner): int
    {
        $mode = $this->resolveMode();
        $startDate = $this->option('start-date');
        $endDate = $this->option('end-date');

        try {
            [$windowStart, $windowEnd] = $pruner->windowFor($mode, $startDate, $endDate);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return Command::FAILURE;
        }

        if ($windowStart !== null && $windowStart > $windowEnd) {
            $this->error("Invalid date range: start_date ({$windowStart}) cannot be after end_date ({$windowEnd}).");

            return Command::FAILURE;
        }

        $offices = $this->resolveTargetOffices($this->option('office-id'));

        if ($offices->isEmpty()) {
            $this->warn('No offices found to prune orphan data.');

            return Command::SUCCESS;
        }

        $tableArg = (string) $this->argument('table');
        $tables = $tableArg === 'all' ? $pruner->getSupportedTables() : [$tableArg];
        $rangeLabel = $windowStart === null ? 'FULL SCAN' : "{$windowStart} to {$windowEnd}";

        if ($this->option('queue')) {
            if ($this->option('dry-run') || $this->option('force')) {
                $this->error('--dry-run and --force run in this process only; drop --queue.');

                return Command::FAILURE;
            }

            foreach ($offices as $office) {
                foreach ($tables as $table) {
                    $job = PruneOfficeTable::dispatch((int) $office->id, $table, $mode, $startDate, $endDate);

                    if ($this->option('priority')) {
                        $job->onQueue(config('sync.queue.priority_name'));
                    }
                }
            }

            $this->info(($this->option('priority') ? 'Queued (priority) ' : 'Queued ').'prune jobs for '.$offices->count().' office(s) × '.count($tables)." table(s) [{$mode}: {$rangeLabel}].");

            return Command::SUCCESS;
        }

        return $this->runInline($pruner, $offices, $tables, $mode, $startDate, $endDate, $rangeLabel);
    }

    /**
     * @param  Collection<int, Office>  $offices
     * @param  list<string>  $tables
     */
    private function runInline(HardDeleteSyncService $pruner, Collection $offices, array $tables, string $mode, ?string $startDate, ?string $endDate, string $rangeLabel): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info(($dryRun ? '[DRY RUN] Checking' : 'Pruning')." orphan records for {$offices->count()} office(s) [{$mode}: {$rangeLabel}]...");

        $rows = [];
        $total = 0;
        $failed = false;

        foreach ($offices as $office) {
            $this->line("<comment>Processing Office [{$office->id}] {$office->name}...</comment>");

            foreach ($tables as $table) {
                try {
                    $result = $pruner->prune($table, $office, $mode, $startDate, $endDate, $dryRun, allowMassDelete: (bool) $this->option('force'));

                    $status = match (true) {
                        $result['skipped'] => 'Skipped (already running)',
                        $dryRun => $result['orphan_count'] > 0 ? 'Would Delete' : 'Clean',
                        default => $result['orphan_count'] > 0 ? 'Deleted' : 'Clean',
                    };

                    $total += $result['orphan_count'];
                    $rows[] = [$table, "[{$office->id}] {$office->name}", $result['range'], $result['local_count'], $result['remote_count'], $result['orphan_count'], $status];
                } catch (Throwable $e) {
                    $failed = true;
                    $this->error("Error processing table {$table} for office [{$office->id}]: {$e->getMessage()}");
                    $rows[] = [$table, "[{$office->id}] {$office->name}", $rangeLabel, 'ERR', 'ERR', 0, 'Failed'];
                }
            }
        }

        $this->table(['Table', 'Office', 'Range', 'Local Count', 'OpenDental Count', 'Orphans Found', 'Status'], $rows);

        if ($dryRun) {
            $this->warn("Dry run completed. Total orphan records found across offices: {$total}");
            $this->info('To actually remove these records, run without the --dry-run option.');
        } else {
            $this->info("Pruning completed. Total orphan records removed across offices: {$total}");
        }

        if ($failed) {
            $this->warn('Some tables failed; see errors above. Nothing was deleted from a batch that failed.');
        }

        return Command::SUCCESS;
    }

    private function resolveMode(): string
    {
        return match (true) {
            (bool) $this->option('full') => HardDeleteSyncService::MODE_FULL,
            $this->option('current-month') || $this->option('initial') => HardDeleteSyncService::MODE_CURRENT_MONTH,
            (bool) $this->option('current-year') => HardDeleteSyncService::MODE_CURRENT_YEAR,
            (bool) $this->option('today') => HardDeleteSyncService::MODE_TODAY,
            $this->option('start-date') !== null || $this->option('end-date') !== null => HardDeleteSyncService::MODE_RANGE,
            default => HardDeleteSyncService::MODE_ROLLING,
        };
    }

    /**
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

        return Office::where('is_active', true)->get()->toBase();
    }
}
