<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesSyncRequests;
use App\Models\Office;
use App\Services\Sync\SyncCheckpointService;
use App\Services\Sync\SyncRequestRunner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SyncManagerController extends Controller
{
    use HandlesSyncRequests;

    /**
     * Display the standalone Sync Manager page.
     */
    public function index(SyncCheckpointService $checkpoints, SyncRequestRunner $runner): View
    {
        $currentOffice = Office::getActiveOffice() ?? Office::first();
        $offices = Office::where('is_active', true)->orderBy('id')->get();
        if ($offices->isEmpty()) {
            $offices = Office::orderBy('id')->get();
        }

        return view('sync_manager.index', [
            'currentOffice' => $currentOffice,
            'offices' => $offices,
            'modules' => $runner->rangeModules(),
            'resetModules' => $checkpoints->resettableModules(),
        ]);
    }

    /**
     * Get recent server sync requests.
     */
    public function requests(): JsonResponse
    {
        return $this->listSyncRequests($this->activeOfficeId());
    }

    /**
     * Queue a new server-to-server date range sync request.
     */
    public function triggerSync(Request $request, SyncRequestRunner $runner): JsonResponse
    {
        return $this->createSyncRequest($request, $runner, $this->activeOfficeId());
    }

    /**
     * Cancel a pending sync request.
     */
    public function cancelSync(Request $request): JsonResponse
    {
        return $this->cancelRequest((int) $request->input('id'), $this->activeOfficeId());
    }

    /**
     * Get module sync checkpoints.
     */
    public function checkpoints(SyncCheckpointService $checkpoints): JsonResponse
    {
        return response()->json(['logs' => $checkpoints->forOffice($this->activeOfficeId())]);
    }

    /**
     * Reset sync checkpoint (start date / primary key) for an office.
     */
    public function resetCheckpoint(Request $request, SyncCheckpointService $checkpoints): JsonResponse
    {
        return $this->resetSyncCheckpointFor($request, $checkpoints, $this->activeOfficeId(), officeFromRequest: true);
    }

    private function activeOfficeId(): int
    {
        return (int) ((Office::getActiveOffice() ?? Office::first())?->id ?? 0);
    }
}
