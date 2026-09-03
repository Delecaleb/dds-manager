<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$total = DB::select("
    SELECT 
        COALESCE(SUM(pl.ProcFee), 0) AS total_sched_prod
    FROM (
        SELECT DISTINCT AptNum
        FROM od_histappointments
        WHERE AptStatus = 1
          AND AptDateTime BETWEEN '2026-08-01 00:00:00' AND '2026-08-31 23:59:59'
    ) AS h
    INNER JOIN od_procedure_logs AS pl ON h.AptNum = pl.AptNum
");

echo 'Total Scheduled Production from od_histappointments (Status 1): '.number_format($total[0]->total_sched_prod, 2)."\n";

$daily = DB::select("
    SELECT 
        DATE(h.AptDateTime) AS `date`,
        COALESCE(SUM(pl.ProcFee), 0) AS daily_sched_prod,
        COUNT(DISTINCT h.AptNum) AS appt_count
    FROM (
        SELECT DISTINCT AptNum, DATE(AptDateTime) AS AptDateTime
        FROM od_histappointments
        WHERE AptStatus = 1
          AND AptDateTime BETWEEN '2026-08-01 00:00:00' AND '2026-08-31 23:59:59'
    ) AS h
    INNER JOIN od_procedure_logs AS pl ON h.AptNum = pl.AptNum
    GROUP BY DATE(h.AptDateTime)
    ORDER BY `date` ASC
");

echo "\nDaily Breakdown (First 5 days):\n";
foreach (array_slice($daily, 0, 5) as $row) {
    echo "  Date: {$row->date} | Sched Prod: $".number_format($row->daily_sched_prod, 2)." | Appts: {$row->appt_count}\n";
}
