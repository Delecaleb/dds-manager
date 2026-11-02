{{--
  x-marketing.scope — heading over a row of stat cards that says what the numbers cover,
  so a card never reads as "overall" when it is one location (or the reverse).

  Props:
    title (required)  — what the cards measure, e.g. "Website traffic"
    scope (required)  — who they cover, e.g. MarketingFilter::scopeLabel()
    period            — e.g. "2026-10-04 → 2026-11-02"
--}}
@props([
    'title',
    'scope',
    'period' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-baseline gap-x-2 gap-y-0.5 mb-2']) }}>
    <h2 class="text-[12px] font-bold text-slate-900">{{ $title }}</h2>
    <span class="text-[11px] text-slate-500">
        — <span class="font-semibold text-slate-700">{{ $scope }}</span>@if($period) · {{ $period }}@endif
    </span>
</div>
