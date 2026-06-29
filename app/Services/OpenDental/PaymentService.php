<?php

namespace App\Services\OpenDental;

use Illuminate\Support\Facades\Cache;


class PaymentService
{


    public function __construct(
        protected OpenDentalClient $client
    ){}




    public function all()
    {

        return Cache::remember(
            'od_payments',
            now()->addHours(12),

            fn() => $this->client->get(
                'payments'
            )
        );

    }





    public function byDate($start,$end)
    {


        $key = "od_payments_{$start}_{$end}";


        return Cache::remember(

            $key,

            now()->addHours(12),


            function() use($start,$end){


                return $this->client->get(

                    'payments',

                    [

                        'dateStart'=>$start,

                        'dateEnd'=>$end

                    ]

                );


            }

        );


    }





    public function find($id)
    {

        return $this->client->get(
            "payments/$id"
        );

    }



}