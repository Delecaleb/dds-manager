<?php

namespace App\Services\Sync;

use App\Jobs\ProcessSyncRequest;
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
     * Modules a date-range request may target → registry module key.
     *
     * @var array<string, string>
     */
    private const MODULES = [
        'appointments' => 'appointments',
        'appointment' => 'appointments',
        'procedurelogs' => 'procedurelogs',
        'procedurelog' => 'procedurelogs',
        'patients' => 'patients',
        'patient' => 'patients',
        'adjustments' => 'adjustments',
        'adjustment' => 'adjustments',
        'payments' => 'payments',
        'payment' => 'payments',
        'claimprocs' => 'claimprocs',
        'claimproc' => 'claimprocs',
        'treatmentplans' => 'treatment_plans',
        'treatmentplan' => 'treatment_plans',
        'treatment_plans' => 'treatment_plans',
    ];

    public const ALL = 'all';

    public function __construct(
        private readonly SyncReportService $registry,
        private readonly HardDeleteSyncService $deleter,
    ) {}

    /**
     * @return list<string> accepted module values, for request validation
     */
    public function acceptedModules(): array
    {
        return [...array_keys(self::MODULES), self::ALL];
    }

    /**
     * Create a date-range request and queue it. Used by every UI entry point.
     */
    public function createAndQueue(int $officeId, string $module, ?string $startDate, ?string $endDate, bool $pruneDeleted, ?int $userId): SyncRequest
    {
        $moduleKeys = $this->moduleKeysFor($module);

        // Fail at creation, not minutes later in the worker: a single module
        // without a business date cannot honour a date range.
        if (($startDate || $endDate) && count($moduleKeys) === 1) {
            $meta = app($this->registry->serviceClassFor($moduleKeys[0]))->describe();

            if ($meta['date_column'] === null) {
                throw new InvalidArgumentException("'{$module}' cannot be synced by date range. Leave the dates empty to sync it in full.");
            }
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
            return array_values(array_unique(self::MODULES));
        }

        return isset(self::MODULES[$module])
            ? [self::MODULES[$module]]
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

            if ($startDate && $endDate) {
                $this->deleter->pruneTable($table, $startDate, $endDate, $office, false);
            } else {
                $this->deleter->pruneAllRecords($table, $office, false);
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
