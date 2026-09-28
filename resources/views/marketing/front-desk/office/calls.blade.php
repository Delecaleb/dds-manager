<x-marketing-layout>
    <x-slot:title>Call Records</x-slot:title>
    <x-slot:subtitle>View and manage your call history</x-slot:subtitle>

    @php
        $fmtTime = fn (?string $t) => $t ? \Carbon\CarbonImmutable::parse($t)->format('n/j g:i A') : '—';
        $fmtDuration = fn (?int $s) => $s === null ? '—' : sprintf('%02d:%02d', intdiv($s, 60), $s % 60);
    @endphp

    <div class="p-6 space-y-4 max-w-[1600px]">
        {{-- The AI number this list belongs to. --}}
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-[12px] text-slate-700">
                @if($agentNumber)
                    <x-front-desk.pill :label="$agentNumber['designation'] === 'live' ? 'Live' : 'Testing'" :tone="$agentNumber['designation'] === 'live' ? 'emerald' : 'sky'" />
                    <span class="font-medium tabular-nums">{{ $agentNumber['number'] }}</span>
                @else
                    <i data-lucide="phone-off" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span class="text-slate-500">No AI number assigned</span>
                @endif
            </span>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)] gap-4 items-start">
            {{-- Call list. --}}
            <x-marketing.panel title="Calls" icon="phone">
                <x-data-table id="fdCalls" min-width="640px" max-height="640px">
                    <x-slot:head>
                        <tr>
                            <th class="px-4 py-3">Caller</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Time</th>
                            <th class="px-4 py-3 text-center">Triaged</th>
                            <th class="px-4 py-3 text-center">Tasks</th>
                        </tr>
                    </x-slot:head>
                    @foreach($calls as $call)
                        @php $on = $selected && $selected['id'] === $call['id']; @endphp
                        <tr class="cursor-pointer {{ $on ? 'bg-blue-50/60 shadow-[inset_3px_0_0_#2563eb]' : 'hover:bg-slate-50' }}"
                            onclick="window.location = @js(request()->fullUrlWithQuery(['call' => $call['id']]))">
                            <td class="px-4 py-3">
                                <div class="text-[13px] font-medium text-slate-900">{{ $call['name'] ?? $call['phone'] }}</div>
                                @if($call['name'])
                                    <div class="text-[11px] text-slate-500 tabular-nums">{{ $call['phone'] }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3"><x-front-desk.pill :outcome="$call['outcome']" /></td>
                            <td class="px-4 py-3 tabular-nums text-slate-700" data-order="{{ $call['time'] }}">{{ $fmtTime($call['time']) }}</td>
                            <td class="px-4 py-3 text-center" data-order="{{ $call['triaged'] ? 1 : 0 }}">
                                <i data-lucide="{{ $call['triaged'] ? 'circle-check' : 'circle' }}" class="w-4 h-4 inline {{ $call['triaged'] ? 'text-emerald-600' : 'text-slate-300' }}"></i>
                            </td>
                            <td class="px-4 py-3 text-center tabular-nums">{{ $call['tasks'] ?: '' }}</td>
                        </tr>
                    @endforeach
                </x-data-table>
            </x-marketing.panel>

            {{-- Selected call. --}}
            <section class="bg-white border border-slate-200 rounded-xl shadow-sm xl:sticky xl:top-4">
                <header class="flex items-center justify-between gap-3 px-5 py-3.5 border-b border-slate-100">
                    <h2 class="text-[13px] font-bold text-slate-900">Call Details</h2>
                    @if($selected)
                        <div class="flex items-center gap-2">
                            <x-front-desk.pill :outcome="$selected['outcome']" />
                            <x-front-desk.pill :label="ucfirst($selected['direction'])" tone="emerald" />
                        </div>
                    @endif
                </header>

                @if(! $selected)
                    <x-marketing.empty icon="phone" title="No calls yet"
                        message="Calls the AI answers or places for this office appear here with a summary, the recording and the transcript."
                        waiting-on="Call platform integration" />
                @else
                    <div class="p-5 space-y-5 max-h-[640px] overflow-y-auto">
                        <div class="flex items-center justify-between">
                            <h3 class="text-[12px] font-semibold text-slate-700">Tasks</h3>
                            <button type="button" disabled title="Tasks arrive with the call platform integration"
                                class="px-2.5 py-1 rounded-lg border border-slate-200 text-[11px] font-semibold text-slate-400 cursor-not-allowed">Create Task</button>
                        </div>

                        <div>
                            <h3 class="text-[12px] font-semibold text-slate-700 mb-2">Summary</h3>
                            <p class="rounded-lg bg-slate-50 px-4 py-3 text-[12px] text-slate-700 leading-relaxed">
                                {{ $selected['summary'] ?? 'Incomplete call, summary not available.' }}
                            </p>
                        </div>

                        <div>
                            <h3 class="text-[12px] font-semibold text-slate-700 mb-2">Call Recording</h3>
                            <div class="rounded-lg border border-slate-200 p-3">
                                <div class="h-10 flex items-center gap-[2px] overflow-hidden" aria-hidden="true">
                                    @for($i = 0; $i < 90; $i++)
                                        <span class="w-[2px] rounded-full bg-blue-400/70" style="height: {{ 6 + (crc32($selected['id'].$i) % 28) }}px"></span>
                                    @endfor
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <button type="button" disabled class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 text-[11px] font-semibold text-slate-400 cursor-not-allowed">
                                            <i data-lucide="play" class="w-3 h-3"></i> Play
                                        </button>
                                        <button type="button" disabled class="px-2.5 py-1 rounded-lg border border-slate-200 text-[11px] font-semibold text-slate-400 cursor-not-allowed">1x</button>
                                    </div>
                                    <span class="font-mono text-[12px] text-slate-600">{{ $fmtDuration($selected['duration']) }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-[12px] font-semibold text-slate-700 mb-1">Patient Details</h3>
                            <dl>
                                @if($selected['name'])
                                    <x-front-desk.field label="Name" :value="$selected['name']" />
                                    <x-front-desk.field label="Intent" :value="$selected['intent']" />
                                @endif
                                <x-front-desk.field label="Phone" :value="$selected['phone']" />
                                @if($selected['dob'])
                                    @php $dob = \Carbon\CarbonImmutable::parse($selected['dob']); @endphp
                                    <x-front-desk.field label="Date of Birth" :value="$dob->format('M j, Y')" />
                                    <x-front-desk.field label="Age" :value="(string) $dob->age" />
                                @endif
                                <x-front-desk.field label="Call ID" :value="$selected['id']" />
                            </dl>
                        </div>

                        @if($selected['transcript'] !== [])
                            <div>
                                <h3 class="text-[12px] font-semibold text-slate-700 mb-2">Transcript</h3>
                                <div class="space-y-2">
                                    @foreach($selected['transcript'] as $line)
                                        <div class="flex {{ $line['speaker'] === 'caller' ? 'justify-start' : 'justify-end' }}">
                                            <p class="max-w-[85%] rounded-xl px-3 py-2 text-[12px] leading-relaxed {{ $line['speaker'] === 'caller' ? 'bg-emerald-50 text-slate-800' : 'bg-blue-50 text-slate-800' }}">{{ $line['text'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            DDS.dataTable(document.getElementById('fdCalls'), { pageLength: 50, order: [[2, 'desc']] });
        });
    </script>
</x-marketing-layout>
