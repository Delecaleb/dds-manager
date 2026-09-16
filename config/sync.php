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

    'queue' => [
        'connection' => env('SYNC_QUEUE_CONNECTION', 'sync-database'),
        'name' => env('SYNC_QUEUE', 'sync'),

        // Concurrent workers started by cron. Keep low on shared hosting
        // (process + MySQL connection limits). 1–2 is plenty for incrementals.
        'workers' => (int) env('SYNC_QUEUE_WORKERS', 1),

        // A worker stops taking new jobs after this many seconds and exits;
        // cron starts a fresh one the next minute.
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
