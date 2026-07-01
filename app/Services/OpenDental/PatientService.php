<?php

namespace App\Services\OpenDental;

class PatientService
{

    public function __construct(
        protected OpenDentalClient $client
    ) {
    }



    public function all($params = [])
    {

        return $this->client->get(
            'patients',
            $params
        );

    }

    public function find($id)
    {

        return $this->client->get(
            "patients/$id"
        );

    }

    public function findMany($ids)
    {
        return collect(
            $this->all()
        )
            ->whereIn(
                'PatNum',
                $ids
            )
            ->values();

    }

    public function allPaginated()
    {

        $patients = [];

        $offset = 0;

        while (true) {

            $response = $this->client->get(
                'patients',
                [
                    'Offset' => $offset
                ]
            );


            if (empty($response)) {
                break;
            }


            $patients = array_merge(
                $patients,
                $response
            );


            if (count($response) < 1000) {
                break;
            }


            $offset += 1000;

        }


        return $patients;

    }
}