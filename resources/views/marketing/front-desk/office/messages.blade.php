<x-marketing-layout>
    <x-slot:title>Messages</x-slot:title>
    <x-slot:subtitle>Text conversations handled by the SMS agent</x-slot:subtitle>
    <x-slot:toolbar>
        {{-- Chats / Actions: a URL-driven tab (?tab=). --}}
        <div data-dds-panels="tab" class="inline-flex items-center p-0.5 rounded-lg border border-slate-200 bg-white">
            @foreach(['chats' => 'Chats', 'actions' => 'Actions'] as $key => $label)
                <button type="button" data-dds-panel-tab="{{ $key }}"
                    class="px-3 py-1.5 rounded-md text-[12px] font-semibold text-slate-600 aria-selected:bg-blue-600 aria-selected:text-white">{{ $label }}</button>
            @endforeach
        </div>
    </x-slot:toolbar>

    @php
        $initials = fn (?string $name) => $name ? collect(explode(' ', $name))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('') : 'NP';
        $when = function (?string $t) {
            if (! $t) return '';
            $d = \Carbon\CarbonImmutable::parse($t);
            return $d->isToday() ? $d->format('g:i A') : ($d->gt(now()->subWeek()) ? $d->format('D') : $d->format('M j'));
        };
    @endphp

    {{-- Chats. --}}
    <div data-dds-panel-for="tab" data-dds-panel="chats" class="h-[calc(100vh-4rem)] flex">
        {{-- Thread list. --}}
        <aside class="w-[340px] shrink-0 border-r border-slate-200 bg-white flex flex-col">
            <div class="p-3 border-b border-slate-100">
                <label class="relative block">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="search" placeholder="Search by name or phone…" data-fd-thread-search
                        class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-200 text-[12px] focus:outline-none focus:ring-2 focus:ring-blue-500">
                </label>
            </div>
            <ul class="flex-1 overflow-y-auto" data-fd-threads>
                @forelse($threads as $thread)
                    @php $on = $selected && $selected['id'] === $thread['id']; @endphp
                    <li data-fd-thread="{{ strtolower(($thread['name'] ?? '').' '.$thread['phone']) }}">
                        <a href="{{ request()->fullUrlWithQuery(['thread' => $thread['id']]) }}"
                            class="flex gap-3 px-4 py-3 border-b border-slate-50 {{ $on ? 'bg-blue-50/60 shadow-[inset_3px_0_0_#2563eb]' : 'hover:bg-slate-50' }}">
                            <span class="w-9 h-9 rounded-full bg-blue-50 text-blue-700 text-[12px] font-semibold flex items-center justify-center shrink-0">{{ $initials($thread['name']) }}</span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="text-[13px] font-medium text-slate-900 truncate">{{ $thread['name'] ?? $thread['phone'] }}</span>
                                    <span class="flex items-center gap-1 text-[11px] text-slate-400 shrink-0">
                                        @unless($thread['agent_on'])
                                            <i data-lucide="bot-off" class="w-3.5 h-3.5" title="SMS agent off"></i>
                                        @endunless
                                        {{ $when($thread['time']) }}
                                    </span>
                                </span>
                                <span class="flex items-center justify-between gap-2 mt-0.5">
                                    <span class="text-[12px] text-slate-500 truncate">{{ $thread['preview'] }}</span>
                                    @if($thread['unread'])
                                        <span class="min-w-5 h-5 px-1.5 rounded-full bg-blue-600 text-white text-[10px] font-bold flex items-center justify-center">{{ $thread['unread'] }}</span>
                                    @endif
                                </span>
                            </span>
                        </a>
                    </li>
                @empty
                    <li><x-marketing.empty icon="message-square" title="No conversations yet" waiting-on="SMS platform integration" /></li>
                @endforelse
            </ul>
        </aside>

        {{-- Conversation. --}}
        <section class="flex-1 min-w-0 flex flex-col bg-slate-50">
            @if(! $selected)
                <div class="flex-1 flex items-center justify-center">
                    <x-marketing.empty icon="messages-square" title="Select a conversation"
                        message="Texts between patients and the SMS agent show here, including when the agent hands a conversation to staff." />
                </div>
            @else
                <header class="h-14 shrink-0 flex items-center justify-between px-5 bg-white border-b border-slate-200">
                    <h2 class="text-[14px] font-semibold text-slate-900 tabular-nums">{{ $selected['name'] ?? $selected['phone'] }}</h2>
                    <label class="inline-flex items-center gap-2 text-[12px] text-slate-700" title="Toggling the agent arrives with the SMS integration">
                        <input type="checkbox" class="sr-only peer" disabled @checked($selected['agent_on'])>
                        <span class="w-9 h-5 rounded-full bg-slate-200 peer-checked:bg-blue-600 relative after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:rounded-full after:bg-white after:transition peer-checked:after:translate-x-4"></span>
                        SMS Agent
                    </label>
                </header>

                <div class="flex-1 overflow-y-auto px-5 py-4 space-y-3">
                    @foreach($selected['messages'] as $message)
                        @php $t = \Carbon\CarbonImmutable::parse($message['time']); @endphp
                        @if($message['from'] === 'system')
                            <div class="flex justify-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-amber-50 border border-amber-200 text-[11px] font-medium text-amber-800">
                                    <i data-lucide="bot-off" class="w-3.5 h-3.5"></i>{{ $message['text'] }} · {{ $t->format('D g:i A') }}
                                </span>
                            </div>
                        @else
                            @php $mine = $message['from'] === 'office'; @endphp
                            <div class="flex flex-col {{ $mine ? 'items-end' : 'items-start' }}">
                                <p class="max-w-[75%] rounded-2xl border px-4 py-2.5 text-[13px] leading-relaxed {{ $mine ? 'bg-blue-50 border-blue-100 text-slate-800' : 'bg-emerald-50 border-emerald-100 text-slate-800' }}">{{ $message['text'] }}</p>
                                <span class="mt-1 text-[11px] text-slate-400">{{ $t->format('D g:i A') }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>

                <footer class="shrink-0 flex items-center gap-2 px-5 py-3 bg-white border-t border-slate-200">
                    <input type="text" disabled placeholder="Type a message…" title="Sending arrives with the SMS integration"
                        class="flex-1 px-3 py-2 rounded-lg border border-slate-200 text-[13px] bg-slate-50 cursor-not-allowed">
                    <button type="button" disabled class="w-10 h-10 rounded-lg bg-blue-300 text-white flex items-center justify-center cursor-not-allowed" aria-label="Send">
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </button>
                </footer>
            @endif
        </section>
    </div>

    {{-- Actions: follow-ups the SMS agent handed to staff. --}}
    <div data-dds-panel-for="tab" data-dds-panel="actions" hidden class="p-6 max-w-[1200px]">
        <x-marketing.panel title="Actions" subtitle="Conversations the SMS agent handed to staff" icon="list-todo">
            @forelse($actions as $action)
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'chats', 'thread' => $action['thread']]) }}"
                    class="flex items-center justify-between gap-4 py-3 border-b border-slate-100 last:border-0 hover:bg-slate-50 -mx-2 px-2 rounded-lg">
                    <span class="min-w-0">
                        <span class="block text-[13px] font-medium text-slate-900 tabular-nums">{{ $action['phone'] }}</span>
                        <span class="block text-[12px] text-slate-500 truncate">{{ $action['reason'] }}</span>
                    </span>
                    <span class="flex items-center gap-3 shrink-0">
                        <span class="text-[11px] text-slate-400">{{ \Carbon\CarbonImmutable::parse($action['time'])->format('D g:i A') }}</span>
                        <x-front-desk.pill :label="$action['done'] ? 'Done' : 'Open'" :tone="$action['done'] ? 'emerald' : 'amber'" />
                    </span>
                </a>
            @empty
                <x-marketing.empty icon="list-todo" title="No actions" message="When the SMS agent needs a person, the follow-up lands here." />
            @endforelse
        </x-marketing.panel>
    </div>

    <script>
        // Thread search filters the rendered list in place.
        document.querySelector('[data-fd-thread-search]')?.addEventListener('input', function (e) {
            var q = e.target.value.trim().toLowerCase();
            document.querySelectorAll('[data-fd-thread]').forEach(function (li) {
                li.hidden = q !== '' && li.getAttribute('data-fd-thread').indexOf(q) === -1;
            });
        });
    </script>
</x-marketing-layout>
