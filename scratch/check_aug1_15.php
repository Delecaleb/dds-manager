<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Services\OpenDental\OperationsAnalyticsService;
use Illuminate\Contracts\Console\Kernel;

$svc = app(OperationsAnalyticsService::class);
$perf = $svc->performance('2026-08-01', '2026-08-15');

echo "=== OPERATIONS PERFORMANCE (2026-08-01 to 2026-08-15) ===\n";
foreach ($perf['rows'] as $r) {
    if ($r['sched_production'] > 0 || $r['actual_production'] > 0 || $r['sched_pts_visit'] > 0) {
        echo "Date: {$r['date_raw']} | Sched Prod: {$r['sched_production']} | Act Prod: {$r['actual_production']} | Sched Pts: {$r['sched_pts_visit']} | Act Pts: {$r['actual_pts_visit']}\n";
    }
}
echo "TOTAL Sched Prod: {$perf['total']['sched_production']}\n";
