<x-marketing-layout>
    <x-slot:title>Integrations</x-slot:title>
    <x-slot:subtitle>The outside services this module depends on</x-slot:subtitle>

    <div class="p-6 space-y-6 max-w-[1500px]">

        <x-marketing.flash />

        @php
            $googleAdsConnected = $googleAds?->isUsable() ?? false;
        @endphp

        <x-marketing.panel title="Google Ads" subtitle="Read-only: campaigns, spend, clicks and conversions per ad account" icon="search">
            <div class="flex flex-wrap items-start justify-between gap-4 text-xs text-slate-600">
                <div class="space-y-2 min-w-0">
                    <div class="flex items-center gap-2">
                        @if ($googleAdsConnected && $googleAds->status === \App\Models\MarketingAdConnection::STATUS_ERROR)
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border border-amber-200 bg-amber-50 text-amber-700">Needs attention</span>
                        @elseif ($googleAdsConnected)
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border border-emerald-200 bg-emerald-50 text-emerald-700">Connected</span>
                        @else
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border border-slate-200 bg-slate-50 text-slate-500">Not connected</span>
                        @endif

                        @if ($googleAdsConnected)
                            <span>{{ $googleAds->accounts_count }} account(s) ·
                                {{ $googleAds->last_synced_at ? 'last synced '.$googleAds->last_synced_at->diffForHumans() : 'not synced yet' }}</span>
                        @endif
                    </div>

                    @if ($googleAds?->last_error)
                        <p class="text-amber-700">{{ $googleAds->last_error }}</p>
                    @endif

                    @if ($googleAdsMissing !== [])
                        <p class="text-amber-700">Set on the server before connecting: <code class="text-[10px]">{{ implode(', ', $googleAdsMissing) }}</code></p>
                    @endif

                    <p>
                        OAuth redirect URI to register on the Google Cloud OAuth client:
                        <code class="text-[10px] break-all select-all">{{ $googleAdsCallbackUrl }}</code>
                    </p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    @if ($googleAdsConnected)
                        <form method="POST" action="{{ route('marketing.google-ads.disconnect') }}"
                            onsubmit="return confirm('Disconnect Google Ads? Imported campaign history is kept.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-2.5 py-1 text-[11px] font-bold rounded-lg border border-slate-200 text-slate-600 bg-white hover:bg-slate-50">
                                Disconnect
                            </button>
                        </form>
                    @endif

                    @if ($googleAdsMissing === [])
                        <a href="{{ route('marketing.google-ads.connect') }}"
                            class="px-2.5 py-1 text-[11px] font-bold rounded-lg border border-[#00bfa5] text-white bg-[#00bfa5] hover:opacity-90">
                            {{ $googleAdsConnected ? 'Reconnect' : 'Connect' }}
                        </a>
                    @endif
                </div>
            </div>
        </x-marketing.panel>

        <x-marketing.panel title="Connections" subtitle="Still to be connected — each row lists what it needs" icon="plug">
            <div class="overflow-x-auto">
                <table class="dds-table w-full text-left text-xs whitespace-nowrap">
                    <thead>
                        <tr>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Service</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Used for</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Access needed</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Status</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ([
                            ['Meta Marketing', 'Facebook and Instagram campaigns and lead forms', 'Read + write, per ad account'],
                            ['Google Business Profile', 'Calls, messages and direction requests from the map pack', 'Read'],
                            ['Twilio', 'Tracked numbers, lead texts and calls', 'Messaging + voice, one number per campaign'],
                            ['Website tracking', 'Click-to-lead matching on landing pages', 'Script on every landing page'],
                            ['OpenDental write-back', 'Creating the appointment a lead books', 'Write access — route not yet chosen'],
                        ] as [$service, $used, $access])
                            <tr>
                                <td class="py-3 px-4 font-semibold text-slate-800">{{ $service }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $used }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $access }}</td>
                                <td class="py-3 px-4">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border border-slate-200 bg-slate-50 text-slate-500">
                                        Not configured
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <button type="button" disabled
                                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg border border-slate-200 text-slate-400 bg-slate-50 cursor-not-allowed">
                                        Connect
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-marketing.panel>

        <x-marketing.panel title="OpenDental write-back" subtitle="The open research item, and the one that decides how far automation can go" icon="database">
            <div class="text-xs text-slate-600 space-y-3 leading-relaxed">
                <p>
                    Everything today is read-only: OpenDental is synced into the local reporting database and never written to.
                    Booking an appointment from a lead reverses that, so the route has to be chosen deliberately.
                </p>
                <ul class="list-disc pl-4 space-y-1.5">
                    <li><span class="font-semibold text-slate-800">OpenDental API:</span> supported and safest, but check the endpoints cover appointment creation for every office's version.</li>
                    <li><span class="font-semibold text-slate-800">Middleware / eConnector:</span> a service on the practice's own machine; more moving parts per location.</li>
                    <li><span class="font-semibold text-slate-800">Direct database write:</span> fastest to build, and the one most likely to corrupt a practice's data. Not recommended.</li>
                </ul>
                <p>
                    Whatever is chosen also needs: which operatory and provider a lead may book into, double-booking protection,
                    and an audit record of every appointment the system creates.
                </p>
            </div>
        </x-marketing.panel>

        <x-marketing.panel title="Before any of this is switched on" subtitle="Patient data and money are both in scope here" icon="shield-check">
            <ul class="text-xs text-slate-600 space-y-2 list-disc pl-4 leading-relaxed">
                <li>Texting and calling patients needs consent, quiet hours and working opt-out, per TCPA.</li>
                <li>Lead records hold contact details for real people, so they carry the same protection as patient data.</li>
                <li>Anything that spends money needs a budget cap and a named approver.</li>
                <li>Every credential here belongs in the server environment, never in the repository.</li>
            </ul>
        </x-marketing.panel>
    </div>
</x-marketing-layout>
