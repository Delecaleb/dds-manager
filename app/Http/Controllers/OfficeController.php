<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Services\Sync\AppointmentSyncService;
use App\Services\Sync\PatientSyncService;
use App\Services\Sync\ProcedureLogSyncService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfficeController extends Controller
{
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
        session(['active_office_id' => $officeId]);

        return redirect()->back()->with('status', 'Switched active office location.');
    }

    public function syncNow(Office $office): JsonResponse
    {
        ob_start();

        try {
            app(PatientSyncService::class)->forOffice($office)->sync();
            app(AppointmentSyncService::class)->forOffice($office)->sync();
            app(ProcedureLogSyncService::class)->forOffice($office)->sync();

            ob_end_clean();

            return response()->json([
                'success' => true,
                'message' => "Successfully synced data for office '{$office->name}'.",
            ]);
        } catch (Exception $e) {
            ob_end_clean();

            return response()->json([
                'success' => false,
                'error' => "Sync failed for office '{$office->name}': ".$e->getMessage(),
            ], 500);
        }
    }
}
