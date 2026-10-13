<?php

namespace App\Domain\Marketing\GoogleAds;

use App\Models\MarketingAdAccount;
use App\Models\MarketingAdCampaign;
use App\Models\MarketingAdConnection;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Pulls Google Ads into the local reporting tables: which accounts exist, their campaigns,
 * and one stats row per campaign per day. Read-only — nothing here changes an ad account.
 *
 * Every write is an upsert keyed on Google's own ids, so a run can be repeated or overlap
 * an earlier window without duplicating anything. The office a campaign or account is
 * mapped to is set in this app and is never overwritten by a sync.
 */
class GoogleAdsSyncService
{
    /** Days per stats request: keeps one response to a manageable size on busy accounts. */
    private const WINDOW_DAYS = 90;

    private const UPSERT_CHUNK = 500;

    public function __construct(private readonly GoogleAdsClient $client) {}

    /**
     * Record every account the connection can reach, including those under a manager.
     *
     * @return int accounts found
     *
     * @throws GoogleAdsException when Google cannot be asked at all, or refuses every account
     */
    public function discoverAccounts(MarketingAdConnection $connection): int
    {
        $found = [];
        $rootIds = $this->client->accessibleCustomerIds($connection);
        $rejections = [];

        foreach ($rootIds as $rootId) {
            try {
                // A manager lists itself and its whole tree; a plain account lists only itself.
                $rows = $this->client->search($connection, $rootId, '
                    SELECT customer_client.id, customer_client.descriptive_name, customer_client.currency_code,
                           customer_client.time_zone, customer_client.manager, customer_client.status
                    FROM customer_client
                ', $rootId);
            } catch (GoogleAdsException $e) {
                // Typically a cancelled or not-yet-enabled account; the others are still usable.
                Log::warning("Google Ads account {$rootId} could not be read during discovery.", ['error' => $e->getMessage()]);
                $rejections[] = $e->getMessage();

                continue;
            }

            foreach ($rows as $row) {
                $client = $row['customerClient'] ?? [];
                $id = GoogleAdsClient::digits((string) ($client['id'] ?? ''));

                if ($id === '' || isset($found[$id])) {
                    continue;
                }

                $found[$id] = [
                    'login_customer_id' => $rootId,
                    'name' => $client['descriptiveName'] ?? null,
                    'currency_code' => $client['currencyCode'] ?? null,
                    'time_zone' => $client['timeZone'] ?? null,
                    'is_manager' => (bool) ($client['manager'] ?? false),
                    'status' => $client['status'] ?? null,
                ];
            }
        }

        // Every account refused is an access problem (e.g. a test-level developer token), not
        // an empty login — surface Google's reason instead of reporting "0 accounts".
        if ($rootIds !== [] && count($rejections) === count($rootIds)) {
            $reasons = array_values(array_unique($rejections));
            $detail = count($reasons) === 1 ? $reasons[0] : $reasons[0].' (other accounts failed differently; see the log)';

            throw new GoogleAdsException('Google refused all '.count($rootIds).' ad account(s) this login can see. '.$detail);
        }

        foreach ($found as $externalId => $attributes) {
            $account = MarketingAdAccount::firstOrNew([
                'provider' => MarketingAdConnection::PROVIDER_GOOGLE_ADS,
                'external_id' => (string) $externalId,
            ]);

            if (! $account->exists) {
                // New accounts get no location: an admin assigns one before anything syncs.
                $account->forceFill([
                    'connection_id' => $connection->id,
                    'history_from' => CarbonImmutable::today()->subDays($this->historyDays())->toDateString(),
                ]);
            } elseif ((int) $account->connection_id !== $connection->id) {
                // Another login already reads this account: keep it unless that login is gone.
                $current = $account->connection;
                if ($current !== null && $current->isUsable()) {
                    continue;
                }
                $account->connection_id = $connection->id;
            }

            $account->fill($attributes)->save();
        }

        return count($found);
    }

    /** How far before an account was first connected its history is pulled. */
    private function historyDays(): int
    {
        return max(1, (int) config('marketing.google_ads.history_days', 90));
    }

    /**
     * Sync every account read through this login that is assigned to a location. Accounts
     * still waiting for a location are left alone. One account failing never stops the others.
     *
     * @param  int|null  $days  trailing days to pull; null picks backfill or refresh per account
     * @return array{synced: int, failed: int, waiting: int, errors: array<string, string>}
     */
    public function syncAll(MarketingAdConnection $connection, ?int $days = null): array
    {
        $result = ['synced' => 0, 'failed' => 0, 'waiting' => 0, 'errors' => []];

        $advertising = $connection->accounts()->advertising();
        $result['waiting'] = (clone $advertising)->whereDoesntHave('locations')->count();
        $accounts = $advertising->assigned()->get();

        foreach ($accounts as $account) {
            try {
                $this->syncAccount($connection, $account, $days);
                $result['synced']++;
            } catch (Throwable $e) {
                $account->forceFill(['last_error' => mb_substr($e->getMessage(), 0, 2000)])->save();
                $result['failed']++;
                $result['errors'][$account->external_id] = $e->getMessage();

                Log::warning("Google Ads sync failed for account {$account->external_id}.", ['error' => $e->getMessage()]);
            }
        }

        if ($result['synced'] > 0) {
            $connection->forceFill(['last_synced_at' => now()])->save();
        }

        return $result;
    }

    /**
     * Sync one account's campaigns and daily stats. Never earlier than the account's
     * history_from (90 days before it was first connected).
     *
     * @param  int|null  $days  trailing days to pull. Null: everything since history_from on the
     *                          first run, then the refresh window (Google restates recent conversions).
     *
     * @throws GoogleAdsException
     */
    public function syncAccount(MarketingAdConnection $connection, MarketingAdAccount $account, ?int $days = null): void
    {
        $end = CarbonImmutable::today();
        $floor = $account->history_from !== null
            ? CarbonImmutable::parse($account->history_from)
            : $end->subDays($this->historyDays());

        $days ??= $account->last_synced_at === null ? null : (int) config('marketing.google_ads.refresh_days', 30);
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
        $rows = $this->client->search($connection, $account->external_id, "
            SELECT campaign.id, campaign.name, campaign.status, campaign.advertising_channel_type,
                   campaign_budget.amount_micros
            FROM campaign
            WHERE campaign.status != 'REMOVED'
        ", $account->login_customer_id);

        $now = now()->format('Y-m-d H:i:s');
        $campaigns = [];

        foreach ($rows as $row) {
            $campaign = $row['campaign'] ?? [];

            if (blank($campaign['id'] ?? null)) {
                continue;
            }

            $campaigns[(string) $campaign['id']] = [
                'account_id' => $account->id,
                'external_id' => (string) $campaign['id'],
                'name' => mb_substr((string) ($campaign['name'] ?? ''), 0, 255),
                'status' => $campaign['status'] ?? null,
                'channel_type' => $campaign['advertisingChannelType'] ?? null,
                'daily_budget_micros' => isset($row['campaignBudget']['amountMicros']) ? (int) $row['campaignBudget']['amountMicros'] : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk(array_values($campaigns), self::UPSERT_CHUNK) as $chunk) {
            DB::table('marketing_ad_campaigns')->upsert(
                $chunk,
                ['account_id', 'external_id'],
                ['name', 'status', 'channel_type', 'daily_budget_micros', 'updated_at']
            );
        }
    }

    /** One row per campaign per day in the window. Removed campaigns keep their past spend. */
    private function syncStats(MarketingAdConnection $connection, MarketingAdAccount $account, string $start, string $end): void
    {
        $rows = $this->client->search($connection, $account->external_id, "
            SELECT campaign.id, campaign.name, campaign.status, campaign.advertising_channel_type,
                   segments.date, metrics.impressions, metrics.clicks, metrics.cost_micros,
                   metrics.conversions, metrics.conversions_value
            FROM campaign
            WHERE segments.date BETWEEN '{$start}' AND '{$end}'
        ", $account->login_customer_id);

        if ($rows === []) {
            return;
        }

        $now = now()->format('Y-m-d H:i:s');
        $campaigns = [];

        foreach ($rows as $row) {
            $campaign = $row['campaign'] ?? [];

            if (filled($campaign['id'] ?? null)) {
                $campaigns[(string) $campaign['id']] = [
                    'account_id' => $account->id,
                    'external_id' => (string) $campaign['id'],
                    'name' => mb_substr((string) ($campaign['name'] ?? ''), 0, 255),
                    'status' => $campaign['status'] ?? null,
                    'channel_type' => $campaign['advertisingChannelType'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Covers campaigns since removed, which the campaign list no longer returns.
        foreach (array_chunk(array_values($campaigns), self::UPSERT_CHUNK) as $chunk) {
            DB::table('marketing_ad_campaigns')->upsert(
                $chunk,
                ['account_id', 'external_id'],
                ['name', 'status', 'channel_type', 'updated_at']
            );
        }

        $campaignIds = MarketingAdCampaign::where('account_id', $account->id)
            ->whereIn('external_id', array_keys($campaigns))
            ->pluck('id', 'external_id');

        $stats = [];

        foreach ($rows as $row) {
            $campaignId = $campaignIds[(string) ($row['campaign']['id'] ?? '')] ?? null;
            $date = $row['segments']['date'] ?? null;

            if ($campaignId === null || $date === null) {
                continue;
            }

            // Google leaves zero-valued metrics out of the JSON entirely.
            $metrics = $row['metrics'] ?? [];

            $stats["{$campaignId}:{$date}"] = [
                'campaign_id' => $campaignId,
                'date' => $date,
                'impressions' => (int) ($metrics['impressions'] ?? 0),
                'clicks' => (int) ($metrics['clicks'] ?? 0),
                'cost_micros' => (int) ($metrics['costMicros'] ?? 0),
                'conversions' => round((float) ($metrics['conversions'] ?? 0), 4),
                'conversions_value' => round((float) ($metrics['conversionsValue'] ?? 0), 4),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk(array_values($stats), self::UPSERT_CHUNK) as $chunk) {
            DB::table('marketing_ad_campaign_stats')->upsert(
                $chunk,
                ['campaign_id', 'date'],
                ['impressions', 'clicks', 'cost_micros', 'conversions', 'conversions_value', 'updated_at']
            );
        }
    }
}
