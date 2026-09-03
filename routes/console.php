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
| Staggered start minutes so the syncs don't all fire at the exact same second.
*/
Schedule::command('sync:process-pending')->everyMinute()->withoutOverlapping(15)->runInBackground()->onOneServer();
Schedule::command('sync:appointments')->cron('*/5 * * * *')->withoutOverlapping(15)->runInBackground()->onOneServer();
Schedule::command('sync:procedurelogs')->cron('1-59/5 * * * *')->withoutOverlapping(15)->runInBackground()->onOneServer();
Schedule::command('sync:adjustments')->cron('2-59/5 * * * *')->withoutOverlapping(15)->runInBackground()->onOneServer();
Schedule::command('sync:paysplits')->cron('3-59/5 * * * *')->withoutOverlapping(15)->runInBackground()->onOneServer();
Schedule::command('sync:claimpayments')->cron('4-59/5 * * * *')->withoutOverlapping(15)->runInBackground()->onOneServer();
Schedule::command('sync:claimprocs')->cron('*/5 * * * *')->withoutOverlapping(15)->runInBackground()->onOneServer();
Schedule::command('sync:patient-balance')->cron('2-59/5 * * * *')->withoutOverlapping(15)->runInBackground()->onOneServer();
Schedule::command('sync:payment')->cron('3-59/5 * * * *')->withoutOverlapping(15)->runInBackground()->onOneServer();

/*
| MEDIUM FREQUENCY (every 30 minutes) — data that changes occasionally.
*/
Schedule::command('sync:patients')->cron('0,30 * * * *')->withoutOverlapping(15)->runInBackground()->onOneServer();
Schedule::command('sync:treatment-plans')->cron('5,35 * * * *')->withoutOverlapping(15)->runInBackground()->onOneServer();
Schedule::command('sync:treatment-plan-attachments')->cron('10,40 * * * *')->withoutOverlapping(15)->runInBackground()->onOneServer();
Schedule::command('sync:payplancharges')->cron('15,45 * * * *')->withoutOverlapping(15)->runInBackground()->onOneServer();
Schedule::command('sync:recalls')->cron('20,50 * * * *')->withoutOverlapping(15)->runInBackground()->onOneServer();
Schedule::command('sync:schedules')->cron('25,55 * * * *')->withoutOverlapping(15)->runInBackground()->onOneServer();
Schedule::command('sync:deposit')->cron('12,42 * * * *')->withoutOverlapping(15)->runInBackground()->onOneServer();
Schedule::command('sync:statements')->cron('18,48 * * * *')->withoutOverlapping(15)->runInBackground()->onOneServer();

/*
| LOW FREQUENCY (daily) — reference tables and historical data (off-peak hours).
*/
Schedule::command('sync:providers')->dailyAt('01:00')->withoutOverlapping(15)->runInBackground()->onOneServer();
Schedule::command('sync:procedures')->dailyAt('01:10')->withoutOverlapping(15)->runInBackground()->onOneServer();
Schedule::command('sync:recall-types')->dailyAt('01:20')->withoutOverlapping(15)->runInBackground()->onOneServer();
Schedule::command('sync:carriers')->dailyAt('01:25')->withoutOverlapping(15)->runInBackground()->onOneServer();
Schedule::command('sync:insplan')->dailyAt('01:30')->withoutOverlapping(15)->runInBackground()->onOneServer();
Schedule::command('sync:histappointments')->dailyAt('02:00')->withoutOverlapping(15)->runInBackground()->onOneServer();

/*
| NOTE: Heavy range-backfill commands (`sync:*-range`) are kept on-demand
| for initial setups and manual backfills via CLI / UI Sync Requests to avoid
| overloading OpenDental servers during regular scheduled runs.
*/
