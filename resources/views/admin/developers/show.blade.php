<x-app-layout>
    <div class="p-6 max-w-[1600px] mx-auto space-y-6">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-[#001f3f] text-emerald-400 flex items-center justify-center shadow-inner">
                    <i data-lucide="activity" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ $key->name }}</h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ $locationName }} · <span class="font-mono">{{ $key->maskedKey() }}</span> ·
                        created {{ $key->created_at->format('M j, Y') }} by {{ $key->creator?->name ?? 'Unknown' }}
                        @if ($key->revoked_at) · <span class="text-rose-700 font-semibold">revoked {{ $key->revoked_at->format('M j, Y g:i A') }}</span> @endif
                    </p>
                </div>
            </div>
            <a href="{{ $backUrl }}" class="px-3 py-2 text-xs font-semibold rounded-lg border border-slate-200 text-slate-600 bg-white hover:bg-slate-50">Back to keys</a>
        </div>

        @include('developer._usage', ['scope' => 'This key'])

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900">Recent requests</h2>
                <span class="text-[11px] text-slate-500">Latest {{ $activityLimit }}, newest first</span>
            </div>
            @if ($requests->isEmpty())
                <p class="px-5 py-4 text-xs text-slate-500">This key has not been used yet.</p>
            @else
                <div class="p-3">
                    <x-data-table id="devKeyActivity" min-width="900px" data-dev-activity>
                        <x-slot:head>
                            <tr>
                                <th class="px-4 py-3">When</th>
                                <th class="px-4 py-3">Request</th>
                                <th class="px-4 py-3 text-right">Status</th>
                                <th class="px-4 py-3 text-right">Records</th>
                                <th class="px-4 py-3 text-right">Time (ms)</th>
                                <th class="px-4 py-3">IP</th>
                            </tr>
                        </x-slot:head>
                        @foreach ($requests as $logged)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap" data-order="{{ $logged->created_at->timestamp }}">{{ $logged->created_at->format('M j, Y g:i:s A') }}</td>
                                <td class="px-4 py-3 font-mono text-[11px] text-slate-700 break-all">{{ $logged->method }} {{ $logged->path }}@if ($logged->query)?{{ $logged->query }}@endif</td>
                                <td class="px-4 py-3 text-right tabular-nums {{ $logged->status >= 400 ? 'text-rose-700 font-semibold' : 'text-emerald-700' }}">{{ $logged->status }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ $logged->rows ?? '—' }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($logged->duration_ms) }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $logged->ip }}</td>
                            </tr>
                        @endforeach
                    </x-data-table>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var table = document.querySelector('[data-dev-activity]');
            if (table) DDS.dataTable(table, { paging: true, pageLength: 50, order: [[0, 'desc']] });
        });
    </script>
</x-app-layout>
