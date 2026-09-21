<?php

use App\Services\Sync\QueueHealthService;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| OpenDental sync schedule (shared-hosting safe: database queue, no Redis)
|--------------------------------------------------------------------------
| Activate on the live server with ONE cron entry that runs every minute:
|
|   * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
|
| How it works:
|  1. `sync:dispatch` only inserts one SyncOfficeModule job per (module,
|     office) into the `jobs` table — it finishes in milliseconds.
|  2. Every minute cron starts short-lived workers that drain the `sync`
|     queue and exit. A job stops at its time budget with the cursor saved
|     and re-queues itself, so the host never has to kill a long process.
|  3. SyncLease (sync_logs.run_token) makes every office+module run
|     exclusive, including manual `sync:*` CLI runs and "Sync now" buttons.
|  Tuning lives in config/sync.php. Failures land in `failed_jobs`
|  (`php artisan queue:failed`, `php artisan queue:retry all`).
|
| Module keys come from the registry in SyncReportService::getModuleDefinitions().
*/

// Heartbeat: proves cron really runs schedule:run (and with which PHP), for the
// Sync Manager's queue health check and `php artisan sync:health`.
Schedule::call(fn () => app(QueueHealthService::class)->recordSchedulerTick())
    ->name('sync-health-heartbeat')
    ->everyMinute();

// Defaults are merged in so a stale config cache (deployed code newer than a
// cached config) degrades to safe values instead of crashing every scheduled task.
$syncQueue = array_merge([
    'connection' => 'sync-database',
    'name' => 'sync',
    'priority_name' => 'sync-priority',
    'workers' => 1,
    'cron_interval_minutes' => 1,
    'worker_max_time' => 240,
    'job_timeout' => 600,
    'scheduler_starts_workers' => true,
    'background_tasks' => true,
], (array) config('sync.queue', []));

// Background processes are killed on hosts that end a cron job's children with
// the job (see sync.queue.background_tasks); there the task runs in schedule:run.
$inBackground = fn (Event $event): Event => $syncQueue['background_tasks'] ? $event->runInBackground() : $event;

// Every-minute cron: short workers that exit when the queue is empty.
// Coarser cron (e.g. 5 min, common on shared hosting): workers keep polling
// until ~20s before the next cron run, so new jobs never wait a full interval.
$cronInterval = (int) $syncQueue['cron_interval_minutes'];
$workerMaxTime = $cronInterval > 1 ? $cronInterval * 60 - 20 : (int) $syncQueue['worker_max_time'];
$stopWhenEmpty = $cronInterval > 1 ? '' : ' --stop-when-empty';

// When false, the worker comes from its own cron line (see sync.queue.scheduler_starts_workers).
$schedulerWorkers = $syncQueue['scheduler_starts_workers'] ? max(1, (int) $syncQueue['workers']) : 0;

foreach ($schedulerWorkers > 0 ? range(1, $schedulerWorkers) : [] as $worker) {
    Schedule::command(sprintf(
        // Priority queue listed first: the worker always drains it before regular syncs.
        'queue:work %s --queue=%s,%s --name=sync-worker-%d%s --max-time=%d --timeout=%d --sleep=3 --memory=256',
        $syncQueue['connection'],
        $syncQueue['priority_name'],
        $syncQueue['name'],
        $worker,
        $stopWhenEmpty,
        $workerMaxTime,
        $syncQueue['job_timeout'],
    ))
        ->everyMinute()
        // Lock expiry (minutes) outlives the worker's max time + one job_timeout, so
        // a host-killed worker frees its slot without workers piling up.
        ->withoutOverlapping((int) ceil(($workerMaxTime + $syncQueue['job_timeout']) / 60))
        ->runInBackground()
        ->onOneServer();
}

/*
| Date-range backfills requested from the Sync Manager UI.
*/
$inBackground(Schedule::command('sync:process-pending')->everyTenMinutes()->withoutOverlapping(20)->onOneServer());

/*
| HIGH FREQUENCY (every 10 minutes) — operational data that changes constantly.
*/
Schedule::command('sync:dispatch appointments procedurelogs adjustments paysplits claimpayments claimprocs payments patient_balance')
    ->everyTenMinutes()
    ->onOneServer();

/*
| MEDIUM FREQUENCY (every 30 minutes) — data that changes occasionally.
| Staggered so each tick only adds a handful of jobs.
*/
Schedule::command('sync:dispatch patients')->cron('0,30 * * * *')->onOneServer();
Schedule::command('sync:dispatch treatment_plans')->cron('5,35 * * * *')->onOneServer();
Schedule::command('sync:dispatch treatment_plan_attachments deposits')->cron('10,40 * * * *')->onOneServer();
Schedule::command('sync:dispatch payplancharges')->cron('15,45 * * * *')->onOneServer();
Schedule::command('sync:dispatch recalls statements')->cron('20,50 * * * *')->onOneServer();
Schedule::command('sync:dispatch schedules')->cron('25,55 * * * *')->onOneServer();

/*
| LOW FREQUENCY (daily) — reference tables and historical data (off-peak hours).
*/
Schedule::command('sync:dispatch providers procedures recall_types carriers insplan')->dailyAt('01:00')->onOneServer();
Schedule::command('sync:dispatch histappointments')->dailyAt('02:00')->onOneServer();

/*
| Queue housekeeping: keep failed_jobs from growing forever.
*/
Schedule::command('queue:prune-failed --hours=168')->dailyAt('03:00')->onOneServer();

/*
| SCHEDULE SNAPSHOTS (8:00 AM EST lock & rolling future forecasts)
*/
$inBackground(Schedule::command('snapshot:daily-schedule --lock-today')
    ->dailyAt('08:00')
    ->timezone('America/New_York')
    ->withoutOverlapping(20)
    ->onOneServer());

$inBackground(Schedule::command('snapshot:daily-schedule --future-days=60')
    ->hourly()
    ->timezone('America/New_York')
    ->withoutOverlapping(20)
    ->onOneServer());

/*
| HARD-DELETE PRUNING — OpenDental hard-deletes rows, which incremental syncs never see.
| Each entry only queues one PruneOfficeTable job per office × table (see config sync.prune).
|  - Rolling (last 7 days + next 90): 07:00 on the priority queue so it finishes before the 08:00
|    schedule snapshot (~1 OpenDental call per office × table), and again at 19:30.
|  - Current month: nightly, catches deletions of anything dated this month.
|  - Full scan: weekly, catches older deletions. Disable with SYNC_PRUNE_WEEKLY_FULL_SCAN=false.
| Every run logs to sync_log_prune; a mass-delete refusal lands in failed_jobs with instructions.
*/
Schedule::command('sync:prune-deleted --rolling --queue --priority')->dailyAt('07:00')->timezone('America/New_York')->onOneServer();
Schedule::command('sync:prune-deleted --rolling --queue')->dailyAt('19:30')->timezone('America/New_York')->onOneServer();
Schedule::command('sync:prune-deleted --current-month --queue')->dailyAt('02:30')->timezone('America/New_York')->onOneServer();

if (config('sync.prune.weekly_full_scan')) {
    Schedule::command('sync:prune-deleted --full --queue')->weeklyOn(0, '03:30')->timezone('America/New_York')->onOneServer();
}

/*
| NOTE: Heavy range-backfill commands (`sync:*-range`) are kept on-demand
| for initial setups and manual backfills via CLI / UI Sync Requests to avoid
| overloading OpenDental servers during regular scheduled runs.
*/
