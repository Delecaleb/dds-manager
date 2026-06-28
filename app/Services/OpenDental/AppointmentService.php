<?php

namespace App\Services\OpenDental;

class AppointmentService
{
    public function __construct(
        protected OpenDentalClient $client
    ){}

    public function all()
    {

        return $this->client->get(
            'appointments'
        );

    }

    public function find($id)
    {

        return $this->client->get(
            "appointments/$id"
        );

    }

    
}