<?php

namespace App\Domain\Marketing\MetaAds;

use App\Domain\Marketing\AdSyncWriter;
use App\Models\MarketingAdAccount;
use App\Models\MarketingAdConnection;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Pulls Meta Ads into the same local reporting tables as Google Ads: the ad accounts the
 * system user can see, their campaigns, and one stats row per campaign per day.
 * Read-only: nothing here changes an ad account.
 *
 * Meta's numbers are translated onto the shared columns so reports never branch on platform:
 *  - status: ACTIVE → ENABLED, DELETED/ARCHIVED → REMOVED (Google's words);
 *  - clicks: link clicks (inline_link_clicks), the clicks that reach the site, not Meta's
 *    all-clicks figure, which also counts likes, comments and profile taps;
 *  - spend and budgets: stored as micros of the account currency;
 *  - conversions: the action types in config('marketing.meta_ads.conversion_actions').
 */
class MetaAdsSyncService
{
    /** Days per insights request: one row per campaign per day keeps a window small. */
    private const WINDOW_DAYS = 30;

    private const PAGE_SIZE = 500;

    /** Meta account_status codes. 9 (grace period) still delivers ads. */
    private const ACCOUNT_STATUSES = [
        1 => 'ENABLED', 2 => 'DISABLED', 3 => 'UNSETTLED', 7 => 'PENDING_RISK_REVIEW',
        8 => 'PENDING_SETTLEMENT', 9 => 'ENABLED', 100 => 'PENDING_CLOSURE', 101 => 'CLOSED',
    ];

    /** Currencies Meta reports budgets in whole units (offset 1) instead of cents. */
    private const WHOLE_UNIT_CURRENCIES = ['CLP', 'COP', 'CRC', 'HUF', 'ISK', 'IDR', 'JPY', 'KRW', 'PYG', 'TWD', 'VND'];

    public function __construct(
        private readonly MetaAdsClient $client,
        private readonly AdSyncWriter $writer,
    ) {}

    /**
     * Record every ad account the system user has been given access to.
     *
     * @return int accounts found
     *
     * @throws MetaAdsException
     */
    public function discoverAccounts(MarketingAdConnection $connection): int
    {
        $rows = $this->client->all($connection, 'me/adaccounts', [
            'fields' => 'account_id,name,currency,timezone_name,account_status',
            'limit' => 100,
        ]);

        $found = 0;

        foreach ($rows as $row) {
            $id = preg_replace('/\D+/', '', (string) ($row['account_id'] ?? '')) ?? '';

            if ($id === '') {
                continue;
            }

            $status = (int) ($row['account_status'] ?? 0);

            $this->writer->recordAccount($connection, $id, [
                'name' => $row['name'] ?? null,
                'currency_code' => $row['currency'] ?? null,
                'time_zone' => $row['timezone_name'] ?? null,
                'is_manager' => false,
                'status' => self::ACCOUNT_STATUSES[$status] ?? "STATUS_{$status}",
            ], $this->historyDays());

            $found++;
        }

        return $found;
    }

    /**
     * Sync every account read through this connection that is assigned to a location.
     * One account failing never stops the others.
     *
     * @param  int|null  $days  trailing days to pull; null picks backfill or refresh per account
     * @return array{synced: int, failed: int, waiting: int, errors: array<string, string>}
     *
     * @throws MetaAdsException when the token itself is rejected (every account would fail)
     */
    public function syncAll(MarketingAdConnection $connection, ?int $days = null): array
    {
        $result = ['synced' => 0, 'failed' => 0, 'waiting' => 0, 'errors' => []];

        $advertising = $connection->accounts()->advertising();
        $result['waiting'] = (clone $advertising)->whereDoesntHave('locations')->count();

        foreach ($advertising->assigned()->get() as $account) {
            try {
                $this->syncAccount($connection, $account, $days);
                $result['synced']++;
            } catch (MetaAdsException $e) {
                if ($e->tokenRejected) {
                    throw $e;
                }
                $this->recordFailure($account, $e, $result);
            } catch (Throwable $e) {
                $this->recordFailure($account, $e, $result);
            }
        }

        if ($result['synced'] > 0) {
            $connection->forceFill(['last_synced_at' => now()])->save();
        }

        return $result;
    }

    /**
     * Sync one account's campaigns and daily stats, never earlier than its history_from.
     *
     * @param  int|null  $days  trailing days to pull. Null: everything since history_from on the
     *                          first run, then the refresh window (Meta restates recent conversions).
     *
     * @throws MetaAdsException
     */
    public function syncAccount(MarketingAdConnection $connection, MarketingAdAccount $account, ?int $days = null): void
    {
        $end = CarbonImmutable::today();
        $floor = $account->history_from !== null
            ? CarbonImmutable::parse($account->history_from)
            : $end->subDays($this->historyDays());

        $days ??= $account->last_synced_at === null ? null : (int) config('marketing.meta_ads.refresh_days', 28);
        $start = $days === null ? $floor : $end->subDays(max(1, $days) - 1)->max($floor);

        $this->syncCampaigns($connection, $account);

        for ($from = $start; $from->lte($end); $from = $from->addDays(self::WINDOW_DAYS)) {
            $to = $from->addDays(self::WINDOW_DAYS - 1)->min($end);

            $this->syncStats($connection, $account, $from->toDateString(), $to->toDateString());
        }

        $account->forceFill(['last_synced_at' => now(), 'last_error' => null])->save();
    }

    /** Current campaigns, including ones that have never spent (they have no stats rows). */
    private function syncCampaigns(MarketingAdConnection $connection, MarketingAdAccount $account): void
    {
        $rows = $this->client->all($connection, "act_{$account->external_id}/campaigns", [
            'fields' => 'id,name,status,objective,daily_budget',
            'limit' => self::PAGE_SIZE,
        ]);

        $campaigns = [];

        foreach ($rows as $row) {
            if (blank($row['id'] ?? null)) {
                continue;
            }

            $campaigns[(string) $row['id']] = [
                'name' => mb_substr((string) ($row['name'] ?? ''), 0, 255),
                'status' => $this->campaignStatus($row['status'] ?? null),
                'channel_type' => $this->objective($row['objective'] ?? null),
                // Only campaign-level budgets are on the campaign; ad set budgets are not summed here.
                'daily_budget_micros' => filled($row['daily_budget'] ?? null)
                    ? $this->budgetMicros((string) $row['daily_budget'], $account->currency_code)
                    : null,
            ];
        }

        $this->writer->upsertCampaigns($account, $campaigns);
    }

    /** One row per campaign per day in the window, as Ads Manager reports it. */
    private function syncStats(MarketingAdConnection $connection, MarketingAdAccount $account, string $start, string $end): void
    {
        $rows = $this->client->all($connection, "act_{$account->external_id}/insights", [
            'level' => 'campaign',
            'fields' => 'campaign_id,campaign_name,impressions,inline_link_clicks,spend,actions,action_values',
            'time_range' => json_encode(['since' => $start, 'until' => $end]),
            'time_increment' => 1,
            // Use each ad set's own attribution setting, so conversions match Ads Manager.
            'use_unified_attribution_setting' => 'true',
            'limit' => self::PAGE_SIZE,
        ]);

        if ($rows === []) {
            return;
        }

        // Covers campaigns since deleted, which the campaign list may no longer return.
        $campaigns = [];
        foreach ($rows as $row) {
            if (filled($row['campaign_id'] ?? null)) {
                $campaigns[(string) $row['campaign_id']] = ['name' => mb_substr((string) ($row['campaign_name'] ?? ''), 0, 255)];
            }
        }
        $campaignIds = $this->writer->upsertCampaigns($account, $campaigns);

        $wanted = (array) config('marketing.meta_ads.conversion_actions', ['lead']);
        $stats = [];

        foreach ($rows as $row) {
            $campaignId = $campaignIds[(string) ($row['campaign_id'] ?? '')] ?? null;
            $date = $row['date_start'] ?? null;

            if ($campaignId === null || $date === null) {
                continue;
            }

            $stats["{$campaignId}:{$date}"] = [
                'campaign_id' => $campaignId,
                'date' => $date,
                'impressions' => (int) ($row['impressions'] ?? 0),
                'clicks' => (int) ($row['inline_link_clicks'] ?? 0),
                'cost_micros' => (int) round((float) ($row['spend'] ?? 0) * 1_000_000),
                'conversions' => round($this->sumActions($row['actions'] ?? [], $wanted), 4),
                'conversions_value' => round($this->sumActions($row['action_values'] ?? [], $wanted), 4),
            ];
        }

        $this->writer->upsertStats(array_values($stats));
    }

    /**
     * @param  array<int, array{action_type?: string, value?: string}>  $actions
     * @param  list<string>  $wanted
     */
    private function sumActions(array $actions, array $wanted): float
    {
        $total = 0.0;

        foreach ($actions as $action) {
            if (in_array($action['action_type'] ?? null, $wanted, true)) {
                $total += (float) ($action['value'] ?? 0);
            }
        }

        return $total;
    }

    /** Meta's configured status in the words the reports use (Google's). */
    private function campaignStatus(?string $status): ?string
    {
        return match ($status) {
            'ACTIVE' => 'ENABLED',
            'PAUSED' => 'PAUSED',
            'DELETED', 'ARCHIVED' => 'REMOVED',
            default => $status,
        };
    }

    /** "OUTCOME_LEADS" → "LEADS": the objective is the closest Meta has to a channel type. */
    private function objective(?string $objective): ?string
    {
        return $objective === null ? null : mb_substr(preg_replace('/^OUTCOME_/', '', $objective) ?? $objective, 0, 48);
    }

    /** Meta budgets are in the currency's smallest unit (cents for USD). */
    private function budgetMicros(string $amount, ?string $currency): int
    {
        $perUnit = in_array(strtoupper((string) $currency), self::WHOLE_UNIT_CURRENCIES, true) ? 1 : 100;

        return (int) round((int) $amount / $perUnit * 1_000_000);
    }

    /**
     * @param  array{synced: int, failed: int, waiting: int, errors: array<string, string>}  $result
     */
    private function recordFailure(MarketingAdAccount $account, Throwable $e, array &$result): void
    {
        $account->forceFill(['last_error' => mb_substr($e->getMessage(), 0, 2000)])->save();
        $result['failed']++;
        $result['errors'][$account->external_id] = $e->getMessage();

        Log::warning("Meta Ads sync failed for account {$account->external_id}.", ['error' => $e->getMessage()]);
    }

    private function historyDays(): int
    {
        return max(1, (int) config('marketing.meta_ads.history_days', 90));
    }
}
