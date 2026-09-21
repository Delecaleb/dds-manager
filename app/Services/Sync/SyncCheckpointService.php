<?php

namespace App\Services\Sync;

use App\Models\Office;
use App\Models\SyncLog;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use RuntimeException;

/**
 * View and reset sync cursors (sync_logs) for office locations.
 *
 * Resetting sets where the scheduled incremental sync continues from:
 *  - a start date: re-scans everything created or changed on/after that date;
 *  - no date: a full initial scan from the first record.
 *
 * A reset never lands under a live run. It is a single conditional UPDATE using
 * the same staleness rule as SyncLease, and it clears run_token so an old run
 * that was still alive stops at its next heartbeat instead of overwriting the
 * new start date.
 */
class SyncCheckpointService
{
    public function __construct(
        private readonly SyncReportService $reportService,
        private readonly OpenDentalTableCatalog $catalog,
    ) {}

    /**
     * @return Collection<int, SyncLog>
     */
    public function forOffice(int $officeId): Collection
    {
        return SyncLog::withoutGlobalScopes()
            ->where('office_id', $officeId)
            ->orderBy('module')
            ->get()
            ->each(fn (SyncLog $log) => $log->makeHidden('run_token'));
    }

    /**
     * Modules whose start date can be reset, keyed by OpenDental table (the
     * suffix of their sync_logs module name) with a display label.
     *
     * @return array<string, string>
     */
    public function resettableModules(): array
    {
        $definitions = $this->reportService->getModuleDefinitions();
        $modules = [];

        foreach ($this->catalog->all() as $table) {
            if ($table->isRepairable()) {
                $modules[$table->key] = $definitions[$table->module]['label'] ?? $table->key;
            }
        }

        asort($modules);

        return $modules;
    }

    /**
     * Reset sync checkpoint (start date / primary key) for an office.
     *
     * $modules is one module, "all", or a list of modules. A single module that is
     * running is an error; with several, running ones are skipped and reported.
     *
     * @param  string|list<string>  $modules
     * @return array{success: bool, message: string, reset_count: int, skipped_running: int, module: string, start_date: ?string}
     *
     * @throws InvalidArgumentException for an unknown office, date or module
     * @throws RuntimeException when a single requested module is running
     */
    public function resetForOffice(int $officeId, string|array $modules, ?string $startDate, int $lastPrimaryKey = 0): array
    {
        $office = Office::find($officeId) ?? throw new InvalidArgumentException("Office #{$officeId} does not exist.");
        $formattedDate = $this->formatDate($startDate);
        $modules = array_values(array_unique(array_map(fn (string $m) => strtolower(trim($m)), (array) $modules)));
        $isAll = in_array('all', $modules, true);

        if ($modules === []) {
            throw new InvalidArgumentException('Select at least one module to reset.');
        }

        $logModules = $isAll
            ? array_map(fn (string $key) => "office_{$officeId}:{$key}", array_keys($this->resettableModules()))
            : array_values(array_unique(array_map(fn (string $m) => $this->resolveLogModule($officeId, $m), $modules)));
        $isSingle = ! $isAll && count($logModules) === 1;
        $module = $isAll ? 'all' : implode(', ', $modules);

        // Modules that never synced get a row so the chosen start date applies to their first run.
        foreach ($logModules as $logModule) {
            SyncLog::withoutGlobalScopes()->firstOrCreate(
                ['module' => $logModule],
                ['office_id' => $officeId, 'status' => 'idle', 'total_processed' => 0]
            );
        }

        $staleBefore = now()->subSeconds((int) config('sync.stale_after_seconds', 600));

        $resetCount = SyncLog::withoutGlobalScopes()
            ->where('office_id', $officeId)
            ->whereIn('module', $logModules)
            ->where(fn ($query) => $query->whereNull('status')
                ->orWhere('status', '!=', 'running')
                ->orWhere('updated_at', '<', $staleBefore))
            ->update([
                'last_synced_at' => $formattedDate,
                'last_primary_key' => max(0, $lastPrimaryKey),
                'cycle_started_at' => null,
                'status' => 'idle',
                'run_token' => null,
                'last_error' => null,
            ]);

        $skippedRunning = count($logModules) - $resetCount;

        if ($isSingle && $resetCount === 0) {
            throw new RuntimeException("Cannot reset '{$module}' while its sync is running. Try again after it finishes.");
        }

        $dateLabel = $formattedDate !== null
            ? 'records created or changed since '.substr($formattedDate, 0, 10)
            : 'a full scan from the first record';

        $message = $isSingle
            ? "Reset '{$module}' for '{$office->name}' to {$dateLabel}."
            : "Reset {$resetCount} module(s) for '{$office->name}' to {$dateLabel}."
                .($skippedRunning > 0 ? " {$skippedRunning} running module(s) were left unchanged; reset them after they finish." : '');

        return [
            'success' => true,
            'message' => $message,
            'reset_count' => $resetCount,
            'skipped_running' => $skippedRunning,
            'module' => $module,
            'start_date' => $formattedDate,
        ];
    }

    private function formatDate(?string $startDate): ?string
    {
        if ($startDate === null || trim($startDate) === '') {
            return null;
        }

        $timestamp = strtotime($startDate);

        if ($timestamp === false) {
            throw new InvalidArgumentException("Invalid start date '{$startDate}'.");
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * Map a module key, table name or existing sync_logs module name to this
     * office's scheduled-sync log row. Only OpenDental sync modules qualify.
     */
    private function resolveLogModule(int $officeId, string $module): string
    {
        $prefix = "office_{$officeId}:";

        // Full module name, e.g. from the checkpoints table: must be a real sync module of this office.
        if (str_starts_with($module, $prefix)) {
            $module = substr($module, strlen($prefix));
        }

        $table = $this->catalog->resolve($module);

        if ($table === null || ! $table->isRepairable()) {
            throw new InvalidArgumentException("'{$module}' is not a sync module that can be reset.");
        }

        return $prefix.$table->key;
    }
}
