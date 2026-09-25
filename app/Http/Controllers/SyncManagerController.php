<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesSyncRequests;
use App\Models\Office;
use App\Services\Sync\QueueHealthService;
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
        // Every office: one submit can queue requests for several offices.
        return $this->listSyncRequests(null);
    }

    /**
     * Queue date range sync requests for the offices ticked on the page.
     */
    public function triggerSync(Request $request, SyncRequestRunner $runner): JsonResponse
    {
        return $this->createSyncRequest($request, $runner, $this->activeOfficeId(), officesFromRequest: true);
    }

    /**
     * Cancel a pending sync request of any office listed on the page.
     */
    public function cancelSync(Request $request): JsonResponse
    {
        return $this->cancelRequest((int) $request->input('id'), null);
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

    /**
     * Queue health panel (rendered HTML, refreshed in place).
     */
    public function health(QueueHealthService $health): View
    {
        return view('sync_manager.partials.queue-health', ['health' => $health->snapshot()]);
    }

    /**
     * Put pending requests that have no job in the queue back on it.
     */
    public function requeueMissing(QueueHealthService $health): JsonResponse
    {
        $count = $health->requeueMissingRequests();

        return response()->json([
            'success' => true,
            'message' => $count > 0 ? "Re-queued {$count} sync request(s)." : 'Every pending request is already in the queue.',
            'requeued' => $count,
        ]);
    }

    private function activeOfficeId(): int
    {
        return (int) ((Office::getActiveOffice() ?? Office::first())?->id ?? 0);
    }
}
