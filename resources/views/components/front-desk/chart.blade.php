{{--
  x-front-desk.chart — a titled chart card drawn by DDS.chart (public/js/ui.js).

  Props:
    title  (required)
    data   (required) — {labels, series: [{name, data, color}]} from a FrontDeskSource
    type   — line | area | bar (stacked)            default line
    format — number | percent                       default number
    max    — fixed y-axis max (100 for rates)
    value  — big number top-right (already formatted), e.g. "55.2%"
    subtitle
    height — canvas height in px                    default 220
--}}
@props([
    'title',
    'data',
    'type' => 'line',
    'format' => 'number',
    'max' => null,
    'value' => null,
    'subtitle' => null,
    'height' => 220,
])

@once
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
@endonce

@php $chartId = 'fd-chart-'.\Illuminate\Support\Str::random(8); @endphp

<section {{ $attributes->merge(['class' => 'bg-white border border-slate-200 rounded-xl shadow-sm p-4']) }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <h3 class="text-[13px] font-semibold text-slate-800">{{ $title }}</h3>
            @if($subtitle)
                <p class="text-[11px] text-slate-500 mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
        @if($value !== null)
            <span class="text-[20px] font-bold text-slate-900 tabular-nums leading-none">{{ $value }}</span>
        @endif
        {{ $actions ?? '' }}
    </div>
    <div class="relative mt-3" style="height: {{ (int) $height }}px">
        <canvas id="{{ $chartId }}"></canvas>
    </div>
    <script>
        DDS.chart(document.getElementById(@json($chartId)), Object.assign(@json($data), {
            type: @json($type), format: @json($format), max: @json($max)
        }));
    </script>
</section>
