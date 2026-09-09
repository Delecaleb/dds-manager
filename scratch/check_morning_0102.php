<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$date = '2026-01-02';

// Check appointments that were on schedule BEFORE 7:00 AM on 2026-01-02
$morningSub = DB::table('od_histappointments')
    ->select('AptNum', DB::raw('MAX(HistDateTStamp) as max_hist'))
    ->where('HistDateTStamp', '<', "$date 07:00:00")
    ->whereBetween('AptDateTime', ["$date 00:00:00", "$date 23:59:59"])
    ->where('AptStatus', 1)
    ->groupBy('AptNum');

$morningAppts = DB::table('od_histappointments as h')
    ->joinSub($morningSub, 'm', function ($j) {
        $j->on('h.AptNum', '=', 'm.AptNum')
            ->on('h.HistDateTStamp', '=', 'm.max_hist');
    })
    ->get();

echo "Appointments on schedule as of 7 AM on $date: ".count($morningAppts)."\n";
foreach ($morningAppts as $ma) {
    echo "  Apt {$ma->AptNum} | Pat {$ma->PatNum} | Descript: '{$ma->ProcDescript}'\n";
}
