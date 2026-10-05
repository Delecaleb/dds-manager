<?php

namespace App\Domain\Marketing;

use Illuminate\Support\Facades\DB;

/**
 * Campaign numbers as the ad platforms report them: spend, impressions, clicks and the
 * platform's own conversion count, per campaign and per location.
 *
 * Leads, booked appointments and production are deliberately not here. They depend on
 * matching an ad click to a patient, and that rule has not been defined yet.
 */
class AdCampaignReportService
{
    private const MICROS = 1_000_000;

    /**
     * One row per campaign with its totals for the period. A campaign is credited to its
     * own office when it has one, otherwise to its account's office.
     *
     * Campaigns that were removed and did nothing in the period are left out.
     *
     * @param  string  $start  'Y-m-d'
     * @param  string  $end  'Y-m-d'
     * @param  list<int>  $officeIds  empty = every location, including unmapped campaigns
     * @return list<array<string, mixed>>
     */
    public function campaignRows(string $start, string $end, array $officeIds = []): array
    {
        $query = DB::table('marketing_ad_campaigns as c')
            ->join('marketing_ad_accounts as a', 'a.id', '=', 'c.account_id')
            ->join('marketing_ad_connections as n', 'n.id', '=', 'a.connection_id')
            ->leftJoin('marketing_ad_campaign_stats as s', function ($join) use ($start, $end) {
                $join->on('s.campaign_id', '=', 'c.id')->whereBetween('s.date', [$start, $end]);
            })
            ->leftJoin('offices as o', 'o.id', '=', DB::raw('COALESCE(c.office_id, a.office_id)'))
            ->groupBy('c.id', 'c.name', 'c.status', 'c.channel_type', 'c.daily_budget_micros', 'n.provider',
                'a.name', 'a.external_id', 'a.currency_code', 'o.id', 'o.name')
            ->havingRaw("(c.status <> 'REMOVED' OR COALESCE(SUM(s.cost_micros), 0) > 0 OR COALESCE(SUM(s.impressions), 0) > 0)")
            ->orderByRaw('COALESCE(SUM(s.cost_micros), 0) DESC')
            ->orderBy('c.name');

        if ($officeIds !== []) {
            $query->whereIn(DB::raw('COALESCE(c.office_id, a.office_id)'), $officeIds);
        }

        $rows = $query->get([
            'c.id', 'c.name', 'c.status', 'c.channel_type', 'c.daily_budget_micros', 'n.provider',
            'a.name as account_name', 'a.external_id as account_external_id', 'a.currency_code',
            'o.id as office_id', 'o.name as office_name',
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

            return [
                'id' => (int) $row->id,
                'name' => $row->name,
                'provider' => $row->provider,
                'channel_type' => $row->channel_type,
                'status' => $row->status,
                'account_name' => $row->account_name,
                'account_external_id' => $row->account_external_id,
                'currency_code' => $row->currency_code,
                'office_id' => $row->office_id !== null ? (int) $row->office_id : null,
                'office_name' => $row->office_name,
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
     * @return array{active_campaigns: int, spend: float, impressions: int, clicks: int, conversions: float}
     */
    public function summarize(array $rows): array
    {
        return [
            'active_campaigns' => count(array_filter($rows, fn (array $row) => $row['status'] === 'ENABLED')),
            'spend' => round(array_sum(array_column($rows, 'spend')), 2),
            'impressions' => (int) array_sum(array_column($rows, 'impressions')),
            'clicks' => (int) array_sum(array_column($rows, 'clicks')),
            'conversions' => round(array_sum(array_column($rows, 'conversions')), 2),
        ];
    }
}
