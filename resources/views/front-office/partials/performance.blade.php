<main class="max-w-[1600px] mx-auto bg-gray-50 min-h-screen p-6 pb-12 space-y-10">

    <!-- Top Overview Cards -->
    <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Daily Activity -->
        <div>
            <h3 class="font-bold text-gray-900 mb-3 text-[13px]">Daily Activity</h3>
            <div class="bg-white rounded shadow-sm border border-gray-200 p-8 min-h-[100px] flex items-center">
                <span class="text-xs text-gray-600">No data found</span>
            </div>
        </div>

        <!-- Reminder Status Overview -->
        <div>
            <h3 class="font-bold text-gray-900 mb-3 text-[13px]">Reminder Status Overview</h3>
            <div class="bg-white rounded shadow-sm border border-gray-200 p-8 min-h-[100px] flex items-center">
                <span class="text-xs text-gray-600">No data found</span>
            </div>
        </div>
    </section>

    <!-- Reminders Table -->
    <section class="space-y-3">
        <div>
            <h2 class="font-bold text-gray-900 text-sm mb-1">Reminders</h2>
            <p class="text-gray-500 text-xs">This table is used for tracking Reminders activity.</p>
        </div>

        <div class="bg-white border border-gray-200 rounded shadow-sm">
            <!-- Toolbar -->
            <div class="p-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
                <div class="flex rounded shadow-sm text-[11px] font-bold">
                    <button class="px-4 py-1.5 bg-green-100 text-green-800 border border-green-200 rounded-l">Top
                        20%</button>
                    <button class="px-4 py-1.5 bg-gray-100 text-gray-600 border-y border-gray-200 hover:bg-gray-200">Mid
                        Tier</button>
                    <button class="px-4 py-1.5 bg-red-100 text-red-800 border border-red-200 rounded-r">Bottom
                        20%</button>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative">
                        <input type="text" placeholder="Search" id="remSearch"
                            class="bg-white border border-gray-300 text-gray-900 text-xs rounded pl-2 pr-7 py-1.5 focus:ring-emerald-500 focus:border-emerald-500 w-48 shadow-sm">
                        <i class="fa-solid fa-magnifying-glass absolute right-2.5 top-2 text-gray-400 text-[10px]"></i>
                    </div>
                    <button id="exportRemindersCsvBtn" type="button"
                        class="text-[11px] font-bold text-gray-800 bg-white border border-emerald-500 rounded px-3 py-1.5 hover:bg-emerald-50 shadow-sm uppercase flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-download"></i> Export CSV
                    </button>
                </div>
            </div>

            <!-- Table Content -->
            <div class="overflow-x-auto custom-table-scrollbar p-0">
                <table id="remindersTable" class="dds-table w-full text-left border-collapse table-auto">
                    <thead>
                        <tr class="bg-white border-y border-gray-200">
                            <th class="text-[11px] font-bold py-3 px-4 border-r border-gray-200 text-gray-800"><i
                                    class="fa-solid fa-arrows-up-down text-[9px] text-gray-400 mr-1"></i> Name</th>
                            <th class="text-[11px] font-bold py-3 px-3 border-r border-gray-200 text-gray-800"><i
                                    class="fa-solid fa-arrows-up-down text-[9px] text-gray-400 mr-1"></i> Location</th>
                            <th class="text-[11px] font-bold py-3 px-3 border-r border-gray-200 text-gray-800"><i
                                    class="fa-solid fa-arrows-up-down text-[9px] text-gray-400 mr-1"></i> Assigned</th>
                            <th class="text-[11px] font-bold py-3 px-3 border-r border-gray-200 text-gray-800"><i
                                    class="fa-solid fa-arrows-up-down text-[9px] text-gray-400 mr-1"></i> Attempts</th>
                            <th class="text-[11px] font-bold py-3 px-3 border-r border-gray-200 text-gray-800"><i
                                    class="fa-solid fa-arrows-up-down text-[9px] text-gray-400 mr-1"></i> Scheduled</th>
                            <th class="text-[11px] font-bold py-3 px-3 border-r border-gray-200 text-gray-800"><i
                                    class="fa-solid fa-arrows-up-down text-[9px] text-gray-400 mr-1"></i> Scheduled Est
                                $</th>
                            <th class="text-[11px] font-bold py-3 px-3 border-r border-gray-200 text-gray-800"><i
                                    class="fa-solid fa-arrows-up-down text-[9px] text-gray-400 mr-1"></i> Attempted Rate
                            </th>
                            <th class="text-[11px] font-bold py-3 px-3 text-gray-800"><i
                                    class="fa-solid fa-arrows-up-down text-[9px] text-gray-400 mr-1"></i> Scheduled Rate
                            </th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>

    <!-- Non Reminder Contacts Table -->
    <section class="space-y-3">
        <div>
            <h2 class="font-bold text-gray-900 text-sm mb-1">Non Reminder Contacts</h2>
            <p class="text-gray-500 text-[11px]">This table is used for tracking activity from Front Office Schedule &
                Tasks, before a reminder is created. Once a reminder is created, all activity is tracked in the
                Reminders Performance Table.</p>
        </div>

        <div class="bg-white border border-gray-200 rounded shadow-sm">
            <!-- Toolbar -->
            <div class="p-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
                <div class="flex rounded shadow-sm text-[11px] font-bold">
                    <button class="px-4 py-1.5 bg-green-100 text-green-800 border border-green-200 rounded-l">Top
                        20%</button>
                    <button class="px-4 py-1.5 bg-gray-100 text-gray-600 border-y border-gray-200 hover:bg-gray-200">Mid
                        Tier</button>
                    <button class="px-4 py-1.5 bg-red-100 text-red-800 border border-red-200 rounded-r">Bottom
                        20%</button>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative">
                        <input type="text" placeholder="Search" id="nonRemSearch"
                            class="bg-white border border-gray-300 text-gray-900 text-xs rounded pl-2 pr-7 py-1.5 focus:ring-emerald-500 focus:border-emerald-500 w-48 shadow-sm">
                        <i class="fa-solid fa-magnifying-glass absolute right-2.5 top-2 text-gray-400 text-[10px]"></i>
                    </div>
                    <button id="exportNonRemindersCsvBtn" type="button"
                        class="text-[11px] font-bold text-gray-800 bg-white border border-emerald-500 rounded px-3 py-1.5 hover:bg-emerald-50 shadow-sm uppercase flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-download"></i> Export CSV
                    </button>
                </div>
            </div>

            <!-- Table Content -->
            <div class="overflow-x-auto custom-table-scrollbar p-0">
                <table id="nonRemindersTable" class="dds-table w-full text-left border-collapse table-auto">
                    <thead>
                        <tr class="bg-white border-y border-gray-200">
                            <th class="text-[11px] font-bold py-3 px-4 border-r border-gray-200 text-gray-800"><i
                                    class="fa-solid fa-arrows-up-down text-[9px] text-gray-400 mr-1"></i> Name</th>
                            <th class="text-[11px] font-bold py-3 px-3 border-r border-gray-200 text-gray-800"><i
                                    class="fa-solid fa-arrows-up-down text-[9px] text-gray-400 mr-1"></i> Location</th>
                            <th class="text-[11px] font-bold py-3 px-3 border-r border-gray-200 text-gray-800"><i
                                    class="fa-solid fa-arrows-up-down text-[9px] text-gray-400 mr-1"></i> Attempts</th>
                            <th class="text-[11px] font-bold py-3 px-3 border-r border-gray-200 text-gray-800"><i
                                    class="fa-solid fa-arrows-up-down text-[9px] text-gray-400 mr-1"></i> Scheduled</th>
                            <th class="text-[11px] font-bold py-3 px-3 border-r border-gray-200 text-gray-800"><i
                                    class="fa-solid fa-arrows-up-down text-[9px] text-gray-400 mr-1"></i> Scheduled Est
                                $</th>
                            <th class="text-[11px] font-bold py-3 px-3 text-gray-800"><i
                                    class="fa-solid fa-arrows-up-down text-[9px] text-gray-400 mr-1"></i> Scheduled Rate
                            </th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>


    <!-- Totals Table -->
    <section class="space-y-3">
        <div>
            <h2 class="font-bold text-gray-900 text-sm mb-1">Totals</h2>
            <p class="text-gray-500 text-[11px]">This table is used for tracking overall performance and includes both
                Reminder and Non Reminder activity.</p>
        </div>

        <div class="bg-white border border-gray-200 rounded shadow-sm">
            <!-- Toolbar -->
            <div class="p-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
                <div class="flex rounded shadow-sm text-[11px] font-bold">
                    <button class="px-4 py-1.5 bg-green-100 text-green-800 border border-green-200 rounded-l">Top
                        20%</button>
                    <button class="px-4 py-1.5 bg-gray-100 text-gray-600 border-y border-gray-200 hover:bg-gray-200">Mid
                        Tier</button>
                    <button class="px-4 py-1.5 bg-red-100 text-red-800 border border-red-200 rounded-r">Bottom
                        20%</button>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative">
                        <input type="text" placeholder="Search" id="totSearch"
                            class="bg-white border border-gray-300 text-gray-900 text-xs rounded pl-2 pr-7 py-1.5 focus:ring-emerald-500 focus:border-emerald-500 w-48 shadow-sm">
                        <i class="fa-solid fa-magnifying-glass absolute right-2.5 top-2 text-gray-400 text-[10px]"></i>
                    </div>
                    <button id="exportTotalsCsvBtn" type="button"
                        class="text-[11px] font-bold text-gray-800 bg-white border border-emerald-500 rounded px-3 py-1.5 hover:bg-emerald-50 shadow-sm uppercase flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-download"></i> Export CSV
                    </button>
                </div>
            </div>

            <!-- Table Content -->
            <div class="overflow-x-auto custom-table-scrollbar p-0">
                <table id="totalsTable" class="dds-table w-full text-left border-collapse table-auto">
                    <thead>
                        <tr class="bg-white border-y border-gray-200">
                            <th class="text-[11px] font-bold py-3 px-4 border-r border-gray-200 text-gray-800"><i
                                    class="fa-solid fa-arrows-up-down text-[9px] text-gray-400 mr-1"></i> Name</th>
                            <th class="text-[11px] font-bold py-3 px-3 border-r border-gray-200 text-gray-800"><i
                                    class="fa-solid fa-arrows-up-down text-[9px] text-gray-400 mr-1"></i> Location</th>
                            <th class="text-[11px] font-bold py-3 px-3 border-r border-gray-200 text-gray-800"><i
                                    class="fa-solid fa-arrows-up-down text-[9px] text-gray-400 mr-1"></i> Total Attempts
                            </th>
                            <th class="text-[11px] font-bold py-3 px-3 border-r border-gray-200 text-gray-800"><i
                                    class="fa-solid fa-arrows-up-down text-[9px] text-gray-400 mr-1"></i> Total
                                Scheduled</th>
                            <th class="text-[11px] font-bold py-3 px-3 border-r border-gray-200 text-gray-800"><i
                                    class="fa-solid fa-arrows-up-down text-[9px] text-gray-400 mr-1"></i> Total
                                Scheduled Est $</th>
                            <th class="text-[11px] font-bold py-3 px-3 text-gray-800"><i
                                    class="fa-solid fa-arrows-up-down text-[9px] text-gray-400 mr-1"></i> Total
                                Scheduled Rate</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>

