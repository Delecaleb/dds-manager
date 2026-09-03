<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Http\Controllers\OperationsController;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;

$controller = app(OperationsController::class);
$request = Request::create('/operations/drilldown', 'GET', [
    'metric' => 'sched_production',
    'start_date' => '2026-01-02',
    'end_date' => '2026-01-02',
]);

$response = $controller->drilldown($request);
$data = $response->getData();

echo "=== DRILLDOWN OUTPUT FOR 2026-01-02 ===\n";
echo 'Title: '.$data['title']."\n";
echo 'Rows count: '.count($data['rows'])."\n";
foreach ($data['rows'] as $r) {
    $pat = is_array($r['patient']) ? $r['patient']['label'] : $r['patient'];
    $prod = number_format($r['production'], 2);
    echo "  Pat {$r['pat_id']} ($pat) | Appt {$r['appt_id']} | Status {$r['status']} | Prod: $ $prod\n";
}
echo 'Total Production: $ '.number_format($data['totals']['production'] ?? 0, 2)."\n";
