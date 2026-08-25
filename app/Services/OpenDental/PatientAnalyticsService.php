<?php

namespace App\Services\OpenDental;

use App\Domain\Patient\PatientService;
use App\Domain\Patient\PatientVisitService;
use App\Domain\Production\ProductionService;
use App\Domain\Support\MetricFilter;
use App\Models\OdAppointment;

class PatientAnalyticsService
{
    public function __construct(
        private readonly PatientService $patients,
        private readonly ProductionService $production,
        private readonly PatientVisitService $patientVisits,
    ) {}

    public function getPatientAnalytics($start, $end)
    {
        $filter = new MetricFilter($start, $end);

        $scheduled = (new OdAppointment)->scheduledPatients($start, $end);

        // Patient visits = distinct patient-per-day among completed procedures
        $visited = $this->patientVisits->patientVisits($start, $end);

        // New patients: single source of truth from PatientVisitService
        $newPatientVisit = $this->patientVisits->newPatientCount($start, $end);

        $newPatientsScheduled = (new OdAppointment)->newPatientsScheduled($start, $end);

        // Average net production per patient visit.
        $patientAvgProduction = $visited > 0
            ? round($this->production->netProduction($filter) / $visited, 2)
            : 0;

        return [
            'patient_scheduled' => $scheduled,
            'patient_visits' => $visited,
            'patient_avg_production' => $patientAvgProduction,
            'new_patient_visit' => $newPatientVisit,
            'new_patients_scheduled' => $newPatientsScheduled,
        ];
    }
}
