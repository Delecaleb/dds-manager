<x-app-layout>
    {{-- Page Title Header --}}
    <div class="bg-white px-6 py-5 flex items-center justify-between border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-extrabold text-[#0a1128] tracking-tight">Hygiene Recall</h1>
            <p class="text-xs text-slate-500 mt-1">Track due hygiene recall patients, scheduled recares, missed appointments, and recovered production.</p>
        </div>
    </div>

    {{-- Controls Header --}}
    <div class="bg-white px-6 py-3.5 flex flex-wrap items-center gap-3 border-b border-slate-200">
        <x-daterange-picker id="hygieneDateRange" />

        <select id="hygieneLocation"
            class="h-9 border border-slate-300 rounded-lg shadow-sm px-3 font-medium text-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 min-w-[180px]">
            <option value="all">All Locations</option>
            @foreach ($clinics as $clinicId => $clinicName)
                <option value="{{ $clinicId }}">{{ $clinicName }}</option>
            @endforeach
        </select>

        <button id="refreshBtn"
            class="h-9 inline-flex items-center gap-1.5 border border-emerald-500 text-emerald-700 font-bold px-4 rounded-lg shadow-sm hover:bg-emerald-50 transition text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
        </button>
    </div>

    {{-- Main Content --}}
    <main class="p-6 bg-slate-50/50 min-h-[calc(100vh-140px)]">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">

            {{-- Table Tools --}}
            <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-slate-100 bg-white">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-bold text-[#0a1128] tracking-wide">Provider Recall Summary</h2>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative">
                        <input type="text" id="tableSearch" placeholder="Search provider or office..."
                            class="border border-slate-300 pr-9 pl-3 py-1.5 rounded-lg text-slate-700 w-64 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-sm">
                        <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <button id="exportCsvBtn" type="button"
                        class="inline-flex items-center gap-1.5 border border-emerald-600 bg-emerald-50 text-emerald-700 font-bold px-3.5 py-1.5 rounded-lg hover:bg-emerald-100 transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export CSV
                    </button>
                </div>
            </div>

            {{-- Table Wrapper --}}
            <div class="w-full relative overflow-x-auto">
                <x-data-table id="hygieneRecallTable" min-width="1100px" max-height="100%">
                    <x-slot:head>
                        <tr class="bg-slate-50/80 border-b border-slate-200">
                            <th class="text-[12px] font-bold text-slate-700 py-3.5 px-4 text-left w-52 dt-col-sticky"
                                style="left:0;">
                                Provider
                            </th>
                            <th class="text-[12px] font-bold text-slate-700 py-3.5 px-4 text-left w-36 bg-slate-50/90 dt-col-sticky"
                                style="left:13rem;">
                                Provider ID
                            </th>
                            <th class="text-[12px] font-bold text-slate-700 py-3.5 px-4 text-left w-40">
                                Office
                            </th>
                            <th class="text-[12px] font-bold text-slate-700 py-3.5 px-4 text-right w-36">
                                Missed Recall
                            </th>
                            <th class="text-[12px] font-bold text-slate-700 py-3.5 px-4 text-right w-36">
                                Patient Recalled
                            </th>
                            <th class="text-[12px] font-bold text-slate-700 py-3.5 px-4 text-right w-44">
                                # Future Apts
                            </th>
                            <th class="text-[12px] font-bold text-slate-700 py-3.5 px-4 text-right w-44">
                                Recalled $
                            </th>
                            <th class="text-[12px] font-bold text-slate-700 py-3.5 px-4 text-right w-40">
                                Recall Rate %
                            </th>
                        </tr>
                    </x-slot:head>

                    <x-slot:foot>
                        <tr class="bg-slate-100/80 font-semibold text-slate-700 border-t-2 border-slate-300 text-[13px]">
                            <td class="py-3 px-4 dt-col-sticky bg-slate-100/90" style="left:0;">Average:</td>
                            <td class="py-3 px-4 dt-col-sticky bg-slate-100/90" style="left:13rem;">—</td>
                            <td class="py-3 px-4">—</td>
                            <td id="footAvg-missed" class="py-3 px-4 text-right font-medium">—</td>
                            <td id="footAvg-recalled" class="py-3 px-4 text-right font-medium">—</td>
                            <td id="footAvg-futureApts" class="py-3 px-4 text-right font-medium">—</td>
                            <td id="footAvg-dollars" class="py-3 px-4 text-right font-medium">—</td>
                            <td id="footAvg-rate" class="py-3 px-4 text-right font-medium">—</td>
                        </tr>
                        <tr class="bg-slate-200/90 font-bold text-slate-900 border-t border-slate-300 text-[13px]">
                            <td class="py-3 px-4 dt-col-sticky bg-slate-200" style="left:0;">Total:</td>
                            <td class="py-3 px-4 dt-col-sticky bg-slate-200" style="left:13rem;">—</td>
                            <td class="py-3 px-4">—</td>
                            <td id="footTot-missed" class="py-3 px-4 text-right font-bold">—</td>
                            <td id="footTot-recalled" class="py-3 px-4 text-right font-bold">—</td>
                            <td id="footTot-futureApts" class="py-3 px-4 text-right font-bold">—</td>
                            <td id="footTot-dollars" class="py-3 px-4 text-right font-bold text-emerald-700">—</td>
                            <td id="footTot-rate" class="py-3 px-4 text-right font-bold">—</td>
                        </tr>
                    </x-slot:foot>
                </x-data-table>
            </div>

        </div>
    </main>

    <script>
        const baseUrl = "{{ url('') }}";

        $(document).ready(function () {
            function getFilters() {
                let startDate = null;
                let endDate = null;
                const drp = $('#hygieneDateRange').data('daterangepicker');
                if (drp && drp.startDate && drp.endDate) {
                    startDate = drp.startDate.format('YYYY-MM-DD');
                    endDate = drp.endDate.format('YYYY-MM-DD');
                }
                const clinic = $('#hygieneLocation').val();

                return {
                    start_date: startDate,
                    end_date: endDate,
                    clinic: clinic
                };
            }

            window.openHygieneDrilldown = function (metric, provNum, clinicNum) {
                const filters = getFilters();
                const params = Object.assign({}, filters, {
                    metric: metric,
                    prov_num: provNum,
                    clinic: clinicNum || filters.clinic
                });
                const url = baseUrl + '/hygiene-recall/drilldown?' + $.param(params);
                if (window.DDS && DDS.modal && typeof DDS.modal.open === 'function') {
                    DDS.modal.open(url);
                } else if (typeof openLimitlessModal === 'function') {
                    openLimitlessModal(url);
                }
            };

            function updateFooter(total, average) {
                if (average) {
                    $('#footAvg-missed').text(average.missed_recall ?? '—');
                    $('#footAvg-recalled').text(average.patient_recalled ?? '—');
                    $('#footAvg-futureApts').text(average.future_appointments ?? '—');
                    $('#footAvg-dollars').text(average.patients_recalled_dollars ?? '—');
                    $('#footAvg-rate').text(average.patient_recall_rate ?? '—');
                }
                if (total) {
                    $('#footTot-missed').text(total.missed_recall ?? '—');
                    $('#footTot-recalled').text(total.patient_recalled ?? '—');
                    $('#footTot-futureApts').text(total.future_appointments ?? '—');
                    $('#footTot-dollars').text(total.patients_recalled_dollars ?? '—');
                    $('#footTot-rate').text(total.patient_recall_rate ?? '—');
                }
            }

            function renderMetricBtn(data, metric, row, rawVal) {
                const orderAttr = rawVal !== undefined && rawVal !== null ? `data-order="${rawVal}"` : '';
                return `
                    <button type="button" ${orderAttr} class="w-full py-1 px-2.5 rounded text-right font-medium text-slate-800 hover:text-emerald-700 hover:bg-emerald-50/80 transition focus:outline-none cursor-pointer flex items-center justify-end gap-1.5"
                        onclick="openHygieneDrilldown('${metric}', '${row.prov_num}', '${row.clinic_num}')"
                        title="Click to view patient breakdown">
                        <span>${data}</span>
                        <svg class="w-3 h-3 text-slate-400 opacity-60 group-hover:opacity-100 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l9.2-9.2M17 17V7H7" />
                        </svg>
                    </button>
                `;
            }

            let table = DDS.dataTable(document.getElementById('hygieneRecallTable'), {
                processing: true,
                serverSide: true,
                searching: true,
                ordering: true,
                ajax: {
                    url: "{{ route('hygiene-recall.data') }}",
                    type: 'GET',
                    data: function (d) {
                        Object.assign(d, getFilters());
                    },
                    dataSrc: function (json) {
                        updateFooter(json.total, json.average);
                        return json.data || [];
                    }
                },
                columns: [
                    {
                        data: 'provider_name',
                        render: function (data, type, row) {
                            return `
                                <div class="flex items-center justify-between gap-2 font-bold text-gray-900">
                                    <span class="truncate">${data}</span>
                                    ${row.prov_num ? `
                                        <button type="button" class="text-emerald-600 hover:text-emerald-800 transition focus:outline-none shrink-0"
                                            onclick="if(typeof openProviderModal === 'function') openProviderModal('${row.prov_num}');"
                                            title="View Provider Details">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                                <polyline points="15 3 21 3 21 9"></polyline>
                                                <line x1="10" y1="14" x2="21" y2="3"></line>
                                            </svg>
                                        </button>
                                    ` : ''}
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'provider_id',
                        render: (data) => `<span class="font-medium text-slate-700">${data}</span>`
                    },
                    {
                        data: 'office',
                        render: (data) => `<span class="text-slate-700">${data}</span>`
                    },
                    {
                        data: 'missed_recall',
                        render: (data, type, row) => renderMetricBtn(data, 'missed_recall', row, row.raw?.missed_recall)
                    },
                    {
                        data: 'patient_recalled',
                        render: (data, type, row) => renderMetricBtn(data, 'patient_recalled', row, row.raw?.patient_recalled)
                    },
                    {
                        data: 'future_appointments',
                        render: (data, type, row) => renderMetricBtn(data, 'future_appointments', row, row.raw?.future_appointments)
                    },
                    {
                        data: 'patients_recalled_dollars',
                        render: (data, type, row) => renderMetricBtn(data, 'patients_recalled_dollars', row, row.raw?.patients_recalled_dollars)
                    },
                    {
                        data: 'patient_recall_rate',
                        render: (data, type, row) => `<div data-order="${row.raw?.patient_recall_rate || 0}" class="text-right font-bold text-slate-800 pr-2">${data}</div>`
                    }
                ],
                dom: 'rt<"flex justify-between items-center px-5 py-4 border-t border-slate-100 bg-white"ip>',
                pagingType: 'simple_numbers',
                pageLength: 25,
                language: { paginate: { previous: "Prev", next: "Next" }, emptyTable: "No hygiene recall data found for the selected filters." },
                createdRow: function (row, data, dataIndex) {
                    $(row).addClass('hover:bg-slate-50 transition border-b border-slate-100 last:border-0');
                    $('td:eq(0)', row).addClass('dt-col-sticky bg-white font-medium text-slate-800 border-r border-slate-100 p-3.5 text-[13px]').css('left', '0');
                    $('td:eq(1)', row).addClass('dt-col-sticky bg-slate-50/60 font-bold text-slate-800 border-r border-slate-100 p-3.5 text-[13px]').css('left', '13rem');
                    $('td', row).not(':lt(2)').addClass('p-3.5 text-[13px] border-l border-slate-100');
                }
            });

            $('#tableSearch').on('keyup', function () {
                table.search(this.value).draw();
            });

            $('#hygieneLocation').on('change', function () {
                table.ajax.reload();
            });

            $('#refreshBtn').on('click', function () {
                table.ajax.reload();
            });

            if (window.DDS && typeof DDS.onDateRange === 'function') {
                DDS.onDateRange('hygieneDateRange', function () {
                    table.ajax.reload();
                });
            }

            $('#hygieneDateRange').on('apply.daterangepicker', function () {
                table.ajax.reload();
            });

            // CSV Export Handler
            $('#exportCsvBtn').on('click', function () {
                const filters = getFilters();
                const url = baseUrl + '/hygiene-recall/export?' + $.param(filters);
                window.location.href = url;
            });
        });
    </script>

    <x-app-components.patient-modal />
</x-app-layout>