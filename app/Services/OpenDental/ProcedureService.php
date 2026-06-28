<?php

namespace App\Services\OpenDental;

class ProcedureService
{

    public function __construct(
        protected OpenDentalClient $client
    ){} 

    public function all()
    {

        return $this->client->get(
            'procedurelogs'
        );

    }

    public function find($id)
    {

        return $this->client->get(
            "procedurelogs/$id"
        );

    }
}