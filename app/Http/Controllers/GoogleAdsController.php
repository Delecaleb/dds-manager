<?php

namespace App\Http\Controllers;

use App\Domain\Marketing\AdCampaignReportService;
use App\Domain\Marketing\GoogleAds\GoogleAdsException;
use App\Domain\Marketing\GoogleAds\GoogleAdsOAuth;
use App\Domain\Marketing\GoogleAds\GoogleAdsSyncService;
use App\Domain\Support\ClinicRegistry;
use App\Models\MarketingAdAccount;
use App\Models\MarketingAdCampaign;
use App\Models\MarketingAdConnection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Google Ads in the Growth Engine: connecting a Google login (no location involved), assigning
 * each ad account to the locations it serves, and — for accounts shared by several locations —
 * assigning each campaign. Campaign data is pulled by `marketing:google-ads-sync`, never
 * during a web request, and only for accounts that have a location.
 */
class GoogleAdsController extends Controller
{
    private const STATE_KEY = 'google_ads_oauth_state';

    public function __construct(private readonly ClinicRegistry $clinics) {}

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
        $oauth->revoke($connection);

        $connection->forceFill([
            'status' => MarketingAdConnection::STATUS_DISCONNECTED,
            'refresh_token' => null,
            'last_error' => null,
        ])->save();

        return $this->back()->with('status', 'Google Ads disconnected ('.$connection->label().'). Imported campaign history has been kept.');
    }

    /** Assign an ad account to the locations it serves (none = stop syncing and reporting it). */
    public function updateAccount(Request $request, MarketingAdAccount $account): RedirectResponse
    {
        $locations = $this->clinics->locations();

        $data = $request->validate([
            'locations' => ['nullable', 'array'],
            'locations.*' => ['string', Rule::in(array_keys($locations))],
        ]);

        $chosen = array_values(array_intersect_key($locations, array_flip(array_unique($data['locations'] ?? []))));

        DB::transaction(fn () => $account->assignLocations($chosen));

        $message = match (count($chosen)) {
            0 => "{$account->name} is no longer assigned to a location; it will not be synced or reported.",
            1 => "{$account->name} assigned to {$chosen[0]->name}. Its campaigns count there.",
            default => "{$account->name} assigned to ".count($chosen).' locations. Assign each campaign to one of them; unassigned campaigns count for no location.',
        };

        return $this->back()->with('status', $message);
    }

    /** The campaigns of an account shared by several locations, to assign each one. */
    public function campaigns(MarketingAdAccount $account, AdCampaignReportService $report): View
    {
        $account->load('locations');
        $all = $this->clinics->locations();

        return view('marketing.integrations.campaigns', [
            'account' => $account,
            'accountLocations' => array_intersect_key($all, array_flip($account->locationKeys())),
            'campaigns' => $report->campaignsForAssignment($account),
            'spendDays' => AdCampaignReportService::ASSIGNMENT_SPEND_DAYS,
        ]);
    }

    /** Credit one campaign to one of its account's locations, or to none. */
    public function updateCampaign(Request $request, MarketingAdCampaign $campaign): RedirectResponse
    {
        $account = $campaign->account()->with('locations')->firstOrFail();
        $allowed = $account->locationKeys();

        $data = $request->validate([
            'location' => ['nullable', 'string', Rule::in($allowed)],
        ]);

        $location = filled($data['location'] ?? null) ? $this->clinics->locations()[$data['location']] ?? null : null;
        $campaign->assignLocation($location)->save();

        return redirect()->route('marketing.google-ads.accounts.campaigns', $account)
            ->with('status', $location === null ? "{$campaign->name} is not credited to any location." : "{$campaign->name} credited to {$location->name}.");
    }

    private function back(): RedirectResponse
    {
        return redirect()->route('marketing.integrations');
    }
}
