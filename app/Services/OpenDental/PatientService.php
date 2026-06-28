<?php

namespace App\Services\OpenDental;

class PatientService
{

    public function __construct(
        protected OpenDentalClient $client
    ){}



    public function all()
    {

        return $this->client->get(
            'patients'
        );

    }

    public function find($id)
    {

        return $this->client->get(
            "patients/$id"
        );

    }

}