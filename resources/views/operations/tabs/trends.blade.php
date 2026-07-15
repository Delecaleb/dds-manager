<div class="bg-transparent space-y-5">
    {{-- Top Filters Dropdowns --}}
    <div class="flex flex-wrap items-center gap-3">
        <select data-ops-filter="metric"
            class="border border-slate-300 rounded text-sm px-3 py-1.5 min-w-[200px] focus:border-[#00bfa5] focus:outline-none">
            <option value="production">By Office - Production</option>
            <option value="collection">By Office - Collection</option>
            <option value="visits">By Office - Visits</option>
        </select>
        <select data-ops-filter="lob"
            class="border border-slate-300 rounded text-sm px-3 py-1.5 min-w-[200px] focus:border-[#00bfa5] focus:outline-none">
            <option value="">Line of Business All</option>
        </select>
    </div>

    {{-- Subtabs Wrapper --}}
    @if (!empty($subtabs))
        <ul class="flex border-b border-slate-200 mt-4 gap-1">
            @foreach ($subtabs as $s => $label)
                <a href="#" data-ops-subtab="{{ $s }}" class="text-xs font-bold px-5 py-2.5 rounded-t tracking-wide whitespace-nowrap border break-words transition-colors
                                  {{ $s === ($activeSubtab ?? 'default')
                    ? 'bg-white text-slate-800 border-x-slate-200 border-t-slate-200 border-b-white -mb-px relative z-10'
                    : 'bg-slate-50 text-slate-400 border-transparent hover:text-slate-600' }}">
                    {{ $label }}
                </a>
            @endforeach
        </ul>
    @endif

    <div class="bg-white border rounded shadow-sm {{ empty($subtabs) ? '' : 'rounded-tl-none -mt-px relative z-0' }}">

        {{-- Info Box --}}
        <div class="p-4 bg-[#e0f0fa] m-4 rounded hidden sm:flex items-center gap-3 border border-[#bae0f5]">
            <div class="text-[#3b82f6]">
                <i data-lucide="info" class="w-5 h-5 opacity-90"></i>
            </div>
            <div class="text-[13.5px] text-[#0c6b9e] leading-snug">
                BYO Production: The total production presented by office (BYO) based on a given date range.<br>
                <span class="text-[#5ba1c9]">Please note, the provider-specific metrics ignore the Line of Business
                    filter.</span>
            </div>
            <button class="ml-auto text-[#85bfe0] hover:text-[#0c6b9e]">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        {{-- Legend --}}
        <div class="flex items-center px-6 py-2 gap-4 text-xs font-bold text-slate-800">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-[#69e4bf] rounded-sm"></div>
                {{ count($clinics ?? []) === 1 ? 'Office ' . current($clinics) : 'Selected Offices' }}
            </div>
            @if(($activeSubtab ?? 'default') === 'compare')
                <div class="flex items-center gap-2 ml-4">
                    <div class="w-3 h-3 bg-[#cbd5e1] rounded-sm"></div>
                    Previous Year
                </div>
            @endif
        </div>

        {{-- Chart container --}}
        <div class="p-6 pt-2 pb-10 w-full relative" style="height: 480px;">
            <canvas id="opsTrendsChart"></canvas>
        </div>

    </div>

    {{-- Datatable wrapper --}}
    <div class="mt-8">
        @include('operations.tabs.table', ['spec' => $spec, 'subtabs' => [], 'tab' => $tab, 'activeSubtab' => $activeSubtab ?? 'default'])
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function () {
        // Slight delay ensures the custom script block injection evaluates and the canvas is fully mounted
        setTimeout(() => {
            const ctx = document.getElementById('opsTrendsChart');
            if (!ctx) return;

            const labels = @json($spec['labels'] ?? []);
            const dataCurrent = @json($spec['current'] ?? []);
            const dataLast = @json($spec['last'] ?? []);
            const isCompare = "{{ $activeSubtab ?? 'default' }}" === 'compare';

            const datasets = [
                {
                    label: 'Selected Range',
                    data: dataCurrent,
                    borderColor: '#69e4bf',
                    backgroundColor: 'rgba(105, 228, 191, 0.45)', // Fill overlay identical to user's screen
                    borderWidth: 2,
                    pointBackgroundColor: '#69e4bf',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0
                }
            ];

            if (isCompare) {
                datasets.push({
                    label: 'Previous Year',
                    data: dataLast,
                    borderColor: '#cbd5e1',
                    backgroundColor: 'rgba(203, 213, 225, 0.1)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointBackgroundColor: '#cbd5e1',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: false,
                    tension: 0
                });
            }

            // Wipe old chart safely if it persists in active memory over hot-swaps
            if (window._opsTrendsChartInstance) {
                window._opsTrendsChartInstance.destroy();
            }

            window._opsTrendsChartInstance = new Chart(ctx, {
                type: 'line',
                data: { labels, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#fff',
                            titleColor: '#334155',
                            bodyColor: '#334155',
                            borderColor: '#e2e8f0',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: true,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9', borderDash: [3, 3], drawBorder: false },
                            ticks: {
                                callback: function (v) { return v >= 1000 ? (v / 1000) + 'k' : v; },
                                font: { size: 11, family: 'Inter, sans-serif' },
                                color: '#64748b',
                                padding: 10
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { size: 10, family: 'Inter, sans-serif' },
                                color: '#64748b',
                                padding: 10
                            }
                        }
                    }
                }
            });
        }, 100);
    })();
</script>