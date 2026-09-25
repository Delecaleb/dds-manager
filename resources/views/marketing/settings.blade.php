<x-marketing-layout>
    <x-slot:title>Settings</x-slot:title>
    <x-slot:subtitle>Attribution rules, guardrails and who gets told</x-slot:subtitle>

    <div class="p-6 space-y-6 max-w-[1100px]">

        <p class="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
            These controls are laid out but not wired up. Each one needs a decision before the automations can be switched on.
        </p>

        <x-marketing.panel title="Attribution" subtitle="How a booking gets credited to a campaign" icon="filter">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ([
                    ['Attribution window', 'How long after a click a booking still counts', '30 days'],
                    ['Credit model', 'Which touch gets the credit', 'First touch'],
                    ['Match key', 'How a lead is tied to a patient record', 'Phone, then email'],
                    ['Production window', 'How long completed treatment counts toward return', '90 days'],
                ] as [$label, $help, $placeholder])
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700">{{ $label }}</label>
                        <p class="text-[10px] text-slate-400 mb-1.5">{{ $help }}</p>
                        <input type="text" value="{{ $placeholder }}" disabled
                            class="w-full text-xs rounded-lg border border-slate-200 bg-slate-50 text-slate-500 px-3 py-2 cursor-not-allowed">
                    </div>
                @endforeach
            </div>
        </x-marketing.panel>

        <x-marketing.panel title="Spending guardrails" subtitle="The limits on anything the system does with money" icon="shield-check">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ([
                    ['Daily budget cap per campaign', 'The system may never exceed this on its own', '—'],
                    ['Monthly cap across all campaigns', 'Hard ceiling for automated spend', '—'],
                    ['Budget change limit', 'Largest shift an automation may make at once', '—'],
                    ['Launch approval', 'Who signs off before a campaign goes live', 'Required'],
                ] as [$label, $help, $placeholder])
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700">{{ $label }}</label>
                        <p class="text-[10px] text-slate-400 mb-1.5">{{ $help }}</p>
                        <input type="text" value="{{ $placeholder }}" disabled
                            class="w-full text-xs rounded-lg border border-slate-200 bg-slate-50 text-slate-500 px-3 py-2 cursor-not-allowed">
                    </div>
                @endforeach
            </div>
        </x-marketing.panel>

        <x-marketing.panel title="Contacting leads" subtitle="Rules for texts and calls, which apply to real people" icon="message-circle">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ([
                    ['Quiet hours', 'No texts or calls in this window, per location time zone', '9:00pm – 8:00am'],
                    ['Maximum attempts', 'Contacts per lead before it is left alone', '3'],
                    ['Time between attempts', 'Gap between follow-ups', '2 days'],
                    ['Opt-out handling', 'Words that stop all contact immediately', 'STOP, UNSUBSCRIBE'],
                ] as [$label, $help, $placeholder])
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700">{{ $label }}</label>
                        <p class="text-[10px] text-slate-400 mb-1.5">{{ $help }}</p>
                        <input type="text" value="{{ $placeholder }}" disabled
                            class="w-full text-xs rounded-lg border border-slate-200 bg-slate-50 text-slate-500 px-3 py-2 cursor-not-allowed">
                    </div>
                @endforeach
            </div>
        </x-marketing.panel>

        <x-marketing.panel title="Alert thresholds and owners" subtitle="When to raise something, and with whom" icon="bell-ring">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ([
                    ['Target cost per booked appointment', 'Above this for N days raises a warning', '—'],
                    ['Days over target before alerting', 'Stops one bad day raising an alert', '3'],
                    ['Spend with no bookings', 'Amount that raises a critical alert', '—'],
                    ['Alert recipients', 'Who is notified, per rule', '—'],
                ] as [$label, $help, $placeholder])
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700">{{ $label }}</label>
                        <p class="text-[10px] text-slate-400 mb-1.5">{{ $help }}</p>
                        <input type="text" value="{{ $placeholder }}" disabled
                            class="w-full text-xs rounded-lg border border-slate-200 bg-slate-50 text-slate-500 px-3 py-2 cursor-not-allowed">
                    </div>
                @endforeach
            </div>
        </x-marketing.panel>
    </div>
</x-marketing-layout>
