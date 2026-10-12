{{--
  API keys table, shared by Administration → Developer API and the Developer Portal.
  Expects $keys (ApiKey models with requests_count), a unique $id and $mode:
    'admin'     — shows each key's owner; actions: Activity, Revoke
    'developer' — shows each key's location; actions: Copy key, Regenerate (both ask for
                  the password, see developer/index), Activity, Revoke
  In developer mode it also expects $locationNames (key id => location name).
--}}
@php $developer = $mode === 'developer'; @endphp
<x-data-table :id="$id" min-width="980px" data-dev-keys>
    <x-slot:head>
        <tr>
            <th class="px-4 py-3">Name</th>
            <th class="px-4 py-3">{{ $developer ? 'Location' : 'Owner' }}</th>
            <th class="px-4 py-3">Key</th>
            <th class="px-4 py-3">Permissions</th>
            <th class="px-4 py-3 text-right">Limit / min</th>
            <th class="px-4 py-3">Last Used</th>
            <th class="px-4 py-3 text-right">Requests</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3"></th>
        </tr>
    </x-slot:head>
    @foreach ($keys as $key)
        @php
            $routes = $developer ? 'developer.keys.' : 'admin.developers.keys.';
            $active = $key->isActive();
        @endphp
        <tr class="hover:bg-slate-50">
            <td class="px-4 py-3">
                <div class="text-[13px] font-semibold text-slate-800">{{ $key->name }}</div>
                <div class="text-[11px] text-slate-500">Created {{ $key->created_at->format('M j, Y') }}</div>
            </td>
            <td class="px-4 py-3 text-[12px] text-slate-700">
                @if ($developer)
                    {{ $locationNames[$key->id] ?? '—' }}
                @else
                    <div>{{ $key->creator?->name ?? 'Deleted user' }}</div>
                    @if ($key->creator?->email)
                        <div class="text-[11px] text-slate-500">{{ $key->creator->email }}</div>
                    @endif
                @endif
            </td>
            <td class="px-4 py-3 font-mono text-[11px] text-slate-600" data-key-masked="{{ $key->id }}">{{ $key->maskedKey() }}</td>
            <td class="px-4 py-3 text-[11px] text-slate-600">
                Bookings
                @if ($key->allows(\App\Models\ApiKey::ABILITY_PATIENT_CONTACT))
                    <span class="ml-1 px-1.5 py-0.5 rounded bg-amber-50 border border-amber-200 text-amber-800">+ patient contact</span>
                @endif
            </td>
            <td class="px-4 py-3 text-right tabular-nums">{{ $key->rate_limit_per_minute }}</td>
            <td class="px-4 py-3 text-slate-500" data-order="{{ $key->last_used_at?->timestamp ?? 0 }}">{{ $key->last_used_at?->diffForHumans() ?? 'Never' }}</td>
            <td class="px-4 py-3 text-right tabular-nums">{{ number_format($key->requests_count) }}</td>
            <td class="px-4 py-3">
                @if ($key->revoked_at)
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">Revoked</span>
                @elseif (! $active)
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200">Expired</span>
                @else
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                    @if ($key->expires_at)
                        <div class="text-[10px] text-slate-500 mt-0.5">until {{ $key->expires_at->format('M j, Y') }}</div>
                    @endif
                @endif
            </td>
            <td class="px-4 py-3">
                <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                    @if ($developer && $active)
                        <button type="button" data-key-action="reveal" data-url="{{ route('developer.keys.reveal', $key) }}" data-key-name="{{ $key->name }}"
                            class="px-2.5 py-1 text-[11px] font-bold rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50">Copy key</button>
                    @endif
                    @if ($developer && $key->revoked_at === null)
                        <button type="button" data-key-action="regenerate" data-url="{{ route('developer.keys.regenerate', $key) }}" data-key-id="{{ $key->id }}" data-key-name="{{ $key->name }}"
                            class="px-2.5 py-1 text-[11px] font-bold rounded-lg border border-amber-200 text-amber-800 bg-white hover:bg-amber-50">Regenerate</button>
                    @endif
                    <a href="{{ route($routes.'show', $key) }}" class="px-2.5 py-1 text-[11px] font-bold rounded-lg border border-slate-200 text-slate-600 bg-white hover:bg-slate-50">Activity</a>
                    @if ($key->revoked_at === null)
                        <form method="POST" action="{{ route($routes.'destroy', $key) }}"
                            onsubmit="return confirm(@js('Revoke '.$key->name.'? Anything using it stops working immediately.'));">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-2.5 py-1 text-[11px] font-bold rounded-lg border border-rose-200 text-rose-700 bg-white hover:bg-rose-50">Revoke</button>
                        </form>
                    @endif
                </div>
            </td>
        </tr>
    @endforeach
</x-data-table>
