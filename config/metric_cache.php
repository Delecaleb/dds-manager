<?php

/*
|--------------------------------------------------------------------------
| Report result cache (App\Domain\Support\MetricCache)
|--------------------------------------------------------------------------
|
| Report figures only change when a sync (or a goal edit) writes an office's data, so
| results are cached until then: every write bumps that office's version and old entries
| are simply never read again. The TTL is only a safety net and bounds table growth.
|
| See speed_update_implementation_plan.md, Phase 3.
|
*/

return [

    'enabled' => (bool) env('METRIC_CACHE_ENABLED', true),

    // Cache store to use; null = the app default (CACHE_STORE).
    'store' => env('METRIC_CACHE_STORE'),

    'ttl' => (int) env('METRIC_CACHE_TTL', 86400),

    // Results larger than this (serialized) are not cached: long drill-down lists cost more
    // to store and read back than to recompute, and a database cache row must also fit
    // MySQL's max_allowed_packet (1 MB on the local XAMPP install).
    'max_bytes' => (int) env('METRIC_CACHE_MAX_BYTES', 512 * 1024),

];
