<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

// Query for 2026-01-02 first as proof
$date = '2026-01-02';

$sql = "
SELECT 
    DATE(h.AptDateTime) AS `date`,
    COUNT(DISTINCT h.AptNum) AS scheduled_appts,
    COALESCE(SUM(pl.ProcFee), 0) AS scheduled_production
FROM od_histappointments AS h
INNER JOIN (
    -- Get latest history record for each appointment before 7:00 AM of the appointment date
    SELECT 
        AptNum,
        MAX(HistDateTStamp) AS max_hist
    FROM od_histappointments
    WHERE AptDateTime BETWEEN '2026-01-02 00:00:00' AND '2026-01-02 23:59:59'
      AND HistDateTStamp < CONCAT(DATE(AptDateTime), ' 07:00:00')
    GROUP BY AptNum
) AS latest ON h.AptNum = latest.AptNum AND h.HistDateTStamp = latest.max_hist
LEFT JOIN od_procedure_logs AS pl ON h.AptNum = pl.AptNum
WHERE h.AptStatus = 1
GROUP BY DATE(h.AptDateTime)
";

$res = DB::select($sql);
print_r($res);
