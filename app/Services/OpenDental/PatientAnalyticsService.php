<?php

namespace App\Services\OpenDental;

use App\Domain\Patient\PatientService;
use App\Domain\Production\ProductionService;
use App\Domain\Support\MetricFilter;
use App\Models\OdAppointment;

class PatientAnalyticsService
{
    public function __construct(
        private readonly PatientService $patients,
        private readonly ProductionService $production,
    ) {}

    public function getPatientAnalytics($start, $end)
    {
        $filter = new MetricFilter($start, $end);

        $scheduled = (new OdAppointment)->scheduledPatients($start, $end);

        // Patient visits = distinct patient-per-day among completed procedures (blueprint D7,
        // visit-events). Single source of truth uses ['C','2'] so status encoding can't hide
        // a visit.
        $visited = $this->production->patientVisits($filter);

        // New patients: first COMPLETED procedure in period (blueprint D8, ['C','2']).
        $newPatientVisit = $this->patients->newPatientCount($filter);

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
