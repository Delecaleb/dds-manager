<main class="p-6 space-y-6 max-w-[1600px] mx-auto bg-gray-50 min-h-screen pt-4">

    <!-- Top Charts Section -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Card 1: Patient Balances -->
        <div class="bg-white rounded-md border border-gray-200 shadow-sm flex flex-col overflow-hidden relative min-h-[360px]">
            <div class="h-1.5 w-full bg-emerald-500 absolute top-0 left-0"></div>
            <div class="p-4 flex items-center justify-between">
                <h3 class="font-bold text-gray-900 text-[15px]">Patient Balances</h3>
                <span class="text-gray-400 hover:text-gray-600 cursor-pointer" title="Displays patient aging balance distribution">
                    <i class="fa-regular fa-circle-info text-xs"></i>
                </span>
            </div>
            <!-- Chart Area -->
            <div class="flex justify-center items-center mb-4 h-48 relative px-4">
                <canvas id="patBalancesChart"></canvas>
            </div>
            <!-- Legend Area -->
            <div class="px-6 pb-6 space-y-2 text-[11px] font-bold text-gray-800 w-full mt-auto">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-sm bg-[#5ce6a1]"></span> 0-30 Days</div>
                    <span class="text-gray-600 font-semibold" id="bal-lbl-curr">$ 0.00</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-sm bg-[#a85cf0]"></span> 31-60 Days</div>
                    <span class="text-gray-600 font-semibold" id="bal-lbl-30">$ 0.00</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-sm bg-[#42cbf5]"></span> 61-90 Days</div>
                    <span class="text-gray-600 font-semibold" id="bal-lbl-60">$ 0.00</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-sm bg-[#ff7b72]"></span> 91-120 Days</div>
                    <span class="text-gray-600 font-semibold" id="bal-lbl-90">$ 0.00</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-sm bg-[#ffd166]"></span> 120+ Days</div>
                    <span class="text-gray-600 font-semibold" id="bal-lbl-120">$ 0.00</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Patient vs Insurance Collections -->
        <div class="bg-white rounded-md border border-gray-200 shadow-sm flex flex-col overflow-hidden relative min-h-[360px]">
            <div class="h-1.5 w-full bg-emerald-500 absolute top-0 left-0"></div>
            <div class="p-4 flex items-center justify-between">
                <h3 class="font-bold text-gray-900 text-[15px]">Patient vs Insurance Collections</h3>
                <span class="text-gray-400 hover:text-gray-600 cursor-pointer" title="Displays patient vs insurance collections for the selected month">
                    <i class="fa-regular fa-circle-info text-xs"></i>
                </span>
            </div>
            <!-- Chart Space -->
            <div class="flex justify-center items-center mb-4 h-48 relative px-4">
                <canvas id="patVsInsChart"></canvas>
            </div>
            <!-- Legend Area -->
            <div class="px-6 pb-6 space-y-2 text-[11px] font-bold text-gray-800 w-full mt-auto">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2 uppercase"><span class="w-3 h-3 rounded-sm bg-[#5ce6a1]"></span> Pts Collection</div>
                    <span class="text-gray-600 font-semibold" id="col-pts-lbl">$ 0.00</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2 uppercase"><span class="w-3 h-3 rounded-sm bg-[#a85cf0]"></span> Ins Collection</div>
                    <span class="text-gray-600 font-semibold" id="col-ins-lbl">$ 0.00</span>
                </div>
                <div class="flex justify-between items-center pt-1 border-t border-gray-100">
                    <div class="flex items-center gap-2 uppercase text-gray-500">Total Collections</div>
                    <span class="text-emerald-700 font-bold text-xs" id="col-tot-lbl">$ 0.00</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Adjustment Percent -->
        <div class="bg-white rounded-md border border-gray-200 shadow-sm flex flex-col overflow-hidden relative min-h-[360px]">
            <div class="h-1.5 w-full bg-emerald-500 absolute top-0 left-0"></div>
            <div class="p-4 flex items-center justify-between">
                <h3 class="font-bold text-gray-900 text-[15px]">Adjustment Percent</h3>
                <span class="text-gray-400 hover:text-gray-600 cursor-pointer" title="Displays total adjustments percentage of gross production for the selected month">
                    <i class="fa-regular fa-circle-info text-xs"></i>
                </span>
            </div>
            <!-- Chart Space -->
            <div class="flex justify-center items-center mb-4 h-48 relative px-4">
                <canvas id="adjPercentChart"></canvas>
            </div>
            <!-- Legend Area -->
            <div class="px-6 pb-6 space-y-2 text-[11px] font-bold text-gray-800 w-full mt-auto">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-sm bg-[#ff7b72]"></span> Total Adjustments</div>
                    <span class="text-rose-600 font-semibold" id="adj-total-lbl">$ 0.00</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-sm bg-[#42cbf5]"></span> Gross Production</div>
                    <span class="text-gray-600 font-semibold" id="adj-gross-lbl">$ 0.00</span>
                </div>
                <div class="flex justify-between items-center pt-1 border-t border-gray-100">
                    <div class="flex items-center gap-2 uppercase text-gray-500">Adjustment Rate</div>
                    <span class="text-gray-900 font-bold text-xs" id="adj-pct-lbl">0.00%</span>
                </div>
            </div>
        </div>

    </section>

    <!-- Data Table Section -->
    <section class="bg-white border border-gray-200 mt-6 shadow-sm overflow-hidden rounded-sm">
        <div class="border-b border-gray-200 bg-gray-50/75">
            <nav class="flex flex-wrap text-xs font-semibold text-gray-500" id="colTabs">
                <button type="button" data-subtab="patient-balances"
                    class="col-subtab-btn px-6 py-3.5 border-t-2 border-t-emerald-500 bg-white text-gray-900 font-bold border-r border-gray-200 hover:bg-white cursor-pointer shadow-[inset_0_2px_0_rgba(16,185,129,1)]">
                    Patient Balances 30/60/90
                </button>
                <button type="button" data-subtab="copay-collections"
                    class="col-subtab-btn px-6 py-3.5 border-t-2 border-t-transparent hover:text-gray-800 border-r border-gray-200 hover:bg-gray-100/60 cursor-pointer transition-colors">
                    CoPay Collections
                </button>
                <button type="button" data-subtab="adjustments"
                    class="col-subtab-btn px-6 py-3.5 border-t-2 border-t-transparent hover:text-gray-800 border-r border-gray-200 hover:bg-gray-100/60 cursor-pointer transition-colors">
                    Adjustments
                </button>
                <button type="button" data-subtab="collections"
                    class="col-subtab-btn px-6 py-3.5 border-t-2 border-t-transparent hover:text-gray-800 hover:bg-gray-100/60 cursor-pointer transition-colors">
                    Collections
                </button>
            </nav>
        </div>

        <div class="p-5 bg-white space-y-4">

            <!-- Info Alert -->
            <div id="colInfoAlert" class="bg-blue-50 border border-blue-200 rounded p-3 flex items-start gap-3 text-xs text-blue-800 font-medium">
                <i class="fa-regular fa-circle-info text-blue-500 mt-0.5 text-sm"></i>
                <p id="colInfoText">Values display Guarantor balances. Individual Patient Aging values can be viewed by selecting the breakout button next to the Guarantor name.</p>
            </div>

            <!-- Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex rounded shadow-sm text-xs font-bold">
                    <span class="px-3 py-1.5 bg-[#5ce6a1]/30 text-emerald-900 border border-emerald-300 rounded-l">Top 20%</span>
                    <span class="px-3 py-1.5 bg-[#ffd166]/30 text-amber-900 border-y border-r border-amber-300">Mid Tier</span>
                    <span class="px-3 py-1.5 bg-[#ff7b72]/30 text-rose-900 border-y border-r border-rose-300 rounded-r">Bottom 20%</span>
                </div>

                <div class="flex items-center gap-3 ml-auto">
                    <div class="relative">
                        <input type="text" placeholder="Search" id="colSearch"
                            class="bg-white border border-gray-300 text-gray-900 text-xs rounded pl-2.5 pr-8 py-1.5 focus:ring-emerald-500 focus:border-emerald-500 w-52 shadow-sm">
                        <i class="fa-solid fa-magnifying-glass absolute right-2.5 top-2.5 text-gray-400 text-[11px]"></i>
                    </div>
                    <button id="exportCollectionsCsvBtn" type="button"
                        class="text-xs font-bold text-gray-700 bg-white border border-emerald-500 rounded px-3 py-1.5 hover:bg-emerald-50 shadow-sm uppercase flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-download text-emerald-600"></i> Export CSV
                    </button>
                </div>
            </div>

            <!-- Table Dynamic Container -->
            <div class="overflow-x-auto custom-table-scrollbar border border-gray-200 rounded-sm">
                <table id="collectionsTable" class="dds-table w-full text-left border-collapse table-auto min-w-[1000px]">
                    <thead id="collectionsThead">
                        <!-- Populated dynamically based on active subtab -->
                    </thead>
                    <tbody class="text-xs font-medium text-gray-700">
                    </tbody>
                </table>
            </div>

        </div>
    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function () {
        let activeSubtab = 'patient-balances';
        let colTable = null;

        const infoDescriptions = {
            'patient-balances': 'Values display Guarantor balances. Individual Patient Aging values can be viewed by selecting the breakout button next to the Guarantor name.',
            'copay-collections': 'Displays Copay Collection Details by Patient and Date of Service.',
            'adjustments': 'Displays Adjustment Details by Patient and Adjustment Type.',
            'collections': 'Displays Collection Detail by Date compared to Net Production.'
        };

        // Format Money helper
        function fmtMoney(num) {
            let n = parseFloat(num) || 0;
            return '$ ' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Tier Class Helper
        function getTierClass(pct) {
            let p = parseFloat(pct) || 0;
            if (p >= 85) {
                return 'bg-emerald-50 text-emerald-800 font-semibold';
            } else if (p >= 50) {
                return 'bg-amber-50 text-amber-800 font-semibold';
            } else {
                return 'bg-rose-50 text-rose-800 font-semibold';
            }
        }

        // 1. Initialize Top Charts
        window._foCollectionsCharts = window._foCollectionsCharts || {};

        function initCharts() {
            // Chart 1: Balances
            const ctxBal = document.getElementById('patBalancesChart')?.getContext('2d');
            if (ctxBal) {
                if (window._foCollectionsCharts.balances) {
                    window._foCollectionsCharts.balances.destroy();
                }
                window._foCollectionsCharts.balances = new Chart(ctxBal, {
                    type: 'doughnut',
                    data: {
                        labels: ['0-30 Days', '31-60 Days', '61-90 Days', '91-120 Days', '120+ Days'],
                        datasets: [{
                            data: [0, 0, 0, 0, 0],
                            backgroundColor: ['#5ce6a1', '#a85cf0', '#42cbf5', '#ff7b72', '#ffd166'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        return ctx.label + ': ' + fmtMoney(ctx.raw);
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Chart 2: Patient vs Insurance
            const ctxCol = document.getElementById('patVsInsChart')?.getContext('2d');
            if (ctxCol) {
                if (window._foCollectionsCharts.patVsIns) {
                    window._foCollectionsCharts.patVsIns.destroy();
                }
                window._foCollectionsCharts.patVsIns = new Chart(ctxCol, {
                    type: 'doughnut',
                    data: {
                        labels: ['Patient Collections', 'Insurance Collections'],
                        datasets: [{
                            data: [0, 0],
                            backgroundColor: ['#5ce6a1', '#a85cf0'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        return ctx.label + ': ' + fmtMoney(ctx.raw);
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Chart 3: Adjustment Percent
            const ctxAdj = document.getElementById('adjPercentChart')?.getContext('2d');
            if (ctxAdj) {
                if (window._foCollectionsCharts.adjPercent) {
                    window._foCollectionsCharts.adjPercent.destroy();
                }
                window._foCollectionsCharts.adjPercent = new Chart(ctxAdj, {
                    type: 'doughnut',
                    data: {
                        labels: ['Adjustments', 'Net Production'],
                        datasets: [{
                            data: [0, 100],
                            backgroundColor: ['#ff7b72', '#5ce6a1'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        return ctx.label + ': ' + fmtMoney(ctx.raw);
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        // Hydrate Stats from Server
        function hydrateCollectionsStats() {
            let params = window.getFoDateParams ? window.getFoDateParams() : { month: $('#frontOfficeMonth').val() };
            $.get(`{{ route('front-office.collections-stats') }}`, params, function (data) {
                if (!data) return;

                // 1. Balances
                const b = data.balances || {};
                $('#bal-lbl-curr').text(fmtMoney(b.current));
                $('#bal-lbl-30').text(fmtMoney(b.over_30));
                $('#bal-lbl-60').text(fmtMoney(b.over_60));
                $('#bal-lbl-90').text(fmtMoney(b.over_90));
                $('#bal-lbl-120').text(fmtMoney(b.over_120));

                if (window._foCollectionsCharts.balances) {
                    window._foCollectionsCharts.balances.data.datasets[0].data = [
                        b.current || 0,
                        b.over_30 || 0,
                        b.over_60 || 0,
                        b.over_90 || 0,
                        b.over_120 || 0
                    ];
                    window._foCollectionsCharts.balances.update();
                }

                // 2. Collections
                const c = data.collections || {};
                $('#col-pts-lbl').text(fmtMoney(c.pts));
                $('#col-ins-lbl').text(fmtMoney(c.ins));
                $('#col-tot-lbl').text(fmtMoney(c.total));

                if (window._foCollectionsCharts.patVsIns) {
                    window._foCollectionsCharts.patVsIns.data.datasets[0].data = [
                        c.pts || 0,
                        c.ins || 0
                    ];
                    window._foCollectionsCharts.patVsIns.update();
                }

                // 3. Adjustments
                const a = data.adjustments || {};
                $('#adj-total-lbl').text(fmtMoney(a.total));
                $('#adj-gross-lbl').text(fmtMoney(a.gross_production));
                $('#adj-pct-lbl').text((parseFloat(a.percent) || 0).toFixed(2) + '%');

                if (window._foCollectionsCharts.adjPercent) {
                    let adjAmt = Math.abs(parseFloat(a.total) || 0);
                    let netAmt = Math.max(0, (parseFloat(a.net_production) || 0));
                    if (adjAmt === 0 && netAmt === 0) {
                        window._foCollectionsCharts.adjPercent.data.datasets[0].data = [0, 1];
                    } else {
                        window._foCollectionsCharts.adjPercent.data.datasets[0].data = [adjAmt, netAmt];
                    }
                    window._foCollectionsCharts.adjPercent.update();
                }
            });
        }

        // Subtab Table Configurations
        const tableConfigs = {
            'patient-balances': {
                thead: `
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-xs font-bold py-3 px-4 border-r border-gray-200 text-gray-800">Guarantor</th>
                        <th class="text-xs font-bold py-3 px-3 border-r border-gray-200 text-gray-800">Current</th>
                        <th class="text-xs font-bold py-3 px-3 border-r border-gray-200 text-gray-800">Over 30</th>
                        <th class="text-xs font-bold py-3 px-3 border-r border-gray-200 text-gray-800 bg-emerald-50/50">Over 60</th>
                        <th class="text-xs font-bold py-3 px-3 border-r border-gray-200 text-gray-800">Over 90</th>
                        <th class="text-xs font-bold py-3 px-3 border-r border-gray-200 text-gray-800">Over 120</th>
                        <th class="text-xs font-bold py-3 px-3 text-gray-800">Total</th>
                    </tr>
                `,
                columns: [
                    {
                        data: 'guarantor',
                        className: 'border-r border-y border-gray-100 bg-white py-2 px-4 shadow-[1px_0_0_0_rgba(243,244,246,1)]',
                        render: function (data) {
                            return `<div class="flex items-center gap-2">
                                <div class="px-1 py-0.5 rounded shadow-sm border border-gray-200 cursor-pointer hover:bg-gray-50"><i class="fa-solid fa-arrow-turn-down-right text-[10px] text-gray-400 rotate-180"></i></div>
                                <span class="font-semibold text-gray-900">${data}</span>
                            </div>`;
                        }
                    },
                    { data: 'current', className: 'border border-gray-100 bg-white py-2 px-3 tracking-wide', render: fmtMoney },
                    { data: 'over_30', className: 'border border-gray-100 bg-emerald-50/40 py-2 px-3 tracking-wide text-emerald-800 font-medium', render: fmtMoney },
                    { data: 'over_60', className: 'border border-gray-100 bg-emerald-100/50 py-2 px-3 tracking-wide text-emerald-900 font-semibold', render: fmtMoney },
                    { data: 'over_90', className: 'border border-gray-100 bg-emerald-50/40 py-2 px-3 tracking-wide text-emerald-800 font-medium', render: fmtMoney },
                    { data: 'over_120', className: 'border border-gray-100 py-2 px-3 tracking-wide text-gray-500', render: function (d) { return d > 0 ? fmtMoney(d) : '-'; } },
                    { data: 'total', className: 'border border-gray-100 bg-red-50 py-2 px-3 tracking-wide text-red-900 font-bold', render: fmtMoney }
                ],
                order: [[6, 'desc']]
            },
            'copay-collections': {
                thead: `
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-xs font-bold py-3 px-4 border-r border-gray-200 text-gray-800">Patient</th>
                        <th class="text-xs font-bold py-3 px-3 border-r border-gray-200 text-gray-800">Provider</th>
                        <th class="text-xs font-bold py-3 px-3 border-r border-gray-200 text-gray-800">Date of Service</th>
                        <th class="text-xs font-bold py-3 px-3 border-r border-gray-200 text-gray-800">Patient Paid</th>
                        <th class="text-xs font-bold py-3 px-3 border-r border-gray-200 text-gray-800">Patient Portion</th>
                        <th class="text-xs font-bold py-3 px-3 text-gray-800">Copay %</th>
                    </tr>
                `,
                columns: [
                    {
                        data: 'patient',
                        className: 'border-r border-y border-gray-100 bg-white py-2 px-4 shadow-[1px_0_0_0_rgba(243,244,246,1)]',
                        render: function (data) {
                            return `<div class="flex items-center gap-2">
                                <div class="px-1 py-0.5 rounded shadow-sm border border-gray-200 cursor-pointer hover:bg-gray-50"><i class="fa-solid fa-arrow-turn-down-right text-[10px] text-gray-400 rotate-180"></i></div>
                                <span class="font-semibold text-gray-900">${data}</span>
                            </div>`;
                        }
                    },
                    { data: 'provider', className: 'border border-gray-100 bg-white py-2 px-3' },
                    { data: 'date_of_service', className: 'border border-gray-100 bg-white py-2 px-3 text-gray-600' },
                    {
                        data: 'patient_paid',
                        className: 'border border-gray-100 py-2 px-3 font-semibold',
                        render: function (d, type, r) {
                            return fmtMoney(d);
                        }
                    },
                    { data: 'patient_portion', className: 'border border-gray-100 bg-white py-2 px-3 font-semibold', render: fmtMoney },
                    {
                        data: 'copay_percent',
                        className: 'border border-gray-100 py-2 px-3 font-bold',
                        render: function (d) {
                            let cls = getTierClass(d);
                            return `<span class="px-2 py-0.5 rounded ${cls}">${(parseFloat(d) || 0).toFixed(2)}%</span>`;
                        }
                    }
                ],
                order: [[2, 'desc']]
            },
            'adjustments': {
                thead: `
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-xs font-bold py-3 px-4 border-r border-gray-200 text-gray-800">Patient</th>
                        <th class="text-xs font-bold py-3 px-3 border-r border-gray-200 text-gray-800">Provider</th>
                        <th class="text-xs font-bold py-3 px-3 border-r border-gray-200 text-gray-800">Date</th>
                        <th class="text-xs font-bold py-3 px-3 border-r border-gray-200 text-gray-800">Adjustment Type</th>
                        <th class="text-xs font-bold py-3 px-3 border-r border-gray-200 text-gray-800">Amount</th>
                        <th class="text-xs font-bold py-3 px-3 text-gray-800">Note</th>
                    </tr>
                `,
                columns: [
                    {
                        data: 'patient',
                        className: 'border-r border-y border-gray-100 bg-white py-2 px-4 shadow-[1px_0_0_0_rgba(243,244,246,1)]',
                        render: function (data) {
                            return `<div class="flex items-center gap-2">
                                <div class="px-1 py-0.5 rounded shadow-sm border border-gray-200 cursor-pointer hover:bg-gray-50"><i class="fa-solid fa-arrow-turn-down-right text-[10px] text-gray-400 rotate-180"></i></div>
                                <span class="font-semibold text-gray-900">${data}</span>
                            </div>`;
                        }
                    },
                    { data: 'provider', className: 'border border-gray-100 bg-white py-2 px-3' },
                    { data: 'date', className: 'border border-gray-100 bg-white py-2 px-3 text-gray-600' },
                    { data: 'adjustment_type', className: 'border border-gray-100 bg-white py-2 px-3 font-medium text-gray-800' },
                    {
                        data: 'amount',
                        className: 'border border-gray-100 py-2 px-3 font-bold',
                        render: function (d) {
                            let n = parseFloat(d) || 0;
                            let color = n < 0 ? 'text-rose-700 bg-rose-50/50' : 'text-emerald-700 bg-emerald-50/50';
                            return `<span class="px-2 py-0.5 rounded ${color}">${fmtMoney(n)}</span>`;
                        }
                    },
                    { data: 'note', className: 'border border-gray-100 bg-white py-2 px-3 text-gray-600 text-xs' }
                ],
                order: [[2, 'desc']]
            },
            'collections': {
                thead: `
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-xs font-bold py-3 px-4 border-r border-gray-200 text-gray-800">Date</th>
                        <th class="text-xs font-bold py-3 px-3 border-r border-gray-200 text-gray-800">Total Net Production</th>
                        <th class="text-xs font-bold py-3 px-3 border-r border-gray-200 text-gray-800">Total Collections</th>
                        <th class="text-xs font-bold py-3 px-3 text-gray-800">Collection %</th>
                    </tr>
                `,
                columns: [
                    { data: 'date', className: 'border-r border-y border-gray-100 bg-white py-2 px-4 font-semibold text-gray-900' },
                    { data: 'total_net_production', className: 'border border-gray-100 bg-white py-2 px-3 font-semibold text-gray-800', render: fmtMoney },
                    { data: 'total_collections', className: 'border border-gray-100 bg-white py-2 px-3 font-bold text-emerald-800', render: fmtMoney },
                    {
                        data: 'collection_percent',
                        className: 'border border-gray-100 py-2 px-3 font-bold',
                        render: function (d) {
                            let cls = getTierClass(d);
                            return `<span class="px-2.5 py-1 rounded ${cls}">${(parseFloat(d) || 0).toFixed(2)}%</span>`;
                        }
                    }
                ],
                order: [[0, 'desc']]
            }
        };

        // Load or Switch Table
        function loadSubtabTable(subtab) {
            activeSubtab = subtab;
            const config = tableConfigs[subtab];
            if (!config) return;

            // Update info alert
            $('#colInfoText').text(infoDescriptions[subtab] || '');

            // Destroy old table
            if (colTable) {
                colTable.destroy();
                $('#collectionsTable').empty();
            }

            // Set new thead
            $('#collectionsThead').html(config.thead);

            // Recreate table
            colTable = DDS.dataTable(document.getElementById('collectionsTable'), {
                processing: true,
                serverSide: true,
                pageLength: 20,
                lengthChange: true,
                lengthMenu: [[10, 20, 50, 100, -1], [10, 20, 50, 100, 'All']],
                layout: { topStart: null, topEnd: null, bottomStart: ['pageLength', 'info'], bottomEnd: 'paging' },
                language: {
                    info: '_START_-_END_ of _TOTAL_ items',
                    paginate: {
                        previous: '<i class="fa-solid fa-chevron-left text-[10px]"></i>',
                        next: '<i class="fa-solid fa-chevron-right text-[10px]"></i>'
                    }
                },
                ajax: {
                    url: "{{ route('front-office.collections-data') }}",
                    data: function (d) {
                        d.subtab = activeSubtab;
                        if (window.getFoDateParams) {
                            $.extend(d, window.getFoDateParams());
                        } else {
                            d.month = $('#frontOfficeMonth').val() || '';
                        }
                    }
                },
                columns: config.columns,
                order: config.order,
                drawCallback: function () {
                    $('.dt-length').addClass('text-xs font-semibold text-gray-600 flex items-center gap-1.5 p-3');
                    $('.dt-length select').addClass('border border-gray-300 rounded text-gray-700 py-1 px-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white cursor-pointer outline-none text-xs');
                    $('.dt-info').addClass('text-xs font-semibold text-gray-600 flex items-center p-3');
                    $('.dt-paging nav').addClass('flex items-center gap-1');
                    $('.dt-paging').addClass('flex items-center pl-4 border-l border-gray-200 h-full');
                    $('.dt-paging-button').addClass('px-2.5 py-1 text-xs font-bold border border-gray-200 text-gray-500 bg-white hover:bg-gray-50 rounded transition-colors shadow-sm cursor-pointer');
                    $('.dt-paging-button.current').removeClass('bg-white text-gray-500 hover:bg-gray-50 border-gray-200').addClass('bg-emerald-50 text-emerald-700 border-emerald-300 shadow-[inset_0_0_0_1px_rgba(16,185,129,0.2)]');
                    $('.dt-paging-button.disabled').addClass('opacity-40 cursor-not-allowed hover:bg-white').removeClass('hover:bg-gray-50 cursor-pointer');

                    // Link external search box to Datatables search
                    $('#colSearch').off('keyup').on('keyup', function () {
                        colTable.search(this.value).draw();
                    });
                }
            });
        }

        // Subtab Navigation Click Handler
        $(document).off('click', '.col-subtab-btn').on('click', '.col-subtab-btn', function () {
            let $this = $(this);
            let sub = $this.data('subtab');
            if (sub === activeSubtab) return;

            // Switch active styling
            $('.col-subtab-btn')
                .removeClass('border-t-emerald-500 bg-white text-gray-900 font-bold shadow-[inset_0_2px_0_rgba(16,185,129,1)]')
                .addClass('border-t-transparent hover:text-gray-800 hover:bg-gray-100/60 font-semibold text-gray-500');

            $this
                .removeClass('border-t-transparent hover:text-gray-800 hover:bg-gray-100/60 font-semibold text-gray-500')
                .addClass('border-t-emerald-500 bg-white text-gray-900 font-bold shadow-[inset_0_2px_0_rgba(16,185,129,1)]');

            loadSubtabTable(sub);
        });

        // Initialize Everything
        initCharts();
        hydrateCollectionsStats();
        loadSubtabTable('patient-balances');

        // Global Refresh Hook
        window.reloadFoTables = function () {
            hydrateCollectionsStats();
            if (colTable) {
                colTable.ajax.reload();
            }
        };

        $('#exportCollectionsCsvBtn').off('click').on('click', function () {
            exportTableToCSV($('#collectionsTable'), activeSubtab + '_export');
        });

    })();
</script>