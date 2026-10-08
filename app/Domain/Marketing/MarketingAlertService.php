<?php

namespace App\Domain\Marketing;

use App\Domain\Support\ClinicRegistry;
use App\Models\MarketingAdAccount;
use App\Models\MarketingAdConnection;
use App\Models\MarketingSite;
use Carbon\CarbonImmutable;

/**
 * Alerts the Growth Engine can raise from the data it holds. Every rule here is checked
 * on the fly against the tracking and ad tables — nothing is stored, nothing is sent.
 *
 * Rules fall in two groups:
 *  - per location (follow the filter): a site that stopped reporting, a campaign that
 *    spends without converting;
 *  - per location too: that location's Google Ads connection being rejected or stale;
 *  - organization-wide (shown whatever is selected): sites, ad accounts or connections
 *    not assigned to any location — configuration problems that would otherwise hide
 *    inside a location nobody is looking at.
 *
 * Thresholds are the constants below. They are deliberately few and conservative.
 */
class MarketingAlertService
{
    /** A new site that has not reported after this long is probably installed wrong. */
    public const SITE_VERIFY_HOURS = 24;

    /** A verified, active site with no events for this long has lost its snippet. */
    public const SITE_SILENT_HOURS = 48;

    /** The scheduler syncs Google Ads every three hours; four misses is a problem. */
    public const ADS_STALE_HOURS = 12;

    /** Spend in the period before "no conversions" is worth a warning. */
    public const MIN_SPEND_FOR_CONVERSION_ALERT = 50.0;

    public function __construct(
        private readonly WebsiteAnalyticsService $web,
        private readonly AdCampaignReportService $ads,
        private readonly ClinicRegistry $clinics,
    ) {}

    /**
     * @return array{items: list<array{severity: string, title: string, detail: string, route: string, scope: string}>, critical: int, warning: int}
     */
    public function alerts(MarketingFilter $filter): array
    {
        $items = [
            ...$this->siteAlerts($filter),
            ...$this->campaignAlerts($filter),
            ...$this->connectionAlerts($filter),
            ...$this->mappingAlerts(),
        ];

        usort($items, fn ($a, $b) => [$a['severity'] === 'critical' ? 0 : 1, $a['title']] <=> [$b['severity'] === 'critical' ? 0 : 1, $b['title']]);

        return [
            'items' => $items,
            'critical' => count(array_filter($items, fn ($i) => $i['severity'] === 'critical')),
            'warning' => count(array_filter($items, fn ($i) => $i['severity'] === 'warning')),
        ];
    }

    /** @return list<array<string, string>> */
    private function siteAlerts(MarketingFilter $filter): array
    {
        $now = CarbonImmutable::now();
        $out = [];

        foreach ($this->web->siteSummaries($filter) as $site) {
            if (! $site['is_active']) {
                continue;
            }

            if (! $site['verified']) {
                $created = $site['created_at'] !== null ? CarbonImmutable::parse($site['created_at']) : null;
                if ($created !== null && $created->diffInHours($now) >= self::SITE_VERIFY_HOURS) {
                    $out[] = $this->item('warning', "{$site['name']} has never reported",
                        "Added {$created->diffForHumans()} and no page view has arrived. Check the snippet is installed on {$site['domain']}.",
                        'marketing.tracking', 'location');
                }

                continue;
            }

            $lastSeen = $site['last_seen'] !== null ? CarbonImmutable::parse($site['last_seen']) : null;
            if ($lastSeen !== null && $lastSeen->diffInHours($now) >= self::SITE_SILENT_HOURS) {
                $out[] = $this->item('critical', "{$site['name']} stopped reporting",
                    "Last page view {$lastSeen->diffForHumans()}. The snippet may have been removed from {$site['domain']}.",
                    'marketing.tracking', 'location');
            }
        }

        return $out;
    }

    /** @return list<array<string, string>> */
    private function campaignAlerts(MarketingFilter $filter): array
    {
        $out = [];

        foreach ($this->ads->campaignRows($filter) as $campaign) {
            if ($campaign['status'] !== 'ENABLED' || $campaign['spend'] < self::MIN_SPEND_FOR_CONVERSION_ALERT || $campaign['conversions'] > 0) {
                continue;
            }

            $out[] = $this->item('warning', "{$campaign['name']} is spending without conversions",
                sprintf('%s spent between %s and %s with no conversion recorded by Google Ads.', ops_fmt($campaign['spend'], 'money'), $filter->start, $filter->end),
                'marketing.campaigns', 'location');
        }

        return $out;
    }

    /** @return list<array<string, string>> */
    private function connectionAlerts(MarketingFilter $filter): array
    {
        $out = [];

        foreach (MarketingAdConnection::query()->googleAds()->within($filter)->get() as $connection) {
            if (! $connection->isUsable()) {
                continue;
            }

            $where = $connection->office_id !== null ? $this->clinics->labelFor((int) $connection->office_id, $connection->clinic_num) : 'unassigned connection';

            if ($connection->status === MarketingAdConnection::STATUS_ERROR) {
                $out[] = $this->item('critical', "Google Ads needs reconnecting ({$where})",
                    $connection->last_error ?: 'Google rejected the stored credentials.', 'marketing.integrations', 'location');

                continue;
            }

            $synced = $connection->last_synced_at;
            if ($synced === null || $synced->diffInHours(CarbonImmutable::now()) >= self::ADS_STALE_HOURS) {
                $out[] = $this->item('warning', "Google Ads has not synced recently ({$where})",
                    $synced === null ? 'No sync has completed since the connection was made.' : "Last sync {$synced->diffForHumans()}; it is scheduled every three hours.",
                    'marketing.integrations', 'location');
            }
        }

        return $out;
    }

    /** @return list<array<string, string>> */
    private function mappingAlerts(): array
    {
        $out = [];

        $sites = MarketingSite::where('is_active', true)->whereNull('office_id')->count();
        if ($sites > 0) {
            $out[] = $this->item('warning', $sites === 1 ? 'A tracked site is not assigned to a location' : "{$sites} tracked sites are not assigned to a location",
                'Its traffic only appears when every location is selected. Assign it on the Tracking Script page.', 'marketing.tracking', 'organization');
        }

        $connections = MarketingAdConnection::query()->googleAds()->whereNull('office_id')->get()->filter(fn ($c) => $c->isUsable())->count();
        if ($connections > 0) {
            $out[] = $this->item('warning', $connections === 1 ? 'A Google Ads connection is not assigned to a location' : "{$connections} Google Ads connections are not assigned to a location",
                'It was connected before locations existed. Assign it on the Integrations page so its campaigns report per office.', 'marketing.integrations', 'organization');
        }

        $accounts = MarketingAdAccount::where('is_manager', false)->where('is_enabled', true)->whereNull('office_id')
            ->whereHas('connection', fn ($q) => $q->whereNull('office_id'))->count();
        if ($accounts > 0) {
            $out[] = $this->item('warning', $accounts === 1 ? 'A Google Ads account is not assigned to a location' : "{$accounts} Google Ads accounts are not assigned to a location",
                'Its campaigns only appear when every location is selected. Assign it on the Integrations page.', 'marketing.integrations', 'organization');
        }

        return $out;
    }

    /** @return array{severity: string, title: string, detail: string, route: string, scope: string} */
    private function item(string $severity, string $title, string $detail, string $route, string $scope): array
    {
        return compact('severity', 'title', 'detail', 'route', 'scope');
    }
}
