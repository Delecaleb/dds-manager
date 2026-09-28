<x-marketing-layout>
    <x-slot:title>Schedule</x-slot:title>
    <x-slot:subtitle>View appointments</x-slot:subtitle>

    @php
        $firstHour = 6;
        $lastHour = 19;
        $hourPx = 72;
        $columns = $schedule['columns'];
        $clock = fn (int $m) => (intdiv($m, 60) % 12 ?: 12).':'.str_pad((string) ($m % 60), 2, '0', STR_PAD_LEFT);
        $dayUrl = fn ($d) => request()->fullUrlWithQuery(['date' => $d->toDateString()]);
    @endphp

    <div class="p-6 max-w-[1600px]">
        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <header class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-slate-100">
                <h2 class="flex items-center gap-2 text-[16px] font-semibold text-slate-900">
                    <i data-lucide="calendar" class="w-4 h-4 text-slate-500"></i>{{ $date->format('D, F j') }}
                </h2>
                <div class="flex flex-wrap items-center gap-2">
                    <select aria-label="Appointment type" @disabled($schedule['appointmentTypes'] === [])
                        class="rounded-lg border border-slate-200 bg-white text-[12px] font-medium text-slate-700 px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @forelse($schedule['appointmentTypes'] as $type)
                            <option>Appointment type: {{ $type }}</option>
                        @empty
                            <option>Appointment type: —</option>
                        @endforelse
                    </select>
                    <button type="button" disabled title="Finding availability arrives with the booking integration"
                        class="px-3 py-1.5 rounded-lg border border-slate-200 text-[12px] font-semibold text-slate-400 cursor-not-allowed">Soonest Availability</button>
                    <a href="{{ $dayUrl(now()) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Today</a>
                    <div class="inline-flex rounded-lg border border-slate-200">
                        <a href="{{ $dayUrl($date->subDay()) }}" class="px-2 py-1.5 text-slate-600 hover:bg-slate-50" aria-label="Previous day"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>
                        <a href="{{ $dayUrl($date->addDay()) }}" class="px-2 py-1.5 border-l border-slate-200 text-slate-600 hover:bg-slate-50" aria-label="Next day"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
                    </div>
                    <a href="{{ request()->fullUrl() }}" class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50" aria-label="Refresh"><i data-lucide="refresh-cw" class="w-4 h-4"></i></a>
                </div>
            </header>

            @if($columns === [])
                <x-marketing.empty icon="calendar-days" title="No schedule to show"
                    message="The day view lists each operatory's appointments and the open slots the AI can book."
                    waiting-on="Operatory and appointment data for AI booking" />
            @else
                <div class="overflow-x-auto max-h-[calc(100vh-14rem)] overflow-y-auto">
                    <div class="grid min-w-[900px]" style="grid-template-columns: 5rem repeat({{ count($columns) }}, minmax(8rem, 1fr))">
                        {{-- Column heads. --}}
                        <div class="sticky top-0 z-10 bg-white border-b-2 border-rose-400"></div>
                        @foreach($columns as $column)
                            <div class="sticky top-0 z-10 bg-white border-b-2 border-rose-400 border-l border-slate-100 px-2 py-2.5 text-center text-[12px] font-semibold text-slate-700">{{ $column }}</div>
                        @endforeach

                        {{-- Hour gutter. --}}
                        <div class="relative" style="height: {{ ($lastHour - $firstHour) * $hourPx }}px">
                            @for($h = $firstHour; $h < $lastHour; $h++)
                                <div class="absolute left-0 right-0 border-t border-slate-100 px-3 pt-1 text-[11px] text-slate-500" style="top: {{ ($h - $firstHour) * $hourPx }}px">{{ ($h % 12 ?: 12).($h < 12 ? 'am' : 'pm') }}</div>
                            @endfor
                        </div>

                        {{-- One lane per column. --}}
                        @foreach($columns as $c => $column)
                            <div class="relative border-l border-slate-100" style="height: {{ ($lastHour - $firstHour) * $hourPx }}px">
                                @for($h = $firstHour; $h < $lastHour; $h++)
                                    <div class="absolute left-0 right-0 border-t border-slate-100" style="top: {{ ($h - $firstHour) * $hourPx }}px"></div>
                                @endfor
                                @foreach($schedule['appointments'] as $appt)
                                    @continue($appt['column'] !== $c)
                                    <div class="absolute left-0.5 right-0.5 rounded bg-blue-100/80 border-l-2 border-blue-500 px-1.5 py-1 overflow-hidden text-[11px] leading-tight text-blue-900"
                                        style="top: {{ ($appt['start'] / 60 - $firstHour) * $hourPx }}px; height: {{ ($appt['end'] - $appt['start']) / 60 * $hourPx - 2 }}px">
                                        <div class="font-semibold tabular-nums">{{ $clock($appt['start']) }}-{{ $clock($appt['end']) }}</div>
                                        <div class="truncate">{{ $appt['patient'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    </div>
</x-marketing-layout>
