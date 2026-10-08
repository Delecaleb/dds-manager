{{--
  The ad accounts under one connection. Each inherits the connection's location and can be
  re-mapped to another, or switched off so it is no longer synced.
  Expects $accounts (AdCampaignReportService::accounts rows), $locations and a unique $id.
--}}
<x-data-table :id="$id" min-width="900px" data-mk-accounts>
    <x-slot:head>
        <tr>
            <th class="px-4 py-3">Account</th>
            <th class="px-4 py-3">Customer ID</th>
            <th class="px-4 py-3 text-right">Campaigns</th>
            <th class="px-4 py-3">Last Synced</th>
            <th class="px-4 py-3">Credited to</th>
            <th class="px-4 py-3">Syncing</th>
        </tr>
    </x-slot:head>
    @foreach($accounts as $account)
        <tr class="hover:bg-slate-50">
            <td class="px-4 py-3">
                <div class="text-[13px] font-semibold text-slate-800">{{ $account['name'] }}</div>
                @if($account['last_error'])
                    <div class="text-[11px] text-amber-700">{{ $account['last_error'] }}</div>
                @endif
            </td>
            <td class="px-4 py-3 text-slate-600 tabular-nums">{{ $account['external_id'] }}</td>
            <td class="px-4 py-3 text-right tabular-nums">{{ $account['campaigns'] }}</td>
            <td class="px-4 py-3 text-slate-500">{{ $account['last_synced_at'] ? \Carbon\CarbonImmutable::parse($account['last_synced_at'])->diffForHumans() : 'Not yet' }}</td>
            <td class="px-4 py-3" colspan="2">
                <form method="POST" action="{{ route('marketing.google-ads.accounts.update', $account['id']) }}" class="flex items-center gap-3">
                    @csrf @method('PATCH')
                    <select name="location" aria-label="Location" onchange="this.form.submit()"
                        class="text-[11px] rounded-lg border {{ $account['location_key'] === null ? 'border-amber-300 bg-amber-50' : 'border-slate-200 bg-white' }} px-2 py-1 cursor-pointer">
                        <option value="">Not assigned</option>
                        @foreach($locations as $key => $location)
                            <option value="{{ $key }}" @selected($account['location_key'] === $key)>{{ $location->name }}</option>
                        @endforeach
                    </select>
                    <label class="inline-flex items-center gap-1.5 text-[11px] text-slate-600 cursor-pointer">
                        <input type="hidden" name="is_enabled" value="0">
                        <input type="checkbox" name="is_enabled" value="1" @checked($account['is_enabled']) onchange="this.form.submit()" class="accent-emerald-600">
                        Sync this account
                    </label>
                </form>
            </td>
        </tr>
    @endforeach
</x-data-table>
