<?php

namespace App\Http\Controllers;

use App\Domain\Marketing\GoogleAds\GoogleAdsException;
use App\Domain\Marketing\GoogleAds\GoogleAdsOAuth;
use App\Domain\Marketing\GoogleAds\GoogleAdsSyncService;
use App\Models\MarketingAdConnection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Google Ads in the Growth Engine: connecting a Google login (no location involved). Assigning
 * its accounts and campaigns to locations is AdAccountController, shared with Meta Ads.
 * Campaign data is pulled by `marketing:google-ads-sync`, never during a web request, and
 * only for accounts that have a location.
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
            ['refresh_token' => $refreshToken, 'email' => $email] = $oauth->exchangeCode((string) $request->input('code'));
        } catch (GoogleAdsException $e) {
            return $this->back()->with('error', $e->getMessage());
        }

        // The same Google login connecting again refreshes its connection rather than adding one.
        $connection = ($email !== null
            ? MarketingAdConnection::query()->googleAds()->where('account_email', $email)->first()
            : null) ?? new MarketingAdConnection(['provider' => MarketingAdConnection::PROVIDER_GOOGLE_ADS]);

        $connection->forceFill([
            'account_email' => $email,
            'status' => MarketingAdConnection::STATUS_CONNECTED,
            'refresh_token' => $refreshToken,
            'connected_by' => $request->user()->id,
            'connected_at' => now(),
            'last_error' => null,
        ])->save();

        $who = $connection->label();

        // The connection is saved either way; a discovery failure is reported, not fatal.
        try {
            $found = $sync->discoverAccounts($connection);
        } catch (GoogleAdsException $e) {
            Log::warning('Google Ads connected but account discovery failed.', ['connection' => $connection->id, 'error' => $e->getMessage()]);
            $connection->forceFill(['last_error' => $e->getMessage()])->save();

            return $this->back()->with('error', "Google Ads connected ({$who}), but its accounts could not be listed: ".$e->getMessage());
        }

        return $this->back()->with('status', "Google Ads connected ({$who}). {$found} account(s) found. Assign each account to its location below; an account is synced only once it has one.");
    }

    /** Withdraw a login's access. Its accounts, campaigns and history are kept. */
    public function disconnect(MarketingAdConnection $connection, GoogleAdsOAuth $oauth): RedirectResponse
    {
        abort_unless($connection->provider === MarketingAdConnection::PROVIDER_GOOGLE_ADS, 404);

        $oauth->revoke($connection);

        $connection->forceFill([
            'status' => MarketingAdConnection::STATUS_DISCONNECTED,
            'refresh_token' => null,
            'last_error' => null,
        ])->save();

        return $this->back()->with('status', 'Google Ads disconnected ('.$connection->label().'). Imported campaign history has been kept.');
    }

    private function back(): RedirectResponse
    {
        return redirect()->route('marketing.integrations');
    }
}
