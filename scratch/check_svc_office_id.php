<?php

$lines = explode("\n", file_get_contents('app/Services/OpenDental/OperationsAnalyticsService.php'));
$missing = [];
foreach ($lines as $i => $l) {
    if (preg_match('/DB::table\([\'"]([^\'"]+)[\'"]\)/', $l, $m)) {
        $table = $m[1];
        // look ahead 8 lines for office_id
        $context = implode(' ', array_slice($lines, $i, 8));
        $hasOfficeId = strpos($context, 'office_id') !== false;
        if (! $hasOfficeId) {
            $missing[] = [
                'line' => $i + 1,
                'table' => $table,
                'context' => trim($context),
            ];
            echo 'Line '.($i + 1).": Table '{$table}' => MISSING office_id\n";
            echo '   Context: '.substr(trim($context), 0, 140)."\n";
        }
    }
}

echo "\nTotal missing in OperationsAnalyticsService: ".count($missing)."\n";
