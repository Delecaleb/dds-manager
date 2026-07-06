<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| HIGH FREQUENCY (Every 5 Minutes)
|--------------------------------------------------------------------------
| Operational data that changes constantly.
*/
return function (Schedule $schedule) {
    $schedule->command('sync:appointments')
        ->cron('*/5 * * * *')
        ->withoutOverlapping()
        ->runInBackground()
        ->onOneServer();

    $schedule->command('sync:procedurelogs')
        ->cron('1-59/5 * * * *')
        ->withoutOverlapping()
        ->runInBackground()
        ->onOneServer();

    $schedule->command('sync:adjustments')
        ->cron('2-59/5 * * * *')
        ->withoutOverlapping()
        ->runInBackground()
        ->onOneServer();

    $schedule->command('sync:paysplits')
        ->cron('3-59/5 * * * *')
        ->withoutOverlapping()
        ->runInBackground()
        ->onOneServer();

    $schedule->command('sync:claimpayments')
        ->cron('4-59/5 * * * *')
        ->withoutOverlapping()
        ->runInBackground()
        ->onOneServer();

    $schedule->command('sync:claimprocs')
        ->cron('*/5 * * * *')
        ->withoutOverlapping()
        ->runInBackground()
        ->onOneServer();

    $schedule->command('sync:patient-balance')
        ->cron('2-59/5 * * * *')
        ->withoutOverlapping()
        ->runInBackground()
        ->onOneServer();


    /*
    |--------------------------------------------------------------------------
    | MEDIUM FREQUENCY (Every 30 Minutes)
    |--------------------------------------------------------------------------
    | Data that changes occasionally.
    */

    $schedule->command('sync:patients')
        ->cron('0,30 * * * *')
        ->withoutOverlapping()
        ->runInBackground()
        ->onOneServer();

    $schedule->command('sync:treatment-plans')
        ->cron('5,35 * * * *')
        ->withoutOverlapping()
        ->runInBackground()
        ->onOneServer();

    $schedule->command('sync:treatment-plan-attachments')
        ->cron('10,40 * * * *')
        ->withoutOverlapping()
        ->runInBackground()
        ->onOneServer();

    $schedule->command('sync:payplancharges')
        ->cron('15,45 * * * *')
        ->withoutOverlapping()
        ->runInBackground()
        ->onOneServer();

    $schedule->command('sync:recalls')
        ->cron('20,50 * * * *')
        ->withoutOverlapping()
        ->runInBackground()
        ->onOneServer();

    $schedule->command('sync:schedules')
        ->cron('25,55 * * * *')
        ->withoutOverlapping()
        ->runInBackground()
        ->onOneServer();


    /*
    |--------------------------------------------------------------------------
    | LOW FREQUENCY (Daily)
    |--------------------------------------------------------------------------
    | Reference tables that rarely change.
    */

    $schedule->command('sync:providers')
        ->dailyAt('01:00')
        ->withoutOverlapping()
        ->runInBackground()
        ->onOneServer();

    $schedule->command('sync:procedures')
        ->dailyAt('01:10')
        ->withoutOverlapping()
        ->runInBackground()
        ->onOneServer();

    $schedule->command('sync:recall-types')
        ->dailyAt('01:20')
        ->withoutOverlapping()
        ->runInBackground()
        ->onOneServer();

    };