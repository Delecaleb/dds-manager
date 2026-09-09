<?php

namespace App\Services\OpenDental;

use Illuminate\Support\Facades\Cache;

class AdjustmentService
{
    public function __construct(

        protected OpenDentalClient $client

    ) {}

    public function byDate($start, $end)
    {

        return Cache::remember(

            "od_adjustments_{$start}_{$end}",

            now()->addHours(12),

            fn () => $this->client->get(

                'adjustments',

                [

                    'DateStart' => $start,

                    'DateEnd' => $end,

                ]

            )

        );

    }
}
