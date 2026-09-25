<x-marketing-layout>
    <x-slot:title>Channels</x-slot:title>
    <x-slot:subtitle>Every source of leads, and what it costs</x-slot:subtitle>

    <div class="p-6 space-y-6 max-w-[1500px]">

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ([
                ['Google Ads', 'Search and Performance Max campaigns', 'search', 'Google Ads API'],
                ['Meta (Facebook / Instagram)', 'Paid social campaigns and lead forms', 'thumbs-up', 'Meta Marketing API'],
                ['Google Business Profile', 'Map pack calls, messages and direction requests', 'map-pin', 'Business Profile API'],
                ['Website / SEO', 'Organic visits and form submissions', 'globe', 'Site tracking script'],
                ['Call Tracking', 'Tracked numbers per campaign, with recordings', 'phone-call', 'Twilio'],
                ['Referrals', 'Patient and practice referrals entered in OpenDental', 'users', 'OpenDental referral source'],
            ] as [$name, $what, $icon, $needs])
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-9 h-9 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                                <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="text-[13px] font-bold text-slate-900 truncate">{{ $name }}</div>
                                <div class="text-[11px] text-slate-500 leading-snug">{{ $what }}</div>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border border-slate-200 bg-slate-50 text-slate-500 shrink-0">
                            Not connected
                        </span>
                    </div>

                    <dl class="grid grid-cols-3 gap-2 mt-4 pt-3 border-t border-slate-100">
                        @foreach (['Spend', 'Leads', 'Booked'] as $metric)
                            <div>
                                <dt class="text-[10px] text-slate-400 font-semibold">{{ $metric }}</dt>
                                <dd class="text-sm font-extrabold text-slate-900 tabular-nums">—</dd>
                            </div>
                        @endforeach
                    </dl>

                    <div class="mt-3 flex items-center justify-between gap-2">
                        <span class="text-[10px] text-slate-400">Needs: {{ $needs }}</span>
                        <a href="{{ route('marketing.integrations') }}" class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800">Connect →</a>
                    </div>
                </div>
            @endforeach
        </div>

        <x-marketing.panel title="Channel comparison" subtitle="Spend, cost per booked appointment and return, side by side" icon="bar-chart-3">
            <x-marketing.empty
                icon="bar-chart-3"
                title="Nothing to compare yet"
                message="Once two or more channels are connected, this table ranks them by cost per booked appointment and by production returned."
                waiting-on="At least one connected channel" />
        </x-marketing.panel>
    </div>
</x-marketing-layout>
