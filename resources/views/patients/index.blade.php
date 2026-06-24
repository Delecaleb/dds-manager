<x-app-layout>
    <div class="min-h-screen flex flex-col">
        
        <!-- SUB-HEADER AREA (Patient Portal context from image_dffd4d.png) -->
        <div class="bg-white border-b border-slate-200 px-8 pt-6 pb-0 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Patient Portal</h1>
                <button class="bg-[#001f3f] text-emerald-400 font-semibold text-xs px-4 py-2 rounded-full flex items-center gap-2 shadow-sm">
                    <i data-lucide="book-open" class="w-4 h-4"></i> Quick Start Guide
                </button>
            </div>

            <!-- Context Engine Controls -->
            <div class="flex items-center gap-3 mb-6">
                <div class="relative w-48">
                    <select class="w-full appearance-none bg-white border border-slate-300 rounded px-3 py-1.5 text-sm font-medium text-slate-700 focus:outline-none focus:border-emerald-500 cursor-pointer">
                        <option value="8mile">8 Mile</option>
                        <option value="downtown">Downtown Dental</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-500">
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </div>
                </div>
                <button class="border border-emerald-500 text-slate-800 text-sm font-semibold px-5 py-1.5 rounded bg-white hover:bg-slate-50 transition-colors">
                    Refresh
                </button>
            </div>

            <!-- Segment Navigation Tabs -->
            <div class="flex gap-6 border-b border-slate-200 text-sm font-medium">
                <a href="#" class="border-b-2 border-emerald-500 text-slate-900 pb-3 font-bold">Patients</a>
                <a href="#" class="text-slate-400 hover:text-slate-600 pb-3">Reminders</a>
                <a href="#" class="text-slate-400 hover:text-slate-600 pb-3">Performance</a>
            </div>
        </div>

        <!-- FILTER AND CONTROL TOOLBAR PANEL -->
        <div class="px-8 py-4 bg-[#f1f5f9] text-xs font-semibold text-emerald-600 border-b border-slate-200">
            <span class="cursor-pointer hover:underline">Additional Filters (0)</span>
        </div>

        <!-- RECONCILIATION FILTER BOX STRIP -->
        <div class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <button class="flex items-center gap-2 text-emerald-600 font-bold text-sm hover:opacity-80">
                    My Lists <i data-lucide="chevron-down" class="w-4 h-4"></i>
                </button>
                <button class="border border-emerald-500 text-slate-800 text-sm font-medium px-4 py-1.5 rounded bg-white flex items-center gap-1">
                    <span class="text-emerald-500 font-bold">+</span> Add Filter
                </button>
            </div>
            <div class="flex items-center gap-2">
                <button class="border border-red-500 text-red-500 text-sm font-medium px-4 py-1.5 rounded bg-white">New</button>
                <button class="bg-emerald-400 text-white text-sm font-medium px-4 py-1.5 rounded opacity-60 cursor-not-allowed">Save List</button>
            </div>
        </div>

        <!-- DATA CONTROL ROW (Actions Right Above Table Viewport) -->
        <div class="bg-white px-8 py-4 flex flex-wrap items-center justify-end gap-3">
            <button class="border border-emerald-500 text-slate-800 text-xs font-bold px-4 py-2 rounded bg-white">
                Create Reminders (3)
            </button>
            <button class="border border-emerald-500 text-slate-800 text-xs font-bold px-4 py-2 rounded bg-white">
                Reset
            </button>
            <div class="relative w-48">
                <input type="text" placeholder="Search" class="w-full pl-3 pr-8 py-2 border border-slate-300 rounded text-xs focus:outline-none focus:border-emerald-500">
                <span class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                </span>
            </div>
            <button class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold px-6 py-2 rounded transition-colors">
                Search
            </button>
            <button class="border border-slate-300 text-slate-700 text-xs font-bold px-4 py-2 rounded bg-white hover:bg-slate-50">
                Export CSV
            </button>
            <button class="border border-emerald-500 text-emerald-600 p-2 rounded bg-white">
                <i data-lucide="more-horizontal" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- MAIN TABLE VIEWER WORKSPACE -->
        <div class="flex-1 px-8 pb-8">
            <div class="bg-white border border-slate-200 rounded shadow-sm overflow-hidden flex flex-col">
                
                <!-- STICKY HORIZONTAL SCROLL CONTAINER -->
                <div class="overflow-x-auto custom-table-scrollbar relative">
                    <table class="w-full text-left border-collapse table-auto">
                        <thead>
                            <tr class="bg-slate-50 text-slate-700 font-bold text-xs border-b border-slate-200">
                                
                                <!-- FIXED AXIS: COMBINED CHECKBOX & PATIENT NAME COLUMN -->
                                <th class="p-3 bg-slate-50 sticky left-0 z-20 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)] border-r border-slate-200 min-w-[260px] max-w-[260px]">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" class="w-4 h-4 text-emerald-500 border-slate-300 rounded focus:ring-0">
                                        <span class="flex items-center gap-1 cursor-pointer select-none">
                                            <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Patient Name
                                        </span>
                                    </div>
                                </th>
                                
                                <!-- SCROLLABLE COLUMNS FROM IMAGE -->
                                <th class="p-3 min-w-[110px] text-xs font-bold"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Patient ID</span></th>
                                <th class="p-3 min-w-[160px] text-xs font-bold"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Guarantor</span></th>
                                <th class="p-3 min-w-[120px] text-xs font-bold"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Guarantor ID</span></th>
                                <th class="p-3 min-w-[80px] text-xs font-bold"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Age</span></th>
                                <th class="p-3 min-w-[100px] text-xs font-bold"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Gender</span></th>
                                <th class="p-3 min-w-[200px] text-xs font-bold"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Address</span></th>
                                <th class="p-3 min-w-[120px] text-xs font-bold"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> City</span></th>
                                <th class="p-3 min-w-[90px] text-xs font-bold"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> State</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700 bg-white">
                            
                            <!-- Patient Data Row 1 -->
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <!-- FIXED COLUMN SECTION -->
                                <td class="p-3 bg-white sticky left-0 group-hover:bg-slate-50/80 z-10 border-r border-slate-200 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)]">
                                    <div class="flex items-center justify-between w-full">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" checked class="w-4 h-4 accent-emerald-500 rounded focus:ring-0">
                                            <span class="font-medium text-slate-900">Akinbode, Erioluwa</span>
                                        </div>
                                        <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-slate-300 hover:text-blue-600 cursor-pointer"></i>
                                    </div>
                                </td>
                                <!-- MOBILE FIELDS -->
                                <td class="p-3 whitespace-nowrap">22057</td>
                                <td class="p-3 whitespace-nowrap">Akinbode, Erioluwa</td>
                                <td class="p-3 whitespace-nowrap">22057</td>
                                <td class="p-3 whitespace-nowrap">31</td>
                                <td class="p-3 whitespace-nowrap">Male</td>
                                <td class="p-3 whitespace-nowrap text-slate-500">633 n dexter dr</td>
                                <td class="p-3 whitespace-nowrap"></td>
                                <td class="p-3 whitespace-nowrap"></td>
                            </tr>

                            <!-- Patient Data Row 2 -->
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <!-- FIXED COLUMN SECTION -->
                                <td class="p-3 bg-white sticky left-0 group-hover:bg-slate-50/80 z-10 border-r border-slate-200 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)]">
                                    <div class="flex items-center justify-between w-full">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" checked class="w-4 h-4 accent-emerald-500 rounded focus:ring-0">
                                            <span class="font-medium text-slate-900">Ayers-Nelson, Riley</span>
                                        </div>
                                        <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-slate-300 hover:text-blue-600 cursor-pointer"></i>
                                    </div>
                                </td>
                                <td class="p-3 whitespace-nowrap">22048</td>
                                <td class="p-3 whitespace-nowrap">Ayers-Nelson, Riley</td>
                                <td class="p-3 whitespace-nowrap">22048</td>
                                <td class="p-3 whitespace-nowrap">9</td>
                                <td class="p-3 whitespace-nowrap">Female</td>
                                <td class="p-3 whitespace-nowrap text-slate-500">3946 fairview</td>
                                <td class="p-3 whitespace-nowrap">Detroit</td>
                                <td class="p-3 whitespace-nowrap">MI</td>
                            </tr>

                            <!-- Patient Data Row 3 -->
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <!-- FIXED COLUMN SECTION -->
                                <td class="p-3 bg-white sticky left-0 group-hover:bg-slate-50/80 z-10 border-r border-slate-200 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)]">
                                    <div class="flex items-center justify-between w-full">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" checked class="w-4 h-4 accent-emerald-500 rounded focus:ring-0">
                                            <span class="font-medium text-slate-900">Davis, Melissa</span>
                                        </div>
                                        <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-slate-300 hover:text-blue-600 cursor-pointer"></i>
                                    </div>
                                </td>
                                <td class="p-3 whitespace-nowrap">22016</td>
                                <td class="p-3 whitespace-nowrap">Davis, Melissa</td>
                                <td class="p-3 whitespace-nowrap">22016</td>
                                <td class="p-3 whitespace-nowrap">51</td>
                                <td class="p-3 whitespace-nowrap">Female</td>
                                <td class="p-3 whitespace-nowrap text-slate-500"></td>
                                <td class="p-3 whitespace-nowrap"></td>
                                <td class="p-3 whitespace-nowrap"></td>
                            </tr>

                            <!-- Patient Data Row 4 -->
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <!-- FIXED COLUMN SECTION -->
                                <td class="p-3 bg-white sticky left-0 group-hover:bg-slate-50/80 z-10 border-r border-slate-200 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)]">
                                    <div class="flex items-center justify-between w-full">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" class="w-4 h-4 text-emerald-500 border-slate-300 rounded focus:ring-0">
                                            <span class="font-medium text-slate-900">Dunwoodie-Sewald, Miya</span>
                                        </div>
                                        <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-slate-300 hover:text-blue-600 cursor-pointer"></i>
                                    </div>
                                </td>
                                <td class="p-3 whitespace-nowrap">22861</td>
                                <td class="p-3 whitespace-nowrap">Dunwoodie-Sewald, Miya</td>
                                <td class="p-3 whitespace-nowrap">22861</td>
                                <td class="p-3 whitespace-nowrap">12</td>
                                <td class="p-3 whitespace-nowrap">Other</td>
                                <td class="p-3 whitespace-nowrap text-slate-500"></td>
                                <td class="p-3 whitespace-nowrap"></td>
                                <td class="p-3 whitespace-nowrap"></td>
                            </tr>

                            <!-- Patient Data Row 5 -->
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <!-- FIXED COLUMN SECTION -->
                                <td class="p-3 bg-white sticky left-0 group-hover:bg-slate-50/80 z-10 border-r border-slate-200 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)]">
                                    <div class="flex items-center justify-between w-full">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" class="w-4 h-4 text-emerald-500 border-slate-300 rounded focus:ring-0">
                                            <span class="font-medium text-slate-900">Files, Shaterricka</span>
                                        </div>
                                        <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-slate-300 hover:text-blue-600 cursor-pointer"></i>
                                    </div>
                                </td>
                                <td class="p-3 whitespace-nowrap">22054</td>
                                <td class="p-3 whitespace-nowrap">Files, Shaterricka</td>
                                <td class="p-3 whitespace-nowrap">22054</td>
                                <td class="p-3 whitespace-nowrap">32</td>
                                <td class="p-3 whitespace-nowrap">Female</td>
                                <td class="p-3 whitespace-nowrap text-slate-500"></td>
                                <td class="p-3 whitespace-nowrap"></td>
                                <td class="p-3 whitespace-nowrap"></td>
                            </tr>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</x-app-layout>