<?php

namespace App\Services\OpenDental;

use App\Models\Office;

class QueryService
{
    public function __construct(
        protected OpenDentalClient $client
    ) {}

    public function forOffice(?Office $office): static
    {
        $this->client->forOffice($office);

        return $this;
    }

    public function getClient(): OpenDentalClient
    {
        return $this->client;
    }

    // public function shortQuery(string $sql, int $offset = 0)
    // {
    //     return $this->client->put('queries/ShortQuery?Offset=' . $offset, [
    //         'SqlCommand' => $sql
    //     ]);
    // }

    public function shortQuery(string $sql)
    {
        return $this->client->put(
            'queries/ShortQuery',
            [
                'SqlCommand' => $sql,
            ]
        );
    }
}
