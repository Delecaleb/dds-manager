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

{{-- Legacy embedded-JSON drilldown modal (used by the cell drilldown buttons that pass
     row detail arrays). Slated to move to DDS.modal in the modal-consolidation step. --}}
<div id="ops_drilldown_modal" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-lg shadow-xl border border-gray-200 w-full max-w-4xl flex flex-col max-h-[85vh]">
        <div class="flex justify-between items-center p-5 border-b border-gray-100 bg-gray-50/50 rounded-t-lg">
            <h4 class="text-sm font-bold text-gray-900" id="ops_modal_title">Details</h4>
            <button onclick="closeOpsDrilldown()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 focus:outline-none">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <table class="w-full text-left border-collapse text-xs whitespace-nowrap">
                <thead class="sticky top-0 bg-white shadow-sm ring-1 ring-gray-100"><tr id="ops_modal_headers"></tr></thead>
                <tbody class="divide-y divide-gray-100 text-gray-700" id="ops_modal_rows"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Embedded-detail drilldown (row passes a details[] array). openLimitlessModal now lives
    // in ui.js (DDS.modal). Only the embedded-JSON variant remains here for now.
    function openOpsDrilldown(title, details) {
        const modal = document.getElementById('ops_drilldown_modal');
        const titleEl = document.getElementById('ops_modal_title');
        const headerContainer = document.getElementById('ops_modal_headers');
        const rowContainer = document.getElementById('ops_modal_rows');

        titleEl.textContent = `Breakdown | ${title}`;
        headerContainer.innerHTML = '';
        rowContainer.innerHTML = '';

        if (!details || details.length === 0) {
            rowContainer.innerHTML = `<tr><td class="py-8 text-center text-gray-400 text-sm">No records found.</td></tr>`;
        } else {
            const keys = Object.keys(details[0]);
            keys.forEach(key => {
                const th = document.createElement('th');
                th.className = 'py-2.5 px-4 font-bold text-gray-900 border-b border-gray-200 capitalize';
                if (key.toLowerCase().includes('production') || key.toLowerCase().includes('visits') || key.toLowerCase().includes('#')) th.classList.add('text-right');
                th.textContent = key;
                headerContainer.appendChild(th);
            });
            details.forEach(item => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50 border-b border-gray-50';
                keys.forEach(key => {
                    const td = document.createElement('td');
                    td.className = 'py-3 px-4';
                    let val = item[key];
                    if (key.toLowerCase().includes('production') && typeof val === 'number') {
                        td.className += ' text-right font-medium text-gray-900';
                        td.textContent = DDS.fmt.money(val);
                    } else if ((key.toLowerCase().includes('visits') || key.toLowerCase().includes('#')) && typeof val === 'number') {
                        td.className += ' text-right font-medium text-gray-900';
                        td.textContent = DDS.fmt.number(val);
                    } else if (key.toLowerCase().includes('id') || key.toLowerCase().includes('pat')) {
                        td.className += ' text-gray-800 font-bold';
                        td.textContent = val;
                    } else {
                        td.className += ' text-gray-700 font-semibold';
                        td.textContent = val || '—';
                    }
                    tr.appendChild(td);
                });
                rowContainer.appendChild(tr);
            });
        }
        modal.classList.remove('hidden');
    }

    function closeOpsDrilldown() {
        document.getElementById('ops_drilldown_modal').classList.add('hidden');
    }
</script>
