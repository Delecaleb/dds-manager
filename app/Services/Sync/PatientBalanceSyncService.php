<?php

namespace App\Services\Sync;


use App\Models\OdPatientBalance;
use App\Models\OdPatient;
use App\Services\OpenDental\AccountModuleService;
use Illuminate\Support\Facades\Log;


class PatientBalanceSyncService
{


    public function __construct(
        protected AccountModuleService $account
    ) {
    }



    public function sync()
    {


        OdPatient::select('PatNum')
            ->chunk(100, function ($patients) {


                foreach ($patients as $patient) {


                    $this->syncPatient(
                        $patient->PatNum
                    );


                }


            });



    }



    private function syncPatient($patNum)
    {


        try {


            $balance = $this->account
                ->aging($patNum);



            OdPatientBalance::updateOrCreate(

                [
                    'PatNum' => $patNum
                ],


                [


                    'Bal_0_30' =>
                        $balance['Bal_0_30'] ?? 0,


                    'Bal_31_60' =>
                        $balance['Bal_31_60'] ?? 0,


                    'Bal_61_90' =>
                        $balance['Bal_61_90'] ?? 0,


                    'BalOver90' =>
                        $balance['BalOver90'] ?? 0,


                    'Total' =>
                        $balance['Total'] ?? 0,


                    'InsEst' =>
                        $balance['InsEst'] ?? 0,


                    'EstBal' =>
                        $balance['EstBal'] ?? 0,


                    'PatEstBal' =>
                        $balance['PatEstBal'] ?? 0,


                    'Unearned' =>
                        $balance['Unearned'] ?? 0


                ]

            );


        } catch (\Exception $e) {


            Log::error(
                "Patient balance sync failed",
                [
                    'PatNum' => $patNum,
                    'error' => $e->getMessage()
                ]
            );


        }


    }



}