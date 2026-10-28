<?php

namespace App\Domain\Support;

use Closure;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Single source of truth for caching report results.
 *
 * A report result only changes when an office's local data changes, so it is cached until
 * then instead of for a fixed time. Each office has a version token; every write to that
 * office's data (sync upserts, prunes, repairs, goal edits) replaces it via
 * invalidateOffice(). Keys embed the versions of every office the result depends on, so
 * stale entries are never read again and nothing has to be flushed or tagged — which is
 * why this works on the plain `database` cache driver.
 *
 * Cache per office (or per location) where a report loops offices: after a sync, an
 * "All locations" view then recomputes only the offices that actually changed.
 *
 * @see speed_update_implementation_plan.md  (Phase 3)
 */
final class MetricCache
{
    private const PREFIX = 'metrics:';

    public function __construct(
        private readonly CacheFactory $caches,
    ) {}

    /**
     * @template T
     *
     * @param  string  $name  report identifier, e.g. 'kpis.hygiene'
     * @param  int[]  $officeIds  every office whose data the result reads
     * @param  array<mixed>  $params  every other input the result depends on (dates, clinics, …)
     * @param  Closure(): T  $compute
     * @return T
     */
    public function remember(string $name, array $officeIds, array $params, Closure $compute): mixed
    {
        if (! config('metric_cache.enabled')) {
            return $compute();
        }

        $officeIds = array_values(array_unique(array_map('intval', $officeIds)));
        sort($officeIds);

        $key = self::PREFIX.$name.':'.md5(json_encode([$officeIds, $this->versions($officeIds), $params]));

        $cached = $this->store()->get($key);
        if ($cached !== null) {
            return $cached;
        }

        $value = $compute();
        $this->put($key, $value, $name);

        return $value;
    }

    /**
     * Store a result unless it is too large to be worth caching (a long drill-down list), and
     * never let a failed write break the report: the computed value is returned either way.
     */
    private function put(string $key, mixed $value, string $name): void
    {
        $bytes = strlen(serialize($value));
        if ($bytes > (int) config('metric_cache.max_bytes', 512 * 1024)) {
            return;
        }

        try {
            $this->store()->put($key, $value, (int) config('metric_cache.ttl', 86400));
        } catch (Throwable $e) {
            Log::warning("MetricCache: could not store {$name} ({$bytes} bytes); served uncached.", [
                'error' => mb_substr($e->getMessage(), 0, 300),
            ]);
        }
    }

    /**
     * @template T
     *
     * @param  Closure(): T  $compute
     * @return T
     */
    public function rememberFilter(string $name, MetricFilter $filter, Closure $compute): mixed
    {
        return $this->remember($name, [$filter->officeId], [$filter->signature()], $compute);
    }

    /** Call after any write to an office's report data; its cached results are never read again. */
    public function invalidateOffice(int $officeId): void
    {
        $this->store()->forever($this->versionKey($officeId), $this->newVersion());
    }

    /**
     * @param  int[]  $officeIds
     * @return string[]
     */
    private function versions(array $officeIds): array
    {
        $keys = array_map(fn (int $id) => $this->versionKey($id), $officeIds);
        $found = $this->store()->many($keys);

        return array_map(function (string $key) use ($found) {
            if (($found[$key] ?? null) === null) {
                // First use (or the cache was cleared): start a fresh version. A random token
                // rather than a counter, so a lost version can never resurrect old entries.
                $found[$key] = $this->newVersion();
                $this->store()->forever($key, $found[$key]);
            }

            return $found[$key];
        }, $keys);
    }

    private function versionKey(int $officeId): string
    {
        return self::PREFIX.'version:'.$officeId;
    }

    private function newVersion(): string
    {
        return bin2hex(random_bytes(8));
    }

    private function store(): Repository
    {
        return $this->caches->store(config('metric_cache.store'));
    }
}
