<?php

namespace App\Services\OpenDental;


use Carbon\Carbon;
use App\Services\OpenDental\ProcedureService;
use App\Services\OpenDental\PaymentService;
use App\Services\OpenDental\TreatmentPlanService;
use Illuminate\Support\Facades\Log;



class DashboardAnalyticsService
{


public function __construct(

    protected ProcedureService $procedures,

    protected PaymentService $payments,

    protected TreatmentPlanService $treatments

){}




public function metrics()
{


return [


    "production" =>
        $this->production(),



    "collection" =>
        $this->collectionRate(),



    "treatment_acceptance" =>
        $this->treatmentAcceptance(),



    "aging" =>
        $this->aging()

];


}





/*
|--------------------------------------------------------------------------
| Gross Production MTD
|--------------------------------------------------------------------------
*/


private function production()
{


$start = now()
    ->startOfMonth()
    ->format('Y-m-d');


$end = now()
    ->endOfMonth()
    ->format('Y-m-d');


Log::info("Fetching procedures from OpenDental API for date range: {$start} to {$end}");
$procedures = collect(
    $this->procedures
    ->byDate($start,$end)
);



$total = $procedures

->filter(function($proc){

    return $proc['ProcStatus']=="C";

})

->sum('ProcFee');




return [


    "mtd" =>
        floatval($total),



    "target" =>
        150000,



    "percentage" =>
        round(
            ($total / 150000) * 100,
            1
        )


];


}







/*
|--------------------------------------------------------------------------
| Net Collection Rate
|--------------------------------------------------------------------------
*/


private function collectionRate()
{


$payments = collect(
    $this->payments->byDate(
        now()->startOfMonth(),
        now()->endOfMonth()
    )
);



$amountCollected = $payments->sum('PayAmt');

$production =
$this->production()['mtd'];




return [


"rate" =>

$production > 0

?

round(
($amountCollected/$production)*100,
1
)

:

0


];


}

/*
|--------------------------------------------------------------------------
| Treatment Acceptance
|--------------------------------------------------------------------------
*/


private function treatmentAcceptance()
{


$treatments = collect(
    $this->treatments->all()
);



$presented =
$treatments->sum('Fee');



$accepted =
$treatments

->where(
    'Status',
    'Accepted'
)

->sum('Fee');

return [


"rate" =>

$presented > 0

?

round(
($accepted/$presented)*100,
1
)

:

0


];

}

/*
|--------------------------------------------------------------------------
| Aging > 90 Days
|--------------------------------------------------------------------------
*/


private function aging()
{


$patients = collect(
    app(\App\Services\OpenDental\PatientService::class)
    ->all()
);



$total = $patients

->filter(function($patient){


return
isset($patient['Bal_30_60_90'])
&&
$patient['Bal_30_60_90'] > 90;


})

->sum('Bal_30_60_90');

return [

"over_90" =>
$total

];

}
}