<?php

namespace App\Domain\Marketing;

use App\Domain\Support\ClinicRegistry;
use App\Domain\Support\Location;
use App\Models\MarketingAdAccount;
use App\Models\MarketingAdConnection;
use Illuminate\Support\Facades\DB;

/**
 * Campaign numbers as the ad platforms report them: spend, impressions, clicks and the
 * platform's own conversion count, per campaign, for the filter's period and locations.
 *
 * A campaign is credited to its own location when it has one, otherwise to its ad
 * account's, otherwise to the location that made the connection. Leads, booked
 * appointments and production are deliberately not here: they depend on matching an ad
 * click to a patient, and that rule has not been defined.
 */
class AdCampaignReportService
{
    private const MICROS = 1_000_000;

    /** The location a campaign is credited to: its own, else its account's, else its connection's. */
    private const OFFICE_EXPR = 'CASE WHEN c.office_id IS NOT NULL THEN c.office_id WHEN a.office_id IS NOT NULL THEN a.office_id ELSE n.office_id END';

    private const CLINIC_EXPR = 'CASE WHEN c.office_id IS NOT NULL THEN c.clinic_num WHEN a.office_id IS NOT NULL THEN a.clinic_num ELSE n.clinic_num END';

    public function __construct(private readonly ClinicRegistry $clinics) {}

    /**
     * One row per campaign with its totals for the period. Campaigns that were removed
     * and did nothing in the period are left out.
     *
     * @return list<array<string, mixed>>
     */
    public function campaignRows(MarketingFilter $filter): array
    {
        $query = DB::table('marketing_ad_campaigns as c')
            ->join('marketing_ad_accounts as a', 'a.id', '=', 'c.account_id')
            ->join('marketing_ad_connections as n', 'n.id', '=', 'a.connection_id')
            ->leftJoin('marketing_ad_campaign_stats as s', function ($join) use ($filter) {
                $join->on('s.campaign_id', '=', 'c.id')->whereBetween('s.date', [$filter->start, $filter->end]);
            })
            ->groupBy('c.id', 'c.name', 'c.status', 'c.channel_type', 'c.daily_budget_micros', 'n.provider',
                'a.name', 'a.external_id', 'a.currency_code', 'c.office_id', 'c.clinic_num', 'a.office_id', 'a.clinic_num', 'n.office_id', 'n.clinic_num')
            ->havingRaw("(c.status <> 'REMOVED' OR COALESCE(SUM(s.cost_micros), 0) > 0 OR COALESCE(SUM(s.impressions), 0) > 0)")
            ->orderByRaw('COALESCE(SUM(s.cost_micros), 0) DESC')
            ->orderBy('c.name');

        LocationScope::apply($query, $filter, self::OFFICE_EXPR, self::CLINIC_EXPR);

        $rows = $query->get([
            'c.id', 'c.name', 'c.status', 'c.channel_type', 'c.daily_budget_micros', 'n.provider',
            'a.name as account_name', 'a.external_id as account_external_id', 'a.currency_code',
            'c.office_id as campaign_office_id', 'c.clinic_num as campaign_clinic_num',
            'a.office_id as account_office_id', 'a.clinic_num as account_clinic_num',
            'n.office_id as connection_office_id', 'n.clinic_num as connection_clinic_num',
            DB::raw('COALESCE(SUM(s.impressions), 0) as impressions'),
            DB::raw('COALESCE(SUM(s.clicks), 0) as clicks'),
            DB::raw('COALESCE(SUM(s.cost_micros), 0) as cost_micros'),
            DB::raw('COALESCE(SUM(s.conversions), 0) as conversions'),
            DB::raw('COALESCE(SUM(s.conversions_value), 0) as conversions_value'),
        ]);

        return $rows->map(function ($row) {
            $spend = (int) $row->cost_micros / self::MICROS;
            $clicks = (int) $row->clicks;
            $impressions = (int) $row->impressions;
            $conversions = (float) $row->conversions;

            [$officeId, $clinicNum] = match (true) {
                $row->campaign_office_id !== null => [$row->campaign_office_id, $row->campaign_clinic_num],
                $row->account_office_id !== null => [$row->account_office_id, $row->account_clinic_num],
                default => [$row->connection_office_id, $row->connection_clinic_num],
            };
            $officeId = $officeId === null ? null : (int) $officeId;
            $clinicNum = $clinicNum === null ? null : (int) $clinicNum;

            return [
                'id' => (int) $row->id,
                'name' => $row->name,
                'provider' => $row->provider,
                'channel_type' => $row->channel_type,
                'status' => $row->status,
                'account_name' => $row->account_name,
                'account_external_id' => $row->account_external_id,
                'currency_code' => $row->currency_code,
                'office_id' => $officeId,
                'clinic_num' => $clinicNum,
                'location' => $officeId !== null ? $this->clinics->labelFor($officeId, $clinicNum) : null,
                'daily_budget' => $row->daily_budget_micros !== null ? (int) $row->daily_budget_micros / self::MICROS : null,
                'impressions' => $impressions,
                'clicks' => $clicks,
                'spend' => round($spend, 2),
                'conversions' => round($conversions, 2),
                'conversions_value' => round((float) $row->conversions_value, 2),
                'ctr' => $impressions > 0 ? round($clicks / $impressions, 4) : null,
                'avg_cpc' => $clicks > 0 ? round($spend / $clicks, 2) : null,
                'cost_per_conversion' => $conversions > 0 ? round($spend / $conversions, 2) : null,
            ];
        })->all();
    }

