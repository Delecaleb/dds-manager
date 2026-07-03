<?php

namespace App\Services\OpenDental;

class QueryService
{
    public function __construct(
        protected OpenDentalClient $client
    ) {
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
                'SqlCommand' => $sql
            ]
        );
    }
}
