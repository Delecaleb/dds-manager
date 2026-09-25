<?php

namespace App\Domain\Marketing;

use App\Models\MarketingEvent;
use App\Models\MarketingSite;
use Illuminate\Support\Facades\DB;

/**
 * Every number on the Websites page. The page reads from here and nowhere else, so a
 * definition — what counts as a signup, how a source is grouped — is changed in one place.
 *
 * Reads only; TrackingIngestService is the writer.
 */
class WebsiteAnalyticsService
{
    /**
     * One row per site for the sites table.
     *
     * @return list<array<string, mixed>>
     */
    public function siteSummaries(string $start, string $end): array
    {
        $sites = MarketingSite::orderBy('name')->get();

        if ($sites->isEmpty()) {
            return [];
        }

        $visitors = $this->countBySite('marketing_sessions', 'visitor_id', $start, $end, true);
        $sessions = $this->countBySite('marketing_sessions', '*', $start, $end);
        $signups = $this->signupsBySite($start, $end);
        $lastSeen = DB::table('marketing_events')->select('site_id', DB::raw('MAX(occurred_at) as last_seen'))
            ->groupBy('site_id')->pluck('last_seen', 'site_id');

        return $sites->map(function (MarketingSite $site) use ($visitors, $sessions, $signups, $lastSeen) {
            $siteSessions = (int) ($sessions[$site->id] ?? 0);
            $siteSignups = (int) ($signups[$site->id] ?? 0);

            return [
                'id' => $site->id,
                'name' => $site->name,
                'domain' => $site->domain,
                'site_key' => $site->site_key,
                'office_id' => $site->office_id,
                'is_active' => $site->is_active,
                'verified' => $site->isVerified(),
                'visitors' => (int) ($visitors[$site->id] ?? 0),
                'sessions' => $siteSessions,
                'signups' => $siteSignups,
                'signup_rate' => $siteSessions > 0 ? round($siteSignups / $siteSessions * 100, 1) : 0.0,
                'last_seen' => $lastSeen[$site->id] ?? null,
            ];
        })->all();
    }

    /**
     * Headline numbers for one site (or all sites when $siteId is null).
     *
     * @return array<string, mixed>
     */
    public function summary(?int $siteId, string $start, string $end): array
    {
        $sessions = $this->sessionQuery($siteId, $start, $end);

        $totals = (clone $sessions)->selectRaw('COUNT(*) as sessions, COUNT(DISTINCT visitor_id) as visitors')->first();
        $sessionCount = (int) ($totals->sessions ?? 0);

        $signups = (clone $sessions)->where('has_signup', true)->count();

        // Time from the visitor's very first visit to the moment they signed up, which is
        // the number that says how long a decision actually takes.
        $timeToSignup = DB::table('marketing_visitors as v')
            ->whereNotNull('v.identified_at')
            ->when($siteId !== null, fn ($q) => $q->where('v.site_id', $siteId))
            ->whereBetween('v.identified_at', [$start.' 00:00:00', $end.' 23:59:59'])
            ->get(['v.first_seen_at', 'v.identified_at'])
            ->map(fn ($row) => strtotime((string) $row->identified_at) - strtotime((string) $row->first_seen_at))
            ->filter(fn ($seconds) => $seconds >= 0);

        return [
            'visitors' => (int) ($totals->visitors ?? 0),
            'sessions' => $sessionCount,
            'signups' => $signups,
            'signup_rate' => $sessionCount > 0 ? round($signups / $sessionCount * 100, 1) : 0.0,
            'avg_seconds_to_signup' => $timeToSignup->isEmpty() ? null : (int) round($timeToSignup->avg()),
        ];
    }

    /**
     * Traffic grouped by source and medium — where visitors came from.
     *
     * @return list<array<string, mixed>>
     */
    public function trafficSources(?int $siteId, string $start, string $end): array
    {
        return $this->sessionQuery($siteId, $start, $end)
            ->selectRaw("COALESCE(source, 'direct') as source, COALESCE(medium, 'none') as medium")
            ->selectRaw('COUNT(DISTINCT visitor_id) as visitors, COUNT(*) as sessions, SUM(CASE WHEN has_signup = 1 THEN 1 ELSE 0 END) as signups')
            ->groupBy('source', 'medium')
            ->orderByDesc('sessions')
            ->get()
            ->map(fn ($row) => [
                'source' => $row->source,
                'medium' => $row->medium,
                'visitors' => (int) $row->visitors,
                'sessions' => (int) $row->sessions,
                'signups' => (int) $row->signups,
                'signup_rate' => (int) $row->sessions > 0 ? round((int) $row->signups / (int) $row->sessions * 100, 1) : 0.0,
            ])->all();
    }

