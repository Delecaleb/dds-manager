<?php

namespace App\Services\OpenDental;

use Illuminate\Support\Facades\Cache;


class TreatmentPlanService
{

    public function __construct(
        protected OpenDentalClient $client
    ){}



    public function all()
    {

        return Cache::remember(

            'od_treatment_plans',

            now()->addHours(12),

            fn() => $this->client->get(
                'treatplans'
            )

        );

    }



    public function find($id)
    {

        return $this->client->get(
            "treatplans/$id"
        );

    }



}