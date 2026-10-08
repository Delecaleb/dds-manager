<x-marketing-layout>
    <x-slot:title>Leads</x-slot:title>
    <x-slot:subtitle>{{ $filter->locationLabel() }} · {{ $filter->start }} → {{ $filter->end }}</x-slot:subtitle>

    @php
        $when = fn (?string $t) => $t ? \Carbon\CarbonImmutable::parse($t)->format('M j, g:i A') : '—';
    @endphp

    <div class="p-6 space-y-6 max-w-[1500px]">

        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
            <x-marketing.stat label="Leads" icon="user-plus" tone="emerald" :value="number_format($summary['leads'])" hint="Visitors who gave contact details in the period" />
            <x-marketing.stat label="With Email" icon="mail" :value="number_format($summary['with_email'])" />
            <x-marketing.stat label="With Phone" icon="phone" :value="number_format($summary['with_phone'])" />
            <x-marketing.stat label="From Ads" icon="megaphone" :value="number_format($summary['paid'])" hint="First visit arrived with an ad click id" />
            <x-marketing.stat label="Returning Visitors" icon="repeat" :value="number_format($summary['returning'])" hint="Visited more than once before signing up" />
        </div>

        <x-marketing.panel title="Lead list" subtitle="Newest first. Each lead stays linked to the visit that first brought them." icon="users">
            @if($leads === [])
                <x-marketing.empty
                    icon="user-plus"
                    title="No leads in this period"
                    message="A lead is created when a tracked site calls dds('signup', …) on a form submit. The Tracking Script page shows the one line to add." />
            @else
                <x-data-table id="mkLeads" min-width="1200px">
                    <x-slot:head>
                        <tr>
                            <th class="px-4 py-3">Lead</th>
                            <th class="px-4 py-3">Contact</th>
                            <th class="px-4 py-3">Location</th>
                            <th class="px-4 py-3">Site</th>
                            <th class="px-4 py-3">Channel</th>
                            <th class="px-4 py-3">First Touch</th>
                            <th class="px-4 py-3">Campaign</th>
                            <th class="px-4 py-3 text-right">Visits</th>
                            <th class="px-4 py-3">Signed Up</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </x-slot:head>
                    @foreach($leads as $lead)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-[13px] font-semibold text-slate-800">
                                {{ $lead['who'] }}
                                @if($lead['paid'])
                                    <x-front-desk.pill label="Ad" tone="violet" class="ml-1" />
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                @if($lead['email'])<div>{{ $lead['email'] }}</div>@endif
                                @if($lead['phone'])<div class="tabular-nums">{{ ops_fmt($lead['phone'], 'phone') }}</div>@endif
                            </td>
                            <td class="px-4 py-3 {{ $lead['location'] ? 'text-slate-600' : 'text-amber-700' }}">{{ $lead['location'] ?? 'Not assigned' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $lead['site'] }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $lead['channel'] }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $lead['source'] }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $lead['campaign'] ?: '—' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $lead['sessions'] }}">{{ $lead['sessions'] }}</td>
                            <td class="px-4 py-3 text-slate-500" data-order="{{ $lead['identified_at'] }}">{{ $when($lead['identified_at']) }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('marketing.journeys', $filter->query(['visitor' => $lead['id']])) }}"
                                    class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800">Timeline →</a>
                            </td>
                        </tr>
                    @endforeach
                </x-data-table>
            @endif
        </x-marketing.panel>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var table = document.getElementById('mkLeads');
            if (table) DDS.dataTable(table, { pageLength: 50, order: [[8, 'desc']] });
        });
    </script>
</x-marketing-layout>
