<x-marketing-layout>
    <x-slot:title>Analytics</x-slot:title>
    <x-slot:subtitle>Get insights into your calls, online bookings, and messages</x-slot:subtitle>


    <div class="p-6 space-y-4 max-w-[1500px]">
        <div class="flex flex-wrap items-center justify-between gap-2">
            {{-- Calls / Online Bookings / Messages / Workflows: a URL-driven tab (?tab=). --}}
            <div data-dds-panels="tab" class="inline-flex items-center p-0.5 rounded-lg border border-slate-200 bg-white">
                @foreach(['calls' => 'Calls', 'bookings' => 'Online Bookings', 'messages' => 'Messages', 'workflows' => 'Workflows'] as $key => $label)
                    <button type="button" data-dds-panel-tab="{{ $key }}"
                        class="px-3 py-1.5 rounded-md text-[12px] font-semibold text-slate-600 aria-selected:bg-blue-600 aria-selected:text-white">{{ $label }}</button>
                @endforeach
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-front-desk.filter-group param="patients" :options="\App\Domain\FrontDesk\FrontDeskFilter::PATIENTS" :current="$filter->patients" />
                <x-front-desk.filter-group param="direction" :options="\App\Domain\FrontDesk\FrontDeskFilter::DIRECTIONS" :current="$filter->direction" />
                <x-front-desk.filter-group param="range" :options="\App\Domain\FrontDesk\FrontDeskFilter::RANGES" :current="$filter->range" />
            </div>
        </div>

        <div data-dds-panel-for="tab" data-dds-panel="calls">
            @include('marketing.front-desk.partials.call-analytics', ['byOffice' => false])
        </div>

        {{-- The reference screenshots show only the Calls tab; these tabs get their
             charts once their content is confirmed. --}}
        @foreach([
            'bookings' => ['calendar-check', 'Online booking analytics', 'Bookings made through the online scheduling link, by service and patient type.'],
            'messages' => ['message-square', 'Message analytics', 'Texts handled by the SMS agent and conversations handed to staff.'],
            'workflows' => ['workflow', 'Workflow analytics', 'Outreach sent by confirmation and reactivation workflows, replies and bookings.'],
        ] as $key => [$icon, $title, $message])
            <div data-dds-panel-for="tab" data-dds-panel="{{ $key }}" hidden>
                <x-marketing.panel :title="$title" :icon="$icon">
                    <x-marketing.empty :icon="$icon" :title="$title" :message="$message" waiting-on="Reference screens for this tab" />
                </x-marketing.panel>
            </div>
        @endforeach
    </div>
</x-marketing-layout>
