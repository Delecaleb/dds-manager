<?php

namespace App\Http\Controllers\Concerns;

use App\Models\SyncRequest;
use App\Services\Sync\SyncCheckpointService;
use App\Services\Sync\SyncRequestRunner;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use RuntimeException;

/**
 * Date-range sync requests and checkpoint resets, shared by the Sync Manager
 * and the OD Data Explorer so both screens behave identically.
 */
trait HandlesSyncRequests
{
    protected function listSyncRequests(int $officeId): JsonResponse
    {
        return response()->json([
            'requests' => SyncRequest::with(['user:id,name', 'office:id,name'])
                ->where('office_id', $officeId)
                ->orderByDesc('id')
                ->take(50)
                ->get(),
        ]);
    }

    protected function createSyncRequest(Request $request, SyncRequestRunner $runner, int $officeId): JsonResponse
    {
        $validated = $request->validate([
            'module' => ['required', 'string', Rule::in($runner->acceptedModules())],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'prune_deleted' => ['nullable', 'boolean'],
        ]);

        try {
            $syncRequest = $runner->createAndQueue(
                $officeId,
                $validated['module'],
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null,
                (bool) ($validated['prune_deleted'] ?? false),
                auth()->id(),
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "Server-to-server sync request queued for '{$syncRequest->module}'. It runs on the server — you can close this page.",
            'sync_request' => $syncRequest,
        ]);
    }

    protected function resetSyncCheckpointFor(Request $request, SyncCheckpointService $checkpoints, int $officeId): JsonResponse
    {
        $validated = $request->validate([
            'module' => ['required', 'string', 'max:255'],
            'last_synced_at' => ['nullable', 'date'],
            'last_primary_key' => ['nullable', 'integer', 'min:0'],
        ]);

        $lastSyncedAt = $validated['last_synced_at'] ?? null;
        $lastPrimaryKey = (int) ($validated['last_primary_key'] ?? 0);

        if ($validated['module'] === 'all') {
            $result = $checkpoints->resetAll($officeId, $lastSyncedAt, $lastPrimaryKey);

            return response()->json([
                'success' => true,
                ...$result,
                'message' => "Reset {$result['reset']} checkpoint(s)".($result['skipped_running'] > 0 ? "; {$result['skipped_running']} running sync(s) were left untouched." : '.'),
            ]);
        }

        try {
            $log = $checkpoints->reset($officeId, $validated['module'], $lastSyncedAt, $lastPrimaryKey);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => "Unknown sync module '{$validated['module']}' for this office."], 404);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }

        return response()->json([
            'success' => true,
            'module' => $log->module,
            'message' => "Reset sync checkpoint for module '{$log->module}'.",
        ]);
    }

    protected function cancelRequest(int $id, int $officeId): JsonResponse
    {
        $syncRequest = SyncRequest::where('office_id', $officeId)->find($id);

        if ($syncRequest === null) {
            return response()->json(['error' => 'Sync request not found.'], 404);
        }

        // Only a pending request can be cancelled atomically; a running one
        // would otherwise finish and overwrite the cancellation.
        $cancelled = SyncRequest::whereKey($id)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled', 'completed_at' => now(), 'error_message' => 'Cancelled by user.']);

        if ($cancelled === 0) {
            return response()->json(['error' => "Cannot cancel a sync request with status '{$syncRequest->fresh()->status}'."], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "Sync request #{$id} has been cancelled.",
        ]);
    }
}
