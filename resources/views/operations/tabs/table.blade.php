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
    $thBase  = 'text-xs font-semibold py-3 px-3 border-l border-slate-300 text-slate-600';
    $tdBase  = 'text-xs py-2 px-3 border-l border-t border-slate-200';

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
    <div class="overflow-x-auto border-t border-slate-200">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead>
                @if (! empty($groups))
                    <tr class="bg-[#cbd5e1]">
                        <th colspan="{{ $leadSpan }}" class="{{ $thBase }} bg-[#cbd5e1]" style="min-width:12rem"></th>
                        @foreach ($groups as $group)
                            <th colspan="{{ $group['span'] }}"
                                class="{{ $thBase }} text-slate-700 font-bold text-left pl-4">
                                {{ $group['label'] }}
                            </th>
                        @endforeach
                    </tr>
                @endif
                <tr class="bg-[#e2e8f0]">
                    @foreach ($columns as $col)
                        <th class="{{ $thBase }}
                                   {{ ($col['type'] ?? 'text') === 'text' ? 'text-left' : 'text-right' }}
                                   {{ ! empty($col['sticky']) ? 'sticky left-0 bg-[#e2e8f0] z-10' : '' }}"
                            @if (! empty($col['sticky'])) style="min-width:12rem" @else style="min-width:8rem" @endif>
                            {{ $col['label'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @forelse ($spec['rows'] as $row)
                    <tr class="odd:bg-slate-50/60 even:bg-white hover:bg-slate-100/60">
                        @foreach ($columns as $col)
                            @php
                                $type = $col['type'] ?? 'text';
                                $heatClass = ($type === 'text' || ! empty($col['sticky']))
                                    ? ''
                                    : ops_heat_class($heat, $col['key'], $row[$col['key']] ?? null);
                            @endphp
                            <td class="{{ $tdBase }} {{ $heatClass }}
                                       {{ $type === 'text' ? 'text-left font-medium text-slate-700' : 'text-right text-slate-600' }}
                                       {{ ! empty($col['sticky']) ? 'sticky left-0 bg-inherit font-semibold text-slate-800' : '' }}">
                                {{ ops_fmt($row[$col['key']] ?? null, $type) }}
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}" class="py-12 text-center text-slate-400 text-sm">
                            No data for the selected range.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            @if (! empty($spec['rows']))
                <tfoot class="bg-[#e2e8f0] font-semibold text-slate-700">
                    <tr>
                        @foreach ($columns as $i => $col)
                            @php $type = $col['type'] ?? 'text'; @endphp
                            <td class="text-xs py-2 px-3 border-l border-t border-slate-300 text-right
                                       {{ ! empty($col['sticky']) ? 'sticky left-0 bg-[#e2e8f0]' : '' }}">
                                {{ $i === 0 ? 'Average:' : ops_fmt($spec['average'][$col['key']] ?? null, $type) }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($columns as $i => $col)
                            @php $type = $col['type'] ?? 'text'; @endphp
                            <td class="text-xs py-2 px-3 border-l border-t border-slate-300 text-right
                                       {{ ! empty($col['sticky']) ? 'sticky left-0 bg-[#e2e8f0]' : '' }}">
                                {{ $i === 0 ? 'Total:' : ops_fmt($spec['total'][$col['key']] ?? null, $type) }}
                            </td>
                        @endforeach
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
