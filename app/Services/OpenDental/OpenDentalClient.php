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

    protected function request()
    {
        return Http::withHeaders([
            'Authorization' =>
                'ODFHIR '
                . config('opendental.developer_key')
                . '/'
                . config('opendental.customer_key'),
            'Content-Type' => 'application/json'
        ])
            ->baseUrl($this->baseUrl)
            ->timeout(120);
    }

    public function get($endpoint, array $params = [])
    {
        return $this->request()
            ->get($endpoint, $params)
            ->throw()
            ->json();
    }

    public function post($endpoint, array $data = [])
    {
        return $this->request()
            ->post($endpoint, $data)
            ->throw()
            ->json();
    }

    public function put($endpoint, array $data = [])
    {
        return $this->request()
            ->put($endpoint, $data)
            ->throw()
            ->json();
    }

    public function delete($endpoint, array $data = [])
    {
        return $this->request()
            ->delete($endpoint, $data)
            ->throw()
            ->json();
    }
}