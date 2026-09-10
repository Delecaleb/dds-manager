<?php

namespace App\Console\Commands;

use App\Models\Office;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Throwable;

class ResyncMonthData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:resync-month
                            {--month= : Month to resync in YYYY-MM format (defaults to current month)}
                            {--office-id= : Target specific office ID (defaults to all active offices)}
                            {--buffer-days=7 : Days before start of month to reset cursor to catch multi-month checks and claims}
                            {--financial : Resync only financial & collections tables (fastest for revenue reconciliation)}
                            {--operations : Resync only appointments, historical appointments, and schedules}
                            {--clinical : Resync only treatment plans, attachments, and recalls}
                            {--only= : Comma-separated list of sync commands (e.g. payment,paysplits,claimpayments)}
                            {--dry-run : Preview target offices, tables, and reset dates without running}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset sync cursors and sequentially resync OpenDental datasets for the specified month with timeout protection.';

    /**
     * Available sync command catalog.
     *
     * @var array<string, array{label: string, category: string, table: string, has_sync_col: bool}>
     */
    protected array $allCommands = [
        // ── Financial & Collections ──
        'sync:procedurelogs' => ['label' => 'Procedure Logs', 'category' => 'financial', 'table' => 'procedurelog', 'has_sync_col' => true],
        'sync:adjustments' => ['label' => 'Adjustments', 'category' => 'financial', 'table' => 'adjustment', 'has_sync_col' => true],
        'sync:claimprocs' => ['label' => 'Claim Procedures', 'category' => 'financial', 'table' => 'claimproc', 'has_sync_col' => true],
        'sync:payment' => ['label' => 'Payments', 'category' => 'financial', 'table' => 'payment', 'has_sync_col' => true],
        'sync:paysplits' => ['label' => 'Pay Splits', 'category' => 'financial', 'table' => 'paysplit', 'has_sync_col' => true],
        'sync:claimpayments' => ['label' => 'Claim Payments & Checks', 'category' => 'financial', 'table' => 'claimpayment', 'has_sync_col' => true],
        'sync:deposit' => ['label' => 'Deposits', 'category' => 'financial', 'table' => 'deposit', 'has_sync_col' => true],
        'sync:payplancharges' => ['label' => 'Pay Plan Charges', 'category' => 'financial', 'table' => 'payplancharge', 'has_sync_col' => true],

        // ── Operations & Schedules ──
        'sync:appointments' => ['label' => 'Appointments', 'category' => 'operations', 'table' => 'appointment', 'has_sync_col' => true],
        'sync:histappointments' => ['label' => 'Historical Appointments', 'category' => 'operations', 'table' => 'appointment', 'has_sync_col' => true],
        'sync:schedules' => ['label' => 'Schedules', 'category' => 'operations', 'table' => 'schedule', 'has_sync_col' => true],

        // ── Clinical & Treatment ──
        'sync:treatment-plans' => ['label' => 'Treatment Plans', 'category' => 'clinical', 'table' => 'treatplan', 'has_sync_col' => true],
        'sync:treatment-plan-attachments' => ['label' => 'Treatment Plan Attachments', 'category' => 'clinical', 'table' => 'treatplanattach', 'has_sync_col' => false],
        'sync:recalls' => ['label' => 'Recalls', 'category' => 'clinical', 'table' => 'recall', 'has_sync_col' => true],
        'sync:recall-types' => ['label' => 'Recall Types', 'category' => 'clinical', 'table' => 'recalltype', 'has_sync_col' => false],

        // ── Master / Catalog ──
        'sync:patients' => ['label' => 'Patients', 'category' => 'catalog', 'table' => 'patient', 'has_sync_col' => true],
        'sync:providers' => ['label' => 'Providers', 'category' => 'catalog', 'table' => 'provider', 'has_sync_col' => false],
        'sync:procedures' => ['label' => 'Procedure Codes', 'category' => 'catalog', 'table' => 'procedurecode', 'has_sync_col' => false],
        'sync:carriers' => ['label' => 'Insurance Carriers', 'category' => 'catalog', 'table' => 'carrier', 'has_sync_col' => false],
        'sync:insplan' => ['label' => 'Insurance Plans', 'category' => 'catalog', 'table' => 'insplan', 'has_sync_col' => false],
        'sync:definition' => ['label' => 'Definitions & Categories', 'category' => 'catalog', 'table' => 'definition', 'has_sync_col' => false],
        'sync:statements' => ['label' => 'Statements', 'category' => 'catalog', 'table' => 'statement', 'has_sync_col' => true],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        $monthInput = $this->option('month') ?: now()->format('Y-m');
        $bufferDays = (int) ($this->option('buffer-days') ?? 7);
        $officeIdOption = $this->option('office-id');
        $dryRun = (bool) $this->option('dry-run');

        try {
            $targetMonth = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        } catch (Throwable) {
            $this->error("Invalid month format '{$monthInput}'. Please use YYYY-MM format (e.g. 2026-09).");

            return Command::FAILURE;
        }

        $cursorResetDate = $targetMonth->copy()->subDays($bufferDays)->startOfDay()->toDateTimeString();
        $monthStart = $targetMonth->format('Y-m-d');
        $monthEnd = $targetMonth->copy()->endOfMonth()->format('Y-m-d');

        $offices = Office::where('is_active', true)
            ->when($officeIdOption, fn ($q) => $q->where('id', (int) $officeIdOption))
            ->orderBy('id')
            ->get();

        if ($offices->isEmpty()) {
            $this->warn('No active offices found matching criteria.');

            return Command::SUCCESS;
        }

        // Determine which commands to run
        $targetCommands = $this->resolveTargetCommands();

        $this->info('=================================================================');
        $this->info("      OPENDENTAL MONTH DATA RESYNC: {$targetMonth->format('F Y')} ({$monthStart} to {$monthEnd})");
        $this->info("      Cursor Reset Watermark: {$cursorResetDate}");
        $this->info("      Target Offices: {$offices->count()} office(s)");
        $this->info('      Selected Datasets: '.count($targetCommands).' table(s)');
        $this->info('=================================================================');

        if ($dryRun) {
            $this->warn("\n[DRY RUN] Target Offices:");
            foreach ($offices as $office) {
                $this->line(" - Office [{$office->id}] {$office->name}");
            }
            $this->warn("\n[DRY RUN] Selected Sync Commands (in sequence):");
            foreach ($targetCommands as $cmd => $meta) {
                $resetNote = $meta['has_sync_col'] ? "Resets cursor to {$cursorResetDate}" : 'Preserves last primary key (catalog)';
                $this->line(" - {$cmd} ({$meta['label']}) [{$meta['category']}] -> {$resetNote}");
            }

            return Command::SUCCESS;
        }

        $globalStart = microtime(true);
        $summary = [];

        foreach ($offices as $office) {
            $officeStart = microtime(true);
            $this->line("\n<comment>=================================================================</comment>");
            $this->line("<comment>Processing Office [{$office->id}] {$office->name}...</comment>");
            $this->line('<comment>=================================================================</comment>');

            // 1. Smartly reset cursors for the selected tables with incremental support
            $modulesToReset = [];
            foreach ($targetCommands as $meta) {
                if ($meta['has_sync_col']) {
                    $modulesToReset[] = "office_{$office->id}:{$meta['table']}";
                }
            }

            if (! empty($modulesToReset)) {
                $resetCount = DB::table('sync_logs')
                    ->where('office_id', $office->id)
                    ->whereIn('module', $modulesToReset)
                    ->update([
                        'last_synced_at' => $cursorResetDate,
                        'last_primary_key' => 0,
                        'status' => 'idle',
                    ]);

                $this->info("Reset {$resetCount} incremental cursor(s) to {$cursorResetDate} for Office [{$office->id}].");
            }

            // 2. Sequentially run sync commands for this office
            $officeErrors = 0;
            foreach ($targetCommands as $command => $meta) {
                $cmdStart = microtime(true);
                $this->line("--> Syncing {$meta['label']} ({$command})...");

                try {
                    $exitCode = Artisan::call($command, [
                        '--office-id' => $office->id,
                    ], $this->output);

                    $elapsed = round(microtime(true) - $cmdStart, 2);
                    if ($exitCode !== Command::SUCCESS) {
                        $this->warn("    [!] {$command} exited with code {$exitCode} in {$elapsed}s.");
                        $officeErrors++;
                    }
                } catch (Throwable $e) {
                    $elapsed = round(microtime(true) - $cmdStart, 2);
                    $this->error("    [X] Error running {$command} for Office [{$office->id}] ({$elapsed}s): {$e->getMessage()}");
                    $officeErrors++;
                }
            }

            $officeElapsed = round(microtime(true) - $officeStart, 2);
            $summary[] = [
                'Office' => "[{$office->id}] {$office->name}",
                'Status' => $officeErrors === 0 ? 'SUCCESS' : "FINISHED ({$officeErrors} errors)",
                'Duration' => "{$officeElapsed}s",
            ];
        }

        $totalElapsed = round(microtime(true) - $globalStart, 2);
        $this->info("\n=================================================================");
        $this->info("   RESYNC COMPLETED in {$totalElapsed}s for {$targetMonth->format('F Y')}");
        $this->info('=================================================================');
        $this->table(['Office', 'Status', 'Duration'], $summary);

        return Command::SUCCESS;
    }

    /**
     * Resolve target sync commands based on user options.
     *
     * @return array<string, array{label: string, category: string, table: string, has_sync_col: bool}>
     */
    protected function resolveTargetCommands(): array
    {
        if ($this->option('financial')) {
            return array_filter($this->allCommands, fn ($m) => $m['category'] === 'financial');
        }

        if ($this->option('operations')) {
            return array_filter($this->allCommands, fn ($m) => $m['category'] === 'operations');
        }

        if ($this->option('clinical')) {
            return array_filter($this->allCommands, fn ($m) => $m['category'] === 'clinical');
        }

        $only = $this->option('only');
        if ($only) {
            $onlyLower = strtolower(trim($only));

            if ($onlyLower === 'financial') {
                return array_filter($this->allCommands, fn ($m) => $m['category'] === 'financial');
            }
            if ($onlyLower === 'operations') {
                return array_filter($this->allCommands, fn ($m) => $m['category'] === 'operations');
            }
            if ($onlyLower === 'clinical') {
                return array_filter($this->allCommands, fn ($m) => $m['category'] === 'clinical');
            }

            $requested = array_map('trim', explode(',', $onlyLower));
            $filtered = [];

            foreach ($this->allCommands as $cmd => $meta) {
                $cmdName = str_replace('sync:', '', $cmd);
                foreach ($requested as $req) {
                    if ($req === $cmd || $req === $cmdName || str_contains($cmdName, $req)) {
                        $filtered[$cmd] = $meta;
                        break;
                    }
                }
            }

            if (! empty($filtered)) {
                return $filtered;
            }
        }

        // Default: all commands
        return $this->allCommands;
    }
}
