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
    if (!function_exists('ops_fmt')) {
        function ops_fmt($value, string $type): string
        {
            if ($value === null)
                return '—';
            if ($value === '--')
                return '--';

            switch ($type) {
                case 'money':
                    $v = (float) $value;
                    if ($v == 0)
                        return '$ 0';
                    $abs = number_format(abs($v), 2);
                    return $v < 0 ? "$ ($abs)" : "$ $abs";
                case 'percent':
                    return number_format((float) $value, 2) . '%';
                case 'number':
                    $v = (float) $value;
                    return floor($v) == $v ? number_format($v) : number_format($v, 2);
                case 'html':
                    return $value;
                default:
                    return e($value);
            }
        }
    }

    // Heat-map: colour each numeric cell by where it ranks in its column.
    //   value >= 80th pct → Top 20% (green), <= 20th pct → Bottom 20% (red), else Mid (yellow).
    //   A column may set 'heat' => false to opt out, or 'heat' => 'invert' when lower is better.
    if (!function_exists('ops_heat_class')) {
        function ops_heat_class(array $heat, string $key, $value): string
        {
            if ($value === null || $value === '--' || !isset($heat[$key])) {
                return '';
            }
            $h = $heat[$key];
            $v = (float) $value;
            [$top, $bottom, $mid] = ['bg-[#c8f7dc]', 'bg-[#fecdd3]', 'bg-[#fef3c7]'];
            if ($h['invert']) {
                [$top, $bottom] = [$bottom, $top];
            }
            if ($v >= $h['p80'])
                return $top;
            if ($v <= $h['p20'])
                return $bottom;
            return $mid;
        }
    }

    $columns = $spec['columns'];
    $groups = $spec['groups'] ?? [];
    // Leading (ungrouped) columns the group header's empty cell must span.
    $leadSpan = max(1, count($columns) - array_sum(array_column($groups, 'span')));
    $thBase = 'text-xs font-extrabold py-3 px-4 border-r border-gray-200 text-gray-900';
    $tdBase = 'text-xs py-3 px-4 border-r border-gray-200';

    // Per-column percentile thresholds (needs ≥2 rows to be meaningful).
    $heat = [];
    if (count($spec['rows']) >= 2) {
        foreach ($columns as $col) {
            $type = $col['type'] ?? 'text';
            if ($type === 'text' || !empty($col['sticky']) || ($col['heat'] ?? null) === false) {
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
                'p20' => $vals[(int) floor(0.2 * ($n - 1))],
                'p80' => $vals[(int) ceil(0.8 * ($n - 1))],
                'invert' => ($col['heat'] ?? null) === 'invert',
            ];
        }
    }
@endphp

<div class="bg-white border border-slate-200 rounded shadow-sm">

    {{-- Subtab bar --}}
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
                                   {{ !empty($col['sticky']) ? 'tb:sm:stick-to-left bg-white' : '' }}
                                   {{ (!empty($col['sticky']) && $loop->index === 1) ? 'tb:sm:stick-shadow-r' : '' }}
                                   {{ $col['class'] ?? '' }}"
                            @if (!empty($col['sticky'])) 
                                style="min-width:12rem" 
                            @elseif (($col['type'] ?? '') === 'yn_badge')
                                style="min-width:3rem"
                            @else 
                                style="min-width:8rem" 
                            @endif>
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
                                    $cellClasses = str_replace('px-4', 'px-2', $cellClasses);
                                    $isY = in_array(strtolower((string) ($row[$col['key']] ?? '')), ['y', 'yes', 'true', '1']);
                                    $cellClasses .= $isY ? ' bg-emerald-50 text-emerald-700 font-semibold text-center' : ' bg-red-50 text-red-700 font-semibold text-center';
                                    $cellContent = $isY ? 'Y' : 'N';
                                } else {
                                    $cellClasses .= " font-medium text-right";
                                    $cellContent = ops_fmt($row[$col['key']] ?? null, $type);
                                    if ($type === 'text')
                                        $cellClasses = str_replace('text-right', 'text-left', $cellClasses);
                                }
                                if (isset($col['class'])) {
                                    $cellClasses .= " " . $col['class'];
                                }
                            @endphp
                            <td class="{{ $cellClasses }} {{ ops_heat_class($heat, $col['key'], $row[$col['key']] ?? null) }}">
                                @if (!empty($col['drilldown_type']) && isset($row['clinic_num']))
                                    @php
                                        $ddUrl = route('operations.drilldown', [
                                            'metric' => $col['drilldown_type'], 
                                            'clinic_num' => $row['clinic_num'], 
                                            'start_date' => request('start_date', now()->startOfMonth()->toDateString()), 
                                            'end_date' => request('end_date', now()->toDateString())
                                        ]);
                                    @endphp
                                    <div class="flex items-center justify-end gap-1.5 {{ $type === 'text' ? 'justify-start' : '' }}">
                                        {!! $cellContent !!}
                                        <button type="button" 
                                                class="text-[#00bfa5] hover:text-[#009688] focus:outline-none shrink-0"
                                                onclick="openLimitlessModal('{{ $ddUrl }}')">
                                            <svg class="h-3 w-3 stroke-current" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                        </button>
                                    </div>
                                @elseif (!empty($col['drilldown']) && (float)($row[$col['key']] ?? 0) > 0)
                                    <div class="flex items-center justify-end gap-1.5 {{ $type === 'text' ? 'justify-start' : '' }}">
                                        {!! $cellContent !!}
                                        <button type="button" 
                                                class="text-[#00bfa5] hover:text-[#009688] focus:outline-none shrink-0"
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
                                        {!! ops_fmt($spec['total'][$key][$col['key']] ?? 0, $col['type']) !!}
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                @else
                    @if (!empty($spec['average']))
                        <tr class="bg-gray-50 text-gray-900 font-bold text-xs text-right">
                            @foreach ($spec['columns'] as $col)
                                @if ($loop->first)
                                    <td class="px-4 py-3.5 border-r border-gray-300 text-right tb:sm:stick-to-left tb:sm:stick-shadow-r bg-gray-50">
                                        Average:
                                    </td>
                                @else
                                    <td class="px-4 py-3.5 border-r border-gray-300 {{ $col['class'] ?? '' }}">
                                        {!! ops_fmt($spec['average'][$col['key']] ?? null, $col['type']) !!}
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endif

                    @if (!empty($spec['total']))
                        <tr class="bg-gray-200 text-gray-900 font-bold text-xs text-right border-t border-gray-300">
                            @foreach ($spec['columns'] as $col)
                                @if ($loop->first)
                                    <td class="px-4 py-3.5 border-r border-gray-300 text-right tb:sm:stick-to-left tb:sm:stick-shadow-r bg-gray-200">
                                        Total:
                                    </td>
                                @else
                                    <td class="px-4 py-3.5 border-r border-gray-300 {{ $col['class'] ?? '' }}">
                                        {!! ops_fmt($spec['total'][$col['key']] ?? null, $col['type']) !!}
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

