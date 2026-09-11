<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| OpenDental sync schedule
|--------------------------------------------------------------------------
| Registered via the Schedule facade (Laravel 11/12). Activate on the live
| server with a single cron entry that runs the scheduler every minute:
|
|   * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
|
| `withoutOverlapping(20)` enforces a 20-minute lock expiry to avoid deadlock
| if a process terminates unexpectedly.
*/

/*
| HIGH FREQUENCY (every 10 minutes) — operational data that changes constantly.
| Run on every 10-minute interval so a 10-minute server cron triggers them cleanly.
*/
Schedule::command('sync:process-pending')->everyTenMinutes()->withoutOverlapping(20)->runInBackground()->onOneServer();
Schedule::command('sync:appointments')->everyTenMinutes()->withoutOverlapping(20)->runInBackground()->onOneServer();
Schedule::command('sync:procedurelogs')->everyTenMinutes()->withoutOverlapping(20)->runInBackground()->onOneServer();
Schedule::command('sync:adjustments')->everyTenMinutes()->withoutOverlapping(20)->runInBackground()->onOneServer();
Schedule::command('sync:paysplits')->everyTenMinutes()->withoutOverlapping(20)->runInBackground()->onOneServer();
Schedule::command('sync:claimpayments')->everyTenMinutes()->withoutOverlapping(20)->runInBackground()->onOneServer();
Schedule::command('sync:claimprocs')->everyTenMinutes()->withoutOverlapping(20)->runInBackground()->onOneServer();
Schedule::command('sync:patient-balance')->everyTenMinutes()->withoutOverlapping(20)->runInBackground()->onOneServer();
Schedule::command('sync:payment')->everyTenMinutes()->withoutOverlapping(20)->runInBackground()->onOneServer();

/*
| MEDIUM FREQUENCY (every 30 minutes) — data that changes occasionally.
| Staggered on 5-minute multiples (0, 5, 10, 15, 20, 25) so they align with the 5-minute cron.
*/
Schedule::command('sync:patients')->cron('0,30 * * * *')->withoutOverlapping(20)->runInBackground()->onOneServer();
Schedule::command('sync:treatment-plans')->cron('5,35 * * * *')->withoutOverlapping(20)->runInBackground()->onOneServer();
Schedule::command('sync:treatment-plan-attachments')->cron('10,40 * * * *')->withoutOverlapping(20)->runInBackground()->onOneServer();
Schedule::command('sync:payplancharges')->cron('15,45 * * * *')->withoutOverlapping(20)->runInBackground()->onOneServer();
Schedule::command('sync:recalls')->cron('20,50 * * * *')->withoutOverlapping(20)->runInBackground()->onOneServer();
Schedule::command('sync:schedules')->cron('25,55 * * * *')->withoutOverlapping(20)->runInBackground()->onOneServer();
Schedule::command('sync:deposit')->cron('10,40 * * * *')->withoutOverlapping(20)->runInBackground()->onOneServer();
Schedule::command('sync:statements')->cron('20,50 * * * *')->withoutOverlapping(20)->runInBackground()->onOneServer();

/*
| LOW FREQUENCY (daily) — reference tables and historical data (off-peak hours).
*/
Schedule::command('sync:providers')->dailyAt('01:00')->withoutOverlapping(20)->runInBackground()->onOneServer();
Schedule::command('sync:procedures')->dailyAt('01:05')->withoutOverlapping(20)->runInBackground()->onOneServer();
Schedule::command('sync:recall-types')->dailyAt('01:10')->withoutOverlapping(20)->runInBackground()->onOneServer();
Schedule::command('sync:carriers')->dailyAt('01:15')->withoutOverlapping(20)->runInBackground()->onOneServer();
Schedule::command('sync:insplan')->dailyAt('01:20')->withoutOverlapping(20)->runInBackground()->onOneServer();
Schedule::command('sync:histappointments')->dailyAt('02:00')->withoutOverlapping(20)->runInBackground()->onOneServer();

/*
| SCHEDULE SNAPSHOTS (8:00 AM EST lock & rolling future forecasts)
*/
Schedule::command('snapshot:daily-schedule --lock-today')
    ->dailyAt('08:00')
    ->timezone('America/New_York')
    ->withoutOverlapping(20)
    ->runInBackground()
    ->onOneServer();

Schedule::command('snapshot:daily-schedule --future-days=60')
    ->hourly()
    ->timezone('America/New_York')
    ->withoutOverlapping(20)
    ->runInBackground()
    ->onOneServer();

/*
| ORPHAN DATA PRUNING (runs twice daily: 07:30 AM EST before morning snapshot & 19:30 PM EST)
| Incremental pruning automatically removes records added today that were deleted in Open Dental across all active offices,
| logging each run to sync_log_prune.
*/
Schedule::command('sync:prune-deleted --today')
    ->dailyAt('07:30')
    ->timezone('America/New_York')
    ->withoutOverlapping(20)
    ->runInBackground()
    ->onOneServer();

Schedule::command('sync:prune-deleted --today')
    ->dailyAt('19:30')
    ->timezone('America/New_York')
    ->withoutOverlapping(20)
    ->runInBackground()
    ->onOneServer();

/*
| NOTE: Heavy range-backfill commands (`sync:*-range`) are kept on-demand
| for initial setups and manual backfills via CLI / UI Sync Requests to avoid
| overloading OpenDental servers during regular scheduled runs.
*/
