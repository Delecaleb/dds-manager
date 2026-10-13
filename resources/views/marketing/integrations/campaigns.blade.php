<x-marketing-layout>
    <x-slot:title>{{ $account->name ?: $account->external_id }}</x-slot:title>
    <x-slot:subtitle>Google Ads account {{ $account->external_id }} · credit each campaign to one of its locations</x-slot:subtitle>

    <div class="p-6 space-y-6 max-w-[1200px]">

        <x-marketing.flash />

        <a href="{{ route('marketing.integrations') }}" class="inline-flex items-center gap-1 text-[12px] font-semibold text-slate-600 hover:text-slate-900">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Integrations
        </a>

        <x-marketing.panel title="Campaigns"
            :subtitle="count($accountLocations) > 1
                ? 'This account serves '.count($accountLocations).' locations. A campaign counts for the location you pick; with none picked it counts for no location.'
                : 'This account has one location, so every campaign counts for it. Assign more locations on the Integrations page to split campaigns.'"
            icon="megaphone">
            @if ($campaigns === [])
                <x-marketing.empty icon="megaphone" title="No campaigns yet" message="Campaigns appear after the next sync of this account." />
            @else
                <div class="p-3">
                    <x-data-table id="mkAccountCampaigns" min-width="760px" data-mk-campaigns>
                        <x-slot:head>
                            <tr>
                                <th class="px-4 py-3">Campaign</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Spend, last {{ $spendDays }} days</th>
                                <th class="px-4 py-3">Credited to</th>
                            </tr>
                        </x-slot:head>
                        @foreach ($campaigns as $campaign)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="text-[13px] font-semibold text-slate-800">{{ $campaign['name'] }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $campaign['channel_type'] ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ ucfirst(strtolower((string) $campaign['status'])) }}</td>
                                <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $campaign['recent_spend'] }}">{{ ops_fmt($campaign['recent_spend'], 'money') }}</td>
                                <td class="px-4 py-3">
                                    @if (count($accountLocations) > 1)
                                        <form method="POST" action="{{ route('marketing.google-ads.campaigns.update', $campaign['id']) }}">
                                            @csrf @method('PATCH')
                                            <select name="location" aria-label="Credited to" onchange="this.form.submit()"
                                                class="text-[11px] rounded-lg border {{ $campaign['location_key'] === null ? 'border-amber-300 bg-amber-50' : 'border-slate-200 bg-white' }} px-2 py-1 cursor-pointer">
                                                <option value="">No location (not counted)</option>
                                                @foreach ($accountLocations as $key => $location)
                                                    <option value="{{ $key }}" @selected($campaign['location_key'] === (string) $key)>{{ $location->name }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @else
                                        <span class="text-[12px] text-slate-700">{{ array_values($accountLocations)[0]->name ?? 'No location' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </x-data-table>
                </div>
            @endif
        </x-marketing.panel>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var table = document.querySelector('[data-mk-campaigns]');
            if (table) DDS.dataTable(table, { paging: false, info: false, searching: true, order: [[2, 'desc']] });
        });
    </script>
</x-marketing-layout>
