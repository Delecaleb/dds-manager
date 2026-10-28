<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;

/**
 * Read-only performance profiler for the report endpoints (speed plan §1).
 *
 * Dispatches each endpoint through the HTTP kernel as a real user and prints wall time,
 * SQL time, query count, the slowest queries and a hash of the response body. Same filters
 * and the same hash before and after a change = the change did not alter any number.
 *
 * An artisan command rather than a script file: the live docroot is the project root, so a
 * PHP script in the repo would be reachable from the web.
 */
class PerfProfileCommand extends Command
{
    protected $signature = 'perf:profile
        {endpoints?* : Paths to profile, e.g. dashboard/data (default: the main report endpoints)}
        {--locations=all : The locations param sent with every request}
        {--start= : start_date (default: Jan 1 of this year)}
        {--end= : end_date (default: today)}
        {--user= : Email of the user to run as (default: first super_admin)}
        {--repeat=1 : Requests per endpoint; >1 shows warm-cache timings}
        {--slow=3 : How many of the slowest queries to list per request}
        {--save= : Directory to write each response body to, for comparing before/after a change}';

    protected $description = 'Read-only: time report endpoints (wall/SQL time, query count, response hash)';

    private const DEFAULT_ENDPOINTS = [
        '/dashboard/data',
        '/dashboard/financials-per-location',
        '/dashboard/patient-visits-per-location',
        '/dashboard/location-stats',
        '/dashboard/providers',
        '/kpis/office',
        '/kpis/hygiene',
        '/kpis/doctor',
        '/operations/data/offices',
    ];

    public function handle(HttpKernel $kernel): int
    {
        $user = $this->option('user')
            ? User::query()->where('email', $this->option('user'))->first()
            : User::query()->where('role', 'super_admin')->first();

        if ($user === null) {
            $this->error('No user to run as. Pass --user=<email>.');

            return self::FAILURE;
        }

        $query = http_build_query([
            'locations' => $this->option('locations'),
            'start_date' => $this->option('start') ?: now()->startOfYear()->toDateString(),
            'end_date' => $this->option('end') ?: now()->toDateString(),
        ]);

        // A leading slash is optional (Git Bash on Windows rewrites "/x" into a file path).
        $endpoints = array_map(
            fn (string $path) => '/'.ltrim($path, '/'),
            $this->argument('endpoints') ?: self::DEFAULT_ENDPOINTS
        );
        $repeat = max(1, (int) $this->option('repeat'));
        $slowCount = max(0, (int) $this->option('slow'));

        $this->info("As {$user->email} — {$query}");

        foreach ($endpoints as $path) {
            for ($run = 1; $run <= $repeat; $run++) {
                $this->profile($kernel, $user, $path.'?'.$query, $run, $slowCount);
            }
        }

        return self::SUCCESS;
    }

    private function profile(HttpKernel $kernel, User $user, string $uri, int $run, int $slowCount): void
    {
        $queries = [];
        Event::listen(QueryExecuted::class, function (QueryExecuted $q) use (&$queries) {
            $queries[] = [$q->time, preg_replace('/\s+/', ' ', $q->sql)];
        });

        auth()->login($user);
        $startedAt = microtime(true);
        $response = $kernel->handle(Request::create($uri, 'GET'));
        $wallMs = (microtime(true) - $startedAt) * 1000;

        Event::forget(QueryExecuted::class);
        $kernel->terminate(Request::create($uri, 'GET'), $response);

        if ($dir = $this->option('save')) {
            File::ensureDirectoryExists($dir);
            File::put($dir.'/'.trim(str_replace('/', '_', strtok($uri, '?')), '_').'.json', (string) $response->getContent());
        }

        $sqlMs = array_sum(array_column($queries, 0));
        usort($queries, fn ($a, $b) => $b[0] <=> $a[0]);

        $this->line(sprintf(
            '%-45s run %d  status=%d  total=%7.0fms  sql=%7.0fms  queries=%4d  body=%s',
            strtok($uri, '?'),
            $run,
            $response->getStatusCode(),
            $wallMs,
            $sqlMs,
            count($queries),
            substr(md5((string) $response->getContent()), 0, 10)
        ));

        foreach (array_slice($queries, 0, $slowCount) as [$ms, $sql]) {
            $this->line(sprintf('      %7.0fms  %s', $ms, mb_substr($sql, 0, 150)));
        }
    }
}
