<x-marketing-layout>
    <x-slot:title>Integrations</x-slot:title>
    <x-slot:subtitle>Connect Google Ads once, then assign each ad account to the locations it serves</x-slot:subtitle>

    @php
        $pill = fn (\App\Models\MarketingAdConnection $c) => match (true) {
            $c->hasUnreadableToken() => ['Needs reconnecting', 'amber'],
            ! $c->isUsable() => ['Disconnected', 'slate'],
            $c->status === \App\Models\MarketingAdConnection::STATUS_ERROR => ['Needs attention', 'amber'],
            default => ['Connected', 'emerald'],
        };
        $waiting = count(array_filter($accounts, fn ($a) => $a['state'] === 'waiting'));
    @endphp

    <div class="p-4 sm:p-6 space-y-6 max-w-[1500px]">

        <x-marketing.flash />

        <div class="text-[11px] text-slate-500 space-y-1">
            @if ($googleAdsMissing !== [])
                <p class="text-amber-700">Google Ads cannot be connected until these are set on the server: <code class="text-[10px]">{{ implode(', ', $googleAdsMissing) }}</code></p>
            @endif
            <p>OAuth redirect URI to register on the Google Cloud OAuth client: <code class="text-[10px] break-all select-all">{{ $googleAdsCallbackUrl }}</code></p>
        </div>

        {{-- Google logins: they only give access; they belong to no location. --}}
        <x-marketing.panel title="Google Ads logins" subtitle="Read-only access to campaigns, spend, clicks and conversions. A login belongs to no location." icon="search">
            <x-slot:actions>
                @if ($googleAdsMissing === [])
                    <a href="{{ route('marketing.google-ads.connect') }}"
                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg border border-[#00bfa5] text-white bg-[#00bfa5] hover:opacity-90">Connect Google Ads</a>
                @endif
            </x-slot:actions>

            @if ($connections === [])
                <x-marketing.empty icon="plug" title="No Google Ads login connected"
                    message="Connect with a Google login that can see your ad accounts. Every account it can see is listed below, once, for you to assign." />
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($connections as $c)
                        @php [$label, $tone] = $pill($c); @endphp
                        <li class="px-5 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-[13px] font-semibold text-slate-800 truncate">{{ $c->label() }}</span>
                                    <x-front-desk.pill :label="$label" :tone="$tone" />
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $c->accounts_count }} {{ \Illuminate\Support\Str::plural('account', $c->accounts_count) }} read through this login ·
                                    {{ $c->last_synced_at ? 'last synced '.$c->last_synced_at->diffForHumans() : 'not synced yet' }}
                                    @if ($c->connected_at) · connected {{ $c->connected_at->diffForHumans() }} @endif
                                </p>
                                @if ($c->hasUnreadableToken())
                                    <p class="mt-1 text-xs text-amber-700">Its stored Google credentials cannot be read on this server (they were saved under a different app key). Reconnect this login.</p>
                                @elseif ($c->last_error)
                                    <p class="mt-1 text-xs text-amber-700">{{ $c->last_error }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                @if ($googleAdsMissing === [])
                                    <a href="{{ route('marketing.google-ads.connect') }}" class="px-2.5 py-1 text-[11px] font-bold rounded-lg border border-slate-200 text-slate-600 bg-white hover:bg-slate-50">Reconnect</a>
                                @endif
                                @if ($c->isUsable())
                                    <form method="POST" action="{{ route('marketing.google-ads.disconnect', $c) }}"
                                        onsubmit="return confirm(@js('Disconnect '.$c->label().'? Imported campaign history is kept.'));">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 text-[11px] font-bold rounded-lg border border-slate-200 text-slate-600 bg-white hover:bg-slate-50">Disconnect</button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-marketing.panel>

        {{-- Every ad account once. Nothing is synced or reported until it has a location. --}}
        <x-marketing.panel title="Ad accounts"
            :subtitle="$waiting > 0 ? $waiting.' waiting for a location — not synced until assigned. Leave other businesses\' accounts unassigned.' : 'Each account is synced and reported for the locations it is assigned to.'"
            icon="briefcase">
            @if ($accounts === [])
                <x-marketing.empty icon="briefcase" title="No ad accounts yet" message="Accounts appear here once a Google Ads login is connected." />
            @else
                <div class="p-3">
                    @include('marketing.integrations._accounts', ['accounts' => $accounts, 'locations' => $locations, 'id' => 'mkAccounts'])
                </div>
                <p class="px-5 pb-4 text-[11px] text-slate-500">
                    One location: every campaign counts for it. Several locations: assign each campaign to one of them — until then it counts for none.
                    History is pulled from 90 days before an account was first connected.
                </p>
            @endif
        </x-marketing.panel>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-mk-accounts]').forEach(function (table) {
                DDS.dataTable(table, { paging: false, info: false, searching: true });
            });
        });
    </script>
</x-marketing-layout>
