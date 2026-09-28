{{--
  Office Home. The reference screenshots don't include this page, so it mirrors the
  organization Overview for one office: headline numbers, bookings and call volume.
--}}
<x-marketing-layout>
    <x-slot:title>{{ $office->name }}</x-slot:title>
    <x-slot:subtitle>{{ now()->format('l, F j, Y') }}</x-slot:subtitle>
    <x-slot:toolbar>
        <x-front-desk.filter-group param="range" :options="['7d' => '7 days', '30d' => '30 days', '3m' => '3 mo']" :current="$filter->range" />
    </x-slot:toolbar>

    @php
        $s = $analytics['summary'];
        $volume = $analytics['callVolume'];
    @endphp

    <div class="p-6 space-y-4 max-w-[1500px]">
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
            <x-front-desk.kpi label="Total Calls" :value="ops_fmt($s['calls'], 'number')" :change="$s['calls_change']" />
            <x-front-desk.kpi label="Appointments Booked" :value="ops_fmt($s['booked'], 'number')" :change="$s['booked_change']"
                :sub="$s['booked'] !== null ? ops_fmt($s['booked_new'], 'number').' new · '.ops_fmt($s['booked_existing'], 'number').' existing' : null" />
            <x-front-desk.kpi label="Production" :value="ops_fmt($s['production'], 'money')" :change="$s['production_change']" />
            <x-front-desk.kpi label="Success rate" :value="ops_fmt($s['success_rate'], 'percent_0')" :change="$s['success_change']" />
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            <x-front-desk.chart title="Appointments booked ({{ $filter->periodLabel() }})" :data="$analytics['booked']" />
            <x-front-desk.chart title="Call volume ({{ $filter->periodLabel() }})" :data="$volume" />
        </div>

        <div class="flex flex-wrap gap-2">
            @foreach([
                ['marketing.front-desk.office.calls', 'phone', 'Call records'],
                ['marketing.front-desk.office.messages', 'message-square', 'Messages'],
                ['marketing.front-desk.office.bookings', 'calendar-check', 'Online bookings'],
                ['marketing.front-desk.office.analytics', 'chart-column', 'Full analytics'],
            ] as [$route, $icon, $label])
                <a href="{{ route($route, $office->key()) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-[12px] font-semibold text-slate-700 hover:border-blue-300 hover:text-blue-700">
                    <i data-lucide="{{ $icon }}" class="w-3.5 h-3.5"></i>{{ $label }}
                </a>
            @endforeach
        </div>
    </div>
</x-marketing-layout>
