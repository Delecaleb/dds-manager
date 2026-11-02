<?php

namespace App\Domain\Marketing;

use App\Models\MarketingAdAccount;
use App\Models\MarketingAdCampaign;
use App\Models\MarketingAdConnection;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Writes what an ad platform sync read into the shared ad tables, the same way for every
 * platform: accounts once per provider + platform id, campaigns per account + platform id,
 * one stats row per campaign per day. Every write is an upsert keyed on the platform's own
 * ids, so a run can repeat or overlap an earlier window without duplicating anything.
 *
 * The location an account or campaign is credited to is set in this app and is never
 * touched here.
 */
class AdSyncWriter
{
    private const UPSERT_CHUNK = 500;

    /**
     * Record one ad account a login can see. A new account starts with no location (an
     * admin assigns one before anything syncs) and a history floor $historyDays back.
     * An account another usable login already reads is left with that login.
     *
     * @param  array<string, mixed>  $attributes  name, currency_code, time_zone, is_manager, status, ...
     */
    public function recordAccount(MarketingAdConnection $connection, string $externalId, array $attributes, int $historyDays): void
    {
        $account = MarketingAdAccount::firstOrNew([
            'provider' => $connection->provider,
            'external_id' => $externalId,
        ]);

        if (! $account->exists) {
            $account->forceFill([
                'connection_id' => $connection->id,
                'history_from' => CarbonImmutable::today()->subDays(max(1, $historyDays))->toDateString(),
            ]);
        } elseif ((int) $account->connection_id !== $connection->id) {
            $current = $account->connection;
            if ($current !== null && $current->isUsable()) {
                return;
            }
            $account->connection_id = $connection->id;
        }

        $account->fill($attributes)->save();
    }

    /**
     * Insert or update an account's campaigns. Every column given (other than external_id)
     * is refreshed on an existing row; columns left out keep their stored value.
     *
     * @param  array<string, array<string, mixed>>  $campaigns  external_id => [name, status, channel_type, daily_budget_micros?]
     * @return array<string, int> external_id => local campaign id
     */
    public function upsertCampaigns(MarketingAdAccount $account, array $campaigns): array
    {
        if ($campaigns === []) {
            return [];
        }

        $now = now()->format('Y-m-d H:i:s');
        $rows = [];

        foreach ($campaigns as $externalId => $campaign) {
            $rows[] = ['account_id' => $account->id, 'external_id' => (string) $externalId]
                + $campaign
                + ['created_at' => $now, 'updated_at' => $now];
        }

        $update = array_values(array_diff(array_keys($rows[0]), ['account_id', 'external_id', 'created_at']));

        foreach (array_chunk($rows, self::UPSERT_CHUNK) as $chunk) {
            DB::table('marketing_ad_campaigns')->upsert($chunk, ['account_id', 'external_id'], $update);
        }

        return MarketingAdCampaign::where('account_id', $account->id)
            ->whereIn('external_id', array_map('strval', array_keys($campaigns)))
            ->pluck('id', 'external_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Insert or update daily stats rows.
     *
     * @param  list<array{campaign_id: int, date: string, impressions: int, clicks: int, cost_micros: int, conversions: float, conversions_value: float}>  $stats
     */
    public function upsertStats(array $stats): void
    {
        $now = now()->format('Y-m-d H:i:s');
        $rows = array_map(fn (array $row) => $row + ['created_at' => $now, 'updated_at' => $now], $stats);

        foreach (array_chunk($rows, self::UPSERT_CHUNK) as $chunk) {
            DB::table('marketing_ad_campaign_stats')->upsert(
                $chunk,
                ['campaign_id', 'date'],
                ['impressions', 'clicks', 'cost_micros', 'conversions', 'conversions_value', 'updated_at']
            );
        }
    }
}
