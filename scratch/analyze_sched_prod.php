<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\OperationsController;
use App\Services\OpenDental\OperationsAnalyticsService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

$date = '2026-01-02';

echo "===============================================================\n";
echo "1. OPERATIONS ANALYTICS SERVICE PERFORMANCE ON $date:\n";
echo "===============================================================\n";

$svc = app(OperationsAnalyticsService::class);
$perf = $svc->performance($date, $date);

foreach ($perf['rows'] as $r) {
    echo "Date: {$r['date']} | Sched Prod: {$r['sched_production']} | Act Prod: {$r['actual_production']} | Booked Prod: {$r['booked_production']}\n";
}
echo "TOTAL Sched Prod: {$perf['total']['sched_production']}\n\n";

echo "===============================================================\n";
echo "2. OPERATIONS DRILLDOWN ON $date:\n";
echo "===============================================================\n";

$controller = app(OperationsController::class);
$req = Request::create('/operations/drilldown', 'GET', [
    'metric' => 'sched_production',
    'start_date' => $date,
    'end_date' => $date,
]);
$res = $controller->drilldown($req);
$dd = $res->getData(true);

echo "Title: {$dd['title']}\n";
echo 'Rows count: '.count($dd['rows'])."\n";
$sumDd = 0;
foreach ($dd['rows'] as $r) {
    $pat = is_array($r['patient']) ? $r['patient']['label'] : $r['patient'];
    $prod = (float) $r['production'];
    $sumDd += $prod;
    echo "  Pat {$r['pat_id']} ($pat) | Appt {$r['appt_id']} | Status {$r['status']} | Prod: $ {$prod}\n";
}
echo "Total from rows: $sumDd | Totals array: ".($dd['totals']['production'] ?? 0)."\n\n";

echo "===============================================================\n";
echo "3. ALL APPOINTMENTS ON $date:\n";
echo "===============================================================\n";

$appts = DB::table('od_appointments')
    ->whereBetween('AptDateTime', ["$date 00:00:00", "$date 23:59:59"])
    ->get();

echo 'Count: '.count($appts)."\n";
foreach ($appts as $a) {
    $procs = DB::table('od_procedure_logs')->where('AptNum', $a->AptNum)->get();
    $pat = DB::table('od_patients')->where('PatNum', $a->PatNum)->first();
    $patName = $pat ? "{$pat->LName}, {$pat->FName}" : 'Unknown';
    $feeSum = $procs->sum('ProcFee');
    echo "Apt {$a->AptNum} | Pat {$a->PatNum} ($patName) | Status {$a->AptStatus} | Time {$a->AptDateTime} | Descript: '{$a->ProcDescript}' | Attached procs count: ".count($procs)." | Attached fee sum: $feeSum\n";
    foreach ($procs as $p) {
        echo "    -> Proc {$p->ProcNum} | Status {$p->ProcStatus} | Fee {$p->ProcFee} | Code {$p->CodeNum} | Date {$p->ProcDate}\n";
    }
}

echo "\n===============================================================\n";
echo "4. PROCEDURES ON $date NOT ATTACHED TO APPT:\n";
echo "===============================================================\n";
$unattachedProcs = DB::table('od_procedure_logs')
    ->whereBetween('ProcDate', ["$date 00:00:00", "$date 23:59:59"])
    ->where(function ($q) {
        $q->whereNull('AptNum')->orWhere('AptNum', 0);
    })
    ->get();
echo "Unattached procs on $date: ".count($unattachedProcs)."\n";
foreach ($unattachedProcs as $p) {
    $pat = DB::table('od_patients')->where('PatNum', $p->PatNum)->first();
    $patName = $pat ? "{$pat->LName}, {$pat->FName}" : 'Unknown';
    echo "  Proc {$p->ProcNum} | Pat {$p->PatNum} ($patName) | Status {$p->ProcStatus} | Fee {$p->ProcFee} | Code {$p->CodeNum}\n";
}
