<?php

namespace App\Services\OpenDental;

use Illuminate\Support\Facades\Cache;

class ProcedureService
{
    public function __construct(
        protected OpenDentalClient $client
    ) {}

    public function all()
    {

        return Cache::remember(
            'od_procedures',

            now()->addHours(12),

            fn () => $this->client->get(
                'procedurelogs'
            )
        );

    }

    public function byDate($start, $end)
    {

        $key = "od_procedures_{$start}_{$end}";

        return Cache::remember(

            $key,

            now()->addHours(12),

            function () use ($start, $end) {

                return $this->client->get(

                    'procedurelogs',

                    [

                        'dateStart' => $start,

                        'dateEnd' => $end,

                    ]

                );

            }

        );

    }

    public function find($id)
    {

        return $this->client->get(
            "procedurelogs/$id"
        );

    }
}
