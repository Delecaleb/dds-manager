<?php

namespace App\Console\Commands;

use App\Services\Sync\QueueHealthService;
use Illuminate\Console\Command;

class SyncHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:health
                            {--requeue : Re-queue pending sync requests that have no job in the queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show whether cron and the sync queue worker are running, what is queued, and why requests are stuck.';

    public function handle(QueueHealthService $health): int
    {
        if ($this->option('requeue')) {
            $this->info('Re-queued '.$health->requeueMissingRequests().' sync request(s).');
        }

        $snapshot = $health->snapshot();
        $ago = fn (array $beat) => $beat['minutes_ago'] === null ? 'never seen' : $beat['minutes_ago'].' min ago';

        $this->newLine();
        $this->line('<options=bold>Diagnosis</>');

        foreach ($snapshot['diagnosis'] as $finding) {
            $tag = match ($finding['level']) {
                'error' => '<fg=red;options=bold>ERROR</>',
                'warning' => '<fg=yellow;options=bold>WARN </>',
                'info' => '<fg=cyan>INFO </>',
                default => '<fg=green;options=bold>OK   </>',
            };

            $this->line("  {$tag} {$finding['title']}");
            $this->line("        {$finding['detail']}");

            if ($finding['fix']) {
                $this->line("        <fg=green>Fix:</> {$finding['fix']}");
            }
        }

        $this->newLine();
        $this->table(['Check', 'Value'], [
            ['Cron (schedule:run) last ran', $ago($snapshot['scheduler'])],
            ['Cron PHP version', $snapshot['scheduler_php'] ?? '—'],
            ['Observed cron interval', $snapshot['observed_cron_minutes'] !== null ? $snapshot['observed_cron_minutes'].' min' : '—'],
            ['Sync worker last seen', $ago($snapshot['worker'])],
            ['Worker last job', $snapshot['worker_last_job'] ?? '—'],
            ['Workers configured / queues read', $snapshot['worker_count'].' / '.implode(', ', $snapshot['worker_queues'])],
            ['Config cached', $snapshot['config_cached'] ? 'yes' : 'no'],
            ['Jobs in queue table', $snapshot['jobs_total'].($snapshot['jobs_truncated'] ? ' (first 5000 inspected)' : '')],
            ['Sync requests pending / running', $snapshot['pending_requests'].' / '.$snapshot['running_requests']],
            ['Jobs ahead of first request', $snapshot['jobs_ahead_of_requests'] ?? '—'],
        ]);

        if ($snapshot['jobs_by_type'] !== []) {
            $this->table(
                ['Queue', 'Job', 'Waiting', 'In progress', 'Later', 'Max attempts', 'Oldest (min)', 'Worker reads it'],
                array_map(fn (array $group) => [
                    $group['queue'], $group['job'], $group['waiting'], $group['in_progress'],
                    $group['scheduled_later'], $group['max_attempts'], $group['oldest_minutes'],
                    $group['read_by_worker'] ? 'yes' : 'NO',
                ], $snapshot['jobs_by_type'])
            );
        }

        if ($snapshot['failed_jobs'] !== []) {
            $this->table(
                ['ID', 'Job', 'Failed at', 'Error'],
                array_map(fn (array $failed) => [$failed['id'], $failed['job'], $failed['failed_at'], mb_substr($failed['error'], 0, 120)], $snapshot['failed_jobs'])
            );
        }

        return Command::SUCCESS;
    }
}
