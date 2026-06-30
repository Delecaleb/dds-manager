<?php

namespace App\Services\OpenDental;

use Illuminate\Support\Facades\Cache;


class FinancialService
{

    public function __construct(

        protected ProcedureService $procedures,

        protected PaymentService $payments,

        protected AdjustmentService $adjustments

    ) {
    }



    public function grossProduction($start, $end)
    {


        $procedures = collect(

            $this->procedures
                ->byDate($start, $end)

        );


        return $procedures

            ->filter(function ($proc) {

                return $proc['ProcStatus'] == "Complete";

            })

            ->sum('ProcFee');


    }




    public function adjustments($start, $end)
    {


        return collect(

            $this->adjustments
                ->byDate($start, $end)

        )

            ->sum('AdjAmt');


    }




    public function netProduction($start, $end)
    {


        return

            $this->grossProduction($start, $end)

            -

            $this->adjustments($start, $end);


    }




    public function collection($start, $end)
    {


        return collect(

            $this->payments
                ->byDate($start, $end)

        )->sum('PayAmt');
    }
}