<?php

use App\Domain\Insurance\PayorService;
use App\Domain\Patient\PatientService;
use App\Domain\Patient\PatientVisitService;
use App\Domain\Production\ProductionService;
use App\Domain\Support\ClinicRegistry;
use App\Domain\TreatmentAcceptance\TreatmentAcceptanceService;

$classes = [
    PatientService::class,
    PatientVisitService::class,
    ProductionService::class,
    ClinicRegistry::class,
    PayorService::class,
    TreatmentAcceptanceService::class,
];

foreach ($classes as $class) {
    $ref = new ReflectionClass($class);
    echo "=== {$class} ===\n";
    foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        $params = [];
        foreach ($method->getParameters() as $p) {
            $params[] = ($p->getType() ? $p->getType().' ' : '').'$'.$p->getName().($p->isDefaultValueAvailable() ? ' = '.var_export($p->getDefaultValue(), true) : '');
        }
        echo "  public function {$method->getName()}(".implode(', ', $params).")\n";
    }
}
