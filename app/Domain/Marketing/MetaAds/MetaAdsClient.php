<?php

namespace App\Domain\Marketing\MetaAds;

use App\Models\MarketingAdConnection;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Thin client for the Meta Marketing API (Graph API). It only knows how to authenticate a
 * request with a system user token and read every page of a list; what to ask for, and
 * what the answer means, belongs to MetaAdsSyncService.
 */
class MetaAdsClient
{
    private const BASE_URL = 'https://graph.facebook.com';

    /** Safety stop for a list that keeps paging: 200 pages × the page size is far beyond any clinic. */
    private const MAX_PAGES = 200;

    /** Graph error codes that mean the token itself is no longer accepted. */
    private const TOKEN_ERROR_CODES = [102, 190];

    /**
     * Who a token belongs to. Used to check a pasted token before it is saved.
     *
     * @return array{id: string, name: string}
     *
     * @throws MetaAdsException
     */
    public function whoAmI(string $token): array
    {
        $response = $this->request($token)->get($this->url('me'), $this->params($token, ['fields' => 'id,name']));

        $this->ensureSuccessful($response);

        return ['id' => (string) $response->json('id'), 'name' => (string) $response->json('name', '')];
    }

    /**
     * Every row of a Graph list endpoint, following the "after" cursor page by page.
     *
     * @param  string  $path  e.g. "me/adaccounts" or "act_123/insights"
     * @param  array<string, mixed>  $params
     * @return list<array<string, mixed>>
     *
     * @throws MetaAdsException
     */
    public function all(MarketingAdConnection $connection, string $path, array $params): array
    {
        $token = $connection->refreshToken();

        if ($token === null) {
            throw new MetaAdsException('No readable Meta token is stored for this connection. Paste a new system user token.', true);
        }

        $rows = [];

        for ($page = 0; $page < self::MAX_PAGES; $page++) {
            $response = $this->request($token)->get($this->url($path), $this->params($token, $params));

            $this->ensureSuccessful($response);

            foreach ((array) $response->json('data', []) as $row) {
                $rows[] = (array) $row;
            }

            $after = $response->json('paging.cursors.after');

            if ($response->json('paging.next') === null || blank($after)) {
                return $rows;
            }

            $params['after'] = $after;
        }

        throw new MetaAdsException("Meta kept returning more pages for {$path}; stopped after ".self::MAX_PAGES.'.');
    }

    private function request(string $token): PendingRequest
    {
        return Http::withToken($token)
            ->acceptJson()
            ->connectTimeout(15)
            ->timeout((int) config('marketing.meta_ads.timeout', 60))
            // Transient only: a 4xx is a real answer and is reported, not retried.
            ->retry(2, 1000, fn ($e) => ! $e instanceof RequestException || $e->response->serverError(), throw: false);
    }

    /**
     * Query parameters, signed with appsecret_proof when an app secret is configured.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function params(string $token, array $params): array
    {
        $secret = (string) config('marketing.meta_ads.app_secret');

        if ($secret !== '') {
            $params['appsecret_proof'] = hash_hmac('sha256', $token, $secret);
        }

        return $params;
    }

    private function url(string $path): string
    {
        return self::BASE_URL.'/'.config('marketing.meta_ads.api_version', 'v24.0').'/'.ltrim($path, '/');
    }

    private function ensureSuccessful(Response $response): void
    {
        if ($response->successful()) {
            return;
        }

        $error = (array) $response->json('error', []);
        $message = (string) ($error['error_user_msg'] ?? $error['message'] ?? mb_substr($response->body(), 0, 500));
        $code = (int) ($error['code'] ?? 0);

        throw new MetaAdsException(
            "Meta API error ({$response->status()}".($code ? ", code {$code}" : '')."): {$message}",
            in_array($code, self::TOKEN_ERROR_CODES, true),
        );
    }
}
