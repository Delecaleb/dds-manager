<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

// Days with target scheduled production in Jarvis:
// Jan 02: 4,945
// Jan 06: 9,840
// Jan 13: 4,500
// Jan 16: 14,100
// Jan 20: 15,950
// Jan 23: 420
// Jan 30: 9,200

$targets = [
    '2026-01-02' => 4945,
    '2026-01-06' => 9840,
    '2026-01-13' => 4500,
    '2026-01-16' => 14100,
    '2026-01-20' => 15950,
    '2026-01-23' => 420,
    '2026-01-30' => 9200,
];

foreach ($targets as $date => $targetAmount) {
    echo "===============================================================\n";
    echo "DATE: $date | Jarvis Target: \$$targetAmount\n";
    echo "===============================================================\n";

    // 1. Check morning snapshot appointments (before 7 AM on $date)
    $sub = DB::table('od_histappointments')
        ->select('AptNum', DB::raw('MAX(HistDateTStamp) as max_hist'))
        ->where('HistDateTStamp', '<', "$date 07:00:00")
        ->whereBetween('AptDateTime', ["$date 00:00:00", "$date 23:59:59"])
        ->groupBy('AptNum');

    $morningAppts = DB::table('od_histappointments as h')
        ->joinSub($sub, 'm', function ($j) {
            $j->on('h.AptNum', '=', 'm.AptNum')
                ->on('h.HistDateTStamp', '=', 'm.max_hist');
        })
        ->where('h.AptStatus', 1)
        ->get();

    echo 'Morning Appts count (Status 1): '.count($morningAppts)."\n";

    // Let's inspect appointments that have non-empty ProcDescript or attached procedure logs
    foreach ($morningAppts as $ma) {
        $procs = DB::table('od_procedure_logs')->where('AptNum', $ma->AptNum)->get();
        // Also check if patient had TP (treatment planned) procedures
        $tpProcs = DB::table('od_procedure_logs')
            ->where('PatNum', $ma->PatNum)
            ->whereIn('ProcStatus', [1, '1', 6, '6', 'TP'])
            ->get();

        $pat = DB::table('od_patients')->where('PatNum', $ma->PatNum)->first();
        $name = $pat ? "{$pat->LName}, {$pat->FName}" : 'Unknown';

        if (count($procs) > 0 || ! empty($ma->ProcDescript) || count($tpProcs) > 0) {
            $fees = $procs->sum('ProcFee');
            if ($fees > 0 || ! empty($ma->ProcDescript)) {
                echo "  Apt {$ma->AptNum} | Pat {$ma->PatNum} ($name) | Descript: '{$ma->ProcDescript}' | Attached Fee: \$$fees | TP Procs: ".count($tpProcs)."\n";
                foreach ($procs as $p) {
                    echo "    -> Attached Proc {$p->ProcNum} | Fee \${$p->ProcFee} | Status {$p->ProcStatus} | ProcDate {$p->ProcDate} | CodeNum {$p->CodeNum}\n";
                }
            }
        }
    }
    echo "\n";
}