</main>

<script>
    $(document).ready(function () {

        const dtConfig = {
            processing: true,
            serverSide: true,
            pageLength: 10,
            lengthChange: true,
            lengthMenu: [10, 20, 50, 100],
            layout: { topStart: null, topEnd: null, bottomStart: ['pageLength', 'info'], bottomEnd: 'paging' },
            language: {
                emptyTable: '<div class="py-1 text-[11px] text-gray-600 font-medium text-center bg-gray-50 w-full border-y border-gray-100">No data</div>',
                info: '_START_-_END_ of _TOTAL_ items',
                paginate: {
                    previous: '<i class="fa-solid fa-chevron-left text-[10px]"></i>',
                    next: '<i class="fa-solid fa-chevron-right text-[10px]"></i>'
                }
            },
            drawCallback: function () {
                $('.dt-length').addClass('text-[11px] font-semibold text-gray-600 flex items-center gap-1.5 p-3');
                $('.dt-length select').addClass('border border-gray-300 rounded text-gray-700 py-1 px-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white cursor-pointer outline-none text-[11px]');
                $('.dt-info').addClass('text-[11px] font-semibold text-gray-600 flex items-center p-3');
                $('.dt-paging nav').addClass('flex items-center gap-1');
                $('.dt-paging').addClass('flex items-center p-3 border-l border-gray-100 h-full bg-white');
                $('.dt-paging-button').addClass('px-2.5 py-1 text-[11px] font-bold border border-gray-200 text-gray-500 bg-white hover:bg-gray-50 rounded transition-colors shadow-sm cursor-pointer');
                $('.dt-paging-button.current').removeClass('bg-white text-gray-500 hover:bg-gray-50 border-gray-200').addClass('bg-emerald-50 text-emerald-700 border-emerald-300 shadow-[inset_0_0_0_1px_rgba(16,185,129,0.2)]');
                $('.dt-paging-button.disabled').addClass('opacity-40 cursor-not-allowed hover:bg-white').removeClass('hover:bg-gray-50 cursor-pointer');
            }
        };

        const sendDateParams = d => {
            if (window.getFoDateParams) {
                $.extend(d, window.getFoDateParams());
            } else {
                d.month = $('#frontOfficeMonth').val() || '';
            }
        };

        // Init Reminders
        let remTable = DDS.dataTable(document.getElementById('remindersTable'), {
            ...dtConfig,
            ajax: {
                url: "{{ route('front-office.performance-reminders-data') }}",
                data: sendDateParams
            },
            columns: [
                { data: 'name', name: 'name', className: 'text-[11px] text-gray-800' },
                { data: 'location', name: 'location', className: 'text-[11px] text-gray-800 border-l border-gray-100' },
                { data: 'assigned', name: 'assigned', className: 'text-[11px] text-gray-800 border-l border-gray-100' },
                { data: 'attempts', name: 'attempts', className: 'text-[11px] text-gray-800 border-l border-gray-100' },
                { data: 'scheduled', name: 'scheduled', className: 'text-[11px] text-gray-800 border-l border-gray-100' },
                { data: 'est', name: 'est', className: 'text-[11px] text-gray-800 border-l border-gray-100' },
                { data: 'attempted_rate', name: 'attempted_rate', className: 'text-[11px] text-gray-800 border-l border-gray-100' },
                { data: 'scheduled_rate', name: 'scheduled_rate', className: 'text-[11px] text-gray-800 border-l border-gray-100' }
            ]
        });

        // Init Non-Reminders
        let nonRemTable = DDS.dataTable(document.getElementById('nonRemindersTable'), {
            ...dtConfig,
            ajax: {
                url: "{{ route('front-office.performance-non-reminders-data') }}",
                data: sendDateParams
            },
            columns: [
                { data: 'name', name: 'name', className: 'text-[11px] text-gray-800' },
                { data: 'location', name: 'location', className: 'text-[11px] text-gray-800 border-l border-gray-100' },
                { data: 'attempts', name: 'attempts', className: 'text-[11px] text-gray-800 border-l border-gray-100' },
                { data: 'scheduled', name: 'scheduled', className: 'text-[11px] text-gray-800 border-l border-gray-100' },
                { data: 'est', name: 'est', className: 'text-[11px] text-gray-800 border-l border-gray-100' },
                { data: 'scheduled_rate', name: 'scheduled_rate', className: 'text-[11px] text-gray-800 border-l border-gray-100' }
            ]
        });

        // Init Totals
        let totTable = DDS.dataTable(document.getElementById('totalsTable'), {
            ...dtConfig,
            ajax: {
                url: "{{ route('front-office.performance-totals-data') }}",
                data: sendDateParams
            },
            columns: [
                { data: 'name', name: 'name', className: 'text-[11px] text-gray-800' },
                { data: 'location', name: 'location', className: 'text-[11px] text-gray-800 border-l border-gray-100' },
                { data: 'tot_attempts', name: 'tot_attempts', className: 'text-[11px] text-gray-800 border-l border-gray-100' },
                { data: 'tot_scheduled', name: 'tot_scheduled', className: 'text-[11px] text-gray-800 border-l border-gray-100' },
                { data: 'tot_est', name: 'tot_est', className: 'text-[11px] text-gray-800 border-l border-gray-100' },
                { data: 'tot_scheduled_rate', name: 'tot_scheduled_rate', className: 'text-[11px] text-gray-800 border-l border-gray-100' }
            ]
        });

        window.reloadFoTables = function () {
            if (remTable) remTable.ajax.reload();
            if (nonRemTable) nonRemTable.ajax.reload();
            if (totTable) totTable.ajax.reload();
        };

        // Bind Custom Search Inputs
        $('#remSearch').off('keyup').on('keyup', function () { remTable.search(this.value).draw(); });
        $('#nonRemSearch').off('keyup').on('keyup', function () { nonRemTable.search(this.value).draw(); });
        $('#totSearch').off('keyup').on('keyup', function () { totTable.search(this.value).draw(); });

        // Bind Export CSV Buttons
        $('#exportRemindersCsvBtn').on('click', function () { exportTableToCSV($('#remindersTable'), 'reminders_export'); });
        $('#exportNonRemindersCsvBtn').on('click', function () { exportTableToCSV($('#nonRemindersTable'), 'non_reminders_export'); });
        $('#exportTotalsCsvBtn').on('click', function () { exportTableToCSV($('#totalsTable'), 'performance_totals_export'); });
    });
</script>