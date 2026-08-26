    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between relative overflow-hidden">
            <!-- Skeleton Loader -->
            <div class="card-skeleton absolute inset-0 bg-white p-5 z-20 flex flex-col justify-between animate-pulse hidden">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-4 w-36 bg-gray-200 rounded"></div>
                        <div class="h-4 w-24 bg-gray-200 rounded"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <div class="h-3 w-12 bg-gray-200 rounded mb-2"></div>
                            <div class="h-6 w-28 bg-gray-200 rounded"></div>
                        </div>
                        <div>
                            <div class="h-3 w-20 bg-gray-200 rounded mb-2"></div>
                            <div class="h-6 w-16 bg-gray-200 rounded"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-100">
                        <div>
                            <div class="h-3 w-24 bg-gray-200 rounded mb-2"></div>
                            <div class="h-6 w-24 bg-gray-200 rounded"></div>
                            <div class="h-3 w-20 bg-gray-200 rounded mt-2"></div>
                        </div>
                        <div>
                            <div class="h-3 w-16 bg-gray-200 rounded mb-2"></div>
                            <div class="h-6 w-24 bg-gray-200 rounded"></div>
                            <div class="h-3 w-20 bg-gray-200 rounded mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-6">
                    <div class="flex justify-between mb-2">
                        <div class="h-3 w-24 bg-gray-200 rounded"></div>
                        <div class="h-3 w-20 bg-gray-200 rounded"></div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4"></div>
                </div>
            </div>
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

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between relative overflow-hidden">
            <!-- Skeleton Loader -->
            <div class="card-skeleton absolute inset-0 bg-white p-5 z-20 flex flex-col justify-between animate-pulse hidden">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-4 w-32 bg-gray-200 rounded"></div>
                        <div class="h-6 w-28 bg-gray-200 rounded"></div>
                    </div>
                    <div class="h-44 pt-6 flex items-end justify-between gap-3">
                        <div class="w-full bg-gray-200 rounded-t h-24"></div>
                        <div class="w-full bg-gray-200 rounded-t h-36"></div>
                        <div class="w-full bg-gray-200 rounded-t h-28"></div>
                        <div class="w-full bg-gray-200 rounded-t h-40"></div>
                        <div class="w-full bg-gray-200 rounded-t h-32"></div>
                    </div>
                </div>
                <div class="flex justify-center gap-4 mt-4 pt-2 border-t border-gray-50">
                    <div class="h-3 w-12 bg-gray-200 rounded"></div>
                    <div class="h-3 w-12 bg-gray-200 rounded"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-900">Daily Production</h3>
                    <div class="flex items-center gap-1 text-xs text-gray-600 font-medium bg-gray-50 border border-gray-200 px-2 py-1 rounded-lg">
                        <button type="button" class="fo-prev-week hover:text-emerald-600 p-0.5 transition-colors cursor-pointer" title="Previous Week">
                            <i class="fa-solid fa-chevron-left text-[10px]"></i>
                        </button>
                        <span class="fo-week-date-range text-[11px] font-semibold text-gray-700 min-w-[95px] text-center">Mon - Fri</span>
                        <button type="button" class="fo-next-week hover:text-emerald-600 p-0.5 transition-colors cursor-pointer" title="Next Week">
                            <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        </button>
                    </div>
                </div>
                <div class="h-44 relative w-full">
                    <canvas id="dailyProductionChartCanvas"></canvas>
                </div>
            </div>
            <div class="flex justify-center gap-4 text-xs font-semibold mt-4 pt-2 border-t border-gray-50">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-emerald-400 rounded-sm"></span>
                    Goal</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-amber-400 rounded-sm"></span>
                    Actual</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between relative overflow-hidden">
            <!-- Skeleton Loader -->
            <div class="card-skeleton absolute inset-0 bg-white p-5 z-20 flex flex-col justify-between animate-pulse hidden">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-4 w-20 bg-gray-200 rounded"></div>
                        <div class="h-6 w-28 bg-gray-200 rounded"></div>
                    </div>
                    <div class="h-44 pt-6 flex items-end justify-between gap-3">
                        <div class="w-full bg-gray-200 rounded-t h-28"></div>
                        <div class="w-full bg-gray-200 rounded-t h-20"></div>
                        <div class="w-full bg-gray-200 rounded-t h-36"></div>
                        <div class="w-full bg-gray-200 rounded-t h-32"></div>
                        <div class="w-full bg-gray-200 rounded-t h-24"></div>
                    </div>
                </div>
                <div class="flex justify-center gap-4 mt-4 pt-2 border-t border-gray-50">
                    <div class="h-3 w-20 bg-gray-200 rounded"></div>
                    <div class="h-3 w-24 bg-gray-200 rounded"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-900">Visits</h3>
                    <div class="flex items-center gap-1 text-xs text-gray-600 font-medium bg-gray-50 border border-gray-200 px-2 py-1 rounded-lg">
                        <button type="button" class="fo-prev-week hover:text-emerald-600 p-0.5 transition-colors cursor-pointer" title="Previous Week">
                            <i class="fa-solid fa-chevron-left text-[10px]"></i>
                        </button>
                        <span class="fo-week-date-range text-[11px] font-semibold text-gray-700 min-w-[95px] text-center">Mon - Fri</span>
                        <button type="button" class="fo-next-week hover:text-emerald-600 p-0.5 transition-colors cursor-pointer" title="Next Week">
                            <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        </button>
                    </div>
                </div>
                <div class="h-44 relative w-full">
                    <canvas id="visitsChartCanvas"></canvas>
                </div>
            </div>
            <div
                class="flex flex-wrap justify-center gap-3 text-[11px] font-semibold mt-4 pt-2 border-t border-gray-50">
                <span class="flex items-center gap-1"><span class="w-2 h-2 bg-emerald-400 rounded-sm"></span> New
                    Patients</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 bg-purple-500 rounded-sm"></span>
                    Existing Patients</span>
            </div>
        </div>

    </section>

    <!-- New Opportunities Section -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Schedule Opportunities -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col overflow-hidden relative">
            <!-- Skeleton Loader -->
            <div class="card-skeleton absolute inset-0 bg-white p-5 z-20 flex flex-col justify-between animate-pulse hidden">
                <div class="space-y-4">
                    <div class="h-5 w-44 bg-gray-200 rounded"></div>
                    <div class="h-8 w-16 bg-gray-200 rounded"></div>
                    <div class="h-5 w-full bg-gray-200 rounded"></div>
                    <div class="h-px w-full bg-gray-100 my-4"></div>
                    <div class="h-5 w-40 bg-gray-200 rounded"></div>
                    <div class="h-8 w-16 bg-gray-200 rounded"></div>
                    <div class="h-2 w-full bg-gray-200 rounded"></div>
                </div>
            </div>
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
            <!-- Skeleton Loader -->
            <div class="card-skeleton absolute inset-0 bg-white p-5 z-20 flex flex-col justify-between animate-pulse hidden">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-5 w-40 bg-gray-200 rounded"></div>
                        <div class="h-4 w-4 bg-gray-200 rounded-full"></div>
                    </div>
                    <div class="flex justify-center items-center h-48">
                        <div class="w-32 h-32 rounded-full border-8 border-gray-200"></div>
                    </div>
                    <div class="space-y-2 mt-4">
                        <div class="h-3 w-full bg-gray-200 rounded"></div>
                        <div class="h-3 w-full bg-gray-200 rounded"></div>
                        <div class="h-3 w-full bg-gray-200 rounded"></div>
                    </div>
                </div>
            </div>
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
            <!-- Skeleton Loader -->
            <div class="card-skeleton absolute inset-0 bg-white p-5 z-20 flex flex-col justify-between animate-pulse hidden">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-5 w-44 bg-gray-200 rounded"></div>
                        <div class="h-4 w-4 bg-gray-200 rounded-full"></div>
                    </div>
                    <div class="flex justify-center items-center h-48">
                        <div class="w-32 h-32 rounded-full border-8 border-gray-200"></div>
                    </div>
                    <div class="space-y-2 mt-4">
                        <div class="h-3 w-full bg-gray-200 rounded"></div>
                        <div class="h-3 w-full bg-gray-200 rounded"></div>
                        <div class="h-3 w-full bg-gray-200 rounded"></div>
                    </div>
                </div>
            </div>
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

    <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden p-5">

        <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-gray-200">
            <div class="flex items-center gap-2 overflow-x-auto">
                <button data-tab="broken"
                    class="schedule-subtab-btn px-4 py-2 text-sm font-semibold rounded-lg border border-emerald-500 bg-emerald-50 text-emerald-700 shadow-sm transition-all flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-calendar-xmark"></i> Broken Appointments
                </button>
                <button data-tab="hygiene-recall"
                    class="schedule-subtab-btn px-4 py-2 text-sm font-semibold rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-all flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-clock-rotate-left"></i> Hygiene Recall Due
                </button>
                <button data-tab="unscheduled-tx"
                    class="schedule-subtab-btn px-4 py-2 text-sm font-semibold rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-all flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-tooth"></i> Unscheduled Treatment
                </button>
                <button data-tab="hygiene-reappoint"
                    class="schedule-subtab-btn px-4 py-2 text-sm font-semibold rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-all flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-user-clock"></i> Hygiene Reappoint
                </button>
            </div>

            <div class="flex items-center gap-3">
                <div class="relative">
                    <input type="text" id="scheduleTableSearch" placeholder="Search..."
                        class="bg-gray-50 border border-gray-300 rounded-lg pl-3 pr-8 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 w-64">
                    <i class="fa-solid fa-magnifying-glass absolute right-3 top-2.5 text-gray-400 text-xs"></i>
                </div>
                <button id="exportScheduleCsvBtn" type="button"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs px-3.5 py-2 rounded-lg flex items-center gap-1.5 shadow-sm transition-colors cursor-pointer">
                    <i class="fa-solid fa-download"></i> Export CSV
                </button>
            </div>
        </div>

        <div id="scheduleSubtabPanels" class="mt-4">
            <!-- 1. Broken Appointments -->
            <div id="subtab-panel-broken" class="schedule-subtab-panel">
                <x-data-table id="brokenAppointmentsTable" min-width="1800px">
                    <x-slot:head>
                        <tr>
                            <th class="dt-col-sticky p-3">Patient</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Amount</th>
                            <th class="p-3">Phone</th>
                            <th class="p-3">Work Phone</th>
                            <th class="p-3">Mobile Phone</th>
                            <th class="p-3">Email</th>
                            <th class="p-3">Insurance Carrier</th>
                            <th class="p-3">Provider</th>
                            <th class="p-3">Next Visit Date</th>
                            <th class="p-3">Recall Due</th>
                            <th class="p-3">Remaining Benefits</th>
                            <th class="p-3">Appt Date</th>
                            <th class="p-3">Appt Time</th>
                            <th class="p-3">Type</th>
                            <th class="p-3">Appt Description</th>
                            <th class="p-3">Note</th>
                        </tr>
                    </x-slot:head>
                </x-data-table>
            </div>

            <!-- 2. Hygiene Recall Due -->
            <div id="subtab-panel-hygiene-recall" class="schedule-subtab-panel hidden">
                <x-data-table id="hygieneRecallTable" min-width="1600px">
                    <x-slot:head>
                        <tr>
                            <th class="dt-col-sticky p-3">Patient</th>
                            <th class="p-3">Age</th>
                            <th class="p-3">Phone</th>
                            <th class="p-3">Work Phone</th>
                            <th class="p-3">Mobile Phone</th>
                            <th class="p-3">Email</th>
                            <th class="p-3">Provider</th>
                            <th class="p-3">Next Visit Date</th>
                            <th class="p-3">Recall Due</th>
                            <th class="p-3">Last Recall Apt Date</th>
                            <th class="p-3">Remaining Benefits</th>
                            <th class="p-3">Appt Description</th>
                            <th class="p-3">Note</th>
                        </tr>
                    </x-slot:head>
                </x-data-table>
            </div>

            <!-- 3. Unscheduled Treatment -->
            <div id="subtab-panel-unscheduled-tx" class="schedule-subtab-panel hidden">
                <x-data-table id="unscheduledTxTable" min-width="1600px">
                    <x-slot:head>
                        <tr>
                            <th class="dt-col-sticky p-3">Patient</th>
                            <th class="p-3">Pend. Tx $ USC</th>
                            <th class="p-3">Remaining Benefits</th>
                            <th class="p-3">Age</th>
                            <th class="p-3">Phone</th>
                            <th class="p-3">Work Phone</th>
                            <th class="p-3">Mobile Phone</th>
                            <th class="p-3">Email</th>
                            <th class="p-3">Preferred Provider</th>
                            <th class="p-3">Next Visit Date</th>
                            <th class="p-3">Recall Due</th>
                            <th class="p-3">Tx Planned Date</th>
                            <th class="p-3">Tx Plan Created Date</th>
                        </tr>
                    </x-slot:head>
                </x-data-table>
            </div>

            <!-- 4. Hygiene Reappoint -->
            <div id="subtab-panel-hygiene-reappoint" class="schedule-subtab-panel hidden">
                <x-data-table id="hygieneReappointTable" min-width="1700px">
                    <x-slot:head>
                        <tr>
                            <th class="dt-col-sticky p-3">Patient</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Age</th>
                            <th class="p-3">Phone</th>
                            <th class="p-3">Work Phone</th>
                            <th class="p-3">Mobile Phone</th>
                            <th class="p-3">Email</th>
                            <th class="p-3">Insurance Carrier</th>
                            <th class="p-3">Provider</th>
                            <th class="p-3">Next Visit Date</th>
                            <th class="p-3">Recall Due</th>
                            <th class="p-3">Benefits Remaining</th>
                            <th class="p-3">Appt Date</th>
                            <th class="p-3">Appt Time</th>
                            <th class="p-3">Appt Description</th>
                        </tr>
                    </x-slot:head>
                </x-data-table>
            </div>
        </div>

    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function () {
        let hygieneChart, unscheduledChart, dailyProductionChartInstance, visitsChartInstance;
        let currentWeekStartDate = null;

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

        function shiftDateStr(dateStr, days) {
            let d = new Date(dateStr + 'T00:00:00');
            d.setDate(d.getDate() + days);
            let yr = d.getFullYear();
            let mo = String(d.getMonth() + 1).padStart(2, '0');
            let da = String(d.getDate()).padStart(2, '0');
            return `${yr}-${mo}-${da}`;
        }

        function hydrateDashboard(startDateOverride) {
            let params = window.getFoDateParams ? window.getFoDateParams() : { month_year: $('#frontOfficeMonth').val() };
            if (startDateOverride) {
                params.start_date = startDateOverride;
            } else if (currentWeekStartDate) {
                params.start_date = currentWeekStartDate;
            }

            $('.card-skeleton').removeClass('hidden');

            $.get("{{ route('front-office.stats') }}", params, function (data) {
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

                if (data.week_period) {
                    currentWeekStartDate = data.week_period.start_date;
                    $('.fo-week-date-range').text(data.week_period.formatted);
                }

                let dailyDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];

                // 1. Daily Production Chart
                if (dailyProductionChartInstance) {
                    dailyProductionChartInstance.data.labels = dailyDays;
                    dailyProductionChartInstance.data.datasets[0].data = data.daily.goals;
                    dailyProductionChartInstance.data.datasets[1].data = data.daily.actuals;
                    dailyProductionChartInstance.update();
                } else {
                    const ctxDP = document.getElementById('dailyProductionChartCanvas');
                    if (ctxDP) {
                        dailyProductionChartInstance = new Chart(ctxDP.getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: dailyDays,
                                datasets: [
                                    {
                                        label: 'Goal',
                                        data: data.daily.goals,
                                        backgroundColor: '#34d399',
                                        borderRadius: 4,
                                        barPercentage: 0.7,
                                        categoryPercentage: 0.6
                                    },
                                    {
                                        label: 'Actual',
                                        data: data.daily.actuals,
                                        backgroundColor: '#fbbf24',
                                        borderRadius: 4,
                                        barPercentage: 0.7,
                                        categoryPercentage: 0.6
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: function (context) {
                                                let label = context.dataset.label || '';
                                                if (label) label += ': ';
                                                if (context.parsed.y !== null) {
                                                    label += '$' + Number(context.parsed.y).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                                }
                                                return label;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: { display: false },
                                        ticks: { font: { size: 11 } }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        grid: { color: 'rgba(243, 244, 246, 1)' },
                                        ticks: {
                                            font: { size: 10 },
                                            callback: function (value) {
                                                if (value >= 1000) {
                                                    return '$' + (value / 1000).toLocaleString('en-US') + 'k';
                                                }
                                                return '$' + Number(value).toLocaleString('en-US');
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                }

                // 2. Visits Chart
                if (visitsChartInstance) {
                    visitsChartInstance.data.labels = dailyDays;
                    visitsChartInstance.data.datasets[0].data = data.visits.new;
                    visitsChartInstance.data.datasets[1].data = data.visits.existing;
                    visitsChartInstance.update();
                } else {
                    const ctxV = document.getElementById('visitsChartCanvas');
                    if (ctxV) {
                        visitsChartInstance = new Chart(ctxV.getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: dailyDays,
                                datasets: [
                                    {
                                        label: 'New Patients',
                                        data: data.visits.new,
                                        backgroundColor: '#34d399',
                                        borderRadius: 4,
                                        barPercentage: 0.7,
                                        categoryPercentage: 0.6
                                    },
                                    {
                                        label: 'Existing Patients',
                                        data: data.visits.existing,
                                        backgroundColor: '#a85cf0',
                                        borderRadius: 4,
                                        barPercentage: 0.7,
                                        categoryPercentage: 0.6
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: function (context) {
                                                let label = context.dataset.label || '';
                                                if (label) label += ': ';
                                                if (context.parsed.y !== null) {
                                                    label += '$' + Number(context.parsed.y).toLocaleString('en-US');
                                                }
                                                return label;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: { display: false },
                                        ticks: { font: { size: 11 } }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        grid: { color: 'rgba(243, 244, 246, 1)' },
                                        ticks: {
                                            font: { size: 10 },
                                            callback: function (value) {
                                                return '$' + Number(value).toLocaleString('en-US');
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                }

                // 4. Update Opportunities Cards
                $('#opp-broken-total').text(data.opportunities.broken.total);
                $('#opp-broken-bar-unscheduled').text(data.opportunities.broken.unscheduled).css('width', `${data.opportunities.broken.total > 0 ? (data.opportunities.broken.unscheduled / data.opportunities.broken.total) * 100 : 100}%`);
                $('#opp-broken-bar-scheduled').text(data.opportunities.broken.scheduled).css('width', `${data.opportunities.broken.total > 0 ? (data.opportunities.broken.scheduled / data.opportunities.broken.total) * 100 : 0}%`);

                $('#opp-hyg-unsched').text(data.opportunities.hygiene.unscheduled);
                $('#opp-hyg-rate').text(data.opportunities.hygiene.rate);
                $('#opp-hyg-progress-val').text(`${data.opportunities.hygiene.scheduled} / ${data.opportunities.hygiene.total}`);

                // 5. Update Charts
                if (hygieneChart) {
                    hygieneChart.data.datasets[0].data = data.recall_due;
                    hygieneChart.update();
                }

                data.recall_due.forEach((val, i) => {
                    $(`#recall-lbl-${i}`).text(val);
                });

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
            }).always(function () {
                $('.card-skeleton').addClass('hidden');
            });
        }

        $(document).on('click', '.fo-prev-week', function (e) {
            e.preventDefault();
            if (!currentWeekStartDate) return;
            let prevWeek = shiftDateStr(currentWeekStartDate, -7);
            hydrateDashboard(prevWeek);
        });

        $(document).on('click', '.fo-next-week', function (e) {
            e.preventDefault();
            if (!currentWeekStartDate) return;
            let nextWeek = shiftDateStr(currentWeekStartDate, 7);
            hydrateDashboard(nextWeek);
        });

        // 4. DataTables Configuration & Logic for each Subtab
        const dtInstances = {};
        let activeSubtab = 'broken';

        function initOrReloadSubtab(subtabKey) {
            if (dtInstances[subtabKey]) {
                dtInstances[subtabKey].ajax.reload();
                return;
            }

            const dtOptions = {
                processing: true,
                serverSide: true,
                pageLength: 10,
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
                drawCallback: function () {
                    $('.dt-length').addClass('text-xs font-semibold text-gray-500 flex items-center gap-1.5');
                    $('.dt-length select').addClass('border border-gray-300 rounded text-gray-700 py-1 px-2 text-xs focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none bg-white font-medium cursor-pointer');
                    $('.dt-info').addClass('text-xs font-semibold text-gray-500 flex items-center');
                    $('.dt-paging nav').addClass('flex items-center gap-1');
                    $('.dt-paging').addClass('flex items-center');
                    $('.dt-paging-button').addClass('px-2.5 py-1 text-xs font-bold border border-gray-200 text-gray-600 bg-white hover:bg-gray-50 rounded cursor-pointer transition-colors shadow-sm select-none');
                    $('.dt-paging-button.current').removeClass('bg-white text-gray-600 hover:bg-gray-50 border-gray-200').addClass('bg-emerald-50 text-emerald-700 border-emerald-300 shadow-[inset_0_0_0_1px_rgba(16,185,129,0.2)]');
                    $('.dt-paging-button.disabled').addClass('opacity-40 cursor-not-allowed hover:bg-white').removeClass('hover:bg-gray-50 cursor-pointer');
                }
            };

            const sendDateParams = d => {
                if (window.getFoDateParams) {
                    $.extend(d, window.getFoDateParams());
                } else {
                    d.month_year = $('#frontOfficeMonth').val();
                }
            };

            if (subtabKey === 'broken') {
                dtInstances['broken'] = DDS.dataTable(document.getElementById('brokenAppointmentsTable'), {
                    ...dtOptions,
                    ajax: {
                        url: "{{ route('front-office.broken-appointments') }}",
                        data: sendDateParams
                    },
                    columns: [
                        { data: 'patient_name', name: 'patient_name', className: 'dt-col-sticky font-semibold text-gray-900 bg-white' },
                        { data: 'status', name: 'status', render: data => `<span class="px-2 py-0.5 bg-red-50 text-red-600 rounded text-xs font-bold tracking-wide">${data}</span>` },
                        { data: 'amount', name: 'amount', render: data => `<span class="text-amber-700 font-semibold">$ ${Number(data).toFixed(2)}</span>` },
                        { data: 'phone', name: 'phone', className: 'text-gray-600' },
                        { data: 'work_phone', name: 'work_phone', className: 'text-gray-600' },
                        { data: 'mobile_phone', name: 'mobile_phone', className: 'text-gray-600' },
                        { data: 'email', name: 'email', className: 'text-gray-600' },
                        { data: 'insurance_carrier', name: 'insurance_carrier', className: 'text-gray-500' },
                        { data: 'provider_name', name: 'provider_name' },
                        { data: 'next_visit_date', name: 'next_visit_date' },
                        { data: 'recall_due', name: 'recall_due' },
                        { data: 'remaining_benefits', name: 'remaining_benefits' },
                        { data: 'date', name: 'date' },
                        { data: 'time', name: 'time', className: 'uppercase text-xs' },
                        { data: 'type', name: 'type', render: data => `<span class="text-xs font-semibold px-2 py-0.5 bg-gray-100 text-gray-600 rounded">${data}</span>` },
                        { data: 'description', name: 'description', className: 'text-gray-500 max-w-xs truncate' },
                        { data: 'note', name: 'note', className: 'text-gray-500 max-w-xs truncate' }
                    ],
                    order: [[12, 'desc']]
                });
            } else if (subtabKey === 'hygiene-recall') {
                dtInstances['hygiene-recall'] = DDS.dataTable(document.getElementById('hygieneRecallTable'), {
                    ...dtOptions,
                    ajax: {
                        url: "{{ route('front-office.hygiene-recall-due') }}",
                        data: sendDateParams
                    },
                    columns: [
                        { data: 'patient_name', name: 'patient_name', className: 'dt-col-sticky font-semibold text-gray-900 bg-white' },
                        { data: 'age', name: 'age' },
                        { data: 'phone', name: 'phone', className: 'text-gray-600' },
                        { data: 'work_phone', name: 'work_phone', className: 'text-gray-600' },
                        { data: 'mobile_phone', name: 'mobile_phone', className: 'text-gray-600' },
                        { data: 'email', name: 'email', className: 'text-gray-600' },
                        { data: 'provider_name', name: 'provider_name' },
                        { data: 'next_visit_date', name: 'next_visit_date' },
                        { data: 'recall_due', name: 'recall_due' },
                        { data: 'last_recall_apt_date', name: 'last_recall_apt_date' },
                        { data: 'remaining_benefits', name: 'remaining_benefits' },
                        { data: 'description', name: 'description', className: 'text-gray-500 max-w-xs truncate' },
                        { data: 'note', name: 'note', className: 'text-gray-500 max-w-xs truncate' }
                    ],
                    order: [[8, 'asc']]
                });
            } else if (subtabKey === 'unscheduled-tx') {
                dtInstances['unscheduled-tx'] = DDS.dataTable(document.getElementById('unscheduledTxTable'), {
                    ...dtOptions,
                    ajax: {
                        url: "{{ route('front-office.unscheduled-treatment') }}",
                        data: sendDateParams
                    },
                    columns: [
                        { data: 'patient_name', name: 'patient_name', className: 'dt-col-sticky font-semibold text-gray-900 bg-white' },
                        { data: 'amount', name: 'amount', render: data => `<span class="text-emerald-700 font-semibold">$ ${Number(data).toFixed(2)}</span>` },
                        { data: 'remaining_benefits', name: 'remaining_benefits' },
                        { data: 'age', name: 'age' },
                        { data: 'phone', name: 'phone', className: 'text-gray-600' },
                        { data: 'work_phone', name: 'work_phone', className: 'text-gray-600' },
                        { data: 'mobile_phone', name: 'mobile_phone', className: 'text-gray-600' },
                        { data: 'email', name: 'email', className: 'text-gray-600' },
                        { data: 'provider_name', name: 'provider_name' },
                        { data: 'next_visit_date', name: 'next_visit_date' },
                        { data: 'recall_due', name: 'recall_due' },
                        { data: 'date_tp', name: 'date_tp' },
                        { data: 'tx_plan_created_date', name: 'tx_plan_created_date' }
                    ],
                    order: [[11, 'desc']]
                });
            } else if (subtabKey === 'hygiene-reappoint') {
                dtInstances['hygiene-reappoint'] = DDS.dataTable(document.getElementById('hygieneReappointTable'), {
                    ...dtOptions,
                    ajax: {
                        url: "{{ route('front-office.hygiene-reappoint') }}",
                        data: sendDateParams
                    },
                    columns: [
                        { data: 'patient_name', name: 'patient_name', className: 'dt-col-sticky font-semibold text-gray-900 bg-white' },
                        { data: 'status', name: 'status', render: data => `<span class="px-2 py-0.5 bg-purple-50 text-purple-600 rounded text-xs font-bold tracking-wide">${data}</span>` },
                        { data: 'age', name: 'age' },
                        { data: 'phone', name: 'phone', className: 'text-gray-600' },
                        { data: 'work_phone', name: 'work_phone', className: 'text-gray-600' },
                        { data: 'mobile_phone', name: 'mobile_phone', className: 'text-gray-600' },
                        { data: 'email', name: 'email', className: 'text-gray-600' },
                        { data: 'insurance_carrier', name: 'insurance_carrier', className: 'text-gray-500' },
                        { data: 'provider_name', name: 'provider_name' },
                        { data: 'next_visit_date', name: 'next_visit_date' },
                        { data: 'recall_due', name: 'recall_due' },
                        { data: 'remaining_benefits', name: 'remaining_benefits' },
                        { data: 'date', name: 'date' },
                        { data: 'time', name: 'time', className: 'uppercase text-xs' },
                        { data: 'description', name: 'description', className: 'text-gray-500 max-w-xs truncate' }
                    ],
                    order: [[12, 'desc']]
                });
            }
        }

        function switchSubtab(subtabKey) {
            activeSubtab = subtabKey;

            $('.schedule-subtab-btn')
                .removeClass('border-emerald-500 bg-emerald-50 text-emerald-700 shadow-sm')
                .addClass('border-gray-200 text-gray-600 hover:bg-gray-50');
            $(`.schedule-subtab-btn[data-tab="${subtabKey}"]`)
                .removeClass('border-gray-200 text-gray-600 hover:bg-gray-50')
                .addClass('border-emerald-500 bg-emerald-50 text-emerald-700 shadow-sm');

            $('.schedule-subtab-panel').addClass('hidden');
            $(`#subtab-panel-${subtabKey}`).removeClass('hidden');

            initOrReloadSubtab(subtabKey);
        }

        $('.schedule-subtab-btn').on('click', function () {
            switchSubtab($(this).data('tab'));
        });

        $('#scheduleTableSearch').on('keyup', function () {
            if (dtInstances[activeSubtab]) {
                dtInstances[activeSubtab].search(this.value).draw();
            }
        });

        $('#exportScheduleCsvBtn').on('click', function () {
            let activeTableIdMap = {
                'broken': 'brokenAppointmentsTable',
                'hygiene-recall': 'hygieneRecallTable',
                'unscheduled-tx': 'unscheduledTxTable',
                'hygiene-reappoint': 'hygieneReappointTable'
            };
            let targetTableId = activeTableIdMap[activeSubtab] || 'brokenAppointmentsTable';
            exportTableToCSV($('#' + targetTableId), activeSubtab + '_export');
        });

        window.hydrateDashboard = hydrateDashboard;
        window.reloadFoTables = function () {
            if (dtInstances[activeSubtab]) {
                dtInstances[activeSubtab].ajax.reload();
            }
        };

        // Initial Load
        initOrReloadSubtab('broken');

        // Initial Hydration
        hydrateDashboard();
    });
</script>