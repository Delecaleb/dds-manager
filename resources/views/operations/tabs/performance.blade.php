<div class="bg-transparent space-y-5">
    <!-- Performance KPIs Dashboard Section -->
    <div class="bg-white rounded shadow-sm border border-slate-200 mt-4 p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            @foreach ($spec['performance_kpis'] ?? [] as $kpi)
                @php
                    $actual = (float) ($kpi['actual'] ?? 0);
                    $goal = (float) ($kpi['goal'] ?? 1);
                    $isNegative = $actual < 0;
                    $absActual = abs($actual);

                    if ($kpi['type'] === 'currency') {
                        $formattedActual = $isNegative 
                            ? '$ (' . number_format($absActual, 2) . ')' 
                            : '$ ' . number_format($actual, 2);
                        $formattedGoal = '$ ' . number_format($goal, 2);
                    } else {
                        $formattedActual = $isNegative 
                            ? '(' . number_format((int) $absActual) . ')' 
                            : number_format((int) $actual);
                        $formattedGoal = number_format((int) $goal);
                    }

                    $textColor = $isNegative ? 'text-red-600' : 'text-gray-800';
                @endphp

                <div class="flex flex-col">
                    <h3 class="text-[13px] font-bold text-gray-900 border-b pb-2 mb-3">{{ $kpi['label'] }}</h3>

                    <div class="space-y-2">
                        @if ($isNegative)
                            @php
                                $zeroPos = 30; // Zero axis line at 30% from left
                                $negPct = min(30, max(5, ($absActual / max($goal, 1)) * 70));
                            @endphp

                            <!-- Actual Row (Negative: Bar grows LEFT from zero axis) -->
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-semibold text-gray-800 w-10 shrink-0 text-right">Actual</span>
                                <div class="flex-1 bg-gray-100 rounded-sm h-6 relative flex items-center overflow-hidden">
                                    <!-- Zero Axis Line -->
                                    <div class="absolute top-0 bottom-0 w-0.5 bg-gray-400 z-10" style="left: {{ $zeroPos }}%;"></div>
                                    
                                    <!-- Red Negative Bar extending LEFT -->
                                    <div class="bg-[#f87171] h-full transition-all duration-300 absolute" 
                                         style="right: {{ 100 - $zeroPos }}%; width: {{ $negPct }}%;"></div>
                                </div>
                                <span class="text-xs font-bold {{ $textColor }} pt-[2px] w-24 shrink-0">
                                    {{ $formattedActual }}
                                </span>
                            </div>

                            <!-- Goal Row (Positive: Bar grows RIGHT from zero axis) -->
                            <div class="flex items-center gap-3 mt-1">
                                <span class="text-xs font-bold text-gray-800 w-10 shrink-0 text-right">Goal</span>
                                <div class="flex-1 bg-gray-100 rounded-sm h-6 relative flex items-center overflow-hidden">
                                    <!-- Zero Axis Line -->
                                    <div class="absolute top-0 bottom-0 w-0.5 bg-gray-400 z-10" style="left: {{ $zeroPos }}%;"></div>
                                    
                                    <!-- Purple Goal Bar extending RIGHT -->
                                    <div class="bg-[#a855f7] h-full transition-all duration-300 absolute flex items-center justify-end px-2" 
                                         style="left: {{ $zeroPos }}%; width: {{ 100 - $zeroPos }}%;">
                                        <span class="text-xs font-bold text-white tracking-widest pt-[1px]">
                                            {{ $formattedGoal }}
                                        </span>
                                    </div>
                                </div>
                                <span class="w-24 shrink-0"></span> <!-- Placeholder for alignment -->
                            </div>
                        @else
                            @php
                                $pct = $goal > 0 ? min(100, max(0, ($actual / $goal) * 100)) : 0;
                            @endphp

                            <!-- Actual Row (Positive: Bar grows RIGHT from 0) -->
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-semibold text-gray-800 w-10 shrink-0 text-right">Actual</span>
                                <div class="flex-1 bg-gray-100 rounded-sm overflow-hidden h-6 relative">
                                    <div class="bg-[#6ee7b7] h-full transition-all duration-300" style="width: {{ $pct }}%;"></div>
                                </div>
                                <span class="text-xs font-bold {{ $textColor }} pt-[2px] w-24 shrink-0">
                                    {{ $formattedActual }}
                                </span>
                            </div>

                            <!-- Goal Row -->
                            <div class="flex items-center gap-3 mt-1">
                                <span class="text-xs font-bold text-gray-800 w-10 shrink-0 text-right">Goal</span>
                                <div class="flex-1 rounded-sm overflow-hidden h-6">
                                    <div class="bg-[#a855f7] h-full w-full flex items-center justify-end px-2">
                                        <span class="text-xs font-bold text-white tracking-widest pt-[1px]">
                                            {{ $formattedGoal }}
                                        </span>
                                    </div>
                                </div>
                                <span class="w-24 shrink-0"></span> <!-- Placeholder for alignment -->
                            </div>
                        @endif
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