<div id="ops_drilldown_modal" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-lg shadow-xl border border-gray-200 w-full max-w-4xl flex flex-col max-h-[85vh] animate-in fade-in zoom-in duration-200">
        <div class="flex justify-between items-center p-5 border-b border-gray-100 bg-gray-50/50 rounded-t-lg">
            <h4 class="text-sm font-bold text-gray-900" id="ops_modal_title">Details</h4>
            <button onclick="closeOpsDrilldown()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 focus:outline-none">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <table class="w-full text-left border-collapse text-xs whitespace-nowrap">
                <thead class="sticky top-0 bg-white shadow-sm ring-1 ring-gray-100">
                    <tr id="ops_modal_headers">
                        <!-- Filled dynamically -->
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700" id="ops_modal_rows">
                    <!-- Filled by JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    window._limitlessZIndex = window._limitlessZIndex || 120;
    
    function openLimitlessModal(url) {
        // Show loading state gracefully if wanted, or just fetch
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                document.body.insertAdjacentHTML('beforeend', html);
                
                // Dynamically inflate z-index per subsequent limitless modal instance
                window._limitlessZIndex += 10;
                const modals = document.querySelectorAll('.ds-limitless-modal');
                const lastModal = modals[modals.length - 1];
                if (lastModal) {
                    lastModal.style.zIndex = window._limitlessZIndex;
                }
            })
            .catch(e => console.error("Drilldown fetch failed: ", e));
    }

    // Maintained for backward compatibility for embedded details
    function openOpsDrilldown(title, details) {
        const modal = document.getElementById('ops_drilldown_modal');
        const titleEl = document.getElementById('ops_modal_title');
        const headerContainer = document.getElementById('ops_modal_headers');
        const rowContainer = document.getElementById('ops_modal_rows');

        titleEl.textContent = `Breakdown | ${title}`;
        headerContainer.innerHTML = '';
        rowContainer.innerHTML = '';

        if (!details || details.length === 0) {
            rowContainer.innerHTML = `
                <tr>
                    <td class="py-8 text-center text-gray-400 text-sm">
                        No records found.
                    </td>
                </tr>
            `;
        } else {
            // Build dynamic headers based on the object keys of the first row
            const keys = Object.keys(details[0]);
            keys.forEach(key => {
                const th = document.createElement('th');
                th.className = 'py-2.5 px-4 font-bold text-gray-900 border-b border-gray-200 capitalize';
                // Adjust right align logic based on if it smells like money/count
                if (key.toLowerCase().includes('production') || key.toLowerCase().includes('visits') || key.toLowerCase().includes('#')) th.classList.add('text-right');
                th.textContent = key;
                headerContainer.appendChild(th);
            });

            // Build rows dynamically
            details.forEach(item => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50 border-b border-gray-50';
                
                keys.forEach(key => {
                    const td = document.createElement('td');
                    td.className = 'py-3 px-4';
                    
                    let val = item[key];
                    if (key.toLowerCase().includes('production') && typeof val === 'number') {
                        td.className += ' text-right font-medium text-gray-900';
                        td.textContent = '$ ' + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    } else if ((key.toLowerCase().includes('visits') || key.toLowerCase().includes('#')) && typeof val === 'number') {
                        td.className += ' text-right font-medium text-gray-900';
                        td.textContent = val.toLocaleString();
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
