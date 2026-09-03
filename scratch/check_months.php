<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$dates = DB::table('od_appointments')
    ->selectRaw('DATE_FORMAT(AptDateTime, "%Y-%m") as ym, COUNT(*) as cnt')
    ->groupBy('ym')
    ->orderByDesc('ym')
    ->get();

echo "=== APPOINTMENTS BY MONTH ===\n";
foreach ($dates as $d) {
    echo "  {$d->ym}: {$d->cnt} appts\n";
}
