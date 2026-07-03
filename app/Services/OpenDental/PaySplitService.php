<?php

namespace App\Services\OpenDental;

class PaySplitService
{
    public function __construct(
        protected OpenDentalClient $client
    ) {
    }

    public function all(array $params = [])
    {
        return $this->client->get(
            'paysplits',
            $params
        );
    }
}