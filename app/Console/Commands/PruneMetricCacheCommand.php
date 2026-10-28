<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Deletes expired rows from the database cache table.
 *
 * MetricCache never reads an entry again once its office's version changes, and the
 * database store only removes an expired row when that exact key is read. Without this,
 * superseded report results would pile up until something deleted them.
 */
class PruneMetricCacheCommand extends Command
{
    protected $signature = 'metrics:prune-cache';

    protected $description = 'Delete expired rows from the database cache table (superseded report results)';

    public function handle(): int
    {
        $storeName = config('metric_cache.store') ?? config('cache.default');
        $store = config("cache.stores.{$storeName}");

        if (($store['driver'] ?? null) !== 'database') {
            $this->info("Cache store '{$storeName}' expires entries itself; nothing to prune.");

            return self::SUCCESS;
        }

        $deleted = 0;

        // Chunked so a large backlog never holds a long lock on the table.
        do {
            $batch = DB::connection($store['connection'] ?? null)
                ->table($store['table'] ?? 'cache')
                ->where('expiration', '<=', now()->getTimestamp())
                ->limit(5000)
                ->delete();
            $deleted += $batch;
        } while ($batch === 5000);

        $this->info("Deleted {$deleted} expired cache rows.");

        return self::SUCCESS;
    }
}
