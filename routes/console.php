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
| `withoutOverlapping(15)` enforces a 15-minute lock expiry to avoid deadlock
| if a process terminates unexpectedly.
*/

/*
| HIGH FREQUENCY (every 5 minutes) — operational data that changes constantly.
| Run on every 5-minute interval so a 5-minute server cron triggers them cleanly.
*/
Schedule::command('sync:process-pending')->everyFiveMinutes()->withoutOverlapping(120)->runInBackground()->onOneServer();
Schedule::command('sync:appointments')->everyFiveMinutes()->withoutOverlapping(120)->runInBackground()->onOneServer();
Schedule::command('sync:procedurelogs')->everyFiveMinutes()->withoutOverlapping(120)->runInBackground()->onOneServer();
Schedule::command('sync:adjustments')->everyFiveMinutes()->withoutOverlapping(120)->runInBackground()->onOneServer();
Schedule::command('sync:paysplits')->everyFiveMinutes()->withoutOverlapping(120)->runInBackground()->onOneServer();
Schedule::command('sync:claimpayments')->everyFiveMinutes()->withoutOverlapping(120)->runInBackground()->onOneServer();
Schedule::command('sync:claimprocs')->everyFiveMinutes()->withoutOverlapping(120)->runInBackground()->onOneServer();
Schedule::command('sync:patient-balance')->everyFiveMinutes()->withoutOverlapping(120)->runInBackground()->onOneServer();
Schedule::command('sync:payment')->everyFiveMinutes()->withoutOverlapping(120)->runInBackground()->onOneServer();

/*
| MEDIUM FREQUENCY (every 30 minutes) — data that changes occasionally.
| Staggered on 5-minute multiples (0, 5, 10, 15, 20, 25) so they align with the 5-minute cron.
*/
Schedule::command('sync:patients')->cron('0,30 * * * *')->withoutOverlapping(120)->runInBackground()->onOneServer();
Schedule::command('sync:treatment-plans')->cron('5,35 * * * *')->withoutOverlapping(120)->runInBackground()->onOneServer();
Schedule::command('sync:treatment-plan-attachments')->cron('10,40 * * * *')->withoutOverlapping(120)->runInBackground()->onOneServer();
Schedule::command('sync:payplancharges')->cron('15,45 * * * *')->withoutOverlapping(120)->runInBackground()->onOneServer();
Schedule::command('sync:recalls')->cron('20,50 * * * *')->withoutOverlapping(120)->runInBackground()->onOneServer();
Schedule::command('sync:schedules')->cron('25,55 * * * *')->withoutOverlapping(120)->runInBackground()->onOneServer();
Schedule::command('sync:deposit')->cron('10,40 * * * *')->withoutOverlapping(120)->runInBackground()->onOneServer();
Schedule::command('sync:statements')->cron('20,50 * * * *')->withoutOverlapping(120)->runInBackground()->onOneServer();

/*
| LOW FREQUENCY (daily) — reference tables and historical data (off-peak hours).
*/
Schedule::command('sync:providers')->dailyAt('01:00')->withoutOverlapping(120)->runInBackground()->onOneServer();
Schedule::command('sync:procedures')->dailyAt('01:05')->withoutOverlapping(120)->runInBackground()->onOneServer();
Schedule::command('sync:recall-types')->dailyAt('01:10')->withoutOverlapping(120)->runInBackground()->onOneServer();
Schedule::command('sync:carriers')->dailyAt('01:15')->withoutOverlapping(120)->runInBackground()->onOneServer();
Schedule::command('sync:insplan')->dailyAt('01:20')->withoutOverlapping(120)->runInBackground()->onOneServer();
Schedule::command('sync:histappointments')->dailyAt('02:00')->withoutOverlapping(120)->runInBackground()->onOneServer();

/*
| SCHEDULE SNAPSHOTS (8:00 AM EST lock & rolling future forecasts)
*/
Schedule::command('snapshot:daily-schedule --lock-today')
    ->dailyAt('08:00')
    ->timezone('America/New_York')
    ->withoutOverlapping(120)
    ->runInBackground()
    ->onOneServer();

Schedule::command('snapshot:daily-schedule --future-days=60')
    ->hourly()
    ->timezone('America/New_York')
    ->withoutOverlapping(120)
    ->runInBackground()
    ->onOneServer();

/*
| NOTE: Heavy range-backfill commands (`sync:*-range`) are kept on-demand
| for initial setups and manual backfills via CLI / UI Sync Requests to avoid
| overloading OpenDental servers during regular scheduled runs.
*/
