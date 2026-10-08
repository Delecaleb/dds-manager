<x-marketing-layout>
    <x-slot:title>Analytics</x-slot:title>
    <x-slot:subtitle>Get insights into your calls and bookings across all offices</x-slot:subtitle>


    <div class="p-6 space-y-4 max-w-[1500px]">
        {{-- Filters: every one is a URL param, so a filtered view can be shared. --}}
        <div class="flex flex-wrap items-center justify-end gap-2">
            <x-front-desk.filter-group param="patients" :options="\App\Domain\FrontDesk\FrontDeskFilter::PATIENTS" :current="$filter->patients" />
            <x-front-desk.filter-group param="direction" :options="\App\Domain\FrontDesk\FrontDeskFilter::DIRECTIONS" :current="$filter->direction" />
            <x-front-desk.filter-group param="range" :options="\App\Domain\FrontDesk\FrontDeskFilter::RANGES" :current="$filter->range" />
        </div>

        @include('marketing.front-desk.partials.call-analytics', ['byOffice' => true])
    </div>
</x-marketing-layout>
