<?php

namespace App\Domain\Marketing;

use App\Domain\Support\ClinicRegistry;
use App\Models\MarketingEvent;
use App\Models\MarketingSite;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Every number read from web tracking: the Websites, Visitor Journeys, Leads, Funnel and
 * Channels pages read from here and nowhere else, so a definition — what counts as a
 * signup, how a source is grouped into a channel — is changed in one place.
 *
 * Every method takes a MarketingFilter: the period and the selected locations. A tracked
 * site belongs to one location (office_id + clinic_num), and sessions, visitors and
 * events belong to a site, so scoping is always "sites in the selected locations".
 *
 * Reads only; TrackingIngestService is the writer.
 */
class WebsiteAnalyticsService
{
    /** Channel buckets, in display order. */
    public const CHANNELS = [
        'paid_search' => 'Paid Search',
        'paid_social' => 'Paid Social',
        'organic_search' => 'Organic Search',
        'social' => 'Social',
        'referral' => 'Referral',
        'email' => 'Email',
        'direct' => 'Direct',
        'other' => 'Other',
    ];

    /** UTM mediums that mean a paid search click, when no click id says so. */
    private const PAID_SEARCH_MEDIUMS = ['cpc', 'ppc', 'paid', 'paidsearch', 'paid_search', 'sem'];

    private const PAID_SOCIAL_MEDIUMS = ['paid_social', 'paidsocial', 'paid-social'];

    private const PAID_SEARCH_CLICKS = ['google', 'microsoft'];

    private const PAID_SOCIAL_CLICKS = ['meta', 'tiktok'];

    /** @var array<string, list<int>> site ids per filter signature, so one page's queries share one lookup */
    private array $siteIds = [];

    public function __construct(private readonly ClinicRegistry $clinics) {}

    /**
     * Tracked sites in the filter's locations (and the one site, when the filter names it).
     *
     * @return Collection<int, MarketingSite>
     */
    public function sites(MarketingFilter $filter): Collection
    {
        $query = MarketingSite::query()->orderBy('name');
        LocationScope::apply($query, $filter, 'office_id', 'clinic_num');

        if ($filter->siteId !== null) {
            $query->where('id', $filter->siteId);
        }

        return $query->get();
    }

