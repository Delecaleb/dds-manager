<p class="text-[13px] text-slate-600 -mt-2">Control your online scheduling notification settings and share your booking link.</p>

<x-marketing.panel title="Online Scheduling Link" subtitle="Share this link with patients to allow them to book appointments online.">
    @if($settings['link'])
        <div class="flex gap-2">
            <input type="text" readonly value="{{ $settings['link'] }}" id="fdBookingLink"
                class="flex-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 font-mono text-[13px] text-slate-800">
            <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('fdBookingLink').value)"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 text-[12px] font-semibold text-slate-700 hover:bg-slate-50">
                <i data-lucide="copy" class="w-3.5 h-3.5"></i> Copy
            </button>
        </div>
    @else
        <p class="text-[13px] text-slate-500">No booking link yet — it is created when online scheduling is set up for this office.</p>
    @endif
</x-marketing.panel>

<x-marketing.panel title="Confirmation Redirect" subtitle="Redirect patients to a custom URL after successful appointment booking. If left empty, patients will see the default confirmation page.">
    <div class="flex gap-2">
        <input type="url" value="{{ $settings['redirect'] }}" placeholder="https://"
            class="flex-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 font-mono text-[13px] text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="button" disabled class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 text-[12px] font-semibold text-slate-400 cursor-not-allowed">
            <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
        </button>
    </div>
</x-marketing.panel>

<x-marketing.panel title="Online Scheduling Services" subtitle="Configure which services patients can book online and customize intake forms.">
    {{-- New / Existing patients: a URL-driven tab (?patients=). --}}
    <div data-dds-panels="patients" class="grid grid-cols-2 p-1 rounded-lg bg-slate-100">
        @foreach(['new' => 'New Patients', 'existing' => 'Existing Patients'] as $key => $label)
            <button type="button" data-dds-panel-tab="{{ $key }}"
                class="py-2 rounded-md text-[13px] font-medium text-slate-600 aria-selected:bg-white aria-selected:text-blue-700 aria-selected:shadow-sm">
                {{ $label }} ({{ count($settings['services'][$key]) }})
            </button>
        @endforeach
    </div>
    @foreach(['new', 'existing'] as $key)
        <div data-dds-panel-for="patients" data-dds-panel="{{ $key }}" class="mt-3 space-y-2" @if(! $loop->first) hidden @endif>
            @forelse($settings['services'][$key] as $service)
                <div class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 px-4 py-3">
                    <span class="text-[14px] text-slate-900">{{ $service }}</span>
                    <span class="flex gap-2">
                        <button type="button" disabled class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 text-[12px] text-slate-400 cursor-not-allowed"><i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit</button>
                        <button type="button" disabled class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-rose-200 text-[12px] text-rose-300 cursor-not-allowed"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete</button>
                    </span>
                </div>
            @empty
                <p class="text-[13px] text-slate-500 px-1 py-2">No services bookable online.</p>
            @endforelse
        </div>
    @endforeach
</x-marketing.panel>

<x-marketing.panel title="Online Scheduling Notifications" subtitle="Configure how you want to be notified about online booking events.">
    @include('marketing.front-desk.office.settings._recipients', [
        'tableId' => 'fdBookingNotifications',
        'recipients' => $settings['recipients'],
        'types' => ['Online Booking'],
        'emptyText' => 'No online scheduling notifications have been configured.',
    ])
</x-marketing.panel>
