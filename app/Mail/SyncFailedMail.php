<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Alert to the sync failure recipient list: a sync job gave up after its retries.
 * Built by SyncFailureNotifier; wording comes from SyncFailureExplainer.
 */
class SyncFailedMail extends Mailable
{
    public function __construct(
        public readonly string $location,
        public readonly string $data,
        public readonly string $problem,
        public readonly string $action,
        public readonly string $technical,
        public readonly int $attempts,
        public readonly int $cooldownMinutes,
        public readonly string $failedAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Sync failed: {$this->location} — {$this->data}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.sync-failed',
            with: ['syncManagerUrl' => route('sync-manager.index')],
        );
    }
}
