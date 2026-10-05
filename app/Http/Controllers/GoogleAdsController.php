<?php

namespace App\Http\Controllers;

use App\Domain\Marketing\GoogleAds\GoogleAdsException;
use App\Domain\Marketing\GoogleAds\GoogleAdsOAuth;
use App\Domain\Marketing\GoogleAds\GoogleAdsSyncService;
use App\Models\MarketingAdAccount;
use App\Models\MarketingAdConnection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Connecting Google Ads to the Growth Engine: the OAuth round trip, disconnecting, and
 * mapping an ad account to a location. Campaign data itself is pulled by
 * `marketing:google-ads-sync`, never during a web request.
 */
class GoogleAdsController extends Controller
{
    private const STATE_KEY = 'google_ads_oauth_state';

    /** Send the user to Google's consent screen. */
    public function connect(Request $request, GoogleAdsOAuth $oauth): RedirectResponse
    {
        if (! $oauth->isConfigured()) {
            return $this->back()->with('error', 'Google Ads is not configured. Missing on the server: '.implode(', ', $oauth->missingSettings()).'.');
        }

        // Ties the callback to this browser session, so a forged callback is rejected.
        $state = Str::random(40);
        $request->session()->put(self::STATE_KEY, $state);

        return redirect()->away($oauth->authorizationUrl($state));
    }

    /** Google sends the user back here with a one-time code (or an error). */
    public function callback(Request $request, GoogleAdsOAuth $oauth, GoogleAdsSyncService $sync): RedirectResponse
    {
        $expectedState = (string) $request->session()->pull(self::STATE_KEY, '');

        if ($request->filled('error')) {
            return $this->back()->with('error', 'Google Ads was not connected: '.$request->input('error').'.');
        }

        if ($expectedState === '' || ! hash_equals($expectedState, (string) $request->input('state'))) {
            return $this->back()->with('error', 'The Google Ads sign-in could not be verified. Please try connecting again.');
        }

        if (! $request->filled('code')) {
            return $this->back()->with('error', 'Google did not return an authorisation code.');
        }

        try {
            $refreshToken = $oauth->exchangeCode((string) $request->input('code'));
        } catch (GoogleAdsException $e) {
            return $this->back()->with('error', $e->getMessage());
        }

        $connection = MarketingAdConnection::updateOrCreate(
            ['provider' => MarketingAdConnection::PROVIDER_GOOGLE_ADS],
            [
                'status' => MarketingAdConnection::STATUS_CONNECTED,
                'refresh_token' => $refreshToken,
                'connected_by' => $request->user()->id,
                'connected_at' => now(),
                'last_error' => null,
            ]
        );

        // The connection is saved either way; a discovery failure is reported, not fatal.
        try {
            $accounts = $sync->discoverAccounts($connection);
        } catch (GoogleAdsException $e) {
            Log::warning('Google Ads connected but account discovery failed.', ['error' => $e->getMessage()]);
            $connection->forceFill(['last_error' => $e->getMessage()])->save();

            return $this->back()->with('error', 'Google Ads connected, but its accounts could not be listed: '.$e->getMessage());
        }

        return $this->back()->with('status', "Google Ads connected. {$accounts} account(s) found; campaign data arrives with the next sync.");
    }

    /** Withdraw access. Imported campaigns and stats are kept for reporting. */
    public function disconnect(GoogleAdsOAuth $oauth): RedirectResponse
    {
        $connection = MarketingAdConnection::googleAds();

        if ($connection !== null) {
            $oauth->revoke($connection);

            $connection->forceFill([
                'status' => MarketingAdConnection::STATUS_DISCONNECTED,
                'refresh_token' => null,
                'last_error' => null,
            ])->save();
        }

        return $this->back()->with('status', 'Google Ads disconnected. Imported campaign history has been kept.');
    }

    /** Map an ad account to a location, or stop syncing it. */
    public function updateAccount(Request $request, MarketingAdAccount $account): RedirectResponse
    {
        $data = $request->validate([
            'office_id' => ['nullable', 'integer', Rule::exists('offices', 'id')],
            'is_enabled' => ['sometimes', 'boolean'],
        ]);

        $account->forceFill([
            'office_id' => $data['office_id'] ?? null,
            'is_enabled' => $data['is_enabled'] ?? $account->is_enabled,
        ])->save();

        return $this->back()->with('status', "Updated {$account->name}.");
    }

    private function back(): RedirectResponse
    {
        return redirect()->route('marketing.integrations');
    }
}
