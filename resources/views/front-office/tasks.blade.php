<x-app-layout>
    <header
        class="bg-white border-b border-gray-200 sticky top-0 z-50 px-6 py-4 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-6">
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Front Office</h1>
            <div class="flex items-center gap-3">
                <input type="month" id="frontOfficeMonth" value="{{ date('Y-m') }}"
                    class="appearance-none bg-gray-100 border border-gray-300 rounded-lg px-3 py-1.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 text-gray-700">

                <div class="relative">
                    <select
                        class="appearance-none bg-gray-100 border border-gray-300 rounded-lg pl-3 pr-8 py-1.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 text-gray-700">
                        <option>8 Mile</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
                <button id="updateStatsBtn"
                    class="bg-white hover:bg-gray-50 text-emerald-600 border border-emerald-500 font-medium text-sm px-4 py-1.5 rounded-lg transition-colors">
                    Update
                </button>
            </div>
        </div>

        <button
            class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm px-4 py-2 rounded-lg flex items-center gap-2 shadow-sm transition-colors">
            <i class="fa-solid fa-book-open"></i> Quick Start Guide
        </button>
    </header>

    <nav class="bg-white border-b border-gray-200 px-6 flex gap-6 text-sm font-medium text-gray-500">
        <a href="{{ route('front-office.index') }}"
            class="border-b-2 border-transparent hover:text-gray-700 py-3.5 px-1">Schedule</a>
        <a href="{{ route('front-office.tasks') }}"
            class="border-b-2 border-emerald-500 text-emerald-600 py-3.5 px-1">Tasks</a>
        <button class="border-b-2 border-transparent hover:text-gray-700 py-3.5 px-1">Collections</button>
        <button class="border-b-2 border-transparent hover:text-gray-700 py-3.5 px-1">KPIs</button>
        <button class="border-b-2 border-transparent hover:text-gray-700 py-3.5 px-1">Performance</button>
    </nav>

    <main class="p-6 space-y-4 max-w-[1600px] mx-auto bg-gray-50 min-h-screen">

        <div class="flex justify-end mb-2">
            <div class="flex rounded-md shadow-sm bg-white" role="group">
                <button type="button"
                    class="px-4 py-1.5 text-xs font-medium text-gray-500 bg-white border-y border-l border-gray-200 rounded-l-md hover:bg-gray-50">Today</button>
                <button type="button"
                    class="px-4 py-1.5 text-xs font-medium text-gray-500 bg-white border border-gray-200 hover:bg-gray-50">2
                    Days</button>
                <button type="button"
                    class="px-4 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 shadow-[inset_0_0_0_1px_rgba(16,185,129,0.2)]">7
                    Days</button>
                <button type="button"
                    class="px-4 py-1.5 text-xs font-medium text-gray-500 bg-white border-y border-r border-gray-200 rounded-r-md hover:bg-gray-50">30
                    Days</button>
            </div>
        </div>

        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Main Chart Area -->
            <div
                class="col-span-2 bg-white rounded-md border border-gray-200 shadow-sm flex flex-col overflow-hidden relative min-h-[300px] p-5">
                <div class="h-1 w-full bg-[#5ce6a1] absolute top-0 left-0"></div>

                <!-- Chart Legend Absolute (Matching screenshot overlay style vaguely) -->
                <!-- We will rely on ChartJS legend or custom HTML below chart -->

                <div class="flex-1 flex justify-center w-full relative">
                    <canvas id="tasksChart"></canvas>
                </div>

                <div class="flex items-center justify-center gap-4 text-[10px] font-bold text-gray-700 mt-2">
                    <div class="flex items-center gap-1.5"><span class="w-3 h-3 bg-[#5ce6a1]"></span> Unconfirmed Apts
                    </div>
                    <div class="flex items-center gap-1.5"><span class="w-3 h-3 bg-[#a85cf0]"></span> Unverified Ins
                    </div>
                    <div class="flex items-center gap-1.5"><span class="w-3 h-3 bg-[#42cbf5]"></span> Missing Data or
                        Balance</div>
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
                        <div class="text-3xl font-bold text-gray-900 mt-1 mb-2">38</div>
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
                        <div class="text-3xl font-bold text-gray-900 mt-1 mb-2">13</div>
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
                        <div class="text-3xl font-bold text-gray-900 mt-1 mb-2">53</div>
                    </div>
                    <div class="flex justify-between items-end border-t border-gray-100 pt-3 mt-1">
                        <p class="text-[10px] text-gray-400 w-3/4 leading-tight">Patient missing key data or has Balance
                            on their account</p>
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
                        <div class="text-3xl font-bold text-gray-900 mt-1 mb-2">0</div>
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
                        class="task-tab border-t-2 border-transparent bg-white text-gray-900 px-6 py-4 shadow-[inset_0_2px_0_rgba(16,185,129,1)] hover:bg-gray-50 border-r border-gray-200">Unconfirmed
                        Appointments</button>
                    <button data-filter="no_insurance"
                        class="task-tab border-t-2 border-transparent text-gray-500 hover:text-gray-700 px-6 py-4 hover:bg-gray-50">New
                        Patients No Insurance</button>
                    <button data-filter="missing_data"
                        class="task-tab border-t-2 border-transparent text-gray-500 hover:text-gray-700 px-6 py-4 hover:bg-gray-50">Missing
                        Data Or Balance</button>
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
                    <input type="text" placeholder="Search"
                        class="bg-white border border-gray-300 text-gray-900 text-xs rounded pl-2 pr-7 py-1.5 focus:ring-emerald-500 focus:border-emerald-500 w-48 shadow-sm">
                    <i class="fa-solid fa-magnifying-glass absolute right-2.5 top-2 text-gray-400 text-[10px]"></i>
                </div>

                <button
                    class="text-[11px] font-bold text-emerald-700 bg-white border border-emerald-500 rounded px-3 py-1.5 hover:bg-emerald-50 tracking-wide shadow-sm">
                    Export CSV
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

            // 1. Line Chart Initialization
            const ctx = document.getElementById('tasksChart').getContext('2d');

            // Note: Colors based on mock: Unconfirmed (Teal), Unverified Ins (Purple), Missing Data (Blue), Reminders (Yellow)
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jul 07', 'Jul 08', 'Jul 09', 'Jul 10', 'Jul 11', 'Jul 12', 'Jul 13', 'Jul 14'],
                    datasets: [
                        {
                            label: 'Unconfirmed Apts',
                            data: [0, 0, 0, 0, 0, 1, 3, 5],
                            borderColor: '#5ce6a1',
                            backgroundColor: 'rgba(92, 230, 161, 0.4)',
                            fill: true,
                            tension: 0.1,
                            pointRadius: 2,
                        },
                        {
                            label: 'Unverified Ins',
                            data: [0, 0, 0, 0, 0, 0, 2, 12],
                            borderColor: '#a85cf0',
                            backgroundColor: 'rgba(168, 92, 240, 0.2)',
                            fill: true,
                            tension: 0.1,
                            pointRadius: 2,
                        },
                        {
                            label: 'Missing Data',
                            data: [0, 0, 0, 0, 0, 0, 0, 53],
                            borderColor: '#42cbf5',
                            backgroundColor: 'rgba(66, 203, 245, 0.8)', // Primary solid spike in screenshot
                            fill: true,
                            tension: 0.1,
                            pointRadius: 2,
                        },
                        {
                            label: 'Reminders',
                            data: [0, 0, 0, 0, 0, 0, 0, 0],
                            borderColor: '#ffd166',
                            backgroundColor: 'rgba(255, 209, 102, 0.4)',
                            fill: true,
                            tension: 0.1,
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
                            max: 60,
                            ticks: { font: { size: 9 }, stepSize: 20 },
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

            // 2. DataTables Configuration
            let currentFilter = 'unconfirmed';

            // Pre-XHR event handler to render skeleton screens inside tbody
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
                processing: false, // Disabled default processing popup to let skeleton loader present cleanly
                serverSide: true,
                pageLength: 20,
                layout: { topStart: null, topEnd: null, bottomStart: ['pageLength', 'info'], bottomEnd: 'paging' },
                language: {
                    lengthMenu: 'Items per page _MENU_ <span class="text-gray-300 mx-2">|</span>',
                    info: '_START_-_END_ of _TOTAL_ items',
                    paginate: {
                        previous: '<i class="fa-solid fa-chevron-left"></i>',
                        next: '<i class="fa-solid fa-chevron-right"></i>'
                    }
                },
                ajax: {
                    url: "{{ route('front-office.tasks-data') }}",
                    data: function (d) {
                        d.month = $('#frontOfficeMonth').val();
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

            // Tab switching logic
            $('.task-tab').click(function () {
                $('.task-tab').removeClass('bg-white text-gray-900 shadow-[inset_0_2px_0_rgba(16,185,129,1)] border-r border-gray-200')
                    .addClass('text-gray-500 hover:text-gray-700 hover:bg-gray-50');

                $(this).removeClass('text-gray-500 hover:text-gray-700 hover:bg-gray-50')
                    .addClass('bg-white text-gray-900 shadow-[inset_0_2px_0_rgba(16,185,129,1)] border-r border-gray-200');

                currentFilter = $(this).data('filter');
                tasksTable.ajax.reload();
            });
        });
    </script>

</x-app-layout>