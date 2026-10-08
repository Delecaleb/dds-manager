<x-marketing-layout>
    <x-slot:title>Alerts</x-slot:title>
    <x-slot:subtitle>{{ $filter->locationLabel() }} · {{ $filter->start }} → {{ $filter->end }}</x-slot:subtitle>

    @php
        $svc = \App\Domain\Marketing\MarketingAlertService::class;
    @endphp

    <div class="p-6 space-y-6 max-w-[1500px]">

        <div class="grid grid-cols-3 gap-3">
            <x-marketing.stat label="Critical" icon="octagon-alert" tone="rose" :value="number_format($alerts['critical'])" hint="Data has stopped or money is being wasted" />
            <x-marketing.stat label="Warning" icon="triangle-alert" tone="amber" :value="number_format($alerts['warning'])" hint="Worth a look" />
            <x-marketing.stat label="Open Alerts" icon="bell-ring" :value="number_format(count($alerts['items']))" />
        </div>

        <x-marketing.panel title="Open alerts" subtitle="Checked against the tracking and ad data every time this page loads" icon="bell-ring">
            @if($alerts['items'] === [])
                <x-marketing.empty icon="bell-off" title="Nothing needs attention"
                    message="Every tracked site is reporting, Google Ads is in sync, and no enabled campaign is spending without conversions." />
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($alerts['items'] as $item)
                        <li class="flex items-start gap-4 py-3">
                            <x-front-desk.pill :label="ucfirst($item['severity'])" :tone="$item['severity'] === 'critical' ? 'rose' : 'amber'" class="mt-0.5 shrink-0" />
                            <div class="min-w-0 flex-1">
                                <div class="text-[13px] font-bold text-slate-900">{{ $item['title'] }}</div>
                                <div class="text-[11px] text-slate-500 leading-relaxed">{{ $item['detail'] }}</div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ $item['scope'] === 'organization' ? 'Organization' : 'This location' }}</span>
                                <a href="{{ route($item['route'], $filter->query()) }}" class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800">Open →</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-marketing.panel>

        <x-marketing.panel title="What raises an alert" subtitle="The rules, and the thresholds they use" icon="list-checks">
            <x-data-table id="mkAlertRules" min-width="800px">
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-3">Rule</th>
                        <th class="px-4 py-3">Fires when</th>
                        <th class="px-4 py-3">Severity</th>
                        <th class="px-4 py-3">Scope</th>
                    </tr>
                </x-slot:head>
                @foreach ([
                    ['Site never reported', 'A site was added over '.$svc::SITE_VERIFY_HOURS.' hours ago and no page view has arrived', 'Warning', 'Location'],
                    ['Site stopped reporting', 'A verified site has sent nothing for '.$svc::SITE_SILENT_HOURS.' hours', 'Critical', 'Location'],
                    ['Spend without conversions', 'An enabled campaign spent at least '.ops_fmt($svc::MIN_SPEND_FOR_CONVERSION_ALERT, 'money').' in the period with zero conversions', 'Warning', 'Location'],
                    ['Google Ads needs reconnecting', 'Google rejected the stored credentials', 'Critical', 'Organization'],
                    ['Google Ads not synced', 'No sync for '.$svc::ADS_STALE_HOURS.' hours (it runs every three)', 'Warning', 'Organization'],
                    ['Not assigned to a location', 'A tracked site or an ad account has no location, so it is missing from every per-office view', 'Warning', 'Organization'],
                ] as [$rule, $when, $severity, $scope])
                    <tr>
                        <td class="px-4 py-3 text-[13px] font-semibold text-slate-800">{{ $rule }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $when }}</td>
                        <td class="px-4 py-3"><x-front-desk.pill :label="$severity" :tone="$severity === 'Critical' ? 'rose' : 'amber'" /></td>
                        <td class="px-4 py-3 text-slate-600">{{ $scope }}</td>
                    </tr>
                @endforeach
            </x-data-table>
        </x-marketing.panel>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            DDS.dataTable(document.getElementById('mkAlertRules'), { paging: false, info: false, searching: false, ordering: false });
        });
    </script>
</x-marketing-layout>
