<main class="p-6 space-y-8 max-w-[1600px] mx-auto">

    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-bold text-gray-900">Monthly Production</h3>
                    <div class="text-xs font-semibold px-2 py-0.5 bg-gray-100 rounded text-gray-600">Line of
                        Business: All</div>
                </div>
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Goal</p>
                        <p class="text-xl font-bold text-gray-900">$ 109,286.00</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Percent to Goal</p>
                        <p class="text-xl font-bold text-gray-900">30.08%</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-100">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Actual Production</p>
                        <p class="text-xl font-bold text-emerald-600" id="mo-actual">...</p>
                        <p class="text-xs font-medium mt-0.5" id="mo-diff-goal">...</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Prior Year</p>
                        <p class="text-xl font-bold text-gray-900" id="mo-prior">...</p>
                        <p class="text-xs font-medium mt-0.5" id="mo-diff-year">...</p>
                    </div>
                </div>
            </div>
            <div class="mt-6">
                <div class="flex justify-between text-xs font-semibold mb-1">
                    <span class="text-indigo-600" id="mo-progress-actual">Net Prod (...)</span>
                    <span class="text-emerald-500" id="mo-progress-goal">Goal (...)</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-4 relative overflow-hidden">
                    <div id="mo-progress-bar" class="bg-indigo-500 h-4 rounded-full transition-all duration-1000"
                        style="width: 0%"></div>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-900">Daily Production</h3>
                    <span class="text-xs text-gray-500">Jun 21 - Jun 27</span>
                </div>
                <div class="h-36 flex items-end justify-between gap-2 pt-4 px-2" id="daily-production-chart">
                    <!-- Chart generated via JS -->
                </div>
            </div>
            <div class="flex justify-center gap-4 text-xs font-semibold mt-4 pt-2 border-t border-gray-50">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-emerald-400 rounded-sm"></span>
                    Goal</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-amber-400 rounded-sm"></span>
                    Actual</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-900">Visits</h3>
                    <span class="text-xs text-gray-500">Jun 21 - Jun 27</span>
                </div>
                <div class="h-36 flex items-end justify-between gap-1 pt-4" id="visits-chart">
                    <!-- Chart generated via JS -->
                </div>
            </div>
            <div
                class="flex flex-wrap justify-center gap-3 text-[11px] font-semibold mt-4 pt-2 border-t border-gray-50">
                <span class="flex items-center gap-1"><span class="w-2 h-2 bg-emerald-300 rounded-sm"></span> New
                    Patients</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 bg-purple-600 rounded-sm"></span>
                    Existing Patients</span>
            </div>
        </div>

    </section>

    <!-- New Opportunities Section -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Schedule Opportunities -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col overflow-hidden relative">
            <div class="h-1.5 w-full bg-blue-500 absolute top-0 left-0"></div>
            <div class="p-5 flex-1 mt-1">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-900 text-lg">Schedule Opportunities</h3>
                </div>

                <div class="mb-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 mb-2">Broken Appointments</h4>
                            <div class="flex items-end gap-2 mb-1">
                                <span class="text-3xl font-bold text-gray-900 leading-none"
                                    id="opp-broken-total">0</span>
                            </div>
                            <p class="text-xs text-gray-500">Patients with Cancellations or No Shows</p>
                        </div>
                        <button class="text-gray-400 hover:text-gray-600"><i
                                class="fa-regular fa-circle-info"></i></button>
                    </div>
                    <div class="mt-4">
                        <div class="flex h-5 w-full">
                            <div id="opp-broken-bar-unscheduled"
                                class="bg-[#42cbf5] h-full text-xs text-white font-bold flex items-center justify-center transition-all duration-1000"
                                style="width: 100%;">0</div>
                            <div id="opp-broken-bar-scheduled"
                                class="bg-[#5ce6a1] h-full text-xs text-white font-bold flex items-center justify-center transition-all duration-1000"
                                style="width: 0%;">0</div>
                        </div>
                        <div class="flex items-center gap-4 mt-2 text-xs font-semibold text-gray-700">
                            <div class="flex items-center gap-1.5"><span class="w-3 h-3 bg-[#42cbf5]"></span>
                                Unscheduled</div>
                            <div class="flex items-center gap-1.5"><span class="w-3 h-3 bg-[#5ce6a1]"></span>
                                Scheduled</div>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100 my-4">

                <div>
                    <div class="flex items-start justify-between">
                        <h4 class="text-sm font-bold text-gray-900 mb-2">Hygiene Reappointment</h4>
                        <button class="text-gray-400 hover:text-gray-600"><i
                                class="fa-regular fa-circle-info"></i></button>
                    </div>
                    <div class="flex justify-between items-end mb-4">
                        <div>
                            <div class="text-3xl font-bold text-gray-900 leading-none mb-1" id="opp-hyg-unsched">0
                            </div>
                            <p class="text-xs text-gray-500 w-32">Patients with no future hyg appointment</p>
                        </div>
                        <div class="text-right">
                            <div class="text-xl font-bold text-gray-900 leading-none mb-1"><span
                                    id="opp-hyg-rate">0.00</span>%</div>
                            <p class="text-xs text-gray-500">Reappointment rate</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="relative h-1 w-full bg-gray-100 flex items-center justify-center">
                            <span class="bg-white px-2 text-xs font-bold -mt-2" id="opp-hyg-progress-val">0</span>
                        </div>
                        <div class="flex items-center gap-4 mt-4 text-xs font-semibold text-gray-700">
                            <div class="flex items-center gap-1.5"><span class="w-3 h-3 bg-[#42cbf5]"></span>
                                Unscheduled</div>
                            <div class="flex items-center gap-1.5"><span class="w-3 h-3 bg-[#5ce6a1]"></span>
                                Scheduled</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hygiene Recall Due -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col overflow-hidden relative">
            <div class="h-1.5 w-full bg-blue-500 absolute top-0 left-0"></div>
            <div class="p-5 flex-1 mt-1">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-900 text-lg">Hygiene Recall Due</h3>
                    <button class="text-gray-400 hover:text-gray-600"><i class="fa-regular fa-circle-info"></i></button>
                </div>

                <div class="flex justify-center mb-6 h-48 relative">
                    <canvas id="hygieneRecallChart"></canvas>
                </div>

                <div class="space-y-2 text-xs font-semibold text-gray-700 w-full px-4">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 bg-[#5ce6a1]"></span> 0-3 Months
                        </div> <span class="text-gray-500 font-normal" id="recall-lbl-0">0</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 bg-[#a85cf0]"></span> 3-6 Months
                        </div> <span class="text-gray-500 font-normal" id="recall-lbl-1">0</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 bg-[#42cbf5]"></span> 6-9 Months
                        </div> <span class="text-gray-500 font-normal" id="recall-lbl-2">0</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 bg-[#ff7b72]"></span> 9-12 Months
                        </div> <span class="text-gray-500 font-normal" id="recall-lbl-3">0</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 bg-[#ffd166]"></span> 12+ Months
                        </div> <span class="text-gray-500 font-normal" id="recall-lbl-4">0</span>
                    </div>
                </div>
            </div>

            <hr class="border-gray-100 mx-5 mt-4">

            <div class="p-5 grid grid-cols-2 divide-x divide-gray-100">
                <div>
                    <p class="text-xs font-bold text-gray-900 mb-1">Hygiene Recall Results</p>
                    <p class="text-2xl font-bold text-gray-900">0</p>
                    <p class="text-[10px] text-gray-400 uppercase">Rescheduled Patients</p>
                </div>
                <div class="pl-4">
                    <p class="text-xs font-bold text-white mb-1">_</p>
                    <p class="text-2xl font-bold text-gray-900">$ 0</p>
                    <p class="text-[10px] text-gray-400 uppercase">Rescheduled Production</p>
                </div>
            </div>
        </div>

        <!-- Unscheduled Treatment -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col overflow-hidden relative">
            <div class="h-1.5 w-full bg-blue-500 absolute top-0 left-0"></div>
            <div class="p-5 flex-1 mt-1">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-900 text-lg">Unscheduled Treatment</h3>
                    <button class="text-gray-400 hover:text-gray-600"><i class="fa-regular fa-circle-info"></i></button>
                </div>

                <div class="flex justify-center mb-6 h-48 relative">
                    <canvas id="unscheduledTxChart"></canvas>
                </div>

                <div class="space-y-2 text-xs font-semibold text-gray-700 w-full px-4">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 bg-[#5ce6a1]"></span> 0-3 Months
                        </div> <span class="text-gray-500 font-normal" id="tx-lbl-0">...</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 bg-[#a85cf0]"></span> 3-6 Months
                        </div> <span class="text-gray-500 font-normal" id="tx-lbl-1">...</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 bg-[#42cbf5]"></span> 6-9 Months
                        </div> <span class="text-gray-500 font-normal" id="tx-lbl-2">...</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 bg-[#ff7b72]"></span> 9-12 Months
                        </div> <span class="text-gray-500 font-normal" id="tx-lbl-3">...</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 bg-[#ffd166]"></span> 12+ Months
                        </div> <span class="text-gray-500 font-normal" id="tx-lbl-4">...</span>
                    </div>
                </div>
            </div>

            <hr class="border-gray-100 mx-5 mt-4">

            <div class="p-5 grid grid-cols-2 divide-x divide-gray-100">
                <div>
                    <p class="text-xs font-bold text-gray-900 mb-1">Unscheduled TX Results</p>
                    <p class="text-2xl font-bold text-gray-900">0</p>
                    <p class="text-[10px] text-gray-400 uppercase">Rescheduled Patients</p>
                </div>
                <div class="pl-4">
                    <p class="text-xs font-bold text-white mb-1">_</p>
                    <p class="text-2xl font-bold text-gray-900">$ 0</p>
                    <p class="text-[10px] text-gray-400 uppercase">Rescheduled Production</p>
                </div>
            </div>
        </div>

    </section>

    <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

        <div class="p-5 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <h2 class="text-lg font-bold text-gray-900">Broken Appointments</h2>
                <div class="flex bg-gray-100 p-0.5 rounded-lg text-xs font-semibold">
                    <button class="px-3 py-1.5 rounded-md hover:bg-white text-gray-600 transition-all">Top
                        20%</button>
                    <button class="px-3 py-1.5 rounded-md hover:bg-white text-gray-600 transition-all">Mid
                        Tier</button>
                    <button class="px-3 py-1.5 rounded-md bg-white text-red-600 shadow-sm">Bottom 20%</button>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="relative">
                    <input type="text" placeholder="Search..."
                        class="bg-gray-50 border border-gray-300 rounded-lg pl-3 pr-8 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 w-64">
                    <i class="fa-solid fa-magnifying-glass absolute right-3 top-2.5 text-gray-400 text-xs"></i>
                </div>
                <button
                    class="bg-white hover:bg-gray-50 text-gray-700 font-medium text-sm px-4 py-1.5 border border-gray-300 rounded-lg flex items-center gap-2 transition-colors">
                    <i class="fa-solid fa-file-csv text-emerald-600"></i> Export CSV
                </button>
            </div>
        </div>

        <div class="overflow-x-auto p-4 custom-table-scrollbar">
            <table id="brokenAppointmentsTable" class="w-full text-left border-collapse table-auto min-w-[1200px]">
                <thead>
                    <tr
                        class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="p-4 w-10"><input type="checkbox"
                                class="rounded text-emerald-600 focus:ring-emerald-500"></th>
                        <th class="p-4">Patient</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Amount</th>
                        <th class="p-4">Phone</th>
                        <th class="p-4">Insurance Carrier</th>
                        <th class="p-4">Provider</th>
                        <th class="p-4">Appt Date</th>
                        <th class="p-4">Appt Time</th>
                        <th class="p-4">Type</th>
                        <th class="p-4 max-w-[200px]">Appt Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm font-medium text-gray-700">
                </tbody>
            </table>
        </div>

    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function () {
        let brokenTable;

        // Initialization Scripts
        let hygieneChart, unscheduledChart;

        const foSchedChartConfigA = {
            type: 'doughnut',
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: { legend: { display: false }, tooltip: { enabled: true } },
                borderWidth: 0
            }
        };

        try {
            hygieneChart = new Chart(document.getElementById('hygieneRecallChart').getContext('2d'), {
                ...foSchedChartConfigA,
                data: {
                    labels: ['0-3 Months', '3-6 Months', '6-9 Months', '9-12 Months', '12+ Months'],
                    datasets: [{
                        data: [0, 0, 0, 0, 0],
                        backgroundColor: ['#5ce6a1', '#a85cf0', '#42cbf5', '#ff7b72', '#ffd166'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                }
            });

            unscheduledChart = new Chart(document.getElementById('unscheduledTxChart').getContext('2d'), {
                ...foSchedChartConfigA,
                data: {
                    labels: ['0-3 Months', '3-6 Months', '6-9 Months', '9-12 Months', '12+ Months'],
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

        function hydrateDashboard() {
            let currentMonth = $('#frontOfficeMonth').val();

            // 1. Fetch Stats API
            $.get("{{ route('front-office.stats') }}", { month_year: currentMonth }, function (data) {
                // Monthly Production Update
                $('#mo-actual').text('$' + data.monthly.actual.toLocaleString('en-US', { minimumFractionDigits: 2 }));
                $('#mo-prior').text('$' + data.monthly.prior_year.toLocaleString('en-US', { minimumFractionDigits: 2 }));

                let diffGoalHtml = data.monthly.diff_goal >= 0
                    ? `<span class="text-emerald-500"><i class="fa-solid fa-arrow-up"></i> $${data.monthly.diff_goal.toLocaleString('en-US', { minimumFractionDigits: 2 })} up vs goal</span>`
                    : `<span class="text-red-500"><i class="fa-solid fa-arrow-down"></i> $${Math.abs(data.monthly.diff_goal).toLocaleString('en-US', { minimumFractionDigits: 2 })} down vs goal</span>`;
                $('#mo-diff-goal').html(diffGoalHtml);

                let diffYearHtml = data.monthly.diff_year >= 0
                    ? `<span class="text-emerald-500"><i class="fa-solid fa-arrow-up"></i> $${data.monthly.diff_year.toLocaleString('en-US', { minimumFractionDigits: 2 })} up vs prior year</span>`
                    : `<span class="text-red-500"><i class="fa-solid fa-arrow-down"></i> $${Math.abs(data.monthly.diff_year).toLocaleString('en-US', { minimumFractionDigits: 2 })} down vs prior year</span>`;
                $('#mo-diff-year').html(diffYearHtml);

                $('#mo-progress-actual').text(`Net Prod ($${data.monthly.actual.toLocaleString('en-US', { minimumFractionDigits: 2 })})`);
                $('#mo-progress-goal').text(`Goal ($${data.monthly.goal.toLocaleString('en-US', { minimumFractionDigits: 2 })})`);
                $('#mo-progress-bar').css('width', `${Math.min(data.monthly.percent_goal, 100)}%`);

                // 2. Render Daily Production Div Chart
                let maxDaily = Math.max(...data.daily.actuals, ...data.daily.goals, 1);
                let dailyDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
                let dailyHtml = '';
                for (let i = 0; i < 5; i++) {
                    let hGoal = (data.daily.goals[i] / maxDaily) * 112; // 112px max height (~ h-28)
                    let hActual = (data.daily.actuals[i] / maxDaily) * 112;

                    dailyHtml += `
                                        <div class="w-full flex flex-col items-center gap-1 group relative">
                                            <div class="opacity-0 group-hover:opacity-100 absolute -top-8 bg-gray-900 text-white text-[10px] py-1 px-2 rounded whitespace-nowrap transition-opacity">
                                                A: $${data.daily.actuals[i].toLocaleString()}<br>G: $${data.daily.goals[i].toLocaleString()}
                                            </div>
                                            <div class="flex gap-0.5 items-end h-[112px]">
                                                <div class="w-3 bg-emerald-400 rounded-t transition-all duration-1000" style="height: ${hGoal}px"></div>
                                                <div class="w-3 bg-amber-400 rounded-t transition-all duration-1000" style="height: ${hActual}px"></div>
                                            </div>
                                            <span class="text-[10px] text-gray-400">${dailyDays[i]}</span>
                                        </div>`;
                }
                $('#daily-production-chart').html(dailyHtml);

                // 3. Render Visits Chart DIV
                let maxVisits = Math.max(...data.visits.new, ...data.visits.existing, 1);
                let visitsHtml = '';
                for (let i = 0; i < 5; i++) {
                    let hNew = (data.visits.new[i] / maxVisits) * 100;
                    let hExist = (data.visits.existing[i] / maxVisits) * 100;

                    visitsHtml += `
                                        <div class="w-full flex flex-col items-center group relative">
                                            <div class="opacity-0 group-hover:opacity-100 z-10 absolute -top-8 bg-gray-900 text-white text-[10px] py-1 px-2 rounded whitespace-nowrap transition-opacity">
                                                New: ${data.visits.new[i]} | Exist: ${data.visits.existing[i]}
                                            </div>
                                            <div class="flex gap-1 items-end h-[100px]">
                                                <div class="w-2.5 bg-emerald-300 rounded-t transition-all duration-1000" style="height: ${hNew}px"></div>
                                                <div class="w-2.5 bg-purple-600 rounded-t transition-all duration-1000" style="height: ${hExist}px"></div>
                                            </div>
                                            <span class="text-[10px] text-gray-400 mt-1">${dailyDays[i]}</span>
                                        </div>`;
                }
                $('#visits-chart').html(visitsHtml);
                // --- OPPORTUNITIES SECTION MAPPING ---

                // Schedule Opportunities
                let opp = data.opportunities;
                $('#opp-broken-total').text(opp.broken.total);
                if (opp.broken.total > 0) {
                    $('#opp-broken-bar-unscheduled').css('width', (opp.broken.unscheduled / opp.broken.total * 100) + '%').text(opp.broken.unscheduled);
                    $('#opp-broken-bar-scheduled').css('width', (opp.broken.scheduled / opp.broken.total * 100) + '%').text(opp.broken.scheduled > 0 ? opp.broken.scheduled : '');
                } else {
                    $('#opp-broken-bar-unscheduled').css('width', '100%').text('0');
                    $('#opp-broken-bar-scheduled').css('width', '0%').text('');
                }

                $('#opp-hyg-unsched').text(opp.hygiene.unscheduled);
                $('#opp-hyg-rate').text(opp.hygiene.rate);
                $('#opp-hyg-progress-val').text(opp.hygiene.unscheduled); // basic placeholder implementation matching UI

                // Hygiene Recall charts
                if (hygieneChart) {
                    hygieneChart.data.datasets[0].data = data.recall_due;
                    hygieneChart.update();
                }
                data.recall_due.forEach((val, i) => {
                    $(`#recall-lbl-${i}`).text(val);
                });

                // Unscheduled TX charts
                if (unscheduledChart) {
                    unscheduledChart.data.datasets[0].data = data.unscheduled_tx.count;
                    unscheduledChart.update();
                }
                data.unscheduled_tx.count.forEach((count, i) => {
                    let amt = data.unscheduled_tx.amount[i];
                    $(`#tx-lbl-${i}`).text(`$ ${amt.toLocaleString('en-US', { minimumFractionDigits: 2 })} (${count})`);
                });

            }).fail(function (error) {
                console.error('API Sync Error:', error);
            });

            // Update Active DataTables AJAX query
            if (brokenTable) {
                brokenTable.ajax.reload();
            }
        }

        // 4. Broken Appointments DataTables Bind
        brokenTable = DDS.dataTable(document.getElementById('brokenAppointmentsTable'), {
            processing: true,
            serverSide: true,
            pageLength: 10,
            layout: { topStart: null, topEnd: null, bottomStart: 'info', bottomEnd: 'paging' },
            language: {
                info: '<span class="flex items-center gap-2">Items per page _MENU_ <span class="text-gray-300 mx-1">|</span> _START_-_END_ of _TOTAL_ items</span>',
                paginate: {
                    previous: '<i class="fa-solid fa-chevron-left text-[10px]"></i>',
                    next: '<i class="fa-solid fa-chevron-right text-[10px]"></i>'
                }
            },
            drawCallback: function () {
                $('.dt-info').addClass('text-xs font-semibold text-gray-500 flex items-center');
                $('.dt-info select').addClass('border border-gray-300 rounded text-gray-700 py-1 px-2 text-xs focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none bg-white font-medium cursor-pointer');
                $('.dt-paging nav').addClass('flex items-center gap-1');
                $('.dt-paging').addClass('flex items-center');
                $('.dt-paging-button').addClass('px-2.5 py-1 text-xs font-bold border border-gray-200 text-gray-600 bg-white hover:bg-gray-50 rounded cursor-pointer transition-colors shadow-sm select-none');
                $('.dt-paging-button.current').removeClass('bg-white text-gray-600 hover:bg-gray-50 border-gray-200').addClass('bg-emerald-50 text-emerald-700 border-emerald-300 shadow-[inset_0_0_0_1px_rgba(16,185,129,0.2)]');
                $('.dt-paging-button.disabled').addClass('opacity-40 cursor-not-allowed hover:bg-white').removeClass('hover:bg-gray-50 cursor-pointer');
            },
            ajax: {
                url: "{{ route('front-office.broken-appointments') }}",
                data: function (d) {
                    d.month_year = $('#frontOfficeMonth').val();
                }
            },
            columns: [
                { data: null, orderable: false, searchable: false, render: () => '<input type="checkbox" class="rounded text-emerald-600 focus:ring-emerald-500">' },
                { data: 'patient_name', name: 'patient_name', className: 'font-semibold text-gray-900' },
                { data: 'status', name: 'status', render: data => `<span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs font-bold tracking-wide">${data}</span>` },
                { data: 'amount', name: 'amount', render: data => `<span class="text-amber-700 bg-amber-50/50 px-2 py-1 rounded font-semibold">$ ${Number(data).toFixed(2)}</span>` },
                { data: 'phone', name: 'phone', className: 'text-gray-600 font-normal' },
                { data: 'insurance', name: 'insurance', className: 'text-gray-500 font-normal' },
                { data: 'provider_name', name: 'provider_name' },
                { data: 'date', name: 'date', className: 'font-normal' },
                { data: 'time', name: 'time', className: 'font-normal uppercase text-xs' },
                { data: 'type', name: 'type', render: data => `<span class="text-xs font-semibold px-2 py-0.5 bg-red-50 text-red-600 rounded">${data}</span>` },
                { data: 'description', name: 'description', className: 'text-gray-500 font-normal max-w-xs truncate' }
            ],
            order: [[7, 'desc']]
        });

        // Initial Hydration
        hydrateDashboard();

        // Bind Update Actions
        $('#updateStatsBtn').click(function () {
            hydrateDashboard();
        });

        $('#frontOfficeMonth').on('change', function () {
            alert('hello');
            hydrateDashboard();
        });
    });
</script>