<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$date = '2026-01-02';

$sql = "
SELECT 
    h.AptNum,
    h.PatNum,
    h.AptDateTime,
    h.AptStatus,
    h.ProcDescript,
    pl.ProcNum,
    pl.ProcFee,
    pl.ProcDate,
    pl.ProcStatus
FROM od_histappointments AS h
INNER JOIN (
    SELECT 
        AptNum,
        MAX(HistDateTStamp) AS max_hist
    FROM od_histappointments
    WHERE AptDateTime BETWEEN '2026-01-02 00:00:00' AND '2026-01-02 23:59:59'
      AND HistDateTStamp < CONCAT(DATE(AptDateTime), ' 07:00:00')
    GROUP BY AptNum
) AS latest ON h.AptNum = latest.AptNum AND h.HistDateTStamp = latest.max_hist
INNER JOIN od_procedure_logs AS pl ON h.AptNum = pl.AptNum
WHERE h.AptStatus = 1 AND pl.ProcFee > 0
";

$res = DB::select($sql);
echo "Appointments with production (> 0) from 7 AM snapshot:\n";
foreach ($res as $r) {
    echo "Apt {$r->AptNum} | Pat {$r->PatNum} | Fee \${$r->ProcFee} | Descript: {$r->ProcDescript} | ProcNum {$r->ProcNum} | ProcStatus {$r->ProcStatus}\n";
}
