{{--
    <x-analytics-table :spec="$spec" /> — the canonical server-rendered analytics table.
    Single place to edit table rendering for all 3 types:
      type 1 (basic)   : spec = ['columns'=>[...], 'rows'=>[...]]
      type 2 (footer)  : + 'average'=>[...] / 'total'=>[...] (+ optional 'header_groups')
      type 3 (grouped) : + 'groups'=>[['label','span'], ...]   (plain head = omit 'groups')

    Column: ['key','label','type'=>text|money|percent|number|html|yn_badge,
             'sticky'?, 'agg'?, 'heat'?=>false|'invert', 'class'?, 'drilldown'?, 'drilldown_type'?]

    Sticky uses the REAL .dds-stick/.dds-stick-2 CSS (ui.css) — the old tb:sm:stick-* were no-ops.
--}}
@props([
    'spec',
    'activeSubtab' => 'default',
    // Column sorting is on by default (DDS.sortable picks up .dds-sortable). Pass
    // :sortable="false" for a table whose row order carries meaning on its own.
    'sortable' => true,
])

@php
    if (!function_exists('ops_fmt')) {
        function ops_fmt($value, string $type): string
        {
            if ($value === null) return '—';
            if ($value === '--') return '--';
            switch ($type) {
                case 'money':
                    $v = (float) $value;
                    if ($v == 0) return '$ 0';
                    $abs = number_format(abs($v), 2);
                    return $v < 0 ? "$ ($abs)" : "$ $abs";
                case 'percent': return number_format((float) $value, 2) . '%';
                case 'number':
                    $v = (float) $value;
                    return floor($v) == $v ? number_format($v) : number_format($v, 2);
                case 'html': return $value;
                default: return e($value);
            }
        }
    }
    if (!function_exists('ops_heat_class')) {
        function ops_heat_class(array $heat, string $key, $value): string
        {
            if ($value === null || $value === '--' || !isset($heat[$key])) return '';
            $h = $heat[$key];
            $v = (float) $value;
            [$top, $bottom, $mid] = ['dds-heat-top', 'dds-heat-bottom', 'dds-heat-mid'];
            if ($h['invert']) { [$top, $bottom] = [$bottom, $top]; }
            if ($v >= $h['p80']) return $top;
            if ($v <= $h['p20']) return $bottom;
            return $mid;
        }
    }

    $columns = $spec['columns'];
    $groups = $spec['groups'] ?? [];
    $rows = $spec['rows'] ?? [];
    $leadSpan = max(1, count($columns) - array_sum(array_column($groups, 'span')));
    $thBase = 'text-xs font-extrabold py-3 px-4 border-r border-gray-200 text-gray-900';

    // Precompute sticky column classes (1st frozen col => dds-stick, 2nd => dds-stick-2,
    // shadow on the last frozen col). Replaces the undefined tb:sm:stick-* classes.
    $stickyPos = [];
    $s = 0;
    foreach ($columns as $i => $col) {
        if (!empty($col['sticky'])) { $stickyPos[$i] = $s; $s++; }
    }
    $stickyCount = $s;
    $stickyClass = function ($i) use ($stickyPos, $stickyCount) {
        if (!isset($stickyPos[$i])) return '';
        $cls = $stickyPos[$i] === 0 ? 'dds-stick' : 'dds-stick-2';
        if ($stickyPos[$i] === $stickyCount - 1) $cls .= ' dds-stick-shadow';
        return $cls;
    };

    // Per-column heatmap percentile thresholds (needs >=2 rows).
    $heat = [];
    if (count($rows) >= 2) {
        foreach ($columns as $col) {
            $type = $col['type'] ?? 'text';
            if ($type === 'text' || !empty($col['sticky']) || ($col['heat'] ?? null) === false) continue;
            $vals = [];
            foreach ($rows as $r) {
                $v = $r[$col['key']] ?? null;
                if ($v !== null && $v !== '--') $vals[] = (float) $v;
            }
            if (count($vals) < 2) continue;
            sort($vals);
            $n = count($vals);
            $heat[$col['key']] = [
                'p20' => $vals[(int) floor(0.2 * ($n - 1))],
                'p80' => $vals[(int) ceil(0.8 * ($n - 1))],
                'invert' => ($col['heat'] ?? null) === 'invert',
            ];
        }
    }

    $isDiffMode = in_array($activeSubtab ?? 'default', ['diff-last-year', 'percent-diff-last-year']);
@endphp

