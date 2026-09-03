<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$date = '2026-01-02';
echo "=== TARGET JARVIS ROWS FOR 2026-01-02 ===\n";

$targetPats = [21644, 22167, 22278, 22421, 22489, 22259, 22423];

foreach ($targetPats as $pNum) {
    $pat = DB::table('od_patients')->where('PatNum', $pNum)->first();
    $name = $pat ? "{$pat->LName}, {$pat->FName}" : 'Unknown';
    echo "==================== Patient $pNum: $name ====================\n";

    // Check appointments
    $appts = DB::table('od_appointments')->where('PatNum', $pNum)->whereBetween('AptDateTime', ["$date 00:00:00", "$date 23:59:59"])->get();
    echo "od_appointments on $date (".count($appts)."):\n";
    foreach ($appts as $a) {
        echo "  Apt {$a->AptNum} | Status {$a->AptStatus} | Time {$a->AptDateTime} | Descript: {$a->ProcDescript} | Note: {$a->Note}\n";
    }

    // Check histappointments
    if (Schema::hasTable('od_histappointments')) {
        $hist = DB::table('od_histappointments')->where('PatNum', $pNum)->whereBetween('AptDateTime', ["$date 00:00:00", "$date 23:59:59"])->orderBy('HistDateTStamp')->get();
        echo "od_histappointments for $date (".count($hist)."):\n";
        foreach ($hist as $h) {
            echo "  HistApptNum {$h->HistApptNum} | AptNum {$h->AptNum} | HistDate {$h->HistDateTStamp} | Status {$h->AptStatus} | Descript: {$h->ProcDescript}\n";
        }
    }

    // Check all procedure logs
    $procs = DB::table('od_procedure_logs')->where('PatNum', $pNum)->get();
    echo 'od_procedure_logs ('.count($procs)."):\n";
    foreach ($procs as $p) {
        echo "  ProcNum {$p->ProcNum} | AptNum {$p->AptNum} | Status {$p->ProcStatus} | Fee {$p->ProcFee} | CodeNum {$p->CodeNum} | ProcDate {$p->ProcDate}\n";
    }
    echo "\n";
}
