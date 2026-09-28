<x-marketing-layout>
    <x-slot:title>AI Website Builder</x-slot:title>
    <x-slot:subtitle>Websites written by AI from your business brief</x-slot:subtitle>
    <x-slot:toolbar>
        <a href="{{ route('marketing.site-builder.create') }}"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-[12px] font-semibold hover:bg-emerald-700">
            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> New website
        </a>
    </x-slot:toolbar>

    <div class="p-6 space-y-4 max-w-[1500px]">
        <x-marketing.flash />

        <x-marketing.panel title="Websites" icon="layout-template">
            @if($builds->isEmpty())
                <x-marketing.empty icon="layout-template" title="No websites yet"
                    message="Give the AI your business details, brand, pages and keywords — it writes the site, and you download it as a zip." />
                <div class="flex justify-center pb-4">
                    <a href="{{ route('marketing.site-builder.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-emerald-600 text-white text-[12px] font-semibold hover:bg-emerald-700">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Build your first website
                    </a>
                </div>
            @else
                <x-data-table id="sbBuilds" min-width="820px">
                    <x-slot:head>
                        <tr>
                            <th class="px-4 py-3">Website</th>
                            <th class="px-4 py-3">Domain</th>
                            <th class="px-4 py-3 text-right">Pages</th>
                            <th class="px-4 py-3">Latest run</th>
                            <th class="px-4 py-3 text-right">Versions</th>
                            <th class="px-4 py-3">Updated</th>
                        </tr>
                    </x-slot:head>
                    @foreach($builds as $build)
                        @php $latest = $build->versions->first(); @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-[13px] font-semibold text-slate-800">
                                <a href="{{ route('marketing.site-builder.show', $build) }}" class="hover:text-emerald-700">{{ $build->name }}</a>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $build->domain ?? '—' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ count($build->pages) }}</td>
                            <td class="px-4 py-3">
                                @if($latest)
                                    @include('marketing.site-builder._status', ['status' => $latest->status])
                                    <span class="ml-1 text-[11px] text-slate-400">v{{ $latest->number }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $build->versions_count }}</td>
                            <td class="px-4 py-3 text-slate-500" data-order="{{ $build->updated_at->timestamp }}">{{ $build->updated_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </x-data-table>
            @endif
        </x-marketing.panel>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var table = document.getElementById('sbBuilds');
            if (table) DDS.dataTable(table, { pageLength: 25 });
        });
    </script>
</x-marketing-layout>
