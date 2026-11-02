{{--
  x-marketing.share — a table cell's "% of the total" with a thin bar in the metric's colour,
  so a row's weight against the period's total is visible at a glance. Use it as the
  content of a <td>; the cell keeps its own data-order for sorting.

  Props: value, total (required); metric — MarketingMetric key for the colour
--}}
@props(['value', 'total', 'metric' => 'visits'])

@use(App\Domain\Marketing\MarketingMetric)

@php
    $percent = $total > 0 ? round($value / $total * 100, 1) : 0.0;
    $bar = str_replace('text-', 'bg-', MarketingMetric::TONES[MarketingMetric::tone($metric)]['icon']);
@endphp

<div class="flex items-center justify-end gap-2">
    <div class="w-16 h-1.5 rounded-full bg-slate-100 overflow-hidden hidden sm:block">
        <div class="h-full rounded-full {{ $bar }}" style="width: {{ min(100, $percent) }}%"></div>
    </div>
    <span class="tabular-nums w-12 text-right">{{ $percent }}%</span>
</div>
