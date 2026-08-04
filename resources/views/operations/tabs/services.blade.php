<div class="bg-transparent space-y-5">
    
    {{-- Top Filters Dropdowns --}}
    <div class="flex flex-wrap items-center gap-3">
        <select data-ops-filter="provider" class="border border-slate-300 rounded text-sm px-3 py-1.5 min-w-[200px] focus:border-[#00bfa5] focus:outline-none">
            <option value="">Providers All</option>
        </select>
        <select data-ops-filter="payor" class="border border-slate-300 rounded text-sm px-3 py-1.5 min-w-[200px] focus:border-[#00bfa5] focus:outline-none">
            <option value="">Payors All</option>
        </select>
    </div>

    {{-- 3 Column layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Top 10 Services --}}
        <div class="col-span-1">
            <div class="flex items-center mb-3">
                <h2 class="text-sm font-bold text-black tracking-wide">Top 10 Services <span class="text-slate-400 font-normal ml-1">| Count</span></h2>
            </div>
            <div class="bg-white border text-sm border-slate-200/60 rounded shadow-sm min-h-[420px] max-h-[800px] overflow-y-auto p-6 flex flex-col">
                <div class="relative w-full aspect-square max-w-[260px] mx-auto mb-6 h-[260px]">
                    <canvas id="topServicesDonut"></canvas>
                </div>
                
                {{-- Custom Legend --}}
                <div class="flex flex-col gap-2.5 w-full mt-2">
                    @forelse($spec['top_services'] ?? [] as $i => $srv)
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 max-w-[85%]">
                            <span id="srv-legend-{{ $i }}" class="w-3.5 h-3.5 flex-shrink-0"></span>
                            <span class="text-black font-bold tracking-wide truncate" title="{{ $srv['label'] }}">{{ $srv['label'] }}</span>
                        </div>
                        <span class="text-slate-400 font-bold ml-2">{{ number_format($srv['count']) }}</span>
                    </div>
                    @empty
                    <div class="text-center text-slate-400 py-12">No services performed in this range.</div>
                    @endforelse
                </div>
            </div>

