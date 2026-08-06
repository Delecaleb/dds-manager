{{-- Fallback fragment for tabs not yet wired to the backend. --}}
<div class="bg-white border border-slate-200 rounded shadow-sm">
    @if (! empty($subtabs))
        <ul class="flex border-b border-slate-200 px-4 pt-3 gap-1">
            @foreach ($subtabs as $slug => $label)
                <a href="{{ route('operations.tab', $slug === 'default' ? [$tab] : [$tab, $slug]) }}"
                   data-ops-subtab="{{ $slug }}"
                   class="text-xs font-semibold px-4 py-2 rounded-t cursor-pointer whitespace-nowrap
                          {{ $slug === $activeSubtab
                               ? 'bg-white text-black border border-b-0 border-slate-200 -mb-px'
                               : 'text-slate-400 hover:text-slate-600 bg-slate-50' }}">
                    {{ $label }}
                </a>
            @endforeach
        </ul>
    @endif

    <div class="flex flex-col items-center justify-center py-20 text-center gap-2">
        <i data-lucide="hammer" class="w-6 h-6 text-slate-300"></i>
        <p class="text-slate-500 font-semibold text-sm">{{ $label ?? 'This tab' }}</p>
        <p class="text-slate-400 text-xs">Not wired to the backend yet — share the data source and we'll build it.</p>
    </div>
</div>
