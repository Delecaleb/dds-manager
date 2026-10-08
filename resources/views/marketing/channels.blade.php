<x-marketing-layout>
    <x-slot:title>Channels</x-slot:title>
    <x-slot:subtitle>{{ $filter->locationLabel() }} · {{ $filter->start }} → {{ $filter->end }}</x-slot:subtitle>

    @php
        $icons = [
            'paid_search' => 'search', 'paid_social' => 'thumbs-up', 'organic_search' => 'globe', 'social' => 'share-2',
            'referral' => 'link', 'email' => 'mail', 'direct' => 'mouse-pointer-click', 'other' => 'circle-help',
        ];
        $reporting = $channels['verifiedSites'] . ' of ' . $channels['sites'] . ' ' . \Illuminate\Support\Str::plural('site', $channels['sites']) . ' reporting';
    @endphp

    <div class="p-6 space-y-6 max-w-[1500px]">

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach ($channels['rows'] as $row)
                @php
                    [$status, $tone] = $row['channel'] === 'paid_search'
                        ? ($channels['googleAdsConnected'] ? ['Google Ads connected', 'emerald'] : ['Google Ads not connected', 'slate'])
                        : ($channels['verifiedSites'] > 0 ? [$reporting, 'emerald'] : [$reporting, 'slate']);
                @endphp
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-9 h-9 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                                <i data-lucide="{{ $icons[$row['channel']] }}" class="w-4 h-4"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="text-[13px] font-bold text-slate-900 truncate">{{ $row['label'] }}</div>
                                <div class="text-[11px] text-slate-500 leading-snug">{{ $row['source'] }}</div>
                            </div>
                        </div>
                        <x-front-desk.pill :label="$status" :tone="$tone" />
                    </div>

                    <dl class="grid grid-cols-3 gap-2 mt-4 pt-3 border-t border-slate-100">
                        @foreach ([['Sessions', number_format($row['sessions'])], ['Signups', number_format($row['signups'])], ['Rate', $row['signup_rate'] . '%']] as [$metric, $value])
                            <div>
                                <dt class="text-[10px] text-slate-400 font-semibold">{{ $metric }}</dt>
                                <dd class="text-sm font-extrabold text-slate-900 tabular-nums">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    @if($row['spend'] !== null)
                        <dl class="grid grid-cols-3 gap-2 mt-3">
                            @foreach ([['Spend', ops_fmt($row['spend'], 'money')], ['Ad clicks', number_format($row['clicks'])], ['Cost / signup', ops_fmt($row['cost_per_signup'], 'money')]] as [$metric, $value])
                                <div>
                                    <dt class="text-[10px] text-slate-400 font-semibold">{{ $metric }}</dt>
                                    <dd class="text-sm font-extrabold text-slate-900 tabular-nums">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                </div>
            @endforeach
        </div>

        <x-marketing.panel title="Channel comparison" subtitle="Traffic and signups from site tracking; spend from the connected ad platform" icon="bar-chart-3">
            <x-data-table id="mkChannels" min-width="900px">
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-3">Channel</th>
                        <th class="px-4 py-3 text-right">Visitors</th>
                        <th class="px-4 py-3 text-right">Sessions</th>
                        <th class="px-4 py-3 text-right">Signups</th>
                        <th class="px-4 py-3 text-right">Signup Rate</th>
                        <th class="px-4 py-3 text-right">Ad Spend</th>
                        <th class="px-4 py-3 text-right">Cost / Signup</th>
                    </tr>
                </x-slot:head>
                @foreach ($channels['rows'] as $row)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-[13px] font-semibold text-slate-800">{{ $row['label'] }}</td>
                        <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['visitors'] }}">{{ number_format($row['visitors']) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['sessions'] }}">{{ number_format($row['sessions']) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold" data-order="{{ $row['signups'] }}">{{ number_format($row['signups']) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['signup_rate'] }}">{{ $row['signup_rate'] }}%</td>
                        <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['spend'] ?? -1 }}">{{ $row['spend'] !== null ? ops_fmt($row['spend'], 'money') : '—' }}</td>
                        <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['cost_per_signup'] ?? -1 }}">{{ $row['cost_per_signup'] !== null ? ops_fmt($row['cost_per_signup'], 'money') : '—' }}</td>
                    </tr>
                @endforeach
            </x-data-table>
            <p class="mt-3 text-[11px] text-slate-500">
                A visit is Paid Search when it carries a Google or Microsoft click id or a cpc / ppc medium; Paid Social on a Meta or TikTok click id;
                Organic Search, Social and Referral from the referring site; Direct when there is no referrer. Spend is Google Ads only, so
                Paid Social and Microsoft clicks show traffic but no cost.
            </p>
        </x-marketing.panel>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            DDS.dataTable(document.getElementById('mkChannels'), { pageLength: 25, order: [[2, 'desc']], paging: false, info: false, searching: false });
        });
    </script>
</x-marketing-layout>
