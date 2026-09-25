<x-marketing-layout>
    <x-slot:title>Leads</x-slot:title>
    <x-slot:subtitle>Everyone who responded, and where they got to</x-slot:subtitle>

    <div class="p-6 space-y-6 max-w-[1500px]">

        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
            <x-marketing.stat label="New Leads" icon="user-plus" />
            <x-marketing.stat label="Contacted" icon="message-square" />
            <x-marketing.stat label="Booked" icon="calendar-check" tone="emerald" />
            <x-marketing.stat label="Completed" icon="circle-check-big" tone="emerald" />
            <x-marketing.stat label="Unworked > 24h" icon="clock-alert" tone="rose" hint="No follow-up since the response" />
        </div>

        <x-marketing.panel title="Lead list" subtitle="Filter by where the lead has got to" icon="users">
            <x-slot:actions>
                <div class="flex items-center gap-1">
                    @foreach (['All', 'New', 'Contacted', 'Booked', 'No-show', 'Lost'] as $i => $status)
                        <button type="button"
                            class="px-2.5 py-1 text-[11px] font-semibold rounded-lg border {{ $i === 0 ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 text-slate-500 hover:bg-slate-50' }}">
                            {{ $status }}
                        </button>
                    @endforeach
                </div>
            </x-slot:actions>

            <div class="overflow-x-auto">
                <table class="dds-table w-full text-left text-xs whitespace-nowrap">
                    <thead>
                        <tr>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Lead</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Contact</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Source</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Campaign</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Location</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">First Touch</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Status</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Last Action</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Next Action</th>
                            <th class="py-2.5 px-4 font-bold text-gray-900">Patient</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="10" class="px-0">
                                <x-marketing.empty
                                    icon="user-plus"
                                    title="No leads captured yet"
                                    message="A lead is created when someone calls a tracked number, submits a form or messages from an ad. It stays linked to the campaign that produced it, and to the patient record once booked."
                                    waiting-on="Call tracking + form capture" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-marketing.panel>

        <x-marketing.panel title="Booking a lead" subtitle="The step that turns this from reporting into action" icon="calendar-plus">
            <div class="text-xs text-slate-600 space-y-2 leading-relaxed">
                <p>
                    Booking from here writes an appointment into OpenDental. Reads are synced today, but
                    <span class="font-semibold text-slate-800">write-back has not been built or chosen yet</span> — that's the open research item in the brief.
                </p>
                <p>Until it exists, this page can hand the front desk a worked list and record the outcome, without creating the appointment itself.</p>
            </div>
            <p class="mt-4 text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                Waiting on: an OpenDental write-back route (API vs middleware vs direct), plus a rule for which operatory and provider a lead may book into.
            </p>
        </x-marketing.panel>
    </div>
</x-marketing-layout>
