<?php

namespace App\Services\OpenDental;

use Illuminate\Support\Facades\Cache;


class PaymentService
{


    public function __construct(
        protected OpenDentalClient $client
    ) {
    }




    public function all()
    {

        return Cache::remember(
            'od_payments',
            now()->addHours(1),

            fn() => $this->client->get(
                'payments'
            )
        );

    }





    public function byDate($start, $end)
    {

        return $this->client->get(

            'payments',

            [
                'DateEntry' => $start,          //no start - end but from the DateEntry upward
            ]

        );
    }





    public function find($id)
    {

        return $this->client->get(
            "payments/$id"
        );

    }



}