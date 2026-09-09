<?php

namespace App\Services\OpenDental;

class AccountModuleService
{
    public function __construct(
        protected OpenDentalClient $client
    ) {}

    public function aging($patNum)
    {

        return $this->client->get(
            "accountmodules/$patNum/Aging"
        );

    }
}
