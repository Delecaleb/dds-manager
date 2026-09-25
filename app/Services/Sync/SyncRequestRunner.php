<?php

namespace App\Services\Sync;

use App\Jobs\ProcessSyncRequest;
use App\Jobs\PruneOfficeTable;
use App\Models\Office;
use App\Models\SyncRequest;
use Exception;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

/**
 * Executes date-range sync requests created from the Sync Manager and the
 * OD Data Explorer. One path for both: requests are queued (never exec()'d
 * from the web request), claimed atomically so two workers can't run the same
 * request, and time-budgeted so a long backfill continues across jobs.
 */
class SyncRequestRunner
{
    public const OUTCOME_COMPLETED = 'completed';

    public const OUTCOME_CONTINUING = 'continuing';

    public const OUTCOME_DEFERRED = 'deferred';

    public const OUTCOME_FAILED = 'failed';

    public const OUTCOME_NOT_CLAIMED = 'not_claimed';

    /**
     * Singular and legacy names still accepted for a registry module key.
     *
     * @var array<string, string>
     */
    private const ALIASES = [
        'appointment' => 'appointments',
        'procedurelog' => 'procedurelogs',
        'patient' => 'patients',
        'adjustment' => 'adjustments',
        'payment' => 'payments',
        'claimproc' => 'claimprocs',
        'treatmentplans' => 'treatment_plans',
        'treatmentplan' => 'treatment_plans',
    ];

    public const ALL = 'all';

    /** @var array<string, string>|null */
    private ?array $rangeModules = null;

    public function __construct(
        private readonly SyncReportService $registry,
        private readonly HardDeleteSyncService $deleter,
    ) {}

    /**
     * Registry modules that can be re-synced by date range (key => label).
     *
     * Derived from each sync service's dateColumn(), so a table gains range
     * support by defining its business date there, not by editing a list here.
     *
     * @return array<string, string>
     */
    public function rangeModules(): array
    {
        if ($this->rangeModules !== null) {
            return $this->rangeModules;
        }

        $modules = [];

        foreach ($this->registry->getModuleDefinitions() as $key => $definition) {
            $class = $definition['service_class'] ?? null;

            if ($class && is_subclass_of($class, BaseQuerySyncService::class) && app($class)->describe()['date_column'] !== null) {
                $modules[$key] = $definition['label'] ?? $key;
            }
        }

        asort($modules);

        return $this->rangeModules = $modules;
    }

    /**
     * @return list<string> accepted module values, for request validation
     */
    public function acceptedModules(): array
    {
        return [...array_keys($this->rangeModules()), ...array_keys(self::ALIASES), self::ALL];
    }

    /**
     * Create one queued request per module. Every module is validated first,
     * so an unknown module creates nothing.
     *
     * @param  list<string>  $modules
     * @return list<SyncRequest>
     */
    public function createAndQueueMany(int $officeId, array $modules, ?string $startDate, ?string $endDate, bool $pruneDeleted, ?int $userId): array
    {
        $moduleKeys = array_values(array_unique(array_merge(
            ...array_map(fn (string $module) => $this->moduleKeysFor($module), $modules)
        )));

        return array_map(
            fn (string $moduleKey) => $this->createAndQueue($officeId, $moduleKey, $startDate, $endDate, $pruneDeleted, $userId),
            $moduleKeys
        );
    }

    /**
     * Create a date-range request and queue it.
     */
    public function createAndQueue(int $officeId, string $module, ?string $startDate, ?string $endDate, bool $pruneDeleted, ?int $userId): SyncRequest
    {
        $moduleKeys = $this->moduleKeysFor($module);

        $startDate = $startDate ? date('Y-m-d', strtotime($startDate)) : null;
        $endDate = $endDate ? date('Y-m-d', strtotime($endDate)) : null;

        if ($startDate || $endDate) {
            $this->restartWindows($officeId, $moduleKeys, $startDate, $endDate);
        }

        $request = SyncRequest::create([
            'office_id' => $officeId,
            'module' => strtolower(trim($module)),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'prune_deleted' => $pruneDeleted,
            'status' => 'pending',
            'created_by' => $userId,
        ]);

        $this->queue($request);

        return $request;
    }

    /**
     * A new request re-reads its whole window, even if an earlier request for the
     * same range completed (otherwise it would only fetch rows edited since then).
     * Modules without a business date sync unwindowed, so they have nothing to restart.
     *
     * @param  list<string>  $moduleKeys
     */
    private function restartWindows(int $officeId, array $moduleKeys, ?string $startDate, ?string $endDate): void
    {
        $office = Office::find($officeId);

        // A missing office is reported when the request runs.
        if ($office === null) {
            return;
        }

        foreach ($moduleKeys as $moduleKey) {
            /** @var BaseQuerySyncService $service */
            $service = app($this->registry->serviceClassFor($moduleKey))->forOffice($office);

            if ($service->describe()['date_column'] !== null) {
                $service->withDateWindow($startDate, $endDate)->restartWindow();
            }
        }
    }

