<x-marketing-layout>
    <x-slot:title>Analytics</x-slot:title>
    <x-slot:subtitle>Get insights into your calls and bookings across all offices</x-slot:subtitle>


    <div class="p-6 space-y-4 max-w-[1500px]">
        {{-- Filters: every one is a URL param, so a filtered view can be shared. --}}
        <div class="flex flex-wrap items-center justify-end gap-2">
            <select aria-label="Office" onchange="window.location = this.value"
                class="rounded-lg border border-slate-200 bg-white text-[12px] font-medium text-slate-700 px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="{{ request()->fullUrlWithQuery(['office' => null]) }}">All offices</option>
                @foreach($locations as $key => $location)
                    <option value="{{ request()->fullUrlWithQuery(['office' => $key]) }}" @selected($office === (string) $key)>{{ $location->name }}</option>
                @endforeach
            </select>
            <x-front-desk.filter-group param="patients" :options="\App\Domain\FrontDesk\FrontDeskFilter::PATIENTS" :current="$filter->patients" />
            <x-front-desk.filter-group param="direction" :options="\App\Domain\FrontDesk\FrontDeskFilter::DIRECTIONS" :current="$filter->direction" />
            <x-front-desk.filter-group param="range" :options="\App\Domain\FrontDesk\FrontDeskFilter::RANGES" :current="$filter->range" />
        </div>

        @include('marketing.front-desk.partials.call-analytics', ['byOffice' => true])
    </div>
</x-marketing-layout>
