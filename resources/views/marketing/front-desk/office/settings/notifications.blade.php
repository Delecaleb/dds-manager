<p class="text-[13px] text-slate-600 -mt-2">Configure how you want to be notified when an action is completed.</p>

<x-marketing.panel title="Notifications" subtitle="Configure how you want to be notified when actions are completed.">
    @include('marketing.front-desk.office.settings._recipients', [
        'tableId' => 'fdNotifications',
        'recipients' => $settings['recipients'],
        'types' => $settings['types'],
        'emptyText' => 'No notification recipients yet.',
    ])
</x-marketing.panel>
