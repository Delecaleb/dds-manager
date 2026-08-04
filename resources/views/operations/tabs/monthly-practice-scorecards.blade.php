@php
    $tab = 'monthly-practice-scorecards';
    $subtab = $activeSubtab ?? 'default';
@endphp

<div class="bg-transparent space-y-5">
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
    </div>

    <div class="mt-4">
        <!-- Notification Banner -->
        <div class="mb-4 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        Please note, the provider-specific metrics ignore the Line of Business filter.
                    </p>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="bg-white relative p-6 shadow rounded-md border border-gray-200">
            <!-- Header Controls -->
            <div class="flex justify-between items-center mb-6">
                <!-- Legend -->
                <div class="flex items-center space-x-4 text-xs font-semibold text-gray-600">
                    <div class="flex items-center space-x-1.5"><span
                            class="w-3 h-3 rounded-full bg-[#e8f5e9]"></span><span>Top 20%</span></div>
                    <div class="flex items-center space-x-1.5"><span
                            class="w-3 h-3 rounded-full bg-[#fff8e1]"></span><span>Mid Tier</span></div>
                    <div class="flex items-center space-x-1.5"><span
                            class="w-3 h-3 rounded-full bg-[#ffebee]"></span><span>Bottom 20%</span></div>
                </div>

                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <input type="text"
                            class="pl-9 pr-4 py-1.5 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Search metrics...">
                    </div>
                    <button
                        class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded text-sm text-gray-700 font-medium whitespace-nowrap transition-colors">Export
                        CSV</button>
                </div>
            </div>

            <!-- Table Matrix -->
            <div class="overflow-x-auto overflow-y-auto" style="max-height: 555px;">
                <table class="dds-table dds-sortable w-full text-left border-collapse min-w-max">
                    <thead class="sticky top-0 z-40 bg-white">
                        <tr>
                            <th
                                class="py-2.5 px-4 font-bold text-gray-900 border-b border-gray-200 bg-gray-50 sticky left-0 z-50 min-w-[200px] shadow-[1px_0_0_0_#e5e7eb]">
                                Entities</th>
                            @foreach ($spec['columns'] as $month)
                                <th
                                    class="py-2.5 px-4 font-bold text-center text-gray-900 border-b border-gray-200 bg-gray-50 uppercase text-[11px] whitespace-nowrap">
                                    {{ $month }}
                                </th>
                            @endforeach
                            <th
                                class="py-2.5 px-4 font-bold text-center text-gray-900 border-b border-gray-200 bg-gray-50 uppercase text-[11px] border-l whitespace-nowrap">
                                Diff Vs Last Year</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm align-top">
                        @foreach ($spec['rows'] as $row)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td
                                    class="py-2 px-4 font-semibold text-gray-900 bg-white sticky left-0 z-30 shadow-[1px_0_0_0_#f3f4f6]">
                                    {{ $row['entity'] }}
                                </td>

                                @foreach ($row['data'] as $col)
                                    @php
                                        // Colors mapping structurally via mock array bounds. 
                                        $bgClass = match ($col['tier']) {
                                            'top' => 'bg-[#e8f5e9] text-gray-900',
                                            'mid' => 'bg-[#fff8e1] text-gray-900',
                                            'bottom' => 'bg-[#ffebee] text-gray-900',
                                            default => 'bg-white'
                                        };

                                        // Format actual value
                                        if ($col['is_currency']) {
                                            $valStr = '$ ' . number_format($col['raw_val'], 2);
                                            $lyStr = '$ ' . number_format($col['raw_ly'], 2);
                                            $diffStr = '$ ' . number_format($col['diff'], 2);
                                        } elseif ($col['is_percent']) {
                                            $valStr = number_format($col['raw_val'] * 100, 2) . '%';
                                            $lyStr = number_format($col['raw_ly'] * 100, 2) . '%';
                                            $diffStr = number_format($col['diff'] * 100, 2) . '%';
                                        } else {
                                            $valStr = number_format($col['raw_val']);
                                            $lyStr = number_format($col['raw_ly']);
                                            $diffStr = number_format($col['diff']);
                                        }

                                        $pctStr = number_format($col['percent_diff'] * 100, 2) . '%';

                                        $arrow = $col['diff'] >= 0 ? '<span class="text-green-500 mx-1">↑</span>' : '<span class="text-red-500 mx-1">↓</span>';
                                    @endphp

                                    <td class="py-2 px-3 text-right tabular-nums whitespace-nowrap {{ $bgClass }}" data-order="{{ (float) ($col['raw_val'] ?? 0) }}">
                                        @if ($activeSubtab === 'default')
                                            {{ $valStr }}
                                        @elseif ($activeSubtab === 'diff-last-year')
                                            {{ $valStr }}<br>{!! $arrow !!} {{ $diffStr }}
                                        @elseif ($activeSubtab === 'percent-diff-last-year')
                                            {{ $valStr }}<br>{!! $arrow !!} {{ $pctStr }}
                                        @endif
                                    </td>
                                @endforeach

                                <td
                                    class="py-2 px-4 text-right tabular-nums font-medium text-gray-700 bg-gray-50/50 border-l whitespace-nowrap">
                                    @php
                                        if ($row['data'][0]['is_currency']) {
                                            $totDiff = '$ ' . number_format($row['summary']['diff'], 2);
                                        } elseif ($row['data'][0]['is_percent']) {
                                            $totDiff = number_format($row['summary']['diff'] * 100, 2) . '%';
                                        } else {
                                            $totDiff = number_format($row['summary']['diff']);
                                        }
                                        $totArrow = $row['summary']['diff'] >= 0 ? '<span class="text-green-500 mx-1">↑</span>' : '<span class="text-red-500 mx-1">↓</span>';
                                    @endphp

                                    @if ($activeSubtab === 'default')
                                        {{ $totDiff }}
                                    @elseif ($activeSubtab === 'diff-last-year')
                                        {!! $totArrow !!} {{ $totDiff }}
                                    @elseif ($activeSubtab === 'percent-diff-last-year')
                                        {!! $totArrow !!} {{ number_format($row['summary']['percent_diff'] * 100, 2) }}%
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot
                        class="sticky bottom-0 z-40 bg-gray-50 shadow-[0_-1px_0_0_#e5e7eb,0_1px_0_0_#e5e7eb] font-bold text-gray-800 text-[13px]">
                        <!-- Average Row -->
                        <tr>
                            <td class="py-2.5 px-4 text-right sticky left-0 z-50 bg-gray-50 shadow-[1px_0_0_0_#e5e7eb]">
                                Average:</td>
                            @foreach ($spec['footer_avg'] as $avg)
                                <td class="py-2.5 px-4 text-right tabular-nums border-t border-gray-200">
                                    {{ number_format($avg, 2) }}
                                </td>
                            @endforeach
                            <td class="py-2.5 px-4 text-right border-l border-t border-gray-200 bg-gray-100"></td>
                        </tr>
                        <!-- Total Row -->
                        <tr>
                            <td class="py-2.5 px-4 text-right sticky left-0 z-50 bg-gray-50 shadow-[1px_0_0_0_#e5e7eb]">
                                Total:</td>
                            @foreach ($spec['footer_total'] as $tot)
                                <td class="py-2.5 px-4 text-right tabular-nums border-t border-gray-200 bg-gray-100">
                                    {{ number_format($tot, 2) }}
                                </td>
                            @endforeach
                            <td class="py-2.5 px-4 text-right border-l border-t border-gray-200 bg-gray-200"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>