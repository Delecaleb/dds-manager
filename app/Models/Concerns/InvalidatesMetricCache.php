<?php

namespace App\Models\Concerns;

use App\Domain\Support\MetricCache;

/**
 * For app-edited rows that reports read (goals, office names, report settings): saving or
 * deleting one invalidates its office's cached report results. Synced od_* tables are
 * handled by the sync services instead.
 */
trait InvalidatesMetricCache
{
    public static function bootInvalidatesMetricCache(): void
    {
        $invalidate = function ($model): void {
            foreach ($model->metricCacheOfficeIds() as $officeId) {
                app(MetricCache::class)->invalidateOffice($officeId);
            }
        };

        static::saved($invalidate);
        static::deleted($invalidate);
    }

    /** @return list<int> the offices whose cached results this row feeds */
    protected function metricCacheOfficeIds(): array
    {
        return $this->office_id === null ? [] : [(int) $this->office_id];
    }
}
