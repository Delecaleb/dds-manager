{{--
  Every ad account, once, with the locations it is assigned to.
  Expects $accounts (AdCampaignReportService::accounts rows), $locations (ClinicRegistry) and a unique $id.
  Locations are picked in a small checklist; saving with none unassigns the account.
--}}
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
                @if (! $account['active'])
                    <span class="text-[11px] text-slate-500">Closed at Google — not synced</span>
                @else
                    <details class="relative">
                        <summary class="cursor-pointer list-none text-[11px] rounded-lg border {{ $account['location_keys'] === [] ? 'border-amber-300 bg-amber-50 text-amber-900' : 'border-slate-200 bg-white text-slate-700' }} px-2 py-1 inline-flex items-center gap-1.5">
                            {{ $account['locations'] === [] ? 'Assign locations…' : implode(', ', $account['locations']) }}
                            <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400"></i>
                        </summary>
                        <form method="POST" action="{{ route('marketing.google-ads.accounts.update', $account['id']) }}"
                            class="absolute z-20 mt-1 w-64 bg-white border border-slate-200 rounded-lg shadow-lg p-3 space-y-2">
                            @csrf @method('PATCH')
                            <div class="max-h-56 overflow-y-auto space-y-1">
                                @foreach ($locations as $key => $location)
                                    <label class="flex items-center gap-2 text-[12px] text-slate-700 cursor-pointer">
                                        <input type="checkbox" name="locations[]" value="{{ $key }}" @checked(in_array((string) $key, $account['location_keys'], true)) class="accent-emerald-600">
                                        {{ $location->name }}
                                    </label>
                                @endforeach
                            </div>
                            <button type="submit" class="w-full px-3 py-1.5 text-[11px] font-bold rounded-lg bg-[#001f3f] text-white hover:bg-[#002b57]">Save locations</button>
                            <p class="text-[10px] text-slate-500">None ticked = not synced or reported.</p>
                        </form>
                    </details>
                @endif
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
