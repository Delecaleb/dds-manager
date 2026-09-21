<?php

namespace App\Services\Sync;

use App\Jobs\ProcessSyncRequest;
use App\Models\SyncRequest;
use Illuminate\Bus\UniqueLock;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Answers "why is nothing syncing?" for the Sync Manager and `sync:health`.
 *
 * Cron and the queue worker leave heartbeats in the cache, which separates the
 * usual failures: cron not running schedule:run, cron running but no worker
 * processing jobs, jobs parked on a queue no worker reads, requests waiting
 * behind a backlog, and requests missing from the queue altogether.
 */
class QueueHealthService
{
    private const SCHEDULER_KEY = 'sync:health:scheduler';

    private const WORKER_KEY = 'sync:health:worker';

    /** Queue rows inspected per check; a larger backlog is reported as "at least". */
    private const MAX_JOBS_INSPECTED = 5000;

    /** Stale unique locks are only looked for on this many missing requests. */
    private const MAX_LOCK_CHECKS = 50;

    /** In-process throttle for the idle-worker heartbeat. */
    private static int $lastWorkerBeat = 0;

    /**
     * Called by the scheduler on every `schedule:run`, i.e. every cron run.
     */
    public function recordSchedulerTick(): void
    {
        $previous = Cache::get(self::SCHEDULER_KEY);

        Cache::forever(self::SCHEDULER_KEY, [
            'at' => now()->toIso8601String(),
            'previous_at' => $previous['at'] ?? null,
            'php' => PHP_VERSION,
        ]);
    }