    /**
     * Queue a request for server-side processing.
     */
    public function queue(SyncRequest $request): void
    {
        ProcessSyncRequest::dispatch((int) $request->id);
    }

    /**
     * Atomically move a pending request to running. False if another worker
     * already took it, or it was cancelled.
     */
    public function claim(SyncRequest $request): bool
    {
        $claimed = SyncRequest::whereKey($request->getKey())
            ->where('status', 'pending')
            ->update(['status' => 'running', 'started_at' => now(), 'error_message' => null]);

        if ($claimed === 1) {
            $request->refresh();
        }

        return $claimed === 1;
    }

    /**
     * Claim and process a request within a time budget.
     */
    public function run(SyncRequest $request, ?int $timeBudgetSeconds = null): string
    {
        if (! $this->claim($request)) {
            return self::OUTCOME_NOT_CLAIMED;
        }

        $deadline = $timeBudgetSeconds !== null ? microtime(true) + $timeBudgetSeconds : null;

        try {
            $office = Office::find($request->office_id)
                ?? throw new Exception("Office #{$request->office_id} no longer exists.");

            $moduleKeys = $this->moduleKeysFor((string) $request->module);
            $startDate = $request->start_date?->format('Y-m-d');
            $endDate = $request->end_date?->format('Y-m-d');
            $skipped = [];

            foreach ($moduleKeys as $moduleKey) {
                $remaining = $deadline !== null ? (int) floor($deadline - microtime(true)) : null;

                if ($remaining !== null && $remaining <= 0) {
                    return $this->markContinuing($request);
                }

                /** @var BaseQuerySyncService $service */
                $service = app($this->registry->serviceClassFor($moduleKey))->forOffice($office);

                if ($startDate || $endDate) {
                    try {
                        $service->withDateWindow($startDate, $endDate);
                    } catch (Exception $e) {
                        // "all" includes modules without a business date; they sync unwindowed.
                        if (count($moduleKeys) === 1) {
                            throw $e;
                        }
                    }
                }

                $service->withTimeBudget($remaining)->sync();

                if ($service->wasInterrupted()) {
                    return $this->markContinuing($request);
                }

                if ($service->wasSkipped()) {
                    $skipped[] = $moduleKey;
                }
            }

            // Another run held the lease: those modules did not sync, so retry
            // later instead of reporting a false success. Upserts are idempotent.
            if ($skipped !== []) {
                $request->update([
                    'status' => 'pending',
                    'started_at' => null,
                    'error_message' => 'Deferred — already running: '.implode(', ', $skipped).'. Will retry on the next run.',
                ]);

                return self::OUTCOME_DEFERRED;
            }

            if ($request->prune_deleted) {
                $this->pruneDeleted($moduleKeys, $office, $startDate, $endDate);
            }

            $request->update(['status' => 'completed', 'completed_at' => now(), 'error_message' => null]);

            return self::OUTCOME_COMPLETED;
        } catch (Throwable $e) {
            Log::error("SyncRequest #{$request->id} failed: {$e->getMessage()}", ['exception' => $e]);

            $request->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            return self::OUTCOME_FAILED;
        }
    }

    /**
     * @return list<string> registry module keys
     */
    private function moduleKeysFor(string $module): array
    {
        $module = strtolower(trim($module));

        if ($module === self::ALL) {
            return array_keys($this->rangeModules());
        }

        $moduleKey = self::ALIASES[$module] ?? $module;

        return isset($this->rangeModules()[$moduleKey])
            ? [$moduleKey]
            : throw new InvalidArgumentException("Unknown sync module '{$module}'.");
    }

    /**
     * Prune only the tables of the requested modules — never every table
     * because one module lacks hard-delete support.
     *
     * @param  list<string>  $moduleKeys
     */
    private function pruneDeleted(array $moduleKeys, Office $office, ?string $startDate, ?string $endDate): void
    {
        $definitions = $this->registry->getModuleDefinitions();
        $supported = $this->deleter->getSupportedTables();

        foreach ($moduleKeys as $moduleKey) {
            $table = $definitions[$moduleKey]['table'] ?? null;

            if ($table === null || ! in_array($table, $supported, true)) {
                continue;
            }

            // Queued like scheduled prunes: chunked, resumable, mass-delete guarded.
            // "From a date, no end" checks from that date through the upcoming
            // schedule — not every record ever synced (one API call per 500 rows).
            if ($startDate) {
                $endDate ??= now()->addDays((int) config('sync.prune.rolling_future_days', 90))->toDateString();

                PruneOfficeTable::dispatch((int) $office->id, $table, HardDeleteSyncService::MODE_RANGE, $startDate, $endDate);
            } else {
                PruneOfficeTable::dispatch((int) $office->id, $table, HardDeleteSyncService::MODE_FULL);
            }
        }
    }

    private function markContinuing(SyncRequest $request): string
    {
        // Cursor is saved in sync_logs; the next claim resumes from it.
        $request->update([
            'status' => 'pending',
            'started_at' => null,
            'error_message' => 'In progress — continuing in the next run.',
        ]);

        return self::OUTCOME_CONTINUING;
    }
}
