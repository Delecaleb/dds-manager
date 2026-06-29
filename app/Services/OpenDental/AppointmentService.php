<?php

namespace App\Services\OpenDental;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AppointmentService
{


    public function __construct(
        protected OpenDentalClient $client
    ){}



    /*
    |--------------------------------------------------------------------------
    | Get appointments by date range
    |--------------------------------------------------------------------------
    */

    public function byDate($start, $end)
    {


        $key = "od_appointments_{$start}_{$end}";


        return Cache::remember(

            $key,

            now()->addHours(12),


            function() use ($start,$end){

            Log::info("Fetching appointments from OpenDental API for date range: {$start} to {$end}");
                return $this->client->get(
                    'appointments',
                    [
                        'dateStart'=>$start,
                        'dateEnd'=>$end
                    ]
                );


            }

        );


    }




    /*
    |--------------------------------------------------------------------------
    | Get single appointment
    |--------------------------------------------------------------------------
    */

    public function find($id)
    {


        return $this->client->get(
            "appointments/$id"
        );


    }



}