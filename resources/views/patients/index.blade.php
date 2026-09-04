<x-app-layout>
    <div class="min-h-screen flex flex-col relative bg-slate-50">

        <!-- Top Header Banner -->
        <div class="bg-white border-b border-slate-200 px-8 pt-6 pb-0 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Patient Portal</h1>
                <button
                    class="bg-[#001f3f] text-emerald-400 font-semibold text-xs px-4 py-2 rounded-full flex items-center gap-2 shadow-sm">
                    <i data-lucide="book-open" class="w-4 h-4"></i> Quick Start Guide
                </button>
            </div>

            <div class="flex items-center gap-3 mb-6">
                <div class="relative w-48">
                    <select id="globalClinicSelect"
                        class="w-full appearance-none bg-white border border-slate-300 rounded px-3 py-1.5 text-sm font-medium text-slate-700 focus:outline-none focus:border-emerald-500 cursor-pointer">
                        <option value="all">All Locations</option>
                        @if(isset($clinics) && count($clinics))
                            @foreach($clinics as $c)
                                <option value="{{ $c->id ?? $c->ClinicNum ?? $loop->index + 1 }}">{{ $c->name ?? $c->Description ?? 'Clinic '.$loop->iteration }}</option>
                            @endforeach
                        @else
                            <option value="1" selected>{{ \App\Models\Office::getActiveOffice()?->name ?? 'Main Office' }}</option>
                        @endif
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-500">
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </div>
                </div>
                <button id="refreshPatients"
                    class="border border-emerald-500 text-slate-800 text-sm font-semibold px-5 py-1.5 rounded bg-white hover:bg-slate-50 transition-colors cursor-pointer">
                    Refresh
                </button>
            </div>

            <!-- Portal Navigation Tabs -->
            <div class="flex gap-8 border-b border-slate-200 text-sm font-medium">
                <button type="button" id="tabPatientsBtn"
                    class="portal-tab-btn border-b-2 border-emerald-500 text-slate-900 pb-3 font-bold flex items-center gap-2 cursor-pointer transition-all">
                    <i data-lucide="users" class="w-4 h-4"></i> Patients
                </button>
                <button type="button" id="tabExportDataBtn"
                    class="portal-tab-btn border-b-2 border-transparent text-slate-500 hover:text-slate-800 pb-3 flex items-center gap-2 cursor-pointer transition-all">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i> Export Data
                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-1.5 py-0.5 rounded-full">New</span>
                </button>
                <button type="button" id="tabRemindersBtn"
                    class="portal-tab-btn border-b-2 border-transparent text-slate-400 hover:text-slate-600 pb-3 cursor-pointer">
                    Reminders
                </button>
                <button type="button" id="tabPerformanceBtn"
                    class="portal-tab-btn border-b-2 border-transparent text-slate-400 hover:text-slate-600 pb-3 cursor-pointer">
                    Performance
                </button>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: PATIENTS LIST CONTENT               -->
        <!-- ========================================== -->
        <div id="patientsListTabContent" class="flex flex-col flex-1">
            <div class="px-8 py-3 bg-[#f1f5f9] text-xs font-semibold text-emerald-600 border-b border-slate-200">
                <span class="cursor-pointer hover:underline">Additional Filters (0)</span>
            </div>

            <div class="bg-white border-b border-slate-200 px-8 py-3 flex items-center justify-between gap-4">
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
                        class="bg-emerald-400 text-white text-sm font-medium px-4 py-1.5 rounded opacity-60 cursor-not-allowed">Save List</button>
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
                    <input type="text" id="searchInput" placeholder="Search patients..."
                        class="w-full pl-3 pr-8 py-2 border border-slate-300 rounded text-xs focus:outline-none focus:border-emerald-500">
                    <button id="searchBtn"
                        class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold px-6 py-2 rounded transition-colors cursor-pointer">
                        Search
                    </button>
                </div>
                <button id="exportBtn"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded transition-colors flex items-center gap-1 shadow-sm cursor-pointer">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i> Export Excel
                </button>
                <div class="relative inline-block text-left ml-2">
                    <button id="columnToggleBtn"
                        class="border border-emerald-500 text-emerald-600 p-2 rounded bg-white hover:bg-slate-50 cursor-pointer">
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
                        <table id="patientsTable" class="dds-table w-full text-left border-collapse table-auto">
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
                                    <th class="p-3 min-w-[100px] text-xs font-bold whitespace-nowrap"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Patient ID</span></th>
                                    <th class="p-3 min-w-[150px] text-xs font-bold whitespace-nowrap"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Guarantor</span></th>
                                    <th class="p-3 min-w-[110px] text-xs font-bold whitespace-nowrap"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Guarantor ID</span></th>
                                    <th class="p-3 min-w-[70px] text-xs font-bold whitespace-nowrap"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Age</span></th>
                                    <th class="p-3 min-w-[90px] text-xs font-bold whitespace-nowrap"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Gender</span></th>
                                    <th class="p-3 min-w-[160px] text-xs font-bold whitespace-nowrap"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Address</span></th>
                                    <th class="p-3 min-w-[120px] text-xs font-bold whitespace-nowrap"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> City</span></th>
                                    <th class="p-3 min-w-[80px] text-xs font-bold whitespace-nowrap"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> State</span></th>
                                    <th class="p-3 min-w-[90px] text-xs font-bold whitespace-nowrap"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> ZIP</span></th>
                                    <th class="p-3 min-w-[120px] text-xs font-bold whitespace-nowrap"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Work Phone</span></th>
                                    <th class="p-3 min-w-[120px] text-xs font-bold whitespace-nowrap"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Home Phone</span></th>
                                    <th class="p-3 min-w-[120px] text-xs font-bold whitespace-nowrap"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Mobile Phone</span></th>
                                    <th class="p-3 min-w-[160px] text-xs font-bold whitespace-nowrap"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Email</span></th>
                                    <th class="p-3 min-w-[100px] text-xs font-bold whitespace-nowrap"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Birth Date</span></th>
                                    <th class="p-3 min-w-[120px] text-xs font-bold whitespace-nowrap"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> First Visit</span></th>
                                    <th class="p-3 min-w-[180px] text-xs font-bold whitespace-nowrap"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Lifetime Value Production</span></th>
                                    <th class="p-3 min-w-[180px] text-xs font-bold whitespace-nowrap"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Lifetime Value Collection</span></th>
                                    <th class="p-3 min-w-[140px] text-xs font-bold whitespace-nowrap"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Referral Source</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs text-slate-700 bg-white whitespace-nowrap">
                            </tbody>
                        </table>
                    </div>

                    <div id="custom-pagination-container"
                        class="p-4 bg-white border-t border-slate-100 flex items-center justify-between">
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: EXPORT DATA STUDIO CONTENT (NEW)    -->
        <!-- ========================================== -->
        <div id="exportDataTabContent" class="hidden flex flex-col flex-1 px-8 py-6 space-y-6 max-w-[1600px] w-full mx-auto">

            <!-- Section 1: Filter Parameters -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 bg-emerald-50 text-emerald-600 rounded-md">
                            <i data-lucide="filter" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">1. Filter Parameters</h2>
                            <p class="text-xs text-slate-500">Filter patients based on the date added to Open Dental, clinic location, status, and keywords</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" id="expResetFiltersBtn"
                            class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 border border-slate-300 hover:bg-slate-50 rounded-md transition-colors cursor-pointer flex items-center gap-1.5">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Reset
                        </button>
                        <button type="button" id="expApplyFiltersBtn"
                            class="px-4 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-md shadow-sm transition-colors cursor-pointer flex items-center gap-1.5">
                            <i data-lucide="search" class="w-3.5 h-3.5"></i> Apply Filters
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Filter 1: Date Added Mode -->
                    <div>
                        <label for="expDateMode" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Date Added to Open Dental
                        </label>
                        <select id="expDateMode"
                            class="w-full bg-white border border-slate-300 rounded-md px-3 py-2 text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 cursor-pointer">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="last_7_days">Last 7 Days</option>
                            <option value="last_30_days">Last 30 Days</option>
                            <option value="this_month" selected>This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_year">Year to Date (YTD)</option>
                            <option value="custom">Custom Date Range</option>
                        </select>
                    </div>

                    <!-- Filter 2: Custom Date Range (From / To) -->
                    <div id="expCustomDateContainer" class="hidden col-span-1 lg:col-span-1 grid grid-cols-2 gap-2">
                        <div>
                            <label for="expDateFrom" class="block text-xs font-bold text-slate-700 mb-1.5">From</label>
                            <input type="date" id="expDateFrom" value="{{ date('Y-m-01') }}"
                                class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label for="expDateTo" class="block text-xs font-bold text-slate-700 mb-1.5">To</label>
                            <input type="date" id="expDateTo" value="{{ date('Y-m-d') }}"
                                class="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>

                    <!-- Filter 3: Clinic / Office -->
                    <div>
                        <label for="expClinic" class="block text-xs font-bold text-slate-700 mb-1.5">Location / Clinic</label>
                        <select id="expClinic"
                            class="w-full bg-white border border-slate-300 rounded-md px-3 py-2 text-xs font-medium text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 cursor-pointer">
                            <option value="all">All Locations</option>
                            @if(isset($clinics) && count($clinics))
                                @foreach($clinics as $c)
                                    <option value="{{ $c->id ?? $c->ClinicNum ?? $loop->iteration }}">{{ $c->name ?? $c->Description ?? 'Clinic '.$loop->iteration }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Filter 4: Patient Status -->
                    <div>
                        <label for="expStatus" class="block text-xs font-bold text-slate-700 mb-1.5">Patient Status</label>
                        <select id="expStatus"
                            class="w-full bg-white border border-slate-300 rounded-md px-3 py-2 text-xs font-medium text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 cursor-pointer">
                            <option value="all">All Statuses</option>
                            <option value="0">Active (0)</option>
                            <option value="2">Inactive (2)</option>
                            <option value="1">Non-Patient (1)</option>
                            <option value="3">Archived (3)</option>
                            <option value="5">Deceased (5)</option>
                            <option value="6">Prospective (6)</option>
                        </select>
                    </div>

                    <!-- Filter 5: Keyword Search -->
                    <div>
                        <label for="expSearch" class="block text-xs font-bold text-slate-700 mb-1.5">Search Keywords</label>
                        <div class="relative">
                            <input type="text" id="expSearch" placeholder="Name, ID, phone, email, city..."
                                class="w-full bg-white border border-slate-300 rounded-md pl-3 pr-8 py-2 text-xs font-medium text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute right-2.5 top-2.5"></i>
                        </div>
                    </div>
                </div>

                <!-- Match Status Indicator -->
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2 text-emerald-800 bg-emerald-50/80 border border-emerald-200 px-3 py-1.5 rounded-md font-medium">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                        <span><strong id="expMatchCount" class="font-bold text-emerald-900">0</strong> patients match your selected filter criteria.</span>
                    </div>
                </div>
            </div>

            <!-- Section 2: Selected Columns to Export -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
                <div class="flex flex-wrap items-center justify-between mb-4 pb-3 border-b border-slate-100 gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 bg-emerald-50 text-emerald-600 rounded-md">
                            <i data-lucide="columns-3" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">2. Select Export Columns</h2>
                            <p class="text-xs text-slate-500">Choose exactly which data fields to include in your export file (<span id="expSelectedBadge" class="font-bold text-emerald-600">8 selected</span>)</p>
                        </div>
                    </div>

                    <!-- Column Presets -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold text-slate-500 mr-1">Presets:</span>
                        <button type="button" class="exp-preset-btn px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded transition-colors cursor-pointer" data-preset="all">
                            Select All
                        </button>
                        <button type="button" class="exp-preset-btn px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded transition-colors cursor-pointer" data-preset="none">
                            Deselect All
                        </button>
                        <button type="button" class="exp-preset-btn px-2.5 py-1 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-xs font-bold rounded transition-colors cursor-pointer" data-preset="contact">
                            Contact List
                        </button>
                        <button type="button" class="exp-preset-btn px-2.5 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs font-bold rounded transition-colors cursor-pointer" data-preset="demographics">
                            Demographics
                        </button>
                        <button type="button" class="exp-preset-btn px-2.5 py-1 bg-amber-100 hover:bg-amber-200 text-amber-900 text-xs font-bold rounded transition-colors cursor-pointer" data-preset="financial">
                            Financials & Balances
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Category 1: Identification -->
                    <div class="bg-slate-50/70 border border-slate-200 rounded-lg p-4 space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2 border-b border-slate-200 pb-2">
                            <i data-lucide="user" class="w-3.5 h-3.5 text-emerald-600"></i> Identification
                        </h3>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="patient_id" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500" checked>
                                <span class="font-semibold">Patient ID (PatNum)</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="first_name" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500" checked>
                                <span>First Name</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="last_name" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500" checked>
                                <span>Last Name</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="full_name" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500" checked>
                                <span class="font-semibold">Full Name</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="date_added" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500" checked>
                                <span class="font-bold text-emerald-800 bg-emerald-100/70 px-1.5 py-0.5 rounded">Date Added (Open Dental)</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="birthdate" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span>Birthdate</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="age" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span>Age</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="gender" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span>Gender</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="ssn" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span>SSN</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="chart_number" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span>Chart Number</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="status" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span>Status</span>
                            </label>
                        </div>
                    </div>

                    <!-- Category 2: Contact Info -->
                    <div class="bg-slate-50/70 border border-slate-200 rounded-lg p-4 space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2 border-b border-slate-200 pb-2">
                            <i data-lucide="phone" class="w-3.5 h-3.5 text-emerald-600"></i> Contact Information
                        </h3>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="email" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500" checked>
                                <span class="font-semibold">Email Address</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="mobile_phone" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500" checked>
                                <span class="font-semibold">Mobile Phone</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="home_phone" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span>Home Phone</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="work_phone" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span>Work Phone</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="address" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500" checked>
                                <span>Street Address</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="address2" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span>Address Line 2</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="city" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500" checked>
                                <span>City</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="state" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500" checked>
                                <span>State</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="zip" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500" checked>
                                <span>ZIP Code</span>
                            </label>
                        </div>
                    </div>

                    <!-- Category 3: Financial & Account -->
                    <div class="bg-slate-50/70 border border-slate-200 rounded-lg p-4 space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2 border-b border-slate-200 pb-2">
                            <i data-lucide="dollar-sign" class="w-3.5 h-3.5 text-emerald-600"></i> Financial & Account
                        </h3>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="guarantor_id" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span>Guarantor ID</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="guarantor_name" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span>Guarantor Name</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="bal_total" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span class="font-semibold">Total Balance</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="bal_0_30" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span>Balance 0-30</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="bal_31_60" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span>Balance 31-60</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="bal_61_90" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span>Balance 61-90</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="bal_over_90" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span>Balance Over 90</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="est_balance" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span>Estimated Balance</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="ins_est" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span>Insurance Estimate</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="billing_type" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span>Billing Type</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none">
                                <input type="checkbox" value="primary_provider" class="exp-col-chk w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span>Primary Provider</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Live Preview & Export Action Bar -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-4 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 bg-emerald-50 text-emerald-600 rounded-md">
                            <i data-lucide="table" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">3. Live Data Preview & Export</h2>
                            <p class="text-xs text-slate-500">Preview the exact data before generating your export file</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2">
                            <label for="expFilenameInput" class="text-xs font-bold text-slate-600">Filename:</label>
                            <input type="text" id="expFilenameInput" value="patients_export_{{ date('Y-m-d') }}"
                                class="bg-slate-50 border border-slate-300 rounded px-2.5 py-1.5 text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 w-48">
                        </div>

                        <button type="button" id="expDownloadCsvBtn"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-5 py-2 rounded-md shadow-sm transition-all flex items-center gap-2 cursor-pointer">
                            <i data-lucide="download" class="w-4 h-4"></i> Export to CSV
                        </button>
                    </div>
                </div>

                <!-- Preview Table -->
                <div class="overflow-x-auto custom-table-scrollbar border border-slate-200 rounded-md relative min-h-[220px]">
                    <div id="expLoadingOverlay" class="hidden absolute inset-0 bg-white/80 backdrop-blur-xs flex items-center justify-center z-20">
                        <div class="flex items-center gap-2 text-emerald-600 font-bold text-xs">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Loading filtered records...
                        </div>
                    </div>

                    <table id="expPreviewTable" class="w-full text-left border-collapse table-auto text-xs">
                        <thead id="expPreviewThead" class="bg-slate-100 border-b border-slate-200 font-bold text-slate-700">
                        </thead>
                        <tbody id="expPreviewTbody" class="divide-y divide-slate-100 text-slate-700 bg-white">
                        </tbody>
                    </table>
                </div>

                <!-- Preview Pagination & Info Bar -->
                <div class="flex flex-wrap items-center justify-between text-xs text-slate-600 pt-2">
                    <div id="expPaginationInfo" class="font-medium">
                        Showing 0 of 0 patients
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" id="expPrevPageBtn" disabled
                            class="px-3 py-1.5 bg-white border border-slate-200 rounded text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed font-medium transition-colors">
                            Previous
                        </button>
                        <span id="expPageIndicator" class="px-2 font-bold text-slate-800">Page 1 of 1</span>
                        <button type="button" id="expNextPageBtn" disabled
                            class="px-3 py-1.5 bg-white border border-slate-200 rounded text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed font-medium transition-colors">
                            Next
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- POPUP & MODALS                             -->
        <!-- ========================================== -->
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
                    <label for="exportFileName" class="block text-xs font-semibold text-slate-600 mb-1.5">File Name</label>
                    <div class="relative flex items-center">
                        <input type="text" id="exportFileName"
                            class="w-full pl-3 pr-16 py-2 border border-slate-300 rounded text-xs font-medium text-slate-800 focus:outline-none focus:border-emerald-500">
                        <span class="absolute right-3 text-xs font-semibold text-slate-400 pointer-events-none">.xlsx</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">You can customize the export filename above before downloading.</p>
                </div>

                <div class="flex items-center justify-end gap-2.5">
                    <button id="cancelExportBtn"
                        class="border border-slate-300 text-slate-700 text-xs font-bold px-4 py-2 rounded bg-white hover:bg-slate-50 transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button id="confirmExportBtn"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-5 py-2 rounded transition-colors flex items-center gap-1 shadow-sm cursor-pointer">
                        Continue Export
                    </button>
                </div>
            </div>
        </div>

        <x-app-components.patient-modal />

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

        $(document).ready(function () {
            // Tab Switching Engine
            $('#tabPatientsBtn').on('click', function () {
                $('.portal-tab-btn').removeClass('border-emerald-500 text-slate-900 font-bold').addClass('border-transparent text-slate-500 font-medium');
                $(this).addClass('border-emerald-500 text-slate-900 font-bold').removeClass('border-transparent text-slate-500 font-medium');

                $('#exportDataTabContent').addClass('hidden');
                $('#patientsListTabContent').removeClass('hidden');
            });

            $('#tabExportDataBtn').on('click', function () {
                $('.portal-tab-btn').removeClass('border-emerald-500 text-slate-900 font-bold').addClass('border-transparent text-slate-500 font-medium');
                $(this).addClass('border-emerald-500 text-slate-900 font-bold').removeClass('border-transparent text-slate-500 font-medium');

                $('#patientsListTabContent').addClass('hidden');
                $('#exportDataTabContent').removeClass('hidden');

                fetchExportPreview();
            });

            // Initialize Patients Main DataTable
            table = DDS.dataTable(document.getElementById('patientsTable'), {
                processing: true,
                serverSide: true,
                pagingType: 'simple_numbers',
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
                        name: 'name',
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
                    { data: 'patient_id', name: 'PatNum' },
                    { data: 'guarantor', name: 'guarantor_name', orderable: false, searchable: false },
                    { data: 'guarantor_id', name: 'Guarantor' },
                    { data: 'age', name: 'age', orderable: false, searchable: false },
                    { data: 'gender', name: 'Gender' },
                    { data: 'address', name: 'Address' },
                    { data: 'city', name: 'City' },
                    { data: 'state', name: 'State' },
                    { data: 'zip', name: 'Zip' },
                    { data: 'work_phone', name: 'WkPhone' },
                    { data: 'home_phone', name: 'HmPhone' },
                    { data: 'mobile_phone', name: 'WirelessPhone' },
                    { data: 'email', name: 'Email' },
                    { data: 'birthdate', name: 'Birthdate' },
                    { data: 'first_visit', name: 'first_visit', orderable: false, searchable: false },
                    {
                        data: 'lifetime_value_production',
                        name: 'lifetime_production',
                        orderable: false,
                        searchable: false,
                        render: function (data) { return data ? '$' + Number(data).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '$0.00'; }
                    },
                    {
                        data: 'lifetime_value_collection',
                        name: 'lifetime_value_collection',
                        orderable: false,
                        searchable: false,
                        render: function (data) { return data ? '$' + Number(data).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '$0.00'; }
                    },
                    { data: 'referral_source', name: 'referral_source', orderable: false, searchable: false }
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

            $("#resetBtn").on('click', function () {
                $("#searchInput").val("");
                table.search("").draw();
            });

            // Export Modal in Tab 1
            $("#exportBtn").click(function () {
                let today = new Date().toISOString().split('T')[0];
                $("#exportFileName").val("patient_export_" + today);
                $("#exportModal").removeClass('hidden');
            });

            $("#cancelExportBtn").click(function () {
                $("#exportModal").addClass('hidden');
            });

            $("#confirmExportBtn").click(function () {
                let currentSearchValue = $("#searchInput").val();
                let customName = $("#exportFileName").val() || "patient_export";
                $("#exportModal").addClass('hidden');

                $.ajax({
                    url: "{{ url('/patients/export') }}",
                    method: 'POST',
                    data: {
                        _token: "{{csrf_token()}}",
                        search: currentSearchValue,
                        filename: customName
                    },
                    success: function (file) {
                        window.location = file.url;
                    }
                });
            });

            // ========================================================
            // EXPORT DATA TAB ENGINE
            // ========================================================
            let expCurrentPage = 1;
            let expTotalPages = 1;
            const expColLabels = {
                'patient_id': 'Patient ID',
                'first_name': 'First Name',
                'last_name': 'Last Name',
                'full_name': 'Full Name',
                'birthdate': 'Birthdate',
                'age': 'Age',
                'gender': 'Gender',
                'ssn': 'SSN',
                'chart_number': 'Chart Number',
                'status': 'Status',
                'date_added': 'Date Added',
                'email': 'Email',
                'mobile_phone': 'Mobile Phone',
                'home_phone': 'Home Phone',
                'work_phone': 'Work Phone',
                'address': 'Street Address',
                'address2': 'Address 2',
                'city': 'City',
                'state': 'State',
                'zip': 'ZIP Code',
                'guarantor_id': 'Guarantor ID',
                'guarantor_name': 'Guarantor Name',
                'bal_total': 'Total Balance',
                'bal_0_30': 'Bal 0-30',
                'bal_31_60': 'Bal 31-60',
                'bal_61_90': 'Bal 61-90',
                'bal_over_90': 'Bal Over 90',
                'est_balance': 'Est Balance',
                'ins_est': 'Ins Est',
                'billing_type': 'Billing Type',
                'primary_provider': 'Primary Provider'
            };

            function getSelectedExportCols() {
                let cols = [];
                $('.exp-col-chk:checked').each(function () {
                    cols.push($(this).val());
                });
                return cols;
            }

            function updateSelectedCountBadge() {
                let count = $('.exp-col-chk:checked').length;
                $('#expSelectedBadge').text(count + ' selected');
            }

            // Date mode toggle
            $('#expDateMode').on('change', function () {
                if ($(this).val() === 'custom') {
                    $('#expCustomDateContainer').removeClass('hidden');
                } else {
                    $('#expCustomDateContainer').addClass('hidden');
                }
            });

            // Column checkbox change
            $(document).on('change', '.exp-col-chk', function () {
                updateSelectedCountBadge();
                fetchExportPreview(1);
            });

            // Presets
            $('.exp-preset-btn').on('click', function () {
                let preset = $(this).data('preset');
                if (preset === 'all') {
                    $('.exp-col-chk').prop('checked', true);
                } else if (preset === 'none') {
                    $('.exp-col-chk').prop('checked', false);
                } else if (preset === 'contact') {
                    $('.exp-col-chk').prop('checked', false);
                    ['patient_id', 'first_name', 'last_name', 'mobile_phone', 'email', 'address', 'city', 'state', 'zip', 'date_added'].forEach(c => {
                        $(`.exp-col-chk[value="${c}"]`).prop('checked', true);
                    });
                } else if (preset === 'demographics') {
                    $('.exp-col-chk').prop('checked', false);
                    ['patient_id', 'first_name', 'last_name', 'birthdate', 'age', 'gender', 'ssn', 'chart_number', 'status', 'date_added'].forEach(c => {
                        $(`.exp-col-chk[value="${c}"]`).prop('checked', true);
                    });
                } else if (preset === 'financial') {
                    $('.exp-col-chk').prop('checked', false);
                    ['patient_id', 'full_name', 'guarantor_id', 'guarantor_name', 'bal_total', 'bal_0_30', 'bal_31_60', 'bal_61_90', 'bal_over_90', 'est_balance', 'ins_est', 'primary_provider'].forEach(c => {
                        $(`.exp-col-chk[value="${c}"]`).prop('checked', true);
                    });
                }
                updateSelectedCountBadge();
                fetchExportPreview(1);
            });

            // Apply Filters
            $('#expApplyFiltersBtn').on('click', function () {
                fetchExportPreview(1);
            });

            $('#expSearch').on('keypress', function (e) {
                if (e.which == 13) { fetchExportPreview(1); }
            });

            // Reset Filters
            $('#expResetFiltersBtn').on('click', function () {
                $('#expDateMode').val('this_month').trigger('change');
                $('#expClinic').val('all');
                $('#expStatus').val('all');
                $('#expSearch').val('');
                fetchExportPreview(1);
            });

            // Pagination Controls
            $('#expPrevPageBtn').on('click', function () {
                if (expCurrentPage > 1) {
                    fetchExportPreview(expCurrentPage - 1);
                }
            });

            $('#expNextPageBtn').on('click', function () {
                if (expCurrentPage < expTotalPages) {
                    fetchExportPreview(expCurrentPage + 1);
                }
            });

            // Fetch Preview Data
            function fetchExportPreview(page = 1) {
                expCurrentPage = page;
                $('#expLoadingOverlay').removeClass('hidden');

                let cols = getSelectedExportCols();
                if (cols.length === 0) {
                    cols = ['patient_id', 'first_name', 'last_name', 'date_added'];
                }

                let params = {
                    date_mode: $('#expDateMode').val(),
                    date_from: $('#expDateFrom').val(),
                    date_to: $('#expDateTo').val(),
                    clinic_id: $('#expClinic').val(),
                    status: $('#expStatus').val(),
                    search: $('#expSearch').val(),
                    columns: cols,
                    page: expCurrentPage,
                    per_page: 20
                };

                $.get("{{ route('patients.export-data') }}", params, function (res) {
                    $('#expLoadingOverlay').addClass('hidden');

                    let total = res.total || 0;
                    expTotalPages = res.total_pages || 1;
                    $('#expMatchCount').text(total.toLocaleString());
                    $('#expPaginationInfo').text(`Showing ${res.data.length} of ${total.toLocaleString()} matching patients`);
                    $('#expPageIndicator').text(`Page ${expCurrentPage} of ${Math.max(1, expTotalPages)}`);

                    $('#expPrevPageBtn').prop('disabled', expCurrentPage <= 1);
                    $('#expNextPageBtn').prop('disabled', expCurrentPage >= expTotalPages);

                    // Build Thead
                    let theadHtml = '<tr>';
                    cols.forEach(col => {
                        theadHtml += `<th class="py-3 px-3.5 border-r border-slate-200 whitespace-nowrap">${expColLabels[col] || col}</th>`;
                    });
                    theadHtml += '</tr>';
                    $('#expPreviewThead').html(theadHtml);

                    // Build Tbody
                    let tbodyHtml = '';
                    if (res.data && res.data.length > 0) {
                        res.data.forEach(row => {
                            tbodyHtml += '<tr class="hover:bg-slate-50/80 transition-colors">';
                            cols.forEach(col => {
                                let val = row[col] !== undefined && row[col] !== null ? row[col] : '';
                                if (typeof val === 'number' && ['bal_total', 'bal_0_30', 'bal_31_60', 'bal_61_90', 'bal_over_90', 'est_balance', 'ins_est'].includes(col)) {
                                    val = '$ ' + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                }
                                tbodyHtml += `<td class="py-2.5 px-3.5 border-r border-slate-100 whitespace-nowrap">${val || '—'}</td>`;
                            });
                            tbodyHtml += '</tr>';
                        });
                    } else {
                        tbodyHtml = `<tr><td colspan="${cols.length}" class="py-12 text-center text-slate-400 font-medium">No patient records found matching your selected filters.</td></tr>`;
                    }
                    $('#expPreviewTbody').html(tbodyHtml);

                    lucide.createIcons();
                }).fail(function () {
                    $('#expLoadingOverlay').addClass('hidden');
                    alert('Failed to load export preview. Please check your connection.');
                });
            }

            // Export Download Action
            $('#expDownloadCsvBtn').on('click', function () {
                let cols = getSelectedExportCols();
                if (cols.length === 0) {
                    alert('Please select at least one column to export.');
                    return;
                }

                let filename = $('#expFilenameInput').val() || 'patients_export';
                let params = $.param({
                    date_mode: $('#expDateMode').val(),
                    date_from: $('#expDateFrom').val(),
                    date_to: $('#expDateTo').val(),
                    clinic_id: $('#expClinic').val(),
                    status: $('#expStatus').val(),
                    search: $('#expSearch').val(),
                    columns: cols,
                    filename: filename
                });

                window.location.href = "{{ route('patients.export-download') }}?" + params;
            });

            // Auto-open patient modal if open_patient_id is in query params
            const urlParams = new URLSearchParams(window.location.search);
            const openPatientId = urlParams.get('open_patient_id');
            if (openPatientId) {
                openPatient(openPatientId);
            }
        });

        $("#refreshPatients").click(function () {
            if (!$('#patientsListTabContent').hasClass('hidden')) {
                table.ajax.reload();
            } else {
                fetchExportPreview(1);
            }
        });
    </script>
</x-app-layout>