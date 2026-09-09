<?php

$lines = explode("\n", file_get_contents('app/Http/Controllers/OperationsController.php'));
foreach ($lines as $i => $l) {
    if (preg_match('/DB::table\([\'"]([^\'"]+)[\'"]\)/', $l, $m)) {
        $table = $m[1];
        // look ahead 5 lines for office_id
        $context = implode(' ', array_slice($lines, $i, 6));
        $hasOfficeId = strpos($context, 'office_id') !== false;
        echo 'Line '.($i + 1).": Table '{$table}' => ".($hasOfficeId ? 'OK (has office_id)' : 'MISSING office_id')."\n";
        if (! $hasOfficeId) {
            echo '   Context: '.substr(trim($context), 0, 120)."\n";
        }
    }
}
