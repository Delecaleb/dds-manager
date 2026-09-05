<?php

$file = file_get_contents('app/Services/OpenDental/OperationsAnalyticsService.php');
preg_match_all('/(public|private|protected)?\s*function\s+([a-zA-Z0-9_]+)\s*\(([^)]*)\)/', $file, $matches, PREG_SET_ORDER);
foreach ($matches as $m) {
    echo "{$m[1]} function {$m[2]}({$m[3]})\n";
}
