<div class="bg-transparent space-y-5">
    <!-- Performance KPIs Dashboard Section -->
    <div class="bg-white rounded shadow-sm border border-slate-200 mt-4 p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            @foreach ($spec['performance_kpis'] ?? [] as $kpi)
                <div class="flex flex-col">
                    <h3 class="text-[13px] font-bold text-gray-900 border-b pb-2 mb-3">{{ $kpi['label'] }}</h3>

                    @php
                        $actual = (float) ($kpi['actual'] ?? 0);
                        $goal = (float) ($kpi['goal'] ?? 1);
                        $pct = $goal > 0 ? min(100, max(0, ($actual / $goal) * 100)) : 0; // Cap bar at 100% physically
                    @endphp

                    <div class="space-y-2">
                        <!-- Actual Row -->
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-semibold text-gray-800 w-10 shrink-0 text-right">Actual</span>
                            <div class="flex-1 bg-gray-100 rounded-sm overflow-hidden h-6 rtl:flex-row-reverse relative">
                                <div class="bg-[#6ee7b7] h-full transition-all duration-300" style="width: {{ $pct }}%;">
                                </div>
                                <!-- Text next to the bar (float right inside container if > than space else next to it? Let's just overlay or append) -->
                            </div>
                            <span class="text-xs font-bold text-gray-800 pt-[2px] w-20 shrink-0">
                                {{ $kpi['type'] === 'currency' ? '$ ' . number_format($actual, 2) : number_format($actual) }}
                            </span>
                        </div>

                        <!-- Goal Row -->
                        <div class="flex items-center gap-3 mt-1">
                            <span class="text-xs font-bold text-gray-800 w-10 shrink-0 text-right">Goal</span>
                            <div class="flex-1 rounded-sm overflow-hidden h-6">
                                <div class="bg-[#a855f7] h-full w-full flex items-center justify-end px-2">
                                    <span class="text-xs font-bold text-white tracking-widest pt-[1px]">
                                        {{ $kpi['type'] === 'currency' ? '$ ' . number_format($goal, 2) : number_format($goal) }}
                                    </span>
                                </div>
                            </div>
                            <span class="w-20 shrink-0"></span> <!-- Placeholder for alignment -->
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Table Section -->
    <div class="mt-4">
        @include('operations.tabs.table', ['spec' => $spec, 'subtabs' => $subtabs ?? [], 'tab' => $tab, 'activeSubtab' => $activeSubtab ?? 'default'])
    </div>
</div>