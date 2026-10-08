<x-marketing-layout>
    <x-slot:title>Overview</x-slot:title>
    <x-slot:subtitle>{{ $filter->locationLabel() }} · {{ $filter->start }} → {{ $filter->end }}</x-slot:subtitle>

    @php
        $web = $data['website'];
        $ads = $data['ads'];
        $paid = $data['paid'];
        $adsOn = $data['googleAdsConnected'];
        $money = fn ($v) => $adsOn ? ops_fmt($v, 'money') : '—';
        $count = fn ($v) => $adsOn ? number_format($v) : '—';
    @endphp

    <div class="p-6 space-y-6 max-w-[1500px]">

        {{-- Website: what the tracked sites in these locations did. --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
            <x-marketing.stat label="Visitors" icon="users" :value="number_format($web['visitors'])" hint="Unique browsers on the tracked sites" />
            <x-marketing.stat label="Sessions" icon="mouse-pointer-click" :value="number_format($web['sessions'])" hint="Visits, split after 30 min idle" />
            <x-marketing.stat label="Signups" icon="user-plus" tone="emerald" :value="number_format($web['signups'])" hint="Visits where contact details were given" />
            <x-marketing.stat label="Signup Rate" icon="percent" tone="emerald" :value="$web['signup_rate'] . '%'" hint="Signups ÷ sessions" />
            <x-marketing.stat label="Avg. Time to Signup" icon="timer"
                :value="$web['avg_seconds_to_signup'] === null ? '—' : \Carbon\CarbonInterval::seconds($web['avg_seconds_to_signup'])->cascade()->forHumans(['short' => true, 'parts' => 2])"
                hint="First visit → signup" />
        </div>

        {{-- Ads: what Google Ads reports for the campaigns credited to these locations. --}}
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-3">
            <x-marketing.stat label="Ad Spend" icon="wallet" :value="$money($ads['spend'])" :hint="$adsOn ? 'Google Ads, selected period' : 'Google Ads not connected'" />
            <x-marketing.stat label="Impressions" icon="eye" :value="$count($ads['impressions'])" hint="Ads shown" />
            <x-marketing.stat label="Clicks" icon="mouse-pointer-click" :value="$count($ads['clicks'])" hint="Ad clicks" />
            <x-marketing.stat label="Avg. CPC" icon="receipt" :value="$money($ads['avg_cpc'])" hint="Spend ÷ clicks" />
            <x-marketing.stat label="Paid Visits" icon="megaphone" :value="number_format($paid['sessions'])" hint="Tracked visits that arrived with an ad click id" />
            <x-marketing.stat label="Paid Signups" icon="badge-check" tone="emerald" :value="number_format($paid['signups'])" hint="Signups among the paid visits" />
        </div>

        <x-marketing.panel title="By location" subtitle="Each selected location, with its sites' traffic and its campaigns' spend" icon="map-pin">
            <x-slot:actions>
                <a href="{{ route('marketing.funnel', $filter->query()) }}" class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800">Funnel →</a>
                <a href="{{ route('marketing.channels', $filter->query()) }}" class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800">Channels →</a>
                <a href="{{ route('marketing.leads', $filter->query()) }}" class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800">Leads →</a>
            </x-slot:actions>

            @if($data['byLocation'] === [])
                <x-marketing.empty icon="map-pin" title="No locations configured" message="Add an office under Offices / Locations in the analytics app; its sites and campaigns then report here." />
            @else
                <x-data-table id="mkByLocation" min-width="1000px">
                    <x-slot:head>
                        <tr>
                            <th class="px-4 py-3">Location</th>
                            <th class="px-4 py-3 text-right">Sites</th>
                            <th class="px-4 py-3 text-right">Visitors</th>
                            <th class="px-4 py-3 text-right">Sessions</th>
                            <th class="px-4 py-3 text-right">Signups</th>
                            <th class="px-4 py-3 text-right">Signup Rate</th>
                            <th class="px-4 py-3 text-right">Campaigns</th>
                            <th class="px-4 py-3 text-right">Ad Spend</th>
                            <th class="px-4 py-3 text-right">Clicks</th>
                            <th class="px-4 py-3 text-right">Conversions</th>
                        </tr>
                    </x-slot:head>
                    @foreach($data['byLocation'] as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-[13px] font-semibold {{ $row['key'] === null ? 'text-amber-700' : 'text-slate-800' }}">
                                @if($row['key'] !== null)
                                    <a href="{{ route('marketing.overview', $filter->query(['locations' => $row['key']])) }}" class="hover:text-emerald-700">{{ $row['name'] }}</a>
                                @else
                                    {{ $row['name'] }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $row['sites'] }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['visitors'] }}">{{ number_format($row['visitors']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['sessions'] }}">{{ number_format($row['sessions']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums font-semibold" data-order="{{ $row['signups'] }}">{{ number_format($row['signups']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['signup_rate'] }}">{{ $row['signup_rate'] }}%</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $row['campaigns'] }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['spend'] }}">{{ $money($row['spend']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['clicks'] }}">{{ $count($row['clicks']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['conversions'] }}">{{ $adsOn ? ops_fmt($row['conversions'], 'number') : '—' }}</td>
                        </tr>
                    @endforeach
                </x-data-table>
            @endif
        </x-marketing.panel>

        @if($data['sites'] === [])
            {{-- Nothing tracked in these locations yet: say how to start, once. --}}
            <x-marketing.panel title="Start tracking a website" subtitle="Three steps — works on WordPress, Wix, Squarespace or a custom site" icon="globe">
                <x-slot:actions>
                    <a href="{{ route('marketing.tracking') }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold rounded-lg border border-emerald-500 text-emerald-700 hover:bg-emerald-50">
                        <i data-lucide="code" class="w-3.5 h-3.5"></i> Get the script
                    </a>
                </x-slot:actions>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach ([
                        ['1. Add the site', 'Name, domain and the location its leads belong to. You get a key and a snippet.', 'plus-circle'],
                        ['2. Paste the snippet', 'On WordPress: a header plugin such as WPCode, or the child theme. Step-by-step instructions, with your key already filled in, are on the Tracking Script page.', 'clipboard-paste'],
                        ['3. Mark signups', 'One line on form submit turns a visitor into a lead, and their earlier visits join to them.', 'user-plus'],
                    ] as [$step, $detail, $icon])
                        <div class="border border-slate-200 rounded-xl p-4">
                            <div class="flex items-center gap-2 text-[12px] font-bold text-slate-900">
                                <i data-lucide="{{ $icon }}" class="w-4 h-4 text-slate-400"></i> {{ $step }}
                            </div>
                            <p class="mt-1.5 text-[11px] text-slate-500 leading-relaxed">{{ $detail }}</p>
                        </div>
                    @endforeach
                </div>
            </x-marketing.panel>
        @endif

        @unless($adsOn)
            <p class="text-[11px] text-slate-500">
                Ad figures appear once Google Ads is connected under
                <a href="{{ route('marketing.integrations') }}" class="font-bold text-emerald-700 hover:text-emerald-800">Integrations</a>
                and each ad account is assigned to a location.
            </p>
        @endunless
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var table = document.getElementById('mkByLocation');
            if (table) DDS.dataTable(table, { pageLength: 50, order: [[4, 'desc']] });
        });
    </script>
</x-marketing-layout>
