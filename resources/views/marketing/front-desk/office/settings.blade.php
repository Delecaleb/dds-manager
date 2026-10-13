@php
    $groups = collect(\App\Domain\FrontDesk\FrontDeskTaxonomy::SETTINGS_SECTIONS)->groupBy(fn ($def) => $def[1], true);
    [$sectionLabel] = \App\Domain\FrontDesk\FrontDeskTaxonomy::SETTINGS_SECTIONS[$section];
@endphp

<x-marketing-layout>
    <x-slot:title>Settings</x-slot:title>
    <x-slot:subtitle>{{ $office->name }} · {{ $sectionLabel }}</x-slot:subtitle>

    <div class="flex flex-col lg:flex-row lg:min-h-[calc(100vh-4rem)]">
        {{-- Phones/tablets: the sub-nav becomes a section picker. --}}
        <div class="lg:hidden px-4 pt-4">
            <label for="fdSettingsSection" class="sr-only">Settings section</label>
            <select id="fdSettingsSection" onchange="window.location = this.value"
                class="w-full rounded-lg bg-white border border-slate-200 text-[13px] font-medium text-slate-700 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @foreach($groups as $group => $sections)
                    <optgroup label="{{ $group }}">
                        @foreach($sections as $slug => [$label])
                            <option value="{{ route('marketing.front-desk.office.settings', [$office->key(), $slug]) }}" @selected($slug === $section)>{{ $label }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>

        {{-- Settings sub-nav: one route, {section} parameter. --}}
        <nav class="hidden lg:block w-60 shrink-0 border-r border-slate-200 bg-slate-50/70 p-4 space-y-5">
            @foreach($groups as $group => $sections)
                <div>
                    <div class="px-2 pb-2 mb-1 text-[10px] font-bold uppercase tracking-wider text-slate-500 {{ $loop->first ? '' : 'border-b border-slate-200' }}">{{ $group }}</div>
                    <div class="space-y-0.5">
                        @foreach($sections as $slug => [$label])
                            @php $on = $slug === $section; @endphp
                            <a href="{{ route('marketing.front-desk.office.settings', [$office->key(), $slug]) }}"
                                class="block px-3 py-2 rounded-lg text-[13px] {{ $on ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-100' : 'text-slate-700 hover:bg-white' }}"
                                @if($on) aria-current="page" @endif>{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        <div class="flex-1 min-w-0">
            <div class="hidden lg:block px-6 py-5 border-b border-slate-200 bg-white">
                <h2 class="text-[18px] sm:text-[20px] font-semibold text-slate-900">{{ $sectionLabel }}</h2>
            </div>
            <div class="p-4 sm:p-6 space-y-5 max-w-[1300px]">
                @include("marketing.front-desk.office.settings.{$section}", ['settings' => $settings])
            </div>
        </div>
    </div>
</x-marketing-layout>
