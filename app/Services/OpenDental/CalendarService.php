<?php

namespace App\Services\OpenDental;


class CalendarService
{


public function __construct(
    protected PatientService $patients,
    protected AppointmentService $appointments
){}



public function events($start,$end)
{


    $appointments = collect(
        $this->appointments->byDate($start,$end)
    );


    if($appointments->isEmpty()){

        return [];

    }



    $patientIds = $appointments
        ->pluck('PatNum')
        ->unique();



    $patients = collect(
        $this->patients->findMany($patientIds)
    );




    return $appointments->map(function($appointment) use($patients){



        $patient = $patients->firstWhere(
            'PatNum',
            $appointment['PatNum']
        );



        return [


            'id'=>$appointment['AptNum'],


            'title'=>
                ($patient['FName'] ?? '')
                .' '
                .($patient['LName'] ?? ''),



            'start'=>
                $appointment['AptDateTime'],



            'doctor'=>
                $appointment['provAbbr'],



            'procedure'=>
                $appointment['ProcDescript'],



            'status'=>
                $appointment['AptStatus'],



            'operator'=>
                $appointment['Op'],



            'clinic'=>
                $appointment['ClinicNum'],



            'pattern'=>
                $appointment['Pattern']


        ];



    });


}


}