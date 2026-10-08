<?php

namespace App\Domain\Marketing\GoogleAds;

use App\Models\MarketingAdConnection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * The OAuth 2.0 side of Google Ads: the consent URL, trading the returned code for a
 * refresh token, and minting the short-lived access tokens every API call needs.
 *
 * The refresh token is the only long-lived secret. It lives encrypted on the connection;
 * access tokens are cached until just before they expire and never stored on a model.
 */
class GoogleAdsOAuth
{
    public const SCOPE = 'https://www.googleapis.com/auth/adwords';

    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const REVOKE_URL = 'https://oauth2.googleapis.com/revoke';

    /**
     * Environment variables still to be set before a connection can be made.
     *
     * @return list<string>
     */
    public function missingSettings(): array
    {
        $required = [
            'GOOGLE_ADS_CLIENT_ID' => 'client_id',
            'GOOGLE_ADS_CLIENT_SECRET' => 'client_secret',
            'GOOGLE_ADS_DEVELOPER_TOKEN' => 'developer_token',
        ];

        return array_keys(array_filter($required, fn (string $key) => blank(config("marketing.google_ads.{$key}"))));
    }

    public function isConfigured(): bool
    {
        return $this->missingSettings() === [];
    }

    /** The callback Google sends the user back to; must be registered on the OAuth client. */
    public function redirectUri(): string
    {
        return (string) (config('marketing.google_ads.redirect_uri') ?: route('marketing.google-ads.callback'));
    }

    public function authorizationUrl(string $state): string
    {
        return self::AUTH_URL.'?'.http_build_query([
            'client_id' => config('marketing.google_ads.client_id'),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => self::SCOPE,
            // offline + consent: without both Google omits the refresh token on a repeat grant.
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);
    }

    /**
     * Trade the authorisation code from the callback for a refresh token.
     *
     * @throws GoogleAdsException
     */
    public function exchangeCode(string $code): string
    {
        $response = Http::asForm()->timeout($this->timeout())->post(self::TOKEN_URL, [
            'code' => $code,
            'client_id' => config('marketing.google_ads.client_id'),
            'client_secret' => config('marketing.google_ads.client_secret'),
            'redirect_uri' => $this->redirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        if ($response->failed()) {
            throw new GoogleAdsException('Google rejected the authorisation code: '.$this->describe($response->json()));
        }

        $refreshToken = (string) $response->json('refresh_token', '');

        if ($refreshToken === '') {
            throw new GoogleAdsException('Google did not return a refresh token. Remove this app under the Google account\'s third-party access and connect again.');
        }

        // The access token that came with it is not kept: the connection it belongs to does
        // not exist yet, and the first API call mints one from the refresh token anyway.
        return $refreshToken;
    }

    /**
     * A valid access token for the connection, refreshed when the cached one has expired.
     *
     * @throws GoogleAdsException
     */
    public function accessToken(MarketingAdConnection $connection): string
    {
        $cached = Cache::get($this->cacheKey($connection));

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        if (! $connection->isUsable()) {
            throw new GoogleAdsException('Google Ads is not connected.');
        }

        $response = Http::asForm()->timeout($this->timeout())->post(self::TOKEN_URL, [
            'client_id' => config('marketing.google_ads.client_id'),
            'client_secret' => config('marketing.google_ads.client_secret'),
            'refresh_token' => $connection->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->failed()) {
            $message = 'Google refused to refresh the access token: '.$this->describe($response->json());

            // invalid_grant = the token was revoked or expired; only reconnecting fixes it.
            if ($response->json('error') === 'invalid_grant') {
                $connection->forceFill([
                    'status' => MarketingAdConnection::STATUS_ERROR,
                    'last_error' => $message.' Reconnect Google Ads.',
                ])->save();
            }

            throw new GoogleAdsException($message);
        }

        $accessToken = (string) $response->json('access_token');
        $this->remember($connection, $accessToken, (int) $response->json('expires_in', 3600));

        return $accessToken;
    }

    /** Withdraw the grant at Google. Best effort: a failure still leaves the local token removable. */
    public function revoke(MarketingAdConnection $connection): void
    {
        if (filled($connection->refresh_token)) {
            Http::asForm()->timeout($this->timeout())->post(self::REVOKE_URL, ['token' => $connection->refresh_token]);
        }

        Cache::forget($this->cacheKey($connection));
    }

    private function remember(MarketingAdConnection $connection, string $accessToken, int $expiresIn): void
    {
        // Expire a minute early so a token is never used in its last seconds.
        Cache::put($this->cacheKey($connection), $accessToken, max(60, $expiresIn - 60));
    }

    /** One cached token per connection: every location holds its own grant. */
    private function cacheKey(MarketingAdConnection $connection): string
    {
        return "marketing:{$connection->provider}:{$connection->getKey()}:access_token";
    }

    private function timeout(): int
    {
        return (int) config('marketing.google_ads.timeout', 60);
    }

    /** @param  mixed  $body */
    private function describe($body): string
    {
        if (! is_array($body)) {
            return 'no details returned.';
        }

        return trim(($body['error'] ?? 'error').': '.($body['error_description'] ?? ''), ': ').'.';
    }
}
