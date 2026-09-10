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
                            {--dry-run : Preview target offices and reset date without running sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset sync cursors and sequentially resync all OpenDental datasets for the specified month.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
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

        $this->info('=================================================================');
        $this->info("      OPENDENTAL MONTH DATA RESYNC: {$targetMonth->format('F Y')} ({$monthStart} to {$monthEnd})");
        $this->info("      Cursor Reset Watermark: {$cursorResetDate}");
        $this->info("      Target Offices: {$offices->count()} office(s)");
        $this->info("=================================================================\n");

        if ($dryRun) {
            $this->warn('[DRY RUN] Would reset sync cursors and run full sync for:');
            foreach ($offices as $office) {
                $this->line(" - Office [{$office->id}] {$office->name}");
            }

            return Command::SUCCESS;
        }

        $syncCommands = [
            'sync:definition' => 'Definitions & Categories',
            'sync:insplan' => 'Insurance Plans',
            'sync:carriers' => 'Insurance Carriers',
            'sync:providers' => 'Providers',
            'sync:procedures' => 'Procedure Codes',
            'sync:patients' => 'Patients',
            'sync:appointments' => 'Appointments',
            'sync:histappointments' => 'Historical Appointments',
            'sync:procedurelogs' => 'Procedure Logs',
            'sync:adjustments' => 'Adjustments',
            'sync:claimprocs' => 'Claim Procedures',
            'sync:payment' => 'Payments',
            'sync:paysplits' => 'Pay Splits',
            'sync:claimpayments' => 'Claim Payments & Checks',
            'sync:deposit' => 'Deposits',
            'sync:payplancharges' => 'Pay Plan Charges',
            'sync:treatment-plans' => 'Treatment Plans',
            'sync:treatment-plan-attachments' => 'Treatment Plan Attachments',
            'sync:schedules' => 'Schedules',
            'sync:recalls' => 'Recalls',
            'sync:recall-types' => 'Recall Types',
            'sync:statements' => 'Statements',
        ];

        foreach ($offices as $office) {
            $this->line('<comment>=================================================================</comment>');
            $this->line("<comment>Processing Office [{$office->id}] {$office->name}...</comment>");
            $this->line('<comment>=================================================================</comment>');

            // 1. Reset sync cursors in sync_logs for this office
            $resetCount = DB::table('sync_logs')
                ->where('office_id', $office->id)
                ->update([
                    'last_synced_at' => $cursorResetDate,
                    'last_primary_key' => 0,
                    'status' => 'idle',
                ]);

            $this->info("Reset {$resetCount} sync cursor(s) to {$cursorResetDate} for Office [{$office->id}].");

            // 2. Sequentially run sync commands for this office
            foreach ($syncCommands as $command => $label) {
                $this->line("Syncing {$label} ({$command})...");
                try {
                    $exitCode = Artisan::call($command, [
                        '--office-id' => $office->id,
                    ], $this->output);

                    if ($exitCode !== Command::SUCCESS) {
                        $this->warn("Command {$command} exited with code {$exitCode}. Continuing...");
                    }
                } catch (Throwable $e) {
                    $this->error("Error running {$command} for Office [{$office->id}]: {$e->getMessage()}");
                }
            }
        }

        $this->info("\n=================================================================");
        $this->info("   SUCCESS: Month data resync completed for {$targetMonth->format('F Y')} across all target offices.");
        $this->info("=================================================================\n");

        return Command::SUCCESS;
    }
}
