<?php

namespace App\Http\Controllers\Concerns;

use App\Models\SyncRequest;
use App\Services\Sync\SyncCheckpointService;
use App\Services\Sync\SyncRequestRunner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use RuntimeException;

/**
 * Date-range sync requests and checkpoint resets, shared by the Sync Manager,
 * Office locations and the OD Data Explorer.
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
            'module' => ['required_without:modules', 'string', Rule::in($runner->acceptedModules())],
            'modules' => ['required_without:module', 'array', 'min:1'],
            'modules.*' => ['string', Rule::in($runner->acceptedModules())],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'prune_deleted' => ['nullable', 'boolean'],
        ]);

        try {
            $syncRequests = $runner->createAndQueueMany(
                $officeId,
                $validated['modules'] ?? [$validated['module']],
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null,
                (bool) ($validated['prune_deleted'] ?? false),
                auth()->id(),
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $modules = implode(', ', array_map(fn (SyncRequest $syncRequest) => $syncRequest->module, $syncRequests));

        return response()->json([
            'success' => true,
            'message' => count($syncRequests) === 1
                ? "Server-to-server sync request queued for '{$modules}'. It runs on the server — you can close this page."
                : count($syncRequests)." sync requests queued ({$modules}). They run on the server — you can close this page.",
            'sync_request' => $syncRequests[0],
            'sync_requests' => $syncRequests,
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

    /**
     * @param  bool  $officeFromRequest  let the request pick the office (Sync Manager selector);
     *                                   otherwise the caller's office (route or active office) is authoritative
     */
    protected function resetSyncCheckpointFor(Request $request, SyncCheckpointService $checkpoints, int $officeId, bool $officeFromRequest = false): JsonResponse
    {
        $validated = $request->validate([
            'office_id' => ['nullable', 'integer', 'exists:offices,id'],
            'module' => ['required_without:modules', 'string'],
            'modules' => ['required_without:module', 'array', 'min:1'],
            'modules.*' => ['string'],
            'start_date' => ['nullable', 'date'],
            'last_synced_at' => ['nullable', 'date'],
            'last_primary_key' => ['nullable', 'integer', 'min:0'],
        ]);

        $targetOfficeId = $officeFromRequest && ! empty($validated['office_id']) ? (int) $validated['office_id'] : $officeId;
        $startDate = $validated['start_date'] ?? $validated['last_synced_at'] ?? null;
        $lastPrimaryKey = (int) ($validated['last_primary_key'] ?? 0);

        try {
            $result = $checkpoints->resetForOffice($targetOfficeId, $validated['modules'] ?? $validated['module'], $startDate, $lastPrimaryKey);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }

        return response()->json($result);
    }
}
