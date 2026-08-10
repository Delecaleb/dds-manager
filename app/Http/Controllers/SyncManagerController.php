<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\SyncLog;
use App\Models\SyncRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SyncManagerController extends Controller
{
    /**
     * Display the standalone Sync Manager page.
     */
    public function index(): View
    {
        $modules = [
            'appointments' => 'Appointments',
            'procedurelogs' => 'Procedures',
            'patients' => 'Patients',
            'adjustments' => 'Adjustments',
            'payments' => 'Payments',
            'claimprocs' => 'Insurance Claims',
            'treatmentplans' => 'Treatment Plans',
            'all' => 'All Modules',
        ];

        return view('sync_manager.index', [
            'modules' => $modules,
        ]);
    }

    /**
     * Get recent server sync requests.
     */
    public function requests(): JsonResponse
    {
        $requests = SyncRequest::with(['user:id,name'])
            ->orderBy('id', 'desc')
            ->take(50)
            ->get();

        return response()->json([
            'requests' => $requests,
        ]);
    }

    /**
     * Trigger a new server-to-server date range sync request.
     */
    public function triggerSync(Request $request): JsonResponse
    {
        $request->validate([
            'module' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'prune_deleted' => 'nullable|boolean',
        ]);

        $module = strtolower(trim((string) $request->input('module')));
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $pruneDeleted = (bool) $request->input('prune_deleted', false);

        if ($startDate && $endDate && $startDate > $endDate) {
            return response()->json(['error' => 'Start date cannot be after end date.'], 422);
        }

        $syncReq = SyncRequest::create([
            'office_id' => Office::getActiveOffice()->id ?? 1,
            'module' => $module,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'prune_deleted' => $pruneDeleted,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        // Attempt immediate background execution on server
        try {
            if (str_contains(PHP_OS_FAMILY, 'Windows')) {
                pclose(popen('start /B php '.base_path('artisan')." sync:process-pending --id={$syncReq->id} > NUL 2>&1", 'r'));
            } else {
                exec('php '.base_path('artisan')." sync:process-pending --id={$syncReq->id} > /dev/null 2>&1 &");
            }
        } catch (Exception $e) {
            // Log warning, background scheduler will process pending records
        }

        return response()->json([
            'success' => true,
            'message' => "Server-to-server sync request created for '{$module}' module.",
            'sync_request' => $syncReq,
        ]);
    }

    /**
     * Cancel a pending or running sync request.
     */
    public function cancelSync(Request $request): JsonResponse
    {
        $id = (int) $request->input('id');
        $syncReq = SyncRequest::find($id);

        if (! $syncReq) {
            return response()->json(['error' => 'Sync request not found.'], 404);
        }

        if (in_array($syncReq->status, ['completed', 'failed', 'cancelled'])) {
            return response()->json(['error' => "Cannot cancel a sync request with status '{$syncReq->status}'."], 422);
        }

        $syncReq->update([
            'status' => 'cancelled',
            'completed_at' => now(),
            'error_message' => 'Cancelled by user.',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Sync request #{$id} has been cancelled.",
        ]);
    }

    /**
     * Get module sync checkpoints.
     */
    public function checkpoints(): JsonResponse
    {
        $logs = SyncLog::orderBy('module')->get();

        return response()->json([
            'logs' => $logs,
        ]);
    }

    /**
     * Reset module sync checkpoint.
     */
    public function resetCheckpoint(Request $request): JsonResponse
    {
        $module = (string) $request->input('module');
        $lastSyncedAt = $request->input('last_synced_at');
        $lastPrimaryKey = (int) $request->input('last_primary_key', 0);

        if (empty($module)) {
            return response()->json(['error' => 'Module is required.'], 400);
        }

        $formattedDate = null;
        if (! empty($lastSyncedAt)) {
            $ts = strtotime((string) $lastSyncedAt);
            if ($ts !== false) {
                $formattedDate = date('Y-m-d H:i:s', $ts);
            }
        }

        if ($module === 'all') {
            SyncLog::query()->update([
                'last_synced_at' => $formattedDate,
                'last_primary_key' => $lastPrimaryKey,
                'status' => 'idle',
                'last_error' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully reset sync checkpoints for ALL modules.',
            ]);
        }

        $log = SyncLog::firstOrCreate(
            ['module' => $module],
            ['status' => 'idle', 'total_processed' => 0]
        );

        $log->update([
            'last_synced_at' => $formattedDate,
            'last_primary_key' => $lastPrimaryKey,
            'status' => 'idle',
            'last_error' => null,
        ]);

        return response()->json([
            'success' => true,
            'module' => $module,
            'message' => "Successfully reset sync checkpoint for module '{$module}'.",
        ]);
    }
}
