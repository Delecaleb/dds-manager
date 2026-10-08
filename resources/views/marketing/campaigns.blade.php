<x-marketing-layout>
    <x-slot:title>Campaigns</x-slot:title>
    <x-slot:subtitle>{{ $filter->locationLabel() }} · {{ $filter->start }} → {{ $filter->end }}</x-slot:subtitle>

    @php
        $channel = fn (?string $type) => $type ? ucwords(strtolower(str_replace('_', ' ', $type))) : '—';
        $status = fn (?string $s) => match ($s) {
            'ENABLED' => ['Active', 'emerald'],
            'PAUSED' => ['Paused', 'slate'],
            'REMOVED' => ['Removed', 'rose'],
            default => [ucfirst(strtolower((string) $s)), 'slate'],
        };
    @endphp

    <div class="p-6 space-y-6 max-w-[1500px]">

        {{-- Headline numbers: exactly what the ad platform reports for the period. --}}
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-3">
            <x-marketing.stat label="Active Campaigns" icon="megaphone" :value="number_format($summary['active_campaigns'])" hint="Enabled on the platform right now" />
            <x-marketing.stat label="Spend" icon="wallet" :value="ops_fmt($summary['spend'], 'money')" hint="As reported by Google Ads" />
            <x-marketing.stat label="Impressions" icon="eye" :value="number_format($summary['impressions'])" hint="Times an ad was shown" />
            <x-marketing.stat label="Clicks" icon="mouse-pointer-click" :value="number_format($summary['clicks'])" hint="Clicks on an ad" />
            <x-marketing.stat label="Conversions" icon="target" :value="ops_fmt($summary['conversions'], 'number')" hint="As counted by Google Ads conversion tracking" />
            <x-marketing.stat label="Cost / Conversion" icon="calculator" :value="ops_fmt($summary['cost_per_conversion'], 'money')" hint="Spend ÷ conversions" />
        </div>

        <x-marketing.panel title="All campaigns" subtitle="One row per campaign, credited to its own location or its ad account's" icon="megaphone">
            <x-slot:actions>
                @if($connected)
                    <span class="text-[11px] text-slate-500">
                        {{ $lastSynced ? 'Synced '.$lastSynced->diffForHumans() : 'Not synced yet' }}
                    </span>
                @endif
                <a href="{{ route('marketing.integrations') }}" class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800">Google Ads connection →</a>
            </x-slot:actions>

            @if(! $connected)
                <x-marketing.empty
                    icon="plug"
                    title="Google Ads is not connected for {{ strtolower($filter->locationLabel()) === 'all locations' ? 'any location' : $filter->locationLabel() }}"
                    message="Each location connects its own Google Ads account under Integrations. Campaigns and their daily spend, impressions, clicks and conversions are then synced every three hours." />
            @elseif($rows === [])
                <x-marketing.empty
                    icon="megaphone"
                    title="No campaign activity in this period"
                    message="{{ $lastSynced ? 'No campaign credited to these locations had spend or impressions between these dates.' : 'The first sync has not run yet. Campaigns appear here once it does.' }}" />
            @else
                <x-data-table id="mkCampaigns" min-width="1200px">
                    <x-slot:head>
                        <tr>
                            <th class="px-4 py-3">Campaign</th>
                            <th class="px-4 py-3">Channel</th>
                            <th class="px-4 py-3">Location</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Spend</th>
                            <th class="px-4 py-3 text-right">Impressions</th>
                            <th class="px-4 py-3 text-right">Clicks</th>
                            <th class="px-4 py-3 text-right">CTR</th>
                            <th class="px-4 py-3 text-right">Avg. CPC</th>
                            <th class="px-4 py-3 text-right">Conversions</th>
                            <th class="px-4 py-3 text-right">Cost / Conv.</th>
                            <th class="px-4 py-3 text-right">Daily Budget</th>
                        </tr>
                    </x-slot:head>
                    @foreach($rows as $row)
                        @php [$statusLabel, $statusTone] = $status($row['status']); @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="text-[13px] font-semibold text-slate-800">{{ $row['name'] }}</div>
                                <div class="text-[11px] text-slate-400">{{ $row['account_name'] ?? $row['account_external_id'] }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $channel($row['channel_type']) }}</td>
                            <td class="px-4 py-3 {{ $row['location'] ? 'text-slate-600' : 'text-amber-700' }}">{{ $row['location'] ?? 'Not assigned' }}</td>
                            <td class="px-4 py-3"><x-front-desk.pill :label="$statusLabel" :tone="$statusTone" /></td>
                            <td class="px-4 py-3 text-right tabular-nums font-semibold" data-order="{{ $row['spend'] }}">{{ ops_fmt($row['spend'], 'money') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['impressions'] }}">{{ number_format($row['impressions']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['clicks'] }}">{{ number_format($row['clicks']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['ctr'] ?? -1 }}">{{ $row['ctr'] !== null ? ops_fmt($row['ctr'] * 100, 'percent') : '—' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['avg_cpc'] ?? -1 }}">{{ ops_fmt($row['avg_cpc'], 'money') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['conversions'] }}">{{ ops_fmt($row['conversions'], 'number') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['cost_per_conversion'] ?? -1 }}">{{ ops_fmt($row['cost_per_conversion'], 'money') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['daily_budget'] ?? -1 }}">{{ ops_fmt($row['daily_budget'], 'money') }}</td>
                        </tr>
                    @endforeach
                </x-data-table>
            @endif
        </x-marketing.panel>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var table = document.getElementById('mkCampaigns');
            if (table) DDS.dataTable(table, { pageLength: 50, order: [[4, 'desc']] });
        });
    </script>
</x-marketing-layout>
