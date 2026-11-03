<x-marketing-layout>
    <x-slot:title>Integrations</x-slot:title>
    <x-slot:subtitle>Connect Google Ads and Meta Ads once, then assign each ad account to the locations it serves</x-slot:subtitle>

    @php
        $pill = fn (\App\Models\MarketingAdConnection $c) => match (true) {
            $c->hasUnreadableToken() => ['Needs reconnecting', 'amber'],
            ! $c->isUsable() => ['Disconnected', 'slate'],
            $c->status === \App\Models\MarketingAdConnection::STATUS_ERROR => ['Needs attention', 'amber'],
            default => ['Connected', 'emerald'],
        };
        // One accounts table per platform, in the same order as the connection panels above.
        $accountPlatforms = [
            [
                'provider' => \App\Models\MarketingAdConnection::PROVIDER_GOOGLE_ADS,
                'icon' => 'search',
                'empty' => 'Accounts appear here once a Google Ads login is connected.',
                'history_days' => (int) config('marketing.google_ads.history_days', 90),
            ],
            [
                'provider' => \App\Models\MarketingAdConnection::PROVIDER_META_ADS,
                'icon' => 'thumbs-up',
                'empty' => 'Accounts appear here once a Meta system user is connected.',
                'history_days' => (int) config('marketing.meta_ads.history_days', 90),
            ],
        ];
        foreach ($accountPlatforms as &$platform) {
            $platform['name'] = \App\Models\MarketingAdConnection::PLATFORMS[$platform['provider']];
            $platform['accounts'] = array_values(array_filter($accounts, fn ($a) => $a['provider'] === $platform['provider']));
        }
        unset($platform);
        $googleConnections = array_values(array_filter($connections, fn ($c) => $c->provider === \App\Models\MarketingAdConnection::PROVIDER_GOOGLE_ADS));
        $metaConnections = array_values(array_filter($connections, fn ($c) => $c->provider === \App\Models\MarketingAdConnection::PROVIDER_META_ADS));
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

            @if ($googleConnections === [])
                <x-marketing.empty icon="plug" title="No Google Ads login connected"
                    message="Connect with a Google login that can see your ad accounts. Every account it can see is listed below, once, for you to assign." />
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($googleConnections as $c)
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

        {{-- Meta system users: a token pasted once; like a Google login, it belongs to no location. --}}
        <x-marketing.panel title="Meta Ads system users" subtitle="Read-only access to Facebook and Instagram campaigns, spend, link clicks and leads. A system user belongs to no location." icon="thumbs-up">
            @if ($metaConnections !== [])
                <ul class="divide-y divide-slate-100 mb-5 border-b border-slate-100">
                    @foreach ($metaConnections as $c)
                        @php [$label, $tone] = $pill($c); @endphp
                        <li class="px-5 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-[13px] font-semibold text-slate-800 truncate">{{ $c->label() }}</span>
                                    <x-front-desk.pill :label="$label" :tone="$tone" />
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $c->accounts_count }} {{ \Illuminate\Support\Str::plural('account', $c->accounts_count) }} read through this system user ·
                                    {{ $c->last_synced_at ? 'last synced '.$c->last_synced_at->diffForHumans() : 'not synced yet' }}
                                    @if ($c->connected_at) · connected {{ $c->connected_at->diffForHumans() }} @endif
                                </p>
                                @if ($c->hasUnreadableToken())
                                    <p class="mt-1 text-xs text-amber-700">Its stored token cannot be read on this server (it was saved under a different app key). Paste the token again below.</p>
                                @elseif ($c->last_error)
                                    <p class="mt-1 text-xs text-amber-700">{{ $c->last_error }}</p>
                                @endif
                            </div>
                            @if ($c->isUsable())
                                <form method="POST" action="{{ route('marketing.meta-ads.disconnect', $c) }}" class="shrink-0"
                                    onsubmit="return confirm(@js('Disconnect '.$c->label().'? Imported campaign history is kept.'));">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 text-[11px] font-bold rounded-lg border border-slate-200 text-slate-600 bg-white hover:bg-slate-50">Disconnect</button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <form method="POST" action="{{ route('marketing.meta-ads.store') }}" class="space-y-2">
                    @csrf
                    <label for="metaToken" class="block text-[12px] font-bold text-slate-900">
                        {{ $metaConnections === [] ? 'System user token' : 'Add a system user, or replace a token' }}
                    </label>
                    <input id="metaToken" name="token" type="password" autocomplete="off" required minlength="20"
                        placeholder="EAAG…"
                        class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 font-mono">
                    @error('token') <p class="text-[11px] text-rose-700">{{ $message }}</p> @enderror
                    <button type="submit" class="px-3 py-1.5 text-[11px] font-bold rounded-lg border border-[#00bfa5] text-white bg-[#00bfa5] hover:opacity-90">Connect Meta Ads</button>
                    <p class="text-[11px] text-slate-500">The token is checked with Meta, then stored encrypted. Saving the same system user's token again replaces the old one.</p>
                </form>

                <details class="text-[11px] text-slate-600" @if ($metaConnections === []) open @endif>
                    <summary class="cursor-pointer text-[12px] font-bold text-slate-900">How to get a token (about 15 minutes, once)</summary>
                    <ol class="mt-2 space-y-1.5 list-decimal pl-4 leading-relaxed">
                        <li>At <span class="font-semibold">developers.facebook.com</span> → My Apps → Create app → type <span class="font-semibold">Business</span>, owned by your Business Manager. Add the <span class="font-semibold">Marketing API</span> product.</li>
                        <li>In <span class="font-semibold">business.facebook.com</span> → Business Settings → Users → <span class="font-semibold">System users</span> → Add. Role: Employee is enough.</li>
                        <li>Select the system user → <span class="font-semibold">Assign assets</span> → Ad accounts → tick each clinic's ad account with <span class="font-semibold">View performance</span>. Also assign the app from step 1.</li>
                        <li><span class="font-semibold">Generate new token</span> → choose the app → expiration <span class="font-semibold">Never</span> → permissions <span class="font-semibold">ads_read</span> (and read_insights if offered) → Generate.</li>
                        <li>Paste the token here. Every ad account it can see is listed under Meta Ads accounts below. Assign each to its location; nothing syncs until you do.</li>
                    </ol>
                    <p class="mt-2 text-slate-500">If the app has "Require app secret" switched on, set <code class="text-[10px]">META_ADS_APP_SECRET</code> on the server first.</p>
                </details>
            </div>
        </x-marketing.panel>

        {{-- Ad accounts, one table per platform. Nothing is synced or reported until an account has a location. --}}
        @foreach ($accountPlatforms as $platform)
            @php $waiting = count(array_filter($platform['accounts'], fn ($a) => $a['state'] === 'waiting')); @endphp
            <x-marketing.panel :title="$platform['name'].' accounts'"
                :subtitle="$waiting > 0 ? $waiting.' waiting for a location — not synced until assigned. Leave other businesses\' accounts unassigned.' : 'Each account is synced and reported for the locations it is assigned to.'"
                :icon="$platform['icon']">
                @if ($platform['accounts'] === [])
                    <x-marketing.empty :icon="$platform['icon']" :title="'No '.$platform['name'].' accounts yet'" :message="$platform['empty']" />
                @else
                    <div class="p-3">
                        @include('marketing.integrations._accounts', ['accounts' => $platform['accounts'], 'locations' => $locations, 'id' => 'mkAccounts-'.$platform['provider']])
                    </div>
                    <p class="px-5 pb-4 text-[11px] text-slate-500">
                        One location: every campaign counts for it. Several locations: assign each campaign to one of them — until then it counts for none.
                        History is pulled from {{ $platform['history_days'] }} days before an account was first connected.
                    </p>
                @endif
            </x-marketing.panel>
        @endforeach
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-mk-accounts]').forEach(function (table) {
                DDS.dataTable(table, { paging: false, info: false, searching: true });
            });
        });
    </script>
</x-marketing-layout>
