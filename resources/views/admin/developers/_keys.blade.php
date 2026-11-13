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
                @foreach ([\App\Models\ApiKey::ABILITY_PATIENT_CONTACT => '+ patient contact', \App\Models\ApiKey::ABILITY_PATIENT_MATCH => '+ patient matching', \App\Models\ApiKey::ABILITY_PROCEDURES => '+ procedures'] as $ability => $label)
                    @if ($key->allows($ability))
                        <span class="ml-1 px-1.5 py-0.5 rounded bg-amber-50 border border-amber-200 text-amber-800">{{ $label }}</span>
                    @endif
                @endforeach
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
                <div class="relative flex justify-end">
                    <button type="button" data-kebab-trigger
                        class="w-7 h-7 flex items-center justify-center rounded-md text-slate-400 hover:text-slate-700 hover:bg-slate-100 focus:outline-none"
                        aria-haspopup="true" aria-expanded="false" aria-label="More actions">
                        <i data-lucide="more-vertical" class="w-4 h-4 pointer-events-none"></i>
                    </button>
                    <div data-kebab-menu class="hidden absolute right-0 top-8 z-30 w-48 bg-white rounded-xl border border-slate-200 shadow-lg overflow-hidden" role="menu">
                        @if ($developer && $active)
                            <button type="button" role="menuitem" data-key-action="reveal"
                                data-url="{{ route('developer.keys.reveal', $key) }}"
                                data-key-name="{{ $key->name }}"
                                class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 text-[12px] text-slate-700 hover:bg-slate-50">
                                <i data-lucide="copy" class="w-3.5 h-3.5 shrink-0 text-slate-400 pointer-events-none"></i>Copy key
                            </button>
                        @endif
                        @if ($developer && $key->revoked_at === null)
                            <button type="button" role="menuitem" data-key-action="regenerate"
                                data-url="{{ route('developer.keys.regenerate', $key) }}"
                                data-key-id="{{ $key->id }}"
                                data-key-name="{{ $key->name }}"
                                class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 text-[12px] text-amber-700 hover:bg-amber-50">
                                <i data-lucide="refresh-cw" class="w-3.5 h-3.5 shrink-0 text-amber-400 pointer-events-none"></i>Regenerate
                            </button>
                        @endif
                        <a href="{{ route($routes.'show', $key) }}" role="menuitem"
                            class="flex items-center gap-2.5 px-4 py-2.5 text-[12px] text-slate-700 hover:bg-slate-50">
                            <i data-lucide="activity" class="w-3.5 h-3.5 shrink-0 text-slate-400"></i>Activity
                        </a>
                        @if ($developer && $key->revoked_at === null)
                            <button type="button" role="menuitem" data-key-action="permissions"
                                data-key-id="{{ $key->id }}"
                                data-key-name="{{ $key->name }}"
                                data-key-abilities="{{ json_encode($key->abilities) }}"
                                data-url="{{ route('developer.keys.updatePermissions', $key) }}"
                                class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 text-[12px] text-slate-700 hover:bg-slate-50">
                                <i data-lucide="shield" class="w-3.5 h-3.5 shrink-0 text-slate-400 pointer-events-none"></i>Edit permissions
                            </button>
                        @endif
                        @if ($key->revoked_at === null)
                            <div class="border-t border-slate-100 mx-3 my-1"></div>
                            <form method="POST" action="{{ route($routes.'destroy', $key) }}">
                                @csrf @method('DELETE')
                                <button type="submit" role="menuitem"
                                    onclick="return confirm(@js('Revoke '.$key->name.'? Anything using it stops working immediately.'))"
                                    class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 text-[12px] text-rose-700 hover:bg-rose-50">
                                    <i data-lucide="ban" class="w-3.5 h-3.5 shrink-0 text-rose-400 pointer-events-none"></i>Revoke
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    @endforeach
</x-data-table>

@once
<script>
(function () {
    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-kebab-trigger]');
        document.querySelectorAll('[data-kebab-menu]:not(.hidden)').forEach(function (menu) {
            if (!trigger || !menu.parentElement.contains(trigger)) {
                menu.classList.add('hidden');
                var t = menu.parentElement.querySelector('[data-kebab-trigger]');
                if (t) t.setAttribute('aria-expanded', 'false');
            }
        });
        if (trigger) {
            var menu = trigger.parentElement.querySelector('[data-kebab-menu]');
            if (menu) {
                var nowOpen = menu.classList.toggle('hidden') === false;
                trigger.setAttribute('aria-expanded', nowOpen ? 'true' : 'false');
            }
        }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('[data-kebab-menu]:not(.hidden)').forEach(function (menu) {
            menu.classList.add('hidden');
            var t = menu.parentElement.querySelector('[data-kebab-trigger]');
            if (t) { t.setAttribute('aria-expanded', 'false'); t.focus(); }
        });
    });
})();
</script>
@endonce
