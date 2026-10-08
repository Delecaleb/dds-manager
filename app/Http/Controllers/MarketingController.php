<?php

namespace App\Http\Controllers;

use App\Domain\Marketing\AdCampaignReportService;
use App\Domain\Marketing\GoogleAds\GoogleAdsOAuth;
use App\Domain\Marketing\MarketingAlertService;
use App\Domain\Marketing\MarketingFilter;
use App\Domain\Marketing\MarketingOverviewService;
use App\Domain\Marketing\WebsiteAnalyticsService;
use App\Domain\Support\ClinicRegistry;
use App\Models\MarketingAdConnection;
use App\Models\MarketingSite;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Growth Engine — the hub, plus its Marketing umbrella.
 *
 * Every page reads for the locations the user picked (the same picker and session choice
 * as the analytics shell) and the period: MarketingFilter carries both into the domain
 * services. The AI Front Desk umbrella lives in FrontDeskController; the AI Website
 * Builder in SiteBuilderController.
 */
class MarketingController extends Controller
{
    public function __construct(private readonly ClinicRegistry $clinics) {}

    /** Growth Engine hub: one card per umbrella. */
    public function index(): View
    {
        return view('marketing.hub');
    }

    public function overview(Request $request, MarketingOverviewService $overview): View
    {
        $filter = $this->filter($request);

        return view('marketing.overview', ['filter' => $filter, 'data' => $overview->overview($filter)]);
    }

    public function funnel(Request $request, MarketingOverviewService $overview): View
    {
        $filter = $this->filter($request);

        return view('marketing.funnel', ['filter' => $filter, 'funnel' => $overview->funnel($filter)]);
    }

    public function channels(Request $request, MarketingOverviewService $overview): View
    {
        $filter = $this->filter($request);

        return view('marketing.channels', ['filter' => $filter, 'channels' => $overview->channels($filter)]);
    }

    /** Traffic, signups and sources — for the selected locations, or one of their sites. */
    public function websites(Request $request, WebsiteAnalyticsService $analytics): View
    {
        $filter = $this->filter($request);

        // The site list and the sites table always show every site in the locations; the
        // site dropdown narrows only the numbers.
        $sites = $analytics->siteSummaries($filter->withSite(null));
        $selected = collect($sites)->firstWhere('id', $filter->siteId);
        $filter = $filter->withSite($selected['id'] ?? null);

        return view('marketing.websites', [
            'filter' => $filter,
            'sites' => $sites,
            'selected' => $selected,
            'summary' => $analytics->summary($filter),
            'trafficSources' => $analytics->trafficSources($filter),
            'adSources' => $analytics->adSources($filter),
            'topPages' => $analytics->topPages($filter),
        ]);
    }

    /** Visitor timelines: search, recent journeys, and one visitor in full. */
    public function journeys(Request $request, WebsiteAnalyticsService $analytics): View
    {
        $filter = $this->filter($request);
        $search = trim((string) $request->input('q', ''));
        $visitorId = $request->filled('visitor') ? (int) $request->input('visitor') : null;

        return view('marketing.journeys', [
            'filter' => $filter,
            'search' => $search,
            'journeys' => $search !== '' ? $analytics->search($search, $filter) : $analytics->recentJourneys($filter),
            'journey' => $visitorId !== null ? $analytics->journey($visitorId) : null,
        ]);
    }

    /** Synced ad campaigns, as the platform reports them. */
    public function campaigns(Request $request, AdCampaignReportService $report): View
    {
        $filter = $this->filter($request);
        $rows = $report->campaignRows($filter);
        $connections = MarketingAdConnection::query()->googleAds()->within($filter)->get()->filter(fn ($c) => $c->isUsable());

        return view('marketing.campaigns', [
            'filter' => $filter,
            'rows' => $rows,
            'summary' => $report->summarize($rows),
            'connected' => $connections->isNotEmpty(),
            'lastSynced' => $connections->max('last_synced_at'),
        ]);
    }

    /** Everyone who gave contact details on a tracked site. */
    public function leads(Request $request, WebsiteAnalyticsService $analytics): View
    {
        $filter = $this->filter($request);

        return view('marketing.leads', [
            'filter' => $filter,
            'summary' => $analytics->leadSummary($filter),
            'leads' => $analytics->leads($filter),
        ]);
    }

    public function alerts(Request $request, MarketingAlertService $alerts): View
    {
        $filter = $this->filter($request);

        return view('marketing.alerts', ['filter' => $filter, 'alerts' => $alerts->alerts($filter)]);
    }

    /** The install snippet per site, and which location each site belongs to. */
    public function tracking(WebsiteAnalyticsService $analytics): View
    {
        $locations = $this->clinics->locations();

        return view('marketing.tracking', [
            'sites' => MarketingSite::orderBy('name')->get()->map(fn (MarketingSite $site) => [
                'model' => $site,
                'location_key' => $site->locationKey(),
                'location' => $site->office_id !== null ? $this->clinics->labelFor((int) $site->office_id, $site->clinic_num) : null,
            ]),
            'locations' => $locations,
            'scriptUrl' => route('tracking.script'),
        ]);
    }

    /** Add a tracked site. Its key is generated here and never supplied by the user. */
    public function storeSite(Request $request): RedirectResponse
    {
        $locations = $this->clinics->locations();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', Rule::in(array_keys($locations))],
        ]);

        $site = (new MarketingSite([
            'site_key' => MarketingSite::generateKey(),
            'name' => $data['name'],
            'domain' => MarketingSite::normalizeDomain($data['domain']),
            'is_active' => true,
        ]))->assignLocation($locations[$data['location'] ?? ''] ?? null);
        $site->save();

        return redirect()
            ->route('marketing.tracking', ['site' => $site->id])
            ->with('status', "Site added. Install the snippet on {$site->domain} to start tracking.");
    }

    /** Move a site to another location (or to none). */
    public function updateSite(Request $request, MarketingSite $site): RedirectResponse
    {
        $locations = $this->clinics->locations();

        $data = $request->validate([
            'location' => ['nullable', 'string', Rule::in(array_keys($locations))],
        ]);

        $site->assignLocation($locations[$data['location'] ?? ''] ?? null)->save();

        return redirect()->route('marketing.tracking')
            ->with('status', $site->office_id === null
                ? "{$site->name} is no longer assigned to a location."
                : "{$site->name} now belongs to ".$this->clinics->labelFor((int) $site->office_id, $site->clinic_num).'.');
    }

    /** Pause or resume a site: a paused key is rejected by the collector. */
    public function toggleSite(Request $request, MarketingSite $site): RedirectResponse
    {
        $site->forceFill(['is_active' => ! $site->is_active])->save();

        return redirect()->route('marketing.tracking')
            ->with('status', $site->is_active ? "Tracking resumed for {$site->domain}." : "Tracking paused for {$site->domain}.");
    }

    /** Per selected location: its Google Ads connection and the accounts under it. */
    public function integrations(Request $request, GoogleAdsOAuth $googleAdsOAuth, AdCampaignReportService $report): View
    {
        $filter = $this->filter($request);

        return view('marketing.integrations', [
            'filter' => $filter,
            'googleAds' => $report->connectionsByLocation($filter),
            'googleAdsMissing' => $googleAdsOAuth->missingSettings(),
            'googleAdsCallbackUrl' => $googleAdsOAuth->redirectUri(),
            'locations' => $this->clinics->locations(),
        ]);
    }

    private function filter(Request $request): MarketingFilter
    {
        return MarketingFilter::fromRequest($request, $this->clinics);
    }
}
