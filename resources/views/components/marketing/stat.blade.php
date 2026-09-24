{{--
  x-marketing.stat — one headline number in the Growth Engine.

  Props:
    label (required)          — what the number is
    value  (default '—')      — the number; em dash until the metric is wired up
    hint                      — the definition, shown under the value
    icon                      — lucide icon name
    tone   (default 'slate')  — slate | emerald | amber | rose, for the icon chip
--}}
@props([
    'label',
    'value' => '—',
    'hint' => null,
    'icon' => 'activity',
    'tone' => 'slate',
])

@php
    $tones = [
        'slate' => 'bg-slate-100 text-slate-500',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'rose' => 'bg-rose-50 text-rose-600',
    ];
    $chip = $tones[$tone] ?? $tones['slate'];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white border border-slate-200 rounded-xl p-4 shadow-sm']) }}>
    <div class="flex items-start justify-between gap-3">
        <span class="text-[11px] font-semibold text-slate-500 leading-tight">{{ $label }}</span>
        <span class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ $chip }}">
            <i data-lucide="{{ $icon }}" class="w-3.5 h-3.5"></i>
        </span>
    </div>
    <div class="mt-2 text-[22px] font-extrabold text-slate-900 tabular-nums leading-tight">{{ $value }}</div>
    @if($hint)
        <div class="mt-1 text-[11px] text-slate-400 leading-snug">{{ $hint }}</div>
    @endif
</div>
