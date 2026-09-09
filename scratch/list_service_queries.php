<?php

$file = file_get_contents('app/Services/OpenDental/OperationsAnalyticsService.php');
$lines = explode("\n", $file);

$currentFunc = '';
foreach ($lines as $i => $line) {
    if (preg_match('/(public|private|protected)\s+function\s+([a-zA-Z0-9_]+)/', $line, $m)) {
        $currentFunc = $m[2];
    }
    if (preg_match('/DB::table|Od[A-Z]/', $line)) {
        echo 'Line '.($i + 1)." [$currentFunc]: ".trim($line)."\n";
    }
}
