<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
foreach ($tables as $t) {
    $tName = array_values((array) $t)[0];
    if (str_starts_with($tName, 'od_') || str_starts_with($tName, 'open_')) {
        $count = DB::table($tName)->count();
        echo "  $tName ($count rows)\n";
    }
}
