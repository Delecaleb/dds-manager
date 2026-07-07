{{--
    Production Details = the shared table plus Date / Provider grouping toggles.
    Toggles re-fetch this tab with ?group=provider,date (handled in the shell JS).

    Expects everything table.blade.php needs, plus $group (array of active dims).
--}}
<div class="space-y-3">
    <div class="flex items-center gap-6 bg-white border border-slate-200 rounded shadow-sm px-4 py-2.5 text-xs font-medium">
        <span class="text-slate-500">Group by:</span>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" data-ops-group="date"
                   {{ in_array('date', $group ?? []) ? 'checked' : '' }}
                   class="w-4 h-4 accent-[#00bfa5] cursor-pointer">
            <span class="text-slate-700">Date</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" data-ops-group="provider"
                   {{ in_array('provider', $group ?? []) ? 'checked' : '' }}
                   class="w-4 h-4 accent-[#00bfa5] cursor-pointer">
            <span class="text-slate-700">Provider</span>
        </label>
    </div>

    @include('operations.tabs.table', [
        'tab'          => $tab,
        'subtabs'      => $subtabs,
        'activeSubtab' => $activeSubtab,
        'spec'         => $spec,
    ])
</div>
