<x-marketing-layout>
    <x-slot:title>Workflows</x-slot:title>
    <x-slot:subtitle>Outreach the AI runs on its own</x-slot:subtitle>

    @php
        $suggestions = [
            'Keep it warm and concise.',
            'Offer to book a routine cleaning.',
            "Don't message after 7 PM local time.",
            "Send at most one follow-up if there's no reply.",
        ];
    @endphp

    <div class="h-[calc(100vh-4rem)] flex">
        {{-- Workflow list. --}}
        <aside class="w-[340px] shrink-0 border-r border-slate-200 bg-white flex flex-col">
            <div class="p-3 border-b border-slate-100">
                <label class="relative block">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="search" placeholder="Search workflows" data-fd-workflow-search
                        class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-200 text-[12px] focus:outline-none focus:ring-2 focus:ring-blue-500">
                </label>
                <p class="mt-2 px-1 text-[11px] text-slate-500">{{ count($workflows) }} {{ \Illuminate\Support\Str::plural('workflow', count($workflows)) }}</p>
            </div>
            <ul class="flex-1 overflow-y-auto">
                @foreach($workflows as $workflow)
                    @php $on = $selected && $selected['id'] === $workflow['id']; @endphp
                    <li data-fd-workflow="{{ strtolower($workflow['name']) }}">
                        <a href="{{ request()->fullUrlWithQuery(['workflow' => $workflow['id']]) }}"
                            class="flex items-center justify-between px-5 py-3 text-[13px] {{ $on ? 'bg-blue-50/60 shadow-[inset_3px_0_0_#2563eb] font-medium text-slate-900' : 'text-slate-700 hover:bg-slate-50' }}">
                            {{ $workflow['name'] }}
                            <span class="w-2 h-2 rounded-full {{ $workflow['enabled'] ? 'bg-emerald-500' : 'bg-slate-300' }}" title="{{ $workflow['enabled'] ? 'Enabled' : 'Disabled' }}"></span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </aside>

        <section class="flex-1 min-w-0 overflow-y-auto">
            @if(! $selected)
                <div class="p-6">
                    <x-marketing.panel title="Workflows" icon="workflow">
                        <x-marketing.empty icon="workflow" title="No workflows yet"
                            message="Confirmation and reactivation workflows text patients on a schedule, in plain-language instructions you write."
                            waiting-on="SMS platform integration" />
                    </x-marketing.panel>
                </div>
            @else
                <div class="p-6 space-y-4 max-w-[1200px]">
                    <div class="flex items-center justify-between">
                        <h2 class="text-[20px] font-semibold text-slate-900">{{ $selected['name'] }}</h2>
                        <label class="inline-flex items-center gap-2 text-[13px] text-slate-700" title="Switching workflows on/off arrives with the SMS integration">
                            {{ $selected['enabled'] ? 'Enabled' : 'Disabled' }}
                            <input type="checkbox" class="sr-only peer" disabled @checked($selected['enabled'])>
                            <span class="w-9 h-5 rounded-full bg-slate-200 peer-checked:bg-blue-600 relative after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:rounded-full after:bg-white after:transition peer-checked:after:translate-x-4"></span>
                        </label>
                    </div>

                    <div class="rounded-xl border border-violet-200 bg-violet-50 px-5 py-4">
                        <h3 class="flex items-center gap-1.5 text-[13px] font-semibold text-violet-700"><i data-lucide="sparkles" class="w-4 h-4"></i>AI summary</h3>
                        <p class="mt-1 text-[13px] text-violet-700">{{ $selected['summary'] ?? 'No runs yet — a summary will appear here after the first run.' }}</p>
                    </div>

                    <x-marketing.panel title="Instructions" subtitle="Plain-language guidance for the agent — tone, what to push, timing limits.">
                        <textarea rows="8" placeholder="e.g. Warm and concise. Offer a cleaning. Don't message after 7 PM local." data-fd-instructions
                            class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-[13px] leading-relaxed text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $selected['instructions'] }}</textarea>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach($suggestions as $suggestion)
                                <button type="button" data-fd-suggestion="{{ $suggestion }}"
                                    class="px-3 py-1 rounded-full border border-slate-200 text-[12px] text-slate-700 hover:bg-slate-50">+ {{ $suggestion }}</button>
                            @endforeach
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="button" disabled title="Saving arrives with the agent configuration backend"
                                class="px-4 py-2 rounded-lg bg-blue-300 text-white text-[13px] font-semibold cursor-not-allowed">Save instructions</button>
                        </div>
                    </x-marketing.panel>

                    <x-marketing.panel title="Run history">
                        @forelse($selected['runs'] as $run)
                            <details class="group border-b border-slate-100 last:border-0" @if($loop->first) open @endif>
                                <summary class="flex items-center justify-between py-3 cursor-pointer list-none">
                                    <span class="flex items-center gap-2 text-[13px] font-medium text-slate-800">
                                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-90"></i>
                                        {{ \Carbon\CarbonImmutable::parse($run['time'])->format('M j, g:i A') }}
                                    </span>
                                    <span class="text-[12px] text-slate-500">{{ ops_fmt($run['patients'], 'number') }} patients</span>
                                </summary>
                                <ul class="pb-3 pl-6 space-y-2">
                                    @foreach($run['messages'] as $message)
                                        <li class="flex items-start justify-between gap-4 text-[12px]">
                                            <span class="min-w-0">
                                                <span class="block font-medium text-slate-800 tabular-nums">{{ $message['phone'] }}</span>
                                                <span class="block text-slate-500 truncate">{{ $message['text'] }}</span>
                                            </span>
                                            <span class="text-slate-400 shrink-0">{{ \Carbon\CarbonImmutable::parse($message['time'])->format('M j') }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        @empty
                            <p class="text-[13px] text-slate-600">No runs yet. Once the automation runs, its outreach shows up here.</p>
                        @endforelse
                    </x-marketing.panel>
                </div>
            @endif
        </section>
    </div>

    <script>
        document.querySelector('[data-fd-workflow-search]')?.addEventListener('input', function (e) {
            var q = e.target.value.trim().toLowerCase();
            document.querySelectorAll('[data-fd-workflow]').forEach(function (li) {
                li.hidden = q !== '' && li.getAttribute('data-fd-workflow').indexOf(q) === -1;
            });
        });
        // Suggestion chips append their line to the instructions.
        document.querySelectorAll('[data-fd-suggestion]').forEach(function (chip) {
            chip.addEventListener('click', function () {
                var box = document.querySelector('[data-fd-instructions]');
                if (!box) return;
                box.value = (box.value.trim() ? box.value.trim() + '\n' : '') + chip.getAttribute('data-fd-suggestion');
                box.focus();
            });
        });
    </script>
</x-marketing-layout>
