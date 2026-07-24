<x-app-layout>

    <!-- ── HEADER ─────────────────────────────────────────── -->
    <header
        class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between shrink-0 sticky top-0 z-20">
        <div class="flex items-center gap-4">
            <h1 class="text-lg font-bold text-slate-800 tracking-tight">Treatment Miner</h1>

            <!-- Filter Section Placeholder -->
            <div class="border-l border-slate-200 pl-4">
                <select
                    class="appearance-none bg-slate-50 border border-slate-200 rounded-lg pl-3 pr-9 py-1.5 text-sm font-semibold text-slate-900 focus:outline-none focus:border-emerald-500 focus:bg-white cursor-pointer transition-colors">
                    <option value="all">All Locations</option>
                </select>
            </div>

            <x-daterange-picker id="txMinerDateRange" />
        </div>
    </header>

    <!-- ── MAIN ───────────────────────────────────────────── -->
    <main class="p-6 space-y-6 max-w-full mx-auto">

        <!-- Tabs -->
        <div class="border-b border-slate-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button type="button"
                    class="tab-btn active border-emerald-500 text-emerald-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                    data-target="#tab-month">
                    By month
                </button>
                <button type="button"
                    class="tab-btn border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                    data-target="#tab-provider">
                    By Provider
                </button>
                <button type="button"
                    class="tab-btn border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                    data-target="#tab-location">
                    By Location
                </button>
            </nav>
        </div>

        <!-- Tab contents -->
        <div class="tab-content relative">

            <!-- By Month Tab -->
            <div id="tab-month" class="tab-pane active">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 overflow-x-hidden">

                    <x-data-table id="tableByMonth" min-width="1200px">
                        <x-slot:head>
                            <tr>
                                <th class="dt-col-sticky px-4 py-3 min-w-[120px]">Month</th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        Total TX Plan
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button
                                                class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <line x1="12" y1="16" x2="12" y2="12" />
                                                    <line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div
                                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal">
                                                Displays the $ amount of treatment plans presented or refreshed. To
                                                refresh a treatment plan, the treatment plan date must be updated
                                                <div
                                                    class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        Tx Scheduled
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button
                                                class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <line x1="12" y1="16" x2="12" y2="12" />
                                                    <line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div
                                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal">
                                                Displays the $ amount of procedures scheduled from a treatment plan
                                                (excludes broken appointments)
                                                <div
                                                    class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        Tx Unscheduled
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button
                                                class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <line x1="12" y1="16" x2="12" y2="12" />
                                                    <line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div
                                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal">
                                                Displays the $ amount of unscheduled procedures from a treatment plan
                                                (includes broken appointments)
                                                <div
                                                    class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        Completed Tx
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button
                                                class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <line x1="12" y1="16" x2="12" y2="12" />
                                                    <line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div
                                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal">
                                                Displays the $ amount of procedure codes completed from a treatment plan
                                                <div
                                                    class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        Case Acceptance %
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button
                                                class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <line x1="12" y1="16" x2="12" y2="12" />
                                                    <line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div
                                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal">
                                                Displays the percentage of treatment plans closed or accepted(Completed
                                                Treatment Plan + Scheduled Treatment Plan) / Total Tx Plan $ Proposed *
                                                100
                                                <div
                                                    class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        # TX Plan Presented
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button
                                                class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <line x1="12" y1="16" x2="12" y2="12" />
                                                    <line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div
                                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal">
                                                # of treatment plans presented or refreshed
                                                <div
                                                    class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        Average Treatment Plan
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button
                                                class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <line x1="12" y1="16" x2="12" y2="12" />
                                                    <line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div
                                                class="absolute bottom-full right-0 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal">
                                                Average amount of treatment plans. Total Tx Plan $ / # of Tx Plan
                                                Presented
                                                <div class="absolute -bottom-1 right-3 w-2 h-2 bg-slate-900 rotate-45">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        Patients with Tx Plan %
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button
                                                class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <line x1="12" y1="16" x2="12" y2="12" />
                                                    <line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div
                                                class="absolute bottom-full right-0 mb-2 w-72 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal">
                                                % of patients seen that also received a treatment plan. # of Patient
                                                with a Treatment Plan / # of Patients Seen in the selected date range *
                                                100
                                                <div class="absolute -bottom-1 right-4 w-2 h-2 bg-slate-900 rotate-45">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                        </x-slot:head>
                    </x-data-table>

                </div>
            </div>

            <!-- By Provider Tab -->
            <div id="tab-provider" class="tab-pane hidden">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <p class="text-sm text-slate-500">Provider specific data will appear here.</p>
                </div>
            </div>

            <!-- By Location Tab -->
            <div id="tab-location" class="tab-pane hidden">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <p class="text-sm text-slate-500">Location specific data will appear here.</p>
                </div>
            </div>

        </div>

    </main>

    <script>
        const baseUrl = "{{ url('') }}";

        // Wait for jQuery
        $(document).ready(function () {
            // Initialize DataTable
            $('#tableByMonth').DataTable({
                processing: true,
                serverSide: true,
                paging: true,
                searching: false,
                ordering: false,
                ajax: {
                    url: baseUrl + '/tx-miner/data',
                    type: 'GET'
                },
                columns: [
                    {
                        data: 'month',
                        render: (data) => `<span class="font-bold text-gray-900">${data}</span>`
                    },
                    { data: 'total_tx_plan' },
                    { data: 'tx_scheduled' },
                    { data: 'tx_unscheduled' },
                    { data: 'completed_tx' },
                    {
                        data: 'case_acceptance',
                        render: function (data) {
                            return `<div class="bg-blue-50 text-blue-700 px-2 py-1 rounded inline-block font-semibold border border-blue-200">${data}</div>`;
                        }
                    },
                    { data: 'tx_presented' },
                    { data: 'avg_tx_plan' },
                    { data: 'patients_with_tx' }
                ]
            });

            // Tab switching logic
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabPanes = document.querySelectorAll('.tab-pane');

            // URL-driven + deep-linkable (DDS.tabs.deeplink). Tab key = the id after "#tab-".
            function activateTxTab(tab) {
                var targetSelect = '#tab-' + tab;
                tabBtns.forEach(b => {
                    b.classList.remove('active', 'border-emerald-500', 'text-emerald-600');
                    b.classList.add('border-transparent', 'text-slate-500');
                });
                var btn = document.querySelector('.tab-btn[data-target="' + targetSelect + '"]');
                if (btn) {
                    btn.classList.add('active', 'border-emerald-500', 'text-emerald-600');
                    btn.classList.remove('border-transparent', 'text-slate-500');
                }
                tabPanes.forEach(pane => { pane.classList.add('hidden'); pane.classList.remove('active'); });
                var target = document.querySelector(targetSelect);
                if (target) { target.classList.remove('hidden'); target.classList.add('active'); }
            }
            var txTabs = DDS.tabs.deeplink('tab', activateTxTab);
            tabBtns.forEach(btn => {
                btn.addEventListener('click', () => { txTabs.go(btn.getAttribute('data-target').replace('#tab-', '')); });
            });
            // Deep-link: honor ?tab= on load, else the default 'month' tab.
            activateTxTab(txTabs.initial || 'month');
        });
    </script>
</x-app-layout>