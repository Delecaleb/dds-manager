<?php

namespace App\Services\OpenDental;

use App\Models\OdAppointment;
use App\Models\OdProcedureLog;

class PatientAnalyticsService
{
    public function getPatientAnalytics($start, $end)
    {
        $scheduled = (new OdAppointment)->scheduledPatients($start, $end);

        $visited = (new OdProcedureLog)->patientVisits($start, $end);

        $newPatientVisit = (new OdProcedureLog)->newPatientVisits($start, $end);

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