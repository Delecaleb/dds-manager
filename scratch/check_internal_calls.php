<?php

$file = file_get_contents('app/Services/OpenDental/OperationsAnalyticsService.php');

$methodsWithOfficeId = [
    'officeRows',
    'productionMetrics',
    'newPatientMetrics',
    'patientRetentionMetrics',
    'activePatientMetrics',
    'sumByClinic',
    'collectionsByClinic',
    'payorRows',
    'newPatientsByPayor',
    'cancellationRows',
    'countAppointments',
    'productionDetailRows',
    'pdGroupedProduction',
    'pdGroupedSum',
    'pdGroupedCollections',
    'pdGroupedNewPatients',
    'providerRows',
    'sumByClinicProvider',
    'collectionsByClinicProvider',
    'scheduledHoursByClinicProvider',
    'newPatientsByClinicProvider',
    'serviceRows',
    'calculateTrendMetricBuckets',
    'complianceRows',
];

$lines = explode("\n", $file);
foreach ($lines as $i => $line) {
    foreach ($methodsWithOfficeId as $m) {
        if (preg_match('/\$this->'.$m.'\(([^)]*)\)/', $line, $match)) {
            $args = array_map('trim', explode(',', $match[1]));
            $lastArg = end($args);
            if ($lastArg !== '$officeId') {
                echo 'Line '.($i + 1).": \$this->{$m}(".$match[1].") => MISSING \$officeId\n";
            }
        }
    }
}
