<?php

namespace App\Services\Sync;

/**
 * Turns a sync error (an exception message from OpenDental, the database or the queue) into
 * words an office manager can act on: what went wrong, and what to do about it. One place for
 * this wording, so every screen that shows a sync failure says the same thing.
 *
 * Rules are checked in order; the first match wins. Anything unrecognised is still reported,
 * as a generic failure, with the technical text kept alongside for support.
 */
class SyncFailureExplainer
{
    /**
     * [needle(s) found in the error (case-insensitive), problem, what to do]
     *
     * @var list<array{0: list<string>, 1: string, 2: string}>
     */
    private const RULES = [
        [
            ['econnector is not running'],
            "The office's OpenDental eConnector is offline, so its data cannot be read.",
            'Ask the office to start the eConnector service on their OpenDental server (or restart that server). Sync resumes on its own and catches up.',
        ],
        [
            ['refused to prune'],
            'A safety check stopped the clean-up of deleted records: OpenDental reported more missing records than expected. Nothing was deleted.',
            'Usually harmless. Ask support to confirm the records were really deleted in OpenDental before forcing the clean-up.',
        ],
        [
            ['status code 401', 'status code 403', 'unauthorized', 'forbidden', 'invalid developer key', 'invalid customer key'],
            "OpenDental refused this office's API key.",
            "Check the office's OpenDental API keys under Offices / Locations; they may have been changed or revoked.",
        ],
        [
            ['status code 429', 'too many requests', 'rate limit'],
            'OpenDental asked us to slow down (too many requests).',
            'No action needed; the next scheduled sync tries again. If it keeps happening, ask support to space out the syncs.',
        ],
        [
            ['curl error 28', 'timed out', 'timeout', 'run too long'],
            'Reading from OpenDental took too long and was stopped.',
            "Usually a slow or busy office server or internet connection. It retries automatically; if it keeps failing, check the office's internet.",
        ],
        [
            ['could not resolve host', 'curl error 6', 'curl error 7', 'connection refused', 'connection reset', 'failed to connect'],
            'Could not connect to OpenDental.',
            "Check the office's internet connection and that OpenDental's API service is reachable. It retries automatically.",
        ],
        [
            ['status code 500', 'status code 502', 'status code 503', 'status code 504'],
            "OpenDental's service returned an error on its side.",
            'Usually temporary; it retries automatically. If it lasts more than a few hours, contact OpenDental support.',
        ],
        [
            ['deadlock', 'lock wait timeout'],
            'Two updates collided in our database.',
            'No action needed; it retries automatically.',
        ],
        [
            ['attempted too many times', 'maxattemptsexceeded'],
            'This update kept failing and was given up after several tries.',
            'Look at earlier failures for the same location to see the underlying cause.',
        ],
        [
            ['sqlstate', 'base table or view not found'],
            'Our database rejected the update.',
            'Contact support with the technical details below.',
        ],
    ];

    /** @return array{problem: string, action: string} */
    public function explain(string $error): array
    {
        $haystack = strtolower($error);

        foreach (self::RULES as [$needles, $problem, $action]) {
            foreach ($needles as $needle) {
                if (str_contains($haystack, $needle)) {
                    return ['problem' => $problem, 'action' => $action];
                }
            }
        }

        return [
            'problem' => 'The update failed for an unexpected reason.',
            'action' => 'It retries automatically. If it keeps failing, contact support with the technical details below.',
        ];
    }
}