    /**
     * Headline totals for the same rows, so the cards and the table can never disagree.
     *
     * @param  list<array<string, mixed>>  $rows  from campaignRows()
     * @return array{active_campaigns: int, spend: float, impressions: int, clicks: int, conversions: float, avg_cpc: ?float, cost_per_conversion: ?float}
     */
    public function summarize(array $rows): array
    {
        $spend = round(array_sum(array_column($rows, 'spend')), 2);
        $clicks = (int) array_sum(array_column($rows, 'clicks'));
        $conversions = round(array_sum(array_column($rows, 'conversions')), 2);

        return [
            'active_campaigns' => count(array_filter($rows, fn (array $row) => $row['status'] === 'ENABLED')),
            'spend' => $spend,
            'impressions' => (int) array_sum(array_column($rows, 'impressions')),
            'clicks' => $clicks,
            'conversions' => $conversions,
            'avg_cpc' => $clicks > 0 ? round($spend / $clicks, 2) : null,
            'cost_per_conversion' => $conversions > 0 ? round($spend / $conversions, 2) : null,
        ];
    }

    /**
     * The Integrations page, per selected location: that location's Google Ads connection
     * (if any) and the accounts under it. Connections made before locations existed come
     * back under 'unassigned' so they can be assigned.
     *
     * @return array{locations: list<array{location: Location, connection: ?MarketingAdConnection, accounts: list<array<string, mixed>>}>, unassigned: list<array{connection: MarketingAdConnection, accounts: list<array<string, mixed>>}>}
     */
    public function connectionsByLocation(MarketingFilter $filter): array
    {
        $connections = MarketingAdConnection::query()->googleAds()->within($filter)->get();
        $accounts = collect($this->accounts())->groupBy('connection_id');

        $rows = [];
        foreach ($filter->locations() as $location) {
            // Exactly this location first; else a connection made for the whole office.
            $connection = $connections->first(fn (MarketingAdConnection $c) => (int) $c->office_id === $location->officeId && $c->clinic_num === $location->clinicNum)
                ?? $connections->first(fn (MarketingAdConnection $c) => (int) $c->office_id === $location->officeId && $c->clinic_num === null);

            $rows[] = [
                'location' => $location,
                'connection' => $connection,
                'accounts' => $connection !== null ? $accounts->get($connection->id, collect())->values()->all() : [],
            ];
        }

        $unassigned = $connections
            ->filter(fn (MarketingAdConnection $c) => $c->office_id === null)
            ->map(fn (MarketingAdConnection $c) => ['connection' => $c, 'accounts' => $accounts->get($c->id, collect())->values()->all()])
            ->values()
            ->all();

        return ['locations' => $rows, 'unassigned' => $unassigned];
    }

    /**
     * Every synced ad account that can hold campaigns, with the location it is credited
     * to — the rows the Integrations page lets the user map.
     *
     * @return list<array<string, mixed>>
     */
    public function accounts(): array
    {
        return MarketingAdAccount::query()
            ->where('is_manager', false)
            ->withCount('campaigns')
            ->orderBy('name')
            ->get()
            ->map(fn (MarketingAdAccount $account) => [
                'id' => $account->id,
                'connection_id' => $account->connection_id,
                'name' => $account->name ?: $account->external_id,
                'external_id' => $account->external_id,
                'currency_code' => $account->currency_code,
                'status' => $account->status,
                'is_enabled' => $account->is_enabled,
                'campaigns' => (int) $account->campaigns_count,
                'location_key' => $account->locationKey(),
                'location' => $account->office_id !== null ? $this->clinics->labelFor((int) $account->office_id, $account->clinic_num) : null,
                'last_synced_at' => $account->last_synced_at?->toDateTimeString(),
                'last_error' => $account->last_error,
            ])->all();
    }
}
