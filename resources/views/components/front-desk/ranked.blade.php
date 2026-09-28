{{--
  x-front-desk.ranked — "why" breakdown: one bar per reason with count and share.

  Props:
    title (required), subtitle
    rows  — list<{label, count}>, largest first
    total — label for the top-right total (e.g. "4,488 unsuccessful calls")
--}}
@props([
    'title',
    'subtitle' => null,
    'rows' => [],
    'total' => null,
])

@php $sum = array_sum(array_column($rows, 'count')); @endphp

<section {{ $attributes->merge(['class' => 'bg-white border border-slate-200 rounded-xl shadow-sm']) }}>
    <header class="flex items-end justify-between gap-4 px-4 pt-4 pb-3">
        <div class="min-w-0">
            <h3 class="text-[13px] font-semibold text-slate-800">{{ $title }}</h3>
            @if($subtitle)
                <p class="text-[11px] text-slate-500 mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
        @if($total)
            <span class="text-[11px] text-slate-500 shrink-0">{{ $total }}</span>
        @endif
    </header>
    <div class="px-4 pb-4">
        @forelse($rows as $row)
            @php $share = $sum > 0 ? $row['count'] / $sum * 100 : 0; @endphp
            <div class="grid grid-cols-[9rem_1fr_3.5rem_2.5rem] items-center gap-3 py-1.5 text-[12px]">
                <span class="truncate text-slate-700" title="{{ $row['label'] }}">{{ $row['label'] }}</span>
                <span class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                    <span class="block h-full rounded-full bg-rose-400" style="width: {{ max(1, round($share)) }}%"></span>
                </span>
                <span class="text-right tabular-nums text-slate-700">{{ ops_fmt($row['count'], 'number') }}</span>
                <span class="text-right tabular-nums text-slate-400">{{ ops_fmt($share, 'percent_0') }}</span>
            </div>
        @empty
            <x-marketing.empty icon="list" title="No calls in this period" />
        @endforelse
    </div>
</section>
