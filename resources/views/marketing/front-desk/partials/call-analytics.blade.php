{{--
  Call analytics body, shared by the organization Analytics page and an office's
  Analytics › Calls tab. Expects $analytics (FrontDeskSource::analytics) and $filter.
  $byOffice (bool) adds the Production by office table (organization level only).
--}}
@php

    $s = $analytics['summary'];
    $byOffice ??= false;
@endphp

<div class="space-y-4">
    {{-- Headline numbers. --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
        @if($byOffice)
            <x-front-desk.kpi label="Total calls" :value="ops_fmt($s['calls'], 'number')" :change="$s['calls_change']"
                hint="Every inbound and outbound call the AI front desk handled." />
        @endif
        <x-front-desk.kpi label="Appointments booked" :value="ops_fmt($s['booked'], 'number')" :change="$s['booked_change']"
            :sub="$s['booked'] !== null ? ops_fmt($s['booked_new'], 'number').' new · '.ops_fmt($s['booked_existing'], 'number').' existing patients' : null"
            hint="Appointments the AI booked into OpenDental in the period." />
        <x-front-desk.kpi label="Production" :value="ops_fmt($s['production'], 'money')" :change="$s['production_change']"
            hint="Production from appointments the AI booked (Production domain service)." />
        <x-front-desk.kpi label="Success rate" :value="ops_fmt($s['success_rate'], 'percent_0')" :change="$s['success_change']"
            hint="Calls that ended with the caller's request handled." />
        <x-front-desk.kpi label="Transfer rate" :value="ops_fmt($s['transfer_rate'], 'percent_0')" :change="$s['transfer_change']"
            hint="Calls transferred to a person." />
        <x-front-desk.kpi label="Time saved" :value="$s['time_saved'] !== null ? ops_fmt($s['time_saved'], 'number').' min' : null" :change="$s['time_saved_change']"
            :sub="$s['avg_minutes'] !== null ? ops_fmt($s['avg_minutes'], 'number_2').' min avg call' : null"
            hint="Staff phone time the AI handled." />
    </div>

    {{-- Trends. --}}
    <div class="flex items-center justify-between pt-2">
        <h2 class="text-[13px] font-semibold text-slate-700">Trends · {{ strtolower(\App\Domain\FrontDesk\FrontDeskFilter::GRANULARITIES[$filter->granularity]) }}</h2>
        <x-front-desk.filter-group param="granularity" :options="\App\Domain\FrontDesk\FrontDeskFilter::GRANULARITIES" :current="$filter->granularity" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <x-front-desk.chart title="Success rate" :data="$analytics['successRate']" format="percent" :max="100"
            :value="ops_fmt($s['success_rate'], 'percent')" />
        <x-front-desk.chart title="Transfer rate" :data="$analytics['transferRate']" format="percent" :max="100"
            :value="ops_fmt($s['transfer_rate'], 'percent')" />
        <x-front-desk.chart title="Intake actions" :data="$analytics['intakeActions']" />
        <x-front-desk.chart title="Booking close rate" :data="$analytics['closeRate']" format="percent" :max="100"
            :value="ops_fmt($s['close_rate'], 'percent')" />
        <x-front-desk.chart title="Call volume" :data="$analytics['callVolume']" />
        <x-front-desk.chart title="Calls by time of day" :data="$analytics['callsByHour']" type="area" />
    </div>

    <x-front-desk.chart title="Daily call outcomes" subtitle="Every inbound call bucketed by outcome."
        :data="$analytics['dailyOutcomes']" type="bar" :height="300" />

    <x-front-desk.chart title="Transferred calls by caller intent" subtitle="Transferred calls bucketed by what the caller was asking about."
        :data="$analytics['transferIntents']" type="bar" :height="300" />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
        <x-front-desk.ranked title="Why calls didn't succeed" subtitle="Reasons calls didn't end successfully."
            :rows="$analytics['failureReasons']"
            :total="$s['unsuccessful'] !== null ? ops_fmt($s['unsuccessful'], 'number').' unsuccessful calls' : null" />
        <x-front-desk.ranked title="New-patient booking gaps" subtitle="Why new-patient callers who wanted to book didn't end up booked."
            :rows="$analytics['bookingGaps']"
            :total="$s['missed_np'] !== null ? ops_fmt($s['missed_np'], 'number').' missed NP bookings' : null" />
    </div>

    @if($byOffice)
        <x-marketing.panel title="Production by office" icon="building-2">
            @if($analytics['byOffice'] === [])
                <x-marketing.empty icon="building-2" title="No offices yet" />
            @else
                <x-analytics-table :spec="[
                    'columns' => [
                        ['key' => 'name', 'label' => 'Office', 'type' => 'text'],
                        ['key' => 'status', 'label' => 'Status', 'type' => 'html'],
                        ['key' => 'calls', 'label' => 'Calls', 'type' => 'number', 'heat' => false],
                        ['key' => 'appts', 'label' => 'Appts', 'type' => 'number', 'heat' => false],
                        ['key' => 'production', 'label' => 'Production', 'type' => 'money', 'heat' => false],
                        ['key' => 'share', 'label' => 'Share', 'type' => 'percent', 'heat' => false],
                    ],
                    'rows' => array_map(fn ($o) => [
                        'name' => $o['name'],
                        'status' => view('marketing.front-desk.partials.office-status', ['status' => $o['status']])->render(),
                    ] + $o, $analytics['byOffice']),
                ]" />
            @endif
        </x-marketing.panel>
    @endif
</div>
