<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

foreach ([21644, 22167] as $pNum) {
    echo "==================== PATIENT $pNum ====================\n";
    $p = DB::table('od_patients')->where('PatNum', $pNum)->first();
    echo "Name: {$p->LName}, {$p->FName}\n";

    echo "--- All Appointments ---\n";
    $appts = DB::table('od_appointments')->where('PatNum', $pNum)->get();
    foreach ($appts as $a) {
        echo "  Apt {$a->AptNum} | Status {$a->AptStatus} | Time {$a->AptDateTime} | Descript: {$a->ProcDescript} | Note: {$a->Note}\n";
    }

    echo "--- All Procedure Logs ---\n";
    $procs = DB::table('od_procedure_logs')->where('PatNum', $pNum)->get();
    foreach ($procs as $pr) {
        $code = DB::table('od_procedures')->where('CodeNum', $pr->CodeNum)->first();
        $codeStr = $code ? "{$code->ProcCode} ({$code->Descript})" : "CodeNum {$pr->CodeNum}";
        echo "  Proc {$pr->ProcNum} | Apt {$pr->AptNum} | Status {$pr->ProcStatus} | Fee {$pr->ProcFee} | Date {$pr->ProcDate} | Code: $codeStr\n";
    }
}
