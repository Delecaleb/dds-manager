{{--
  Every ad account, once, with the locations it is assigned to.
  Expects $accounts (AdCampaignReportService::accounts rows), $locations (ClinicRegistry) and a unique $id.
  Locations are picked in a small checklist; saving with none unassigns the account.
--}}
{{-- Phones: one card per account, with the same actions as the table. --}}
<ul class="md:hidden divide-y divide-slate-100 -mx-3">
    @foreach ($accounts as $account)
        <li class="px-4 py-3 space-y-2 {{ $account['active'] ? '' : 'opacity-60' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-[13px] font-semibold text-slate-800">{{ $account['name'] }}</div>
                    <div class="text-[11px] text-slate-500 tabular-nums break-all">{{ $account['external_id'] }}@if ($account['connection']) · via {{ $account['connection'] }}@endif</div>
                </div>
                <div class="shrink-0">
                    @switch($account['state'])
                        @case('waiting') <x-front-desk.pill label="Waiting" tone="amber" /> @break
                        @case('closed') <x-front-desk.pill :label="ucfirst(strtolower((string) $account['status']))" tone="slate" /> @break
                        @default <x-front-desk.pill label="Syncing" tone="emerald" />
                    @endswitch
                </div>
            </div>
            @if ($account['last_error'])
                <div class="text-[11px] text-amber-700">{{ $account['last_error'] }}</div>
            @endif
            @include('marketing.integrations._account-locations')
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-slate-500">
                <span>{{ $account['campaigns'] }} {{ \Illuminate\Support\Str::plural('campaign', $account['campaigns']) }}</span>
                @if (count($account['location_keys']) > 1)
                    <a href="{{ route('marketing.google-ads.accounts.campaigns', $account['id']) }}"
                        class="font-semibold {{ $account['unassigned_campaigns'] > 0 ? 'text-amber-700' : 'text-slate-600' }} hover:underline">
                        {{ $account['unassigned_campaigns'] > 0 ? $account['unassigned_campaigns'].' to assign' : 'Assign campaigns' }}
                    </a>
                @endif
                <span>History from {{ $account['history_from'] ?? '—' }}</span>
                <span>{{ $account['last_synced_at'] ? 'Synced '.\Carbon\CarbonImmutable::parse($account['last_synced_at'])->diffForHumans() : 'Not synced yet' }}</span>
            </div>
        </li>
    @endforeach
</ul>

<div class="hidden md:block">
<x-data-table :id="$id" min-width="1000px" data-mk-accounts>
    <x-slot:head>
        <tr>
            <th class="px-4 py-3">Account</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Locations</th>
            <th class="px-4 py-3 text-right">Campaigns</th>
            <th class="px-4 py-3">History from</th>
            <th class="px-4 py-3">Last Synced</th>
        </tr>
    </x-slot:head>
    @foreach ($accounts as $account)
        <tr class="hover:bg-slate-50 align-top {{ $account['active'] ? '' : 'opacity-60' }}">
            <td class="px-4 py-3">
                <div class="text-[13px] font-semibold text-slate-800">{{ $account['name'] }}</div>
                <div class="text-[11px] text-slate-500 tabular-nums">{{ $account['external_id'] }}@if ($account['connection']) · via {{ $account['connection'] }}@endif</div>
                @if ($account['last_error'])
                    <div class="text-[11px] text-amber-700">{{ $account['last_error'] }}</div>
                @endif
            </td>
            <td class="px-4 py-3" data-order="{{ ['waiting' => 0, 'assigned' => 1, 'closed' => 2][$account['state']] }}">
                @switch($account['state'])
                    @case('waiting') <x-front-desk.pill label="Waiting for a location" tone="amber" /> @break
                    @case('closed') <x-front-desk.pill :label="ucfirst(strtolower((string) $account['status']))" tone="slate" /> @break
                    @default <x-front-desk.pill label="Syncing" tone="emerald" />
                @endswitch
            </td>
            <td class="px-4 py-3">
                @include('marketing.integrations._account-locations')
            </td>
            <td class="px-4 py-3 text-right tabular-nums">
                {{ $account['campaigns'] }}
                @if (count($account['location_keys']) > 1)
                    <div>
                        <a href="{{ route('marketing.google-ads.accounts.campaigns', $account['id']) }}"
                            class="text-[11px] font-semibold {{ $account['unassigned_campaigns'] > 0 ? 'text-amber-700' : 'text-slate-600' }} hover:underline">
                            {{ $account['unassigned_campaigns'] > 0 ? $account['unassigned_campaigns'].' to assign' : 'Assign campaigns' }}
                        </a>
                    </div>
                @endif
            </td>
            <td class="px-4 py-3 text-slate-500 tabular-nums">{{ $account['history_from'] ?? '—' }}</td>
            <td class="px-4 py-3 text-slate-500" data-order="{{ $account['last_synced_at'] ?? '' }}">{{ $account['last_synced_at'] ? \Carbon\CarbonImmutable::parse($account['last_synced_at'])->diffForHumans() : 'Not yet' }}</td>
        </tr>
    @endforeach
</x-data-table>
</div>
