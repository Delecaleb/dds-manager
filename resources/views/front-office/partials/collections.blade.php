<main class="p-6 space-y-6 max-w-[1600px] mx-auto bg-gray-50 min-h-screen pt-4">

    <!-- Top Charts Section -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Card 1: Patient Balances -->
        <div
            class="bg-white rounded-md border border-gray-200 shadow-sm flex flex-col overflow-hidden relative min-h-[360px]">
            <div class="h-1.5 w-full bg-red-500 absolute top-0 left-0"></div>
            <div class="p-4 flex items-center justify-between">
                <h3 class="font-bold text-gray-900 text-[15px]">Patient Balances</h3>
                <button class="text-gray-400 hover:text-gray-600"><i
                        class="fa-regular fa-circle-info text-xs"></i></button>
            </div>
            <!-- Chart Area -->
            <div class="flex justify-center mb-6 h-48 relative">
                <canvas id="patBalancesChart"></canvas>
            </div>
            <!-- Legend Area -->
            <div class="px-6 pb-6 space-y-2 text-[10px] font-bold text-gray-800 w-full">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 bg-[#5ce6a1]"></span> 0-30 Days</div>
                    <span class="text-gray-500 font-medium" id="bal-lbl-curr">$ 0</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 bg-[#a85cf0]"></span> 31-60 Days</div>
                    <span class="text-gray-500 font-medium" id="bal-lbl-30">$ 0</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 bg-[#42cbf5]"></span> 61-90 Days</div>
                    <span class="text-gray-500 font-medium" id="bal-lbl-60">$ 0</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 bg-[#ff7b72]"></span> 91-120 Days</div>
                    <span class="text-gray-500 font-medium" id="bal-lbl-90">$ 0</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 bg-[#ffd166]"></span> 120+ Days</div>
                    <span class="text-gray-500 font-medium" id="bal-lbl-120">$ 0</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Patient vs Insurance Collections -->
        <div
            class="bg-white rounded-md border border-gray-200 shadow-sm flex flex-col overflow-hidden relative min-h-[360px]">
            <div class="h-1.5 w-full bg-red-500 absolute top-0 left-0"></div>
            <div class="p-4 flex items-center justify-between">
                <h3 class="font-bold text-gray-900 text-[15px]">Patient vs Insurance Collections</h3>
                <button class="text-gray-400 hover:text-gray-600"><i
                        class="fa-regular fa-circle-info text-xs"></i></button>
            </div>
            <!-- Empty Chart Space (No chart drawn in mockup) -->
            <div class="flex-1 flex justify-center items-center">
                <!-- Space for bar chart -->
            </div>
            <!-- Legend Area -->
            <div class="px-6 pb-6 space-y-2 text-[10px] font-bold text-gray-800 w-full mt-auto">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2 uppercase"><span class="w-3 h-3 bg-[#5ce6a1]"></span> Pts
                        Collection</div>
                    <span class="text-gray-500 font-medium" id="col-pts-lbl">$ 0</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2 uppercase"><span class="w-3 h-3 bg-[#a85cf0]"></span> Ins
                        Collection</div>
                    <span class="text-gray-500 font-medium" id="col-ins-lbl">$ 0</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Adjustment Percent -->
        <div
            class="bg-white rounded-md border border-gray-200 shadow-sm flex flex-col overflow-hidden relative min-h-[360px]">
            <div class="h-1.5 w-full bg-red-500 absolute top-0 left-0"></div>
            <div class="p-4 flex items-center justify-between">
                <h3 class="font-bold text-gray-900 text-[15px]">Adjustment Percent</h3>
                <button class="text-gray-400 hover:text-gray-600"><i
                        class="fa-regular fa-circle-info text-xs"></i></button>
            </div>
            <!-- Empty Chart Space -->
            <div class="flex-1 flex justify-center items-center"></div>
        </div>

    </section>


    <!-- Data Table Section -->
    <section class="bg-white border border-gray-200 mt-6 shadow-sm overflow-hidden">
        <div class="border-b border-gray-100 bg-gray-50/50">
            <nav class="flex text-[11px] font-semibold text-gray-500" id="colTabs">
                <button
                    class="border-t-2 border-transparent bg-white text-gray-900 px-6 py-4 shadow-[inset_0_2px_0_rgba(16,185,129,1)] hover:bg-gray-50 border-r border-gray-200 cursor-pointer">Patient
                    Balances 30/60/90</button>
                <button
                    class="border-t-2 border-transparent hover:text-gray-700 px-6 py-4 hover:bg-gray-50 cursor-pointer">CoPay
                    Collections</button>
                <button
                    class="border-t-2 border-transparent hover:text-gray-700 px-6 py-4 hover:bg-gray-50 cursor-pointer">Adjustments</button>
                <button
                    class="border-t-2 border-transparent hover:text-gray-700 px-6 py-4 hover:bg-gray-50 cursor-pointer">Collections</button>
            </nav>
        </div>

        <div class="p-4 bg-white space-y-4">

            <!-- Info Alert -->
            <div class="bg-blue-50 border border-blue-200 rounded p-3 flex items-start gap-3">
                <i class="fa-regular fa-circle-info text-blue-500 mt-0.5"></i>
                <p class="text-xs text-blue-700 font-medium">Values display Guarantor balances. Individual Patient Aging
                    values can be viewed by selecting the breakout button next to the Guarantor name.</p>
            </div>

            <!-- Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex rounded shadow-sm text-[11px] font-bold">
                    <button class="px-4 py-1.5 bg-green-100 text-green-800 border border-green-200 rounded-l">Top
                        20%</button>
                    <button class="px-4 py-1.5 bg-gray-100 text-gray-600 border-y border-gray-200 hover:bg-gray-200">Mid
                        Tier</button>
                    <button class="px-4 py-1.5 bg-red-100 text-red-800 border border-red-200 rounded-r">Bottom
                        20%</button>
                </div>

                <div class="flex items-center gap-3">
                    <button class="text-gray-400 hover:text-gray-600"><i
                            class="fa-regular fa-circle-info text-[11px]"></i></button>
                    <div class="relative">
                        <input type="text" placeholder="Search" id="colSearch"
                            class="bg-white border border-gray-300 text-gray-900 text-xs rounded pl-2 pr-7 py-1.5 focus:ring-emerald-500 focus:border-emerald-500 w-48 shadow-sm">
                        <i class="fa-solid fa-magnifying-glass absolute right-2.5 top-2 text-gray-400 text-[10px]"></i>
                    </div>
                    <button
                        class="text-[11px] font-bold text-gray-800 bg-white border border-emerald-500 rounded px-3 py-1.5 hover:bg-emerald-50 shadow-sm uppercase">
                        Export CSV
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto custom-table-scrollbar">
                <table id="collectionsTable" class="w-full text-left border-collapse table-auto min-w-[1000px]">
                    <thead>
                        <tr class="bg-white border-y border-gray-200">
                            <th class="text-[11px] font-bold py-3 px-4 border-r border-gray-200 text-gray-800">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1">
                                        <i class="fa-solid fa-arrows-up-down text-[9px] text-gray-400"></i> Guarantor
                                    </div>
                                    <i class="fa-regular fa-circle-info text-gray-300 text-[10px]"></i>
                                </div>
                            </th>
                            <th class="text-[11px] font-bold py-3 px-3 border-r border-gray-200 text-gray-800">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1">
                                        <i class="fa-solid fa-arrows-up-down text-[9px] text-gray-400"></i> Current
                                    </div>
                                    <i class="fa-regular fa-circle-info text-gray-300 text-[10px]"></i>
                                </div>
                            </th>
                            <th class="text-[11px] font-bold py-3 px-3 border-r border-gray-200 text-gray-800">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1">
                                        <i class="fa-solid fa-arrows-up-down text-[9px] text-gray-400"></i> Over 30
                                    </div>
                                    <i class="fa-regular fa-circle-info text-gray-300 text-[10px]"></i>
                                </div>
                            </th>
                            <th
                                class="text-[11px] font-bold py-3 px-3 border-r border-gray-200 text-gray-800 bg-emerald-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1">
                                        <i class="fa-solid fa-arrows-up-down text-[9px] text-gray-400"></i> Over 60
                                    </div>
                                    <i class="fa-regular fa-circle-info text-gray-300 text-[10px]"></i>
                                </div>
                            </th>
                            <th class="text-[11px] font-bold py-3 px-3 border-r border-gray-200 text-gray-800">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1">
                                        <i class="fa-solid fa-arrows-up-down text-[9px] text-gray-400"></i> Over 90
                                    </div>
                                    <i class="fa-regular fa-circle-info text-gray-300 text-[10px]"></i>
                                </div>
                            </th>
                            <th class="text-[11px] font-bold py-3 px-3 border-r border-gray-200 text-gray-800">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1">
                                        <i class="fa-solid fa-arrows-up-down text-[9px] text-gray-400"></i> Over 120
                                    </div>
                                    <i class="fa-regular fa-circle-info text-gray-300 text-[10px]"></i>
                                </div>
                            </th>
                            <th class="text-[11px] font-bold py-3 px-3 text-gray-800">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1">
                                        <i class="fa-solid fa-arrows-up-down text-[9px] text-gray-400"></i> Total
                                    </div>
                                    <i class="fa-regular fa-circle-info text-gray-300 text-[10px]"></i>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-[11px] font-medium text-gray-700">
                    </tbody>
                </table>
            </div>

        </div>
    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function () {

        // Chart Config
        const chartConfig = {
            type: 'doughnut',
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: { legend: { display: false }, tooltip: { enabled: true } },
                borderWidth: 0
            }
        };

        let balancesChart;
        try {
            const ctx = document.getElementById('patBalancesChart').getContext('2d');
            balancesChart = new Chart(ctx, {
                ...chartConfig,
                data: {
                    labels: ['0-30 Days', '31-60 Days', '61-90 Days', '91-120 Days', '120+ Days'],
                    datasets: [{
                        data: [0, 0, 0, 0, 0],
                        backgroundColor: ['#5ce6a1', '#a85cf0', '#42cbf5', '#ff7b72', '#ffd166'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                }
            });
        } catch (e) {
            console.warn('Charts failed to initialize', e);
        }

        // Hydrate Stats
        function hydrateCollections() {
            let month = $('#frontOfficeMonth').val() || '';
            $.get(`{{ route('front-office.collections-stats') }}?month=${month}`, function (data) {
                $('#bal-lbl-curr').text('$ ' + data.balances.current.toLocaleString('en-US', { minimumFractionDigits: 2 }));
                $('#bal-lbl-30').text('$ ' + data.balances.over_30.toLocaleString('en-US', { minimumFractionDigits: 2 }));
                $('#bal-lbl-60').text('$ ' + data.balances.over_60.toLocaleString('en-US', { minimumFractionDigits: 2 }));
                $('#bal-lbl-90').text('$ ' + data.balances.over_90.toLocaleString('en-US', { minimumFractionDigits: 2 }));
                $('#bal-lbl-120').text('$ ' + data.balances.over_120.toLocaleString('en-US', { minimumFractionDigits: 2 }));

                $('#col-pts-lbl').text('$ ' + data.collections.pts.toLocaleString('en-US', { minimumFractionDigits: 2 }));
                $('#col-ins-lbl').text('$ ' + data.collections.ins.toLocaleString('en-US', { minimumFractionDigits: 2 }));

                if (balancesChart) {
                    balancesChart.data.datasets[0].data = [
                        data.balances.current,
                        data.balances.over_30,
                        data.balances.over_60,
                        data.balances.over_90,
                        data.balances.over_120
                    ];
                    balancesChart.update();
                }
            });
        }
        // Trigger load initially
        hydrateCollections();

        $('#frontOfficeMonth').off('change.cols').on('change.cols', function() {
            colTable.ajax.reload();
            hydrateCollections();
        });


        // DataTable Init
        let colTable = $('#collectionsTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            layout: { topStart: null, topEnd: null, bottomStart: 'info', bottomEnd: 'paging' },
            language: {
                info: '<span class="flex items-center gap-2">Items per page _MENU_ <span class="text-gray-300 mx-1">|</span> _START_-_END_ of _TOTAL_ items</span>',
                paginate: {
                    previous: '<i class="fa-solid fa-chevron-left text-[10px]"></i>',
                    next: '<i class="fa-solid fa-chevron-right text-[10px]"></i>'
                }
            },
            ajax: {
                url: "{{ route('front-office.collections-data') }}",
                data: function (d) {
                    d.month = $('#frontOfficeMonth').val() || '';
                }
            },
            columns: [
                {
                    data: 'guarantor', name: 'guarantor',
                    className: 'border-r border-y border-gray-100 bg-white py-2 px-4 shadow-[1px_0_0_0_rgba(243,244,246,1)]',
                    render: function (data) {
                        return `<div class="flex items-center gap-2">
                                        <div class="px-1 py-0.5 rounded shadow-sm border border-gray-200 cursor-pointer hover:bg-gray-50"><i class="fa-solid fa-arrow-turn-down-right text-[10px] text-gray-400 rotate-180"></i></div> 
                                        <span class="text-gray-900">${data}</span>
                                    </div>`;
                    }
                },
                { data: 'current', name: 'current', className: 'border border-gray-100 bg-white py-2 px-3 tracking-wide' },
                { data: 'over_30', name: 'over_30', className: 'border border-gray-100 bg-emerald-100/40 py-2 px-3 tracking-wide text-emerald-800' },
                { data: 'over_60', name: 'over_60', className: 'border border-gray-100 bg-emerald-100 py-2 px-3 tracking-wide text-emerald-900' },
                { data: 'over_90', name: 'over_90', className: 'border border-gray-100 bg-emerald-100/40 py-2 px-3 tracking-wide text-emerald-800' },
                {
                    data: 'over_120', name: 'over_120',
                    className: 'border border-gray-100 py-2 px-3 tracking-wide',
                    createdCell: function (td, cellData, rowData) {
                        // Example dynamically adding tint based on value (screenshot shows red for $10k+, so pink tint)
                        if (rowData.over_90 && rowData.over_90.indexOf('$ 0') === -1 && rowData.over_90 !== '-') {
                            // Mamp screenshot logic for row dynamic color
                        }
                        $(td).addClass('bg-red-50 text-red-800 font-semibold');
                    }
                },
                { data: 'total', name: 'total', className: 'border border-gray-100 bg-red-100/50 py-2 px-3 tracking-wide text-red-900 font-semibold shadow-sm' }
            ],
            order: [[6, 'desc']],
            drawCallback: function () {
                $('.dt-info').addClass('text-[11px] font-semibold text-gray-600 flex items-center pr-4');
                $('.dt-info select').addClass('border border-gray-300 rounded text-gray-700 py-1 px-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white cursor-pointer mx-1 outline-none text-[11px]');
                $('.dt-paging nav').addClass('flex items-center gap-1');
                $('.dt-paging').addClass('flex items-center pl-4 border-l border-gray-200 h-full');
                $('.dt-paging-button').addClass('px-2.5 py-1 text-[11px] font-bold border border-gray-200 text-gray-500 bg-white hover:bg-gray-50 rounded transition-colors shadow-sm cursor-pointer');
                $('.dt-paging-button.current').removeClass('bg-white text-gray-500 hover:bg-gray-50 border-gray-200').addClass('bg-emerald-50 text-emerald-700 border-emerald-300 shadow-[inset_0_0_0_1px_rgba(16,185,129,0.2)]');
                $('.dt-paging-button.disabled').addClass('opacity-40 cursor-not-allowed hover:bg-white').removeClass('hover:bg-gray-50 cursor-pointer');

                // Link external search box to Datatables search
                $('#colSearch').off('keyup').on('keyup', function () {
                    colTable.search(this.value).draw();
                });
            }
        });

        // Adjust specific row coloring based on screenshot examples (mock representations via classes)
    });
</script>