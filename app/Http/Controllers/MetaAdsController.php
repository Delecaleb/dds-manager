<?php

namespace App\Http\Controllers;

use App\Domain\Marketing\MetaAds\MetaAdsClient;
use App\Domain\Marketing\MetaAds\MetaAdsException;
use App\Domain\Marketing\MetaAds\MetaAdsSyncService;
use App\Models\MarketingAdConnection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Meta Ads in the Growth Engine: connecting with a Business Manager system user token (no
 * OAuth, so no Meta app review). The token is checked with Meta before it is stored, and
 * stored encrypted. Assigning accounts and campaigns to locations is AdAccountController,
 * shared with Google Ads. Campaign data is pulled by `marketing:meta-ads-sync`, never during
 * a web request, and only for accounts that have a location.
 */
class MetaAdsController extends Controller
{
    /** Check a pasted system user token, store it, and list the ad accounts it can see. */
    public function store(Request $request, MetaAdsClient $client, MetaAdsSyncService $sync): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        $token = trim($data['token']);

        try {
            $user = $client->whoAmI($token);
        } catch (MetaAdsException $e) {
            return $this->back()->with('error', 'Meta did not accept that token: '.$e->getMessage());
        }

        // The same system user connecting again replaces its token rather than adding a login.
        $identity = trim(($user['name'] !== '' ? $user['name'] : 'System user')." ({$user['id']})");
        $connection = MarketingAdConnection::query()->metaAds()
            ->where('account_email', 'like', '% ('.$user['id'].')')
            ->first() ?? new MarketingAdConnection(['provider' => MarketingAdConnection::PROVIDER_META_ADS]);

        $connection->forceFill([
            'account_email' => mb_substr($identity, 0, 255),
            'status' => MarketingAdConnection::STATUS_CONNECTED,
            'refresh_token' => $token,
            'connected_by' => $request->user()->id,
            'connected_at' => now(),
            'last_error' => null,
        ])->save();

        // The connection is saved either way; a discovery failure is reported, not fatal.
        try {
            $found = $sync->discoverAccounts($connection);
        } catch (MetaAdsException $e) {
            Log::warning('Meta Ads connected but account discovery failed.', ['connection' => $connection->id, 'error' => $e->getMessage()]);
            $connection->forceFill(['last_error' => $e->getMessage()])->save();

            return $this->back()->with('error', "Meta Ads connected ({$identity}), but its ad accounts could not be listed: ".$e->getMessage());
        }

        $hint = $found === 0
            ? ' Give the system user access to the ad accounts in Business Settings → Users → System users → Assign assets, then save the token again.'
            : ' Assign each account to its location below; an account is synced only once it has one.';

        return $this->back()->with('status', "Meta Ads connected ({$identity}). {$found} ad account(s) found.".$hint);
    }

    /**
     * Forget a system user's token. Its accounts, campaigns and history are kept. The token
     * itself stays valid at Meta until it is revoked there (Business Settings → System users).
     */
    public function disconnect(MarketingAdConnection $connection): RedirectResponse
    {
        abort_unless($connection->provider === MarketingAdConnection::PROVIDER_META_ADS, 404);

        $connection->forceFill([
            'status' => MarketingAdConnection::STATUS_DISCONNECTED,
            'refresh_token' => null,
            'last_error' => null,
        ])->save();

        return $this->back()->with('status', 'Meta Ads disconnected ('.$connection->label().'). Imported campaign history has been kept. To retire the token itself, revoke it in Meta Business Settings → System users.');
    }

    private function back(): RedirectResponse
    {
        return redirect()->route('marketing.integrations');
    }
}
