<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$date = '2026-01-02';

echo "=== CHECKING HISTAPPOINTMENTS AS OF START OF $date (<= $date 00:00:00 or morning) ===\n";

// In OpenDental histappointment:
// For appointments that were scheduled for $date:
// The last histappointment record for an AptNum BEFORE or AT the start of $date ($date 00:00:00 or first thing on $date)
// where AptDateTime was on $date!

$sub = DB::table('od_histappointments')
    ->select('AptNum', DB::raw('MAX(HistDateTStamp) as max_hist'))
    ->where('HistDateTStamp', '<', "$date 07:00:00") // before office opens on Jan 2
    ->whereBetween('AptDateTime', ["$date 00:00:00", "$date 23:59:59"])
    ->groupBy('AptNum');

$histApptsAtMorning = DB::table('od_histappointments as h')
    ->joinSub($sub, 'latest', function ($join) {
        $join->on('h.AptNum', '=', 'latest.AptNum')
            ->on('h.HistDateTStamp', '=', 'latest.max_hist');
    })
    ->get();

echo "Appointments scheduled for $date as of morning of $date: ".count($histApptsAtMorning)."\n";
foreach ($histApptsAtMorning as $h) {
    echo "  HistAppt {$h->HistApptNum} | AptNum {$h->AptNum} | PatNum {$h->PatNum} | Status {$h->AptStatus} | Time {$h->AptDateTime} | Descript: '{$h->ProcDescript}'\n";
}
