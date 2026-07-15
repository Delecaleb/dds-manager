@php
    $tab = 'marketing';
    $subtab = $activeSubtab ?? 'default';
@endphp

<div class="space-y-4">

    {{-- Subtab bar --}}
    <div class="bg-white border w-full border-slate-200 rounded shadow-sm">
        @if (!empty($subtabs))
            <ul class="flex border-b border-slate-200 px-4 pt-3 gap-1">
                @foreach ($subtabs as $slug => $label)
                    <a href="{{ route('operations.tab', $slug === 'default' ? [$tab] : [$tab, $slug]) }}"
                        data-ops-subtab="{{ $slug }}" class="text-xs font-semibold px-4 py-2 rounded-t cursor-pointer whitespace-nowrap
                                              {{ $slug === $subtab
                    ? 'bg-white text-black border border-b-0 border-slate-200 -mb-px hover:bg-slate-50'
                    : 'text-slate-400 hover:text-slate-600 bg-slate-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </ul>
        @endif

        {{-- Toolbar: zip filter --}}
        <div class="flex items-center justify-end p-3 bg-white">
            <div class="flex items-center space-x-2">
                <label for="marketing_zip" class="text-xs font-semibold text-slate-700">ZIP</label>
                <select id="marketing_zip" data-ops-filter="zip"
                    class="border border-slate-300 rounded px-2 py-1 text-xs bg-white text-slate-700 w-32 focus:outline-none focus:ring-1 focus:ring-[#00bfa5]">
                    <option value="ALL">ALL</option>
                    @if(isset($spec['available_zips']))
                        @foreach($spec['available_zips'] as $z)
                            <option value="{{ $z }}" {{ request('zip') == $z ? 'selected' : '' }}>{{ $z }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>
    </div>

    @if ($subtab !== 'patient-analysis')
        {{-- Marketing default: Donut Charts for New Patients --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- Top 10 Referrals --}}
            <div class="bg-white border border-slate-200 rounded shadow-sm p-4 flex flex-col h-full">
                <div class="mb-4">
                    <h3 class="text-sm font-bold text-gray-900 leading-tight">Top 10 Referrals <span
                            class="font-normal text-gray-400 ml-1">| New Patients</span></h3>
                </div>
                <!-- Relative container to give Chart.js an absolute bound -->
                <div class="relative w-full aspect-square flex-1 min-h-[300px]">
                    <canvas id="marketing_referrals_chart" class="absolute inset-0 w-full h-full"></canvas>
                </div>
            </div>

            {{-- Top 10 Payors --}}
            <div class="bg-white border border-slate-200 rounded shadow-sm p-4 flex flex-col h-full">
                <div class="mb-4">
                    <h3 class="text-sm font-bold text-gray-900 leading-tight">Top 10 Payors <span
                            class="font-normal text-gray-400 ml-1">| New Patients</span></h3>
                </div>
                <div class="relative w-full aspect-square flex-1 min-h-[300px]">
                    <canvas id="marketing_payors_chart" class="absolute inset-0 w-full h-full"></canvas>
                </div>
            </div>

            {{-- Top 10 Employers --}}
            <div class="bg-white border border-slate-200 rounded shadow-sm p-4 flex flex-col h-full">
                <div class="mb-4">
                    <h3 class="text-sm font-bold text-gray-900 leading-tight">Top 10 Employers <span
                            class="font-normal text-gray-400 ml-1">| New Patients</span></h3>
                </div>
                <div class="relative w-full aspect-square flex-1 min-h-[300px]">
                    <canvas id="marketing_employers_chart" class="absolute inset-0 w-full h-full"></canvas>
                </div>
            </div>

        </div>

        {{-- Map and Top Zips row --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Map section --}}
            <div
                class="bg-gray-100 border border-slate-200 rounded shadow-sm overflow-hidden flex flex-col h-[400px] relative">
                <!-- Using a generic Detroit Google Maps embed as a styling placebo for the underlying heatmap screenshot -->
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d117929.98632616239!2d-83.16709893540026!3d42.3618790074218!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8824ca0110cb1d75%3A0x5776864e35b9c4d2!2sDetroit%2C%20MI!5e0!3m2!1sen!2sus!4v1714088015523!5m2!1sen!2sus"
                    width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
                <div class="absolute inset-0 pointer-events-none"
                    style="background: radial-gradient(circle at 30% 30%, rgba(239, 68, 68, 0.4) 0%, rgba(239, 68, 68, 0) 15%), radial-gradient(circle at 70% 60%, rgba(239, 68, 68, 0.4) 0%, rgba(239, 68, 68, 0) 15%), radial-gradient(circle at 80% 80%, rgba(239, 68, 68, 0.3) 0%, rgba(239, 68, 68, 0) 10%);">
                </div>
            </div>

            {{-- Top 10 Zip Codes Table --}}
            <div class="bg-white border border-slate-200 rounded shadow-sm overflow-hidden flex flex-col h-[400px]">
                <div class="flex items-center justify-between p-4 border-b border-slate-100 bg-white">
                    <h3 class="text-[15px] font-bold text-gray-900 leading-tight">Top 10 Zip Codes</h3>
                    <span class="text-xs text-gray-400 font-medium">{{ request('start', 'May 01, 2026') }} -
                        {{ request('end', 'May 31, 2026') }}</span>
                </div>
                <div class="flex-1 overflow-auto bg-white">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50/50 text-gray-900 sticky top-0 z-10 border-b border-gray-100">
                            <tr>
                                <th class="py-3 px-4 font-extrabold w-12 text-center text-[11px]">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-3 h-3 text-gray-400 -mb-0.5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7" />
                                        </svg>
                                        <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </th>
                                <th class="py-3 px-4 font-extrabold text-[12px]">
                                    <div class="flex items-center gap-1">
                                        Zip Code
                                    </div>
                                </th>
                                <th class="py-3 px-6 font-extrabold text-right text-[12px]">
                                    <div class="flex items-center justify-end gap-1">
                                        <div class="flex flex-col items-center justify-center mr-1">
                                            <svg class="w-3 h-3 text-gray-400 -mb-0.5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7" />
                                            </svg>
                                            <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                        New Patient Visits
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700 font-semibold text-xs">
                            @if (!empty($spec['top_zips']))
                                @php $rank = 1; @endphp
                                @foreach($spec['top_zips'] as $zip => $count)
                                    <tr class="hover:bg-gray-50/80 transition-colors">
                                        <td class="py-4 px-4 text-center font-bold text-gray-900">{{ $rank++ }}</td>
                                        <td class="py-4 px-4 text-gray-800">{{ $zip ?: 'No Zip' }}</td>
                                        <td class="py-4 px-6 text-right text-gray-900">{{ number_format($count) }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3" class="py-12 text-center text-gray-400">No zip code data available.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Marketing Section Table wrapper --}}
        @if (isset($spec['table_data']))
            @php
                $tableSpec = $spec['table_data'];
                $isExisting = $tableSpec['is_existing'] ?? false;
            @endphp
            
            <div class="bg-white border border-slate-200 rounded shadow-sm p-6 mt-6">
                <!-- Section Title & Legend -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 leading-tight">
                            {{ str_contains($subtab, 'referral') ? 'Referrals' : 'Payors' }} Breakdown
                            <span class="font-normal text-xs text-gray-400 ml-2">
                                | {{ $isExisting ? 'Existing Patients' : 'New Patients' }}
                            </span>
                        </h3>
                    </div>
                </div>

                <!-- Control Bar -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
                    <!-- Legend -->
                    <div class="flex items-center gap-2 text-[11px] font-semibold">
                        <span class="bg-[#e8f5e9] text-[#2e7d32] px-2.5 py-1 rounded">Top 20%</span>
                        <span class="bg-[#fff8e1] text-[#f57f17] px-2.5 py-1 rounded">Mid Tier</span>
                        <span class="bg-[#ffebee] text-[#c62828] px-2.5 py-1 rounded">Bottom 20%</span>
                    </div>

                    <!-- Search & CSV Export -->
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <div class="relative w-full sm:w-64">
                            <input type="text" id="marketing_table_search" placeholder="Search..."
                                   class="w-full border border-slate-300 rounded px-3 py-1.5 text-xs pr-8 bg-white focus:outline-none focus:border-[#00bfa5]">
                            <svg class="w-4 h-4 text-slate-400 absolute inset-y-0 right-0 my-auto mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <button id="marketing_table_export"
                                class="bg-white border border-[#00bfa5] text-[#00bfa5] font-bold px-4 py-1.5 rounded text-xs shrink-0 hover:bg-[#00bfa5] hover:text-white transition-colors">
                            Export CSV
                        </button>
                    </div>
                </div>

                <!-- Table Grid -->
                <div class="overflow-x-auto border border-slate-100 rounded-md max-h-[60vh] relative min-w-full">
                    <table class="w-full text-left border-collapse text-xs whitespace-nowrap" id="marketing_main_table">
                        <thead class="sticky top-0 z-20 bg-gray-50 shadow-[0_1px_0_0_#e2e8f0]">
                            <tr>
                                @foreach ($tableSpec['columns'] as $col)
                                    <th class="py-3 px-4 font-bold border-r border-gray-100 text-gray-900 bg-gray-50/90 backdrop-blur-sm
                                               {{ $loop->first ? 'sticky left-0 z-30 shadow-[1px_0_0_0_#e2e8f0] bg-gray-50' : '' }}
                                               {{ ($col['type'] ?? 'text') === 'text' ? 'text-left' : 'text-right' }}"
                                        title="{{ $col['tooltip'] ?? '' }}">
                                        {{ $col['label'] }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700" id="marketing_table_rows_container">
                            @forelse ($tableSpec['rows'] as $idx => $row)
                                @php
                                    $bgClass = match($row['tier_color'] ?? 'mid') {
                                        'top' => 'bg-[#e8f5e9]',
                                        'bottom' => 'bg-[#ffebee]',
                                        default => 'bg-[#fff8e1]',
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50 transition border-b border-gray-50 marketing-data-row"
                                    data-search-terms="{{ strtolower($row['entity']) }}">
                                    
                                    @foreach ($tableSpec['columns'] as $col)
                                        @php
                                            $val = $row[$col['key']] ?? null;
                                            $type = $col['type'] ?? 'text';
                                            $colKey = $col['key'];

                                            // Cell Formatting
                                            if ($val === null) {
                                                $formatted = '—';
                                            } else {
                                                switch ($type) {
                                                    case 'money':
                                                        $formatted = '$ ' . number_format((float)$val, 2);
                                                        break;
                                                    case 'percent':
                                                        $formatted = number_format((float)$val, 2) . '%';
                                                        break;
                                                    case 'number':
                                                        $formatted = number_format((float)$val);
                                                        break;
                                                    default:
                                                        $formatted = $val;
                                                }
                                            }
                                        @endphp
                                        <td class="py-2.5 px-4 border-r border-gray-50 font-medium {{ $bgClass }}
                                                   {{ $loop->first ? 'sticky left-0 z-10 shadow-[1px_0_0_0_#f1f5f9]' : '' }}
                                                   {{ $type === 'text' ? 'text-left' : 'text-right' }}">
                                            @if (!empty($col['drilldown']) && $val > 0)
                                                <button type="button" 
                                                        class="text-[#00bfa5] font-bold hover:underline inline-flex items-center gap-1.5 focus:outline-none"
                                                        onclick="openMarketingDrilldown({{ json_encode($row['entity']) }}, {{ json_encode($row['details'] ?? []) }})">
                                                    {{ $formatted }}
                                                    <svg class="h-3 w-3 stroke-current" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                                </button>
                                            @else
                                                {{ $formatted }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($tableSpec['columns']) }}" class="py-12 text-center text-gray-400 text-sm">
                                        No data available for the selected parameters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="sticky bottom-0 z-20 bg-gray-50 border-t border-gray-200 font-bold shadow-[0_-1px_0_0_#e2e8f0]">
                            <!-- Average Row -->
                            @if (!empty($tableSpec['average']))
                                <tr class="bg-gray-50 text-gray-900 border-b border-gray-100">
                                    @foreach ($tableSpec['columns'] as $col)
                                        @php
                                            $val = $tableSpec['average'][$col['key'] === 'entity' ? 'visits' : $col['key']] ?? null;
                                            $type = $col['type'] ?? 'text';
                                            if ($col['key'] === 'entity') {
                                                $formatted = 'Average:';
                                            } else {
                                                switch ($type) {
                                                    case 'money':
                                                        $formatted = '$ ' . number_format((float)$val, 2);
                                                        break;
                                                    case 'percent':
                                                        $formatted = number_format((float)$val, 2) . '%';
                                                        break;
                                                    case 'number':
                                                        $formatted = number_format((float)$val);
                                                        break;
                                                    default:
                                                        $formatted = e($val);
                                                }
                                            }
                                        @endphp
                                        <td class="py-2.5 px-4 border-r border-gray-200 bg-gray-50/95
                                                   {{ $loop->first ? 'sticky left-0 z-30 shadow-[1px_0_0_0_#e2e8f0] bg-gray-50' : 'text-right' }}">
                                            {{ $formatted }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endif

                            <!-- Total Row -->
                            @if (!empty($tableSpec['total']))
                                <tr class="bg-gray-100 text-gray-900 font-bold border-t border-gray-200">
                                    @foreach ($tableSpec['columns'] as $col)
                                        @php
                                            $val = $tableSpec['total'][$col['key']] ?? null;
                                            $type = $col['type'] ?? 'text';
                                            if ($col['key'] === 'entity') {
                                                $formatted = 'Total:';
                                            } else {
                                                switch ($type) {
                                                    case 'money':
                                                        $formatted = '$ ' . number_format((float)$val, 2);
                                                        break;
                                                    case 'percent':
                                                        $formatted = number_format((float)$val, 2) . '%';
                                                        break;
                                                    case 'number':
                                                        $formatted = number_format((float)$val);
                                                        break;
                                                    default:
                                                        $formatted = e($val);
                                                }
                                            }
                                        @endphp
                                        <td class="py-2.5 px-4 border-r border-gray-200 bg-gray-100/95
                                                   {{ $loop->first ? 'sticky left-0 z-30 shadow-[1px_0_0_0_#d1d5db] bg-gray-100' : 'text-right' }}">
                                            {{ $formatted }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>

                <!-- Pagination Component -->
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mt-6 pt-4 border-t border-gray-100">
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <span>Items per page:</span>
                        <select id="marketing_items_per_page" class="border border-gray-300 rounded px-2 py-1 bg-white text-gray-700 focus:outline-none">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div class="text-xs text-gray-500 font-semibold" id="marketing_pagination_info">
                        Showing 0-0 of 0 items
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button id="marketing_prev_page" class="w-8 h-8 rounded border border-gray-300 flex items-center justify-center bg-white text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </button>
                        <button id="marketing_next_page" class="w-8 h-8 rounded border border-gray-300 flex items-center justify-center bg-white text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Patient List Drill-down Modal overlay -->
            <div id="marketing_drilldown_modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
                <div class="bg-white rounded-lg shadow-xl border border-gray-200 w-full max-w-4xl flex flex-col max-h-[85vh] animate-in fade-in zoom-in duration-200">
                    <div class="flex justify-between items-center p-5 border-b border-gray-100 bg-gray-50/50 rounded-t-lg">
                        <h4 class="text-sm font-bold text-gray-900" id="marketing_modal_title">Patient List</h4>
                        <button onclick="closeMarketingDrilldown()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 focus:outline-none">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-6">
                        <table class="w-full text-left border-collapse text-xs whitespace-nowrap">
                            <thead class="sticky top-0 bg-white shadow-sm ring-1 ring-gray-100">
                                <tr>
                                    <th class="py-2.5 px-4 font-bold text-gray-900 border-b border-gray-200">PatNum</th>
                                    <th class="py-2.5 px-4 font-bold text-gray-900 border-b border-gray-200">Patient Name</th>
                                    <th class="py-2.5 px-4 font-bold text-gray-900 border-b border-gray-200">Phone</th>
                                    <th class="py-2.5 px-4 font-bold text-gray-900 border-b border-gray-200">Email</th>
                                    <th class="py-2.5 px-4 font-bold text-right text-gray-900 border-b border-gray-200">Range Production</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-700" id="marketing_modal_rows">
                                <!-- Filled by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Table Client-Side JavaScript Logic -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const searchInput = document.getElementById('marketing_table_search');
                    const itemsPerPageSelect = document.getElementById('marketing_items_per_page');
                    const container = document.getElementById('marketing_table_rows_container');
                    const paginationInfo = document.getElementById('marketing_pagination_info');
                    const prevBtn = document.getElementById('marketing_prev_page');
                    const nextBtn = document.getElementById('marketing_next_page');
                    const exportBtn = document.getElementById('marketing_table_export');

                    let currentPage = 1;
                    let itemsPerPage = parseInt(itemsPerPageSelect.value);
                    let allRows = Array.from(document.querySelectorAll('.marketing-data-row'));
                    let filteredRows = [...allRows];

                    function updatePagination() {
                        const totalItems = filteredRows.length;
                        const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;

                        if (currentPage > totalPages) currentPage = totalPages;
                        if (currentPage < 1) currentPage = 1;

                        const startIndex = (currentPage - 1) * itemsPerPage;
                        const endIndex = Math.min(startIndex + itemsPerPage, totalItems);

                        allRows.forEach(row => row.classList.add('hidden'));
                        for (let i = startIndex; i < endIndex; i++) {
                            filteredRows[i].classList.remove('hidden');
                        }

                        // Info string
                        paginationInfo.textContent = totalItems === 0
                            ? `Showing 0 of 0 items`
                            : `Showing ${startIndex + 1}-${endIndex} of ${totalItems} items`;

                        prevBtn.disabled = currentPage === 1;
                        nextBtn.disabled = currentPage === totalPages;
                    }

                    // Search listener
                    searchInput.addEventListener('input', function() {
                        const val = this.value.toLowerCase().trim();
                        filteredRows = allRows.filter(row => {
                            const terms = row.getAttribute('data-search-terms') || '';
                            return terms.includes(val);
                        });
                        currentPage = 1;
                        updatePagination();
                    });

                    // Items per page listener
                    itemsPerPageSelect.addEventListener('change', function() {
                        itemsPerPage = parseInt(this.value);
                        currentPage = 1;
                        updatePagination();
                    });

                    // Buttons
                    prevBtn.addEventListener('click', function() {
                        if (currentPage > 1) {
                            currentPage--;
                            updatePagination();
                        }
                    });
                    nextBtn.addEventListener('click', function() {
                        const totalPages = Math.ceil(filteredRows.length / itemsPerPage);
                        if (currentPage < totalPages) {
                            currentPage++;
                            updatePagination();
                        }
                    });

                    // CSV Export logic
                    exportBtn.addEventListener('click', function() {
                        const headers = Array.from(document.querySelectorAll('#marketing_main_table thead th'))
                            .map(th => th.textContent.trim());
                        
                        const rows = filteredRows.map(tr => {
                            return Array.from(tr.querySelectorAll('td')).map(td => {
                                // Extract text securely without nesting HTML details
                                return td.innerText.replace(/\n|↑|↓/g, '').trim();
                            });
                        });

                        // Add tools averages & totals
                        const footerRows = Array.from(document.querySelectorAll('#marketing_main_table tfoot tr')).map(tr => {
                            return Array.from(tr.querySelectorAll('td')).map(td => td.innerText.trim());
                        });

                        const csvContent = [
                            headers.join(','),
                            ...rows.map(row => row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',')),
                            ...footerRows.map(row => row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(','))
                        ].join('\n');

                        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                        const link = document.createElement('a');
                        const url = URL.createObjectURL(blob);
                        link.setAttribute('href', url);
                        link.setAttribute('download', `marketing_breakdown_${new Date().toISOString().slice(0, 10)}.csv`);
                        link.style.visibility = 'hidden';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    });

                    // Init pagination
                    updatePagination();
                });

                function openMarketingDrilldown(entityName, details) {
                    const modal = document.getElementById('marketing_drilldown_modal');
                    const title = document.getElementById('marketing_modal_title');
                    const container = document.getElementById('marketing_modal_rows');

                    title.textContent = `Patient Details | ${entityName}`;
                    container.innerHTML = '';

                    if (details.length === 0) {
                        container.innerHTML = `
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-400 text-sm">
                                    No patients records connected.
                                </td>
                            </tr>
                        `;
                    } else {
                        details.forEach(item => {
                            const tr = document.createElement('tr');
                            tr.className = 'hover:bg-gray-50 border-b border-gray-50';
                            
                            const currencyStr = '$ ' + item.production.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            
                            tr.innerHTML = `
                                <td class="py-3 px-4 text-gray-800 font-bold">${item.pat_num}</td>
                                <td class="py-3 px-4 text-gray-900 font-semibold">${item.name}</td>
                                <td class="py-3 px-4 text-gray-700">${item.phone || '—'}</td>
                                <td class="py-3 px-4 text-gray-500">${item.email || '—'}</td>
                                <td class="py-3 px-4 text-right font-medium text-gray-900">${currencyStr}</td>
                            `;
                            container.appendChild(tr);
                        });
                    }
                    modal.classList.remove('hidden');
                }

                function closeMarketingDrilldown() {
                    const modal = document.getElementById('marketing_drilldown_modal');
                    modal.classList.add('hidden');
                }
            </script>
        @endif

    @else
        {{-- Patient Analysis --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Patient Gender --}}
            <div class="bg-white border border-slate-200 rounded shadow-sm p-4 flex flex-col h-[400px]">
                <div class="mb-4">
                    <h3 class="text-[15px] font-bold text-gray-900 leading-tight">Patient Gender</h3>
                </div>
                <div class="relative w-full flex-1 min-h-[300px]">
                    <canvas id="pa_gender_chart" class="absolute inset-0 w-full h-full"></canvas>
                </div>
            </div>

            {{-- Age Brackets Table --}}
            <div class="bg-white border border-slate-200 rounded shadow-sm overflow-hidden flex flex-col h-[400px]">
                <div class="flex items-center justify-between p-4 border-b border-slate-100 bg-white">
                    <h3 class="text-[15px] font-bold text-gray-900 leading-tight">Age Brackets</h3>
                </div>
                <div class="flex-1 overflow-auto bg-white pt-2">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-white text-gray-900 sticky top-0 z-10">
                            <tr>
                                <th class="pb-1 px-4 font-extrabold text-[12px] border-b border-gray-100 align-bottom border-r"
                                    rowspan="2">Ages</th>
                                <th class="pb-1 px-2 font-extrabold text-[11px] text-center border-b border-gray-100 border-r"
                                    colspan="2">18 months</th>
                                <th class="pb-1 px-2 font-extrabold text-[11px] text-center border-b border-gray-100"
                                    colspan="2">24 months</th>
                            </tr>
                            <tr>
                                <th class="py-1 px-2 font-extrabold text-[10px] text-center border-b border-gray-100"># of
                                    Active</th>
                                <th
                                    class="py-1 px-2 font-extrabold text-[10px] text-center border-b border-gray-100 border-r">
                                    % of TTL</th>
                                <th class="py-1 px-2 font-extrabold text-[10px] text-center border-b border-gray-100"># of
                                    Active</th>
                                <th class="py-1 px-2 font-extrabold text-[10px] text-center border-b border-gray-100">% of
                                    TTL</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 font-semibold text-xs border-b border-gray-100">
                            @php
                                $total18 = max(1, array_sum($spec['ages18'] ?? []));
                                $total24 = max(1, array_sum($spec['ages24'] ?? []));
                            @endphp
                            @foreach(($spec['ages24'] ?? []) as $bracket => $count24)
                                @php $count18 = $spec['ages18'][$bracket] ?? 0; @endphp
                                <tr class="hover:bg-gray-50/80 transition-colors border-b border-gray-50">
                                    <td class="py-1.5 px-4 text-gray-900 font-extrabold border-r border-gray-50">{{ $bracket }}
                                    </td>
                                    <td class="py-1.5 px-2 text-center text-gray-700">{{ number_format($count18) }}</td>
                                    <td class="py-1.5 px-2 text-center text-gray-500 border-r border-gray-50">
                                        {{ number_format(($count18 / $total18) * 100, 1) }}%</td>
                                    <td class="py-1.5 px-2 text-center text-gray-700">{{ number_format($count24) }}</td>
                                    <td class="py-1.5 px-2 text-center text-gray-500">
                                        {{ number_format(($count24 / $total24) * 100, 1) }}%</td>
                                </tr>
                            @endforeach
                            <tr class="bg-gray-50/20 border-t-2 border-gray-100">
                                <td class="py-2.5 px-4 text-gray-900 font-extrabold border-r border-gray-50">Total</td>
                                <td class="py-2.5 px-2 text-center text-gray-600 font-medium">
                                    {{ number_format(array_sum($spec['ages18'] ?? [])) }}</td>
                                <td class="py-2.5 px-2 text-center text-gray-600 font-medium border-r border-gray-50">100.0%
                                </td>
                                <td class="py-2.5 px-2 text-center text-gray-600 font-medium">
                                    {{ number_format(array_sum($spec['ages24'] ?? [])) }}</td>
                                <td class="py-2.5 px-2 text-center text-gray-600 font-medium">100.0%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- New Patient Seen vs Goal --}}
            <div class="bg-white border border-slate-200 rounded shadow-sm p-5 flex flex-col h-[400px]">
                <div class="mb-4 text-left">
                    <h3 class="text-[15px] font-bold text-gray-900 leading-tight">New Patient Seen vs Goal</h3>
                </div>

                <div class="flex-1 flex flex-col justify-center gap-10 px-2 mt-4">
                    @php
                        $mtd = $spec['goals']['mtd'] ?? ['actual' => 0, 'goal' => 40];
                        $ytd = $spec['goals']['ytd'] ?? ['actual' => 0, 'goal' => 200];

                        $maxMtd = max($mtd['actual'], $mtd['goal'], 1);
                        $wMtdAct = ($mtd['actual'] / $maxMtd) * 100;
                        $wMtdGoal = ($mtd['goal'] / $maxMtd) * 100;

                        $maxYtd = max($ytd['actual'], $ytd['goal'], 1);
                        $wYtdAct = ($ytd['actual'] / $maxYtd) * 100;
                        $wYtdGoal = ($ytd['goal'] / $maxYtd) * 100;
                    @endphp

                    {{-- Month to Date --}}
                    <div>
                        <p class="text-[12px] font-bold text-gray-400 mb-3 ml-14">Month to Date</p>
                        <div class="flex items-center mb-1.5 relative">
                            <span
                                class="text-[11px] font-extrabold w-12 flex-shrink-0 text-gray-900 text-right pr-2">Actual</span>
                            <div class="flex-1 h-[14px] flex relative">
                                <div class="bg-[#6ee7b7] h-full z-10" style="width: {{ max(1, $wMtdAct) }}%"></div>
                                <span
                                    class="text-[10px] font-extrabold text-gray-800 absolute right-[-20px] top-[1px] transform translate-x-full">{{ number_format($mtd['actual']) }}</span>
                            </div>
                        </div>
                        <div class="flex items-center relative">
                            <span
                                class="text-[11px] font-extrabold w-12 flex-shrink-0 text-gray-900 text-right pr-2">Goal</span>
                            <div class="flex-1 h-[14px] flex relative">
                                <div class="bg-[#a78bfa] h-full z-10" style="width: {{ max(1, $wMtdGoal) }}%"></div>
                                <span
                                    class="text-[10px] font-extrabold text-gray-800 absolute right-[-20px] top-[1px] transform translate-x-full">{{ number_format($mtd['goal']) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="w-full h-px bg-gray-100 my-1"></div>

                    {{-- Year to Date --}}
                    <div>
                        <p class="text-[12px] font-bold text-gray-400 mb-3 ml-14">Year to Date</p>
                        <div class="flex items-center mb-1.5 relative">
                            <span
                                class="text-[11px] font-extrabold w-12 flex-shrink-0 text-gray-900 text-right pr-2">Actual</span>
                            <div class="flex-1 h-[14px] flex relative">
                                <div class="bg-[#6ee7b7] h-full z-10" style="width: {{ max(1, $wYtdAct) }}%"></div>
                                <span
                                    class="text-[10px] font-extrabold text-gray-800 absolute right-[-20px] top-[1px] transform translate-x-full">{{ number_format($ytd['actual']) }}</span>
                            </div>
                        </div>
                        <div class="flex items-center relative">
                            <span
                                class="text-[11px] font-extrabold w-12 flex-shrink-0 text-gray-900 text-right pr-2">Goal</span>
                            <div class="flex-1 h-[14px] flex relative">
                                <div class="bg-[#a78bfa] h-full z-10" style="width: {{ max(1, $wYtdGoal) }}%"></div>
                                <span
                                    class="text-[10px] font-extrabold text-gray-800 absolute right-[-20px] top-[1px] transform translate-x-full">{{ number_format($ytd['goal']) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Volume Trend Row --}}
        <div class="mt-6 bg-white border border-slate-200 rounded shadow-sm p-6 flex flex-col h-[400px]">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-[16px] font-bold text-gray-900 leading-tight">New Patient Seen Volume</h3>
                <div class="flex border border-gray-200 rounded overflow-hidden shadow-sm">
                    <button
                        class="px-4 py-1.5 text-xs text-gray-400 font-semibold border-r border-gray-200 bg-white hover:bg-gray-50">Daily</button>
                    <button
                        class="px-4 py-1.5 text-xs text-gray-400 font-semibold border-r border-gray-200 bg-white hover:bg-gray-50">Weekly</button>
                    <button
                        class="px-4 py-1.5 text-xs text-gray-800 font-medium bg-green-50 shadow-inner ring-1 ring-green-100 ring-inset">Monthly</button>
                </div>
            </div>
            <div class="relative w-full flex-1">
                <canvas id="pa_volume_chart" class="absolute inset-0 w-full h-full"></canvas>
            </div>
        </div>
    @endif
</div>

@if ($subtab === 'patient-analysis')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') {
                console.warn('Waiting for Chart.js...');
                return;
            }

            // Gender Donut
            new Chart(document.getElementById('pa_gender_chart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Female', 'Male', 'Other'],
                    datasets: [{
                        data: [
                                {{ $spec['gender']['Female'] ?? 0 }},
                                {{ $spec['gender']['Male'] ?? 0 }},
                            {{ $spec['gender']['Other'] ?? 0 }}
                        ],
                        backgroundColor: ['#6ee7b7', '#a78bfa', '#38bdf8'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    layout: { padding: 20 },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 10, usePointStyle: true, font: { size: 10, weight: 'bold' } }
                        }
                    }
                }
            });

            // Volume Line Chart
            new Chart(document.getElementById('pa_volume_chart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: {!! json_encode(array_keys($spec['volume'] ?? [])) !!},
                    datasets: [{
                        label: 'New Patients',
                        data: {!! json_encode(array_values($spec['volume'] ?? [])) !!},
                        borderColor: '#6ee7b7',
                        backgroundColor: '#6ee7b7',
                        borderWidth: 2,
                        tension: 0,
                        pointRadius: 4,
                        pointBackgroundColor: '#6ee7b7'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            align: 'start',
                            labels: { boxWidth: 10, usePointStyle: true, font: { size: 11, weight: 'bold' }, color: '#111827' }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9', drawBorder: false },
                            ticks: { font: { size: 10 }, stepSize: 5 }
                        },
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: { font: { size: 10 } }
                        }
                    }
                }
            });
        });
    </script>
