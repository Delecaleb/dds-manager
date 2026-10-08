<?php

namespace App\Domain\Marketing;

use App\Domain\Support\Location;
use App\Models\MarketingAdConnection;

/**
 * The pages that put web tracking and ad platform numbers side by side: Overview, the
 * Attribution Funnel and Channels. Nothing is computed here that the two source services
 * do not already define; this class only lines their numbers up per location, per funnel
 * stage and per channel.
 *
 * What is NOT here, on purpose: booked appointments and production per lead. Crediting an
 * OpenDental patient to an ad click needs a matching rule that has not been defined, so
 * every funnel here stops at the signup.
 */
class MarketingOverviewService
{
    public function __construct(
        private readonly WebsiteAnalyticsService $web,
        private readonly AdCampaignReportService $ads,
    ) {}

    /**
     * @return array{
     *   website: array<string, mixed>, paid: array<string, mixed>, ads: array<string, mixed>,
     *   sites: list<array<string, mixed>>, byLocation: list<array<string, mixed>>,
     *   googleAdsConnected: bool
     * }
     */
    public function overview(MarketingFilter $filter): array
    {
        $sites = $this->web->siteSummaries($filter);
        $campaigns = $this->ads->campaignRows($filter);

        return [
            'website' => $this->web->summary($filter),
            'paid' => $this->web->paidSummary($filter),
            'ads' => $this->ads->summarize($campaigns),
            'sites' => $sites,
            'byLocation' => $this->byLocation($filter, $sites, $campaigns),
            'googleAdsConnected' => MarketingAdConnection::googleAdsUsableWithin($filter),
        ];
    }

    /**
     * The funnel as far as the data goes: ad impressions → clicks → paid visits → paid
     * signups, beside the whole-site visitors → sessions → signups.
     *
     * @return array{ad: list<array<string, mixed>>, site: list<array<string, mixed>>, sources: list<array<string, mixed>>, adSources: list<array<string, mixed>>}
     */
    public function funnel(MarketingFilter $filter): array
    {
        $ads = $this->ads->summarize($this->ads->campaignRows($filter));
        $paid = $this->web->paidSummary($filter);
        $site = $this->web->summary($filter);

        return [
            'ad' => $this->stages([
                ['Impressions', 'Ads shown, as Google Ads reports', $ads['impressions']],
                ['Clicks', 'Ad clicks, as Google Ads reports', $ads['clicks']],
                ['Paid visits', 'Tracked visits that arrived with an ad click id', $paid['sessions']],
                ['Paid signups', 'Those visits that gave contact details', $paid['signups']],
            ]),
            'site' => $this->stages([
                ['Visitors', 'Unique browsers on the tracked sites', $site['visitors']],
                ['Sessions', 'Visits, split after 30 minutes idle', $site['sessions']],
                ['Signups', 'Visits where contact details were given', $site['signups']],
            ]),
            'sources' => $this->web->trafficSources($filter),
            'adSources' => $this->web->adSources($filter),
        ];
    }

    /**
     * One row per channel: traffic and signups from web tracking, plus ad spend where a
     * connected platform reports it (Google Ads → Paid Search).
     *
     * @return array{rows: list<array<string, mixed>>, googleAdsConnected: bool, sites: int, verifiedSites: int}
     */
    public function channels(MarketingFilter $filter): array
    {
        $ads = $this->ads->summarize($this->ads->campaignRows($filter));
        $sites = $this->web->sites($filter);

        $rows = array_map(function (array $row) use ($ads) {
            $spend = $row['channel'] === 'paid_search' ? $ads['spend'] : null;

            return $row + [
                'spend' => $spend,
                'clicks' => $row['channel'] === 'paid_search' ? $ads['clicks'] : null,
                'cost_per_signup' => $spend !== null && $row['signups'] > 0 ? round($spend / $row['signups'], 2) : null,
                'source' => match ($row['channel']) {
                    'paid_search' => 'Google Ads + site tracking',
                    default => 'Site tracking',
                },
            ];
        }, $this->web->channels($filter));

        return [
            'rows' => $rows,
            'googleAdsConnected' => MarketingAdConnection::googleAdsUsableWithin($filter),
            'sites' => $sites->count(),
            'verifiedSites' => $sites->filter(fn ($s) => $s->isVerified())->count(),
        ];
    }

    /**
     * Per selected location: its sites' traffic and its campaigns' spend. A site or
     * campaign assigned to a whole multi-clinic office counts for each of its clinics.
     *
     * @param  list<array<string, mixed>>  $sites
     * @param  list<array<string, mixed>>  $campaigns
     * @return list<array<string, mixed>>
     */
    private function byLocation(MarketingFilter $filter, array $sites, array $campaigns): array
    {
        $belongs = fn (?int $officeId, ?int $clinicNum, Location $l) => $officeId === $l->officeId
            && ($clinicNum === null || $clinicNum === $l->clinicNum);

        $rows = [];
        foreach ($filter->locations() as $location) {
            $rows[] = $this->locationRow(
                $location->key(),
                $location->name,
                array_filter($sites, fn ($s) => $belongs($s['office_id'] === null ? null : (int) $s['office_id'], $s['clinic_num'] === null ? null : (int) $s['clinic_num'], $location)),
                array_filter($campaigns, fn ($c) => $belongs($c['office_id'], $c['clinic_num'], $location)),
            );
        }

        if ($filter->allLocations) {
            $unassignedSites = array_filter($sites, fn ($s) => $s['office_id'] === null);
            $unassignedCampaigns = array_filter($campaigns, fn ($c) => $c['office_id'] === null);

            if ($unassignedSites !== [] || $unassignedCampaigns !== []) {
                $rows[] = $this->locationRow(null, 'Not assigned to a location', $unassignedSites, $unassignedCampaigns);
            }
        }

        return $rows;
    }

    /**
     * @param  array<int, array<string, mixed>>  $sites
     * @param  array<int, array<string, mixed>>  $campaigns
     * @return array<string, mixed>
     */
    private function locationRow(?string $key, string $name, array $sites, array $campaigns): array
    {
        $sessions = (int) array_sum(array_column($sites, 'sessions'));
        $signups = (int) array_sum(array_column($sites, 'signups'));
        $ads = $this->ads->summarize(array_values($campaigns));

        return [
            'key' => $key,
            'name' => $name,
            'sites' => count($sites),
            'visitors' => (int) array_sum(array_column($sites, 'visitors')),
            'sessions' => $sessions,
            'signups' => $signups,
            'signup_rate' => $sessions > 0 ? round($signups / $sessions * 100, 1) : 0.0,
            'campaigns' => count($campaigns),
            'spend' => $ads['spend'],
            'clicks' => $ads['clicks'],
            'conversions' => $ads['conversions'],
        ];
    }

    /**
     * Stage rows with the step-to-step conversion into each.
     *
     * @param  list<array{0: string, 1: string, 2: int}>  $stages
     * @return list<array{label: string, meaning: string, value: int, rate: ?float}>
     */
    private function stages(array $stages): array
    {
        $out = [];
        $previous = null;
        foreach ($stages as [$label, $meaning, $value]) {
            $out[] = [
                'label' => $label,
                'meaning' => $meaning,
                'value' => $value,
                'rate' => $previous !== null && $previous > 0 ? round($value / $previous * 100, 1) : null,
            ];
            $previous = $value;
        }

        return $out;
    }
}
