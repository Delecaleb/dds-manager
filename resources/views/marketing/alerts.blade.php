<x-marketing-layout>
    <x-slot:title>Alerts</x-slot:title>
    <x-slot:subtitle>What needs a decision from a person</x-slot:subtitle>

    <div class="p-6 space-y-6 max-w-[1500px]">

        <div class="grid grid-cols-3 gap-3">
            <x-marketing.stat label="Critical" icon="octagon-alert" tone="rose" hint="Money is being wasted now" />
            <x-marketing.stat label="Warning" icon="triangle-alert" tone="amber" hint="Trending the wrong way" />
            <x-marketing.stat label="Resolved (30 days)" icon="circle-check-big" tone="emerald" />
        </div>

        <x-marketing.panel title="Open alerts" subtitle="Newest first" icon="bell-ring">
            <x-slot:actions>
                <a href="{{ route('marketing.settings') }}" class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800">Thresholds →</a>
            </x-slot:actions>

            <x-marketing.empty
                icon="bell-off"
                title="No open alerts"
                message="Alerts appear when a campaign breaches a threshold, a lead goes unworked, or an automation fails."
                waiting-on="Campaign tracking" />
        </x-marketing.panel>

        <x-marketing.panel title="What raises an alert" subtitle="Each rule needs a threshold and an owner before it can fire" icon="list-checks">
            <div class="overflow-x-auto">
                <table class="dds-table w-full text-left text-xs whitespace-nowrap">
                    <thead>
                        <tr>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Rule</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Fires when</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Severity</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Notifies</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ([
                            ['Campaign underperforming', 'Cost per booked appointment over target for N days', 'Warning', 'Campaign owner'],
                            ['Spend with no bookings', 'Spend over a set amount with zero booked appointments', 'Critical', 'Campaign owner'],
                            ['Lead unworked', 'A lead has had no follow-up within the agreed time', 'Warning', 'Front desk'],
                            ['Automation failed', 'A text, call or budget change did not go through', 'Critical', 'System owner'],
                            ['Tracking broken', 'A channel stopped reporting clicks or a tracked number went quiet', 'Critical', 'System owner'],
                        ] as [$rule, $when, $severity, $who])
                            <tr>
                                <td class="py-3 px-4 font-semibold text-slate-800">{{ $rule }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $when }}</td>
                                <td class="py-3 px-4">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $severity === 'Critical' ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
                                        {{ $severity }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-600">{{ $who }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-4 text-[11px] text-slate-500">
                The brief names one recipient for campaign alerts. Owners are set per rule on the Settings page so the list survives people changing roles.
            </p>
        </x-marketing.panel>
    </div>
</x-marketing-layout>
