<x-marketing.panel title="Basic Information" subtitle="Verify that this information is correct for your office.">
    <dl class="space-y-3 text-[13px]">
        @foreach([
            'Location name' => $settings['location_name'],
            'AI assistant name' => $settings['assistant_name'],
            'Address' => $settings['address'],
            'Time zone' => $settings['timezone'],
            'Phone' => $settings['phone'],
            'Email' => $settings['email'],
        ] as $label => $value)
            <div>
                <dt class="font-medium text-slate-800">{{ $label }}</dt>
                <dd class="text-slate-600">{{ $value ?: '—' }}</dd>
            </div>
        @endforeach
    </dl>
</x-marketing.panel>

<x-marketing.panel title="Services" subtitle="Add or remove any services that your practice offers.">
    <x-slot:actions>
        <button type="button" disabled title="Editing services arrives with the agent configuration backend"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-[12px] font-semibold text-slate-400 cursor-not-allowed">
            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add service
        </button>
    </x-slot:actions>
    @if($settings['services'] === [])
        <x-marketing.empty icon="stethoscope" title="No services listed" message="The AI tells callers which services this office offers." />
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
            @foreach($settings['services'] as $service)
                <div class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg border border-slate-200 text-[13px] text-slate-800">
                    {{ $service }}<i data-lucide="x" class="w-4 h-4 text-slate-400"></i>
                </div>
            @endforeach
        </div>
    @endif
</x-marketing.panel>
