{{--
    Reusable Operations table fragment.

    Expects:
      $tab          string   current tab slug
      $subtabs      array    slug => label (empty = no subtab bar)
      $activeSubtab string
      $spec         array    ['groups' => [...], 'columns' => [...], 'rows' => [...],
                              'average' => [...], 'total' => [...]]

    Column: ['key','label','type' => text|money|percent|number,'sticky'?,'agg'?]
--}}
@php
    if (! function_exists('ops_fmt')) {
        function ops_fmt($value, string $type): string
        {
            if ($value === null)  return '—';
            if ($value === '--')  return '--';

            switch ($type) {
                case 'money':
                    $v = (float) $value;
                    if ($v == 0)  return '$ 0';
                    $abs = number_format(abs($v), 2);
                    return $v < 0 ? "$ ($abs)" : "$ $abs";
                case 'percent':
                    return number_format((float) $value, 2) . '%';
                case 'number':
                    $v = (float) $value;
                    return floor($v) == $v ? number_format($v) : number_format($v, 2);
                default:
                    return e($value);
            }
        }
    }

    // Heat-map: colour each numeric cell by where it ranks in its column.
    //   value >= 80th pct → Top 20% (green), <= 20th pct → Bottom 20% (red), else Mid (yellow).
    //   A column may set 'heat' => false to opt out, or 'heat' => 'invert' when lower is better.
    if (! function_exists('ops_heat_class')) {
        function ops_heat_class(array $heat, string $key, $value): string
        {
            if ($value === null || $value === '--' || ! isset($heat[$key])) {
                return '';
            }
            $h = $heat[$key];
            $v = (float) $value;
            [$top, $bottom, $mid] = ['bg-[#c8f7dc]', 'bg-[#fecdd3]', 'bg-[#fef3c7]'];
            if ($h['invert']) {
                [$top, $bottom] = [$bottom, $top];
            }
            if ($v >= $h['p80']) return $top;
            if ($v <= $h['p20']) return $bottom;
            return $mid;
        }
    }

    $columns = $spec['columns'];
    $groups  = $spec['groups'] ?? [];
    // Leading (ungrouped) columns the group header's empty cell must span.
    $leadSpan = max(1, count($columns) - array_sum(array_column($groups, 'span')));
    $thBase  = 'text-xs font-extrabold py-3 px-4 border-r border-gray-200 text-gray-900';
    $tdBase  = 'text-xs py-3 px-4 border-r border-gray-200';

    // Per-column percentile thresholds (needs ≥2 rows to be meaningful).
    $heat = [];
    if (count($spec['rows']) >= 2) {
        foreach ($columns as $col) {
            $type = $col['type'] ?? 'text';
            if ($type === 'text' || ! empty($col['sticky']) || ($col['heat'] ?? null) === false) {
                continue;
            }
            $vals = [];
            foreach ($spec['rows'] as $r) {
                $v = $r[$col['key']] ?? null;
                if ($v !== null && $v !== '--') {
                    $vals[] = (float) $v;
                }
            }
            if (count($vals) < 2) {
                continue;
            }
            sort($vals);
            $n = count($vals);
            $heat[$col['key']] = [
                'p20'    => $vals[(int) floor(0.2 * ($n - 1))],
                'p80'    => $vals[(int) ceil(0.8 * ($n - 1))],
                'invert' => ($col['heat'] ?? null) === 'invert',
            ];
        }
    }
@endphp

