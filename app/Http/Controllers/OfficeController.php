<?php

namespace App\Http\Controllers;

use App\Domain\Support\ClinicRegistry;
use App\Http\Controllers\Concerns\HandlesSyncRequests;
use App\Models\Office;
use App\Services\Sync\SyncCheckpointService;
use App\Services\Sync\SyncReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class OfficeController extends Controller
{
    use HandlesSyncRequests;

    public function index(): View
    {
        $offices = Office::orderBy('id')->get();
        $activeOfficeId = Office::getActiveOfficeId();

        return view('offices.index', compact('offices', 'activeOfficeId'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'developer_key' => 'nullable|string|max:1000',
            'customer_key' => 'nullable|string|max:1000',
            'api_url' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        if (! empty($validated['api_url']) && ! str_starts_with($validated['api_url'], 'http://') && ! str_starts_with($validated['api_url'], 'https://')) {
            $validated['api_url'] = 'https://'.$validated['api_url'];
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $office = Office::create($validated);

        if (! session()->has('active_office_id')) {
            session(['active_office_id' => $office->id]);
        }

        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Office '{$office->name}' created successfully.",
                'office' => $office,
            ]);
        }

        return redirect()->route('offices.index')
            ->with('status', "Office '{$office->name}' created successfully.");
    }

    public function update(Request $request, Office $office): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'developer_key' => 'nullable|string|max:1000',
            'customer_key' => 'nullable|string|max:1000',
            'api_url' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        if (! empty($validated['api_url']) && ! str_starts_with($validated['api_url'], 'http://') && ! str_starts_with($validated['api_url'], 'https://')) {
            $validated['api_url'] = 'https://'.$validated['api_url'];
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $office->update($validated);

        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Office '{$office->name}' updated successfully.",
                'office' => $office,
            ]);
        }

        return redirect()->route('offices.index')
            ->with('status', "Office '{$office->name}' updated successfully.");
    }

    public function destroy(Office $office): RedirectResponse
    {
        if (Office::count() <= 1) {
            return redirect()->route('offices.index')
                ->with('error', 'Cannot delete the only remaining office location.');
        }

        $name = $office->name;
        $office->delete();

        if (session('active_office_id') == $office->id) {
            session()->forget('active_office_id');
        }

        return redirect()->route('offices.index')
            ->with('status', "Office '{$name}' deleted successfully.");
    }

    public function switch(Request $request): RedirectResponse
    {
        $request->validate([
            'office_id' => 'required|exists:offices,id',
        ]);

        $officeId = (int) $request->input('office_id');
        session([
            'active_office_id' => $officeId,
            'selected_locations' => [(string) $officeId],
        ]);

        return redirect()->back()->with('status', 'Switched active office location.');
    }

    public function switchClinic(Request $request, ClinicRegistry $clinicRegistry): RedirectResponse
    {
        $request->validate([
            'clinic_num' => 'required',
            'office_id' => 'nullable|exists:offices,id',
        ]);

        $officeId = $request->filled('office_id') ? (int) $request->input('office_id') : Office::getActiveOfficeId();
        $clinicNum = $request->input('clinic_num');

        if ($officeId && $clinicNum !== 'all') {
            $clinicRegistry->setActiveClinicNum((int) $clinicNum, $officeId);
            $clinicName = $clinicRegistry->name((int) $clinicNum, $officeId);
            session(['selected_locations' => ["{$officeId}:{$clinicNum}"]]);
            $message = "Switched active clinic to '{$clinicName}'.";
        } else {
            if ($officeId) {
                session()->forget("active_clinic_id_{$officeId}");
                session(['selected_locations' => [(string) $officeId]]);
            }
            $message = 'Viewing all clinics for location.';
        }

        return redirect()->back()->with('status', $message);
    }

    /**
     * AJAX endpoint to persist user's chosen reporting location(s).
     */
    public function selectLocations(Request $request, ClinicRegistry $clinicRegistry): JsonResponse
    {
        $locations = $request->input('locations');
        $selection = $clinicRegistry->select($locations);
        $keys = $selection->keys();

        return response()->json([
            'success' => true,
            'selected' => $keys,
            'active_office_id' => Office::getActiveOfficeId(),
        ]);
    }

    public function syncReport(Office $office, SyncReportService $syncReportService): JsonResponse
    {
        $report = $syncReportService->getReportForOffice($office);

        return response()->json($report);
    }

    public function syncModule(Request $request, Office $office, SyncReportService $syncReportService): JsonResponse
    {
        $request->validate([
            'module' => 'required|string',
        ]);

        $moduleKey = (string) $request->input('module');

        try {
            return response()->json($syncReportService->queueModuleForOffice($office, $moduleKey));
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Queue a full server-side sync for the office. Returns immediately; the
     * queue worker does the work, so it never depends on the browser session.
     */
    public function syncNow(Office $office, SyncReportService $syncReportService): JsonResponse
    {
        return response()->json($syncReportService->queueAllModulesForOffice($office));
    }

    /**
     * Reset sync checkpoint / start date for the office.
     */
    public function resetSyncCheckpoint(Request $request, Office $office, SyncCheckpointService $checkpoints): JsonResponse
    {
        return $this->resetSyncCheckpointFor($request, $checkpoints, (int) $office->id);
    }
}
