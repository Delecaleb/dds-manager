{{--
  x-marketing.stat — one headline number in the Growth Engine.

  Props:
    metric                    — a MarketingMetric key; fills label, hint, icon and tone from
                                the glossary so the card matches its columns on every page
    label                     — what the number is (overrides the metric's)
    value  (default '—')      — the number; em dash until the metric is wired up
    hint                      — the definition, shown under the value (overrides the metric's)
    icon                      — lucide icon name (overrides the metric's)
    tone                      — slate | sky | indigo | violet | emerald | amber | rose
--}}
@props([
    'metric' => null,
    'label' => null,
    'value' => '—',
    'hint' => null,
    'icon' => null,
    'tone' => null,
])

@use(App\Domain\Marketing\MarketingMetric)

@php
    $label ??= $metric ? MarketingMetric::label($metric) : '';
    $hint ??= $metric ? MarketingMetric::help($metric) : null;
    $icon ??= $metric ? MarketingMetric::icon($metric) : 'activity';
    $tone ??= $metric ? MarketingMetric::tone($metric) : 'slate';
    $chip = (MarketingMetric::TONES[$tone] ?? MarketingMetric::TONES['slate'])['chip'];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white border border-slate-200 rounded-xl p-4 shadow-sm']) }}>
    <div class="flex items-start justify-between gap-3">
        <span class="text-[12px] font-bold text-slate-700 leading-tight">{{ $label }}</span>
        <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $chip }}">
            <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
        </span>
    </div>
    <div class="mt-2 text-[22px] font-extrabold text-slate-900 tabular-nums leading-tight">{{ $value }}</div>
    @if($hint)
        <div class="mt-1 text-[11px] text-slate-500 leading-snug">{{ $hint }}</div>
    @endif
</div>
