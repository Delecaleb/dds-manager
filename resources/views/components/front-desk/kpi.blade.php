{{--
  x-front-desk.kpi — one headline number with its change vs the previous period.

  Props:
    label  (required)
    value  — already formatted (ops_fmt); null renders "—"
    change — signed percent vs the previous period; null hides the chip
    hint   — the definition, shown in the (i) tooltip
    sub    — small line under the value (e.g. "388 new · 384 existing")
--}}
@props([
    'label',
    'value' => null,
    'change' => null,
    'hint' => null,
    'sub' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white border border-slate-200 rounded-xl p-4 shadow-sm']) }}>
    <div class="flex items-start justify-between gap-3">
        <span class="text-[12px] font-medium text-slate-600 leading-tight">{{ $label }}</span>
        @if($hint)
            <span class="text-slate-400" title="{{ $hint }}"><i data-lucide="info" class="w-3.5 h-3.5"></i></span>
        @endif
    </div>
    <div class="mt-2 flex items-baseline gap-2 flex-wrap">
        <span class="text-[24px] font-bold text-slate-900 tabular-nums leading-none">{{ $value ?? '—' }}</span>
        @if($change !== null)
            @php $up = $change >= 0; @endphp
            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md text-[10px] font-bold {{ $up ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-600' }}">
                <i data-lucide="{{ $up ? 'arrow-up' : 'arrow-down' }}" class="w-3 h-3"></i>{{ ops_fmt(abs($change), 'percent_0') }}
            </span>
        @endif
        {{ $slot }}
    </div>
    @if($sub)
        <div class="mt-1.5 text-[11px] text-slate-500">{{ $sub }}</div>
    @endif
</div>
