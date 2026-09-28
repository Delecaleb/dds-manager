{{--
  x-front-desk.field — one label / value row in a detail pane.
  Pass `value`, or put rich content in the slot. Empty renders "—".
--}}
@props([
    'label',
    'value' => null,
])

@php $empty = ($value === null || $value === '') && $slot->isEmpty(); @endphp

<div class="grid grid-cols-[8rem_1fr] gap-3 py-1.5 text-[12px]">
    <dt class="text-slate-500">{{ $label }}</dt>
    <dd class="text-slate-800 break-words">
        @if($empty)
            —
        @elseif($value !== null && $value !== '')
            {{ $value }}
        @else
            {{ $slot }}
        @endif
    </dd>
</div>
