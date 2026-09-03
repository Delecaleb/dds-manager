<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

echo "=== TABLES IN DATABASE ===\n";
$tables = DB::select('SHOW TABLES');
foreach ($tables as $t) {
    $tName = array_values((array) $t)[0];
    if (str_contains($tName, 'appt') || str_contains($tName, 'proc') || str_contains($tName, 'sched') || str_contains($tName, 'plan')) {
        echo "  Table: $tName\n";
    }
}