<div class="bg-white border border-slate-200 rounded shadow-sm">

    {{-- Subtab bar --}}
    @if (! empty($subtabs))
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

    {{-- Toolbar: legend + search + export --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-4">
        <div class="flex items-center gap-1.5 text-[11px] font-medium">
            <span class="bg-[#c8f7dc] text-[#1e4620] px-2.5 py-1 rounded font-bold">Top 20%</span>
            <span class="bg-[#fef3c7] text-[#78350f] px-2.5 py-1 rounded font-bold">Mid Tier</span>
            <span class="bg-[#fecdd3] text-[#9f1239] px-2.5 py-1 rounded font-bold">Bottom 20%</span>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <div class="relative w-full sm:w-64">
                <input type="text" data-ops-search placeholder="Search"
                       class="w-full border border-slate-300 rounded px-3 py-1.5 text-xs pr-8 bg-white focus:outline-none focus:border-[#00bfa5]">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute inset-y-0 right-0 my-auto mr-2.5"></i>
            </div>
            <button data-ops-export
                    class="bg-white border border-[#00bfa5] text-[#00bfa5] font-bold px-4 py-1.5 rounded text-xs shrink-0 hover:bg-[#00bfa5] hover:text-white transition-colors">
                Export CSV
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto border-t border-slate-200 max-h-[70vh]">
        <table class="w-full text-left border-collapse whitespace-nowrap" style="min-width: max-content;">
            <thead class="sticky top-0 z-50 shadow-sm bg-white ring-1 ring-gray-200">
                @if (! empty($groups))
                    @php
                        $currentIndex = $leadSpan - 1;
                        if (isset($columns[$currentIndex])) {
                            $columns[$currentIndex]['class'] = trim(($columns[$currentIndex]['class'] ?? '') . ' border-r-[6px] border-white');
                        }
                        foreach ($groups as $g) {
                            $currentIndex += $g['span'];
                            if (isset($columns[$currentIndex])) {
                                $columns[$currentIndex]['class'] = trim(($columns[$currentIndex]['class'] ?? '') . ' border-r-[6px] border-white');
                            }
                        }
                    @endphp
                    <tr class="bg-white">
                        <th colspan="{{ $leadSpan }}" class="{{ $thBase }} border-r-[6px] border-white"></th>
                        @foreach ($groups as $group)
                            <th colspan="{{ $group['span'] }}"
                                class="{{ $thBase }} text-center border-r-[6px] border-white">
                                {{ $group['label'] }}
                            </th>
                        @endforeach
                    </tr>
                @endif
                @if (!empty($spec['header_groups']))
                    <tr class="bg-white">
                        @foreach ($spec['header_groups'] as $hg)
                            <th colspan="{{ $hg['colspan'] ?? 1 }}" class="{{ $thBase }} {{ $hg['class'] ?? '' }}">
                                {{ $hg['label'] ?? '' }}
                            </th>
                        @endforeach
                    </tr>
                @endif
                <tr class="bg-white">
                    @foreach ($columns as $col)
                        <th class="{{ $thBase }}
                                   {{ ($col['type'] ?? 'text') === 'text' ? 'text-left' : 'text-right' }}
                                   {{ ! empty($col['sticky']) ? 'tb:sm:stick-to-left bg-white' : '' }}
                                   {{ (! empty($col['sticky']) && $loop->index === 1) ? 'tb:sm:stick-shadow-r' : '' }}
                                   {{ $col['class'] ?? '' }}"
                            @if (! empty($col['sticky'])) style="min-width:12rem" @else style="min-width:8rem" @endif>
                            {{ $col['label'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 break-words whitespace-normal">
                @forelse ($spec['rows'] as $row)
                    <tr class="hover:bg-gray-50/80 transition bg-white">
                        @foreach ($columns as $i => $col)
                            @php 
                                $type = $col['type'] ?? 'text'; 
                                $cellClasses = "px-4 py-3 border-r border-gray-200 text-gray-700 text-xs";
                                $isSticky = !empty($col['sticky']);
                                if ($isSticky) {
                                    $cellClasses .= " tb:sm:stick-to-left bg-white";
                                    if ($loop->index === 1) {
                                        $cellClasses .= " tb:sm:stick-shadow-r";
                                    }
                                }
                                
                                if ($type === 'yn_badge') {
                                    $isY = in_array(strtolower((string)($row[$col['key']] ?? '')), ['y', 'yes', 'true', '1']);
                                    $cellClasses .= $isY ? ' bg-emerald-50 text-emerald-700 font-semibold text-center' : ' bg-red-50 text-red-700 font-semibold text-center';
                                    $cellContent = $isY ? 'Y' : 'N';
                                } else {
                                    $cellClasses .= " font-medium text-right";
                                    $cellContent = ops_fmt($row[$col['key']] ?? null, $type);
                                    if ($type === 'text') $cellClasses = str_replace('text-right', 'text-left', $cellClasses);
                                }
                                if (isset($col['class'])) {
                                    $cellClasses .= " " . $col['class'];
                                }
                            @endphp
                            <td class="{{ $cellClasses }} {{ ops_heat_class($heat, $col['key'], $row[$col['key']] ?? null) }}">
                                {!! $cellContent !!}
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}" class="py-12 text-center text-gray-400 text-sm">
                            No data for the selected range.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            <tfoot class="sticky bottom-0 z-50 bg-gray-50 border-t border-gray-200 shadow-sm">
                @if(($spec['is_compare'] ?? false) && isset($spec['total']) && is_array($spec['total']))
                    @foreach(['current' => 'Current', 'previous' => 'Previous', 'difference' => 'Difference'] as $key => $label)
                        <tr class="bg-gray-50 text-gray-900 font-bold text-xs text-right">
                            @foreach ($spec['columns'] as $col)
                                @if ($loop->first)
                                    <td class="px-4 py-3.5 border-r border-gray-300 text-right tb:sm:stick-to-left tb:sm:stick-shadow-r bg-gray-50">
                                        {{ $loop->parent->first ? 'Total:' : '' }}
                                    </td>
                                @elseif($col['key'] === 'type_label')
                                    <td class="px-4 py-3.5 border-r border-gray-300 text-left {{ $col['class'] ?? '' }}">
                                        {{ $label }}
                                    </td>
                                @else
                                    <td class="px-4 py-3.5 border-r border-gray-300 {{ $col['class'] ?? '' }}">
                                        {{ ops_fmt($spec['total'][$key][$col['key']] ?? 0, $col['type']) }}
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                @else
                    @if (! empty($spec['average']))
                        <tr class="bg-gray-50 text-gray-900 font-bold text-xs text-right">
                            @foreach ($spec['columns'] as $col)
                                @if ($loop->first)
                                    <td class="px-4 py-3.5 border-r border-gray-300 text-right tb:sm:stick-to-left tb:sm:stick-shadow-r bg-gray-50">
                                        Average:
                                    </td>
                                @else
                                    <td class="px-4 py-3.5 border-r border-gray-300 {{ $col['class'] ?? '' }}">
                                        {{ ops_fmt($spec['average'][$col['key']] ?? null, $col['type']) }}
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endif

                    @if (! empty($spec['total']))
                        <tr class="bg-gray-200 text-gray-900 font-bold text-xs text-right border-t border-gray-300">
                            @foreach ($spec['columns'] as $col)
                                @if ($loop->first)
                                    <td class="px-4 py-3.5 border-r border-gray-300 text-right tb:sm:stick-to-left tb:sm:stick-shadow-r bg-gray-200">
                                        Total:
                                    </td>
                                @else
                                    <td class="px-4 py-3.5 border-r border-gray-300 {{ $col['class'] ?? '' }}">
                                        {{ ops_fmt($spec['total'][$col['key']] ?? null, $col['type']) }}
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endif
                @endif
            </tfoot>
        </table>
    </div>
</div>
