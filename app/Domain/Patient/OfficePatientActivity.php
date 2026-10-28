<?php

namespace App\Domain\Patient;

/**
 * Patient-history counts behind the Office KPI cards (retention, active patients, new
 * patients, attrition, reactivation, recare). Produced by PatientActivityService.
 */
final class OfficePatientActivity
{
    public function __construct(
        public readonly int $active18m,
        public readonly int $active36mPrior,
        public readonly int $newPatients18m,
        public readonly int $newPatients,
        public readonly int $newPatientsWithFmx,
        public readonly int $attrition,
        public readonly int $reactivationList,
        public readonly int $inRecare,
    ) {}

    /** @return array<string, int> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
