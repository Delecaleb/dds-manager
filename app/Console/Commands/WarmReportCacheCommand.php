<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Throwable;

/**
 * Recomputes the cached results of the main report pages after syncs (speed plan Phase 4),
 * so the first person to open a page after a sync gets a cached result instead of paying
 * the full computation.
 *
 * It requests each page's data endpoint exactly as the browser does, for "all locations"
 * and the ranges people open most (month to date — the date picker's default — and year to
 * date). Results are cached per office, so this also serves single-office views, and an
 * office whose data did not change since the last run is a cache hit (milliseconds). Only
 * offices a sync touched are recomputed.
 */
class WarmReportCacheCommand extends Command
{
    protected $signature = 'reports:warm {--locations=all : The locations param to warm}';

    protected $description = 'Recompute cached report results that a sync invalidated (main report pages)';

    private const ENDPOINTS = [
        // Dashboard
        '/dashboard/data', '/dashboard/financials-per-location', '/dashboard/location-stats',
        '/dashboard/patient-visits-per-location', '/dashboard/providers',
        // Financials
        '/financials/data', '/financials/revenue', '/financials/score-cards',
        // Deposit slip
        '/deposits/data',
        // KPIs
        '/kpis/office', '/kpis/hygiene', '/kpis/doctor',
        // Operations (default sub-tabs)
        '/operations/data/offices', '/operations/data/payors', '/operations/data/cancellations',
        '/operations/data/performance', '/operations/data/services', '/operations/data/providers',
        '/operations/data/trends', '/operations/data/compliance', '/operations/data/production-details',
        '/operations/data/marketing',
    ];

    public function handle(HttpKernel $kernel): int
    {
        if (! config('metric_cache.enabled')) {
            $this->info('Metric cache is disabled; nothing to warm.');

            return self::SUCCESS;
        }

        $user = User::query()->where('role', 'super_admin')->first();
        if ($user === null) {
            $this->error('No super_admin user to run the report requests as.');

            return self::FAILURE;
        }

        // The requests need a session, but these must not leave rows in the sessions table.
        config(['session.driver' => 'array']);

        $today = now()->toDateString();
        $ranges = [
            'month to date' => [now()->startOfMonth()->toDateString(), $today],
            'year to date' => [now()->startOfYear()->toDateString(), $today],
        ];

        $failures = 0;
        $started = microtime(true);

        foreach ($ranges as $label => [$start, $end]) {
            $query = http_build_query(['locations' => $this->option('locations'), 'start_date' => $start, 'end_date' => $end]);

            foreach (self::ENDPOINTS as $path) {
                $t = microtime(true);

                try {
                    auth()->login($user);
                    $request = Request::create($path.'?'.$query, 'GET');
                    $response = $kernel->handle($request);
                    $kernel->terminate($request, $response);
                    $status = $response->getStatusCode();
                } catch (Throwable $e) {
                    $status = 'error: '.$e->getMessage();
                }

                if ($status !== 200) {
                    $failures++;
                }

                $this->line(sprintf('%-14s %-45s %s  %6.0f ms', $label, $path, $status, (microtime(true) - $t) * 1000));
            }
        }

        $this->info(sprintf('Warmed %d requests in %.1f s, %d failed.', count($ranges) * count(self::ENDPOINTS), microtime(true) - $started, $failures));

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }
}
