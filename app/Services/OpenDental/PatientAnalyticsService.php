<?php

namespace App\Services\OpenDental;

use App\Domain\Patient\PatientService;
use App\Domain\Support\MetricFilter;
use App\Models\OdAppointment;
use App\Models\OdProcedureLog;

class PatientAnalyticsService
{
    public function __construct(
        private readonly PatientService $patients,
    ) {}

    public function getPatientAnalytics($start, $end)
    {
        $scheduled = (new OdAppointment)->scheduledPatients($start, $end);

        $visited = (new OdProcedureLog)->patientVisits($start, $end);

        // New patients via the single source of truth (blueprint D8: first COMPLETED
        // procedure in period, using ['C','2'] so real 2010-2012 visits count).
        $newPatientVisit = $this->patients->newPatientCount(new MetricFilter($start, $end));

        $newPatientsScheduled = (new OdAppointment)->newPatientsScheduled($start, $end);

        $patientAvgProduction = (new OdProcedureLog)->avgProductionPerPatient($start, $end);


        return [
            'patient_scheduled' => $scheduled,
            'patient_visits' => $visited,
            'patient_avg_production' => $patientAvgProduction,
            'new_patient_visit' => $newPatientVisit,
            'new_patients_scheduled' => $newPatientsScheduled,
        ];
    }
}