    /**
     * Called by a sync worker while it runs. Throttled in-process, so an idle
     * worker polling the queue writes at most once a minute.
     */
    public function recordWorkerAlive(?string $job = null): void
    {
        if ($job === null && time() - self::$lastWorkerBeat < 60) {
            return;
        }

        self::$lastWorkerBeat = time();

        Cache::forever(self::WORKER_KEY, [
            'at' => now()->toIso8601String(),
            'job' => $job ?? (Cache::get(self::WORKER_KEY)['job'] ?? null),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        $now = now();
        $scheduler = Cache::get(self::SCHEDULER_KEY);
        $worker = Cache::get(self::WORKER_KEY);
        $workerQueues = [(string) config('sync.queue.priority_name'), (string) config('sync.queue.name')];
        $cronInterval = max(1, (int) config('sync.queue.cron_interval_minutes', 1));

        $jobs = $this->queuedJobs();
        $pendingIds = SyncRequest::where('status', 'pending')->orderBy('id')->pluck('id')->map(fn ($id) => (int) $id)->all();
        $queuedRequestIds = array_filter(array_column($jobs['rows'], 'sync_request_id'));
        $missingIds = $jobs['truncated'] ? [] : array_values(array_diff($pendingIds, $queuedRequestIds));

        $health = [
            'checked_at' => $now->toIso8601String(),
            'scheduler' => $this->beat($scheduler, $now),
            'scheduler_php' => $scheduler['php'] ?? null,
            'observed_cron_minutes' => $this->observedInterval($scheduler),
            'worker' => $this->beat($worker, $now),
            'worker_last_job' => $worker['job'] ?? null,
            'config_cached' => app()->configurationIsCached(),
            'cron_interval_minutes' => $cronInterval,
            'worker_count' => (int) config('sync.queue.workers', 1),
            'worker_queues' => $workerQueues,
            'jobs_total' => $jobs['total'],
            'jobs_truncated' => $jobs['truncated'],
            'jobs_by_type' => $this->jobsByType($jobs['rows'], $workerQueues, $now),
            'jobs_ahead_of_requests' => $this->jobsAheadOfRequests($jobs['rows'], $workerQueues, $now),
            'pending_requests' => count($pendingIds),
            'running_requests' => SyncRequest::where('status', 'running')->count(),
            'missing_request_ids' => $missingIds,
            'stale_lock_request_ids' => $this->staleLockIds(array_slice($missingIds, 0, self::MAX_LOCK_CHECKS)),
            'failed_jobs' => $this->failedJobs(),
            'failed_last_24h' => $this->failedSince($now->copy()->subDay()),
        ];

        $health['diagnosis'] = $this->diagnose($health, $jobs['rows'], $workerQueues, $now);

        return $health;
    }

    /**
     * Put pending requests that have no queued job back on the queue, clearing
     * a stale unique lock first (it would silently drop the dispatch again).
     *
     * @return int requests re-queued
     */
    public function requeueMissingRequests(): int
    {
        $missing = $this->snapshot()['missing_request_ids'];

        foreach ($missing as $id) {
            Cache::lock(UniqueLock::getKey(new ProcessSyncRequest($id)))->forceRelease();
            ProcessSyncRequest::dispatch($id);
        }

        return count($missing);
    }

    /**
     * @param  array{at?: string}|null  $beat
     * @return array{at: ?string, minutes_ago: ?int}
     */
    private function beat(?array $beat, Carbon $now): array
    {
        if (empty($beat['at'])) {
            return ['at' => null, 'minutes_ago' => null];
        }

        $at = Carbon::parse($beat['at']);

        return ['at' => $at->toIso8601String(), 'minutes_ago' => (int) $at->diffInMinutes($now)];
    }

    private function observedInterval(?array $scheduler): ?int
    {
        if (empty($scheduler['at']) || empty($scheduler['previous_at'])) {
            return null;
        }

        return (int) round(Carbon::parse($scheduler['previous_at'])->diffInMinutes(Carbon::parse($scheduler['at'])));
    }

    /**
     * Every row of the jobs table (all queues, so jobs parked on a queue no
     * worker reads show up too), oldest first.
     *
     * @return array{rows: list<array<string, mixed>>, total: int, truncated: bool}
     */
    private function queuedJobs(): array
    {
        $connection = config('queue.connections.'.config('sync.queue.connection'), []);
        $query = DB::connection($connection['connection'] ?? null)->table($connection['table'] ?? 'jobs');

        $total = (clone $query)->count();
        $rows = [];

        $records = $query->orderBy('id')
            ->limit(self::MAX_JOBS_INSPECTED)
            ->get(['id', 'queue', 'payload', 'attempts', 'reserved_at', 'available_at', 'created_at']);

        foreach ($records as $record) {
            $payload = json_decode((string) $record->payload, true) ?: [];
            $name = (string) ($payload['displayName'] ?? 'Unknown');
            $requestId = null;

            if ($name === ProcessSyncRequest::class
                && preg_match('/"syncRequestId";i:(\d+);/', (string) ($payload['data']['command'] ?? ''), $match)) {
                $requestId = (int) $match[1];
            }

            $rows[] = [
                'id' => (int) $record->id,
                'queue' => (string) $record->queue,
                'job' => class_basename($name),
                'attempts' => (int) $record->attempts,
                'reserved' => $record->reserved_at !== null,
                'available_at' => (int) $record->available_at,
                'created_at' => (int) $record->created_at,
                'sync_request_id' => $requestId,
            ];
        }

        return ['rows' => $rows, 'total' => $total, 'truncated' => $total > count($rows)];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  list<string>  $workerQueues
     * @return list<array<string, mixed>>
     */
    private function jobsByType(array $rows, array $workerQueues, Carbon $now): array
    {
        $groups = [];

        foreach ($rows as $row) {
            $key = $row['queue'].'|'.$row['job'];
            $groups[$key] ??= [
                'queue' => $row['queue'],
                'job' => $row['job'],
                'waiting' => 0,
                'in_progress' => 0,
                'scheduled_later' => 0,
                'max_attempts' => 0,
                'oldest_created_at' => $row['created_at'],
                'read_by_worker' => in_array($row['queue'], $workerQueues, true),
            ];

            $group = &$groups[$key];

            if ($row['reserved']) {
                $group['in_progress']++;
            } elseif ($row['available_at'] > $now->timestamp) {
                $group['scheduled_later']++;
            } else {
                $group['waiting']++;
            }

            $group['max_attempts'] = max($group['max_attempts'], $row['attempts']);
            $group['oldest_created_at'] = min($group['oldest_created_at'], $row['created_at']);
            unset($group);
        }

        return array_values(array_map(function (array $group) use ($now) {
            $group['oldest_minutes'] = (int) floor(($now->timestamp - $group['oldest_created_at']) / 60);
            unset($group['oldest_created_at']);

            return $group;
        }, $groups));
    }

    /**
     * Jobs a worker will take before the first sync request: the whole priority
     * queue, then everything older on the regular queue.
     *
     * @param  list<array<string, mixed>>  $rows
     * @param  list<string>  $workerQueues
     */
    private function jobsAheadOfRequests(array $rows, array $workerQueues, Carbon $now): ?int
    {
        [$priorityQueue, $regularQueue] = $workerQueues;
        $firstRequest = null;

        foreach ($rows as $row) {
            if ($row['sync_request_id'] !== null && $row['queue'] === $regularQueue) {
                $firstRequest = $row;
                break;
            }
        }

        if ($firstRequest === null) {
            return null;
        }

        $ahead = 0;

        foreach ($rows as $row) {
            $available = ! $row['reserved'] && $row['available_at'] <= $now->timestamp;

            if ($available && $row['sync_request_id'] === null
                && ($row['queue'] === $priorityQueue || ($row['queue'] === $regularQueue && $row['id'] < $firstRequest['id']))) {
                $ahead++;
            }
        }

        return $ahead;
    }

    /**
     * Missing requests whose unique lock is still held: every dispatch of them
     * is silently dropped until the lock expires.
     *
     * @param  list<int>  $requestIds
     * @return list<int>
     */
    private function staleLockIds(array $requestIds): array
    {
        $stale = [];

        foreach ($requestIds as $id) {
            $lock = Cache::lock(UniqueLock::getKey(new ProcessSyncRequest($id)), 1);

            try {
                if ($lock->get()) {
                    $lock->release();
                } else {
                    $stale[] = $id;
                }
            } catch (Throwable) {
                // Lock store unavailable: nothing useful to report.
            }
        }

        return $stale;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function failedJobs(): array
    {
        return DB::table('failed_jobs')
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'queue', 'payload', 'exception', 'failed_at'])
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'queue' => (string) $row->queue,
                'job' => class_basename((string) (json_decode((string) $row->payload, true)['displayName'] ?? 'Unknown')),
                'error' => mb_substr(strtok((string) $row->exception, "\n") ?: '', 0, 300),
                'failed_at' => (string) $row->failed_at,
            ])
            ->all();
    }

