<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$date = '2026-01-02';

$query = DB::table('od_appointments as a')
    ->join('od_procedure_logs as pl', 'a.AptNum', '=', 'pl.AptNum')
    ->leftJoin('od_patients as p', 'a.PatNum', '=', 'p.PatNum')
    ->leftJoin('od_providers as prov', 'a.ProvNum', '=', 'prov.ProvNum')
    ->select(
        'a.AptNum',
        'a.PatNum',
        'p.LName',
        'p.FName',
        'a.ProvNum',
        'prov.Abbr',
        'a.AptDateTime',
        'a.AptStatus',
        'pl.ProcNum',
        'pl.ProcFee',
        'pl.ProcStatus'
    )
    ->whereNotIn('a.AptStatus', [6])
    ->whereBetween('a.AptDateTime', ["$date 00:00:00", "$date 23:59:59"])
    ->get();

echo "Directly attached procedures on $date (".count($query)."):\n";
$sum = 0;
foreach ($query as $row) {
    echo "  Apt {$row->AptNum} | Pat {$row->PatNum} ({$row->LName}, {$row->FName}) | Prov {$row->ProvNum} ({$row->Abbr}) | Status {$row->AptStatus} | Proc {$row->ProcNum} | Fee {$row->ProcFee}\n";
    $sum += (float) $row->ProcFee;
}
echo 'Total Direct Sched Production: $ '.number_format($sum, 2)."\n";
