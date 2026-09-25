{{--
  x-marketing.panel — titled card that holds a table, list or form.

  Props: title (required), subtitle, icon
  Slots: default (body), actions (right side of the header)
--}}
@props([
    'title',
    'subtitle' => null,
    'icon' => null,
])

<section {{ $attributes->merge(['class' => 'bg-white border border-slate-200 rounded-xl shadow-sm']) }}>
    <header class="flex items-start justify-between gap-4 px-5 py-3.5 border-b border-slate-100">
        <div class="min-w-0">
            <h2 class="text-[13px] font-bold text-slate-900 flex items-center gap-2">
                @if($icon)
                    <i data-lucide="{{ $icon }}" class="w-4 h-4 text-slate-400"></i>
                @endif
                {{ $title }}
            </h2>
            @if($subtitle)
                <p class="text-[11px] text-slate-500 mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="flex items-center gap-2 shrink-0">{{ $actions }}</div>
        @endisset
    </header>

    <div class="p-5">
        {{ $slot }}
    </div>
</section>
