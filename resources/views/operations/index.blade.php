<x-app-layout>
    <div class="bg-[#f8fafc] text-[#475569] font-sans antialiased text-[13px]" onload="switchTab('offices')">
        <div class="p-6 space-y-5 max-w-[1600px] mx-auto w-full">

            <!-- TOP BAR (image_8f2ecb.png) -->
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold text-black tracking-tight">Operations</h1>
                <button class="bg-[#00bfa5] hover:bg-[#00a892] text-white font-bold px-4 py-2 rounded-full flex items-center gap-2 shadow-sm text-xs transition-colors">
                    <i data-lucide="book-open" class="w-4 h-4"></i>
                    <span>Quick Start Guide</span>
                </button>
            </div>

            <!-- CONTROL FILTERS BAR (image_8db30b.png) -->
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="flex items-center border border-slate-300 rounded px-3 py-1.5 bg-white shadow-sm min-w-[210px]">
                    <i data-lucide="calendar" class="w-4 h-4 text-slate-400 mr-2"></i>
                    <span class="text-slate-700 font-medium">Jun 01, 2026 - Jun 30, 2026</span>
                </div>
                <div class="relative min-w-[140px]">
                    <select class="w-full appearance-none bg-white border border-slate-300 rounded px-3 py-1.5 font-medium text-slate-700 pr-8 shadow-sm focus:outline-none">
                        <option>8 Mile</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-slate-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                </div>
                <div class="relative min-w-[160px]">
                    <select class="w-full appearance-none bg-white border border-slate-300 rounded px-3 py-1.5 font-medium text-slate-700 pr-8 shadow-sm focus:outline-none">
                        <option>Line of Business: All</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-slate-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                </div>
                <button class="bg-white border border-[#00bfa5] text-[#00bfa5] font-bold px-5 py-1.5 rounded shadow-sm text-xs">Refresh</button>
            </div>

            <!-- DYNAMIC WORKSPACE ROUTER TABS -->
            <div class="border-b border-slate-200 w-full flex flex-wrap gap-x-6 gap-y-2 text-slate-400 font-medium text-sm pt-2">
                <button onclick="switchTab('offices')" id="btn-offices" class="pb-2 border-b-2 border-transparent">Offices</button>
                <button onclick="switchTab('production')" id="btn-production" class="pb-2 border-b-2 border-transparent">Production Details</button>
                <button onclick="switchTab('payors')" id="btn-payors" class="pb-2 border-b-2 border-transparent">Payors</button>
                <button onclick="switchTab('performance')" id="btn-performance" class="pb-2 border-b-2 border-transparent">Performance</button>
                <button onclick="switchTab('providers')" id="btn-providers" class="pb-2 border-b-2 border-transparent">Providers</button>
                <button onclick="switchTab('services')" id="btn-services" class="pb-2 border-b-2 border-transparent">Services</button>
                <button onclick="switchTab('trends')" id="btn-trends" class="pb-2 border-b-2 border-transparent">Trends</button>
                <button onclick="switchTab('cancellations')" id="btn-cancellations" class="pb-2 border-b-2 border-transparent">Cancellations</button>
            </div>

            <!-- SHARED BASE CARD FRAME -->
            <div class="bg-white border border-slate-200 rounded shadow-sm p-4 space-y-4">
                
                <!-- COMMON LEGEND BAR FOR TABLES (Not visible on Services tab layout) -->
                <div id="common-table-controls" class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pt-1">
                    <div class="flex items-center gap-1.5 text-slate-700 font-medium text-[11px]">
                        <span class="bg-[#c8f7dc] text-[#1e4620] px-2.5 py-1 rounded font-bold">Top 20%</span>
                        <span class="bg-[#fef3c7] text-[#78350f] px-2.5 py-1 rounded font-bold">Mid Tier</span>
                        <span class="bg-[#fecdd3] text-[#9f1239] px-2.5 py-1 rounded font-bold">Bottom 20%</span>
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <div class="relative w-full sm:w-64">
                            <input type="text" placeholder="Search" class="w-full border border-slate-300 rounded px-3 py-1.5 text-xs pr-8 bg-white">
                            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute inset-y-0 right-0 my-auto mr-2.5"></i>
                        </div>
                        <button class="bg-white border border-[#00bfa5] text-[#00bfa5] font-bold px-4 py-1.5 rounded text-xs shrink-0">Export CSV</button>
                    </div>
                </div>

                <!-- ================= TAB: OFFICES (image_8f2ecb.png) ================= -->
                <div id="tab-content-offices" class="hidden-tab space-y-4">
                    <div class="overflow-x-auto border border-slate-200 rounded table-container">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="bg-[#e2e8f0] border-b border-slate-300"><th class="p-2 border-r border-slate-300 bg-[#cbd5e1] w-[140px]"></th><th colspan="9" class="p-2 font-bold pl-4 text-slate-700">By Office</th></tr>
                                <tr class="bg-[#e2e8f0] text-slate-600 text-xs font-semibold border-b border-slate-300">
                                    <th class="p-2 border-r border-slate-300">Location</th><th class="p-2 border-r border-slate-300 text-right">Gross Prod</th><th class="p-2 border-r border-slate-300 text-right">Adjustment</th><th class="p-2 border-r border-slate-300 text-right">Adjustment % of Prod</th><th class="p-2 border-r border-slate-300 text-right">Net Prod</th><th class="p-2 border-r border-slate-300 text-right">Collection</th><th class="p-2 border-r border-slate-300 text-right">Collection %</th><th class="p-2 border-r border-slate-300 text-right">Pts Visit</th><th class="p-2 border-r border-slate-300 text-right"># of Unique Pts</th><th class="p-2 text-right">Npt Visit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-[#c8f7dc] text-slate-800 border-b border-slate-200 font-medium">
                                    <td class="p-2.5 font-semibold border-r border-slate-200">8 Mile</td>
                                    <td class="p-2.5 text-right border-r border-slate-200">$ 32,565.00</td>
                                    <td class="p-2.5 text-right border-r border-slate-200">$ (3,335.85)</td>
                                    <td class="p-2.5 text-right border-r border-slate-200">-10.24%</td>
                                    <td class="p-2.5 text-right border-r border-slate-200">$ 29,229.15</td>
                                    <td class="p-2.5 text-right border-r border-slate-200">$ 49,215.78</td>
                                    <td class="p-2.5 text-right border-r border-slate-200">168.38%</td>
                                    <td class="p-2.5 text-right border-r border-slate-200">88</td>
                                    <td class="p-2.5 text-right border-r border-slate-200">85</td>
                                    <td class="p-2.5 text-right text-slate-400">—</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ================= TAB: PRODUCTION DETAILS (image_8dc5b5.png) ================= -->
                <div id="tab-content-production" class="hidden-tab space-y-4">
                    <div class="flex items-center gap-6 bg-slate-50 p-2.5 rounded border border-slate-200 text-xs font-medium">
                        <div class="flex items-center gap-2">
                            <div class="relative inline-block w-8 h-4 select-none transition duration-200 ease-in">
                                <input type="checkbox" id="toggleDate" class="toggle-checkbox absolute block w-4 h-4 rounded-full bg-white border-2 border-slate-300 appearance-none cursor-pointer z-10 checked:right-0 checked:border-[#00bfa5]"/>
                                <label for="toggleDate" class="toggle-label block overflow-hidden h-4 rounded-full bg-slate-300 cursor-pointer"><span class="toggle-dot block w-4 h-4 rounded-full bg-white shadow transform transition-transform duration-150"></span></label>
                            </div>
                            <span class="text-slate-700">Date</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="relative inline-block w-8 h-4 select-none transition duration-200 ease-in">
                                <input type="checkbox" id="toggleProvider" class="toggle-checkbox absolute block w-4 h-4 rounded-full bg-white border-2 border-slate-300 appearance-none cursor-pointer z-10"/>
                                <label for="toggleProvider" class="toggle-label block overflow-hidden h-4 rounded-full bg-slate-300 cursor-pointer"><span class="toggle-dot block w-4 h-4 rounded-full bg-white shadow transform transition-transform duration-150"></span></label>
                            </div>
                            <span class="text-slate-700">Provider</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto border border-slate-200 rounded table-container">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="bg-[#e2e8f0] border-b border-slate-300"><th class="p-2 border-r border-slate-300 bg-[#cbd5e1] w-[140px]"></th><th colspan="5" class="p-2 font-bold pl-4 border-r border-slate-300 text-slate-700">By Office</th><th colspan="4" class="p-2 font-bold pl-4 text-slate-700">Per Working Day</th></tr>
                                <tr class="bg-[#e2e8f0] text-slate-600 text-xs font-semibold border-b border-slate-300">
                                    <th class="p-2 border-r border-slate-300">Location</th><th class="p-2 border-r border-slate-300 text-right">Production</th><th class="p-2 border-r border-slate-300 text-right">Adjustment</th><th class="p-2 border-r border-slate-300 text-right">Collection</th><th class="p-2 border-r border-slate-300 text-right">Pts Visits</th><th class="p-2 border-r border-slate-300 text-right">New Pts Visit</th><th class="p-2 border-r border-slate-300 text-right">Production</th><th class="p-2 border-r border-slate-300 text-right">Collection</th><th class="p-2 border-r border-slate-300 text-right">Pts Visit</th><th class="p-2 text-right">Npt Visit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-[#c8f7dc] text-slate-800 font-medium">
                                    <td class="p-2.5 font-semibold border-r border-slate-200">8 Mile</td>
                                    <td class="p-2.5 text-right border-r border-slate-200">$ 29,229.15</td>
                                    <td class="p-2.5 text-right border-r border-slate-200">$ (3,335.85)</td>
                                    <td class="p-2.5 text-right border-r border-slate-200">$ 49,215.78</td>
                                    <td class="p-2.5 text-right border-r border-slate-200">88</td>
                                    <td class="p-2.5 text-right border-r border-slate-200">8</td>
                                    <td class="p-2.5 text-right border-r border-slate-200">$ 5,845.83</td>
                                    <td class="p-2.5 text-right border-r border-slate-200">$ 9,843.16</td>
                                    <td class="p-2.5 text-right border-r border-slate-200">18</td>
                                    <td class="p-2.5 text-right">2</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ================= TAB: PAYORS (image_8dc28c.png) ================= -->
                <div id="tab-content-payors" class="hidden-tab space-y-4">
                    <div class="overflow-x-auto border border-slate-200 rounded table-container">
                        <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                            <thead>
                                <tr class="bg-[#e2e8f0] border-b border-slate-300"><th colspan="2" class="border-r border-slate-300"></th><th colspan="7" class="p-2 font-bold pl-4 text-slate-700">By Payor</th></tr>
                                <tr class="bg-[#e2e8f0] text-slate-600 font-semibold border-b border-slate-300">
                                    <th class="p-2 border-r border-slate-300">Payor</th><th class="p-2 border-r border-slate-300">Location</th><th class="p-2 border-r border-slate-300 text-right">Gross Production</th><th class="p-2 border-r border-slate-300 text-right">Net Production</th><th class="p-2 border-r border-slate-300 text-right">% of TTL</th><th class="p-2 border-r border-slate-300 text-right">Adjustment</th><th class="p-2 border-r border-slate-300 text-right">Collection</th><th class="p-2 border-r border-slate-300 text-right">Pts Visits</th><th class="p-2 text-right">Npt Visit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-[#fecdd3] border-b border-slate-200"><td class="p-2 border-r border-slate-200 font-medium">Medicaid - 7</td><td class="p-2 border-r border-slate-200">8 Mile</td><td class="p-2 text-right border-r border-slate-200">$ 0</td><td class="p-2 text-right border-r border-slate-200">$ 0</td><td class="p-2 text-right border-r border-slate-200">0.00%</td><td class="p-2 text-right border-r border-slate-200 bg-[#fef3c7]">$ 0</td><td class="p-2 text-right border-r border-slate-200 bg-[#fef3c7]">$ 0</td><td class="p-2 text-right border-r border-slate-200 bg-[#fef3c7]">2</td><td class="p-2 text-right text-slate-400">—</td></tr>
                                <tr class="bg-[#fecdd3] border-b border-slate-200"><td class="p-2 border-r border-slate-200 font-medium">Dentaquest - 935</td><td class="p-2 border-r border-slate-200">8 Mile</td><td class="p-2 text-right border-r border-slate-200">$ 0</td><td class="p-2 text-right border-r border-slate-200">$ 0</td><td class="p-2 text-right border-r border-slate-200">0.00%</td><td class="p-2 text-right border-r border-slate-200 bg-[#fef3c7]">$ 0</td><td class="p-2 text-right border-r border-slate-200 bg-[#fef3c7]">$ 197.00</td><td class="p-2 text-right border-r border-slate-200 bg-[#fef3c7]">1</td><td class="p-2 text-right text-slate-400">—</td></tr>
                                <tr class="bg-[#fef3c7] border-b border-slate-200"><td class="p-2 border-r border-slate-200 font-medium">Delta Dental of MI - 1029</td><td class="p-2 border-r border-slate-200">8 Mile</td><td class="p-2 text-right border-r border-slate-200">$ 4,500.00</td><td class="p-2 text-right border-r border-slate-200">$ 3,600.00</td><td class="p-2 text-right border-r border-slate-200">12.32%</td><td class="p-2 text-right border-r border-slate-200 bg-[#fecdd3]">$ (900.00)</td><td class="p-2 text-right border-r border-slate-200">$ 3,600.00</td><td class="p-2 text-right border-r border-slate-200 font-bold">1</td><td class="p-2 text-right text-slate-400">—</td></tr>
                                <tr class="bg-[#c8f7dc] border-b border-slate-200"><td class="p-2 border-r border-slate-200 font-medium">No Insurance - 999999</td><td class="p-2 border-r border-slate-200">8 Mile</td><td class="p-2 text-right border-r border-slate-200">$ 28,065.00</td><td class="p-2 text-right border-r border-slate-200">$ 25,629.15</td><td class="p-2 text-right border-r border-slate-200 font-bold">87.68%</td><td class="p-2 text-right border-r border-slate-200 bg-[#fecdd3]">$ (2,435.85)</td><td class="p-2 text-right border-r border-slate-200">$ 45,418.78</td><td class="p-2 text-right border-r border-slate-200">84</td><td class="p-2 text-right text-slate-400">—</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ================= TAB: PERFORMANCE (image_8dc20c.png) ================= -->
                <div id="tab-content-performance" class="hidden-tab space-y-6">
                    <!-- Dropdown selector strip -->
                    <div class="relative max-w-[180px]">
                        <select class="w-full appearance-none bg-white border border-slate-300 rounded px-2 py-1 font-semibold text-slate-700 pr-8 shadow-sm">
                            <option>PROVIDERS 0 selected</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400"><i data-lucide="chevron-down" class="w-3.5 h-3.5"></i></div>
                    </div>

                    <!-- Core visual progress graphs grid -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="border border-slate-200 rounded p-3 bg-slate-50/50 space-y-2">
                            <div class="font-bold text-slate-800 text-sm">Production</div>
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs font-medium"><span>Actual: <b class="text-slate-900">$ 29,229.15</b></span></div>
                                <div class="w-full bg-slate-200 h-2.5 rounded-full overflow-hidden"><div class="bg-[#00bfa5] h-full" style="width: 22%"></div></div>
                                <div class="w-full bg-slate-200 h-2.5 rounded-full overflow-hidden relative"><div class="bg-purple-500 h-full" style="width: 100%"></div><span class="absolute inset-0 text-[9px] text-white flex items-center justify-center font-bold">Goal: $ 135,000.00</span></div>
                            </div>
                        </div>
                        <div class="border border-slate-200 rounded p-3 bg-slate-50/50 space-y-2">
                            <div class="font-bold text-slate-800 text-sm">Collection</div>
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs font-medium"><span>Actual: <b class="text-slate-900">$ 49,215.78</b></span></div>
                                <div class="w-full bg-slate-200 h-2.5 rounded-full overflow-hidden"><div class="bg-[#00bfa5] h-full" style="width: 36%"></div></div>
                                <div class="w-full bg-slate-200 h-2.5 rounded-full overflow-hidden relative"><div class="bg-purple-500 h-full" style="width: 100%"></div><span class="absolute inset-0 text-[9px] text-white flex items-center justify-center font-bold">Goal: $ 135,000.00</span></div>
                            </div>
                        </div>
                        <div class="border border-slate-200 rounded p-3 bg-slate-50/50 space-y-2">
                            <div class="font-bold text-slate-800 text-sm">Patient Visits</div>
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs font-medium"><span>Actual: <b class="text-slate-900">88</b></span></div>
                                <div class="w-full bg-slate-200 h-2.5 rounded-full overflow-hidden"><div class="bg-[#00bfa5] h-full" style="width: 44%"></div></div>
                                <div class="w-full bg-slate-200 h-2.5 rounded-full overflow-hidden relative"><div class="bg-purple-500 h-full" style="width: 100%"></div><span class="absolute inset-0 text-[9px] text-white flex items-center justify-center font-bold">Goal: 200</span></div>
                            </div>
                        </div>
                        <div class="border border-slate-200 rounded p-3 bg-slate-50/50 space-y-2">
                            <div class="font-bold text-slate-800 text-sm">New Patient Visits</div>
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs font-medium"><span>Actual: <b class="text-slate-900">8</b></span></div>
                                <div class="w-full bg-slate-200 h-2.5 rounded-full overflow-hidden"><div class="bg-[#00bfa5] h-full" style="width: 20%"></div></div>
                                <div class="w-full bg-slate-200 h-2.5 rounded-full overflow-hidden relative"><div class="bg-purple-500 h-full" style="width: 100%"></div><span class="absolute inset-0 text-[9px] text-white flex items-center justify-center font-bold">Goal: 40</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= TAB: PROVIDERS (image_8dbf08.png) ================= -->
                <div id="tab-content-providers" class="hidden-tab space-y-4">
                    <div class="overflow-x-auto border border-slate-200 rounded table-container">
                        <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                            <thead>
                                <tr class="bg-[#e2e8f0] border-b border-slate-300"><th colspan="3"></th><th colspan="10" class="p-2 font-bold pl-4 text-slate-700">By Provider</th></tr>
                                <tr class="bg-[#e2e8f0] text-slate-600 font-semibold border-b border-slate-300">
                                    <th class="p-2 border-r border-slate-300">Location</th><th class="p-2 border-r border-slate-300">Provider</th><th class="p-2 border-r border-slate-300">Provider ID</th><th class="p-2 border-r border-slate-300 text-right">Gross Production</th><th class="p-2 border-r border-slate-300 text-right">Net Production</th><th class="p-2 border-r border-slate-300 text-right">Adjustment</th><th class="p-2 border-r border-slate-300 text-right">Collection</th><th class="p-2 border-r border-slate-300 text-right">Pts Visits</th><th class="p-2 text-right">Npt Visits</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-slate-200">
                                    <td class="p-2 border-r border-slate-200">8 Mile</td>
                                    <td class="p-2 border-r border-slate-200 flex items-center gap-2"><span class="bg-red-100 text-red-700 text-[10px] px-1.5 py-0.5 rounded font-bold">Hygiene</span> Detroit Dental Care</td>
                                    <td class="p-2 border-r border-slate-200">46 - DETD</td>
                                    <td class="p-2 text-right border-r border-slate-200 bg-[#fecdd3]">$ 0</td>
                                    <td class="p-2 text-right border-r border-slate-200 bg-[#fef3c7]">$ 54.00</td>
                                    <td class="p-2 text-right border-r border-slate-200 bg-[#c8f7dc]">$ 54.00</td>
                                    <td class="p-2 text-right border-r border-slate-200 bg-[#fecdd3]">$ 0</td>
                                    <td class="p-2 text-right border-r border-slate-200 bg-[#fef3c7]">2</td>
                                    <td class="p-2 text-right bg-[#fecdd3]">0</td>
                                </tr>
                                <tr class="border-b border-slate-200">
                                    <td class="p-2 border-r border-slate-200">8 Mile</td>
                                    <td class="p-2 border-r border-slate-200 flex items-center gap-2"><span class="bg-purple-100 text-purple-700 text-[10px] px-1.5 py-0.5 rounded font-bold">Invisalign</span> Haddow, Mason</td>
                                    <td class="p-2 border-r border-slate-200">64 - HADD</td>
                                    <td class="p-2 text-right border-r border-slate-200 bg-[#fecdd3]">$ 0</td>
                                    <td class="p-2 text-right border-r border-slate-200 bg-[#fecdd3]">$ (810.00)</td>
                                    <td class="p-2 text-right border-r border-slate-200 bg-[#fecdd3]">$ (810.00)</td>
                                    <td class="p-2 text-right border-r border-slate-200 bg-[#fef3c7]">$ 4,320.00</td>
                                    <td class="p-2 text-right border-r border-slate-200 bg-[#fecdd3]">0</td>
                                    <td class="p-2 text-right bg-[#fecdd3]">0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ================= TAB: SERVICES (image_8dbe49.png) ================= -->
                <div id="tab-content-services" class="hidden-tab space-y-6">
                    <!-- Filters row -->
                    <div class="flex items-center gap-3">
                        <div class="relative min-w-[160px]">
                            <select class="w-full appearance-none bg-white border border-slate-300 rounded px-2 py-1 font-semibold text-slate-700 pr-8 shadow-sm text-xs"><option>Providers 0 selected</option></select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400"><i data-lucide="chevron-down" class="w-3.5 h-3.5"></i></div>
                        </div>
                        <div class="relative min-w-[140px]">
                            <select class="w-full appearance-none bg-white border border-slate-300 rounded px-2 py-1 font-semibold text-slate-700 pr-8 shadow-sm text-xs"><option>Payors 0 selected</option></select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400"><i data-lucide="chevron-down" class="w-3.5 h-3.5"></i></div>
                        </div>
                    </div>

                    <!-- Content Grid Mocking Infographics -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                        <!-- Left: Top 10 Services donut simulation -->
                        <div class="border border-slate-200 rounded p-4 space-y-4 bg-white">
                            <div class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-2">Top 10 Services | Count</div>
                            <div class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-slate-100 rounded bg-slate-50/50">
                                <div class="w-32 h-32 rounded-full border-8 border-teal-400 border-t-purple-400 border-r-amber-300 flex items-center justify-center font-bold text-slate-700 text-xs">1,251 TTL</div>
                                <div class="w-full mt-4 space-y-1.5 text-[11px]">
                                    <div class="flex justify-between items-center"><span class="flex items-center gap-2"><span class="w-2.5 h-2.5 bg-teal-400 inline-block rounded-sm"></span>periodic orthodontic visit</span><b>59</b></div>
                                    <div class="flex justify-between items-center"><span class="flex items-center gap-2"><span class="w-2.5 h-2.5 bg-purple-400 inline-block rounded-sm"></span>Broken Bracket</span><b>15</b></div>
                                    <div class="flex justify-between items-center"><span class="flex items-center gap-2"><span class="w-2.5 h-2.5 bg-amber-300 inline-block rounded-sm"></span>BOND LOWER</span><b>12</b></div>
                                </div>
                            </div>
                        </div>

                        <!-- Center: Target comparison lists -->
                        <div class="border border-slate-200 rounded p-4 space-y-4 bg-white lg:col-span-1">
                            <div class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-2">New Patient Visit vs Goal</div>
                            <div class="space-y-4 py-2">
                                <div class="space-y-1">
                                    <div class="text-xs font-bold text-slate-600 text-center">20.0% Month To Date</div>
                                    <div class="flex items-center gap-2"><span class="w-16 text-right font-semibold text-slate-500">New Visits</span><div class="flex-1 bg-slate-100 h-5 rounded overflow-hidden"><div class="bg-teal-400 h-full flex items-center pl-2 font-bold text-white text-[11px]" style="width: 20%">8</div></div></div>
                                    <div class="flex items-center gap-2"><span class="w-16 text-right font-semibold text-slate-500">Goal</span><div class="flex-1 bg-slate-100 h-5 rounded overflow-hidden"><div class="bg-purple-400 h-full flex items-center pl-2 font-bold text-white text-[11px]" style="width: 90%">40</div></div></div>
                                </div>
                                <div class="space-y-1 pt-2 border-t border-slate-100">
                                    <div class="text-xs font-bold text-slate-600 text-center">55.0% Year To Date</div>
                                    <div class="flex items-center gap-2"><span class="w-16 text-right font-semibold text-slate-500">New Visits</span><div class="flex-1 bg-slate-100 h-5 rounded overflow-hidden"><div class="bg-teal-400 h-full flex items-center pl-2 font-bold text-white text-[11px]" style="width: 55%">132</div></div></div>
                                    <div class="flex items-center gap-2"><span class="w-16 text-right font-semibold text-slate-500">Goal</span><div class="flex-1 bg-slate-100 h-5 rounded overflow-hidden"><div class="bg-purple-400 h-full flex items-center pl-2 font-bold text-white text-[11px]" style="width: 100%">240</div></div></div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Age demographics summary table -->
                        <div class="border border-slate-200 rounded p-4 space-y-2 bg-white">
                            <div class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-2">Age Brackets</div>
                            <table class="w-full text-[12px]">
                                <thead><tr class="bg-slate-100 text-slate-600 font-bold"><th class="p-1.5 text-left">Age</th><th class="p-1.5 text-right"># of active</th><th class="p-1.5 text-right">% of TTL</th></tr></thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr><td class="p-1.5 text-slate-700">0-9</td><td class="p-1.5 text-right font-semibold">46</td><td class="p-1.5 text-right text-slate-500">3.68%</td></tr>
                                    <tr><td class="p-1.5 text-slate-700">10-19</td><td class="p-1.5 text-right font-semibold">352</td><td class="p-1.5 text-right text-slate-500">28.14%</td></tr>
                                    <tr><td class="p-1.5 text-slate-700">20-29</td><td class="p-1.5 text-right font-semibold">228</td><td class="p-1.5 text-right text-slate-500">18.23%</td></tr>
                                    <tr class="bg-slate-50 font-bold"><td class="p-1.5 text-slate-900">Total:</td><td class="p-1.5 text-right text-slate-900">1,251</td><td class="p-1.5 text-right text-slate-900">100.00%</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ================= TAB: TRENDS (image_8dbae5.png) ================= -->
                <div id="tab-content-trends" class="hidden-tab space-y-4">
                    <div class="bg-sky-50 border border-sky-200 rounded p-3 text-sky-800 font-medium flex items-start gap-2.5">
                        <i data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5 text-sky-600"></i>
                        <p class="text-xs">BYO Production: The total production presented by office (BYO) based on a given date range. Please note, the provider-specific metrics ignore the Line of Business filter.</p>
                    </div>
                    <!-- Linear Chart Mock Area -->
                    <div class="border border-slate-200 rounded p-4 bg-white space-y-2">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 bg-teal-400 rounded-sm"></span><span class="font-bold text-slate-800 text-xs">8 Mile</span></div>
                        <div class="h-44 flex items-end justify-between px-4 pt-4 border-b border-l border-slate-200 bg-slate-50/50 relative">
                            <!-- Chart Line Trace Simulation -->
                            <div class="absolute inset-x-4 bottom-8 h-20 border-t border-dashed border-teal-300"></div>
                            <span class="text-[10px] text-slate-400 absolute left-2 top-2">175k</span>
                            <span class="text-[10px] text-slate-400 absolute left-2 top-16">100k</span>
                            
                            <!-- Mini Peak Points -->
                            <div class="flex flex-col items-center w-12"><div class="w-2 h-2 bg-teal-400 rounded-full mb-1"></div><span class="text-[9px] text-slate-400 font-mono">Jun 2025</span></div>
                            <div class="flex flex-col items-center w-12"><div class="w-2 h-2 bg-teal-400 rounded-full mb-14"></div><span class="text-[9px] text-slate-400 font-mono">Sep 2025</span></div>
                            <div class="flex flex-col items-center w-12"><div class="w-2 h-2 bg-teal-400 rounded-full mb-8"></div><span class="text-[9px] text-slate-400 font-mono">Dec 2025</span></div>
                            <div class="flex flex-col items-center w-12"><div class="w-2 h-2 bg-teal-400 rounded-full mb-24"></div><span class="text-[9px] text-slate-400 font-mono">Feb 2026</span></div>
                            <div class="flex flex-col items-center w-12"><div class="w-2 h-2 bg-teal-400 rounded-full mb-6"></div><span class="text-[9px] text-slate-400 font-mono">Jun 2026</span></div>
                        </div>
                    </div>
                </div>

                <!-- ================= TAB: CANCELLATIONS (image_8db30b.png) ================= -->
                <div id="tab-content-cancellations" class="hidden-tab space-y-4">
                    <div class="overflow-x-auto border border-slate-200 rounded table-container">
                        <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                            <thead>
                                <tr class="bg-[#e2e8f0] text-slate-600 font-semibold border-b border-slate-300">
                                    <th class="p-2 border-r border-slate-300">Location</th><th class="p-2 border-r border-slate-300 text-right">Cancellation</th><th class="p-2 border-r border-slate-300 text-right">Cancellation $</th><th class="p-2 border-r border-slate-300 text-right">Cancellation Rescheduled</th><th class="p-2 border-r border-slate-300 text-right">Cancellation Rescheduled $</th><th class="p-2 border-r border-slate-300 text-right">% Cancellation</th><th class="p-2 border-r border-slate-300 text-right">% Rescheduled</th><th class="p-2 text-right">Total Appointments Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-white border-b border-slate-200 font-medium">
                                    <td class="p-2.5 font-semibold border-r border-slate-200">8 Mile</td>
                                    <td class="p-2.5 text-right border-r border-slate-200 bg-[#fecdd3] text-red-900">115</td>
                                    <td class="p-2.5 text-right border-r border-slate-200 bg-[#fecdd3] text-red-900">$ 100.00</td>
                                    <td class="p-2.5 text-right border-r border-slate-200 bg-[#fecdd3] text-red-900">0</td>
                                    <td class="p-2.5 text-right border-r border-slate-200 bg-[#fecdd3] text-red-900">$ 0</td>
                                    <td class="p-2.5 text-right border-r border-slate-200 bg-[#fecdd3] text-red-900">36.74%</td>
                                    <td class="p-2.5 text-right border-r border-slate-200 bg-[#fecdd3] text-red-900">0.00%</td>
                                    <td class="p-2.5 text-right bg-[#c8f7dc] text-emerald-900">313</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>
    <!-- SPA VIEW SWITCHER ROUTER INLINE ENGINE -->
    <script>
        const tabsList = ['offices', 'production', 'payors', 'performance', 'providers', 'services', 'trends', 'cancellations'];

        function switchTab(targetId) {
            tabsList.forEach(id => {
                const contentBlock = document.getElementById(`tab-content-${id}`);
                const tabButton = document.getElementById(`btn-${id}`);
                
                if (id === targetId) {
                    contentBlock.classList.remove('hidden-tab');
                    tabButton.className = "pb-2 text-slate-900 font-bold border-b-3 border-[#00bfa5]";
                } else {
                    contentBlock.classList.add('hidden-tab');
                    tabButton.className = "pb-2 text-slate-400 font-medium border-b-2 border-transparent hover:text-slate-600 transition-colors";
                }
            });

            // Adjust search controls bar visibility contextually
            const commonControls = document.getElementById('common-table-controls');
            if (targetId === 'services' || targetId === 'performance') {
                commonControls.classList.add('hidden-tab');
            } else {
                commonControls.classList.remove('hidden-tab');
            }
        }
        
        lucide.createIcons();
    </script>
</x-app-layout>