@else
    <script>
        (function () {
            // Generate randomized brand colors for donut slices.
            function generatePalette(count) {
                const colors = [
                    '#6ee7b7', '#34d399', '#10b981', '#059669', '#047857',
                    '#93c5fd', '#60a5fa', '#3b82f6', '#2563eb', '#1d4ed8'
                ];
                return colors.slice(0, count);
            }

            // Fallback waiting for Chart.js to load exactly like services tab
            function initializeMarketingCharts() {
                if (typeof Chart === 'undefined') {
                    setTimeout(initializeMarketingCharts, 100);
                    return;
                }

                // Configuration
                const chartOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return ` ${context.label}: ${context.raw} New Patients`;
                                }
                            }
                        }
                    }
                };

                // Data Payloads
                var referralsKeys = {!! json_encode(array_keys($spec['top_referrals'] ?? [])) !!};
                var referralsData = {!! json_encode(array_values($spec['top_referrals'] ?? [])) !!};

                var payorsKeys = {!! json_encode(array_keys($spec['top_payors'] ?? [])) !!};
                var payorsData = {!! json_encode(array_values($spec['top_payors'] ?? [])) !!};

                var employersKeys = {!! json_encode(array_keys($spec['top_employers'] ?? [])) !!};
                var employersData = {!! json_encode(array_values($spec['top_employers'] ?? [])) !!};

                if (referralsKeys.length === 0) { referralsKeys = ['No Data']; referralsData = [1]; }
                if (payorsKeys.length === 0) { payorsKeys = ['No Data']; payorsData = [1]; }
                if (employersKeys.length === 0) { employersKeys = ['No Data']; employersData = [1]; }

                if (document.getElementById('marketing_referrals_chart')) {
                    new Chart(document.getElementById('marketing_referrals_chart').getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: referralsKeys,
                            datasets: [{
                                data: referralsData,
                                backgroundColor: generatePalette(referralsKeys.length).reverse(),
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: chartOptions
                    });
                }

                if (document.getElementById('marketing_payors_chart')) {
                    new Chart(document.getElementById('marketing_payors_chart').getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: payorsKeys,
                            datasets: [{
                                data: payorsData,
                                backgroundColor: generatePalette(payorsKeys.length).reverse(),
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: chartOptions
                    });
                }

                if (document.getElementById('marketing_employers_chart')) {
                    new Chart(document.getElementById('marketing_employers_chart').getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: employersKeys,
                            datasets: [{
                                data: employersData,
                                backgroundColor: generatePalette(employersKeys.length).reverse(),
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: chartOptions
                    });
                }
            }

            initializeMarketingCharts();
        })();
    </script>
@endif