<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$start = '2026-08-01';
$end = '2026-08-31';

echo "=== AUGUST 2026: 12:01 AM SNAPSHOT QUERY ===\n";

$sql = "
SELECT 
    DATE(h.AptDateTime) AS `date`,
    COUNT(DISTINCT h.AptNum) AS appts_at_1201_am,
    COALESCE(SUM(pl.ProcFee), 0) AS sched_prod_1201_am
FROM od_histappointments AS h
INNER JOIN (
    -- Latest history snapshot for each appointment as of 12:01 AM on the visit date
    SELECT 
        AptNum,
        MAX(HistDateTStamp) AS max_hist
    FROM od_histappointments
    WHERE AptDateTime BETWEEN '$start 00:00:00' AND '$end 23:59:59'
      AND HistDateTStamp <= CONCAT(DATE(AptDateTime), ' 00:01:00')
    GROUP BY AptNum
) AS latest ON h.AptNum = latest.AptNum AND h.HistDateTStamp = latest.max_hist
LEFT JOIN od_procedure_logs AS pl ON h.AptNum = pl.AptNum
WHERE h.AptStatus = 1
GROUP BY DATE(h.AptDateTime)
ORDER BY `date` ASC
";

$res = DB::select($sql);

$totAppts = 0;
$totProd = 0;
foreach ($res as $r) {
    $totAppts += (int) $r->appts_at_1201_am;
    $totProd += (float) $r->sched_prod_1201_am;
    echo "Date: {$r->date} | Appts: {$r->appts_at_1201_am} | Sched Prod: $".number_format($r->sched_prod_1201_am, 2)."\n";
}

echo "--------------------------------------------------------\n";
echo "TOTAL August Sched Appts (12:01 AM): $totAppts\n";
echo 'TOTAL August Sched Prod (12:01 AM): $'.number_format($totProd, 2)."\n";
