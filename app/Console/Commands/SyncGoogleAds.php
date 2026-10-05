<?php

namespace App\Console\Commands;

use App\Domain\Marketing\GoogleAds\GoogleAdsException;
use App\Domain\Marketing\GoogleAds\GoogleAdsSyncService;
use App\Models\MarketingAdConnection;
use Illuminate\Console\Command;

/**
 * Pull Google Ads accounts, campaigns and daily stats into the local reporting tables.
 * Scheduled in routes/console.php; safe to run by hand at any time (every write is an upsert).
 */
class SyncGoogleAds extends Command
{
    protected $signature = 'marketing:google-ads-sync
                            {--days= : Trailing days to pull (default: full backfill on an account\'s first run, then the refresh window)}
                            {--skip-discovery : Do not re-list the ad accounts first}';

    protected $description = 'Sync Google Ads campaigns and daily stats for the connected accounts';

    public function handle(GoogleAdsSyncService $sync): int
    {
        $connection = MarketingAdConnection::googleAds();

        if ($connection === null || ! $connection->isUsable()) {
            $this->info('Google Ads is not connected; nothing to sync.');

            return Command::SUCCESS;
        }

        $days = $this->option('days') !== null ? max(1, (int) $this->option('days')) : null;

        try {
            if (! $this->option('skip-discovery')) {
                $this->info('Found '.$sync->discoverAccounts($connection).' account(s).');
            }

            $result = $sync->syncAll($connection, $days);
        } catch (GoogleAdsException $e) {
            // Could not reach Google at all (expired token, bad developer token, outage).
            $connection->forceFill(['last_error' => mb_substr($e->getMessage(), 0, 2000)])->save();
            $this->error($e->getMessage());

            return Command::FAILURE;
        }

        if ($result['failed'] === 0) {
            $connection->forceFill([
                'status' => MarketingAdConnection::STATUS_CONNECTED,
                'last_error' => null,
            ])->save();
        }

        $this->info("Synced {$result['synced']} account(s); {$result['failed']} failed.");

        foreach ($result['errors'] as $accountId => $message) {
            $this->warn("  {$accountId}: {$message}");
        }

        return $result['failed'] > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