@php
    // Sort only a table that actually has rows — DataTables needs one cell per column,
    // and the empty state is a single colspan cell.
    $isSortable = $sortable && count($rows) > 0;
@endphp

<div class="dds-table-scroll border-t border-slate-200 max-h-[70vh]">
    <table class="dds-table {{ $isSortable ? 'dds-sortable' : '' }}" style="min-width: max-content;">
        <thead class="dds-head-sticky z-50 shadow-sm bg-white ring-1 ring-gray-200">
            @if (!empty($groups))
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
                <tr class="bg-gray-50">
                    <th colspan="{{ $leadSpan }}" class="{{ $thBase }} bg-gray-200 border-r-[6px] border-white"></th>
                    @foreach ($groups as $group)
                        <th colspan="{{ $group['span'] }}"
                            class="{{ $thBase }} bg-gray-200 text-center uppercase tracking-wider text-xs border-r-[6px] border-white">
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
                               {{ $stickyClass($loop->index) }}
                               {{ $col['class'] ?? '' }}"
                        @if (!empty($col['sticky'])) style="min-width:12rem"
                        @elseif (($col['type'] ?? '') === 'yn_badge') style="min-width:3rem"
                        @else style="min-width:8rem" @endif>
                        {{ $col['label'] }}
                    </th>
                @endforeach
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-100 break-words whitespace-normal">
            @forelse ($rows as $row)
                <tr class="hover:bg-gray-50/80 transition bg-white">
                    @foreach ($columns as $i => $col)
                        @php
                            $type = $col['type'] ?? 'text';
                            $rawValue = $row[$col['key']] ?? null;

                            // Sort key for numeric columns: the raw number. Without this
                            // DataTables sorts the DISPLAY text, and "$ 1,200.00" / "$ (900.00)"
                            // / "12.34%" sort alphabetically — 900 would beat 1,200 and negatives
                            // would land at the top. Missing values get an empty key so they sort
                            // together at one end instead of scattering.
                            $orderAttr = '';
                            if (in_array($type, ['money', 'percent', 'number'], true)) {
                                $orderAttr = ($rawValue === null || $rawValue === '--')
                                    ? ' data-order=""'
                                    : ' data-order="' . (float) $rawValue . '"';
                            }

                            $cellClasses = 'px-4 py-3 border-r border-gray-200 text-gray-700 text-xs';
                            $cellClasses .= ' ' . $stickyClass($i);
                            if ($type === 'yn_badge') {
                                $cellClasses = str_replace('px-4', 'px-2', $cellClasses);
                                $isY = in_array(strtolower((string) ($row[$col['key']] ?? '')), ['y', 'yes', 'true', '1']);
                                $cellClasses .= $isY ? ' bg-emerald-50 text-emerald-700 font-semibold text-center' : ' bg-red-50 text-red-700 font-semibold text-center';
                                $cellContent = $isY ? 'Y' : 'N';
                            } else {
                                $cellClasses .= ' font-medium text-right';
                                $cellContent = ops_fmt($row[$col['key']] ?? null, $type);
                                if ($type === 'text') $cellClasses = str_replace('text-right', 'text-left', $cellClasses);
                            }
                            if (isset($col['class'])) $cellClasses .= ' ' . $col['class'];
                        @endphp
                        <td class="{{ $cellClasses }} {{ ops_heat_class($heat, $col['key'], $rawValue) }}" {!! $orderAttr !!}>
                            @if ($isDiffMode)
                                <div class="flex items-center gap-1.5 {{ $type === 'text' ? 'justify-start' : 'justify-end' }}">{!! $cellContent !!}</div>
                            @elseif (($col['key'] === 'provider' || !empty($col['provider_modal'])) && !empty($row['prov_num']))
                                <div class="flex items-center justify-start gap-1.5">
                                    <span>{!! $cellContent !!}</span>
                                    <button type="button" class="text-emerald-500 hover:text-emerald-700 focus:outline-none shrink-0 inline-block align-middle"
                                            onclick="if(typeof openProviderModal === 'function') openProviderModal('{{ $row['prov_num'] }}'); else alert('Provider modal not loaded.');"
                                            title="View Provider Information">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-500 hover:text-emerald-700 cursor-pointer shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                            <polyline points="15 3 21 3 21 9"></polyline>
                                            <line x1="10" y1="14" x2="21" y2="3"></line>
                                        </svg>
                                    </button>
                                </div>
                            @elseif (!empty($col['drilldown_type']) && (isset($row['clinic_num']) || isset($row['prov_num'])))
                                @php
                                    $ddUrl = route('operations.drilldown', array_filter([
                                        'metric' => $col['drilldown_type'],
                                        'clinic_num' => $row['clinic_num'] ?? null,
                                        'prov_num' => $row['prov_num'] ?? null,
                                        'start_date' => request('start_date', now()->startOfMonth()->toDateString()),
                                        'end_date' => request('end_date', now()->toDateString()),
                                        'subtab' => $activeSubtab ?? 'default',
                                    ], fn ($v) => $v !== null && $v !== ''));
                                @endphp
                                <div class="flex items-center justify-end gap-1.5 {{ $type === 'text' ? 'justify-start' : '' }}">
                                    {!! $cellContent !!}
                                    <button type="button" class="dds-accent hover:text-[#009688] focus:outline-none shrink-0"
                                            onclick="DDS.modal.open('{{ $ddUrl }}')">
                                        <svg class="h-3 w-3 stroke-current" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    </button>
                                </div>
                            @elseif (!empty($col['drilldown']) && (float) ($row[$col['key']] ?? 0) > 0)
                                <div class="flex items-center justify-end gap-1.5 {{ $type === 'text' ? 'justify-start' : '' }}">
                                    {!! $cellContent !!}
                                    <button type="button" class="dds-accent hover:text-[#009688] focus:outline-none shrink-0"
                                            onclick="openOpsDrilldown({{ json_encode($row['title'] ?? 'Details') }}, {{ json_encode($row[$col['key'] . '_details'] ?? []) }})">
                                        <svg class="h-3 w-3 stroke-current" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    </button>
                                </div>
                            @else
                                {!! $cellContent !!}
                            @endif
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

        @php $hasFooter = ($spec['is_compare'] ?? false) || !empty($spec['average']) || !empty($spec['total']); @endphp
        @if ($hasFooter)
            <tfoot class="dds-foot-sticky z-50 bg-gray-50 border-t border-gray-200 shadow-sm">
                @if (($spec['is_compare'] ?? false) && isset($spec['total']) && is_array($spec['total']))
                    @foreach (['current' => 'Current', 'previous' => 'Previous', 'difference' => 'Difference'] as $key => $label)
                        <tr class="bg-gray-50 text-gray-900 font-bold text-xs text-right">
                            @foreach ($columns as $col)
                                @if ($loop->first)
                                    <td class="px-4 py-3.5 border-r border-gray-300 text-right dds-stick dds-stick-shadow bg-gray-50">{{ $loop->parent->first ? 'Total:' : '' }}</td>
                                @elseif ($col['key'] === 'type_label')
                                    <td class="px-4 py-3.5 border-r border-gray-300 text-left {{ $col['class'] ?? '' }}">{{ $label }}</td>
                                @else
                                    <td class="px-4 py-3.5 border-r border-gray-300 {{ $col['class'] ?? '' }}">{!! ops_fmt($spec['total'][$key][$col['key']] ?? 0, $col['type']) !!}</td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                @else
                    @if (!empty($spec['average']))
                        <tr class="bg-gray-50 text-gray-900 font-bold text-xs text-right">
                            @foreach ($columns as $col)
                                @if ($loop->first)
                                    <td class="px-4 py-3.5 border-r border-gray-300 text-right dds-stick dds-stick-shadow bg-gray-50">Average:</td>
                                @else
                                    <td class="px-4 py-3.5 border-r border-gray-300 {{ $col['class'] ?? '' }}">{!! ops_fmt($spec['average'][$col['key']] ?? null, $col['type']) !!}</td>
                                @endif
                            @endforeach
                        </tr>
                    @endif
                    @if (!empty($spec['total']))
                        <tr class="bg-gray-200 text-gray-900 font-bold text-xs text-right border-t border-gray-300">
                            @foreach ($columns as $col)
                                @if ($loop->first)
                                    <td class="px-4 py-3.5 border-r border-gray-300 text-right dds-stick dds-stick-shadow bg-gray-200">Total:</td>
                                @else
                                    <td class="px-4 py-3.5 border-r border-gray-300 {{ $col['class'] ?? '' }}">{!! ops_fmt($spec['total'][$col['key']] ?? null, $col['type']) !!}</td>
                                @endif
                            @endforeach
                        </tr>
                    @endif
                @endif
            </tfoot>
        @endif
    </table>
</div>
