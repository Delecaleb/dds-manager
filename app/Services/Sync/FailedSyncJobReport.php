<?php

namespace App\Services\Sync;

use App\Domain\Support\ClinicRegistry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Recent failed queue jobs, in terms a non-technical user can act on: which location, what
 * went wrong, what to do, which sync it happened on (with how often), and when.
 *
 * Failures are grouped by location + problem, so an office whose eConnector is down shows as
 * one line ("Appointments, Payments, … — 37 times, last at 7:52 AM"), not 37 near-identical rows.
 *
 * The office and data type are read from the job's stored payload without running any of its
 * code: the serialized job is unserialized with classes disallowed, which yields plain values.
 */
class FailedSyncJobReport
{
    /** Failed jobs inspected (newest first) when building the report. */
    private const SCAN_LIMIT = 500;

    /** Groups shown. */
    private const GROUP_LIMIT = 15;

    /** @var array<string, string>|null module key => label, built on first use */
    private ?array $moduleLabels = null;

    /** @var array<string, string> local table => label */
    private array $tableLabels = [];

    public function __construct(
        private readonly SyncFailureExplainer $explainer,
        private readonly SyncReportService $modules,
        private readonly ClinicRegistry $clinics,
    ) {}

    /**
     * `error_on` lists each sync the problem hit, most frequent first, e.g.
     * [['label' => 'Appointments sync', 'times' => 12], ['label' => 'Payments sync', 'times' => 3]].
     *
     * @return list<array{location: string, problem: string, action: string, error_on: list<array{label: string, times: int}>, error_on_text: string, times: int, first_failed_at: string, last_failed_at: string, technical: string}>
     */
    public function recent(): array
    {
        $groups = [];

        $rows = DB::table('failed_jobs')
            ->orderByDesc('id')
            ->limit(self::SCAN_LIMIT)
            ->get(['payload', 'exception', 'failed_at']);

        foreach ($rows as $row) {
            $job = $this->describeJob((string) $row->payload);
            $error = trim((string) strtok((string) $row->exception, "\n"));
            $explained = $this->explainer->explain($error);
            $key = $job['location']."\0".$explained['problem'];

            $group = $groups[$key] ?? [
                'location' => $job['location'],
                'problem' => $explained['problem'],
                'action' => $explained['action'],
                'error_on' => [],
                'times' => 0,
                'first_failed_at' => (string) $row->failed_at,
                'last_failed_at' => (string) $row->failed_at,
                'technical' => mb_substr($error, 0, 500),
            ];

            $group['error_on'][$job['data']] = ($group['error_on'][$job['data']] ?? 0) + 1;
            $group['times']++;
            // Rows arrive newest first: the last one seen is the earliest.
            $group['first_failed_at'] = (string) $row->failed_at;
            $groups[$key] = $group;
        }

        $groups = array_values($groups);
        usort($groups, fn (array $a, array $b) => strcmp($b['last_failed_at'], $a['last_failed_at']));

        return array_map(function (array $group) {
            arsort($group['error_on']);
            $group['error_on'] = array_map(fn (string $label, int $times) => ['label' => $label, 'times' => $times], array_keys($group['error_on']), $group['error_on']);
            $group['error_on_text'] = implode(', ', array_map(fn (array $on) => $on['times'] > 1 ? "{$on['label']} ({$on['times']}x)" : $on['label'], $group['error_on']));

            return $group;
        }, array_slice($groups, 0, self::GROUP_LIMIT));
    }

    /** "Oct 16, 2026 7:52 AM" in the app's timezone. */
    public static function when(string $timestamp): string
    {
        return Carbon::parse($timestamp)->format('M j, Y g:i A');
    }

    /**
     * Which location and which data a stored job was about.
     *
     * @return array{location: string, data: string}
     */
    private function describeJob(string $payload): array
    {
        $decoded = json_decode($payload, true);
        $name = class_basename((string) ($decoded['displayName'] ?? 'Unknown'));
        $fields = $this->jobFields((string) ($decoded['data']['command'] ?? ''));

        $officeId = isset($fields['officeId']) ? (int) $fields['officeId'] : null;
        $location = $officeId !== null ? $this->clinics->labelFor($officeId, null) : 'All locations';

        $data = match (true) {
            isset($fields['module']) || isset($fields['table']) => $this->dataLabel(
                isset($fields['module']) ? (string) $fields['module'] : null,
                isset($fields['table']) ? (string) $fields['table'] : null,
            ),
            $name === 'ProcessSyncRequest' => 'Manual sync request',
            $name === 'RepairOpenDentalRows' => 'Data repair',
            default => $name,
        };

        return ['location' => $location, 'data' => $data];
    }

    /**
     * What a sync or prune job works on, as shown to users: "Appointments sync",
     * "Deleted-record clean-up: Claim Procs". Shared with the failure alert email.
     */
    public function dataLabel(?string $module, ?string $table): string
    {
        if ($this->moduleLabels === null) {
            $definitions = $this->modules->getModuleDefinitions();
            $this->moduleLabels = array_map(fn (array $def) => $def['label'], $definitions);
            // Local table => the same friendly name the Sync Manager uses (od_claim_procs => "Claim Procedures").
            $this->tableLabels = array_column(array_filter($definitions, fn (array $def) => isset($def['table'])), 'label', 'table');
        }

        if ($module !== null) {
            return ($this->moduleLabels[$module] ?? ucfirst(str_replace('_', ' ', $module))).' sync';
        }

        return 'Deleted-record clean-up: '.($this->tableLabels[(string) $table] ?? $this->tableLabel((string) $table));
    }

    /**
     * The job's public properties, read without instantiating any class.
     *
     * @return array<string, mixed>
     */
    private function jobFields(string $serialized): array
    {
        if ($serialized === '') {
            return [];
        }

        $object = @unserialize($serialized, ['allowed_classes' => false]);
        if (! is_object($object)) {
            return [];
        }

        // Public properties keep their plain names when cast; others carry a \0 prefix.
        return array_filter((array) $object, fn ($key) => ! str_contains((string) $key, "\0"), ARRAY_FILTER_USE_KEY);
    }

    /** "od_claim_procs" → "Claim procs" */
    private function tableLabel(string $table): string
    {
        return ucfirst(str_replace('_', ' ', preg_replace('/^od_/', '', $table) ?? $table));
    }
}