<script>
(function() {
    const rawData = @json($spec['top_services'] ?? []);
    if (!rawData.length) return;

    // Hardcoded palette precisely matching the original screenshot
    const palette = ['#6ee7b7','#a855f7','#4ade80','#fb7185','#fcd34d','#14b8a6','#a21caf','#3b82f6','#dc2626','#fbbf24'];
    
    rawData.forEach((d, i) => {
        const sq = document.getElementById('srv-legend-' + i);
        if(sq) sq.style.backgroundColor = palette[i % palette.length];
    });

    function renderDonut() {
        if (typeof Chart === 'undefined') {
            setTimeout(renderDonut, 50);
            return;
        }

        const canvas = document.getElementById('topServicesDonut');
        if (!canvas) return;
        
        if (window.opsServicesChart) {
            window.opsServicesChart.destroy();
        }

        const ctx = canvas.getContext('2d');
        window.opsServicesChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: rawData.map(d => d.count),
                    backgroundColor: palette,
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) { return ' ' + rawData[ctx.dataIndex].label + ': ' + ctx.raw; }
                        }
                    }
                }
            }
        });
    }
    
    renderDonut();
})();
</script>
        </div>

        {{-- New Patient Visit vs Goal --}}
        <div class="col-span-1">
            <div class="flex items-center mb-3">
                <h2 class="text-sm font-bold text-black tracking-wide">New Patient Visit vs Goal</h2>
            </div>
            <div class="bg-white border border-slate-200/60 rounded shadow-sm min-h-[420px] flex flex-col items-center justify-center p-6 gap-y-12">
                
                {{-- Month To Date --}}
                <div class="w-full text-center">
                    @php 
                        $mtdGoal = $spec['npt_mtd']['goal'] ?? 0;
                        $mtdVisits = $spec['npt_mtd']['visits'] ?? 0;
                        $mtdPct = $mtdGoal > 0 ? round(($mtdVisits / $mtdGoal) * 100, 1) : 0;

                        // Mock heights for CSS visuals
                        $mtdMax = max(1, $mtdGoal, $mtdVisits);
                        $mtdVHeight = ($mtdVisits / $mtdMax) * 100;
                        $mtdGHeight = ($mtdGoal / $mtdMax) * 100;
                    @endphp
                    <h3 class="text-slate-600 text-sm font-medium mb-6">
                        @if($mtdGoal == 0) N/A Month To Date @else {{ $mtdPct }}% Month To Date @endif
                    </h3>
                    
                    <div class="flex items-end justify-center h-32 gap-10 border-b border-l border-slate-200 p-4 pt-0 mx-auto w-3/4 max-w-[200px]">
                        <div class="relative flex flex-col items-center justify-end h-full w-10 group">
                            <div class="w-full bg-[#bfdbfe] border-t-2 border-blue-400 transition-all" style="height: {{ max(10, $mtdVHeight) }}%;"></div>
                            <span class="absolute -bottom-6 text-[10px] text-slate-500 whitespace-nowrap -left-6">New Visits</span>
                            <span class="absolute -top-6 font-bold text-xs text-slate-700">{{ $mtdVisits }}</span>
                        </div>
                        <div class="relative flex flex-col items-center justify-end h-full w-10 group">
                            <div class="w-full bg-slate-100 border-t-2 border-slate-300 transition-all" style="height: {{ max(10, $mtdGHeight) }}%;"></div>
                            <span class="absolute -bottom-6 text-[10px] text-slate-500 whitespace-nowrap">Goal</span>
                            <span class="absolute -top-6 font-bold text-xs text-slate-700">{{ $mtdGoal ? $mtdGoal : 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <div class="w-3/4 border-t border-slate-100"></div>

                {{-- Year To Date --}}
                <div class="w-full text-center">
                    @php 
                        $ytdGoal = $spec['npt_ytd']['goal'] ?? 0;
                        $ytdVisits = $spec['npt_ytd']['visits'] ?? 0;
                        $ytdPct = $ytdGoal > 0 ? round(($ytdVisits / $ytdGoal) * 100, 1) : 0;
                    @endphp
                    <h3 class="text-slate-600 text-sm font-medium mb-4">{{ $ytdPct }}% Year To Date</h3>
                    
                    {{-- Small YTD visual (placeholder) --}}
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden mb-2 max-w-[200px] mx-auto">
                        <div class="bg-blue-400 h-full rounded-full" style="width: {{ min(100, $ytdPct) }}%"></div>
                    </div>
                    <div class="flex justify-between items-center max-w-[200px] mx-auto text-xs">
                        <span class="text-slate-500">Visits: <b class="text-slate-700">{{ $ytdVisits }}</b></span>
                        <span class="text-slate-500">Goal: <b class="text-slate-700">{{ $ytdGoal ? $ytdGoal : 'N/A' }}</b></span>
                    </div>
                </div>

            </div>
        </div>

        {{-- Age Brackets --}}
        <div class="col-span-1">
            <div class="flex items-center mb-3">
                <h2 class="text-sm font-bold text-black tracking-wide">Age Brackets</h2>
            </div>
            <div class="bg-white border border-slate-200/60 rounded shadow-sm overflow-hidden min-h-[420px]">
                <table class="dds-table dds-sortable w-full text-left border-collapse">
                    <thead class="bg-[#f1f5f9]">
                        <tr>
                            <th class="py-3 px-4 text-xs font-bold text-[#334155]">Age</th>
                            <th class="py-3 px-4 text-xs font-bold text-[#334155] text-right border-l border-white"># of active</th>
                            <th class="py-3 px-4 text-xs font-bold text-[#334155] text-right border-l border-white">% of TTL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($spec['age_brackets']['rows'] ?? [] as $r)
                        <tr class="even:bg-slate-50 border-t border-slate-200/60">
                            <td class="py-3 px-4 text-[13px] text-slate-700 font-medium border-r border-slate-200/60">{{ $r['label'] }}</td>
                            <td class="py-3 px-4 text-[13px] text-slate-700 text-right bg-white" data-order="{{ (float) $r['count'] }}">{{ number_format($r['count']) }}</td>
                            <td class="py-3 px-4 text-[13px] text-slate-700 text-right font-medium bg-[#f8fafc] border-l border-slate-200/60" data-order="{{ (float) $r['pct'] }}">{{ number_format($r['pct'], 2) }}%</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-32 text-center text-slate-400 text-sm">No active patients found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-[#e2e8f0]">
                        <tr>
                            <td class="py-3.5 px-4 font-bold text-xs text-slate-800">Total:</td>
                            <td class="py-3.5 px-4 font-bold text-xs text-slate-800 text-right border-l border-white">{{ number_format($spec['age_brackets']['total'] ?? 0) }}</td>
                            <td class="py-3.5 px-4 font-bold text-[11px] text-slate-800 text-right border-l border-white">
                                {{ ($spec['age_brackets']['total'] ?? 0) > 0 ? '100.00%' : '0.00%' }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>

    {{-- Datatable with subtabs --}}
    <div class="mt-8">
        @include('operations.tabs.table', ['spec' => $spec, 'subtabs' => $subtabs ?? [], 'tab' => $tab, 'activeSubtab' => $activeSubtab ?? 'default'])
    </div>

</div>
