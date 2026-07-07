<x-app-layout>
    {{-- Page Title Header --}}
    <div class="bg-white px-6 py-5 flex items-center justify-between border-b border-slate-200">
        <h1 class="text-3xl font-extrabold text-[#0a1128] tracking-tight">Hygiene Recall</h1>
    </div>

    {{-- Controls Header --}}
    <div class="bg-white px-6 py-4 flex items-center gap-3 border-b border-slate-200">
        <x-daterange-picker id="hygieneDateRange" on-apply="refreshHygieneTable" />

        <select
            class="h-9 border border-slate-300 rounded shadow-sm px-3 font-medium text-slate-700 text-sm focus:outline-emerald-500 w-48">
            <option>8 Mile</option>
        </select>

        <button id="refreshBtn"
            class="h-9 border border-emerald-500 text-emerald-700 font-bold px-5 rounded shadow-sm hover:bg-emerald-50 transition text-sm">
            Refresh
        </button>
    </div>

    {{-- Main Content --}}
    <main class="p-6 bg-slate-50/50 min-h-[calc(100vh-140px)]">
        <h2 class="text-lg font-bold text-[#0a1128] mb-4 tracking-wide">Summary</h2>

        <div class="bg-white border border-slate-200 rounded-md shadow-sm">

            {{-- Table Tools --}}
            <div class="flex items-center justify-end gap-3 p-4 border-b border-slate-100">
                <select
                    class="border border-emerald-500 text-emerald-700 font-bold px-3 py-1.5 rounded-sm bg-white text-sm focus:outline-none">
                    <option>Display Columns</option>
                </select>

                <div class="relative">
                    <input type="text" id="tableSearch" placeholder="Search"
                        class="border border-slate-300 pr-9 pl-3 py-1.5 rounded-sm text-slate-700 w-56 focus:outline-emerald-500 text-sm">
                    <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <button
                    class="border border-emerald-500 text-emerald-700 font-bold px-4 py-1.5 rounded-sm hover:bg-emerald-50 transition text-sm">
                    Export CSV
                </button>
            </div>

            {{-- Table Wrapper --}}
            <div class="w-full relative overflow-x-auto">
                <x-data-table id="hygieneRecallTable" min-width="1200px" max-height="100%">
                    <x-slot:head>
                        <tr class="bg-white">
                            <th class="text-[11px] font-bold text-slate-800 py-4 px-4 text-left border-b border-slate-200 w-48 dt-col-sticky"
                                style="left:0;">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 11V7a5 5 0 0110 0v4 M5 11l7 7 7-7" />
                                    </svg>
                                    Provider
                                </div>
                            </th>
                            <th class="text-[11px] font-bold text-slate-800 py-4 px-4 text-left border-l border-b border-slate-200 w-32 bg-slate-50/50 dt-col-sticky"
                                style="left:12rem;">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 11V7a5 5 0 0110 0v4 M5 11l7 7 7-7" />
                                    </svg>
                                    Provider ID
                                </div>
                            </th>
                            <th
                                class="text-[11px] font-bold text-slate-800 py-4 px-4 text-left border-l border-b border-slate-200 w-32">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 11V7a5 5 0 0110 0v4 M5 11l7 7 7-7" />
                                    </svg>
                                    Office
                                </div>
                            </th>
                            <th
                                class="text-[11px] font-bold text-slate-800 py-4 px-4 text-right border-l border-b border-slate-200 w-40">
                                <div class="flex items-center justify-end gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 11V7a5 5 0 0110 0v4 M5 11l7 7 7-7" />
                                    </svg>
                                    Missed Recall
                                </div>
                            </th>
                            <th
                                class="text-[11px] font-bold text-slate-800 py-4 px-4 text-right border-l border-b border-slate-200 w-40">
                                <div class="flex items-center justify-end gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 11V7a5 5 0 0110 0v4 M5 11l7 7 7-7" />
                                    </svg>
                                    Patient Recalled
                                </div>
                            </th>
                            <th
                                class="text-[11px] font-bold text-slate-800 py-4 px-4 text-right border-l border-b border-slate-200 w-48">
                                <div class="flex items-center justify-end gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 11V7a5 5 0 0110 0v4 M5 11l7 7 7-7" />
                                    </svg>
                                    # of Future Appointments
                                </div>
                            </th>
                            <th
                                class="text-[11px] font-bold text-slate-800 py-4 px-4 text-right border-l border-b border-slate-200 w-48">
                                <div class="flex items-center justify-end gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 11V7a5 5 0 0110 0v4 M5 11l7 7 7-7" />
                                    </svg>
                                    Patients Recalled $
                                </div>
                            </th>
                            <th
                                class="text-[11px] font-bold text-slate-800 py-4 px-4 text-right border-l border-b border-slate-200 w-48">
                                <div class="flex items-center justify-end gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 11V7a5 5 0 0110 0v4 M5 11l7 7 7-7" />
                                    </svg>
                                    Patient Recall Rate %
                                </div>
                            </th>
                        </tr>
                    </x-slot:head>

                    {{-- Body is populated dynamically by DataTables via API --}}
                </x-data-table>
            </div>

        </div>
    </main>

    @push('scripts')
        <script>
            $(document.ready(function () {
                let table = $('#hygieneRecallTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('hygiene-recall.data') }}",
                        data: function (d) {
                            var drp = $('#hygieneDateRange').data('daterangepicker');
                            if (drp) {
                                d.start_date = drp.startDate.format('YYYY-MM-DD');
                                d.end_date = drp.endDate.format('YYYY-MM-DD');
                            }
                        }
                    },
                    columns: [
                        { data: 'provider_name', name: 'provider_name' },
                        { data: 'provider_id', name: 'provider_id' },
                        { data: 'office', name: 'office' },
                        { data: 'missed_recall', name: 'missed_recall' },
                        { data: 'patient_recalled', name: 'patient_recalled' },
                        { data: 'future_appointments', name: 'future_appointments' },
                        { data: 'patients_recalled_dollars', name: 'patients_recalled_dollars' },
                        { data: 'patient_recall_rate', name: 'patient_recall_rate' }
                    ],
                    dom: 'rt<"flex justify-between items-center px-5 py-4 border-t border-slate-100 bg-white"ip>',
                    pagingType: 'simple_numbers',
                    pageLength: 10,
                    language: { paginate: { previous: "Prev", next: "Next" }, processing: "" },
                    createdRow: function (row, data, dataIndex) {
                        $(row).addClass('hover:bg-slate-50 transition border-b border-slate-100 last:border-0');

                        // Fixed Columns styling matching Figma
                        $('td:eq(0)', row).addClass('dt-col-sticky bg-white font-medium text-slate-700 border-r border-slate-100 p-4 text-[13px]').css('left', '0');
                        $('td:eq(1)', row).addClass('dt-col-sticky bg-slate-50/50 font-bold text-slate-800 border-r border-slate-100 p-4 text-[13px] shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]').css('left', '12rem');

                        // Remainder styling
                        $('td', row).not(':lt(2)').addClass('text-right p-4 font-medium text-slate-600 text-[13px] border-l border-slate-100');
                        $('td:eq(2)', row).removeClass('text-right').addClass('text-left'); // Office column

                        // Inject decorative icon to all except the percentage
                        const svgLink = '<div class="ml-2 inline-flex items-center justify-center p-0.5 border border-slate-200 rounded text-slate-400 bg-slate-50 hover:bg-slate-100 flex-shrink-0"><svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="square" stroke-width="2" d="M7 17l9.2-9.2M17 17V7H7"/></svg></div>';

                        for (let i = 0; i <= 6; i++) {
                            if (i === 2) continue; // Office column skipped
                            let td = $('td:eq(' + i + ')', row);
                            let isRight = td.hasClass('text-right');
                            let content = td.html();
                            if (isRight) {
                                td.html('<div class="flex items-center justify-end w-full">' + content + svgLink + '</div>');
                            } else {
                                td.html('<div class="flex items-center justify-between w-full">' + content + svgLink + '</div>');
                            }
                        }
                    }
                });

                $('#tableSearch').on('keyup', function () {
                    table.search(this.value).draw();
                });

                window.refreshHygieneTable = function (start, end) {
                    table.ajax.reload();
                };

                $('#refreshBtn').on('click', function () {
                    table.ajax.reload();
                });
            }));
        </script>
    @endpush
</x-app-layout>
