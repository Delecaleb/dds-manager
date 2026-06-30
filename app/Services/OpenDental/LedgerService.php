<?php

namespace App\Services\OpenDental;

use Illuminate\Support\Facades\Cache;


class LedgerService
{

    public function __construct(
        protected OpenDentalClient $client
    ) {
    }



    /**
     * Get all ledger transactions
     */
    public function all()
    {

        return Cache::remember(

            'od_ledger',

            now()->addHours(12),

            fn() => $this->client->get(
                'ledgers'
            )

        );

    }




    /**
     * Get ledger transactions by date range
     */
    public function byDate($start, $end)
    {


        $key = "od_ledger_{$start}_{$end}";


        return Cache::remember(

            $key,

            now()->addHours(12),


            function () use ($start, $end) {

                return $this->client->get(

                    'ledgers',

                    [

                        'dateStart' => $start,

                        'dateEnd' => $end

                    ]

                );

            }

        );


    }




    /**
     * Get ledger for one patient
     */
    public function byPatient($patientId)
    {

        return $this->client->get(

            'ledgers',

            [

                'PatNum' => $patientId

            ]

        );

    }


}