<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$procs = DB::table('od_procedure_logs')
    ->whereIn('ProcNum', [1906528, 1906751, 1906974, 1907159])
    ->get();

foreach ($procs as $p) {
    echo "Proc {$p->ProcNum} | AptNum {$p->AptNum} | Fee \${$p->ProcFee} | ProcDate: {$p->ProcDate} | DateTStamp: {$p->DateTStamp} | Status: {$p->ProcStatus}\n";
}
