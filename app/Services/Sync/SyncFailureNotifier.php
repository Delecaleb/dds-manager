<?php

namespace App\Services\Sync;

use App\Domain\Support\ClinicRegistry;
use App\Mail\SyncFailedMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Emails the super-admin recipient list when a sync job has failed for good — i.e. the queue
 * gave up after its retries (sync.queue.max_exceptions) and the job landed in failed_jobs.
 *
 * One outage fails many jobs at once (an office's eConnector going down fails every module),
 * so alerts are throttled per location + problem: the first failure emails, repeats within
 * sync.alerts.cooldown_minutes do not. Called from a job's failed() hook, so it never throws.
 */
class SyncFailureNotifier
{
    public function __construct(
        private readonly SyncAlertSettings $settings,
        private readonly SyncFailureExplainer $explainer,
        private readonly FailedSyncJobReport $jobs,
        private readonly ClinicRegistry $clinics,
    ) {}

    /**
     * @param  string|null  $module  registry module key (sync jobs)
     * @param  string|null  $table  local table (prune jobs)
     * @param  bool  $retried  false when the job failed on purpose without retrying (e.g. a prune safety refusal)
     * @return bool whether an alert was sent
     */
    public function jobFailed(int $officeId, ?string $module, ?string $table, ?Throwable $e, bool $retried = true): bool
    {
        try {
            return $this->send($officeId, $module, $table, $e, $retried);
        } catch (Throwable $alertError) {
            Log::error('Sync failure alert could not be sent.', ['office_id' => $officeId, 'module' => $module, 'table' => $table, 'exception' => $alertError]);

            return false;
        }
    }

    /**
     * Sample alert so a super admin can confirm mail delivery from the Sync Manager page.
     *
     * @return list<string> recipients that could not be emailed
     */
    public function sendTest(): array
    {
        return $this->mailEach($this->settings->recipients(), new SyncFailedMail(
            location: 'Test location',
            data: 'Appointments sync',
            problem: 'This is a test alert. No sync has failed.',
            action: 'Nothing to do. You are on the sync failure alert list.',
            technical: 'Test message sent from the Sync Manager page.',
            attempts: $this->attempts(),
            cooldownMinutes: $this->cooldownMinutes(),
            failedAt: now()->format('M j, Y g:i A'),
        ));
    }

    private function send(int $officeId, ?string $module, ?string $table, ?Throwable $e, bool $retried): bool
    {
        $recipients = $this->settings->recipients();
        if ($recipients === []) {
            return false;
        }

        $technical = trim((string) strtok($e?->getMessage() ?? 'Unknown error', "\n"));
        $explained = $this->explainer->explain($technical);

        // Atomic: of several workers failing the same way at once, only one emails.
        $throttleKey = 'sync-failure-alert:'.$officeId.':'.sha1($explained['problem']);
        if (! Cache::add($throttleKey, true, now()->addMinutes($this->cooldownMinutes()))) {
            return false;
        }

        $failed = $this->mailEach($recipients, new SyncFailedMail(
            location: $this->clinics->labelFor($officeId, null),
            data: $this->jobs->dataLabel($module, $table),
            problem: $explained['problem'],
            action: $explained['action'],
            technical: mb_substr($technical, 0, 500),
            attempts: $retried ? $this->attempts() : 1,
            cooldownMinutes: $this->cooldownMinutes(),
            failedAt: now()->format('M j, Y g:i A'),
        ));

        return count($failed) < count($recipients);
    }

    /**
     * One message per recipient, so one bad address does not stop the others.
     *
     * @param  list<string>  $recipients
     * @return list<string> recipients that could not be emailed
     */
    private function mailEach(array $recipients, SyncFailedMail $mail): array
    {
        $failed = [];

        foreach ($recipients as $recipient) {
            try {
                // Clone: to() adds to the mailable, so a shared instance would collect every address.
                Mail::to($recipient)->send(clone $mail);
            } catch (Throwable $e) {
                $failed[] = $recipient;
                Log::warning("Sync failure alert to {$recipient} failed: {$e->getMessage()}");
            }
        }

        return $failed;
    }

    private function attempts(): int
    {
        return (int) config('sync.queue.max_exceptions', 4);
    }

    private function cooldownMinutes(): int
    {
        return max(1, (int) config('sync.alerts.cooldown_minutes', 240));
    }
}
