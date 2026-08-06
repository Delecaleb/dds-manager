<?php

namespace App\Services\OpenDental;

use App\Models\Office;
use Illuminate\Support\Facades\Http;

class OpenDentalClient
{
    protected ?Office $office = null;

    public function __construct(?Office $office = null)
    {
        $this->office = $office;
    }

    public function forOffice(?Office $office): static
    {
        $this->office = $office;

        return $this;
    }

    protected function getTargetOffice(): ?Office
    {
        return $this->office ?? Office::getActiveOffice();
    }

    protected function request()
    {
        $office = $this->getTargetOffice();

        $developerKey = $office?->developer_key ?: config('opendental.developer_key');
        $customerKey = $office?->customer_key ?: config('opendental.customer_key');
        $baseUrl = $office?->api_url ?: config('opendental.url');

        return Http::withHeaders([
            'Authorization' => 'ODFHIR '.$developerKey.'/'.$customerKey,
            'Content-Type' => 'application/json',
        ])
            ->baseUrl($baseUrl)
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
