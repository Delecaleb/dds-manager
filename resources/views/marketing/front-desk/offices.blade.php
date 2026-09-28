<x-marketing-layout>
    <x-slot:title>Offices</x-slot:title>
    <x-slot:subtitle>Manage and monitor your practice offices</x-slot:subtitle>

    <div class="p-6 space-y-4 max-w-[1500px]">
        <div class="flex items-center justify-end">
            {{-- List / Map: a URL-driven tab (?view=). --}}
            <div data-dds-panels="view" class="inline-flex items-center p-0.5 rounded-lg border border-slate-200 bg-white">
                @foreach(['list' => ['List', 'list'], 'map' => ['Map', 'map-pin']] as $key => [$label, $icon])
                    <button type="button" data-dds-panel-tab="{{ $key }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-[12px] font-semibold text-slate-600 aria-selected:bg-blue-600 aria-selected:text-white">
                        <i data-lucide="{{ $icon }}" class="w-3.5 h-3.5"></i>{{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <div data-dds-panel-for="view" data-dds-panel="list">
            <x-marketing.panel title="All offices" icon="building-2">
                <x-data-table id="fdOffices" min-width="900px">
                    <x-slot:head>
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Calls (30d)</th>
                            <th class="px-4 py-3 text-right">Appts (30d)</th>
                            <th class="px-4 py-3 text-right">Production (30d)</th>
                            <th class="px-4 py-3">Address</th>
                        </tr>
                    </x-slot:head>
                    @foreach($offices as $office)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold text-slate-800">
                                <a href="{{ route('marketing.front-desk.office.home', $office['key']) }}" class="hover:text-blue-700">{{ $office['name'] }}</a>
                            </td>
                            <td class="px-4 py-3">@include('marketing.front-desk.partials.office-status', ['status' => $office['status']])</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $office['calls'] ?? -1 }}">{{ ops_fmt($office['calls'], 'number') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $office['appts'] ?? -1 }}">{{ ops_fmt($office['appts'], 'number') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums" data-order="{{ $office['production'] ?? -1 }}">{{ ops_fmt($office['production'], 'money') }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $office['address'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </x-data-table>
            </x-marketing.panel>
        </div>

        <div data-dds-panel-for="view" data-dds-panel="map" hidden>
            @include('marketing.front-desk.partials.office-map', ['offices' => $offices])
        </div>
    </div>

    <script>
        // DataTables loads at the end of the layout body, so initialise once the page is parsed.
        document.addEventListener('DOMContentLoaded', function () {
            DDS.dataTable(document.getElementById('fdOffices'), { pageLength: 25 });
        });
    </script>
</x-marketing-layout>
