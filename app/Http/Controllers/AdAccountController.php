<?php

namespace App\Http\Controllers;

use App\Domain\Marketing\AdCampaignReportService;
use App\Domain\Support\ClinicRegistry;
use App\Models\MarketingAdAccount;
use App\Models\MarketingAdCampaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Ad accounts in the Growth Engine, whatever platform they come from (Google Ads, Meta Ads):
 * assigning each account to the locations it serves and, for accounts shared by several
 * locations, crediting each campaign. How an account is connected and synced is each
 * platform's own controller and command; this is only the location mapping.
 */
class AdAccountController extends Controller
{
    public function __construct(private readonly ClinicRegistry $clinics) {}

    /** Assign an ad account to the locations it serves (none = stop syncing and reporting it). */
    public function update(Request $request, MarketingAdAccount $account): RedirectResponse
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

        return redirect()->route('marketing.integrations')->with('status', $message);
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

        return redirect()->route('marketing.ad-accounts.campaigns', $account)
            ->with('status', $location === null ? "{$campaign->name} is not credited to any location." : "{$campaign->name} credited to {$location->name}.");
    }
}
