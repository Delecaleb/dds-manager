<x-marketing-layout>
    <x-slot:title>AI Front Desk</x-slot:title>
    <x-slot:subtitle>{{ now()->format('l, F j, Y') }}</x-slot:subtitle>
    <x-slot:toolbar>
        <x-front-desk.filter-group param="range" :options="['7d' => '7 days', '30d' => '30 days', '3m' => '3 mo']" :current="$filter->range" />
    </x-slot:toolbar>

    @php
        $s = $analytics['summary'];
        $volume = $analytics['callVolume'];
        $volume['series'] = array_slice($volume['series'], 0, 1); // Overview shows total calls only.
    @endphp

    <div class="p-6 space-y-4 max-w-[1500px]">
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
            <x-front-desk.kpi label="Total Offices" :value="$s['offices_live'].'/'.$s['offices_total']"
                hint="Offices with the AI front desk live, of all offices.">
                <span class="text-[12px] text-slate-500">offices live</span>
            </x-front-desk.kpi>
            <x-front-desk.kpi label="Total Calls" :value="ops_fmt($s['calls'], 'number')" :change="$s['calls_change']"
                hint="Every call the AI front desk handled, {{ $filter->periodLabel() }}." />
            <x-front-desk.kpi label="Appointments Booked" :value="ops_fmt($s['booked'], 'number')" :change="$s['booked_change']"
                hint="Appointments the AI booked into OpenDental." />
            <x-front-desk.kpi label="Total Production" :value="ops_fmt($s['production'], 'money')" :change="$s['production_change']"
                hint="Production from appointments the AI booked." />
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            <div class="space-y-4">
                <x-front-desk.chart title="Appointments booked ({{ $filter->periodLabel() }})" :data="$analytics['booked']" />
                <x-front-desk.chart title="Call volume ({{ $filter->periodLabel() }})" :data="$volume" />
            </div>

            @include('marketing.front-desk.partials.office-map', ['offices' => $offices, 'title' => 'All Offices'])
        </div>
    </div>
</x-marketing-layout>