    /**
     * Paid traffic only — the visits that carried an ad click id, grouped by campaign.
     *
     * @return list<array<string, mixed>>
     */
    public function adSources(?int $siteId, string $start, string $end): array
    {
        return $this->sessionQuery($siteId, $start, $end)
            ->whereNotNull('click_id')
            ->selectRaw("COALESCE(click_source, 'unknown') as platform, COALESCE(campaign, '(not set)') as campaign, COALESCE(content, '(not set)') as content, COALESCE(term, '(not set)') as term")
            ->selectRaw('COUNT(DISTINCT visitor_id) as visitors, COUNT(*) as sessions, SUM(CASE WHEN has_signup = 1 THEN 1 ELSE 0 END) as signups')
            ->groupBy('platform', 'campaign', 'content', 'term')
            ->orderByDesc('sessions')
            ->get()
            ->map(fn ($row) => [
                'platform' => $row->platform,
                'campaign' => $row->campaign,
                'content' => $row->content,
                'term' => $row->term,
                'visitors' => (int) $row->visitors,
                'sessions' => (int) $row->sessions,
                'signups' => (int) $row->signups,
            ])->all();
    }

    /**
     * Most viewed pages, and how many landings each one took.
     *
     * @return list<array<string, mixed>>
     */
    public function topPages(?int $siteId, string $start, string $end, int $limit = 15): array
    {
        $landings = $this->sessionQuery($siteId, $start, $end)
            ->selectRaw('landing_path, COUNT(*) as landings')
            ->groupBy('landing_path')
            ->pluck('landings', 'landing_path');

        return DB::table('marketing_events')
            ->where('type', MarketingEvent::TYPE_PAGEVIEW)
            ->when($siteId !== null, fn ($q) => $q->where('site_id', $siteId))
            ->whereBetween('occurred_at', [$start.' 00:00:00', $end.' 23:59:59'])
            ->selectRaw('path, COUNT(*) as views, COUNT(DISTINCT visitor_id) as visitors')
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'path' => $row->path,
                'views' => (int) $row->views,
                'visitors' => (int) $row->visitors,
                'landings' => (int) ($landings[$row->path] ?? 0),
            ])->all();
    }

    /**
     * Recent journeys: one row per visitor, newest first.
     *
     * @return list<array<string, mixed>>
     */
    public function recentJourneys(?int $siteId, string $start, string $end, int $limit = 25): array
    {
        return DB::table('marketing_visitors as v')
            ->leftJoin('marketing_sites as s', 's.id', '=', 'v.site_id')
            ->when($siteId !== null, fn ($q) => $q->where('v.site_id', $siteId))
            ->whereBetween('v.last_seen_at', [$start.' 00:00:00', $end.' 23:59:59'])
            ->selectRaw('v.id, v.visitor_uid, v.name, v.email, v.phone, v.identified_at, v.first_seen_at, v.last_seen_at')
            ->selectRaw('v.first_source, v.first_medium, v.first_campaign, s.name as site_name')
            ->selectRaw('(SELECT COUNT(*) FROM marketing_sessions ms WHERE ms.visitor_id = v.id) as sessions')
            ->selectRaw('(SELECT COUNT(*) FROM marketing_events me WHERE me.visitor_id = v.id) as events')
            ->orderByDesc('v.last_seen_at')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'visitor_uid' => $row->visitor_uid,
                'site' => $row->site_name,
                'who' => $row->name ?: ($row->email ?: ($row->phone ?: 'Anonymous visitor')),
                'identified' => $row->identified_at !== null,
                'source' => trim(($row->first_source ?? 'direct').' / '.($row->first_medium ?? 'none')),
                'campaign' => $row->first_campaign,
                'sessions' => (int) $row->sessions,
                'events' => (int) $row->events,
                'first_seen' => $row->first_seen_at,
                'last_seen' => $row->last_seen_at,
            ])->all();
    }

    /**
     * One visitor's full timeline, oldest first.
     *
     * @return array{visitor: array<string, mixed>, events: list<array<string, mixed>>}|null
     */
    public function journey(int $visitorId): ?array
    {
        $visitor = DB::table('marketing_visitors as v')
            ->leftJoin('marketing_sites as s', 's.id', '=', 'v.site_id')
            ->where('v.id', $visitorId)
            ->select('v.*', 's.name as site_name')
            ->first();

        if ($visitor === null) {
            return null;
        }

        $events = DB::table('marketing_events as e')
            ->leftJoin('marketing_sessions as ms', 'ms.id', '=', 'e.session_id')
            ->where('e.visitor_id', $visitorId)
            ->orderBy('e.occurred_at')
            ->select('e.type', 'e.path', 'e.title', 'e.referrer', 'e.payload', 'e.occurred_at')
            ->selectRaw('ms.source, ms.medium, ms.campaign, ms.device, ms.browser, ms.session_uid')
            ->get()
            ->map(fn ($row) => [
                'type' => $row->type,
                'path' => $row->path,
                'title' => $row->title,
                'source' => trim(($row->source ?? 'direct').' / '.($row->medium ?? 'none')),
                'campaign' => $row->campaign,
                'device' => $row->device,
                'browser' => $row->browser,
                'session' => $row->session_uid,
                'occurred_at' => $row->occurred_at,
            ])->all();

        return [
            'visitor' => [
                'id' => (int) $visitor->id,
                'site' => $visitor->site_name,
                'visitor_uid' => $visitor->visitor_uid,
                'name' => $visitor->name,
                'email' => $visitor->email,
                'phone' => $visitor->phone,
                'identified_at' => $visitor->identified_at,
                'first_seen_at' => $visitor->first_seen_at,
                'last_seen_at' => $visitor->last_seen_at,
                'first_source' => trim(($visitor->first_source ?? 'direct').' / '.($visitor->first_medium ?? 'none')),
                'first_campaign' => $visitor->first_campaign,
                'first_landing_path' => $visitor->first_landing_path,
                'first_click_id' => $visitor->first_click_id,
                'first_click_source' => $visitor->first_click_source,
            ],
            'events' => $events,
        ];
    }

    /**
     * Find journeys by email, phone, visitor id or name.
     *
     * @return list<array<string, mixed>>
     */
    public function search(string $term, int $limit = 25): array
    {
        $term = trim($term);

        if ($term === '') {
            return [];
        }

        $digits = preg_replace('/\D+/', '', $term) ?? '';

        $ids = DB::table('marketing_visitors')
            ->where(function ($q) use ($term, $digits) {
                $q->where('email', 'like', "%{$term}%")
                    ->orWhere('name', 'like', "%{$term}%")
                    ->orWhere('visitor_uid', $term);

                if (strlen($digits) >= 7) {
                    $q->orWhere('phone', 'like', "%{$digits}%");
                }
            })
            ->orderByDesc('last_seen_at')
            ->limit($limit)
            ->pluck('id');

        if ($ids->isEmpty()) {
            return [];
        }

        return collect($this->recentJourneysByIds($ids->all()))->all();
    }

    /**
     * @param  list<int>  $ids
     * @return list<array<string, mixed>>
     */
    private function recentJourneysByIds(array $ids): array
    {
        return DB::table('marketing_visitors as v')
            ->leftJoin('marketing_sites as s', 's.id', '=', 'v.site_id')
            ->whereIn('v.id', $ids)
            ->orderByDesc('v.last_seen_at')
            ->selectRaw('v.id, v.visitor_uid, v.name, v.email, v.phone, v.identified_at, v.first_seen_at, v.last_seen_at')
            ->selectRaw('v.first_source, v.first_medium, v.first_campaign, s.name as site_name')
            ->selectRaw('(SELECT COUNT(*) FROM marketing_sessions ms WHERE ms.visitor_id = v.id) as sessions')
            ->selectRaw('(SELECT COUNT(*) FROM marketing_events me WHERE me.visitor_id = v.id) as events')
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'visitor_uid' => $row->visitor_uid,
                'site' => $row->site_name,
                'who' => $row->name ?: ($row->email ?: ($row->phone ?: 'Anonymous visitor')),
                'identified' => $row->identified_at !== null,
                'source' => trim(($row->first_source ?? 'direct').' / '.($row->first_medium ?? 'none')),
                'campaign' => $row->first_campaign,
                'sessions' => (int) $row->sessions,
                'events' => (int) $row->events,
                'first_seen' => $row->first_seen_at,
                'last_seen' => $row->last_seen_at,
            ])->all();
    }

    private function sessionQuery(?int $siteId, string $start, string $end)
    {
        return DB::table('marketing_sessions')
            ->when($siteId !== null, fn ($q) => $q->where('site_id', $siteId))
            ->whereBetween('started_at', [$start.' 00:00:00', $end.' 23:59:59']);
    }

    private function countBySite(string $table, string $column, string $start, string $end, bool $distinct = false)
    {
        $expression = $column === '*' ? 'COUNT(*)' : ($distinct ? "COUNT(DISTINCT {$column})" : "COUNT({$column})");

        return DB::table($table)
            ->whereBetween('started_at', [$start.' 00:00:00', $end.' 23:59:59'])
            ->selectRaw("site_id, {$expression} as total")
            ->groupBy('site_id')
            ->pluck('total', 'site_id');
    }

    private function signupsBySite(string $start, string $end)
    {
        return DB::table('marketing_sessions')
            ->where('has_signup', true)
            ->whereBetween('started_at', [$start.' 00:00:00', $end.' 23:59:59'])
            ->selectRaw('site_id, COUNT(*) as total')
            ->groupBy('site_id')
            ->pluck('total', 'site_id');
    }
}
