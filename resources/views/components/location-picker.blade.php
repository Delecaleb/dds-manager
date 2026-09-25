{{--
  x-location-picker — reusable multi-select of reporting locations.

  A location is an office; an office with more than one clinic contributes one location per
  clinic (see ClinicRegistry::locations()). Values are location keys ("5", "8:2").

  Props:
    id         (string)  — unique picker id, used with DDS.getLocations(id) / DDS.onLocations(id, cb)
    locations  (array)   — ClinicRegistry::locations(): key => Location
    selected   (array)   — keys initially selected
    class      (string)  — extra classes on the wrapper

  Behavior lives in public/js/ui.js (DDS.locationPicker). Selection is emitted only on Apply,
  via the 'locations:changed' event.
--}}
@props([
    'id' => 'locations',
    'locations' => null,
    'selected' => null,
    'class' => '',
])

@php
    if ($locations === null || empty($locations)) {
        $clinicRegistry = app(\App\Domain\Support\ClinicRegistry::class);
        $locations = $clinicRegistry->locations();
    }
    if ($selected === null || empty($selected)) {
        $clinicRegistry = app(\App\Domain\Support\ClinicRegistry::class);
        $selected = $clinicRegistry->select(request('locations'))->keys();
    }
@endphp

<div data-dds-location-picker="{{ $id }}" class="relative z-40 {{ $class }}">
    <button type="button" data-lp-toggle aria-haspopup="true" aria-expanded="false"
        class="flex items-center gap-2 border border-slate-200 rounded-lg bg-slate-50 px-3 py-1.5 min-w-[190px] max-w-[260px] hover:border-emerald-400 hover:bg-white transition-colors cursor-pointer text-sm font-medium text-slate-700 focus:outline-none focus:border-emerald-400">
        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
        <span data-lp-label class="truncate flex-1 text-left">All Locations</span>
        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
    </button>

    <div data-lp-menu class="hidden absolute left-0 top-full mt-1.5 w-72 bg-white border border-slate-200 rounded-xl shadow-xl z-[100] p-2.5 flex flex-col gap-2">
        @if (count($locations) > 6)
            <input type="text" data-lp-search placeholder="Search locations…" aria-label="Search locations"
                class="w-full px-2.5 py-1 text-xs border border-slate-300 rounded-lg focus:outline-none focus:border-emerald-400 bg-slate-50 focus:bg-white">
        @endif

        <div class="flex items-center justify-between px-1">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Locations</span>
            <div class="flex items-center gap-2 text-[11px] font-bold">
                <button type="button" data-lp-all class="text-emerald-600 hover:text-emerald-700 cursor-pointer">Select all</button>
                <span class="text-slate-300">|</span>
                <button type="button" data-lp-none class="text-slate-500 hover:text-slate-700 cursor-pointer">Clear</button>
            </div>
        </div>

        <div class="overflow-y-auto max-h-64 space-y-0.5 border-y border-slate-100 py-1">
            @foreach ($locations as $key => $location)
                <label class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg hover:bg-emerald-50 cursor-pointer text-xs font-medium text-slate-700">
                    <input type="checkbox" data-lp-option value="{{ $key }}" data-label="{{ $location->name }}"
                        class="w-3.5 h-3.5 cursor-pointer accent-emerald-600"
                        @checked(in_array((string) $key, $selected, true))>
                    <span class="truncate">{{ $location->name }}</span>
                </label>
            @endforeach
        </div>

        <div class="flex justify-end">
            <button type="button" data-lp-apply
                class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-3 py-1 rounded-md cursor-pointer">
                Apply
            </button>
        </div>
    </div>
</div>
