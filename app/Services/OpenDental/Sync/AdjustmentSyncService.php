<?php

namespace App\Services\OpenDental\Sync;


use App\Models\OdAdjustment;
use App\Services\OpenDental\OpenDentalClient;
use App\Services\OpenDental\PatientService;



class AdjustmentSyncService
{


    public function __construct(

        protected OpenDentalClient $client,

        protected PatientService $patients

    ) {
    }



    public function sync()
    {


        $patients = collect(
            $this->patients->all()
        );



        foreach ($patients as $patient) {



            $adjustments = $this->client->get(

                'adjustments',

                [

                    'PatNum' => $patient['PatNum']

                ]

            );




            foreach ($adjustments as $adj) {



                OdAdjustment::updateOrCreate(

                    [
                        'AdjNum' => $adj['AdjNum']

                    ],

                    [
                        'AdjDate' => $adj['AdjDate'],

                        'AdjAmt' => $adj['AdjAmt'],

                        'PatNum' => $adj['PatNum'],

                        'AdjType' => $adj['AdjType'],

                        'ProvNum' => $adj['ProvNum'],

                        'AdjNote' => $adj['AdjNote'],

                        'ProcDate' => $adj['ProcDate'],

                        'ProcNum' => $adj['ProcNum'],

                        'DateEntry' => $adj['DateEntry'],

                        'ClinicNum' => $adj['ClinicNum'],

                        'StatementNum' => $adj['StatementNum'],

                        'SecUserNumEntry' => $adj['SecUserNumEntry'],

                        'SecDateTEdit' => $adj['SecDateTEdit']

                    ]

                );

            }

        }

    }
}