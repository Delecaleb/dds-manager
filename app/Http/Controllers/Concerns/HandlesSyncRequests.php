<?php

namespace App\Http\Controllers\Concerns;

use App\Models\SyncRequest;
use App\Services\Sync\SyncRequestRunner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

/**
 * Date-range sync requests, shared by the Sync Manager
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
