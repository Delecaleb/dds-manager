<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Services\OpenDental\OperationsAnalyticsService;
use Illuminate\Contracts\Console\Kernel;

$svc = app(OperationsAnalyticsService::class);
$perf = $svc->performance('2026-08-25', '2026-08-31');

echo "=== OPERATIONS PERFORMANCE (2026-08-25 to 2026-08-31) ===\n";
foreach ($perf['rows'] as $r) {
    echo "Date: {$r['date_raw']} | Sched Prod: {$r['sched_production']} | Act Prod: {$r['actual_production']} | Booked Prod: {$r['booked_production']}\n";
}
echo "TOTAL Sched Prod: {$perf['total']['sched_production']}\n";
