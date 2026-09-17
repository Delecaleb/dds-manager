<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesSyncRequests;
use App\Jobs\RepairOpenDentalRows;
use App\Models\Office;
use App\Services\Sync\Explorer\ExplorerQueryService;
use App\Services\Sync\Explorer\ReconciliationService;
use App\Services\Sync\Explorer\RowRepairService;
use App\Services\Sync\OpenDentalTable;
use App\Services\Sync\OpenDentalTableCatalog;
use App\Services\Sync\SyncCheckpointService;
use App\Services\Sync\SyncRequestRunner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

/**
 * OD Data Explorer: query, compare and repair OpenDental data for the active office.
 *
 * Coordination only. Every table goes through OpenDentalTableCatalog (OpenDental
 * data only — never application tables), and all logic lives in the explorer
 * services. Repairs send primary keys, never row data.
 */
class OpenDentalExplorerController extends Controller
{
    use HandlesSyncRequests;

    private const INVALID_TABLE = 'Invalid or unauthorized table selected.';

    public function __construct(
        private readonly OpenDentalTableCatalog $catalog,
        private readonly ExplorerQueryService $explorer,
    ) {}

    public function index(): View
    {
        return view('od-explorer.index', [
            'currentOffice' => Office::getActiveOffice() ?? Office::first(),
            'tables' => $this->catalog->all(),
        ]);
    }

    public function tables(): JsonResponse
    {
        $tables = $this->catalog->all();

        return response()->json([
            'opendental_tables' => array_keys(array_filter($tables, fn (OpenDentalTable $t) => $t->existsInOpenDental)),
            'local_tables' => array_values(array_map(fn (OpenDentalTable $t) => $t->localTable, $tables)),
        ]);
    }

    public function columns(Request $request): JsonResponse
    {
        $table = $this->catalog->resolve((string) $request->input('table'));

        if ($table === null) {
            return response()->json(['error' => self::INVALID_TABLE], 400);
        }

        return response()->json([
            'table' => (string) $request->input('table'),
            'resolved_table' => $table->localTable,
            'columns' => $this->explorer->columns($table),
        ]);
    }

    public function query(Request $request): JsonResponse
    {
        $startedAt = microtime(true);
        $table = $this->catalog->resolve((string) $request->input('table'));
        $office = $this->activeOffice();

        if ($table === null) {
            return response()->json(['error' => self::INVALID_TABLE], 400);
        }

        if ($office === null) {
            return response()->json(['error' => 'No office is configured.'], 422);
        }

        $columns = $this->explorer->selectColumns($table, $request->input('columns'));
        $conditions = $this->explorer->normalizeConditions($table, $request->input('conditions'), $request->input('start_date'), $request->input('end_date'));
        $orderBy = $this->explorer->validOrderColumn($table, $request->input('order_by'));
        $direction = strtolower((string) $request->input('order_direction')) === 'desc' ? 'desc' : 'asc';
        $limit = $this->explorer->clampLimit((int) $request->input('limit', 50));

        if ($request->input('source', 'opendental_live') === 'opendental_live') {
            try {
                $live = $this->explorer->live($table, $office, $columns, $conditions, $orderBy, $direction, $limit);
            } catch (InvalidArgumentException $e) {
                return response()->json(['error' => $e->getMessage()], 422);
            } catch (Throwable $e) {
                // No silent fallback to local data: the user asked for live OpenDental.
                return response()->json(['error' => 'OpenDental live API unavailable: '.$e->getMessage()], 502);
            }

            return response()->json([
                'source_type' => 'OpenDental Realtime API',
                'table' => $table->key,
                'primary_key' => $table->primaryKey,
                'office_id' => (int) $office->id,
                'count' => count($live['rows']),
                'execution_time_ms' => $this->elapsedMs($startedAt),
                'columns' => $columns === ['*'] ? array_keys($live['rows'][0] ?? array_flip($this->explorer->columns($table))) : $columns,
                'sql' => $live['sql'],
                'rows' => $live['rows'],
            ]);
        }

        $builder = $this->explorer->localBuilder($table, (int) $office->id, $conditions)->select($columns);

        if ($orderBy !== null) {
            $builder->orderBy($orderBy, $direction);
        }

        $builder->limit($limit);
        $rows = $builder->get();

        return response()->json([
            'source_type' => 'Local Synced Database',
            'table' => $table->localTable,
            'primary_key' => $table->primaryKey,
            'office_id' => (int) $office->id,
            'count' => $rows->count(),
            'execution_time_ms' => $this->elapsedMs($startedAt),
            'columns' => $columns === ['*'] ? $this->explorer->columns($table) : $columns,
            'sql' => $builder->toSql(),
            'bindings' => $builder->getBindings(),
            'rows' => $rows,
        ]);
    }

