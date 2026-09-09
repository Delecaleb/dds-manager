<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$date = '2026-01-02';

echo "=== CHECKING PATIENTS WITH PRODUCTION ON 2026-01-02 ===\n";

// In od_appointments, who had procedures?
$appts = DB::table('od_appointments as a')
    ->join('od_procedure_logs as pl', 'a.AptNum', '=', 'pl.AptNum')
    ->select('a.AptNum', 'a.PatNum', 'a.AptDateTime', 'a.AptStatus', 'pl.ProcFee', 'pl.ProcDate', 'pl.DateTStamp', 'pl.CodeNum')
    ->whereBetween('a.AptDateTime', ["$date 00:00:00", "$date 23:59:59"])
    ->get();

foreach ($appts as $a) {
    if ($a->ProcFee > 0) {
        $pat = DB::table('od_patients')->where('PatNum', $a->PatNum)->first();
        echo "Apt {$a->AptNum} | Pat {$a->PatNum} ({$pat->LName}) | Fee \${$a->ProcFee} | AptDateTime: {$a->AptDateTime}\n";

        // Check when this appointment was CREATED or SCHEDULED in histappointments
        $hists = DB::table('od_histappointments')
            ->where('AptNum', $a->AptNum)
            ->orderBy('HistDateTStamp')
            ->get();
        echo "  Hist records for Apt {$a->AptNum}:\n";
        foreach ($hists as $h) {
            echo "    -> HistNum {$h->HistApptNum} | HistDate {$h->HistDateTStamp} | Status {$h->AptStatus} | AptDate {$h->AptDateTime} | Descript: {$h->ProcDescript}\n";
        }
    }
}
