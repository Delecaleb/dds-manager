<x-marketing-layout>
    <x-slot:title>Websites</x-slot:title>
    <x-slot:subtitle>{{ $filter->locationLabel() }} · {{ $selected['name'] ?? 'all sites' }} · {{ $filter->start }} → {{ $filter->end }}</x-slot:subtitle>

    <x-slot:toolbar>
        <form method="GET" class="flex items-center gap-2">
            <select name="site" onchange="this.form.submit()"
                class="text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 bg-white cursor-pointer">
                <option value="">All sites</option>
                @foreach($sites as $site)
                    <option value="{{ $site['id'] }}" @selected(($selected['id'] ?? null) === $site['id'])>{{ $site['name'] }}</option>
                @endforeach
            </select>
            <input type="hidden" name="start_date" value="{{ $filter->start }}">
            <input type="hidden" name="end_date" value="{{ $filter->end }}">
        </form>
    </x-slot:toolbar>

    @php
        // Who each block covers: the sites table always lists every site in the locations;
        // the cards and the tables below narrow to one site when the dropdown picks one.
        $where = $filter->allLocations ? 'all locations' : implode(', ', array_map(fn ($l) => $l->name, $filter->locations()));
        $numbersFor = $selected ? $selected['name'] . ' (' . $selected['domain'] . ')' : $where;
        $period = $filter->start . ' → ' . $filter->end;
        $source = fn (array $row) => $row['source'] === 'direct' ? 'Direct (typed in or bookmarked)' : $row['source'] . ' / ' . $row['medium'];
        $totalVisitors = $summary['visitors'];
        $totalVisits = $summary['sessions'];
    @endphp

    <div class="p-4 sm:p-6 space-y-6 max-w-[1500px]">

        <div>
            <x-marketing.scope title="Website traffic"
                :scope="$selected ? $numbersFor . ' only' : 'All tracked sites, ' . $filter->scopeLabel()"
                :period="$period" />
            <x-marketing.traffic-sentence :summary="$summary" class="mb-3" />
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
                <x-marketing.stat metric="visitors" :value="number_format($summary['visitors'])" />
                <x-marketing.stat metric="visits" :value="number_format($summary['sessions'])" />
                <x-marketing.stat metric="pageviews" :value="number_format($summary['pageviews'])" />
                <x-marketing.stat metric="signups" :value="number_format($summary['signups'])" />
                <x-marketing.stat metric="signup_rate" :value="$summary['signup_rate'] . '%'" />
                <x-marketing.stat metric="time_to_signup"
                    :value="$summary['avg_seconds_to_signup'] === null ? '—' : \Carbon\CarbonInterval::seconds($summary['avg_seconds_to_signup'])->cascade()->forHumans(['short' => true, 'parts' => 2])" />
            </div>
        </div>

        {{-- Where the visits came from: visits add up to the total, unique visitors do not. --}}
        <x-marketing.panel :title="'Where visitors came from · ' . $numbersFor"
            subtitle="Each visit has one source, read from UTM tags, ad click ids and the referring site. Hover a column for its meaning."
            icon="share-2">
            @if($trafficSources === [])
                <x-marketing.empty icon="share-2" title="No visits in this period" />
            @else
                <x-data-table id="mkTrafficSources" min-width="860px">
                    <x-slot:head>
                        <tr>
                            <x-marketing.th metric="source" />
                            <x-marketing.th metric="visits" align="right" />
                            <x-marketing.th metric="share_visits" align="right" />
                            <x-marketing.th metric="visitors" align="right" />
                            <x-marketing.th metric="share_visitors" align="right" />
                            <x-marketing.th metric="signups" align="right" />
                            <x-marketing.th metric="signup_rate" align="right" />
                        </tr>
                    </x-slot:head>
                    @foreach($trafficSources as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-[13px] text-slate-800 font-medium">{{ $source($row) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums font-semibold" data-order="{{ $row['sessions'] }}">{{ number_format($row['sessions']) }}</td>
                            <td class="px-4 py-3" data-order="{{ $row['sessions'] }}"><x-marketing.share :value="$row['sessions']" :total="$totalVisits" metric="visits" /></td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['visitors'] }}">{{ number_format($row['visitors']) }}</td>
                            <td class="px-4 py-3" data-order="{{ $row['visitors'] }}"><x-marketing.share :value="$row['visitors']" :total="$totalVisitors" metric="visitors" /></td>
                            <td class="px-4 py-3 text-right tabular-nums font-semibold" data-order="{{ $row['signups'] }}">{{ number_format($row['signups']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['signup_rate'] }}">{{ $row['signup_rate'] }}%</td>
                        </tr>
                    @endforeach
                    <x-slot:foot>
                        <tr class="text-xs font-bold text-slate-800">
                            <td class="px-4 py-3">All sources</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ number_format($totalVisits) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">100%</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ number_format($totalVisitors) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">—</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ number_format($summary['signups']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $summary['signup_rate'] }}%</td>
                        </tr>
                    </x-slot:foot>
                </x-data-table>
                <p class="mt-3 text-[11px] text-slate-500 leading-relaxed">
                    <span class="font-semibold text-slate-700">Visits</span> add up to the {{ number_format($totalVisits) }} total: every visit has exactly one source.
                    <span class="font-semibold text-slate-700">Unique visitors</span> can add up to more than {{ number_format($totalVisitors) }}:
                    someone who came from Google one day and typed the address the next is in both rows, but counted once in the total.
                </p>
            @endif
        </x-marketing.panel>

        {{-- Which pages they saw: visits started here add up to the total; views and visitors overlap. --}}
        <x-marketing.panel :title="'Pages they visited · ' . $numbersFor"
            :subtitle="'The ' . count($topPages) . ' most viewed pages. Hover a column for its meaning.'"
            icon="file-text">
            @if($topPages === [])
                <x-marketing.empty icon="file-text" title="No page views in this period" />
            @else
                <x-data-table id="mkTopPages" min-width="980px">
                    <x-slot:head>
                        <tr>
                            <th class="px-4 py-3">Page</th>
                            <x-marketing.th metric="pageviews" align="right" />
                            <x-marketing.th metric="visitors" align="right" />
                            <x-marketing.th metric="share_visitors" align="right" label="% of all visitors who saw it" />
                            <x-marketing.th metric="entrances" align="right" />
                            <x-marketing.th metric="share_visits" align="right" label="% of all visits that started here" />
                        </tr>
                    </x-slot:head>
                    @foreach($topPages as $page)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-[12px] text-slate-700 font-medium">
                                @if(str_starts_with((string) $page['url'], 'https://'))
                                    <a href="{{ $page['url'] }}" target="_blank" rel="noopener noreferrer" class="hover:text-emerald-700 hover:underline">{{ $page['url'] }}</a>
                                @else
                                    {{ $page['url'] ?? '—' }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums font-semibold" data-order="{{ $page['views'] }}">{{ number_format($page['views']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $page['visitors'] }}">{{ number_format($page['visitors']) }}</td>
                            <td class="px-4 py-3" data-order="{{ $page['visitors'] }}"><x-marketing.share :value="$page['visitors']" :total="$totalVisitors" metric="visitors" /></td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $page['landings'] }}">{{ number_format($page['landings']) }}</td>
                            <td class="px-4 py-3" data-order="{{ $page['landings'] }}"><x-marketing.share :value="$page['landings']" :total="$totalVisits" metric="visits" /></td>
                        </tr>
                    @endforeach
                    <x-slot:foot>
                        <tr class="text-xs font-bold text-slate-800">
                            <td class="px-4 py-3">All pages (not only those listed)</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ number_format($summary['pageviews']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ number_format($totalVisitors) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">—</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ number_format($totalVisits) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">100%</td>
                        </tr>
                    </x-slot:foot>
                </x-data-table>
                <p class="mt-3 text-[11px] text-slate-500 leading-relaxed">
                    <span class="font-semibold text-slate-700">Visits started here</span> add up to the {{ number_format($totalVisits) }} total across all pages: every visit starts on one page.
                    <span class="font-semibold text-slate-700">Unique visitors</span> overlap between pages, because most people see more than one.
                    Read "% of all visitors who saw it" as: out of {{ number_format($totalVisitors) }} people, this share opened the page.
                </p>
            @endif
        </x-marketing.panel>

        <x-marketing.panel :title="'Visits from ads · ' . $numbersFor" :subtitle="\App\Domain\Marketing\MarketingMetric::help('paid_visits')" icon="megaphone">
            @if($adSources === [])
                <x-marketing.empty
                    icon="megaphone"
                    title="No ad clicks recorded"
                    message="A visit counts here when it arrives with gclid, fbclid or msclkid on the landing URL — which is what ties a visitor back to the ad that paid for them." />
            @else
                <x-data-table id="mkAdSources" min-width="760px">
                    <x-slot:head>
                        <tr>
                            <th class="px-4 py-3">Platform</th>
                            <th class="px-4 py-3">Campaign</th>
                            <th class="px-4 py-3">Ad / Keyword</th>
                            <x-marketing.th metric="visits" align="right" />
                            <x-marketing.th metric="visitors" align="right" />
                            <x-marketing.th metric="signups" align="right" />
                        </tr>
                    </x-slot:head>
                    @foreach($adSources as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-700 font-medium capitalize">{{ $row['platform'] }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $row['campaign'] }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $row['content'] }} / {{ $row['term'] }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['sessions'] }}">{{ number_format($row['sessions']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $row['visitors'] }}">{{ number_format($row['visitors']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums font-semibold" data-order="{{ $row['signups'] }}">{{ number_format($row['signups']) }}</td>
                        </tr>
                    @endforeach
                </x-data-table>
            @endif
        </x-marketing.panel>

        <x-marketing.panel :title="'Tracked sites for ' . $where" :subtitle="'Every site in ' . $where . ', whichever site is picked above; each reports under its own key · ' . $period" icon="globe">
            <x-slot:actions>
                <a href="{{ route('marketing.tracking') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold rounded-lg border border-emerald-500 text-emerald-700 hover:bg-emerald-50">
                    <i data-lucide="code" class="w-3.5 h-3.5"></i> Add site / get script
                </a>
            </x-slot:actions>

            @if($sites === [])
                <x-marketing.empty
                    icon="globe"
                    title="No tracked sites in these locations"
                    message="Add a site on the Tracking Script page and assign it to a location — visitors appear here as soon as the first page loads." />
            @else
                <x-data-table id="mkSites" min-width="1000px">
                    <x-slot:head>
                        <tr>
                            <th class="px-4 py-3">Site</th>
                            <th class="px-4 py-3">Domain</th>
                            <th class="px-4 py-3">Location</th>
                            <th class="px-4 py-3">Status</th>
                            <x-marketing.th metric="visitors" align="right" />
                            <x-marketing.th metric="visits" align="right" />
                            <x-marketing.th metric="signups" align="right" />
                            <x-marketing.th metric="signup_rate" align="right" />
                            <x-marketing.th metric="last_seen" />
                        </tr>
                    </x-slot:head>
                    @foreach($sites as $site)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold text-slate-800">
                                <a href="{{ route('marketing.websites', $filter->query(['site' => $site['id']])) }}"
                                    class="hover:text-emerald-700">{{ $site['name'] }}</a>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $site['domain'] }}</td>
                            <td class="px-4 py-3 {{ $site['location'] ? 'text-slate-600' : 'text-amber-700' }}">{{ $site['location'] ?? 'Not assigned' }}</td>
                            <td class="px-4 py-3">
                                @if(! $site['is_active'])
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border border-slate-200 bg-slate-50 text-slate-500">Paused</span>
                                @elseif($site['verified'])
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border border-emerald-200 bg-emerald-50 text-emerald-700">Tracking</span>
                                @else
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border border-amber-200 bg-amber-50 text-amber-700">Awaiting first event</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $site['visitors'] }}">{{ number_format($site['visitors']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $site['sessions'] }}">{{ number_format($site['sessions']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums font-semibold" data-order="{{ $site['signups'] }}">{{ number_format($site['signups']) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $site['signup_rate'] }}">{{ $site['signup_rate'] }}%</td>
                            <td class="px-4 py-3 text-slate-500" data-order="{{ $site['last_seen'] ?? '' }}">{{ $site['last_seen'] ? \Carbon\CarbonImmutable::parse($site['last_seen'])->diffForHumans() : '—' }}</td>
                        </tr>
                    @endforeach
                </x-data-table>
            @endif
        </x-marketing.panel>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tables = { mkTrafficSources: [[1, 'desc']], mkTopPages: [[1, 'desc']], mkAdSources: [[3, 'desc']], mkSites: [[5, 'desc']] };
            Object.keys(tables).forEach(function (id) {
                var table = document.getElementById(id);
                if (table) DDS.dataTable(table, { paging: false, info: false, searching: false, order: tables[id] });
            });
        });
    </script>
</x-marketing-layout>
