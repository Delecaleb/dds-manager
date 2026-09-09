<?php

namespace App\Services\OpenDental;

use Illuminate\Support\Facades\Cache;

class TreatmentPlanService
{
    public function __construct(
        protected OpenDentalClient $client
    ) {}

    public function all()
    {

        return Cache::remember(

            'od_treatment_plans',

            now()->addHours(12),

            fn () => $this->client->get(
                'treatplans'
            )

        );

    }

    public function find($id)
    {

        return $this->client->get(
            'treatplans',
            [
                'PatNum' => $id,
            ]
        );

    }

    public function active()
    {
        return $this->client->get(

            'treatplans',

            [
                'TPStatus' => 'Active',
            ]

        );
    }

    public function attach($id)
    {
        return $this->client->get(

            'treatplanattaches',

            [
                'TreatPlanNum' => $id,
            ]
        );
    }
}
