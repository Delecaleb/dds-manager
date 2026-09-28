<x-marketing-layout>
    <x-slot:title>Growth Engine</x-slot:title>
    <x-slot:subtitle>Bring patients in, turn their calls into appointments, and build the sites they land on</x-slot:subtitle>

    {{-- One card per umbrella; everything on a card comes from MarketingLayout::UMBRELLAS. --}}
    <div class="p-6 max-w-[1400px]">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach (\App\View\Components\MarketingLayout::UMBRELLAS as $umbrella)
                <a href="{{ route($umbrella['home']) }}"
                    class="group flex flex-col bg-white border border-slate-200 rounded-xl shadow-sm p-6 transition hover:border-emerald-300 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                    <div class="flex items-start justify-between gap-4">
                        <span class="w-11 h-11 rounded-xl bg-[#0b1220] text-emerald-400 flex items-center justify-center">
                            <i data-lucide="{{ $umbrella['icon'] }}" class="w-5 h-5"></i>
                        </span>
                        @if($umbrella['status'])
                            <span class="px-2 py-0.5 rounded-md border border-amber-200 bg-amber-50 text-amber-800 text-[10px] font-bold uppercase tracking-wide">
                                {{ $umbrella['status'] }}
                            </span>
                        @endif
                    </div>

                    <h2 class="mt-4 text-base font-bold text-slate-900">{{ $umbrella['label'] }}</h2>
                    <p class="mt-1 text-[13px] text-slate-500 leading-relaxed">{{ $umbrella['description'] }}</p>

                    <div class="mt-4 flex flex-wrap gap-1.5">
                        @foreach($umbrella['highlights'] as $highlight)
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[11px] font-medium">{{ $highlight }}</span>
                        @endforeach
                    </div>

                    <span class="mt-6 pt-4 border-t border-slate-100 flex items-center gap-1.5 text-[12px] font-bold text-emerald-700 group-hover:text-emerald-800">
                        Open {{ $umbrella['label'] }}
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5"></i>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</x-marketing-layout>
