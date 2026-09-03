<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$start = '2026-08-01';
$end = '2026-08-31';

echo "=== TESTING 7:00 AM SNAPSHOT QUERY FOR AUGUST 2026 ===\n";

$sql = "
SELECT 
    DATE(h.AptDateTime) AS `date`,
    COUNT(DISTINCT h.AptNum) AS morning_appts,
    COALESCE(SUM(pl.ProcFee), 0) AS morning_sched_production
FROM od_histappointments AS h
INNER JOIN (
    -- Subquery: find the latest snapshot of each appointment before 7:00 AM on the appointment date
    SELECT 
        AptNum,
        MAX(HistDateTStamp) AS max_hist
    FROM od_histappointments
    WHERE AptDateTime BETWEEN '$start 00:00:00' AND '$end 23:59:59'
      AND HistDateTStamp < CONCAT(DATE(AptDateTime), ' 07:00:00')
    GROUP BY AptNum
) AS latest ON h.AptNum = latest.AptNum AND h.HistDateTStamp = latest.max_hist
LEFT JOIN od_procedure_logs AS pl ON h.AptNum = pl.AptNum
WHERE h.AptStatus = 1
GROUP BY DATE(h.AptDateTime)
ORDER BY `date` ASC
";

$res = DB::select($sql);

$totalProd = 0;
$totalAppts = 0;
foreach ($res as $r) {
    $totalProd += (float) $r->morning_sched_production;
    $totalAppts += (int) $r->morning_appts;
    echo "Date: {$r->date} | Appts: {$r->morning_appts} | Sched Prod: $".number_format($r->morning_sched_production, 2)."\n";
}

echo "--------------------------------------------------------\n";
echo 'TOTAL August Sched Prod (7 AM snapshot): $'.number_format($totalProd, 2)."\n";
echo 'TOTAL August Sched Appts (7 AM snapshot): '.number_format($totalAppts)."\n";
