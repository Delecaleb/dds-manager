<?php

namespace App\Http\Controllers;

use App\Domain\Marketing\WebsiteAnalyticsService;
use App\Models\MarketingSite;
use App\Models\Office;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Growth Engine — the marketing module.
 *
 * Website tracking (Websites, Visitor Journeys, Tracking Script) reads its numbers from
 * WebsiteAnalyticsService; the remaining pages are still page structure awaiting their
 * data sources.
 */
class MarketingController extends Controller
{
    public function index(): View
    {
        return view('marketing.overview');
    }

    public function funnel(): View
    {
        return view('marketing.funnel');
    }

    /** Traffic, signups and sources — for one tracked site, or all of them. */
    public function websites(Request $request, WebsiteAnalyticsService $analytics): View
    {
        [$start, $end] = $this->period($request);

        $sites = $analytics->siteSummaries($start, $end);
        $selected = $this->selectedSite($request, $sites);
        $siteId = $selected['id'] ?? null;

        return view('marketing.websites', [
            'sites' => $sites,
            'selected' => $selected,
            'start' => $start,
            'end' => $end,
            'summary' => $analytics->summary($siteId, $start, $end),
            'trafficSources' => $analytics->trafficSources($siteId, $start, $end),
            'adSources' => $analytics->adSources($siteId, $start, $end),
            'topPages' => $analytics->topPages($siteId, $start, $end),
        ]);
    }

    /** Visitor timelines: search, recent journeys, and one visitor in full. */
    public function journeys(Request $request, WebsiteAnalyticsService $analytics): View
    {
        [$start, $end] = $this->period($request);
        $search = trim((string) $request->input('q', ''));

        $journeys = $search !== ''
            ? $analytics->search($search)
            : $analytics->recentJourneys(null, $start, $end);

        $visitorId = $request->filled('visitor') ? (int) $request->input('visitor') : null;

        return view('marketing.journeys', [
            'start' => $start,
            'end' => $end,
            'search' => $search,
            'journeys' => $journeys,
            'journey' => $visitorId !== null ? $analytics->journey($visitorId) : null,
        ]);
    }

    /** The install snippet, per site, plus whether the site has reported yet. */
    public function tracking(): View
    {
        return view('marketing.tracking', [
            'sites' => MarketingSite::orderBy('name')->get(),
            'offices' => Office::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'scriptUrl' => route('tracking.script'),
        ]);
    }

    /** Add a tracked site. Its key is generated here and never supplied by the user. */
    public function storeSite(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255'],
            'office_id' => ['nullable', 'integer', Rule::exists('offices', 'id')],
        ]);

        $site = MarketingSite::create([
            'site_key' => MarketingSite::generateKey(),
            'name' => $data['name'],
            'domain' => MarketingSite::normalizeDomain($data['domain']),
            'office_id' => $data['office_id'] ?? null,
            'is_active' => true,
        ]);

        return redirect()
            ->route('marketing.tracking', ['site' => $site->id])
            ->with('status', "Site added. Install the snippet on {$site->domain} to start tracking.");
    }

    /** Pause or resume a site: a paused key is rejected by the collector. */
    public function toggleSite(Request $request, MarketingSite $site): RedirectResponse
    {
        $site->forceFill(['is_active' => ! $site->is_active])->save();

        return redirect()->route('marketing.tracking')
            ->with('status', $site->is_active ? "Tracking resumed for {$site->domain}." : "Tracking paused for {$site->domain}.");
    }

    public function channels(): View
    {
        return view('marketing.channels');
    }

    public function campaigns(): View
    {
        return view('marketing.campaigns');
    }

    public function leads(): View
    {
        return view('marketing.leads');
    }

    public function automations(): View
    {
        return view('marketing.automations');
    }

    public function alerts(): View
    {
        return view('marketing.alerts');
    }

    public function integrations(): View
    {
        return view('marketing.integrations');
    }

    public function settings(): View
    {
        return view('marketing.settings');
    }

    /** @return array{0: string, 1: string} start, end */
    private function period(Request $request): array
    {
        return [
            (string) $request->input('start_date', now()->subDays(29)->toDateString()),
            (string) $request->input('end_date', now()->toDateString()),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $sites
     * @return array<string, mixed>|null
     */
    private function selectedSite(Request $request, array $sites): ?array
    {
        if (! $request->filled('site')) {
            return null;
        }

        $id = (int) $request->input('site');

        foreach ($sites as $site) {
            if ($site['id'] === $id) {
                return $site;
            }
        }

        return null;
    }
}
