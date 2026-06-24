<x-app-layout>
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50 px-6 py-4 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-6">
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Front Office</h1>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <select class="appearance-none bg-gray-100 border border-gray-300 rounded-lg pl-3 pr-8 py-1.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 text-gray-700">
                        <option>June 2026</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                        <i class="fa-solid :fa-chevron-down text-xs"></i>
                    </div>
                </div>
                <div class="relative">
                    <select class="appearance-none bg-gray-100 border border-gray-300 rounded-lg pl-3 pr-8 py-1.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 text-gray-700">
                        <option>8 Mile</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
                <button class="bg-white hover:bg-gray-50 text-emerald-600 border border-emerald-500 font-medium text-sm px-4 py-1.5 rounded-lg transition-colors">
                    Update
                </button>
            </div>
        </div>
        
        <button class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm px-4 py-2 rounded-lg flex items-center gap-2 shadow-sm transition-colors">
            <i class="fa-solid fa-book-open"></i> Quick Start Guide
        </button>
    </header>

    <nav class="bg-white border-b border-gray-200 px-6 flex gap-6 text-sm font-medium text-gray-500">
        <button class="border-b-2 border-emerald-500 text-emerald-600 py-3.5 px-1">Schedule</button>
        <button class="border-b-2 border-transparent hover:text-gray-700 py-3.5 px-1">Tasks</button>
        <button class="border-b-2 border-transparent hover:text-gray-700 py-3.5 px-1">Collections</button>
        <button class="border-b-2 border-transparent hover:text-gray-700 py-3.5 px-1">KPIs</button>
        <button class="border-b-2 border-transparent hover:text-gray-700 py-3.5 px-1">Performance</button>
    </nav>

    <main class="p-6 space-y-8 max-w-[1600px] mx-auto">

        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-bold text-gray-900">Monthly Production</h3>
                        <div class="text-xs font-semibold px-2 py-0.5 bg-gray-100 rounded text-gray-600">Line of Business: All</div>
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
                            <p class="text-xl font-bold text-emerald-600">$ 32,870.17</p>
                            <p class="text-xs text-red-500 font-medium mt-0.5"><i class="fa-solid fa-arrow-down"></i> $ 76,415.83 down vs goal</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Prior Year</p>
                            <p class="text-xl font-bold text-gray-900">$ 2,239.73</p>
                            <p class="text-xs text-emerald-600 font-medium mt-0.5"><i class="fa-solid fa-arrow-up"></i> $ 30,630.44 up vs prior year</p>
                        </div>
                    </div>
                </div>
                <div class="mt-6">
                    <div class="flex justify-between text-xs font-semibold mb-1">
                        <span class="text-indigo-600">Net Prod ($32,870.17)</span>
                        <span class="text-emerald-500">Goal ($109,286.00)</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-4 relative overflow-hidden">
                        <div class="bg-indigo-500 h-4 rounded-full" style="width: 30%"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-gray-900">Daily Production</h3>
                        <span class="text-xs text-gray-500">Jun 21 - Jun 27</span>
                    </div>
                    <div class="h-36 flex items-end justify-between gap-2 pt-4 px-2">
                        <div class="w-full flex flex-col items-center gap-1">
                            <div class="w-3 bg-emerald-400 h-28 rounded-t"></div>
                            <span class="text-[10px] text-gray-400">Mon</span>
                        </div>
                        <div class="w-full flex flex-col items-center gap-1">
                            <div class="flex gap-0.5 items-end">
                                <div class="w-3 bg-emerald-400 h-28 rounded-t"></div>
                                <div class="w-3 bg-amber-400 h-6 rounded-t"></div>
                            </div>
                            <span class="text-[10px] text-gray-400">Tue</span>
                        </div>
                        <div class="w-full flex flex-col items-center gap-1">
                            <div class="w-3 bg-emerald-400 h-28 rounded-t"></div>
                            <span class="text-[10px] text-gray-400">Wed</span>
                        </div>
                        <div class="w-full flex flex-col items-center gap-1">
                            <div class="w-3 bg-emerald-400 h-28 rounded-t"></div>
                            <span class="text-[10px] text-gray-400">Thu</span>
                        </div>
                        <div class="w-full flex flex-col items-center gap-1">
                            <div class="w-3 bg-emerald-400 h-28 rounded-t"></div>
                            <span class="text-[10px] text-gray-400">Fri</span>
                        </div>
                    </div>
                </div>
                <div class="flex justify-center gap-4 text-xs font-semibold mt-4 pt-2 border-t border-gray-50">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-emerald-400 rounded-sm"></span> Goal</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-amber-400 rounded-sm"></span> Actual</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-gray-900">Visits</h3>
                        <span class="text-xs text-gray-500">Jun 21 - Jun 27</span>
                    </div>
                    <div class="h-36 flex items-end justify-between gap-1 pt-4">
                        <div class="w-full flex flex-col items-center">
                            <div class="w-2 bg-purple-600 h-8 rounded-t"></div>
                            <span class="text-[10px] text-gray-400 mt-1">Mon</span>
                        </div>
                        <div class="w-full flex flex-col items-center">
                            <div class="flex gap-px items-end">
                                <div class="w-2 bg-emerald-300 h-6 rounded-t"></div>
                                <div class="w-2 bg-purple-600 h-24 rounded-t"></div>
                                <div class="w-2 bg-indigo-800 h-10 rounded-t"></div>
                            </div>
                            <span class="text-[10px] text-gray-400 mt-1">Tue</span>
                        </div>
                        <div class="w-full flex flex-col items-center">
                            <div class="w-2 bg-purple-600 h-12 rounded-t"></div>
                            <span class="text-[10px] text-gray-400 mt-1">Wed</span>
                        </div>
                        <div class="w-full flex flex-col items-center">
                            <div class="w-2 bg-purple-600 h-12 rounded-t"></div>
                            <span class="text-[10px] text-gray-400 mt-1">Thu</span>
                        </div>
                        <div class="w-full flex flex-col items-center">
                            <div class="flex gap-px items-end">
                                <div class="w-2 bg-emerald-300 h-4 rounded-t"></div>
                                <div class="w-2 bg-purple-600 h-12 rounded-t"></div>
                            </div>
                            <span class="text-[10px] text-gray-400 mt-1">Fri</span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap justify-center gap-3 text-[11px] font-semibold mt-4 pt-2 border-t border-gray-50">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 bg-emerald-300 rounded-sm"></span> New Patients</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 bg-purple-600 rounded-sm"></span> Existing Patients</span>
                </div>
            </div>

        </section>

        <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            
            <div class="p-5 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <h2 class="text-lg font-bold text-gray-900">Broken Appointments</h2>
                    <div class="flex bg-gray-100 p-0.5 rounded-lg text-xs font-semibold">
                        <button class="px-3 py-1.5 rounded-md hover:bg-white text-gray-600 transition-all">Top 20%</button>
                        <button class="px-3 py-1.5 rounded-md hover:bg-white text-gray-600 transition-all">Mid Tier</button>
                        <button class="px-3 py-1.5 rounded-md bg-white text-red-600 shadow-sm">Bottom 20%</button>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <input type="text" placeholder="Search..." class="bg-gray-50 border border-gray-300 rounded-lg pl-3 pr-8 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 w-64">
                        <i class="fa-solid fa-magnifying-glass absolute right-3 top-2.5 text-gray-400 text-xs"></i>
                    </div>
                    <button class="bg-white hover:bg-gray-50 text-gray-700 font-medium text-sm px-4 py-1.5 border border-gray-300 rounded-lg flex items-center gap-2 transition-colors">
                        <i class="fa-solid fa-file-csv text-emerald-600"></i> Export CSV
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[1200px]">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="p-4 w-10"><input type="checkbox" class="rounded text-emerald-600 focus:ring-emerald-500"></th>
                            <th class="p-4">Patient</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Amount</th>
                            <th class="p-4">Phone</th>
                            <th class="p-4">Insurance Carrier</th>
                            <th class="p-4">Provider</th>
                            <th class="p-4">Appt Date</th>
                            <th class="p-4">Appt Time</th>
                            <th class="p-4">Type</th>
                            <th class="p-4">Appt Description</th>
                            <th class="p-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm font-medium text-gray-700">
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="p-4"><input type="checkbox" class="rounded text-emerald-600 focus:ring-emerald-500"></td>
                            <td class="p-4 font-semibold text-gray-900">Elaine Williamson</td>
                            <td class="p-4"><span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs font-bold tracking-wide">UNSCHEDULED</span></td>
                            <td class="p-4 text-emerald-600 bg-emerald-50/50 font-semibold">$ 840.00</td>
                            <td class="p-4 text-gray-600 font-normal">(248) 259-4343</td>
                            <td class="p-4 text-gray-500 font-normal">No insurance</td>
                            <td class="p-4">Mason Haddow</td>
                            <td class="p-4 font-normal">Jun 19, 2026</td>
                            <td class="p-4 font-normal">02:00 pm</td>
                            <td class="p-4"><span class="text-xs font-semibold px-2 py-0.5 bg-red-50 text-red-600 rounded">Cancellation</span></td>
                            <td class="p-4 text-gray-500 font-normal max-w-xs truncate">replacement of lo...</td>
                            <td class="p-4 text-center">
                                <button class="text-gray-400 hover:text-gray-600 p-1"><i class="fa-solid fa-ellipsis"></i></button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="p-4"><input type="checkbox" class="rounded text-emerald-600 focus:ring-emerald-500"></td>
                            <td class="p-4 font-semibold text-gray-900">KEVIN WALLACE</td>
                            <td class="p-4"><span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs font-bold tracking-wide">UNSCHEDULED</span></td>
                            <td class="p-4 text-amber-700 bg-amber-50/50 font-semibold">$ 100.00</td>
                            <td class="p-4 text-gray-600 font-normal">(734) 201-2807</td>
                            <td class="p-4 text-gray-500 font-normal">No insurance</td>
                            <td class="p-4">Kathy Elias</td>
                            <td class="p-4 font-normal">Jun 16, 2026</td>
                            <td class="p-4 font-normal">10:20 am</td>
                            <td class="p-4"><span class="text-xs font-semibold px-2 py-0.5 bg-red-50 text-red-600 rounded">Cancellation</span></td>
                            <td class="p-4 text-gray-500 font-normal max-w-xs truncate">Broken Bracket</td>
                            <td class="p-4 text-center">
                                <button class="text-gray-400 hover:text-gray-600 p-1"><i class="fa-solid fa-ellipsis"></i></button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="p-4"><input type="checkbox" class="rounded text-emerald-600 focus:ring-emerald-500"></td>
                            <td class="p-4 font-semibold text-gray-900">Aaliyah Powell</td>
                            <td class="p-4"><span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs font-bold tracking-wide">UNSCHEDULED</span></td>
                            <td class="p-4 text-gray-400 font-normal bg-red-50/30">$ 0</td>
                            <td class="p-4 text-gray-600 font-normal">(248) 219-4711</td>
                            <td class="p-4 text-gray-500 font-normal">Delta Dental of MO</td>
                            <td class="p-4">Kathy Elias</td>
                            <td class="p-4 font-normal">Jun 02, 2026</td>
                            <td class="p-4 font-normal">12:00 pm</td>
                            <td class="p-4"><span class="text-xs font-semibold px-2 py-0.5 bg-red-50 text-red-600 rounded">Cancellation</span></td>
                            <td class="p-4 text-gray-500 font-normal max-w-xs truncate">periodic orthodon...</td>
                            <td class="p-4 text-center">
                                <button class="text-gray-400 hover:text-gray-600 p-1"><i class="fa-solid fa-ellipsis"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-200 bg-gray-50 flex flex-wrap items-center justify-between gap-4 text-xs font-medium text-gray-500">
                <div class="flex items-center gap-2">
                    <span>Items per page:</span>
                    <select class="bg-white border border-gray-300 rounded px-1.5 py-1 text-xs font-medium focus:outline-none">
                        <option>20</option>
                    </select>
                    <span class="ml-2">1-20 of 84 items</span>
                </div>
                <div class="flex items-center gap-1">
                    <select class="bg-white border border-gray-300 rounded px-1.5 py-1 text-xs font-medium focus:outline-none mr-2">
                        <option>1</option>
                    </select>
                    <span>of 5 pages</span>
                    <button class="p-1 px-2 border border-gray-300 rounded bg-white hover:bg-gray-50 text-gray-600 ml-2"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="p-1 px-2 border border-gray-300 rounded bg-white hover:bg-gray-50 text-gray-600/70"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>

        </section>

    </main>
</x-app-layout>