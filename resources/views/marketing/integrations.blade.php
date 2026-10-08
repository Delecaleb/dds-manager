<x-marketing-layout>
    <x-slot:title>Integrations</x-slot:title>
    <x-slot:subtitle>{{ $filter->locationLabel() }} · each location connects its own ad accounts</x-slot:subtitle>

    @php
        $pill = fn (?\App\Models\MarketingAdConnection $c) => match (true) {
            $c === null || ! $c->isUsable() => ['Not connected', 'slate'],
            $c->status === \App\Models\MarketingAdConnection::STATUS_ERROR => ['Needs attention', 'amber'],
            default => ['Connected', 'emerald'],
        };
    @endphp

    <div class="p-6 space-y-6 max-w-[1500px]">

        <x-marketing.flash />

        <div class="text-[11px] text-slate-500 space-y-1">
            @if ($googleAdsMissing !== [])
                <p class="text-amber-700">Google Ads cannot be connected until these are set on the server: <code class="text-[10px]">{{ implode(', ', $googleAdsMissing) }}</code></p>
                @endif
                <p>OAuth redirect URI to register on the Google Cloud OAuth client: <code class="text-[10px] break-all select-all">{{ $googleAdsCallbackUrl }}</code></p>
        </div>

        @if ($googleAds['locations'] === [] && $googleAds['unassigned'] === [])
            <x-marketing.panel title="Google Ads" icon="search">
                <x-marketing.empty icon="map-pin" title="No locations configured"
                    message="Add an office under Offices / Locations in the analytics app, then connect its Google Ads account here." />
            </x-marketing.panel>
        @endif

        {{-- One card per selected location: its connection, then the accounts under it. --}}
        @foreach ($googleAds['locations'] as $row)
            @php [$label, $tone] = $pill($row['connection']); $c = $row['connection']; @endphp
            <x-marketing.panel :title="$row['location']->name" subtitle="Google Ads — read-only: campaigns, spend, clicks and conversions" icon="search">
                <x-slot:actions>
                    <x-front-desk.pill :label="$label" :tone="$tone" />
                    @if ($c !== null && $c->isUsable())
                        <form method="POST" action="{{ route('marketing.google-ads.disconnect', $c) }}"
                            onsubmit="return confirm('Disconnect Google Ads for {{ $row['location']->name }}? Imported campaign history is kept.');">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-2.5 py-1 text-[11px] font-bold rounded-lg border border-slate-200 text-slate-600 bg-white hover:bg-slate-50">Disconnect</button>
                        </form>
                    @endif
                    @if ($googleAdsMissing === [])
                        <a href="{{ route('marketing.google-ads.connect', ['location' => $row['location']->key()]) }}"
                            class="px-2.5 py-1 text-[11px] font-bold rounded-lg border border-[#00bfa5] text-white bg-[#00bfa5] hover:opacity-90">
                            {{ $c !== null && $c->isUsable() ? 'Reconnect' : 'Connect Google Ads' }}
                        </a>
                    @endif
                </x-slot:actions>

                @if ($c === null || ! $c->isUsable())
                    <p class="text-xs text-slate-500">
                        Not connected. Connect with the Google account that owns this location's ad account; its campaigns then report under {{ $row['location']->name }}.
                    </p>
                @else
                    <p class="text-xs text-slate-600">
                        {{ count($row['accounts']) }} {{ \Illuminate\Support\Str::plural('account', count($row['accounts'])) }} ·
                        {{ $c->last_synced_at ? 'last synced '.$c->last_synced_at->diffForHumans() : 'not synced yet' }}
                        @if ($c->connected_at) · connected {{ $c->connected_at->diffForHumans() }} @endif
                    </p>
                    @if ($c->last_error)
                        <p class="mt-1 text-xs text-amber-700">{{ $c->last_error }}</p>
                    @endif

                    @if ($row['accounts'] !== [])
                        <div class="mt-4">
                            @include('marketing.integrations._accounts', ['accounts' => $row['accounts'], 'id' => 'mkAccounts'.$loop->index])
                        </div>
                    @endif
                @endif
            </x-marketing.panel>
        @endforeach

        {{-- Connections made before locations existed: assign them, or they only count org-wide. --}}
        @foreach ($googleAds['unassigned'] as $row)
            @php [$label, $tone] = $pill($row['connection']); $c = $row['connection']; @endphp
            <x-marketing.panel title="Not assigned to a location" subtitle="A Google Ads connection made before locations existed" icon="triangle-alert" class="border-amber-200">
                <x-slot:actions>
                    <x-front-desk.pill :label="$label" :tone="$tone" />
                    <form method="POST" action="{{ route('marketing.google-ads.update', $c) }}" class="flex items-center gap-1.5">
                        @csrf @method('PATCH')
                        <select name="location" aria-label="Location" onchange="this.form.submit()"
                            class="text-[11px] rounded-lg border border-amber-300 bg-amber-50 px-2 py-1 cursor-pointer">
                            <option value="">Assign to a location…</option>
                            @foreach($locations as $key => $location)
                                <option value="{{ $key }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </form>
                    @if ($c->isUsable())
                        <form method="POST" action="{{ route('marketing.google-ads.disconnect', $c) }}"
                            onsubmit="return confirm('Disconnect this Google Ads connection? Imported campaign history is kept.');">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-2.5 py-1 text-[11px] font-bold rounded-lg border border-slate-200 text-slate-600 bg-white hover:bg-slate-50">Disconnect</button>
                        </form>
                    @endif
                </x-slot:actions>

                <p class="text-xs text-slate-600">
                    {{ count($row['accounts']) }} {{ \Illuminate\Support\Str::plural('account', count($row['accounts'])) }} ·
                    {{ $c->last_synced_at ? 'last synced '.$c->last_synced_at->diffForHumans() : 'not synced yet' }}.
                    Its campaigns appear only when every location is selected until it is assigned.
                </p>
                @if ($row['accounts'] !== [])
                    <div class="mt-4">
                        @include('marketing.integrations._accounts', ['accounts' => $row['accounts'], 'id' => 'mkUnassigned'.$loop->index])
                    </div>
                @endif
            </x-marketing.panel>
        @endforeach
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-mk-accounts]').forEach(function (table) {
                DDS.dataTable(table, { paging: false, info: false, searching: false });
            });
        });
    </script>
</x-marketing-layout>
