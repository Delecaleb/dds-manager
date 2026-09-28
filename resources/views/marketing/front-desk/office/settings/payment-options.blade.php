<p class="text-[13px] text-slate-600 -mt-2">Configure financing options, insurance plans, packages, and promotions.</p>

<x-marketing.panel title="Packages & Promotions" subtitle="Manage packages, promotions, and special offers for your practice.">
    <div class="space-y-3">
        @forelse($settings['packages'] as $package)
            <div class="rounded-lg border border-slate-200 px-4 py-3 flex items-start justify-between gap-4">
                <div class="text-[12px] text-slate-600 space-y-0.5">
                    <div class="text-[13px] font-medium text-slate-900">{{ $package['name'] }}</div>
                    @foreach($package['details'] as $line)
                        <div>{{ $line }}</div>
                    @endforeach
                </div>
                <span class="px-2.5 py-1 rounded-full border border-slate-200 text-[12px] font-semibold text-slate-800">{{ ops_fmt($package['price'], 'money') }}</span>
            </div>
        @empty
            <p class="text-[12px] text-slate-500">No packages or promotions.</p>
        @endforelse
    </div>
    <button type="button" disabled class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-[12px] font-semibold text-slate-400 cursor-not-allowed">
        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Package
    </button>
</x-marketing.panel>

<x-marketing.panel title="Insurance Plans" subtitle="Configure insurance plans that your practice accepts and handles.">
    <div class="space-y-3">
        @forelse($settings['insurance'] as $plan)
            <div class="rounded-lg border border-slate-200 px-4 py-3 flex items-start justify-between gap-4">
                <div class="text-[12px] text-slate-600 space-y-0.5">
                    <div class="text-[13px] font-medium text-slate-900">{{ $plan['name'] }}</div>
                    <div>{{ $plan['note'] }}</div>
                    <div>Type: {{ $plan['type'] }}</div>
                </div>
                <x-front-desk.pill :label="$plan['accepted'] ? 'Accepted' : 'Not accepted'" :tone="$plan['accepted'] ? 'emerald' : 'rose'" />
            </div>
        @empty
            <x-marketing.empty icon="shield-check" title="No insurance plans listed"
                message="The AI checks a caller's plan against this list before booking." />
        @endforelse
    </div>
    <button type="button" disabled class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-[12px] font-semibold text-slate-400 cursor-not-allowed">
        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Insurance Plan
    </button>
</x-marketing.panel>

<x-marketing.panel title="Financing Options" subtitle="Configure financing and payment options available to your patients.">
    <label class="block text-[12px] font-medium text-slate-700 mb-1" for="fdFinancing">CareCredit & Financing Options</label>
    <input id="fdFinancing" type="text" value="{{ $settings['financing'] }}" placeholder="e.g. We accept CareCredit."
        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500">
</x-marketing.panel>

@include('marketing.front-desk.office.settings._save')
