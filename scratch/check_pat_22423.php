<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$procs = DB::table('od_procedure_logs')->where('PatNum', 22423)->get();
foreach ($procs as $p) {
    echo "Proc {$p->ProcNum} | AptNum {$p->AptNum} | Fee \${$p->ProcFee} | CodeNum {$p->CodeNum} | ProcDate: {$p->ProcDate} | SecDateEntry: {$p->SecDateEntry} | Status: {$p->ProcStatus}\n";
}
