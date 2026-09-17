<?php

namespace App\Console\Commands;

use App\Models\SyncLog;
use App\Models\SyncRequest;
use App\Services\Sync\SyncRequestRunner;
use Illuminate\Console\Command;

class ProcessPendingSyncRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:process-pending {--id= : Run one sync request now, in this process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue pending server-to-server date range sync requests (or run one with --id)';

    /**
     * Execute the console command.
     */
    public function handle(SyncRequestRunner $runner): int
    {
        $this->recoverAbandonedRequests();

        if ($this->option('id')) {
            return $this->runOne($runner, (int) $this->option('id'));
        }

        $pending = SyncRequest::where('status', 'pending')->orderBy('id')->get();

        if ($pending->isEmpty()) {
            $this->info('No pending sync requests found.');

            return Command::SUCCESS;
        }

        // Duplicates of an already-queued request are dropped by ShouldBeUnique.
        $pending->each(fn (SyncRequest $request) => $runner->queue($request));

        $this->info("Queued {$pending->count()} pending sync request(s).");

        return Command::SUCCESS;
    }

    private function runOne(SyncRequestRunner $runner, int $id): int
    {
        $request = SyncRequest::find($id);

        if ($request === null) {
            $this->error("Sync request #{$id} not found.");

            return Command::FAILURE;
        }

        $this->info("Running sync request #{$id} for module '{$request->module}'...");

        $outcome = $runner->run($request);
        $this->info("Sync request #{$id}: {$outcome}.");

        return $outcome === SyncRequestRunner::OUTCOME_FAILED ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Recover 'running' requests whose worker process is really gone.
     *
     * A long backfill legitimately runs for more than 10 minutes, so age alone
     * is not proof of death: a request counts as abandoned only when no sync for
     * its office has sent a heartbeat within the stale window.
     *
     * Hosts kill long-running processes, and every sync resumes from its saved
     * cursor, so an abandoned request is re-queued to continue. Only a request
     * that keeps dying for a day is given up on.
     */
    protected function recoverAbandonedRequests(): void
    {
        $staleAfter = (int) config('sync.stale_after_seconds', 600);
        $cutoff = now()->subSeconds($staleAfter);
        $giveUpBefore = now()->subDay();

        SyncRequest::where('status', 'running')
            ->where('started_at', '<', $cutoff)
            ->get()
            ->reject(fn (SyncRequest $req) => SyncLog::withoutGlobalScopes()
                ->where('module', 'like', "office_{$req->office_id}:%")
                ->where('status', 'running')
                ->where('updated_at', '>=', $cutoff)
                ->exists())
            ->each(function (SyncRequest $req) use ($giveUpBefore) {
                if ($req->created_at !== null && $req->created_at->lt($giveUpBefore)) {
                    $req->update([
                        'status' => 'failed',
                        'error_message' => 'Sync process was repeatedly terminated by the server for over 24 hours. Check storage/logs/laravel.log and failed jobs.',
                        'completed_at' => now(),
                    ]);

                    return;
                }

                $req->update([
                    'status' => 'pending',
                    'started_at' => null,
                    'error_message' => 'Worker stopped mid-run (server terminated the process); resuming from the saved position.',
                ]);
            });
    }
}
