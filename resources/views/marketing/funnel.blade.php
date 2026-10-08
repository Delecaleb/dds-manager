<x-marketing-layout>
    <x-slot:title>Attribution Funnel</x-slot:title>
    <x-slot:subtitle>{{ $filter->locationLabel() }} · {{ $filter->start }} → {{ $filter->end }}</x-slot:subtitle>

    <div class="p-6 space-y-6 max-w-[1500px]">

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            @foreach ([
                ['Paid funnel', 'From an ad being shown to a signup from that click', 'megaphone', $funnel['ad']],
                ['Site funnel', 'Every visit to the tracked sites, paid or not', 'globe', $funnel['site']],
            ] as [$title, $subtitle, $icon, $stages])
                <x-marketing.panel :title="$title" :subtitle="$subtitle" :icon="$icon">
                    <div class="space-y-2">
                        @foreach ($stages as $i => $stage)
                            <div class="flex items-center gap-4 border border-slate-200 rounded-xl px-4 py-3">
                                <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center shrink-0 text-[11px] font-bold">{{ $i + 1 }}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="text-[13px] font-bold text-slate-900">{{ $stage['label'] }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $stage['meaning'] }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-extrabold text-slate-900 tabular-nums leading-none">{{ number_format($stage['value']) }}</div>
                                    <div class="text-[10px] text-slate-400 mt-1">
                                        {{ $i === 0 ? 'starting point' : ($stage['rate'] === null ? 'no previous stage' : $stage['rate'] . '% of previous') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-marketing.panel>
            @endforeach
        </div>

        <x-marketing.panel title="By source" subtitle="Visitors → sessions → signups for every source / medium" icon="share-2">
            @if($funnel['sources'] === [])
                <x-marketing.empty icon="share-2" title="No traffic in this period" />
            @else
                <x-data-table id="mkSources" min-width="800px">
                    <x-slot:head>
                        <tr>
                            <th class="px-4 py-3">Source / Medium</th>
                            <th class="px-4 py-3 text-right">Visitors</th>
                            <th class="px-4 py-3 text-right">Sessions</th>
                            <th class="px-4 py-3 text-right">Signups</th>
                            <th class="px-4 py-3 text-right">Signup Rate</th>
                        </tr>
                    </x-slot:head>
                    @foreach($funnel['sources'] as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-[13px] font-medium text-slate-800">{{ $row['source'] }} / {{ $row['medium'] }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['visitors'] }}">{{ number_format($row['visitors']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['sessions'] }}">{{ number_format($row['sessions']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums font-semibold" data-order="{{ $row['signups'] }}">{{ number_format($row['signups']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['signup_rate'] }}">{{ $row['signup_rate'] }}%</td>
                        </tr>
                    @endforeach
                </x-data-table>
            @endif
        </x-marketing.panel>

        <x-marketing.panel title="Paid traffic by campaign" subtitle="Visits that arrived with an ad click id, grouped by the campaign on the landing URL" icon="megaphone">
            @if($funnel['adSources'] === [])
                <x-marketing.empty icon="megaphone" title="No ad clicks recorded"
                    message="A visit counts here when it arrives with gclid, fbclid or msclkid on the landing URL — which is what ties a visitor back to the ad that paid for them." />
            @else
                <x-data-table id="mkAdSources" min-width="800px">
                    <x-slot:head>
                        <tr>
                            <th class="px-4 py-3">Platform</th>
                            <th class="px-4 py-3">Campaign</th>
                            <th class="px-4 py-3">Ad / Keyword</th>
                            <th class="px-4 py-3 text-right">Visitors</th>
                            <th class="px-4 py-3 text-right">Sessions</th>
                            <th class="px-4 py-3 text-right">Signups</th>
                        </tr>
                    </x-slot:head>
                    @foreach($funnel['adSources'] as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-[13px] font-medium text-slate-800 capitalize">{{ $row['platform'] }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $row['campaign'] }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $row['content'] }} / {{ $row['term'] }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['visitors'] }}">{{ number_format($row['visitors']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['sessions'] }}">{{ number_format($row['sessions']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums font-semibold" data-order="{{ $row['signups'] }}">{{ number_format($row['signups']) }}</td>
                        </tr>
                    @endforeach
                </x-data-table>
            @endif
        </x-marketing.panel>

        <p class="text-[11px] text-slate-500">
            The funnel ends at the signup. Crediting a booked appointment or completed production to a click needs a
            rule for matching a lead to an OpenDental patient, which this app does not apply.
        </p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            ['mkSources', 'mkAdSources'].forEach(function (id) {
                var table = document.getElementById(id);
                if (table) DDS.dataTable(table, { pageLength: 25, order: [[id === 'mkSources' ? 2 : 4, 'desc']] });
            });
        });
    </script>
</x-marketing-layout>
