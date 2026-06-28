<?php

namespace App\Services\OpenDental;

use Illuminate\Support\Facades\Http;

class OpenDentalClient
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('opendental.url');
    }


    public function get($endpoint, $params = [])
    {
        return Http::withHeaders([
            'Authorization' => 
                'ODFHIR '
                .config('opendental.developer_key')
                .'/'
                .config('opendental.customer_key'),

            'Content-Type'=>'application/json'

        ])
        ->timeout(30)
        ->get(
            $this->baseUrl.'/'.$endpoint,
            $params
        )
        ->throw()
        ->json();
    }



    public function post($endpoint, $data = [])
    {

        return Http::withHeaders([
            'Authorization'=>'ODFHIR '
                .config('opendental.developer_key')
                .'/'
                .config('opendental.customer_key'),

            'Content-Type'=>'application/json'

        ])
        ->post(
            $this->baseUrl.'/'.$endpoint,
            $data
        )
        ->throw()
        ->json();

    }
}