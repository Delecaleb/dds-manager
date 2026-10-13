<?php

namespace App\Domain\Marketing;

use App\Domain\Support\ClinicRegistry;
use App\Models\MarketingAdAccount;
use App\Models\MarketingAdAccountLocation;
use App\Models\MarketingAdCampaign;
use App\Models\MarketingAdConnection;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Campaign numbers as the ad platforms report them: spend, impressions, clicks and the
 * platform's own conversion count, per campaign, for the filter's period and locations.
 *
 * The single rule for which location a campaign's numbers count for:
 *  - the location assigned to the campaign itself, if any (always one of its account's);
 *  - else the account's location, when the account is assigned to exactly one;
 *  - else none — a campaign in an account shared by several locations counts nowhere until
 *    it is assigned, and an account with no location is not reported (or synced) at all.
 * Nothing is guessed from which login found the account.
 *
 * Leads, booked appointments and production are deliberately not here: they depend on
 * matching an ad click to a patient, and that rule has not been defined.
 */
class AdCampaignReportService
{
    private const MICROS = 1_000_000;

    private const OFFICE_EXPR = 'CASE WHEN c.office_id IS NOT NULL THEN c.office_id WHEN al.locations = 1 THEN al.office_id END';

    private const CLINIC_EXPR = 'CASE WHEN c.office_id IS NOT NULL THEN c.clinic_num WHEN al.locations = 1 THEN al.clinic_num END';

    /** Days of spend shown beside each campaign on the assignment page. */
    public const ASSIGNMENT_SPEND_DAYS = 30;

    public function __construct(private readonly ClinicRegistry $clinics) {}

    /**
     * One row per campaign credited to a selected location, with its totals for the period.
     * Campaigns that were removed and did nothing in the period are left out.
     *
     * @return list<array<string, mixed>>
     */
    public function campaignRows(MarketingFilter $filter): array
    {
        $query = DB::table('marketing_ad_campaigns as c')
            ->join('marketing_ad_accounts as a', 'a.id', '=', 'c.account_id')
            ->joinSub($this->accountLocations(), 'al', 'al.account_id', '=', 'a.id')
            ->leftJoin('marketing_ad_campaign_stats as s', function ($join) use ($filter) {
                $join->on('s.campaign_id', '=', 'c.id')->whereBetween('s.date', [$filter->start, $filter->end]);
            })
            ->whereRaw('('.self::OFFICE_EXPR.') IS NOT NULL')
            ->groupBy('c.id', 'c.name', 'c.status', 'c.channel_type', 'c.daily_budget_micros', 'a.provider',
                'a.name', 'a.external_id', 'a.currency_code', 'c.office_id', 'c.clinic_num', 'al.locations', 'al.office_id', 'al.clinic_num')
            ->havingRaw("(c.status <> 'REMOVED' OR COALESCE(SUM(s.cost_micros), 0) > 0 OR COALESCE(SUM(s.impressions), 0) > 0)")
            ->orderByRaw('COALESCE(SUM(s.cost_micros), 0) DESC')
            ->orderBy('c.name');

        LocationScope::apply($query, $filter, '('.self::OFFICE_EXPR.')', '('.self::CLINIC_EXPR.')');

        $rows = $query->get([
            'c.id', 'c.name', 'c.status', 'c.channel_type', 'c.daily_budget_micros', 'a.provider',
            'a.name as account_name', 'a.external_id as account_external_id', 'a.currency_code',
            DB::raw('('.self::OFFICE_EXPR.') as credited_office_id'),
            DB::raw('('.self::CLINIC_EXPR.') as credited_clinic_num'),
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
            $officeId = (int) $row->credited_office_id;
            $clinicNum = $row->credited_clinic_num === null ? null : (int) $row->credited_clinic_num;

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
                'location' => $this->clinics->labelFor($officeId, $clinicNum),
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
     * Google logins that have been connected, newest first.
     *
     * @return list<MarketingAdConnection>
     */
    public function connections(): array
    {
        return MarketingAdConnection::query()->googleAds()->withCount('accounts')->latest('connected_at')->get()->all();
    }

    /**
     * Every ad account that can hold campaigns, each once, with the locations it is assigned
     * to and where it stands — the rows the Integrations page lets an admin assign.
     *
     * @return list<array<string, mixed>>
     */
    public function accounts(): array
    {
        return MarketingAdAccount::query()
            ->where('is_manager', false)
            ->with(['locations', 'connection'])
            ->withCount([
                'campaigns',
                'campaigns as unassigned_campaigns_count' => fn ($q) => $q->whereNull('office_id'),
            ])
            ->orderByRaw("CASE WHEN status IS NULL OR status = 'ENABLED' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get()
            ->map(function (MarketingAdAccount $account) {
                $locations = $account->locations;
                $active = $account->status === null || $account->status === 'ENABLED';

                return [
                    'id' => $account->id,
                    'name' => $account->name ?: $account->external_id,
                    'external_id' => $account->external_id,
                    'currency_code' => $account->currency_code,
                    'status' => $account->status,
                    'active' => $active,
                    'connection' => $account->connection?->label(),
                    'campaigns' => (int) $account->campaigns_count,
                    'location_keys' => $account->locationKeys(),
                    'locations' => $locations->map(fn (MarketingAdAccountLocation $l) => $this->clinics->labelFor((int) $l->office_id, $l->clinic_num))->all(),
                    // Only meaningful when the account is shared: then these count for no location.
                    'unassigned_campaigns' => $locations->count() > 1 ? (int) $account->unassigned_campaigns_count : 0,
                    'state' => match (true) {
                        ! $active => 'closed',
                        $locations->isEmpty() => 'waiting',
                        default => 'assigned',
                    },
                    'history_from' => $account->history_from?->toDateString(),
                    'last_synced_at' => $account->last_synced_at?->toDateTimeString(),
                    'last_error' => $account->last_error,
                ];
            })->all();
    }

    /**
     * An account's campaigns for the assignment page, with recent spend to help decide.
     *
     * @return list<array<string, mixed>>
     */
    public function campaignsForAssignment(MarketingAdAccount $account): array
    {
        $since = CarbonImmutable::today()->subDays(self::ASSIGNMENT_SPEND_DAYS - 1)->toDateString();

        return MarketingAdCampaign::query()
            ->where('account_id', $account->id)
            ->withSum(['stats as recent_cost_micros' => fn ($q) => $q->where('date', '>=', $since)], 'cost_micros')
            ->orderByRaw("CASE WHEN status = 'REMOVED' THEN 1 ELSE 0 END")
            ->orderBy('name')
            ->get()
            ->map(fn (MarketingAdCampaign $campaign) => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'status' => $campaign->status,
                'channel_type' => $campaign->channel_type,
                'location_key' => $campaign->locationKey(),
                'recent_spend' => round((int) $campaign->recent_cost_micros / self::MICROS, 2),
            ])->all();
    }

    /** Per account: how many locations it has, and the single one when there is one. */
    private function accountLocations(): Builder
    {
        return DB::table('marketing_ad_account_locations')
            ->selectRaw('account_id, COUNT(*) as locations, MIN(office_id) as office_id, MIN(clinic_num) as clinic_num')
            ->groupBy('account_id');
    }
}
