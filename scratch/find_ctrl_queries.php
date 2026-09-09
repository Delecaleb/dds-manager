<?php

$lines = explode("\n", file_get_contents('app/Http/Controllers/OperationsController.php.orig'));
foreach ($lines as $i => $l) {
    if (strpos($l, 'od_') !== false || strpos($l, 'Od') !== false) {
        echo ($i + 1).': '.trim($l)."\n";
    }
}
