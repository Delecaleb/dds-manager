<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

echo "=== APPOINTMENTS DATE RANGE IN DB ===\n";
$minDate = DB::table('od_appointments')->min('AptDateTime');
$maxDate = DB::table('od_appointments')->max('AptDateTime');
echo "Min AptDateTime: $minDate | Max AptDateTime: $maxDate\n";

$latestDates = DB::table('od_appointments')
    ->selectRaw('DATE(AptDateTime) as d, COUNT(*) as cnt')
    ->groupBy('d')
    ->orderByDesc('d')
    ->limit(10)
    ->get();

echo "\nLatest appointment dates:\n";
foreach ($latestDates as $ld) {
    echo "  Date: {$ld->d} (Count: {$ld->cnt})\n";
}

$procDates = DB::table('od_procedure_logs')
    ->selectRaw('DATE(ProcDate) as d, COUNT(*) as cnt')
    ->groupBy('d')
    ->orderByDesc('d')
    ->limit(10)
    ->get();

echo "\nLatest procedure dates:\n";
foreach ($procDates as $pd) {
    echo "  Date: {$pd->d} (Count: {$pd->cnt})\n";
}
