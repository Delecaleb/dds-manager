<x-marketing-layout>
    <x-slot:title>Automations</x-slot:title>
    <x-slot:subtitle>The actions the system takes on its own</x-slot:subtitle>

    <div class="p-6 space-y-6 max-w-[1500px]">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach ([
                [
                    'Retarget the ones who didn\'t book',
                    'repeat',
                    'When someone clicks an ad and doesn\'t book within the window, move budget to retarget them.',
                    ['Trigger', 'Click with no booking after N days'],
                    ['Action', 'Add the visitor to the retargeting audience and shift budget to it'],
                    'Ad platform API with write access, plus an agreed budget cap',
                ],
                [
                    'Nurture the lead',
                    'message-circle',
                    'Text or call people who responded but never booked, and keep nudging until they do or opt out.',
                    ['Trigger', 'Lead with contact details and no appointment'],
                    ['Action', 'Send a text, then place a call, on a set schedule'],
                    'Twilio, consent and quiet-hours rules, opt-out handling',
                ],
                [
                    'Flag underperforming campaigns',
                    'triangle-alert',
                    'Watch cost per booked appointment and raise it with the person who owns the budget.',
                    ['Trigger', 'Cost per booked over target for N days'],
                    ['Action', 'Post an alert and notify the named owner'],
                    'Enough attributed bookings to judge a campaign fairly',
                ],
                [
                    'Launch a location campaign',
                    'rocket',
                    'Create the location page and start its campaign from a short brief.',
                    ['Trigger', 'Someone requests a launch for a location'],
                    ['Action', 'Generate the page, then create the campaign at the agreed budget'],
                    'Approval step and budget guardrails before anything goes live',
                ],
            ] as [$name, $icon, $what, $trigger, $action, $needs])
                <section class="bg-white border border-slate-200 rounded-xl shadow-sm p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                                <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
                            </span>
                            <div class="min-w-0">
                                <h2 class="text-[13px] font-bold text-slate-900">{{ $name }}</h2>
                                <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">{{ $what }}</p>
                            </div>
                        </div>

                        {{-- Deliberately inert: an automation that can spend money or contact a
                             patient stays off until its rules and guardrails are agreed. --}}
                        <div class="flex flex-col items-end gap-1.5 shrink-0">
                            <span class="inline-flex items-center h-5 w-9 rounded-full bg-slate-200 px-0.5 cursor-not-allowed" title="Not available yet">
                                <span class="h-4 w-4 rounded-full bg-white shadow"></span>
                            </span>
                            <span class="text-[10px] font-bold text-slate-400">Off</span>
                        </div>
                    </div>

                    <dl class="mt-4 space-y-1.5 text-[11px]">
                        @foreach ([$trigger, $action] as [$label, $value])
                            <div class="flex gap-2">
                                <dt class="w-14 shrink-0 font-bold text-slate-500">{{ $label }}</dt>
                                <dd class="text-slate-700">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    <p class="mt-3 pt-3 border-t border-slate-100 text-[10px] text-slate-400">Needs: {{ $needs }}</p>
                </section>
            @endforeach
        </div>

        <x-marketing.panel title="Action log" subtitle="Every automated action, with what triggered it and what it changed" icon="scroll-text">
            <x-marketing.empty
                icon="scroll-text"
                title="Nothing has run yet"
                message="Each automated text, call, budget change and alert is recorded here: what fired it, what it did, and what happened next. This log is how an agentic system stays auditable."
                waiting-on="The first automation being switched on" />
        </x-marketing.panel>
    </div>
</x-marketing-layout>
