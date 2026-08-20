<main class="p-6 space-y-4 max-w-[1600px] mx-auto bg-gray-50 min-h-screen">

    <div class="flex justify-end mb-2">
        <div class="flex rounded-md shadow-sm bg-white" role="group" id="taskRangeGroup">
            <button type="button" data-days="0"
                class="task-range-btn px-4 py-1.5 text-xs font-medium text-gray-500 bg-white border-y border-l border-gray-200 rounded-l-md hover:bg-gray-50">Today</button>
            <button type="button" data-days="2"
                class="task-range-btn px-4 py-1.5 text-xs font-medium text-gray-500 bg-white border border-gray-200 hover:bg-gray-50">2 Days</button>
            <button type="button" data-days="7"
                class="task-range-btn px-4 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 shadow-[inset_0_0_0_1px_rgba(16,185,129,0.2)]">7 Days</button>
            <button type="button" data-days="30"
                class="task-range-btn px-4 py-1.5 text-xs font-medium text-gray-500 bg-white border-y border-r border-gray-200 rounded-r-md hover:bg-gray-50">30 Days</button>
        </div>
    </div>

    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Main Chart Area -->
        <div
            class="col-span-2 bg-white rounded-md border border-gray-200 shadow-sm flex flex-col overflow-hidden relative min-h-[300px] p-5">
            <div class="h-1 w-full bg-[#5ce6a1] absolute top-0 left-0"></div>

            <div class="flex-1 flex justify-center w-full relative">
                <canvas id="tasksChart"></canvas>
            </div>

            <div class="flex items-center justify-center gap-4 text-[10px] font-bold text-gray-700 mt-2">
                <div class="flex items-center gap-1.5"><span class="w-3 h-3 bg-[#5ce6a1]"></span> Unconfirmed Apts</div>
                <div class="flex items-center gap-1.5"><span class="w-3 h-3 bg-[#a85cf0]"></span> Unverified Ins</div>
                <div class="flex items-center gap-1.5"><span class="w-3 h-3 bg-[#42cbf5]"></span> Missing Data or Balance</div>
                <div class="flex items-center gap-1.5"><span class="w-3 h-3 bg-[#ffd166]"></span> Reminders</div>
            </div>
        </div>

        <!-- Summary KPI Grid -->
        <div class="col-span-1 grid grid-cols-2 gap-4">

            <!-- Card 1 -->
            <div
                class="bg-white rounded-md border border-gray-200 shadow-sm p-4 flex flex-col justify-between relative overflow-hidden">
                <div class="h-1 w-full bg-[#5ce6a1] absolute top-0 left-0"></div>
                <div>
                    <div class="flex items-start justify-between mb-2">
                        <h4 class="text-xs font-bold text-gray-900 leading-tight">Unconfirmed Appointments</h4>
                        <button class="text-gray-400 hover:text-gray-600"><i
                                class="fa-regular fa-circle-info text-[10px]"></i></button>
                    </div>
                    <div id="taskUnconfirmedCount" class="text-3xl font-bold text-gray-900 mt-1 mb-2">0</div>
                </div>
                <div class="flex justify-between items-end border-t border-gray-100 pt-3 mt-1">
                    <p class="text-[10px] text-gray-400 w-3/4 leading-tight">Patient with unconfirmed apts</p>
                    <i class="fa-regular fa-arrow-up-right-from-square text-gray-300 text-xs"></i>
                </div>
            </div>

            <!-- Card 2 -->
            <div
                class="bg-white rounded-md border border-gray-200 shadow-sm p-4 flex flex-col justify-between relative overflow-hidden">
                <div class="h-1 w-full bg-[#5ce6a1] absolute top-0 left-0"></div>
                <div>
                    <div class="flex items-start justify-between mb-2">
                        <h4 class="text-xs font-bold text-gray-900 leading-tight">New Patients No Insurance</h4>
                        <button class="text-gray-400 hover:text-gray-600"><i
                                class="fa-regular fa-circle-info text-[10px]"></i></button>
                    </div>
                    <div id="taskNoInsuranceCount" class="text-3xl font-bold text-gray-900 mt-1 mb-2">0</div>
                </div>
                <div class="flex justify-between items-end border-t border-gray-100 pt-3 mt-1">
                    <p class="text-[10px] text-gray-400 w-3/4 leading-tight">Patients with unverified insurance</p>
                    <i class="fa-regular fa-arrow-up-right-from-square text-gray-300 text-xs"></i>
                </div>
            </div>

            <!-- Card 3 -->
            <div
                class="bg-white rounded-md border border-gray-200 shadow-sm p-4 flex flex-col justify-between relative overflow-hidden">
                <div class="h-1 w-full bg-[#5ce6a1] absolute top-0 left-0"></div>
                <div>
                    <div class="flex items-start justify-between mb-2">
                        <h4 class="text-xs font-bold text-gray-900 leading-tight">Missing Data or Balance</h4>
                        <button class="text-gray-400 hover:text-gray-600"><i
                                class="fa-regular fa-circle-info text-[10px]"></i></button>
                    </div>
                    <div id="taskMissingDataCount" class="text-3xl font-bold text-gray-900 mt-1 mb-2">0</div>
                </div>
                <div class="flex justify-between items-end border-t border-gray-100 pt-3 mt-1">
                    <p class="text-[10px] text-gray-400 w-3/4 leading-tight">Patient missing key data or has Balance on their account</p>
                    <i class="fa-regular fa-arrow-up-right-from-square text-gray-300 text-xs"></i>
                </div>
            </div>

            <!-- Card 4 -->
            <div
                class="bg-white rounded-md border border-gray-200 shadow-sm p-4 flex flex-col justify-between relative overflow-hidden">
                <div class="h-1 w-full bg-[#5ce6a1] absolute top-0 left-0"></div>
                <div>
                    <div class="flex items-start justify-between mb-2">
                        <h4 class="text-xs font-bold text-gray-900 leading-tight">Reminders</h4>
                        <button class="text-gray-400 hover:text-gray-600"><i
                                class="fa-regular fa-circle-info text-[10px]"></i></button>
                    </div>
                    <div id="taskRemindersCount" class="text-3xl font-bold text-gray-900 mt-1 mb-2">0</div>
                </div>
                <div class="flex justify-between items-end border-t border-gray-100 pt-3 mt-1">
                    <p class="text-[10px] text-gray-400 w-3/4 leading-tight">Patient Reminders</p>
                    <i class="fa-regular fa-arrow-up-right-from-square text-gray-300 text-xs"></i>
                </div>
            </div>

        </div>

    </section>

    <!-- Nested Data Table Section -->
    <section class="bg-white rounded-md border border-gray-200 shadow-sm overflow-hidden mt-6">
        <div class="border-b border-gray-100 bg-gray-50/50">
            <nav class="flex text-xs font-semibold" id="taskTabs">
                <button data-filter="unconfirmed"
                    class="task-tab border-t-2 border-transparent bg-white text-gray-900 px-6 py-4 shadow-[inset_0_2px_0_rgba(16,185,129,1)] hover:bg-gray-50 border-r border-gray-200">Unconfirmed Appointments</button>
                <button data-filter="no_insurance"
                    class="task-tab border-t-2 border-transparent text-gray-500 hover:text-gray-700 px-6 py-4 hover:bg-gray-50">New Patients No Insurance</button>
                <button data-filter="missing_data"
                    class="task-tab border-t-2 border-transparent text-gray-500 hover:text-gray-700 px-6 py-4 hover:bg-gray-50">Missing Data Or Balance</button>
                <button data-filter="reminders"
                    class="task-tab border-t-2 border-transparent text-gray-500 hover:text-gray-700 px-6 py-4 hover:bg-gray-50">Reminders</button>
            </nav>
        </div>

        <div class="p-3 border-b border-gray-200 flex justify-end gap-3 items-center">
            <div class="flex items-center gap-2 mr-2">
                <button class="text-gray-400 hover:text-gray-600"><i
                        class="fa-regular fa-circle-info text-[10px]"></i></button>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer">
                    <div
                        class="w-8 h-4 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-emerald-500">
                    </div>
                    <span class="ml-2 text-[11px] font-bold text-gray-700">Completed</span>
                </label>
            </div>

            <div class="relative">
                <input id="tasksSearchInput" type="text" placeholder="Search"
                    class="bg-white border border-gray-300 text-gray-900 text-xs rounded pl-2 pr-7 py-1.5 focus:ring-emerald-500 focus:border-emerald-500 w-48 shadow-sm">
                <i class="fa-solid fa-magnifying-glass absolute right-2.5 top-2 text-gray-400 text-[10px]"></i>
            </div>

            <button id="exportTasksCsvBtn" type="button"
                class="text-[11px] font-bold text-emerald-700 bg-white border border-emerald-500 rounded px-3 py-1.5 hover:bg-emerald-50 tracking-wide shadow-sm flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-download"></i> Export CSV
            </button>
        </div>

        <div class="overflow-x-auto p-4 pt-1 custom-table-scrollbar bg-white">
            <table id="tasksTable" class="dds-table w-full text-left border-collapse table-auto min-w-[1400px]">
                <thead>
                    <tr class="bg-white border-b border-gray-200">
                        <th aria-sort="" class="text-[11px] font-bold py-3 pl-4 pr-3 text-gray-700"
                            style="min-width: 12rem;">
                            <span class="flex items-center">
                                <label class="inline-flex items-center mr-2 text-xs font-semibold cursor-pointer">
                                    <input type="checkbox" aria-checked="false"
                                        class="w-4 h-4 mr-2 bg-white border border-gray-300 rounded-sm appearance-none cursor-pointer focus:outline-none focus:ring-1 focus:ring-emerald-500 checked:bg-emerald-500">
                                </label>
                                <span class="flex justify-between items-center w-full">
                                    <span>Patient</span>
                                    <i class="fa-regular fa-circle-info text-gray-300"></i>
                                </span>
                            </span>
                        </th>
                        <th aria-sort=""
                            class="text-[11px] font-bold py-3 px-3 border-l border-b border-gray-200 text-gray-700"
                            style="min-width: 4rem;">
                            <span class="flex justify-between items-center"><span>Age</span> <i
                                    class="fa-regular fa-circle-info text-gray-300"></i></span>
                        </th>
                        <th aria-sort=""
                            class="text-[11px] font-bold py-3 px-3 border-l border-b border-gray-200 text-gray-700"
                            style="min-width: 7rem;">
                            <span class="flex justify-between items-center"><span>Phone</span> <i
                                    class="fa-regular fa-circle-info text-gray-300"></i></span>
                        </th>
                        <th aria-sort=""
                            class="text-[11px] font-bold py-3 px-3 border-l border-b border-gray-200 text-gray-700"
                            style="min-width: 7rem;">
                            <span class="flex justify-between items-center"><span>Work Phone</span> <i
                                    class="fa-regular fa-circle-info text-gray-300"></i></span>
                        </th>
                        <th aria-sort=""
                            class="text-[11px] font-bold py-3 px-3 border-l border-b border-gray-200 text-gray-700"
                            style="min-width: 7rem;">
                            <span class="flex justify-between items-center"><span>Mobile Phone</span> <i
                                    class="fa-regular fa-circle-info text-gray-300"></i></span>
                        </th>
                        <th aria-sort=""
                            class="text-[11px] font-bold py-3 px-3 border-l border-b border-gray-200 text-gray-700"
                            style="min-width: 10rem;">
                            <span class="flex justify-between items-center"><span>Email</span> <i
                                    class="fa-regular fa-circle-info text-gray-300"></i></span>
                        </th>
                        <th aria-sort=""
                            class="text-[11px] font-bold py-3 px-3 border-l border-b border-gray-200 text-gray-700"
                            style="min-width: 6rem;">
                            <span class="flex justify-between items-center"><span>Appt Date</span> <i
                                    class="fa-regular fa-circle-info text-gray-300"></i></span>
                        </th>
                        <th aria-sort=""
                            class="text-[11px] font-bold py-3 px-3 border-l border-b border-gray-200 text-gray-700"
                            style="min-width: 5rem;">
                            <span class="flex justify-between items-center"><span>Appt Time</span> <i
                                    class="fa-regular fa-circle-info text-gray-300"></i></span>
                        </th>
                        <th aria-sort=""
                            class="text-[11px] font-bold py-3 px-3 border-l border-b border-gray-200 text-gray-700"
                            style="min-width: 12rem;">
                            <span class="flex justify-between items-center"><span>Appt Description</span> <i
                                    class="fa-regular fa-circle-info text-gray-300"></i></span>
                        </th>
                        <th aria-sort=""
                            class="text-[11px] font-bold py-3 px-3 border-l border-b border-gray-200 text-gray-700"
                            style="min-width: 8rem;">
                            <span class="flex justify-between items-center"><span>Provider</span> <i
                                    class="fa-regular fa-circle-info text-gray-300"></i></span>
                        </th>
                        <th aria-sort=""
                            class="text-[11px] font-bold py-3 px-3 border-l border-b border-gray-200 text-gray-700"
                            style="min-width: 5rem;">
                            <span class="flex justify-between items-center"><span>Action</span> <i
                                    class="fa-regular fa-circle-info text-gray-300"></i></span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-[11px] font-medium text-gray-700">
                </tbody>
            </table>
        </div>

    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function () {
        let tasksChartInstance = null;
        let currentFilter = 'unconfirmed';
        let customDateParams = null;

        // 1. Line Chart Initialization
        const ctx = document.getElementById('tasksChart').getContext('2d');
        tasksChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Unconfirmed Apts',
                        data: [],
                        borderColor: '#5ce6a1',
                        backgroundColor: 'rgba(92, 230, 161, 0.4)',
                        fill: true,
                        tension: 0.2,
                        pointRadius: 2,
                    },
                    {
                        label: 'Unverified Ins',
                        data: [],
                        borderColor: '#a85cf0',
                        backgroundColor: 'rgba(168, 92, 240, 0.2)',
                        fill: true,
                        tension: 0.2,
                        pointRadius: 2,
                    },
                    {
                        label: 'Missing Data',
                        data: [],
                        borderColor: '#42cbf5',
                        backgroundColor: 'rgba(66, 203, 245, 0.8)',
                        fill: true,
                        tension: 0.2,
                        pointRadius: 2,
                    },
                    {
                        label: 'Reminders',
                        data: [],
                        borderColor: '#ffd166',
                        backgroundColor: 'rgba(255, 209, 102, 0.4)',
                        fill: true,
                        tension: 0.2,
                        pointRadius: 0,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { font: { size: 9 }, stepSize: 10 },
                        border: { display: false },
                        grid: { color: '#f3f4f6' }
                    },
                    x: {
                        ticks: { font: { size: 9 } },
                        grid: { display: false }
                    }
                },
                interaction: { mode: 'index', intersect: false }
            }
        });

        // 2. Hydrate Stats & Chart Data by Date
        function hydrateTasksStats() {
            let params = {};
            if (customDateParams) {
                params = customDateParams;
            } else if (window.getFoDateParams) {
                params = window.getFoDateParams();
            } else {
                params = { month: $('#frontOfficeMonth').val() || '' };
            }

            $.get("{{ route('front-office.tasks-stats') }}", params, function (res) {
                if (res && res.summary) {
                    $('#taskUnconfirmedCount').text(res.summary.unconfirmed || 0);
                    $('#taskNoInsuranceCount').text(res.summary.no_insurance || 0);
                    $('#taskMissingDataCount').text(res.summary.missing_data || 0);
                    $('#taskRemindersCount').text(res.summary.reminders || 0);
                }

                if (res && res.chart && tasksChartInstance) {
                    tasksChartInstance.data.labels = res.chart.labels || [];
                    tasksChartInstance.data.datasets[0].data = res.chart.unconfirmed || [];
                    tasksChartInstance.data.datasets[1].data = res.chart.no_insurance || [];
                    tasksChartInstance.data.datasets[2].data = res.chart.missing_data || [];
                    tasksChartInstance.data.datasets[3].data = res.chart.reminders || [];
                    tasksChartInstance.update();
                }
            });
        }

        // 3. Preset Date Range Buttons (Today, 2 Days, 7 Days, 30 Days)
        $('.task-range-btn').on('click', function () {
            $('.task-range-btn')
                .removeClass('text-emerald-700 bg-emerald-50 border-emerald-200 shadow-[inset_0_0_0_1px_rgba(16,185,129,0.2)]')
                .addClass('text-gray-500 bg-white border-gray-200 hover:bg-gray-50');

            $(this)
                .removeClass('text-gray-500 bg-white border-gray-200 hover:bg-gray-50')
                .addClass('text-emerald-700 bg-emerald-50 border-emerald-200 shadow-[inset_0_0_0_1px_rgba(16,185,129,0.2)]');

            let days = parseInt($(this).data('days'), 10);
            let today = new Date();
            let start = today.toISOString().slice(0, 10);
            let endDate = new Date(today);
            endDate.setDate(today.getDate() + (days > 0 ? days - 1 : 0));
            let end = endDate.toISOString().slice(0, 10);

            customDateParams = {
                start_date: start,
                end_date: end
            };

            tasksTable.ajax.reload();
            hydrateTasksStats();
        });

        // 4. DataTables Configuration
        $('#tasksTable').on('preXhr.dt', function (e, settings, data) {
            var colsCount = $('#tasksTable thead th').length;
            var skeletonRows = '';
            for (var i = 0; i < 5; i++) {
                skeletonRows += '<tr class="skeleton-row border-b border-gray-100 animate-pulse">';
                for (var j = 0; j < colsCount; j++) {
                    var width = 'w-16';
                    if (j === 0) width = 'w-32';
                    else if (j === 5 || j === 8) width = 'w-24';
                    skeletonRows += '<td class="py-4 px-3 border-l border-gray-100 first:border-l-0"><div class="h-3.5 bg-gray-200 rounded ' + width + '"></div></td>';
                }
                skeletonRows += '</tr>';
            }
            $('#tasksTable tbody').html(skeletonRows);
        });

        let tasksTable = DDS.dataTable(document.getElementById('tasksTable'), {
            processing: false,
            serverSide: true,
            pageLength: 20,
            lengthChange: true,
            lengthMenu: [10, 20, 50, 100],
            layout: { topStart: null, topEnd: null, bottomStart: ['pageLength', 'info'], bottomEnd: 'paging' },
            language: {
                info: '_START_-_END_ of _TOTAL_ items',
                paginate: {
                    previous: '<i class="fa-solid fa-chevron-left text-[10px]"></i>',
                    next: '<i class="fa-solid fa-chevron-right text-[10px]"></i>'
                }
            },
            drawCallback: function () {
                $('.dt-length').addClass('text-xs font-semibold text-gray-500 flex items-center gap-1.5');
                $('.dt-length select').addClass('border border-gray-300 rounded text-gray-700 py-1 px-2 text-xs focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none bg-white font-medium cursor-pointer');
                $('.dt-info').addClass('text-xs font-semibold text-gray-500 flex items-center');
                $('.dt-paging nav').addClass('flex items-center gap-1');
                $('.dt-paging').addClass('flex items-center');
                $('.dt-paging-button').addClass('px-2.5 py-1 text-xs font-bold border border-gray-200 text-gray-600 bg-white hover:bg-gray-50 rounded cursor-pointer transition-colors shadow-sm select-none');
                $('.dt-paging-button.current').removeClass('bg-white text-gray-600 hover:bg-gray-50 border-gray-200').addClass('bg-emerald-50 text-emerald-700 border-emerald-300 shadow-[inset_0_0_0_1px_rgba(16,185,129,0.2)]');
                $('.dt-paging-button.disabled').addClass('opacity-40 cursor-not-allowed hover:bg-white').removeClass('hover:bg-gray-50 cursor-pointer');
            },
            ajax: {
                url: "{{ route('front-office.tasks-data') }}",
                data: function (d) {
                    if (customDateParams) {
                        $.extend(d, customDateParams);
                    } else if (window.getFoDateParams) {
                        $.extend(d, window.getFoDateParams());
                    } else {
                        d.month = $('#frontOfficeMonth').val();
                    }
                    d.filter = currentFilter;
                }
            },
            columns: [
                { data: 'patient_name', name: 'patient_name', className: 'font-semibold' },
                { data: 'age', name: 'age' },
                { data: 'phone', name: 'phone' },
                { data: 'work_phone', name: 'work_phone' },
                { data: 'mobile_phone', name: 'mobile_phone' },
                { data: 'email', name: 'email', className: 'truncate max-w-[120px]' },
                { data: 'appt_date', name: 'appt_date' },
                { data: 'appt_time', name: 'appt_time' },
                { data: 'description', name: 'description', className: 'truncate max-w-[150px] text-gray-500' },
                { data: 'provider', name: 'provider' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        // Search Input filter
        $('#tasksSearchInput').on('keyup', function () {
            tasksTable.search(this.value).draw();
        });

        // Tab switching logic
        $('.task-tab').click(function () {
            $('.task-tab').removeClass('bg-white text-gray-900 shadow-[inset_0_2px_0_rgba(16,185,129,1)] border-r border-gray-200')
                .addClass('text-gray-500 hover:text-gray-700 hover:bg-gray-50');

            $(this).removeClass('text-gray-500 hover:text-gray-700 hover:bg-gray-50')
                .addClass('bg-white text-gray-900 shadow-[inset_0_2px_0_rgba(16,185,129,1)] border-r border-gray-200');

            currentFilter = $(this).data('filter');
            tasksTable.ajax.reload();
        });

        // Listen for parent Front Office date change
        $('#frontOfficeMonth').off('change.tasks').on('change.tasks', function () {
            customDateParams = null;
            tasksTable.ajax.reload();
            hydrateTasksStats();
        });

        window.reloadFoTables = function () {
            if (tasksTable) {
                tasksTable.ajax.reload();
            }
            hydrateTasksStats();
        };

        $('#exportTasksCsvBtn').on('click', function () {
            exportTableToCSV($('#tasksTable'), (currentFilter || 'tasks') + '_export');
        });

        // Initial Load
        hydrateTasksStats();
    });
</script>