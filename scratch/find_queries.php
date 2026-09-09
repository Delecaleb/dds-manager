<?php

$file = file_get_contents('app/Services/OpenDental/OperationsAnalyticsService.php');
$lines = explode("\n", $file);

foreach ($lines as $i => $line) {
    if (preg_match('/DB::table|Od[A-Z]|->join|->leftJoin|where\(|whereIn\(|whereBetween\(/', $line)) {
        echo ($i + 1).': '.trim($line)."\n";
    }
}
