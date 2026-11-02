{{--
  x-marketing.th — a column header for a Growth Engine metric: the glossary's label with the
  metric's coloured icon (the same as on its stat card), and its definition on hover.

  Props:
    metric (required) — a MarketingMetric key
    label             — overrides the glossary label (e.g. a shorter one in a narrow table)
    align             — left (default) | right
--}}
@props([
    'metric',
    'label' => null,
    'align' => 'left',
])

@use(App\Domain\Marketing\MarketingMetric)

@php
    $iconClass = (MarketingMetric::TONES[MarketingMetric::tone($metric)] ?? MarketingMetric::TONES['slate'])['icon'];
@endphp

<th {{ $attributes->merge(['class' => 'px-4 py-3 cursor-help ' . ($align === 'right' ? 'text-right' : '')]) }} title="{{ MarketingMetric::help($metric) }}">
    <span class="inline-flex items-center gap-1.5">
        <i data-lucide="{{ MarketingMetric::icon($metric) }}" class="w-3.5 h-3.5 shrink-0 {{ $iconClass }}"></i>
        <span>{{ $label ?? MarketingMetric::label($metric) }}</span>
    </span>
</th>
