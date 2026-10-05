<?php

namespace App\Domain\Marketing\GoogleAds;

use App\Models\MarketingAdConnection;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Thin REST client for the Google Ads API. It only knows how to authenticate a request and
 * run a GAQL query; what to ask for, and what the answer means, belongs to the services
 * that call it.
 */
class GoogleAdsClient
{
    private const BASE_URL = 'https://googleads.googleapis.com';

    public function __construct(private readonly GoogleAdsOAuth $oauth) {}

    /**
     * Customer ids the authorising Google user can reach directly (digits only).
     *
     * @return list<string>
     *
     * @throws GoogleAdsException
     */
    public function accessibleCustomerIds(MarketingAdConnection $connection): array
    {
        $response = $this->request($connection)->get($this->url('customers:listAccessibleCustomers'));

        $this->ensureSuccessful($response);

        return array_values(array_map(
            fn (string $resourceName) => self::digits($resourceName),
            (array) $response->json('resourceNames', [])
        ));
    }

    /**
     * Run a GAQL query against one customer and return every result row.
     *
     * @param  string|null  $loginCustomerId  manager account the customer is reached through
     * @return list<array<string, mixed>>
     *
     * @throws GoogleAdsException
     */
    public function search(MarketingAdConnection $connection, string $customerId, string $query, ?string $loginCustomerId = null): array
    {
        $response = $this->request($connection, $loginCustomerId)
            ->post($this->url('customers/'.self::digits($customerId).'/googleAds:searchStream'), ['query' => $query]);

        $this->ensureSuccessful($response);

        $rows = [];

        // searchStream answers with a list of batches, each holding a slice of the results.
        foreach ((array) $response->json() as $batch) {
            foreach ((array) ($batch['results'] ?? []) as $row) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /** "customers/123-456-7890" or "123-456-7890" → "1234567890". */
    public static function digits(string $customerId): string
    {
        return preg_replace('/\D+/', '', $customerId) ?? '';
    }

    private function request(MarketingAdConnection $connection, ?string $loginCustomerId = null): PendingRequest
    {
        $headers = ['developer-token' => (string) config('marketing.google_ads.developer_token')];

        if (filled($loginCustomerId)) {
            $headers['login-customer-id'] = self::digits((string) $loginCustomerId);
        }

        return Http::withToken($this->oauth->accessToken($connection))
            ->withHeaders($headers)
            ->acceptJson()
            ->connectTimeout(15)
            ->timeout((int) config('marketing.google_ads.timeout', 60))
            // Transient only: a 4xx is a real answer and is reported, not retried.
            ->retry(2, 1000, fn ($e) => ! $e instanceof RequestException || $e->response->serverError(), throw: false);
    }

    private function url(string $path): string
    {
        return self::BASE_URL.'/'.config('marketing.google_ads.api_version', 'v25').'/'.$path;
    }

    private function ensureSuccessful(Response $response): void
    {
        if ($response->successful()) {
            return;
        }

        throw new GoogleAdsException("Google Ads API error ({$response->status()}): ".$this->errorMessage($response));
    }

    /** Google nests the useful message; searchStream additionally wraps the error in a list. */
    private function errorMessage(Response $response): string
    {
        $body = $response->json();

        if (! is_array($body)) {
            return mb_substr($response->body(), 0, 500);
        }

        $error = $body['error'] ?? $body[0]['error'] ?? [];
        $detail = $error['details'][0]['errors'][0]['message'] ?? null;

        return (string) ($detail ?? $error['message'] ?? 'unknown error');
    }
}
