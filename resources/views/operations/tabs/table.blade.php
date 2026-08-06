{{--
    Operations tab wrapper. The TABLE itself is the shared <x-analytics-table> component
    (single source for all table rendering). This wrapper adds the Operations-specific
    chrome: subtab bar, toolbar (heat legend + search + export), and the legacy embedded
    drilldown modal (retired once all drilldowns move to DDS.modal).

    Expects: $tab, $subtabs (slug=>label), $activeSubtab, $spec
--}}
<div class="bg-white border border-slate-200 rounded shadow-sm">

    {{-- Subtab bar (Operations deep-link routes; handled by the ops tab JS) --}}
    @if (!empty($subtabs))
        <ul class="flex border-b border-slate-200 px-4 pt-3 gap-1">
            @foreach ($subtabs as $slug => $label)
                <a href="{{ route('operations.tab', $slug === 'default' ? [$tab] : [$tab, $slug]) }}"
                   data-ops-subtab="{{ $slug }}"
                   class="text-xs font-semibold px-4 py-2 rounded-t cursor-pointer whitespace-nowrap
                          {{ $slug === $activeSubtab
                              ? 'bg-white text-black border border-b-0 border-slate-200 -mb-px'
                              : 'text-slate-400 hover:text-slate-600 bg-slate-50' }}">
                    {{ $label }}
                </a>
            @endforeach
        </ul>
    @endif

    {{-- Toolbar: heat legend + search + export --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-4">
        <div class="flex items-center gap-1.5 text-[11px] font-medium">
            <span class="dds-heat-top text-[#1e4620] px-2.5 py-1 rounded font-bold">Top 20%</span>
            <span class="dds-heat-mid text-[#78350f] px-2.5 py-1 rounded font-bold">Mid Tier</span>
            <span class="dds-heat-bottom text-[#9f1239] px-2.5 py-1 rounded font-bold">Bottom 20%</span>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <div class="relative w-full sm:w-64">
                <input type="text" data-ops-search placeholder="Search"
                       class="w-full border border-slate-300 rounded px-3 py-1.5 text-xs pr-8 bg-white focus:outline-none focus:border-[#00bfa5]">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute inset-y-0 right-0 my-auto mr-2.5"></i>
            </div>
            <button data-ops-export class="dds-btn-accent font-bold px-4 py-1.5 rounded text-xs shrink-0">
                Export CSV
            </button>
        </div>
    </div>

    {{-- The table (all 3 types handled by the component; real sticky columns) --}}
    <x-analytics-table :spec="$spec" :active-subtab="$activeSubtab" />
</div>

{{-- Drilldowns use the shared, stackable DDS.modal.details (ui.js). The old embedded
     #ops_drilldown_modal + openOpsDrilldown copy has been retired. --}}
