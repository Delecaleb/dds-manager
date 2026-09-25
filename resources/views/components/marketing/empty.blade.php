{{--
  x-marketing.empty — the placeholder every Growth Engine page shows until its data
  source is connected. Says plainly what will fill the space and what it is waiting on.

  Props: icon, title (required), message, waiting-on
--}}
@props([
    'icon' => 'inbox',
    'title',
    'message' => null,
    'waitingOn' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center text-center gap-2 py-12 px-6']) }}>
    <span class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center">
        <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
    </span>
    <h3 class="text-sm font-bold text-slate-700">{{ $title }}</h3>
    @if($message)
        <p class="text-xs text-slate-500 max-w-md leading-relaxed">{{ $message }}</p>
    @endif
    @if($waitingOn)
        <p class="mt-1 text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-2.5 py-1">
            Waiting on: {{ $waitingOn }}
        </p>
    @endif
</div>