    /**
     * One row per site for the sites table.
     *
     * @return list<array<string, mixed>>
     */
    public function siteSummaries(MarketingFilter $filter): array
    {
        $sites = $this->sites($filter);

        if ($sites->isEmpty()) {
            return [];
        }

        $ids = $sites->pluck('id')->all();
        $visitors = $this->countBySite('visitor_id', $filter, $ids, true);
        $sessions = $this->countBySite('*', $filter, $ids);
        $signups = $this->signupsBySite($filter, $ids);
        $lastSeen = DB::table('marketing_events')->whereIn('site_id', $ids)
            ->select('site_id', DB::raw('MAX(occurred_at) as last_seen'))
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
                'clinic_num' => $site->clinic_num,
                'location_key' => $site->locationKey(),
                'location' => $site->office_id !== null ? $this->clinics->labelFor((int) $site->office_id, $site->clinic_num) : null,
                'is_active' => $site->is_active,
                'verified' => $site->isVerified(),
                'created_at' => $site->created_at?->toDateTimeString(),
                'visitors' => (int) ($visitors[$site->id] ?? 0),
                'sessions' => $siteSessions,
                'signups' => $siteSignups,
                'signup_rate' => $siteSessions > 0 ? round($siteSignups / $siteSessions * 100, 1) : 0.0,
                'last_seen' => $lastSeen[$site->id] ?? null,
            ];
        })->all();
    }

    /**
     * Headline numbers for the filter's sites.
     *
     * @return array{visitors: int, sessions: int, signups: int, signup_rate: float, avg_seconds_to_signup: ?int}
     */
    public function summary(MarketingFilter $filter): array
    {
        $sessions = $this->sessionQuery($filter);

        $totals = (clone $sessions)->selectRaw('COUNT(*) as sessions, COUNT(DISTINCT visitor_id) as visitors')->first();
        $sessionCount = (int) ($totals->sessions ?? 0);
        $signups = (clone $sessions)->where('has_signup', true)->count();

        // Time from the visitor's very first visit to the moment they signed up, which is
        // the number that says how long a decision actually takes.
        $timeToSignup = DB::table('marketing_visitors as v')
            ->whereIn('v.site_id', $this->siteIds($filter))
            ->whereNotNull('v.identified_at')
            ->whereBetween('v.identified_at', $this->range($filter))
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
     * Visits that arrived from an ad (carried a click id), and the signups among them.
     *
     * @return array{visitors: int, sessions: int, signups: int}
     */
    public function paidSummary(MarketingFilter $filter): array
    {
        $row = $this->sessionQuery($filter)
            ->whereNotNull('click_id')
            ->selectRaw('COUNT(*) as sessions, COUNT(DISTINCT visitor_id) as visitors, SUM(CASE WHEN has_signup = 1 THEN 1 ELSE 0 END) as signups')
            ->first();

        return [
            'visitors' => (int) ($row->visitors ?? 0),
            'sessions' => (int) ($row->sessions ?? 0),
            'signups' => (int) ($row->signups ?? 0),
        ];
    }

    /**
     * Traffic grouped by source and medium — where visitors came from.
     *
     * @return list<array<string, mixed>>
     */
    public function trafficSources(MarketingFilter $filter): array
    {
        return $this->sessionQuery($filter)
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
     * Traffic grouped into channels (paid search, organic search, social …), the grouping
     * the Channels page and the Leads page share.
     *
     * @return list<array{channel: string, label: string, visitors: int, sessions: int, signups: int, signup_rate: float}>
     */
    public function channels(MarketingFilter $filter): array
    {
        // Bucket each session first, then aggregate the buckets: grouping a derived table's
        // column is accepted by every database and every MySQL sql_mode, where grouping by
        // the CASE expression itself trips ONLY_FULL_GROUP_BY.
        $buckets = $this->sessionQuery($filter)
            ->selectRaw($this->channelExpression().' as channel')
            ->addSelect('visitor_id', 'has_signup');

        $rows = DB::query()->fromSub($buckets, 'b')
            ->select('channel')
            ->selectRaw('COUNT(DISTINCT visitor_id) as visitors, COUNT(*) as sessions, SUM(CASE WHEN has_signup = 1 THEN 1 ELSE 0 END) as signups')
            ->groupBy('channel')
            ->get()
            ->keyBy('channel');

        $out = [];
        foreach (self::CHANNELS as $key => $label) {
            $row = $rows[$key] ?? null;
            $sessions = (int) ($row->sessions ?? 0);
            $signups = (int) ($row->signups ?? 0);
            $out[] = [
                'channel' => $key,
                'label' => $label,
                'visitors' => (int) ($row->visitors ?? 0),
                'sessions' => $sessions,
                'signups' => $signups,
                'signup_rate' => $sessions > 0 ? round($signups / $sessions * 100, 1) : 0.0,
            ];
        }

        return $out;
    }

    /** The channel a visit (or a visitor's first touch) falls into; the PHP twin of channelExpression(). */
    public function channelOf(?string $medium, ?string $clickSource): string
    {
        $medium = strtolower((string) $medium);
        $click = strtolower((string) $clickSource);

        return match (true) {
            in_array($click, self::PAID_SEARCH_CLICKS, true) || in_array($medium, self::PAID_SEARCH_MEDIUMS, true) => 'paid_search',
            in_array($click, self::PAID_SOCIAL_CLICKS, true) || in_array($medium, self::PAID_SOCIAL_MEDIUMS, true) => 'paid_social',
            $medium === 'organic' => 'organic_search',
            $medium === 'social' => 'social',
            $medium === 'referral' => 'referral',
            $medium === 'email' => 'email',
            $medium === '' || $medium === 'none' => 'direct',
            default => 'other',
        };
    }

    /**
     * Paid traffic only — the visits that carried an ad click id, grouped by campaign.
     *
     * @return list<array<string, mixed>>
     */
    public function adSources(MarketingFilter $filter): array
    {
        return $this->sessionQuery($filter)
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
    public function topPages(MarketingFilter $filter, int $limit = 15): array
    {
        $landings = $this->sessionQuery($filter)
            ->selectRaw('landing_path, COUNT(*) as landings')
            ->groupBy('landing_path')
            ->pluck('landings', 'landing_path');

        return DB::table('marketing_events')
            ->where('type', MarketingEvent::TYPE_PAGEVIEW)
            ->whereIn('site_id', $this->siteIds($filter))
            ->whereBetween('occurred_at', $this->range($filter))
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
     * Recent journeys: one row per visitor active in the period, newest first.
     *
     * @return list<array<string, mixed>>
     */
    public function recentJourneys(MarketingFilter $filter, int $limit = 25): array
    {
        $ids = DB::table('marketing_visitors')
            ->whereIn('site_id', $this->siteIds($filter))
            ->whereBetween('last_seen_at', $this->range($filter))
            ->orderByDesc('last_seen_at')
            ->limit($limit)
            ->pluck('id');

        return $this->journeyRows($ids->all());
    }

    /**
     * Find journeys by email, phone, visitor id or name, within the filter's locations.
     *
     * @return list<array<string, mixed>>
     */
    public function search(string $term, MarketingFilter $filter, int $limit = 25): array
    {
        $term = trim($term);

        if ($term === '') {
            return [];
        }

        $digits = preg_replace('/\D+/', '', $term) ?? '';

        $ids = DB::table('marketing_visitors')
            ->whereIn('site_id', $this->siteIds($filter))
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

        return $this->journeyRows($ids->all());
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
            ->select('v.*', 's.name as site_name', 's.office_id as site_office_id', 's.clinic_num as site_clinic_num')
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
                'location' => $visitor->site_office_id !== null
                    ? $this->clinics->labelFor((int) $visitor->site_office_id, $visitor->site_clinic_num === null ? null : (int) $visitor->site_clinic_num)
                    : null,
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
     * Leads: visitors who gave contact details in the period, newest first, with the
     * first touch that brought them and the site (and so the location) they came through.
     *
     * @return list<array<string, mixed>>
     */
    public function leads(MarketingFilter $filter, int $limit = 500): array
    {
        return DB::table('marketing_visitors as v')
            ->join('marketing_sites as s', 's.id', '=', 'v.site_id')
            ->whereIn('v.site_id', $this->siteIds($filter))
            ->whereNotNull('v.identified_at')
            ->whereBetween('v.identified_at', $this->range($filter))
            ->selectRaw('v.id, v.name, v.email, v.phone, v.identified_at, v.first_seen_at, v.last_seen_at')
            ->selectRaw('v.first_source, v.first_medium, v.first_campaign, v.first_click_source, v.first_landing_path')
            ->selectRaw('s.name as site_name, s.office_id, s.clinic_num')
            ->selectRaw('(SELECT COUNT(*) FROM marketing_sessions ms WHERE ms.visitor_id = v.id) as sessions')
            ->orderByDesc('v.identified_at')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'who' => $row->name ?: ($row->email ?: ($row->phone ?: 'Unknown')),
                'name' => $row->name,
                'email' => $row->email,
                'phone' => $row->phone,
                'site' => $row->site_name,
                'location' => $row->office_id !== null
                    ? $this->clinics->labelFor((int) $row->office_id, $row->clinic_num === null ? null : (int) $row->clinic_num)
                    : null,
                'source' => trim(($row->first_source ?? 'direct').' / '.($row->first_medium ?? 'none')),
                'channel' => self::CHANNELS[$this->channelOf($row->first_medium, $row->first_click_source)],
                'campaign' => $row->first_campaign,
                'landing_path' => $row->first_landing_path,
                'paid' => $row->first_click_source !== null,
                'sessions' => (int) $row->sessions,
                'first_seen' => $row->first_seen_at,
                'identified_at' => $row->identified_at,
                'last_seen' => $row->last_seen_at,
            ])->all();
    }

    /**
     * Lead totals for the period (exact, not limited like leads()).
     *
     * @return array{leads: int, with_email: int, with_phone: int, paid: int, returning: int}
     */
    public function leadSummary(MarketingFilter $filter): array
    {
        $row = DB::table('marketing_visitors as v')
            ->whereIn('v.site_id', $this->siteIds($filter))
            ->whereNotNull('v.identified_at')
            ->whereBetween('v.identified_at', $this->range($filter))
            ->selectRaw('COUNT(*) as leads')
            ->selectRaw('SUM(CASE WHEN v.email IS NOT NULL THEN 1 ELSE 0 END) as with_email')
            ->selectRaw('SUM(CASE WHEN v.phone IS NOT NULL THEN 1 ELSE 0 END) as with_phone')
            ->selectRaw('SUM(CASE WHEN v.first_click_id IS NOT NULL THEN 1 ELSE 0 END) as paid')
            ->selectRaw('SUM(CASE WHEN (SELECT COUNT(*) FROM marketing_sessions ms WHERE ms.visitor_id = v.id) > 1 THEN 1 ELSE 0 END) as returning_visitors')
            ->first();

        return [
            'leads' => (int) ($row->leads ?? 0),
            'with_email' => (int) ($row->with_email ?? 0),
            'with_phone' => (int) ($row->with_phone ?? 0),
            'paid' => (int) ($row->paid ?? 0),
            'returning' => (int) ($row->returning_visitors ?? 0),
        ];
    }

    /** @return list<int> ids of the sites the filter covers */
    public function siteIds(MarketingFilter $filter): array
    {
        $key = md5(json_encode([$filter->scopes(), $filter->allLocations, $filter->siteId]));

        return $this->siteIds[$key] ??= $this->sites($filter)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * @param  list<int>  $ids
     * @return list<array<string, mixed>>
     */
    private function journeyRows(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

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

    private function sessionQuery(MarketingFilter $filter)
    {
        return DB::table('marketing_sessions')
            ->whereIn('site_id', $this->siteIds($filter))
            ->whereBetween('started_at', $this->range($filter));
    }

    /** @param list<int> $ids */
    private function countBySite(string $column, MarketingFilter $filter, array $ids, bool $distinct = false)
    {
        $expression = $column === '*' ? 'COUNT(*)' : ($distinct ? "COUNT(DISTINCT {$column})" : "COUNT({$column})");

        return DB::table('marketing_sessions')
            ->whereIn('site_id', $ids)
            ->whereBetween('started_at', $this->range($filter))
            ->selectRaw("site_id, {$expression} as total")
            ->groupBy('site_id')
            ->pluck('total', 'site_id');
    }

    /** @param list<int> $ids */
    private function signupsBySite(MarketingFilter $filter, array $ids)
    {
        return DB::table('marketing_sessions')
            ->whereIn('site_id', $ids)
            ->where('has_signup', true)
            ->whereBetween('started_at', $this->range($filter))
            ->selectRaw('site_id, COUNT(*) as total')
            ->groupBy('site_id')
            ->pluck('total', 'site_id');
    }

    /** @return array{0: string, 1: string} */
    private function range(MarketingFilter $filter): array
    {
        return [$filter->start.' 00:00:00', $filter->end.' 23:59:59'];
    }

    /** SQL CASE that buckets a session into a channel key; keep in step with channelOf(). */
    private function channelExpression(): string
    {
        $quote = fn (array $values) => implode(', ', array_map(fn ($v) => "'".$v."'", $values));

        return 'CASE'
            .' WHEN LOWER(COALESCE(click_source, \'\')) IN ('.$quote(self::PAID_SEARCH_CLICKS).') OR LOWER(COALESCE(medium, \'\')) IN ('.$quote(self::PAID_SEARCH_MEDIUMS).') THEN \'paid_search\''
            .' WHEN LOWER(COALESCE(click_source, \'\')) IN ('.$quote(self::PAID_SOCIAL_CLICKS).') OR LOWER(COALESCE(medium, \'\')) IN ('.$quote(self::PAID_SOCIAL_MEDIUMS).') THEN \'paid_social\''
            .' WHEN LOWER(COALESCE(medium, \'\')) = \'organic\' THEN \'organic_search\''
            .' WHEN LOWER(COALESCE(medium, \'\')) = \'social\' THEN \'social\''
            .' WHEN LOWER(COALESCE(medium, \'\')) = \'referral\' THEN \'referral\''
            .' WHEN LOWER(COALESCE(medium, \'\')) = \'email\' THEN \'email\''
            .' WHEN COALESCE(medium, \'\') IN (\'\', \'none\') THEN \'direct\''
            .' ELSE \'other\' END';
    }
}
