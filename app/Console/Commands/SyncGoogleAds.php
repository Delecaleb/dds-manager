<?php

namespace App\Console\Commands;

use App\Domain\Marketing\GoogleAds\GoogleAdsException;
use App\Domain\Marketing\GoogleAds\GoogleAdsSyncService;
use App\Models\MarketingAdConnection;
use Illuminate\Console\Command;

/**
 * Pull Google Ads accounts, campaigns and daily stats into the local reporting tables, for
 * every connected Google login. Only accounts assigned to a location are synced, and never
 * further back than 90 days before the account was connected. Scheduled in routes/console.php;
 * safe to run by hand at any time (every write is an upsert). One login failing never stops the others.
 */
class SyncGoogleAds extends Command
{
    protected $signature = 'marketing:google-ads-sync
                            {--days= : Trailing days to pull (default: the account\'s whole history window on its first run, then the refresh window; never before its history start)}
                            {--skip-discovery : Do not re-list the ad accounts first}';

    protected $description = 'Sync Google Ads campaigns and daily stats for every account assigned to a location';

    public function handle(GoogleAdsSyncService $sync): int
    {
        $connections = MarketingAdConnection::query()->googleAds()->get()->filter(fn (MarketingAdConnection $c) => $c->isUsable());

        if ($connections->isEmpty()) {
            $this->info('Google Ads is not connected; nothing to sync.');

            return Command::SUCCESS;
        }

        $days = $this->option('days') !== null ? max(1, (int) $this->option('days')) : null;
        $failed = false;

        foreach ($connections as $connection) {
            $this->info('Google Ads — '.$connection->label());

            try {
                if (! $this->option('skip-discovery')) {
                    $this->info('  Found '.$sync->discoverAccounts($connection).' account(s).');
                }

                $result = $sync->syncAll($connection, $days);
            } catch (GoogleAdsException $e) {
                // Could not reach Google at all (expired token, bad developer token, outage).
                $connection->forceFill(['last_error' => mb_substr($e->getMessage(), 0, 2000)])->save();
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
