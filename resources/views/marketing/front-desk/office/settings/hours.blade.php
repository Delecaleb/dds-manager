@php
    $times = [];
    for ($m = 6 * 60; $m <= 21 * 60; $m += 15) {
        $h = intdiv($m, 60);
        $times[] = ($h % 12 ?: 12).($m % 60 ? ':'.str_pad((string) ($m % 60), 2, '0', STR_PAD_LEFT) : '').($h < 12 ? 'am' : 'pm');
    }
@endphp

<p class="text-[13px] text-slate-600 -mt-2">Configure your business/admin hours as well as schedule overrides.</p>

<x-marketing.panel title="Schedule Closures" subtitle="Override your schedule and close certain days.">
    <div class="flex flex-wrap gap-2">
        @forelse($settings['closures'] as $day)
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-[12px] text-slate-700">
                {{ \Carbon\CarbonImmutable::parse($day)->format('D, M j') }}<i data-lucide="x" class="w-3.5 h-3.5 text-slate-400"></i>
            </span>
        @empty
            <span class="text-[12px] text-slate-500">No closures scheduled.</span>
        @endforelse
    </div>
    <button type="button" disabled title="Editing closures arrives with the agent configuration backend"
        class="mt-3 inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 text-[13px] text-slate-400 cursor-not-allowed">
        <i data-lucide="calendar-plus" class="w-4 h-4"></i> Add closure date
    </button>
</x-marketing.panel>

@foreach([
    'business' => ['Business Hours', 'Configure when your business is open for appointments.'],
    'admin' => ['Admin Hours', 'Configure when your staff is available for administrative tasks.'],
] as $key => [$title, $subtitle])
    <x-marketing.panel :title="$title" :subtitle="$subtitle">
        <x-data-table id="fdHours{{ ucfirst($key) }}" min-width="520px">
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 w-12"><span class="sr-only">Open</span></th>
                    <th class="px-4 py-3">Day</th>
                    <th class="px-4 py-3">Hours</th>
                </tr>
            </x-slot:head>
            @foreach($settings[$key] as $day => $hours)
                <tr>
                    <td class="px-4 py-3"><input type="checkbox" class="rounded border-slate-300 text-blue-600" @checked(is_array($hours)) aria-label="{{ $day }} open"></td>
                    <td class="px-4 py-3 text-[13px] {{ is_array($hours) ? 'text-slate-800' : 'text-slate-400' }}">{{ $day }}</td>
                    <td class="px-4 py-3">
                        @if(is_array($hours))
                            <div class="flex items-center gap-2">
                                @foreach($hours as $i => $value)
                                    @if($i) <span class="text-slate-400">-</span> @endif
                                    <select class="rounded-lg border border-slate-200 text-[12px] px-2 py-1" aria-label="{{ $day }} {{ $i ? 'close' : 'open' }}">
                                        @foreach($times as $t)
                                            <option @selected($t === $value)>{{ $t }}</option>
                                        @endforeach
                                    </select>
                                @endforeach
                            </div>
                        @else
                            <span class="text-[13px] text-slate-400">{{ $hours === false ? 'Closed' : 'Not set' }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </x-marketing.panel>
@endforeach

@include('marketing.front-desk.office.settings._save')