    public function reconcileDiff(Request $request, ReconciliationService $reconciliation): JsonResponse
    {
        $table = $this->catalog->resolve((string) $request->input('table', 'appointment'));
        $office = $this->activeOffice();

        if ($table === null) {
            return response()->json(['error' => self::INVALID_TABLE], 400);
        }

        if ($office === null) {
            return response()->json(['error' => 'No office is configured.'], 422);
        }

        if (! $table->existsInOpenDental) {
            return response()->json(['error' => "'{$table->key}' is a local rollup and cannot be compared with OpenDental."], 422);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $conditions = $this->explorer->normalizeConditions($table, $request->input('conditions'), $startDate, $endDate);

        return response()->json([
            ...$reconciliation->compare($table, $office, $conditions, (int) $request->input('limit', 500)),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    /**
     * Re-fetch rows from OpenDental by primary key and upsert them locally.
     */
    public function syncToLocal(Request $request): JsonResponse
    {
        return $this->repair($request, RepairOpenDentalRows::ACTION_SYNC);
    }

    /**
     * Delete local rows that OpenDental confirms are gone.
     */
    public function pruneOrphans(Request $request): JsonResponse
    {
        return $this->repair($request, RepairOpenDentalRows::ACTION_PRUNE);
    }

    public function syncCheckpoints(SyncCheckpointService $checkpoints): JsonResponse
    {
        return response()->json(['logs' => $checkpoints->forOffice($this->activeOfficeId())]);
    }

    public function getSyncRequests(): JsonResponse
    {
        return $this->listSyncRequests($this->activeOfficeId());
    }

    public function triggerDateSync(Request $request, SyncRequestRunner $runner): JsonResponse
    {
        return $this->createSyncRequest($request, $runner, $this->activeOfficeId());
    }

    public function cancelSyncRequest(Request $request): JsonResponse
    {
        return $this->cancelRequest((int) $request->input('id'), $this->activeOfficeId());
    }

    private function repair(Request $request, string $action): JsonResponse
    {
        $validated = $request->validate([
            'table' => ['required', 'string'],
            'keys' => ['required', 'array', 'min:1', 'max:'.RowRepairService::MAX_KEYS],
            'keys.*' => ['integer', 'min:1'],
        ]);

        $table = $this->catalog->resolve($validated['table']);
        $office = $this->activeOffice();

        if ($table === null) {
            return response()->json(['error' => self::INVALID_TABLE], 400);
        }

        if ($office === null) {
            return response()->json(['error' => 'No office is configured.'], 422);
        }

        if (! $table->isRepairable()) {
            return response()->json(['error' => "'{$table->key}' cannot be repaired from OpenDental."], 422);
        }

        $job = new RepairOpenDentalRows((int) $office->id, $table->key, $action, array_map('intval', $validated['keys']));

        if (count($validated['keys']) > RowRepairService::INLINE_MAX_KEYS) {
            dispatch($job);

            return response()->json([
                'success' => true,
                'queued' => true,
                'table' => $table->localTable,
                'message' => count($validated['keys']).' record(s) queued. They are processed on the server — re-run the comparison in a minute.',
            ]);
        }

        try {
            $result = app()->call([$job, 'handle']);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            return response()->json(['error' => 'OpenDental could not be reached; nothing was changed. '.$e->getMessage()], 502);
        }

        return response()->json([
            'success' => true,
            'queued' => false,
            'table' => $table->localTable,
            ...$this->repairSummary($action, $result),
        ]);
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    private function repairSummary(string $action, array $result): array
    {
        if ($action === RepairOpenDentalRows::ACTION_SYNC) {
            $notFound = count($result['not_found']);

            return [
                'synced_count' => $result['synced'],
                'not_found_keys' => $result['not_found'],
                'message' => "Synced {$result['synced']} record(s) from OpenDental.".($notFound > 0 ? " {$notFound} no longer exist in OpenDental." : ''),
            ];
        }

        $kept = count($result['still_in_opendental']);

        return [
            'deleted_count' => $result['deleted'],
            'still_in_opendental_keys' => $result['still_in_opendental'],
            'message' => "Pruned {$result['deleted']} record(s) confirmed deleted in OpenDental.".($kept > 0 ? " {$kept} still exist in OpenDental and were kept." : ''),
        ];
    }

    private function activeOffice(): ?Office
    {
        return Office::getActiveOffice() ?? Office::first();
    }

    private function activeOfficeId(): int
    {
        return (int) ($this->activeOffice()?->id ?? 0);
    }

    private function elapsedMs(float $startedAt): float
    {
        return round((microtime(true) - $startedAt) * 1000, 2);
    }
}
