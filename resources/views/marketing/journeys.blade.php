<x-marketing-layout>
    <x-slot:title>Visitor Journeys</x-slot:title>
    <x-slot:subtitle>One visitor, first visit to signup</x-slot:subtitle>

    <div class="p-6 space-y-6 max-w-[1500px]">

        <x-marketing.panel title="Find a journey" subtitle="Search by email, phone, name or visitor id" icon="search">
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <input type="search" name="q" value="{{ $search }}" placeholder="patient@example.com, 313 555 0142, or a visitor id"
                    class="flex-1 min-w-[260px] text-xs rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:border-emerald-500">
                <button type="submit"
                    class="px-3 py-2 text-[11px] font-bold rounded-lg border border-emerald-500 bg-emerald-600 text-white hover:bg-emerald-700 cursor-pointer">
                    Search
                </button>
                @if($search !== '')
                    <a href="{{ route('marketing.journeys') }}" class="text-[11px] font-bold text-slate-500 hover:text-slate-800">Clear</a>
                @endif
            </form>
            <p class="mt-2 text-[11px] text-slate-500">
                A visitor is anonymous until they sign up. From that moment their earlier visits belong to them,
                so the journey starts before the practice knew who they were.
            </p>
        </x-marketing.panel>

        @if($journey)
            @php $v = $journey['visitor']; @endphp
            <x-marketing.panel :title="$v['name'] ?: ($v['email'] ?: ($v['phone'] ?: 'Anonymous visitor'))"
                :subtitle="$v['site'] . ' · first seen ' . $v['first_seen_at']" icon="route">
                <x-slot:actions>
                    <a href="{{ route('marketing.journeys', ['q' => $search]) }}" class="text-[11px] font-bold text-slate-500 hover:text-slate-800">Close</a>
                </x-slot:actions>

                <dl class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
                    @foreach ([
                        ['First touch', $v['first_source']],
                        ['Campaign', $v['first_campaign'] ?: '—'],
                        ['Landed on', $v['first_landing_path'] ?: '—'],
                        ['Ad click', $v['first_click_id'] ? ucfirst((string) $v['first_click_source']) : '—'],
                        ['Email', $v['email'] ?: '—'],
                        ['Phone', $v['phone'] ?: '—'],
                        ['Signed up', $v['identified_at'] ?: 'Not yet'],
                        ['Last seen', $v['last_seen_at']],
                    ] as [$label, $value])
                        <div class="border border-slate-200 rounded-lg px-3 py-2">
                            <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">{{ $label }}</dt>
                            <dd class="text-[12px] text-slate-800 font-semibold truncate">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>

                <ol class="relative border-l border-slate-200 ml-3 space-y-4">
                    @foreach($journey['events'] as $event)
                        <li class="ml-6">
                            <span class="absolute -left-3 flex items-center justify-center w-6 h-6 rounded-full bg-white border {{ $event['type'] === 'signup' ? 'border-emerald-300 text-emerald-600' : 'border-slate-200 text-slate-400' }}">
                                <i data-lucide="{{ $event['type'] === 'signup' ? 'user-plus' : 'file-text' }}" class="w-3 h-3"></i>
                            </span>
                            <div class="flex flex-wrap items-baseline gap-2">
                                <h3 class="text-[13px] font-bold text-slate-900">
                                    {{ $event['type'] === 'signup' ? 'Signed up' : ($event['title'] ?: $event['path']) }}
                                </h3>
                                <span class="text-[10px] font-semibold text-slate-400">{{ $event['occurred_at'] }}</span>
                            </div>
                            <p class="text-[11px] text-slate-600 mt-0.5">
                                {{ $event['path'] }} · {{ $event['source'] }}@if($event['campaign']) · {{ $event['campaign'] }}@endif
                            </p>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $event['device'] }} · {{ $event['browser'] }}</p>
                        </li>
                    @endforeach
                </ol>
            </x-marketing.panel>
        @endif

        <x-marketing.panel
            :title="$search !== '' ? 'Search results' : 'Recent journeys'"
            :subtitle="$search !== '' ? 'Matching ' . $search : $start . ' → ' . $end . ', newest first'"
            icon="route">
            <div class="overflow-x-auto">
                <table class="dds-table w-full text-left text-xs whitespace-nowrap">
                    <thead>
                        <tr>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Visitor</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Site</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">First touch</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Campaign</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900 text-right">Visits</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900 text-right">Events</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">First seen</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Last seen</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($journeys as $row)
                            <tr>
                                <td class="py-3 px-4 font-semibold text-slate-800">
                                    {{ $row['who'] }}
                                    @if($row['identified'])
                                        <span class="ml-1.5 text-[9px] font-bold px-1.5 py-0.5 rounded-full border border-emerald-200 bg-emerald-50 text-emerald-700">Lead</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-slate-600">{{ $row['site'] }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $row['source'] }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $row['campaign'] ?: '—' }}</td>
                                <td class="py-3 px-4 text-right tabular-nums">{{ $row['sessions'] }}</td>
                                <td class="py-3 px-4 text-right tabular-nums">{{ $row['events'] }}</td>
                                <td class="py-3 px-4 text-slate-500">{{ $row['first_seen'] }}</td>
                                <td class="py-3 px-4 text-slate-500">{{ $row['last_seen'] }}</td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('marketing.journeys', ['visitor' => $row['id'], 'q' => $search]) }}"
                                        class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800">Timeline →</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-0">
                                    <x-marketing.empty
                                        icon="route"
                                        :title="$search !== '' ? 'No matching visitors' : 'No journeys recorded yet'"
                                        message="Journeys appear as soon as a tracked site reports its first page view." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-marketing.panel>

        <x-marketing.panel title="How a visitor becomes a patient record" subtitle="The identity chain, and where it can break" icon="link">
            <div class="overflow-x-auto">
                <table class="dds-table w-full text-left text-xs whitespace-nowrap">
                    <thead>
                        <tr>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Stage</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Identified by</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Breaks when</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Built?</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ([
                            ['Anonymous visitor', 'First-party id in browser storage + site key', 'Storage cleared, or a different device or browser', true],
                            ['Known lead', 'Email or phone given at signup', 'Typo in the details, or a shared family phone', true],
                            ['Patient', 'Match to the OpenDental patient record', 'Name differs, or a family member books instead', false],
                            ['Production', 'Completed procedures for that patient', 'Treatment falls outside the attribution window', false],
                        ] as [$stage, $by, $breaks, $done])
                            <tr>
                                <td class="py-3 px-4 font-semibold text-slate-800">{{ $stage }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $by }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $breaks }}</td>
                                <td class="py-3 px-4">
                                    @if($done)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border border-emerald-200 bg-emerald-50 text-emerald-700">Working</span>
                                    @else
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border border-amber-200 bg-amber-50 text-amber-700">Next</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-marketing.panel>
    </div>
</x-marketing-layout>
