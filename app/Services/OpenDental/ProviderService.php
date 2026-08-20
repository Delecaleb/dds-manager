<?php

namespace App\Services\OpenDental;

class ProviderService
{
    public function __construct(
        protected OpenDentalClient $client
    ) {}

    public function all()
    {

        return $this->client->get(
            'providers'
        );

    }

    public function find($id)
    {

        return $this->client->get(
            "providers/$id"
        );

    }

    public function findMany($ids)
    {

        return collect(
            $this->all()
        )
            ->whereIn(
                'ProvNum',
                $ids
            )
            ->values();

    }
}
