<x-app-components.drilldown.table-modal :title="$title" :provider-info="$providerInfo ?? null">
    {{-- dds-datatable → auto-initialised as the shared, sortable DataTable when the modal
         opens (DDS.dataTableAll). No fixed id, so stacked drilldowns never collide. --}}
    <table class="dds-table {{ count($rows) ? 'dds-datatable' : '' }} w-full text-left text-xs whitespace-nowrap">
        <thead>
            <tr>
                @foreach ($columns as $col)
                    <th
                        class="py-2.5 px-4 font-bold text-gray-900 border-b border-gray-200 capitalize {{ ($col['type'] ?? 'text') === 'money' || ($col['type'] ?? 'text') === 'percent' ? 'text-right' : 'text-left' }}">
                        {{ $col['label'] }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-gray-700">
            @forelse ($rows as $row)
                <tr class="hover:bg-gray-50 border-b border-gray-50">
                    @foreach ($columns as $col)
                        @php
                            $val = $row[$col['key']] ?? null;
                            $align = ($col['type'] ?? 'text') === 'money' || ($col['type'] ?? 'text') === 'percent' ? 'text-right' : 'text-left';

                            $isLink = is_array($val) && !empty($val['link']);
                            $displayStr = $isLink ? $val['label'] : $val;

                            // Numeric columns carry data-order so the DataTable sorts by the raw
                            // value, not the formatted "$ 1,234.56" / "12.34%" text.
                            $orderAttr = '';
                            if ($col['type'] === 'money') {
                                $orderAttr = 'data-order="' . (float) $displayStr . '"';
                                $displayStr = '$ ' . number_format((float) $displayStr, 2);
                            } elseif ($col['type'] === 'percent') {
                                $orderAttr = 'data-order="' . (float) $displayStr . '"';
                                $displayStr = number_format((float) $displayStr, 2) . '%';
                            }
                        @endphp
                        <td {!! $orderAttr !!} class="py-3 px-4 {{ $align }} font-medium">
                            @if ($isLink && $col['key'] === 'patient')
                                {!! e($displayStr) !!}
                                <button type="button"
                                    class="text-[#00bfa5] ml-1 hover:text-[#009688] focus:outline-none shrink-0 inline-block align-middle"
                                    onclick="if(typeof openPatient === 'function') openPatient('{{ $row['pat_id'] }}'); else alert('Patient dig-deep must be imported globally.');">
                                    <svg class="h-3.5 w-3.5 stroke-current cursor-pointer" fill="none" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </button>
                            @elseif ($isLink && $col['key'] === 'provider')
                                {!! e($displayStr) !!}
                                <button type="button"
                                    class="text-[#00bfa5] ml-1 hover:text-[#009688] focus:outline-none shrink-0 inline-block align-middle"
                                    onclick="if(typeof openProviderModal === 'function') openProviderModal('{{ $row['prov_num'] ?? $row['prov_id'] }}'); else alert('Provider dig-deep must be imported globally.');">
                                    <svg class="h-3.5 w-3.5 stroke-current cursor-pointer" fill="none" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </button>
                            @elseif ($col['key'] === 'note')
                                <div class="group relative cursor-help max-w-[200px] inline-block align-middle" title="{{ $displayStr }}">
                                    <div class="truncate text-gray-700 font-normal max-w-[200px]">{!! e($displayStr) !!}</div>
                                    @if (!empty($displayStr) && $displayStr !== '—' && $displayStr !== 'No note')
                                        <div class="pointer-events-none absolute bottom-full left-1/2 mb-2 hidden -translate-x-1/2 group-hover:block z-[150] w-64 rounded-lg bg-gray-900 px-3 py-2 text-xs font-normal text-white shadow-xl whitespace-normal break-words">
                                            {!! e($displayStr) !!}
                                            <div class="absolute top-full left-1/2 -mt-1 -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                {!! e($displayStr) !!}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}" class="py-8 text-center text-gray-400 text-sm">
                        No records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($totals)
            <tfoot class="sticky bottom-0 bg-gray-50 border-t border-gray-200">
                <tr class="font-bold text-gray-900 border-t border-gray-300">
                    @foreach ($columns as $index => $col)
                        @php
                            $align = ($col['type'] ?? 'text') === 'money' || ($col['type'] ?? 'text') === 'percent' ? 'text-right' : 'text-left';
                        @endphp
                        @if ($index === 0)
                            <td class="py-3 px-4">Total:</td>
                        @elseif (isset($totals[$col['key']]))
                            @php
                                $totVal = $totals[$col['key']];
                                if ($col['type'] === 'money') {
                                    $totVal = '$ ' . number_format((float) $totVal, 2);
                                } elseif ($col['type'] === 'percent') {
                                    $totVal = number_format((float) $totVal, 2) . '%';
                                }
                            @endphp
                            <td class="py-3 px-4 {{ $align }}">{!! e($totVal) !!}</td>
                        @else
                            <td class="py-3 px-4"></td>
                        @endif
                    @endforeach
                </tr>
            </tfoot>
        @endif
    </table>
</x-app-components.drilldown.table-modal>