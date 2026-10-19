<?php

namespace App\Http\Controllers;

use App\Domain\Marketing\AdCampaignReportService;
use App\Domain\Marketing\GoogleAds\GoogleAdsOAuth;
use App\Domain\Marketing\MarketingAlertService;
use App\Domain\Marketing\MarketingFilter;
use App\Domain\Marketing\MarketingOverviewService;
use App\Domain\Marketing\VisitLocationResolver;
use App\Domain\Marketing\WebsiteAnalyticsService;
use App\Domain\Support\ClinicRegistry;
use App\Jobs\RestampSiteVisitLocations;
use App\Models\MarketingAdConnection;
use App\Models\MarketingSite;
use App\Models\MarketingSiteLocation;
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
        $connections = MarketingAdConnection::query()->googleAds()->get()->filter(fn ($c) => $c->isUsable());

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

    /** The install snippet per site, and the locations each site serves. */
    public function tracking(Request $request, WebsiteAnalyticsService $analytics, VisitLocationResolver $resolver): View
    {
        $locations = $this->clinics->locations();
        $selected = $this->clinics->getSelectedLocationKeys();

        return view('marketing.tracking', [
            // The site just added (or linked to) opens with its code showing.
            'openSiteId' => $request->integer('site') ?: null,
            // Working in one location: the add form starts with it chosen.
            'defaultLocation' => count($selected) === 1 && isset($locations[$selected[0]]) ? (string) $selected[0] : null,
            'sites' => MarketingSite::with('locations')->orderBy('name')->get()->map(fn (MarketingSite $site) => [
                'model' => $site,
                'location_keys' => $site->locationKeys(),
                'location' => $analytics->siteLocationLabel($site),
                'shared' => $site->isShared(),
                // Per served location: its name, the value its form sends, and its paths.
                'rules' => $site->locations->map(fn ($l) => [
                    'key' => $l->locationKey(),
                    'name' => $resolver->nameOf($l),
                    'code' => $l->formCode($resolver->nameOf($l)),
                    'paths' => implode(', ', $l->path_prefixes ?? []),
                ])->all(),
                'form_codes' => $resolver->formCodes($site),
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
            'name' => ['nullable', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', Rule::in(array_keys($locations))],
        ]);

        $domain = MarketingSite::normalizeDomain($data['domain']);

        $site = (new MarketingSite([
            'site_key' => MarketingSite::generateKey(),
            // The name is optional: the domain is a fine label until someone renames it.
            'name' => filled($data['name'] ?? null) ? trim($data['name']) : $domain,
            'domain' => $domain,
            'is_active' => true,
        ]));
        $site->save();

        if (isset($locations[$data['location'] ?? ''])) {
            $site->assignLocations([$locations[$data['location']]]);
        }

        return redirect()
            ->route('marketing.tracking', ['site' => $site->id])
            ->with('status', "{$site->domain} added. Copy its code below and paste it into the website.");
    }

    /**
     * Set the locations a site serves, and on a shared site how each location's visits are
     * recognised (the form value and the URL paths). Past visits are re-credited in the
     * background by the same rules.
     */
    public function updateSite(Request $request, MarketingSite $site, WebsiteAnalyticsService $analytics): RedirectResponse
    {
        $locations = $this->clinics->locations();

        $data = $request->validate([
            'locations' => ['nullable', 'array'],
            'locations.*' => ['string', Rule::in(array_keys($locations))],
            'codes' => ['nullable', 'array'],
            'codes.*' => ['nullable', 'string', 'max:64'],
            'paths' => ['nullable', 'array'],
            'paths.*' => ['nullable', 'string', 'max:2000'],
        ]);

        $chosen = array_values(array_unique($data['locations'] ?? []));
        $rules = [];
        foreach ($chosen as $key) {
            if (array_key_exists($key, $data['codes'] ?? [])) {
                $rules[$key]['code'] = $data['codes'][$key];
            }
            if (array_key_exists($key, $data['paths'] ?? [])) {
                $rules[$key]['paths'] = MarketingSiteLocation::normalizePaths((string) $data['paths'][$key]);
            }
        }

        $site->assignLocations(array_map(fn (string $key) => $locations[$key], $chosen), $rules);
        RestampSiteVisitLocations::dispatch($site->id);

        $label = $analytics->siteLocationLabel($site->load('locations'));

        return redirect()->route('marketing.tracking', ['site' => $site->id])
            ->with('status', $label === null
                ? "{$site->name} is no longer assigned to a location."
                : "{$site->name} now serves {$label}.".($site->isShared() ? ' Past visits are being re-credited by these rules.' : ''));
    }

    /** Pause or resume a site: a paused key is rejected by the collector. */
    public function toggleSite(Request $request, MarketingSite $site): RedirectResponse
    {
        $site->forceFill(['is_active' => ! $site->is_active])->save();

        return redirect()->route('marketing.tracking')
            ->with('status', $site->is_active ? "Tracking resumed for {$site->domain}." : "Tracking paused for {$site->domain}.");
    }

    /** The connected Google logins, and every ad account once, with the locations it is assigned to. */
    public function integrations(Request $request, GoogleAdsOAuth $googleAdsOAuth, AdCampaignReportService $report): View
    {
        $filter = $this->filter($request);

        return view('marketing.integrations', [
            'filter' => $filter,
            'connections' => $report->connections(),
            'accounts' => $report->accounts(),
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
