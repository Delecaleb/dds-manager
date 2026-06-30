<?php

namespace App\Services\OpenDental;


use App\Services\OpenDental\ProcedureService;
use App\Services\OpenDental\PaymentService;


class DashboardAnalyticsService
{


    public function __construct(

        protected ProcedureService $procedures,

        protected PaymentService $payments

    ) {
    }




    public function metrics()
    {


        return [

            "production" =>
                $this->production(),


            "collection" =>
                $this->collectionRate(),


            "patients" =>
                $this->patientMetrics(),


            "aging" =>
                $this->aging()


        ];


    }




    /*
    |--------------------------------------------------------------------------
    | Production MTD
    |--------------------------------------------------------------------------
    */


    private function production()
    {


        $start = now()
            ->startOfMonth()
            ->format('Y-m-d');


        $end = now()
            ->format('Y-m-d');



        $procedures = collect(

            $this->procedures
                ->byDate($start, $end)

        );



        $gross = $procedures

            ->filter(function ($item) {

                return $item['ProcStatus'] == "Complete";

            })

            ->sum('ProcFee');



        return [


            "gross" =>
                round($gross, 2),



            "net" =>

                // temporary
                // until adjustments API/query is available

                round($gross, 2)


        ];


    }






    /*
    |--------------------------------------------------------------------------
    | Collection Rate
    |--------------------------------------------------------------------------
    */


    private function collectionRate()
    {



        $start =
            now()
                ->startOfMonth()
                ->format('Y-m-d');


        $end =
            now()
                ->format('Y-m-d');



        $payments = collect(

            $this->payments
                ->byDate($start, $end)

        );



        $collected =
            $payments
                ->sum('PayAmt');



        $production =
            $this->production()['gross'];



        return [


            "collected" =>
                round($collected, 2),



            "rate" =>


                $production > 0

                ?

                round(
                    ($collected / $production) * 100,
                    1
                )

                :

                0


        ];


    }





    /*
    |--------------------------------------------------------------------------
    | Patient Metrics
    |--------------------------------------------------------------------------
    */


    private function patientMetrics()
    {


        $start =
            now()
                ->subMonths(24)
                ->format('Y-m-d');



        $procedures = collect(

            $this->procedures
                ->byDate($start, now()->format('Y-m-d'))

        );



        $activePatients =

            $procedures

                ->filter(function ($proc) {

                    return
                        $proc['ProcStatus'] == "Complete";

                })

                ->pluck('PatNum')

                ->unique()

                ->count();



        return [


            "active_patients" =>
                $activePatients


        ];

    }





    /*
    |--------------------------------------------------------------------------
    | Aging placeholder
    |--------------------------------------------------------------------------
    */


    private function aging()
    {


        return [


            "over_90" => 0,


            "message" =>
                "Requires ledger/query access"


        ];


    }


}