    private function failedSince(Carbon $since): int
    {
        return DB::table('failed_jobs')->where('failed_at', '>=', $since)->count();
    }

    /**
     * Plain-language findings, most serious first. Each has a level
     * (error|warning|info|ok), a title, what it means and how to fix it.
     *
     * @param  array<string, mixed>  $health
     * @param  list<array<string, mixed>>  $rows
     * @param  list<string>  $workerQueues
     * @return list<array{level: string, title: string, detail: string, fix: ?string}>
     */
    private function diagnose(array $health, array $rows, array $workerQueues, Carbon $now): array
    {
        $findings = [];
        $cronLine = 'cd '.base_path().' && php artisan schedule:run >> '.storage_path('logs/cron.log').' 2>&1';
        $schedulerAge = $health['scheduler']['minutes_ago'];
        $schedulerLate = max(10, $health['cron_interval_minutes'] * 2 + 2);
        $waiting = count(array_filter($rows, fn ($row) => ! $row['reserved']
            && $row['available_at'] <= $now->timestamp && in_array($row['queue'], $workerQueues, true)));
        $inProgress = count(array_filter($rows, fn ($row) => $row['reserved']));

        if ($schedulerAge === null) {
            $findings[] = [
                'level' => 'error',
                'title' => 'Cron has not run the scheduler yet',
                'detail' => 'No run of `schedule:run` has been recorded since this check was installed. If you deployed within the last few minutes, wait one cron cycle and refresh.',
                'fix' => "In cPanel → Cron Jobs, run every minute (or every 5): {$cronLine} — use a PHP 8.2+ binary (cPanel shows it as e.g. /usr/local/bin/ea-php83). Any error lands in storage/logs/cron.log.",
            ];
        } elseif ($schedulerAge > $schedulerLate) {
            $findings[] = [
                'level' => 'error',
                'title' => "Cron stopped running the scheduler {$schedulerAge} minutes ago",
                'detail' => 'Nothing is being dispatched or processed until cron runs again.',
                'fix' => "Check the cPanel cron entry still exists and points to this folder: {$cronLine}. Then read storage/logs/cron.log for the error.",
            ];
        }

        $workerAge = $health['worker']['minutes_ago'];
        $workerLate = max(15, $health['cron_interval_minutes'] * 3);

        if ($schedulerAge !== null && $schedulerAge <= $schedulerLate && $waiting > 0 && $inProgress === 0
            && ($workerAge === null || $workerAge > $workerLate)) {
            $findings[] = [
                'level' => 'error',
                'title' => 'Cron runs, but no queue worker is processing jobs',
                'detail' => "{$waiting} job(s) are waiting and no worker has been seen ".($workerAge === null ? 'since this check was installed' : "for {$workerAge} minutes").'. The scheduler starts the worker as a background process; on shared hosting that fails when PHP\'s proc_open/exec are disabled, or the worker crashes on start.',
                'fix' => 'Read storage/logs/cron.log and storage/logs/laravel.log for the error. If background processes are blocked, ask the host to enable proc_open, or add a second cron line that runs the worker directly: cd '.base_path().' && php artisan queue:work '.config('sync.queue.connection').' --queue='.implode(',', $workerQueues).' --stop-when-empty --max-time=280 --timeout=600 >> '.storage_path('logs/worker.log').' 2>&1',
            ];
        }

        $parked = array_filter($health['jobs_by_type'], fn ($group) => ! $group['read_by_worker']);

        if ($parked !== []) {
            $count = array_sum(array_map(fn ($group) => $group['waiting'] + $group['scheduled_later'], $parked));
            $queues = implode(', ', array_unique(array_column($parked, 'queue')));

            $findings[] = [
                'level' => 'warning',
                'title' => "{$count} job(s) are on a queue no sync worker reads ({$queues})",
                'detail' => 'The sync worker only reads '.implode(' and ', $workerQueues).'. Jobs elsewhere never run.',
                'fix' => $health['config_cached']
                    ? 'The configuration is cached and may be older than the code. Run: php artisan config:clear (or php artisan config:cache after editing .env), then re-queue.'
                    : 'Check SYNC_QUEUE / SYNC_QUEUE_CONNECTION in .env match on the web server and in cron.',
            ];
        }

        if ($health['missing_request_ids'] !== []) {
            $stale = count($health['stale_lock_request_ids']);

            $findings[] = [
                'level' => 'warning',
                'title' => count($health['missing_request_ids']).' queued sync request(s) have no job in the queue',
                'detail' => 'They show as Queued but nothing will run them.'.($stale > 0 ? " {$stale} are blocked by a leftover lock that makes the queue silently drop them." : ' The scheduler re-queues pending requests every 10 minutes.'),
                'fix' => 'Click "Re-queue missing requests" below.',
            ];
        }

        $ahead = $health['jobs_ahead_of_requests'];

        if ($ahead !== null && $ahead > 0 && ($workerAge !== null && $workerAge <= $workerLate)) {
            $findings[] = [
                'level' => 'info',
                'title' => "Your resync requests are waiting behind {$ahead} job(s)",
                'detail' => "The worker is running and takes jobs oldest first. With {$health['worker_count']} worker(s), regular scheduled syncs ahead of your requests are processed first.",
                'fix' => $ahead > 50
                    ? 'If this number does not go down between refreshes, add a worker: set SYNC_QUEUE_WORKERS=2 in .env, then run php artisan config:clear.'
                    : null,
            ];
        }

        if ($health['failed_last_24h'] > 0) {
            $findings[] = [
                'level' => 'warning',
                'title' => "{$health['failed_last_24h']} job(s) failed in the last 24 hours",
                'detail' => 'See the failed jobs list below for the error of each.',
                'fix' => 'Fix the cause shown in the error, then retry with: php artisan queue:retry all',
            ];
        }

        if ($findings === []) {
            $findings[] = [
                'level' => 'ok',
                'title' => 'Cron and the queue worker are running',
                'detail' => $waiting > 0 ? "{$waiting} job(s) waiting; they are being processed." : 'Nothing is waiting.',
                'fix' => null,
            ];
        }

        return $findings;
    }
}
