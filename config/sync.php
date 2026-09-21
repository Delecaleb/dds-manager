<?php

/*
|--------------------------------------------------------------------------
| OpenDental sync runtime
|--------------------------------------------------------------------------
| Tuned for shared hosting: no Redis, no supervisor, cron-only. Every
| (office, module) pair runs as its own queued job on the database queue,
| drained by short-lived workers the scheduler starts every minute.
|
| Timing invariants (keep these true when changing values):
|   time_budget  <  job_timeout  <  queue retry_after (SYNC_QUEUE_RETRY_AFTER)
|   one API batch incl. retries  <  stale_after_seconds
*/

return [

    /*
    | A sync_logs row marked "running" whose heartbeat is older than this is
    | considered abandoned (process killed by the host) and may be taken over.
    | Matches the "Stuck" threshold used by SyncReportService.
    */
    'stale_after_seconds' => (int) env('SYNC_STALE_AFTER_SECONDS', 600),

    /*
    | Attempts per OpenDental API call before the batch fails. Long waits are
    | left to the queue backoff so a single job never blocks a worker for long.
    */
    'api_max_attempts' => (int) env('SYNC_API_MAX_ATTEMPTS', 3),

    /*
    | Attempts for a local upsert that hits a MySQL deadlock / lock wait timeout.
    */
    'db_lock_attempts' => (int) env('SYNC_DB_LOCK_ATTEMPTS', 3),

    /*
    | Hard-delete pruning: removes local rows OpenDental no longer has.
    | OpenDental hard-deletes rows (appointments, procedures, splits…), so the
    | incremental sync alone never notices them.
    */
    'prune' => [
        // Rolling window checked twice daily: recent past + upcoming schedule.
        'rolling_past_days' => (int) env('SYNC_PRUNE_PAST_DAYS', 7),
        'rolling_future_days' => (int) env('SYNC_PRUNE_FUTURE_DAYS', 90),

        // Local keys checked per OpenDental lookup (one API call per chunk).
        'chunk_size' => (int) env('SYNC_PRUNE_CHUNK_SIZE', 500),

        // Mass-delete guard: refuse to delete when OpenDental reports more than
        // this share of a chunk missing (wrong API key/database looks exactly
        // like "everything was deleted"). Override per run with --force.
        'max_missing_ratio' => (float) env('SYNC_PRUNE_MAX_MISSING_RATIO', 0.5),
        'max_missing_min_rows' => (int) env('SYNC_PRUNE_MAX_MISSING_MIN_ROWS', 20),

        // Weekly full scan of every local row. Costs one API call per chunk_size
        // rows per table per office — disable if OpenDental API usage is a concern.
        'weekly_full_scan' => (bool) env('SYNC_PRUNE_WEEKLY_FULL_SCAN', true),
    ],

    'queue' => [
        'connection' => env('SYNC_QUEUE_CONNECTION', 'sync-database'),
        'name' => env('SYNC_QUEUE', 'sync'),

        // Drained before the regular queue. Used for work with a deadline, e.g. the
        // morning prune that must finish before the 08:00 schedule snapshot.
        'priority_name' => env('SYNC_PRIORITY_QUEUE', 'sync-priority'),

        // Concurrent workers started by cron. Keep low on shared hosting
        // (process + MySQL connection limits). 1–2 is plenty for incrementals.
        'workers' => (int) env('SYNC_QUEUE_WORKERS', 1),

        // Whether `schedule:run` starts the workers (as background processes).
        // Hosts that kill a cron job's child processes when the job ends
        // (e.g. CloudLinux) must set this false and run the worker from its own
        // cron line instead: php artisan queue:work … --stop-when-empty --max-time=280
        'scheduler_starts_workers' => (bool) env('SYNC_SCHEDULER_STARTS_WORKERS', true),

        // Whether slow scheduled tasks (pending-request recovery, schedule
        // snapshots) run as background processes. False on the same hosts:
        // they then run inside schedule:run, one after another.
        'background_tasks' => (bool) env('SYNC_SCHEDULE_BACKGROUND_TASKS', true),

        // How often the host runs `schedule:run`. Some shared hosts only allow
        // every 5 minutes. Above 1, workers keep polling for new jobs until just
        // before the next cron run instead of exiting when the queue is empty —
        // otherwise freshly dispatched syncs would wait a full cron interval.
        'cron_interval_minutes' => max(1, (int) env('SYNC_CRON_INTERVAL_MINUTES', 1)),

        // A worker stops taking new jobs after this many seconds and exits;
        // cron starts a fresh one on its next run. Ignored when
        // cron_interval_minutes > 1 (derived from the interval instead).
        'worker_max_time' => (int) env('SYNC_WORKER_MAX_TIME', 240),

        // A job stops cleanly after this many seconds (cursor saved) and
        // re-queues itself to continue. Protects against host CPU-time kills.
        'time_budget' => (int) env('SYNC_JOB_TIME_BUDGET', 240),

        // Hard kill for a hung job (only enforced when pcntl is available).
        'job_timeout' => (int) env('SYNC_JOB_TIMEOUT', 600),

        // Delay before a budget-paused job continues.
        'continue_delay' => (int) env('SYNC_JOB_CONTINUE_DELAY', 5),

        // Real failures (exceptions) tolerated before the job lands in failed_jobs.
        'max_exceptions' => (int) env('SYNC_JOB_MAX_EXCEPTIONS', 3),

        // Seconds to wait before retrying after a failure.
        'backoff' => [60, 300, 900],

        // Upper bound for a job's lifetime including continuations — large
        // enough for an initial full sync of a multi-million-row table.
        'retry_until_hours' => (int) env('SYNC_JOB_RETRY_UNTIL_HOURS', 24),

        // Prevents the same (office, module) job piling up in the queue when
        // workers fall behind.
        'unique_for' => (int) env('SYNC_JOB_UNIQUE_FOR', 3600),
    ],

];
