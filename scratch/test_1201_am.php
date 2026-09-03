<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

// Test 12:01 AM cutoff on 2026-01-02
$date = '2026-01-02';

echo "=== TEST 12:01 AM SNAPSHOT FOR $date ===\n";

// Query 1: 12:01 AM appointment snapshot + all attached procedures
$sql1 = "
SELECT 
    DATE(h.AptDateTime) AS `date`,
    COUNT(DISTINCT h.AptNum) AS appt_count,
    COALESCE(SUM(pl.ProcFee), 0) AS sched_prod_all_procs
FROM od_histappointments AS h
INNER JOIN (
    SELECT 
        AptNum,
        MAX(HistDateTStamp) AS max_hist
    FROM od_histappointments
    WHERE AptDateTime BETWEEN '$date 00:00:00' AND '$date 23:59:59'
      AND HistDateTStamp <= CONCAT(DATE(AptDateTime), ' 00:01:00')
    GROUP BY AptNum
) AS latest ON h.AptNum = latest.AptNum AND h.HistDateTStamp = latest.max_hist
LEFT JOIN od_procedure_logs AS pl ON h.AptNum = pl.AptNum
WHERE h.AptStatus = 1
GROUP BY DATE(h.AptDateTime)
";

$res1 = DB::select($sql1);
print_r($res1);

// Query 2: 12:01 AM appointment snapshot + ONLY procedures entered BEFORE 12:01 AM on $date
$sql2 = "
SELECT 
    DATE(h.AptDateTime) AS `date`,
    COUNT(DISTINCT h.AptNum) AS appt_count,
    COALESCE(SUM(pl.ProcFee), 0) AS sched_prod_prior_entered_procs
FROM od_histappointments AS h
INNER JOIN (
    SELECT 
        AptNum,
        MAX(HistDateTStamp) AS max_hist
    FROM od_histappointments
    WHERE AptDateTime BETWEEN '$date 00:00:00' AND '$date 23:59:59'
      AND HistDateTStamp <= CONCAT(DATE(AptDateTime), ' 00:01:00')
    GROUP BY AptNum
) AS latest ON h.AptNum = latest.AptNum AND h.HistDateTStamp = latest.max_hist
LEFT JOIN od_procedure_logs AS pl ON h.AptNum = pl.AptNum 
  AND (pl.SecDateEntry < DATE(h.AptDateTime) OR pl.SecDateEntry IS NULL)
WHERE h.AptStatus = 1
GROUP BY DATE(h.AptDateTime)
";

$res2 = DB::select($sql2);
print_r($res2);
