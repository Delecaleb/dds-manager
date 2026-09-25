<x-marketing-layout>
    <x-slot:title>Overview</x-slot:title>
    <x-slot:subtitle>Spend to completed production, across every channel</x-slot:subtitle>

    <div class="p-6 space-y-6 max-w-[1500px]">

        {{-- Headline numbers: money in on the left, money out on the right. --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-3">
            <x-marketing.stat label="Ad Spend" icon="wallet" hint="All channels, selected period" />
            <x-marketing.stat label="Leads" icon="user-plus" hint="Clicks that gave contact details" />
            <x-marketing.stat label="Booked" icon="calendar-check" tone="emerald" hint="Leads that reached an appointment" />
            <x-marketing.stat label="Completed Production" icon="badge-dollar-sign" tone="emerald" hint="Production from attributed patients" />
            <x-marketing.stat label="Cost per Lead" icon="receipt" hint="Spend ÷ leads" />
            <x-marketing.stat label="Cost per Booked" icon="calculator" hint="Spend ÷ booked appointments" />
            <x-marketing.stat label="ROAS" icon="trending-up" tone="emerald" hint="Completed production ÷ spend" />
        </div>

        {{-- The funnel this module exists to close: impression → completed work. --}}
        <x-marketing.panel title="Lead journey" subtitle="Impression → click → response → booked → completed" icon="filter">
            <x-slot:actions>
                <a href="{{ route('marketing.funnel') }}" class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800">Open funnel →</a>
            </x-slot:actions>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                @foreach ([
                    ['Impressions', 'eye', 'Ad platform reports'],
                    ['Clicks', 'mouse-pointer-click', 'Ad platform reports'],
                    ['Responses', 'message-square', 'Calls, forms, chat'],
                    ['Booked', 'calendar-check', 'Appointment created'],
                    ['Completed', 'circle-check-big', 'Procedure completed'],
                ] as [$stage, $icon, $source])
                    <div class="border border-slate-200 rounded-xl p-3.5">
                        <div class="flex items-center gap-2 text-[11px] font-semibold text-slate-500">
                            <i data-lucide="{{ $icon }}" class="w-3.5 h-3.5"></i> {{ $stage }}
                        </div>
                        <div class="mt-1.5 text-xl font-extrabold text-slate-900 tabular-nums">—</div>
                        <div class="mt-0.5 text-[10px] text-slate-400">{{ $source }}</div>
                    </div>
                @endforeach
            </div>
        </x-marketing.panel>

        {{-- Getting started: website tracking is the part that works today, so the overview
             points straight at it rather than leaving someone to find it. --}}
        <x-marketing.panel title="Start tracking a website" subtitle="Three steps — works on WordPress, Wix, Squarespace or a custom site" icon="globe">
            <x-slot:actions>
                <a href="{{ route('marketing.tracking') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold rounded-lg border border-emerald-500 text-emerald-700 hover:bg-emerald-50">
                    <i data-lucide="code" class="w-3.5 h-3.5"></i> Get the script
                </a>
            </x-slot:actions>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ([
                    ['1. Add the site', 'Name, domain and the office its leads belong to. You get a key and a snippet.', 'plus-circle'],
                    ['2. Paste the snippet', 'On WordPress: a header plugin such as WPCode, or the child theme. Step-by-step instructions, with your key already filled in, are on the Tracking Script page.', 'clipboard-paste'],
                    ['3. Mark signups', 'One line on form submit turns a visitor into a lead, and their earlier visits join to them.', 'user-plus'],
                ] as [$step, $detail, $icon])
                    <div class="border border-slate-200 rounded-xl p-4">
                        <div class="flex items-center gap-2 text-[12px] font-bold text-slate-900">
                            <i data-lucide="{{ $icon }}" class="w-4 h-4 text-slate-400"></i> {{ $step }}
                        </div>
                        <p class="mt-1.5 text-[11px] text-slate-500 leading-relaxed">{{ $detail }}</p>
                    </div>
                @endforeach
            </div>
        </x-marketing.panel>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <x-marketing.panel title="Channel performance" subtitle="Spend and return by channel" icon="share-2" class="xl:col-span-2">
                <x-slot:actions>
                    <a href="{{ route('marketing.channels') }}" class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800">Manage channels →</a>
                </x-slot:actions>

                <x-marketing.empty
                    icon="share-2"
                    title="No channels connected"
                    message="Connect Google Ads, Meta and call tracking to see spend, leads and booked appointments per channel here."
                    waiting-on="Ad platform API credentials" />
            </x-marketing.panel>

            <x-marketing.panel title="Needs attention" subtitle="Campaigns and automations flagged for review" icon="bell-ring">
                <x-slot:actions>
                    <a href="{{ route('marketing.alerts') }}" class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800">All alerts →</a>
                </x-slot:actions>

                <x-marketing.empty
                    icon="bell-off"
                    title="No alerts yet"
                    message="Underperforming campaigns and stalled follow-ups will be flagged here once tracking is live." />
            </x-marketing.panel>
        </div>
    </div>
</x-marketing-layout>
