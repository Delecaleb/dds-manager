<?php

namespace App\Console\Commands;

use App\Models\Office;
use App\Services\OpenDental\ScheduleSnapshotService;
use Illuminate\Console\Command;

class TakeDailyScheduleSnapshot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snapshot:daily-schedule
                            {--office-id= : Specific office ID to process}
                            {--date= : Specific date to snapshot (Y-m-d)}
                            {--lock-today : Take snapshot and lock today\'s schedule at 8:00 AM EST}
                            {--future-days= : Refresh upcoming future days (default 60)}
                            {--backfill-start= : Backfill start date (Y-m-d)}
                            {--backfill-end= : Backfill end date (Y-m-d)}
                            {--force : Force overwrite locked snapshots}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Captures daily schedule snapshots and locks them at 8:00 AM EST for historical accuracy';

    /**
     * Execute the console command.
     */
    public function handle(ScheduleSnapshotService $service): int
    {
        $specificOfficeId = $this->option('office-id');
        $force = (bool) $this->option('force');

        $officeIds = [];
        if ($specificOfficeId !== null) {
            $officeIds = [(int) $specificOfficeId];
        } else {
            $officeIds = Office::query()->pluck('id')->map(fn ($id) => (int) $id)->toArray();
            if (empty($officeIds)) {
                $officeIds = [1];
            }
        }

        $date = $this->option('date');
        $lockToday = (bool) $this->option('lock-today');
        $futureDays = $this->option('future-days');
        $backfillStart = $this->option('backfill-start');
        $backfillEnd = $this->option('backfill-end');

        foreach ($officeIds as $officeId) {
            $this->info("Processing schedule snapshots for Office #{$officeId}...");

            if ($date) {
                $res = $service->takeSnapshot($officeId, $date, $force);
                $this->line("  [{$date}] {$res['status']}: {$res['message']}");
            } elseif ($lockToday) {
                $res = $service->snapshotToday($officeId, $force);
                $this->line("  [Today: {$res['date']}] {$res['status']}: {$res['message']} (Locked: ".($res['locked'] ? 'YES' : 'NO').')');
            } elseif ($backfillStart && $backfillEnd) {
                $count = $service->backfillPastSnapshots($officeId, $backfillStart, $backfillEnd, $force);
                $this->line("  Backfilled {$count} dates between {$backfillStart} and {$backfillEnd}.");
            } elseif ($futureDays !== null) {
                $days = max(1, (int) $futureDays);
                $count = $service->syncFutureSnapshots($officeId, $days);
                $this->line("  Refreshed {$count} future days (0 to +{$days}).");
            } else {
                // Default: snapshot today and rolling next 30 days
                $res = $service->snapshotToday($officeId, $force);
                $this->line("  [Today: {$res['date']}] {$res['status']}: {$res['message']}");
                $futureCount = $service->syncFutureSnapshots($officeId, 30);
                $this->line("  Refreshed {$futureCount} future days ahead.");
            }
        }

        $this->info('Daily schedule snapshot completed successfully.');

        return Command::SUCCESS;
    }
}
