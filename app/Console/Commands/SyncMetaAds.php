<?php

namespace App\Console\Commands;

use App\Domain\Marketing\MetaAds\MetaAdsException;
use App\Domain\Marketing\MetaAds\MetaAdsSyncService;
use App\Models\MarketingAdConnection;
use Illuminate\Console\Command;

/**
 * Pull Meta Ads accounts, campaigns and daily stats into the local reporting tables, for
 * every connected system user. Only accounts assigned to a location are synced, and never
 * further back than the history window before the account was connected. Scheduled in
 * routes/console.php; safe to run by hand at any time (every write is an upsert).
 */
class SyncMetaAds extends Command
{
    protected $signature = 'marketing:meta-ads-sync
                            {--days= : Trailing days to pull (default: the account\'s whole history window on its first run, then the refresh window; never before its history start)}
                            {--skip-discovery : Do not re-list the ad accounts first}';

    protected $description = 'Sync Meta Ads campaigns and daily stats for every account assigned to a location';

    public function handle(MetaAdsSyncService $sync): int
    {
        $connections = MarketingAdConnection::query()->metaAds()->get()->filter(fn (MarketingAdConnection $c) => $c->isUsable());

        if ($connections->isEmpty()) {
            $this->info('Meta Ads is not connected; nothing to sync.');

            return Command::SUCCESS;
        }

        $days = $this->option('days') !== null ? max(1, (int) $this->option('days')) : null;
        $failed = false;

        foreach ($connections as $connection) {
            $this->info('Meta Ads — '.$connection->label());

            try {
                if (! $this->option('skip-discovery')) {
                    $this->info('  Found '.$sync->discoverAccounts($connection).' account(s).');
                }

                $result = $sync->syncAll($connection, $days);
            } catch (MetaAdsException $e) {
                // A rejected token needs a new one pasted; anything else is worth another run.
                $connection->forceFill([
                    'status' => $e->tokenRejected ? MarketingAdConnection::STATUS_ERROR : $connection->status,
                    'last_error' => mb_substr($e->getMessage(), 0, 2000),
                ])->save();
                $this->error('  '.$e->getMessage());
                $failed = true;

                continue;
            }

            if ($result['failed'] === 0) {
                $connection->forceFill([
                    'status' => MarketingAdConnection::STATUS_CONNECTED,
                    'last_error' => null,
                ])->save();
            } else {
                $failed = true;
            }

            $this->info("  Synced {$result['synced']} account(s); {$result['failed']} failed; {$result['waiting']} waiting for a location (not synced).");

            foreach ($result['errors'] as $accountId => $message) {
                $this->warn("    {$accountId}: {$message}");
            }
        }

        return $failed ? Command::FAILURE : Command::SUCCESS;
    }
}
