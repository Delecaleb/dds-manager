<x-app-layout>
    <div class="min-h-screen flex flex-col relative">

        <div class="bg-white border-b border-slate-200 px-8 pt-6 pb-0 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Patient Portal</h1>
                <button
                    class="bg-[#001f3f] text-emerald-400 font-semibold text-xs px-4 py-2 rounded-full flex items-center gap-2 shadow-sm">
                    <i data-lucide="book-open" class="w-4 h-4"></i> Quick Start Guide
                </button>
            </div>

            <div class="flex items-center gap-3 mb-6">
                <div class="relative w-48">
                    <select
                        class="w-full appearance-none bg-white border border-slate-300 rounded px-3 py-1.5 text-sm font-medium text-slate-700 focus:outline-none focus:border-emerald-500 cursor-pointer">
                        <option value="8mile">8 Mile</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-500">
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </div>
                </div>
                <button id="refreshPatients"
                    class="border border-emerald-500 text-slate-800 text-sm font-semibold px-5 py-1.5 rounded bg-white hover:bg-slate-50 transition-colors">
                    Refresh
                </button>
            </div>

            <div class="flex gap-6 border-b border-slate-200 text-sm font-medium">
                <a href="#" class="border-b-2 border-emerald-500 text-slate-900 pb-3 font-bold">Patients</a>
                <a href="#" class="text-slate-400 hover:text-slate-600 pb-3">Reminders</a>
                <a href="#" class="text-slate-400 hover:text-slate-600 pb-3">Performance</a>
            </div>
        </div>

        <div class="px-8 py-4 bg-[#f1f5f9] text-xs font-semibold text-emerald-600 border-b border-slate-200">
            <span class="cursor-pointer hover:underline">Additional Filters (0)</span>
        </div>

        <div class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <button class="flex items-center gap-2 text-emerald-600 font-bold text-sm hover:opacity-80">
                    My Lists <i data-lucide="chevron-down" class="w-4 h-4"></i>
                </button>
                <button
                    class="border border-emerald-500 text-slate-800 text-sm font-medium px-4 py-1.5 rounded bg-white flex items-center gap-1">
                    <span class="text-emerald-500 font-bold">+</span> Add Filter
                </button>
            </div>

            <div class="flex items-center gap-2">
                <button
                    class="border border-red-500 text-red-500 text-sm font-medium px-4 py-1.5 rounded bg-white">New</button>
                <button
                    class="bg-emerald-400 text-white text-sm font-medium px-4 py-1.5 rounded opacity-60 cursor-not-allowed">Save
                    List</button>
            </div>
        </div>

        <div class="bg-white px-8 py-4 flex flex-wrap items-center justify-end gap-3">
            <button
                class="border border-emerald-500 text-slate-800 text-xs font-bold px-4 py-2 rounded bg-white hover:bg-slate-50">
                Create Reminders (3)
            </button>
            <button id="resetBtn"
                class="border border-emerald-500 text-slate-800 text-xs font-bold px-4 py-2 rounded bg-white hover:bg-slate-50">
                Reset
            </button>
            <div class="flex items-center gap-1 w-full md:w-auto">
                <input type="text" id="searchInput" placeholder="Search"
                    class="w-full pl-3 pr-8 py-2 border border-slate-300 rounded text-xs focus:outline-none focus:border-emerald-500">
                <button id="searchBtn"
                    class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold px-6 py-2 rounded transition-colors">
                    Search
                </button>
            </div>
            <button id="exportBtn"
                class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded transition-colors flex items-center gap-1 shadow-sm">
                <i data-lucide="download" class="w-3.5 h-3.5"></i> Export Excel
            </button>
            <div class="relative inline-block text-left ml-2">
                <button id="columnToggleBtn"
                    class="border border-emerald-500 text-emerald-600 p-2 rounded bg-white hover:bg-slate-50">
                    <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                </button>
                <div id="columnToggleMenu"
                    class="hidden absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-30 max-h-60 overflow-y-auto p-2">
                    <div class="space-y-1" id="columnCheckboxesContainer">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-1 px-8 pb-8">
            <div class="bg-white border border-slate-200 rounded shadow-sm overflow-hidden flex flex-col">

                <div class="overflow-x-auto custom-table-scrollbar relative">
                    <x-table-skeleton />
                    <table id="patientsTable" class="w-full text-left border-collapse table-auto">
                        <thead>
                            <tr class="bg-slate-50 text-slate-700 font-bold text-xs border-b border-slate-200">
                                <th
                                    class="p-3 bg-slate-50 sticky left-0 z-20 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)] border-r border-slate-200 min-w-[260px] max-w-[260px]">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox"
                                            class="w-4 h-4 text-emerald-500 border-slate-300 rounded focus:ring-0">
                                        <span class="flex items-center gap-1 cursor-pointer select-none">
                                            <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i>
                                            Patient Name
                                        </span>
                                    </div>
                                </th>
                                <th class="p-3 min-w-[100px] text-xs font-bold"><span
                                        class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down"
                                            class="w-3 h-3 text-slate-400"></i> Patient ID</span></th>
                                <th class="p-3 min-w-[120px] text-xs font-bold"><span
                                        class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down"
                                            class="w-3 h-3 text-slate-400"></i> Phone</span></th>
                                <th class="p-3 min-w-[160px] text-xs font-bold"><span
                                        class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down"
                                            class="w-3 h-3 text-slate-400"></i> Email</span></th>
                                <th class="p-3 min-w-[100px] text-xs font-bold"><span
                                        class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down"
                                            class="w-3 h-3 text-slate-400"></i> Birthdate</span></th>
                                <th class="p-3 min-w-[120px] text-xs font-bold"><span
                                        class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down"
                                            class="w-3 h-3 text-slate-400"></i> City</span></th>
                                <th class="p-3 min-w-[80px] text-xs font-bold"><span
                                        class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down"
                                            class="w-3 h-3 text-slate-400"></i> State</span></th>
                                <th class="p-3 min-w-[120px] text-xs font-bold"><span
                                        class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down"
                                            class="w-3 h-3 text-slate-400"></i> First Visit</span></th>
                                <th class="p-3 min-w-[120px] text-xs font-bold"><span
                                        class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down"
                                            class="w-3 h-3 text-slate-400"></i> Last Visit</span></th>
                                <th class="p-3 min-w-[150px] text-xs font-bold"><span
                                        class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down"
                                            class="w-3 h-3 text-slate-400"></i> Lifetime Prod</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700 bg-white">
                        </tbody>
                    </table>
                </div>

                <div id="custom-pagination-container"
                    class="p-4 bg-white border-t border-slate-100 flex items-center justify-between">
                </div>

            </div>
        </div>

        <div id="exportModal"
            class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center transition-opacity">
            <div
                class="bg-white rounded-lg border border-slate-200 shadow-xl w-full max-w-md overflow-hidden transform transition-transform scale-100 p-6">
                <div class="flex items-center gap-3 mb-4 text-slate-900">
                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                        <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold">Export Options</h3>
                </div>

                <div class="mb-5">
                    <label for="exportFileName" class="block text-xs font-semibold text-slate-600 mb-1.5">File
                        Name</label>
                    <div class="relative flex items-center">
                        <input type="text" id="exportFileName"
                            class="w-full pl-3 pr-16 py-2 border border-slate-300 rounded text-xs font-medium text-slate-800 focus:outline-none focus:border-emerald-500">
                        <span
                            class="absolute right-3 text-xs font-semibold text-slate-400 pointer-events-none">.xlsx</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">You can customize the export filename above before
                        downloading.</p>
                </div>

                <div class="flex items-center justify-end gap-2.5">
                    <button id="cancelExportBtn"
                        class="border border-slate-300 text-slate-700 text-xs font-bold px-4 py-2 rounded bg-white hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button id="confirmExportBtn"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-5 py-2 rounded transition-colors flex items-center gap-1 shadow-sm">
                        Continue Export
                    </button>
                </div>
            </div>
        </div>

        {{-- ========== PATIENT DETAIL MODAL ========== --}}
        <div id="patientModal"
            class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-start justify-center pt-10 pb-10 overflow-y-auto">
            <div class="bg-white rounded-xl border border-slate-200 shadow-2xl w-full max-w-4xl mx-4 flex flex-col">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-800">Patient Information</h3>
                    <button id="closePatientModal" class="text-slate-400 hover:text-slate-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </div>

                {{-- Patient Identity Row --}}
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div id="patientAvatar"
                            class="w-11 h-11 rounded-full bg-violet-500 flex items-center justify-center text-white text-sm font-bold shrink-0">
                            ??</div>
                        <span id="patientModalName" class="text-xl font-bold text-slate-900">—</span>
                    </div>
                    <button id="addReminderBtn"
                        class="border border-emerald-500 text-emerald-700 text-xs font-semibold px-4 py-1.5 rounded hover:bg-emerald-50 transition-colors">+
                        Add Reminder</button>
                </div>

                {{-- Tabs --}}
                <div class="px-6 border-b border-slate-100">
                    <nav class="flex gap-5" id="patientTabNav">
                        <button data-tab="pm-info"
                            class="pm-tab pb-3 text-sm font-semibold border-b-2 border-emerald-500 text-slate-900">Info</button>
                        <button data-tab="pm-family"
                            class="pm-tab pb-3 text-sm font-medium border-b-2 border-transparent text-slate-400 hover:text-slate-600">Family</button>
                        <button data-tab="pm-employer"
                            class="pm-tab pb-3 text-sm font-medium border-b-2 border-transparent text-slate-400 hover:text-slate-600">Employer</button>
                        <button data-tab="pm-ledger"
                            class="pm-tab pb-3 text-sm font-medium border-b-2 border-transparent text-slate-400 hover:text-slate-600">Ledger</button>
                        <button data-tab="pm-txplans"
                            class="pm-tab pb-3 text-sm font-medium border-b-2 border-transparent text-slate-400 hover:text-slate-600">TX
                            Plans</button>
                        <button data-tab="pm-ar"
                            class="pm-tab pb-3 text-sm font-medium border-b-2 border-transparent text-slate-400 hover:text-slate-600">AR
                            Summary</button>
                        <button data-tab="pm-notes"
                            class="pm-tab pb-3 text-sm font-medium border-b-2 border-transparent text-slate-400 hover:text-slate-600">Activities
                            and Notes</button>
                        <button data-tab="pm-reminder"
                            class="pm-tab pb-3 text-sm font-medium border-b-2 border-transparent text-slate-400 hover:text-slate-600">Reminder</button>
                    </nav>
                </div>

                {{-- Tab Content --}}
                <div class="px-6 py-5 flex-1 min-h-[380px]">

                    {{-- INFO TAB --}}
                    <div id="pm-info" class="pm-panel">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                            {{-- Left: Patient Info card --}}
                            <div class="border border-slate-200 rounded-lg p-4">
                                <p class="text-xs font-bold text-slate-700 mb-4">Patient Information</p>
                                <div class="grid grid-cols-2 gap-x-6 gap-y-4 text-xs mb-4">
                                    <div>
                                        <p class="text-slate-400 mb-0.5">Age</p>
                                        <p class="font-bold text-slate-800" id="pi-age">—</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-400 mb-0.5">Gender</p>
                                        <p class="font-bold text-slate-800" id="pi-gender">—</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-400 mb-0.5">Birthdate</p>
                                        <p class="font-bold text-slate-800" id="pi-birthdate">—</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-400 mb-0.5">Status</p>
                                        <p class="font-bold text-slate-800" id="pi-status">—</p>
                                    </div>
                                </div>
                                <div
                                    class="border-t border-slate-100 pt-4 grid grid-cols-2 gap-x-6 gap-y-4 text-xs mb-4">
                                    <div>
                                        <p class="text-slate-400 mb-0.5">Mobile Phone</p>
                                        <p class="font-bold text-slate-800" id="pi-mobile">—</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-400 mb-0.5">Work Phone</p>
                                        <p class="font-bold text-slate-800" id="pi-work">—</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-400 mb-0.5">Home Phone</p>
                                        <p class="font-bold text-slate-800" id="pi-home">—</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-400 mb-0.5">Email Address</p>
                                        <p class="font-bold text-slate-800 truncate" id="pi-email">—</p>
                                    </div>
                                </div>
                                {{-- Map placeholder --}}
                                <div class="border border-slate-200 rounded overflow-hidden h-36 bg-slate-100 mb-3">
                                    <iframe id="pi-map" class="w-full h-full" frameborder="0" style="border:0"
                                        allowfullscreen loading="lazy"
                                        src="https://www.google.com/maps/embed/v1/place?key=&q=United+States"
                                        referrerpolicy="no-referrer-when-downgrade">
                                    </iframe>
                                </div>
                                <div class="grid grid-cols-3 gap-3 text-xs">
                                    <div>
                                        <p class="text-slate-400 mb-0.5">Address</p>
                                        <p class="font-bold text-slate-800" id="pi-address">—</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-400 mb-0.5">City</p>
                                        <p class="font-bold text-slate-800" id="pi-city">—</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-400 mb-0.5">Zip</p>
                                        <p class="font-bold text-slate-800" id="pi-zip">—</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Right: Overview card --}}
                            <div class="border border-slate-200 rounded-lg p-4">
                                <p class="text-xs font-bold text-slate-700 mb-4">Overview</p>
                                <div class="grid grid-cols-2 gap-3 text-xs">
                                    {{-- Next Visit --}}
                                    <div class="border border-slate-100 rounded p-3">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-slate-400">Next Visit</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-300"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M7 17L17 7" />
                                                <path d="M7 7h10v10" />
                                            </svg>
                                        </div>
                                        <p class="font-bold text-slate-800" id="ov-next-date">—</p>
                                        <p class="text-slate-400" id="ov-next-label">N/A</p>
                                    </div>
                                    {{-- Last Visit --}}
                                    <div class="border border-slate-100 rounded p-3">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-slate-400">Last Visit</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-300"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M7 17L17 7" />
                                                <path d="M7 7h10v10" />
                                            </svg>
                                        </div>
                                        <p class="font-bold text-slate-800" id="ov-last-date">—</p>
                                        <p class="text-slate-400" id="ov-last-label">N/A</p>
                                    </div>
                                    {{-- Remaining Insurance --}}
                                    <div class="border border-slate-100 rounded p-3">
                                        <p class="text-slate-400 mb-1">Remaining Insurance</p>
                                        <p class="font-bold text-slate-800" id="ov-insurance">$ 0</p>
                                    </div>
                                    {{-- Treatment Plans --}}
                                    <div class="border border-slate-100 rounded p-3">
                                        <p class="text-slate-400 mb-1">Treatment Plans</p>
                                        <div class="flex gap-4">
                                            <div>
                                                <p class="font-bold text-slate-800" id="ov-tp-sched">$ 0</p>
                                                <p class="text-slate-400 text-[10px]">Scheduled</p>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-800" id="ov-tp-unsched">$ 0</p>
                                                <p class="text-slate-400 text-[10px]">Unsched</p>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Hygiene Due --}}
                                    <div class="border border-slate-100 rounded p-3">
                                        <p class="text-slate-400 mb-1">Hygiene Due</p>
                                        <p class="font-bold text-slate-800" id="ov-hygiene">—</p>
                                        <p class="text-slate-400 text-[10px]">Scheduled:</p>
                                    </div>
                                    {{-- Lifetime Value --}}
                                    <div class="border border-slate-100 rounded p-3">
                                        <p class="text-slate-400 mb-1">Lifetime Value</p>
                                        <p class="font-bold text-slate-800" id="ov-lifetime">$ 0</p>
                                    </div>
                                    {{-- Appointments --}}
                                    <div class="col-span-2 border border-slate-100 rounded p-3">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-slate-400">Appointments</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-300"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M7 17L17 7" />
                                                <path d="M7 7h10v10" />
                                            </svg>
                                        </div>
                                        <div class="grid grid-cols-3 text-center gap-2">
                                            <div>
                                                <p class="font-bold text-slate-800 text-base" id="ov-apt-completed-pct">
                                                    0.00%</p>
                                                <p class="text-slate-400 text-[10px]">Completed: <span
                                                        id="ov-apt-completed-cnt">0</span></p>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-800 text-base" id="ov-apt-sched-pct">
                                                    0.00%</p>
                                                <p class="text-slate-400 text-[10px]">Scheduled: <span
                                                        id="ov-apt-sched-cnt">0</span></p>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-800 text-base" id="ov-apt-broken-pct">
                                                    0.00%</p>
                                                <p class="text-slate-400 text-[10px]">Broken: <span
                                                        id="ov-apt-broken-cnt">0</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- FAMILY TAB --}}
                    <div id="pm-family" class="pm-panel hidden">
                        <div class="border border-slate-200 rounded-lg overflow-hidden">
                            <table class="w-full text-xs text-slate-700">
                                <thead class="bg-slate-50 border-b border-slate-200">
                                    <tr>
                                        <th class="p-3 text-left font-semibold">Name</th>
                                        <th class="p-3 text-left font-semibold">Status</th>
                                        <th class="p-3 text-left font-semibold">Gender</th>
                                        <th class="p-3 text-left font-semibold">Last Visit</th>
                                        <th class="p-3 text-left font-semibold">Next Visit</th>
                                        <th class="p-3 text-left font-semibold">Hygiene Due</th>
                                    </tr>
                                </thead>
                                <tbody id="pm-family-body">
                                    <tr>
                                        <td colspan="6" class="p-4 text-center text-slate-400">No Data</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- EMPLOYER TAB --}}
                    <div id="pm-employer" class="pm-panel hidden">
                        <div class="border border-slate-200 rounded-lg p-4 bg-white">
                            <p class="text-xs font-semibold text-slate-500 mb-1">Employer Name</p>
                            <p class="text-sm font-bold text-slate-800" id="pm-employer-name">No employer information
                                available.</p>
                        </div>
                    </div>

                    {{-- LEDGER TAB --}}
                    <div id="pm-ledger" class="pm-panel hidden">
                        <div class="border border-slate-200 rounded-lg overflow-hidden">
                            <table class="w-full text-xs text-slate-700">
                                <thead class="bg-slate-50 border-b border-slate-200">
                                    <tr>
                                        <th class="p-3 text-left font-semibold">Code</th>
                                        <th class="p-3 text-left font-semibold">Description</th>
                                        <th class="p-3 text-left font-semibold">Tooth</th>
                                        <th class="p-3 text-left font-semibold">Surface</th>
                                        <th class="p-3 text-left font-semibold">Amount</th>
                                        <th class="p-3 text-left font-semibold">Provider</th>
                                        <th class="p-3 text-left font-semibold">Date</th>
                                    </tr>
                                </thead>
                                <tbody id="pm-ledger-body">
                                    <tr>
                                        <td colspan="7" class="p-4 text-center text-slate-400">No Data</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TX PLANS TAB --}}
                    <div id="pm-txplans" class="pm-panel hidden">
                        <div class="border border-slate-200 rounded-lg overflow-hidden overflow-x-auto">
                            <table class="w-full text-xs text-slate-700 min-w-[780px]">
                                <thead class="bg-slate-50 border-b border-slate-200">
                                    <tr>
                                        <th class="p-3 text-left font-semibold">Code</th>
                                        <th class="p-3 text-left font-semibold">Description</th>
                                        <th class="p-3 text-left font-semibold">Tooth</th>
                                        <th class="p-3 text-left font-semibold">Surface</th>
                                        <th class="p-3 text-left font-semibold">Amount</th>
                                        <th class="p-3 text-left font-semibold">Provider</th>
                                        <th class="p-3 text-left font-semibold">Status</th>
                                        <th class="p-3 text-left font-semibold">Planned</th>
                                        <th class="p-3 text-left font-semibold">Scheduled</th>
                                        <th class="p-3 text-left font-semibold">Completed</th>
                                        <th class="p-3 text-left font-semibold">Date Created</th>
                                    </tr>
                                </thead>
                                <tbody id="pm-txplans-body">
                                    <tr>
                                        <td colspan="11" class="p-4 text-center text-slate-400">No Data</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- AR SUMMARY TAB --}}
                    <div id="pm-ar" class="pm-panel hidden">
                        <p class="text-xs font-bold text-slate-800 mb-4">Accounts Receivable Summary</p>
                        {{-- Summary stats --}}
                        <div class="border border-slate-200 rounded-lg p-4 mb-4">
                            <div class="grid grid-cols-7 gap-3 text-xs" id="card-grid">
                                <div>
                                    <p class="text-slate-400 mb-0.5">Total</p>
                                    <p class="font-bold text-slate-800" id="ar-total">$ 0</p>
                                </div>
                                <div>
                                    <p class="text-slate-400 mb-0.5">Insurance Claims</p>
                                    <p class="font-bold text-slate-800" id="ar-insurance">$ 0</p>
                                </div>
                                <div>
                                    <p class="text-slate-400 mb-0.5">Estimated Patients</p>
                                    <p class="font-bold text-slate-800" id="ar-estimated">$ 0</p>
                                </div>
                                <div>
                                    <p class="text-slate-400 mb-0.5">Current</p>
                                    <p class="font-bold text-slate-800" id="ar-current">$ 0</p>
                                </div>
                                <div>
                                    <p class="text-slate-400 mb-0.5">30+ Days</p>
                                    <p class="font-bold text-slate-800" id="ar-30">$ 0</p>
                                </div>
                                <div>
                                    <p class="text-slate-400 mb-0.5">60+ Days</p>
                                    <p class="font-bold text-slate-800" id="ar-60">$ 0</p>
                                </div>
                                <div>
                                    <p class="text-slate-400 mb-0.5">90+ Days</p>
                                    <p class="font-bold text-slate-800" id="ar-90">$ 0</p>
                                </div>
                            </div>
                        </div>
                        {{-- Transactions table --}}
                        <div class="border border-slate-200 rounded-lg overflow-hidden">
                            <table class="w-full text-xs text-slate-700">
                                <thead class="bg-slate-50 border-b border-slate-200">
                                    <tr>
                                        <th class="p-3 text-left font-semibold">Description</th>
                                        <th class="p-3 text-left font-semibold">Code</th>
                                        <th class="p-3 text-left font-semibold">Amount</th>
                                        <th class="p-3 text-left font-semibold">Provider</th>
                                        <th class="p-3 text-left font-semibold">Date</th>
                                    </tr>
                                </thead>
                                <tbody id="pm-ar-body">
                                    <tr>
                                        <td colspan="5" class="p-4 text-center text-slate-400">No Data</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ACTIVITIES AND NOTES TAB --}}
                    <div id="pm-notes" class="pm-panel hidden">
                        <div class="border border-slate-200 rounded-lg p-4 bg-white">
                            <p class="text-xs font-semibold text-slate-500 mb-1">Patient Notes</p>
                            <p class="text-sm text-slate-700 whitespace-pre-line" id="pm-patient-notes">No activities or
                                notes available.</p>
                        </div>
                    </div>

                    {{-- REMINDER TAB --}}
                    <div id="pm-reminder" class="pm-panel hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {{-- Left: Add a Reminder Form --}}
                            <div>
                                <p class="text-sm font-semibold text-slate-800 mb-4">Add a Reminder</p>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1.5">Date</label>
                                        <div
                                            class="relative inline-flex items-center border border-slate-300 rounded px-3 py-2 text-xs text-slate-700 bg-white gap-2 cursor-pointer">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                                <line x1="16" y1="2" x2="16" y2="6" />
                                                <line x1="8" y1="2" x2="8" y2="6" />
                                                <line x1="3" y1="10" x2="21" y2="10" />
                                            </svg>
                                            <input type="date" id="reminder-date"
                                                class="border-0 outline-none text-xs bg-transparent text-slate-700 cursor-pointer"
                                                value="{{ date('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs text-slate-500 mb-1.5">Type</label>
                                            <select id="reminder-type"
                                                class="w-full border border-slate-300 rounded px-3 py-2 text-xs text-slate-700 bg-white focus:outline-none focus:border-emerald-500 appearance-none">
                                                <option value="">Select option</option>
                                                <option>Call</option>
                                                <option>Email</option>
                                                <option>Text</option>
                                                <option>Follow Up</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-slate-500 mb-1.5">Estimate $</label>
                                            <input type="number" id="reminder-estimate" placeholder="$0.00" step="0.01"
                                                min="0"
                                                class="w-full border border-slate-300 rounded px-3 py-2 text-xs text-slate-700 focus:outline-none focus:border-emerald-500">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1.5">Assignee</label>
                                        <select id="reminder-assignee"
                                            class="w-full border border-slate-300 rounded px-3 py-2 text-xs text-slate-700 bg-white focus:outline-none focus:border-emerald-500 appearance-none">
                                            <option value="">Select user</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1.5">Notes</label>
                                        <textarea id="reminder-notes" rows="5"
                                            placeholder="Say something about this reminder"
                                            class="w-full border border-slate-300 rounded px-3 py-2 text-xs text-slate-700 focus:outline-none focus:border-emerald-500 resize-none"></textarea>
                                    </div>
                                    <div class="flex justify-end">
                                        <button id="submitReminderBtn"
                                            class="bg-emerald-400 hover:bg-emerald-500 text-white text-xs font-semibold px-6 py-2 rounded transition-colors">
                                            Add Reminder
                                        </button>
                                    </div>
                                </div>
                            </div>
                            {{-- Right: Activity --}}
                            <div>
                                <p class="text-sm font-semibold text-slate-800 mb-4">Activity</p>
                                <div class="bg-slate-100 rounded-lg flex items-center justify-center min-h-[200px]">
                                    <p class="text-xs text-slate-400">No Data Available</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <style>
        .dt-paging {
            display: flex;
            gap: 0.25rem;
        }

        .dt-paging .dt-paging-button {
            padding: 0.375rem 0.75rem !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            border-radius: 0.375rem !important;
            border: 1px solid #e2e8f0 !important;
            background: white !important;
            color: #334155 !important;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .dt-paging .dt-paging-button:hover:not(.disabled) {
            background-color: #f8fafc !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }

        .dt-paging .dt-paging-button.current {
            background-color: #10b981 !important;
            border-color: #10b981 !important;
            color: white !important;
        }

        .dt-paging .dt-paging-button.disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
    </style>

    <script>
        let table;
        let currentPatientId = null;

        $(document).ready(function () {
            table = $('#patientsTable').DataTable({
                processing: true,
                serverSide: true,
                paging: true,
                pageLength: 10,
                lengthChange: false,
                pagingType: 'simple_numbers',
                searching: true,
                info: false,
                layout: { topStart: null, topEnd: null, bottomStart: null, bottomEnd: 'paging' },
                ajax: {
                    url: "{{ route('patients.data') }}",
                    type: "GET",
                    beforeSend: function () { $("#tableSkeleton").removeClass('hidden'); },
                    complete: function () { $("#tableSkeleton").addClass('hidden'); }
                },
                columns: [
                    {
                        data: 'name',
                        render: function (data, type, row) {
                            return `
                        <div class="p-3 bg-white sticky left-0 group-hover:bg-slate-50/80 z-10 border-r border-slate-200 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)] flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" class="w-4 h-4 text-emerald-500 border-slate-300 rounded focus:ring-0">
                                <span class="font-medium">${data}</span>
                            </div>
                            <button onclick="openPatient(${row.id})" class="text-slate-400 hover:text-emerald-500 transition-colors p-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7"/><path d="M7 7h10v10"/></svg>
                            </button>
                        </div>
                        `;
                        }
                    },
                    { data: 'id' },
                    { data: 'phone' },
                    { data: 'email' },
                    { data: 'birthdate' },
                    { data: 'city' },
                    { data: 'state' },
                    { data: 'first_visit' },
                    { data: 'last_visit' },
                    {
                        data: 'lifetime_production',
                        render: function (data) { return '$' + Number(data).toLocaleString(); }
                    }
                ],
                order: [[0, 'asc']],
                drawCallback: function () {
                    lucide.createIcons();
                    $('#custom-pagination-container').append($('.dt-paging'));
                },
                initComplete: function () {
                    let container = $('#columnCheckboxesContainer');
                    table.columns().every(function (index) {
                        let title = $(this.header()).text().trim();
                        if (title === "") return;
                        let checked = this.visible() ? 'checked' : '';
                        container.append(`
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded text-xs text-slate-700 cursor-pointer select-none">
                            <input type="checkbox" data-column="${index}" ${checked} class="col-toggle-chk w-3.5 h-3.5 text-emerald-500 border-slate-300 rounded focus:ring-0">
                            <span>${title}</span>
                        </label>
                    `);
                    });
                }
            });

            // Column Visibility 
            $(document).on('change', '.col-toggle-chk', function () {
                let colIndex = $(this).data('column');
                table.column(colIndex).visible(!table.column(colIndex).visible());
            });

            $('#columnToggleBtn').on('click', function (e) {
                e.stopPropagation();
                $('#columnToggleMenu').toggleClass('hidden');
            });
            $(document).on('click', function () { $('#columnToggleMenu').addClass('hidden'); });
            $('#columnToggleMenu').on('click', function (e) { e.stopPropagation(); });

            // Custom search mechanism
            $("#searchBtn").on('click', function () {
                table.search($("#searchInput").val()).draw();
            });
            $("#searchInput").on('keypress', function (e) {
                if (e.which == 13) { $("#searchBtn").click(); }
            });

            // NEW: Reset Button Interaction
            $("#resetBtn").on('click', function () {
                $("#searchInput").val(""); // Clear input window text
                table.search("").draw(); // Clear search query matrix inside DataTables & hit remote server route afresh
            });

            // NEW: Export Trigger Modal Behavior
            $("#exportBtn").click(function () {
                // Set dynamic predefined export filename format: patient_export_YYYY-MM-DD
                let today = new Date().toISOString().split('T')[0];
                $("#exportFileName").val("patient_export_" + today);

                // Pop open modal
                $("#exportModal").removeClass('hidden');
            });

            // Close modal configurations
            $("#cancelExportBtn").click(function () {
                $("#exportModal").addClass('hidden');
            });

            // Action continuation configuration
            $("#confirmExportBtn").click(function () {
                let currentSearchValue = $("#searchInput").val();
                let customName = $("#exportFileName").val() || "patient_export";

                $("#exportModal").addClass('hidden'); // Hide popup interface immediately

                $.ajax({
                    url: '/patients/export',
                    method: 'POST',
                    data: {
                        _token: "{{csrf_token()}}",
                        search: currentSearchValue,
                        filename: customName // Pass requested target file name to your server backend controller framework
                    },
                    success: function (file) {
                        window.location = file.url;
                    }
                });
            });
        });

        // ---- Patient Modal Logic ----
        function openPatient(id) {
            currentPatientId = id;
            // Reset to Info tab
            activatePmTab('pm-info');

            // Show modal immediately with loading state
            $('#patientModal').removeClass('hidden');
            $('#patientModalName').text('Loading...');
            $('#patientAvatar').text('…');

            $.ajax({
                url: '/patients/' + id,
                type: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function (p) {
                    // ---- Header ----
                    const nameParts = p.name ? p.name.split(', ') : ['', ''];
                    const lastName = nameParts[0] || '';
                    const firstName = nameParts[1] || '';
                    const initials = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase() || '??';
                    $('#patientAvatar').text(initials);
                    $('#patientModalName').text(p.name || '—');

                    // ---- Info Tab ----
                    $('#pi-age').text(p.age || '—');
                    $('#pi-gender').text(p.gender || '—');
                    $('#pi-birthdate').text(p.birthdate || '—');
                    $('#pi-status').text(p.status || '—');
                    $('#pi-mobile').text(p.mobile_phone || 'N/A');
                    $('#pi-work').text(p.work_phone || 'N/A');
                    $('#pi-home').text(p.home_phone || 'N/A');
                    $('#pi-email').text(p.email || 'N/A');
                    $('#pi-address').text(p.address || '—');
                    $('#pi-city').text(p.city || '—');
                    $('#pi-zip').text(p.zip || '—');

                    // ---- Map ----
                    const mapQuery = encodeURIComponent(
                        [p.address, p.city, p.state, p.zip].filter(Boolean).join(', ')
                    );
                    $('#pi-map').attr('src',
                        'https://maps.google.com/maps?q=' + mapQuery + '&output=embed&z=14'
                    );

                    // ---- Overview ----
                    const ov = p.overview || {};
                    const nv = ov.next_visit || {};
                    const lv = ov.last_visit || {};
                    const tp = ov.treatment_plans || {};
                    const apts = ov.appointments || {};
                    const comp = apts.completed || {};
                    const sched = apts.scheduled || {};
                    const broken = apts.broken || {};

                    $('#ov-next-date').text(nv.date || '—');
                    $('#ov-next-label').text(nv.label || 'N/A');
                    $('#ov-last-date').text(lv.date || '—');
                    $('#ov-last-label').text(lv.label || 'N/A');
                    $('#ov-insurance').text('$ ' + Number(ov.remaining_insurance || 0).toLocaleString());
                    $('#ov-tp-sched').text('$ ' + Number(tp.scheduled || 0).toLocaleString());
                    $('#ov-tp-unsched').text('$ ' + Number(tp.unscheduled || 0).toLocaleString());
                    $('#ov-hygiene').text(ov.hygiene_due || '—');
                    $('#ov-lifetime').text('$ ' + Number(ov.lifetime_production || 0).toLocaleString());

                    $('#ov-apt-completed-pct').text((comp.percent || '0.00') + '%');
                    $('#ov-apt-completed-cnt').text(comp.count || 0);
                    $('#ov-apt-sched-pct').text((sched.percent || '0.00') + '%');
                    $('#ov-apt-sched-cnt').text(sched.count || 0);
                    $('#ov-apt-broken-pct').text((broken.percent || '0.00') + '%');
                    $('#ov-apt-broken-cnt').text(broken.count || 0);

                    // ---- Family Tab ----
                    const family = p.family || [];
                    if (family.length > 0) {
                        let rows = '';
                        family.forEach(function (m) {
                            rows += `<tr class="border-t border-slate-100">
                                <td class="p-3">${m.name || '—'}</td>
                                <td class="p-3">${m.status || '—'}</td>
                                <td class="p-3">${m.gender || '—'}</td>
                                <td class="p-3">${m.last_visit || '—'}</td>
                                <td class="p-3">${m.next_visit || '—'}</td>
                                <td class="p-3">${m.hygiene_due || '—'}</td>
                            </tr>`;
                        });
                        $('#pm-family-body').html(rows);
                    } else {
                        $('#pm-family-body').html('<tr><td colspan="6" class="p-4 text-center text-slate-400">No Data</td></tr>');
                    }

                    // ---- Employer Tab ----
                    $('#pm-employer-name').text(p.employer || 'No employer information available.');

                    // ---- Ledger Tab ----
                    const ledger = p.ledger || [];
                    if (ledger.length > 0) {
                        let rows = '';
                        ledger.forEach(function (itm) {
                            rows += `<tr class="border-t border-slate-100 hover:bg-slate-50/50">
                                <td class="p-3 font-medium">${itm.code || '—'}</td>
                                <td class="p-3 text-slate-500">${itm.description || '—'}</td>
                                <td class="p-3 text-center">${itm.tooth || '—'}</td>
                                <td class="p-3 text-center">${itm.surface || '—'}</td>
                                <td class="p-3 font-semibold ${itm.amount.startsWith('-') ? 'text-emerald-600' : 'text-slate-700'}">${itm.amount || '—'}</td>
                                <td class="p-3">${itm.provider || '—'}</td>
                                <td class="p-3 text-slate-500">${itm.date || '—'}</td>
                            </tr>`;
                        });
                        $('#pm-ledger-body').html(rows);
                    } else {
                        $('#pm-ledger-body').html('<tr><td colspan="7" class="p-4 text-center text-slate-400">No Data</td></tr>');
                    }

                    // ---- AR Summary Tab ----
                    const ar = p.ar || {};
                    $('#ar-total').text(ar.total || '$ 0.00');
                    $('#ar-insurance').text(ar.insurance || '$ 0.00');
                    $('#ar-estimated').text(ar.estimated || '$ 0.00');
                    $('#ar-current').text(ar.current || '$ 0.00');
                    $('#ar-30').text(ar.thirty || '$ 0.00');
                    $('#ar-60').text(ar.sixty || '$ 0.00');
                    $('#ar-90').text(ar.ninety || '$ 0.00');

                    const arTx = ar.transactions || [];
                    if (arTx.length > 0) {
                        let rows = '';
                        arTx.forEach(function (itm) {
                            rows += `<tr class="border-t border-slate-100 hover:bg-slate-50/50">
                                <td class="p-3 text-slate-500">${itm.description || '—'}</td>
                                <td class="p-3 font-medium">${itm.code || '—'}</td>
                                <td class="p-3 font-semibold">${itm.amount || '—'}</td>
                                <td class="p-3">${itm.provider || '—'}</td>
                                <td class="p-3 text-slate-500">${itm.date || '—'}</td>
                            </tr>`;
                        });
                        $('#pm-ar-body').html(rows);
                    } else {
                        $('#pm-ar-body').html('<tr><td colspan="5" class="p-4 text-center text-slate-400">No Data</td></tr>');
                    }

                    // ---- Activities & Notes Tab ----
                    $('#pm-patient-notes').text(p.notes || 'No activities or notes available.');
                },
                error: function () {
                    $('#patientModalName').text('Could not load patient');
                }
            });
        }

        function txSkeletonRows() {
            let skeleton = '';
            for (let i = 0; i < 5; i++) {
                skeleton += `<tr class="border-t border-slate-100">`;
                for (let j = 0; j < 11; j++) {
                    const w = j === 1 ? 'w-28' : 'w-14';
                    skeleton += `<td class="p-3"><div class="h-3 ${w} bg-slate-200 rounded animate-pulse"></div></td>`;
                }
                skeleton += `</tr>`;
            }
            return skeleton;
        }

        function loadPatientTXPlans(patientId) {
            $.ajax({
                url: '/patients/' + patientId + '/treatment-plans',
                type: 'GET',
                beforeSend: function () {
                    $('#pm-txplans-body').html(txSkeletonRows());
                },
                success: function (data) {
                    const txplans = data || [];
                    if (txplans.length > 0) {
                        let rows = '';
                        txplans.forEach(function (itm) {
                            let statusBadge = '';
                            if (itm.status === 'Completed') {
                                statusBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">Completed</span>';
                            } else if (itm.status === 'Scheduled') {
                                statusBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200">Scheduled</span>';
                            } else {
                                statusBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-50 text-slate-600 border border-slate-200">Unscheduled</span>';
                            }
                            rows += `<tr class="border-t border-slate-100 hover:bg-slate-50/50">
                                <td class="p-3 font-medium">${itm.code || '—'}</td>
                                <td class="p-3 text-slate-500">${itm.description || '—'}</td>
                                <td class="p-3 text-center">${itm.tooth || '—'}</td>
                                <td class="p-3 text-center">${itm.surface || '—'}</td>
                                <td class="p-3 font-semibold">${itm.amount || '—'}</td>
                                <td class="p-3">${itm.provider || '—'}</td>
                                <td class="p-3">${statusBadge}</td>
                                <td class="p-3 text-slate-500">${itm.date_planned || '—'}</td>
                                <td class="p-3 text-slate-500">${itm.date_scheduled || '—'}</td>
                                <td class="p-3 text-slate-500">${itm.date_completed || '—'}</td>
                                <td class="p-3 text-slate-500">${itm.date_created || '—'}</td>
                            </tr>`;
                        });
                        $('#pm-txplans-body').html(rows);
                    } else {
                        $('#pm-txplans-body').html('<tr><td colspan="11" class="p-4 text-center text-slate-400">No Data</td></tr>');
                    }
                },
                error: function () {
                    $('#pm-txplans-body').html('<tr><td colspan="11" class="p-4 text-center text-slate-400">Failed to load data</td></tr>');
                }
            });
        }

        function loadPatientAR(patientId) {
            $.ajax({
                url: '/patients/' + patientId + '/ar',
                type: 'GET',
                beforeSend: function () {
                    $('#pm-ar-body').html(txSkeletonRows());
                },
                success: function (data) {
                    const ar = data || [];
                    console.log(ar);
                    if (ar.length > 0) {
                        $('#ar-total').text(ar.total);
                        $('#ar-insurance').text(ar.insurance_claims);
                        $('#ar-estimated').text(ar.estimated_patient);
                        $('#ar-current').text(ar.current);
                        $('#ar-30-days').text(ar.thirty_days);
                        $('#ar-60-days').text(ar.sixty_days);
                        $('#ar-90-days').text(ar.ninety_days);
                        let rows = '';
                        ar.forEach(function (itm) {
                            let statusBadge = '';
                            if (itm.status === 'Completed') {
                                statusBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">Completed</span>';
                            } else if (itm.status === 'Scheduled') {
                                statusBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200">Scheduled</span>';
                            } else {
                                statusBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-50 text-slate-600 border border-slate-200">Unscheduled</span>';
                            }
                            rows += `<tr class="border-t border-slate-100 hover:bg-slate-50/50">
                                <td class="p-3 font-medium">${itm.code || '—'}</td>
                                <td class="p-3 text-slate-500">${itm.description || '—'}</td>
                                <td class="p-3 text-center">${itm.tooth || '—'}</td>
                                <td class="p-3 text-center">${itm.surface || '—'}</td>
                                <td class="p-3 font-semibold">${itm.amount || '—'}</td>
                                <td class="p-3">${itm.provider || '—'}</td>
                                <td class="p-3">${statusBadge}</td>
                                <td class="p-3 text-slate-500">${itm.date_planned || '—'}</td>
                                <td class="p-3 text-slate-500">${itm.date_scheduled || '—'}</td>
                                <td class="p-3 text-slate-500">${itm.date_completed || '—'}</td>
                                <td class="p-3 text-slate-500">${itm.date_created || '—'}</td>
                            </tr>`;
                        });
                        $('#pm-txplans-body').html(rows);
                    } else {
                        $('#pm-txplans-body').html('<tr><td colspan="11" class="p-4 text-center text-slate-400">No Data</td></tr>');
                    }
                },
                error: function () {
                    $('#pm-txplans-body').html('<tr><td colspan="11" class="p-4 text-center text-slate-400">Failed to load data</td></tr>');
                }
            });
        }

        function activatePmTab(tabId) {
            // Reset all tabs
            $('.pm-tab').removeClass('border-emerald-500 text-slate-900').addClass('border-transparent text-slate-400');
            // Activate clicked
            $('[data-tab="' + tabId + '"]').addClass('border-emerald-500 text-slate-900').removeClass('border-transparent text-slate-400');
            // Show/hide panels
            $('.pm-panel').addClass('hidden');
            $('#' + tabId).removeClass('hidden');
        }

        // Tab switching
        $('#patientTabNav').on('click', '.pm-tab', function () {
            const tab = $(this).data('tab');
            activatePmTab(tab);

            if (tab === 'pm-txplans' && currentPatientId) {
                loadPatientTXPlans(currentPatientId);
            }
            if (tab === 'pm-ar' && currentPatientId) {
                loadPatientAR(currentPatientId);
            }
        });

        // Close modal
        $('#closePatientModal').on('click', function () {
            $('#patientModal').addClass('hidden');
        });
        // Add Reminder button click handler inside modal
        $('#addReminderBtn').on('click', function () {
            activatePmTab('pm-reminder');
        });

        // Close on backdrop click
        $('#patientModal').on('click', function (e) {
            if (e.target === this) $('#patientModal').addClass('hidden');
        });

        $("#refreshPatients").click(function () { table.ajax.reload(); });
    </script>
</x-app-layout>