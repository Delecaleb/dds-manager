<?php

namespace App\Services\Sync;

use App\Models\AppSetting;

/**
 * Who is emailed when a sync job fails for good (see SyncFailureNotifier).
 * Set by a super admin on the Sync Manager page. No recipients = no alerts.
 */
class SyncAlertSettings
{
    public const KEY = 'sync_failure_alert_recipients';

    public const MAX_RECIPIENTS = 25;

    /**
     * @return list<string>
     */
    public function recipients(): array
    {
        return array_values(array_filter((array) AppSetting::valueFor(self::KEY, []), 'is_string'));
    }

    /**
     * @param  list<string>  $emails  already validated
     * @return list<string> what was saved
     */
    public function setRecipients(array $emails, ?int $userId = null): array
    {
        $emails = array_values(array_unique(array_map(fn (string $email) => strtolower(trim($email)), $emails)));

        AppSetting::put(self::KEY, $emails, $userId);

        return $emails;
    }

    /**
     * Free text from the page ("a@x.com, b@y.com" or one per line) => separate addresses.
     *
     * @return list<string>
     */
    public static function parse(?string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/[\s,;]+/', (string) $text) ?: [])));
    }
}
