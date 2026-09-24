<x-marketing-layout>
    <x-slot:title>Campaigns</x-slot:title>
    <x-slot:subtitle>What is running, what it costs, what it returns</x-slot:subtitle>

    <div class="p-6 space-y-6 max-w-[1500px]">

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <x-marketing.stat label="Active Campaigns" icon="megaphone" />
            <x-marketing.stat label="Spend (period)" icon="wallet" />
            <x-marketing.stat label="Booked Appointments" icon="calendar-check" tone="emerald" />
            <x-marketing.stat label="Flagged" icon="triangle-alert" tone="amber" hint="Below target cost per booked" />
        </div>

        <x-marketing.panel title="All campaigns" subtitle="One row per campaign, per location" icon="megaphone">
            <x-slot:actions>
                <button type="button" disabled
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold rounded-lg border border-slate-200 text-slate-400 bg-slate-50 cursor-not-allowed"
                    title="Available once a channel is connected">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> New campaign
                </button>
            </x-slot:actions>

            {{-- Column layout is fixed now so the data work has a target to fill. --}}
            <div class="overflow-x-auto">
                <table class="dds-table w-full text-left text-xs whitespace-nowrap">
                    <thead>
                        <tr>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Campaign</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Channel</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Location</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Status</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900 text-right">Spend</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900 text-right">Leads</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900 text-right">Booked</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900 text-right">Cost / Booked</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900 text-right">Production</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900 text-right">ROAS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="10" class="px-0">
                                <x-marketing.empty
                                    icon="megaphone"
                                    title="No campaigns imported"
                                    message="Campaigns are pulled from the connected ad accounts, then matched to a location so cost per booked appointment can be worked out per office."
                                    waiting-on="Google Ads / Meta connection" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-marketing.panel>

        <x-marketing.panel title="Launch a location campaign" subtitle="The automated setup from the brief: a location page plus its campaign, from minimal input" icon="rocket">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ([
                    ['1. Pick a location', 'Office, service area and the treatments to promote', 'building-2'],
                    ['2. Generate the page', 'Location landing page with tracking and a booking form', 'file-plus-2'],
                    ['3. Launch the campaign', 'Budget, keywords and creative pushed to the ad platform', 'rocket'],
                ] as [$step, $detail, $icon])
                    <div class="border border-slate-200 rounded-xl p-4">
                        <div class="flex items-center gap-2 text-[12px] font-bold text-slate-900">
                            <i data-lucide="{{ $icon }}" class="w-4 h-4 text-slate-400"></i> {{ $step }}
                        </div>
                        <p class="mt-1.5 text-[11px] text-slate-500 leading-relaxed">{{ $detail }}</p>
                    </div>
                @endforeach
            </div>
            <p class="mt-4 text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                Spending money automatically needs guardrails agreed first: budget caps, who approves a launch, and whether a campaign can go live without review. Those live on the Settings page.
            </p>
        </x-marketing.panel>
    </div>
</x-marketing-layout>
