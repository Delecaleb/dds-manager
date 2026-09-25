<x-marketing-layout>
    <x-slot:title>Websites</x-slot:title>
    <x-slot:subtitle>{{ $selected['name'] ?? 'All sites' }} · {{ $start }} → {{ $end }}</x-slot:subtitle>

    <x-slot:toolbar>
        <form method="GET" class="flex items-center gap-2">
            <select name="site" onchange="this.form.submit()"
                class="text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 bg-white cursor-pointer">
                <option value="">All sites</option>
                @foreach($sites as $site)
                    <option value="{{ $site['id'] }}" @selected(($selected['id'] ?? null) === $site['id'])>{{ $site['name'] }}</option>
                @endforeach
            </select>
            <input type="date" name="start_date" value="{{ $start }}" onchange="this.form.submit()"
                class="text-xs rounded-lg border border-slate-200 px-2 py-1.5 bg-white">
            <input type="date" name="end_date" value="{{ $end }}" onchange="this.form.submit()"
                class="text-xs rounded-lg border border-slate-200 px-2 py-1.5 bg-white">
        </form>
    </x-slot:toolbar>

    <div class="p-6 space-y-6 max-w-[1500px]">

        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
            <x-marketing.stat label="Visitors" icon="users" :value="number_format($summary['visitors'])" hint="Unique browsers in the period" />
            <x-marketing.stat label="Sessions" icon="mouse-pointer-click" :value="number_format($summary['sessions'])" hint="Visits, split after 30 min idle" />
            <x-marketing.stat label="Signups" icon="user-plus" tone="emerald" :value="number_format($summary['signups'])" hint="Visits where contact details were given" />
            <x-marketing.stat label="Signup Rate" icon="percent" tone="emerald" :value="$summary['signup_rate'] . '%'" hint="Signups ÷ sessions" />
            <x-marketing.stat label="Avg. Time to Signup" icon="timer"
                :value="$summary['avg_seconds_to_signup'] === null ? '—' : \Carbon\CarbonInterval::seconds($summary['avg_seconds_to_signup'])->cascade()->forHumans(['short' => true, 'parts' => 2])"
                hint="First visit → signup" />
        </div>

        <x-marketing.panel title="Tracked sites" subtitle="Each site reports under its own key" icon="globe">
            <x-slot:actions>
                <a href="{{ route('marketing.tracking') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold rounded-lg border border-emerald-500 text-emerald-700 hover:bg-emerald-50">
                    <i data-lucide="code" class="w-3.5 h-3.5"></i> Add site / get script
                </a>
            </x-slot:actions>

            <div class="overflow-x-auto">
                <table class="dds-table w-full text-left text-xs whitespace-nowrap">
                    <thead>
                        <tr>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Site</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Domain</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Status</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900 text-right">Visitors</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900 text-right">Sessions</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900 text-right">Signups</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900 text-right">Signup Rate</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Last Seen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sites as $site)
                            <tr>
                                <td class="py-3 px-4 font-semibold text-slate-800">
                                    <a href="{{ route('marketing.websites', ['site' => $site['id'], 'start_date' => $start, 'end_date' => $end]) }}"
                                        class="hover:text-emerald-700">{{ $site['name'] }}</a>
                                </td>
                                <td class="py-3 px-4 text-slate-600">{{ $site['domain'] }}</td>
                                <td class="py-3 px-4">
                                    @if(! $site['is_active'])
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border border-slate-200 bg-slate-50 text-slate-500">Paused</span>
                                    @elseif($site['verified'])
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border border-emerald-200 bg-emerald-50 text-emerald-700">Tracking</span>
                                    @else
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border border-amber-200 bg-amber-50 text-amber-700">Awaiting first event</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums">{{ number_format($site['visitors']) }}</td>
                                <td class="py-3 px-4 text-right tabular-nums">{{ number_format($site['sessions']) }}</td>
                                <td class="py-3 px-4 text-right tabular-nums font-semibold">{{ number_format($site['signups']) }}</td>
                                <td class="py-3 px-4 text-right tabular-nums">{{ $site['signup_rate'] }}%</td>
                                <td class="py-3 px-4 text-slate-500">{{ $site['last_seen'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-0">
                                    <x-marketing.empty
                                        icon="globe"
                                        title="No sites tracked yet"
                                        message="Add a site and install its snippet — visitors appear here as soon as the first page loads." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-marketing.panel>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <x-marketing.panel title="Traffic sources" subtitle="Read from UTM tags, ad click ids and the referrer" icon="share-2">
                <div class="overflow-x-auto">
                    <table class="dds-table w-full text-left text-xs whitespace-nowrap">
                        <thead>
                            <tr>
                                <th class="py-2.5 px-4 font-bold text-gray-900">Source / Medium</th>
                                <th class="py-2.5 px-4 font-bold text-gray-900 text-right">Visitors</th>
                                <th class="py-2.5 px-4 font-bold text-gray-900 text-right">Sessions</th>
                                <th class="py-2.5 px-4 font-bold text-gray-900 text-right">Signups</th>
                                <th class="py-2.5 px-4 font-bold text-gray-900 text-right">Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trafficSources as $row)
                                <tr>
                                    <td class="py-3 px-4 text-slate-700 font-medium">{{ $row['source'] }} / {{ $row['medium'] }}</td>
                                    <td class="py-3 px-4 text-right tabular-nums">{{ number_format($row['visitors']) }}</td>
                                    <td class="py-3 px-4 text-right tabular-nums">{{ number_format($row['sessions']) }}</td>
                                    <td class="py-3 px-4 text-right tabular-nums font-semibold">{{ number_format($row['signups']) }}</td>
                                    <td class="py-3 px-4 text-right tabular-nums">{{ $row['signup_rate'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-0">
                                        <x-marketing.empty icon="share-2" title="No traffic in this period" />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-marketing.panel>

            <x-marketing.panel title="Ad sources" subtitle="Visits that arrived with an ad click id" icon="megaphone">
                <div class="overflow-x-auto">
                    <table class="dds-table w-full text-left text-xs whitespace-nowrap">
                        <thead>
                            <tr>
                                <th class="py-2.5 px-4 font-bold text-gray-900">Platform</th>
                                <th class="py-2.5 px-4 font-bold text-gray-900">Campaign</th>
                                <th class="py-2.5 px-4 font-bold text-gray-900">Ad / Keyword</th>
                                <th class="py-2.5 px-4 font-bold text-gray-900 text-right">Visitors</th>
                                <th class="py-2.5 px-4 font-bold text-gray-900 text-right">Signups</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adSources as $row)
                                <tr>
                                    <td class="py-3 px-4 text-slate-700 font-medium capitalize">{{ $row['platform'] }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ $row['campaign'] }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ $row['content'] }} / {{ $row['term'] }}</td>
                                    <td class="py-3 px-4 text-right tabular-nums">{{ number_format($row['visitors']) }}</td>
                                    <td class="py-3 px-4 text-right tabular-nums font-semibold">{{ number_format($row['signups']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-0">
                                        <x-marketing.empty
                                            icon="megaphone"
                                            title="No ad clicks recorded"
                                            message="A visit counts here when it arrives with gclid, fbclid or msclkid on the landing URL — which is what ties a visitor back to the ad that paid for them." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-marketing.panel>
        </div>

        <x-marketing.panel title="Top pages" subtitle="Views, unique visitors, and how often the page was the entry point" icon="file-text">
            <div class="overflow-x-auto">
                <table class="dds-table w-full text-left text-xs whitespace-nowrap">
                    <thead>
                        <tr>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Page</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900 text-right">Views</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900 text-right">Visitors</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900 text-right">Landings</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topPages as $page)
                            <tr>
                                <td class="py-3 px-4 text-slate-700 font-medium">{{ $page['path'] }}</td>
                                <td class="py-3 px-4 text-right tabular-nums">{{ number_format($page['views']) }}</td>
                                <td class="py-3 px-4 text-right tabular-nums">{{ number_format($page['visitors']) }}</td>
                                <td class="py-3 px-4 text-right tabular-nums">{{ number_format($page['landings']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-0">
                                    <x-marketing.empty icon="file-text" title="No page views in this period" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-marketing.panel>
    </div>
</x-marketing-layout>
