<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Sync\SyncAlertSettings;
use App\Services\Sync\SyncFailureNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Super admin: who is emailed when a sync fails for good. Edited on the Sync Manager page.
 */
class SyncAlertController extends Controller
{
    public function update(Request $request, SyncAlertSettings $settings): JsonResponse
    {
        $emails = SyncAlertSettings::parse($request->input('recipients'));

        Validator::make(['recipients' => $emails], [
            'recipients' => ['array', 'max:'.SyncAlertSettings::MAX_RECIPIENTS],
            'recipients.*' => ['email:rfc', 'max:255'],
        ], [
            'recipients.max' => 'Enter at most '.SyncAlertSettings::MAX_RECIPIENTS.' email addresses.',
            'recipients.*.email' => '":input" is not a valid email address.',
        ])->validate();

        $saved = $settings->setRecipients($emails, $request->user()?->id);

        return response()->json([
            'success' => true,
            'message' => $saved === []
                ? 'Saved. No one is on the list, so sync failure alerts are off.'
                : 'Saved. '.count($saved).' recipient(s) will be emailed when a sync fails.',
            'recipients' => $saved,
        ]);
    }

    public function test(SyncAlertSettings $settings, SyncFailureNotifier $notifier): JsonResponse
    {
        $recipients = $settings->recipients();

        if ($recipients === []) {
            return response()->json(['success' => false, 'message' => 'Save at least one email address first.'], 422);
        }

        $failed = $notifier->sendTest();

        return response()->json([
            'success' => $failed === [],
            'message' => $failed === []
                ? 'Test alert sent to '.count($recipients).' recipient(s).'
                : 'Could not email: '.implode(', ', $failed).'. Check the mail settings (MAIL_*) in .env and the log.',
        ], $failed === [] ? 200 : 502);
    }
}
