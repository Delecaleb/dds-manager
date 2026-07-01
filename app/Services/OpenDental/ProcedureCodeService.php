<?php

namespace App\Services\OpenDental;


class ProcedureCodeService
{


    public function __construct(
        protected OpenDentalClient $client
    ) {
    }



    public function find($id)
    {

        return $this->client->get(
            "procedurecodes/$id"
        );

    }


}