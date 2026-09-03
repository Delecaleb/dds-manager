<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$start = '2026-08-01';
$end = '2026-08-31';

$sql = "
SELECT 
    DATE(h.AptDateTime) AS `date`,
    COUNT(DISTINCT h.AptNum) AS appts_count,
    COALESCE(SUM(pl.ProcFee), 0) AS sched_prod_prior_procs
FROM od_histappointments AS h
INNER JOIN (
    SELECT 
        AptNum,
        MAX(HistDateTStamp) AS max_hist
    FROM od_histappointments
    WHERE AptDateTime BETWEEN '$start 00:00:00' AND '$end 23:59:59'
      AND HistDateTStamp <= CONCAT(DATE(AptDateTime), ' 00:01:00')
    GROUP BY AptNum
) AS latest ON h.AptNum = latest.AptNum AND h.HistDateTStamp = latest.max_hist
LEFT JOIN od_procedure_logs AS pl ON h.AptNum = pl.AptNum
  AND (pl.SecDateEntry < DATE(h.AptDateTime) OR pl.SecDateEntry IS NULL)
WHERE h.AptStatus = 1
GROUP BY DATE(h.AptDateTime)
ORDER BY `date` ASC
";

$res = DB::select($sql);
$totProd = 0;
foreach ($res as $r) {
    $totProd += (float) $r->sched_prod_prior_procs;
    echo "Date: {$r->date} | Appts: {$r->appts_count} | Sched Prod (Prior Procs): $".number_format($r->sched_prod_prior_procs, 2)."\n";
}
echo 'TOTAL August (Prior Procs): $'.number_format($totProd, 2)."\n";
