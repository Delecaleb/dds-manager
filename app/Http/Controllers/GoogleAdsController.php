<?php

namespace App\Http\Controllers;

use App\Domain\Marketing\GoogleAds\GoogleAdsException;
use App\Domain\Marketing\GoogleAds\GoogleAdsOAuth;
use App\Domain\Marketing\GoogleAds\GoogleAdsSyncService;
use App\Domain\Support\ClinicRegistry;
use App\Models\MarketingAdAccount;
use App\Models\MarketingAdConnection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Connecting Google Ads to one location of the Growth Engine: the OAuth round trip (which
 * carries the location through the session), disconnecting, and re-mapping an account or
 * a connection to a location. Campaign data itself is pulled by `marketing:google-ads-sync`,
 * never during a web request.
 */
class GoogleAdsController extends Controller
{
    private const STATE_KEY = 'google_ads_oauth_state';

    private const LOCATION_KEY = 'google_ads_oauth_location';

    public function __construct(private readonly ClinicRegistry $clinics) {}

    /** Send the user to Google's consent screen, remembering which location is connecting. */
    public function connect(Request $request, GoogleAdsOAuth $oauth): RedirectResponse
    {
        if (! $oauth->isConfigured()) {
            return $this->back()->with('error', 'Google Ads is not configured. Missing on the server: '.implode(', ', $oauth->missingSettings()).'.');
        }

        $locations = $this->clinics->locations();
        $key = (string) $request->query('location', '');

        if (! isset($locations[$key])) {
            return $this->back()->with('error', 'Choose which location is connecting its Google Ads account.');
        }

        // Ties the callback to this browser session, so a forged callback is rejected.
        $state = Str::random(40);
        $request->session()->put(self::STATE_KEY, $state);
        $request->session()->put(self::LOCATION_KEY, $key);

        return redirect()->away($oauth->authorizationUrl($state));
    }

    /** Google sends the user back here with a one-time code (or an error). */
    public function callback(Request $request, GoogleAdsOAuth $oauth, GoogleAdsSyncService $sync): RedirectResponse
    {
        $expectedState = (string) $request->session()->pull(self::STATE_KEY, '');
        $locationKey = (string) $request->session()->pull(self::LOCATION_KEY, '');

        if ($request->filled('error')) {
            return $this->back()->with('error', 'Google Ads was not connected: '.$request->input('error').'.');
        }

        if ($expectedState === '' || ! hash_equals($expectedState, (string) $request->input('state'))) {
            return $this->back()->with('error', 'The Google Ads sign-in could not be verified. Please try connecting again.');
        }

        $location = $this->clinics->locations()[$locationKey] ?? null;
        if ($location === null) {
            return $this->back()->with('error', 'The location this connection was started for no longer exists. Please try connecting again.');
        }

        if (! $request->filled('code')) {
            return $this->back()->with('error', 'Google did not return an authorisation code.');
        }

        try {
            $refreshToken = $oauth->exchangeCode((string) $request->input('code'));
        } catch (GoogleAdsException $e) {
            return $this->back()->with('error', $e->getMessage());
        }

        $connection = MarketingAdConnection::forLocation(MarketingAdConnection::PROVIDER_GOOGLE_ADS, $location)
            ?? (new MarketingAdConnection(['provider' => MarketingAdConnection::PROVIDER_GOOGLE_ADS]))->assignLocation($location);

        $connection->forceFill([
            'status' => MarketingAdConnection::STATUS_CONNECTED,
            'refresh_token' => $refreshToken,
            'connected_by' => $request->user()->id,
            'connected_at' => now(),
            'last_error' => null,
        ])->save();

        // The connection is saved either way; a discovery failure is reported, not fatal.
        try {
            $accounts = $sync->discoverAccounts($connection);
        } catch (GoogleAdsException $e) {
            Log::warning('Google Ads connected but account discovery failed.', ['location' => $locationKey, 'error' => $e->getMessage()]);
            $connection->forceFill(['last_error' => $e->getMessage()])->save();

            return $this->back()->with('error', "Google Ads connected for {$location->name}, but its accounts could not be listed: ".$e->getMessage());
        }

        return $this->back()->with('status', "Google Ads connected for {$location->name}. {$accounts} account(s) found; campaign data arrives with the next sync.");
    }

    /** Withdraw one location's access. Imported campaigns and stats are kept for reporting. */
    public function disconnect(MarketingAdConnection $connection, GoogleAdsOAuth $oauth): RedirectResponse
    {
        $oauth->revoke($connection);

        $connection->forceFill([
            'status' => MarketingAdConnection::STATUS_DISCONNECTED,
            'refresh_token' => null,
            'last_error' => null,
        ])->save();

        return $this->back()->with('status', 'Google Ads disconnected. Imported campaign history has been kept.');
    }

    /** Assign a connection made before locations existed to a location. */
    public function updateConnection(Request $request, MarketingAdConnection $connection): RedirectResponse
    {
        $locations = $this->clinics->locations();

        $data = $request->validate([
            'location' => ['nullable', 'string', Rule::in(array_keys($locations))],
        ]);

        $location = $locations[$data['location'] ?? ''] ?? null;
        $connection->assignLocation($location)->save();

        // Accounts that were never mapped on their own follow the connection.
        $connection->accounts()->whereNull('office_id')->update([
            'office_id' => $location?->officeId,
            'clinic_num' => $location?->clinicNum,
        ]);

        return $this->back()->with('status', $location === null ? 'Connection is no longer assigned to a location.' : "Connection assigned to {$location->name}.");
    }

    /** Map an ad account to a location (a ClinicRegistry key), or stop syncing it. */
    public function updateAccount(Request $request, MarketingAdAccount $account): RedirectResponse
    {
        $locations = $this->clinics->locations();

        $data = $request->validate([
            'location' => ['nullable', 'string', Rule::in(array_keys($locations))],
            'is_enabled' => ['sometimes', 'boolean'],
        ]);

        $account->assignLocation($locations[$data['location'] ?? ''] ?? null);
        $account->is_enabled = (bool) ($data['is_enabled'] ?? $account->is_enabled);
        $account->save();

        return $this->back()->with('status', "Updated {$account->name}.");
    }

    private function back(): RedirectResponse
    {
        return redirect()->route('marketing.integrations');
    }
}
