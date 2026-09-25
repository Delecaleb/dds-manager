<x-marketing-layout>
    <x-slot:title>Attribution Funnel</x-slot:title>
    <x-slot:subtitle>Where leads drop out, by channel and campaign</x-slot:subtitle>

    <div class="p-6 space-y-6 max-w-[1500px]">

        <x-marketing.panel title="Funnel stages" subtitle="Each stage and the step-to-step conversion into it" icon="filter">
            <div class="space-y-2">
                @foreach ([
                    ['Impressions', 'Ad served', 'eye'],
                    ['Clicks', 'Visitor reached a landing page', 'mouse-pointer-click'],
                    ['Responses', 'Call, form or chat with contact details', 'message-square'],
                    ['Booked', 'Appointment created in OpenDental', 'calendar-check'],
                    ['Completed', 'Treatment completed and produced', 'circle-check-big'],
                ] as $i => [$stage, $meaning, $icon])
                    <div class="flex items-center gap-4 border border-slate-200 rounded-xl px-4 py-3">
                        <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                            <i data-lucide="{{ $icon }}" class="w-3.5 h-3.5"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="text-[13px] font-bold text-slate-900">{{ $stage }}</div>
                            <div class="text-[11px] text-slate-500">{{ $meaning }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-extrabold text-slate-900 tabular-nums leading-none">—</div>
                            <div class="text-[10px] text-slate-400 mt-1">{{ $i === 0 ? 'starting point' : 'conversion from previous —' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-marketing.panel>

        <x-marketing.panel title="Funnel by campaign" subtitle="The same stages, split by campaign, to find where spend stalls" icon="table">
            <x-marketing.empty
                icon="filter"
                title="No attributed journeys yet"
                message="A journey is stitched together from an ad click, the response it produced and the appointment it became. That needs click tracking on the landing pages and the lead-to-patient match."
                waiting-on="Channel tracking + OpenDental patient match" />
        </x-marketing.panel>

        <x-marketing.panel title="How a lead is attributed" subtitle="The rules this page will apply, to be confirmed before build" icon="info">
            <ul class="text-xs text-slate-600 space-y-2 list-disc pl-4">
                <li><span class="font-semibold text-slate-800">Attribution window:</span> how long after a click a booking still counts. Set on the Settings page.</li>
                <li><span class="font-semibold text-slate-800">Match key:</span> phone number and email captured at response, matched against the patient record.</li>
                <li><span class="font-semibold text-slate-800">Credit:</span> first touch, last touch, or both shown side by side. To be decided.</li>
                <li><span class="font-semibold text-slate-800">Production:</span> completed procedures for the matched patient inside the window.</li>
            </ul>
        </x-marketing.panel>
    </div>
</x-marketing-layout>
