<x-app-layout>

    {{-- FullCalendar Scheduler (includes resource-timegrid) --}}
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.15/index.global.min.css' rel='stylesheet'>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.15/index.global.min.js'></script>

    <style>
        .notes-cell {
            max-width: 15rem;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            cursor: pointer;
        }

        /* ---------- FullCalendar overrides ---------- */
        .fc .fc-toolbar {
            display: none !important;
        }

        .fc .fc-col-header-cell {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .fc .fc-col-header-cell-cushion {
            font-size: 0.75rem;
            font-weight: 700;
            color: #1e293b;
            text-decoration: none;
            padding: 8px 4px;
        }

        .fc .fc-timegrid-slot {
            height: 32px;
        }

        .fc .fc-timegrid-slot-label-cushion {
            font-size: 0.68rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .fc .fc-timegrid-now-indicator-line {
            border-color: #ef4444;
        }

        .fc .fc-timegrid-now-indicator-arrow {
            border-top-color: #ef4444;
            border-bottom-color: #ef4444;
        }

        .fc-event {
            cursor: pointer;
            border-radius: 4px !important;
            border: none !important;
            min-height: 42px !important;
        }

        .fc-event-main {
            overflow: hidden;
        }

        .fc-license-message {
            display: none !important;
        }

        /* Resource header column */
        .fc .fc-resource-timeline-divider {
            width: 0;
        }

        .fc-datagrid-cell-cushion {
            font-size: 0.7rem;
        }

        /* Scrollbar */
        .fc-scroller::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .fc-scroller::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .fc-scroller::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        /* Active view button */
        .view-btn.active {
            background: white;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .1);
            color: #059669;
            font-weight: 700;
        }

        /* Skeleton progress bar */
        #skel-bar {
            transition: width 0.35s cubic-bezier(.4, 0, .2, 1);
        }

        #cal-skeleton {
            transition: opacity 0.45s ease;
        }

        @keyframes skel-pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .45;
            }
        }

        .skel-pulse {
            animation: skel-pulse 1.6s ease-in-out infinite;
        }

        /* Month View overrides */
        .fc-daygrid-day {
            cursor: pointer;
            min-height: 115px;
            transition: background-color 0.15s ease;
        }

        .fc-daygrid-day:hover {
            background-color: #f8fafc;
        }

        .fc-daygrid-day-frame {
            min-height: 115px;
            height: 100%;
        }
    </style>
    <header class="bg-white border-b border-gray-100 px-8 py-4 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">Calendar</h1>
        </div>
    </header>
    <div class="flex flex-col bg-slate-50" style="min-height: calc(100vh - 64px);">

        {{-- ══════════════════ TOP TOOLBAR ══════════════════ --}}
        <div class="bg-white border-b border-slate-200 px-6 py-3 flex items-center justify-between gap-4 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div id="singleDateWrapper"
                    class="relative flex items-center border border-slate-300 rounded px-3 py-1.5 gap-2 bg-white shadow-sm">
                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2" />
                        <line x1="16" y1="2" x2="16" y2="6" stroke-width="2" />
                        <line x1="8" y1="2" x2="8" y2="6" stroke-width="2" />
                        <line x1="3" y1="10" x2="21" y2="10" stroke-width="2" />
                    </svg>
                    <input type="date" id="calDate"
                        class="border-0 outline-none text-sm font-semibold text-slate-700 bg-transparent cursor-pointer"
                        value="{{ date('Y-m-d') }}">
                </div>

                <div id="rangeDateWrapper" class="hidden">
                    <x-daterange-picker id="calDateRange" on-apply="onCalendarRangeApply" />
                </div>


                <button id="refreshBtn"
                    class="border border-emerald-500 text-emerald-600 px-5 py-1.5 rounded text-sm font-semibold hover:bg-emerald-50 transition shadow-sm">
                    Refresh
                </button>
            </div>

            <div class="flex items-center gap-3">
                <div id="viewToggleWrapper" class="flex bg-slate-100 rounded-md border border-slate-200 p-0.5 gap-0.5">
                    <button class="view-btn px-4 py-1.5 text-xs font-medium rounded text-slate-500 transition-all"
                        data-view="dayGridMonth">Month</button>
                    <button class="view-btn px-4 py-1.5 text-xs font-medium rounded text-slate-500 transition-all"
                        data-view="resourceTimeGridWeek">Week</button>
                    <button class="view-btn active px-4 py-1.5 text-xs font-medium rounded transition-all"
                        data-view="resourceTimeGridDay">Day</button>
                </div>
            </div>
        </div>

        {{-- ══════════════════ TABS ══════════════════ --}}
        <div class="bg-white border-b border-slate-200 px-6">
            <nav class="flex gap-6">
                <button id="tab-calendar"
                    class="cal-tab py-2.5 text-sm font-bold text-slate-900 border-b-2 border-emerald-500"
                    data-target="view-calendar">
                    Appointments Calendar
                </button>
                <button id="tab-details"
                    class="cal-tab py-2.5 text-sm font-medium text-slate-400 border-b-2 border-transparent hover:text-slate-600 transition-colors"
                    data-target="view-details">
                    Appointment Details
                </button>
                <button id="tab-capacity"
                    class="cal-tab py-2.5 text-sm font-medium text-slate-400 border-b-2 border-transparent hover:text-slate-600 transition-colors"
                    data-target="view-capacity">
                    Appointment Capacity
                </button>
            </nav>
        </div>

        <div id="view-calendar" class="flex flex-col flex-1 overflow-hidden">

            {{-- ══════════════════ STATS ROW ══════════════════ --}}
            <div class="bg-white border-b border-slate-200 px-6 py-3 flex items-center gap-10 flex-shrink-0">
                <div>
                    <p class="text-xs text-slate-500 mb-0.5 flex items-center gap-1">
                        <span id="stat-production-title">Production</span>
                        <span class="text-slate-400 cursor-help" id="stat-production-help"
                            title="Display $ amount of what has been produced for the day">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </p>
                    <p class="text-xl font-bold text-slate-900" id="stat-production">—</p>
                </div>
                <div id="stat-scheduled-container" class="cursor-pointer group rounded-lg p-1.5 -m-1.5 transition hover:bg-emerald-50/60" title="Click to view scheduled production breakdown" onclick="openScheduledProductionModal()">
                    <p class="text-xs text-slate-500 mb-0.5 flex items-center gap-1.5">
                        <span id="stat-scheduled-title">Scheduled Production</span>
                        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-1.5 py-0.5 rounded-full flex items-center gap-1 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                            Breakdown
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </p>
                    <p class="text-xl font-bold text-slate-900 group-hover:text-emerald-700 transition-colors" id="stat-scheduled">—</p>
                </div>
                <div class="ml-auto flex items-center gap-2">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="activeColumnsToggle" class="sr-only peer" checked>
                        <div class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:bg-emerald-500
                     after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                     after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all
                     peer-checked:after:translate-x-full"></div>
                        <span class="ml-2 text-sm font-medium text-slate-600">Active Columns only</span>
                    </label>
                </div>
            </div>

            {{-- ══════════════════ NAV BAR ══════════════════ --}}
            <div class="bg-white border-b border-slate-200 px-6 py-2 flex items-center justify-between flex-shrink-0">
                <button id="prevBtn" class="p-1.5 rounded border border-slate-300 hover:bg-slate-50 transition">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <div class="flex items-center gap-3 text-sm">
                    <span id="calDateLabel" class="font-bold text-slate-900 text-base"></span>
                    <span class="text-slate-300">|</span>
                    <span id="liveTime" class="font-medium text-slate-500"></span>
                </div>
                <button id="nextBtn" class="p-1.5 rounded border border-slate-300 hover:bg-slate-50 transition">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            {{-- ══════════════════ PROVIDER HEADER ══════════════════ --}}
            <div id="provider-header"
                class="bg-white border-b border-slate-200 px-6 py-3 flex items-center gap-3 overflow-x-auto select-none flex-shrink-0">
                <!-- Rendered dynamically -->
            </div>

            {{-- ══════════════════ CALENDAR + SIDEBAR ══════════════════ --}}
            <div class="flex flex-1 overflow-hidden bg-white">

                {{-- Calendar --}}
                <div id="calendar-wrap" class="flex-1 overflow-auto p-3 relative">
                    <div id="calendar"></div>
                </div>

                {{-- Appointment Detail Sidebar (hidden until event clicked) --}}
                <div id="apt-sidebar"
                    class="hidden w-80 border-l border-slate-200 bg-white flex-col overflow-y-auto flex-shrink-0">

                    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Appointment Details</h3>
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <button onclick="closeSidebar()"
                                class="text-slate-400 hover:text-slate-700 transition-colors p-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <line x1="18" y1="6" x2="6" y2="18" stroke-width="2" />
                                    <line x1="6" y1="6" x2="18" y2="18" stroke-width="2" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div id="sidebar-body" class="p-4 flex-1">
                        <p class="text-xs text-slate-400 text-center mt-8">Click an appointment to see details</p>
                    </div>

                </div>
            </div>

        </div>

        <div id="view-details" class="hidden flex-col flex-1 overflow-y-auto bg-slate-50 p-6">
            {{-- Filters --}}
            <div class="flex gap-4 mb-6">
                <div class="flex flex-col flex-1">
                    <label class="text-xs font-bold text-slate-900 mb-1">Provider(s)</label>
                    <select id="detailsFilterProvider"
                        class="border border-slate-300 rounded px-3 py-1.5 text-sm text-slate-700 bg-white font-semibold">
                        <option value="">All Providers</option>
                    </select>
                </div>
                <div class="flex flex-col flex-1">
                    <label class="text-xs font-bold text-slate-900 mb-1">Appointment Status</label>
                    <select id="detailsFilterStatus"
                        class="border border-slate-300 rounded px-3 py-1.5 text-sm text-slate-700 bg-white font-semibold">
                        <option value="">All Statuses</option>
                        <option value="1">Scheduled</option>
                        <option value="2">Completed</option>
                        <option value="4">ASAP</option>
                        <option value="5">Broken</option>
                    </select>
                </div>
            </div>

            {{-- White container for DataTable --}}
            <div class="bg-white border border-slate-200 p-0 flex flex-col flex-1 h-full relative">
                {{-- Toolbar inside the table container --}}
                <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex text-sm">
                        <button
                            class="bg-green-100 text-green-700 font-medium px-4 py-1.5 border border-transparent rounded-sm hover:bg-green-200 transition">Top
                            20%</button>
                        <button
                            class="bg-yellow-100/70 text-yellow-700 font-medium px-4 py-1.5 border border-transparent 0 rounded-sm ml-1 hover:bg-yellow-200 transition">Mid
                            Tier</button>
                        <button
                            class="bg-red-100 text-red-600 font-medium px-4 py-1.5 border border-transparent rounded-sm ml-1 hover:bg-red-200 transition">Bottom
                            20%</button>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <div class="relative">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <input type="text" id="detailsSearch" placeholder="Search"
                                class="border border-slate-300 pl-9 pr-3 py-1.5 text-slate-700 w-64 focus:outline-emerald-500">
                        </div>
                        <button id="exportDetailsCsvBtn" onclick="exportAppointmentDetailsCsv()"
                            class="border border-emerald-500 text-emerald-600 font-semibold px-4 py-1.5 rounded-sm hover:bg-emerald-50 transition shadow-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export CSV
                        </button>
                    </div>
                </div>

                {{-- Table --}}
                <div class="flex-1 w-full relative">
                    <x-table-skeleton />
                    <x-data-table id="appointmentDetailsTable" min-width="1800px" max-height="100%">
                        <x-slot:head>
                            <tr class="bg-white">
                                <th class="text-xs font-semibold py-4 pl-5 pr-3 border-l border-t border-gray-200 dt-col-sticky text-left"
                                    style="min-width: 12rem;">Location</th>
                                <th class="text-xs font-semibold py-4 pl-5 pr-3 border-l border-t border-gray-200 dt-col-sticky text-left"
                                    style="min-width: 12rem; left: 12rem;">Patient Name</th>
                                <th class="text-xs font-semibold py-4 pl-5 pr-3 border-l border-t border-gray-200 dt-col-sticky text-left shadow-[2px_0_5px_-2px_rgba(0,0,0,0.10)]"
                                    style="min-width: 12rem; left: 24rem;">Appointment Date</th>
                                <th class="text-xs font-semibold py-4 pl-5 pr-3 border-l border-t border-gray-200"
                                    style="min-width: 10rem;">Appointment Time</th>
                                <th class="text-xs font-semibold py-4 pl-5 pr-3 border-l border-t border-gray-200"
                                    style="min-width: 10rem;">Appointment Duration</th>
                                <th class="text-xs font-semibold py-4 pl-5 pr-3 border-l border-t border-gray-200"
                                    style="min-width: 10rem;">Operatory Name</th>
                                <th class="text-xs font-semibold py-4 pl-5 pr-3 border-l border-t border-gray-200"
                                    style="min-width: 10rem;">Appointment Status</th>
                                <th class="text-xs font-semibold py-4 pl-5 pr-3 border-l border-t border-gray-200"
                                    style="min-width: 10rem;">Patient Age</th>
                                <th class="text-xs font-semibold py-4 pl-5 pr-3 border-l border-t border-gray-200"
                                    style="min-width: 10rem;">Patient Phone</th>
                                <th class="text-xs font-semibold py-4 pl-5 pr-3 border-l border-t border-gray-200"
                                    style="min-width: 10rem;">Email Address</th>
                                <th class="text-xs font-semibold py-4 pl-5 pr-3 border-l border-t border-gray-200"
                                    style="min-width: 10rem;">Patient Type</th>
                                <th class="text-xs font-semibold py-4 pl-5 pr-3 border-l border-t border-gray-200"
                                    style="min-width: 10rem;">Appointment Notes</th>
                                <th class="text-xs font-semibold py-4 pl-5 pr-3 border-l border-t border-gray-200"
                                    style="min-width: 10rem;">Confirmation Status</th>
                                <th class="text-xs font-semibold py-4 pl-5 pr-3 border-l border-t border-gray-200"
                                    style="min-width: 10rem;">Provider Name</th>
                                <th class="text-xs font-semibold py-4 pl-5 pr-3 border-l border-t border-gray-200"
                                    style="min-width: 10rem;">Procedure Codes</th>
                                <th class="text-xs font-semibold py-4 pl-5 pr-3 border-l border-t border-gray-200"
                                    style="min-width: 10rem;">Production</th>
                                <th class="text-xs font-semibold py-4 pl-5 pr-3 border-l border-t border-gray-200"
                                    style="min-width: 10rem;">Primary Insurance Carrier</th>
                                <th class="text-xs font-semibold py-4 pl-5 pr-3 border-l border-t border-gray-200"
                                    style="min-width: 10rem;">Secondary Insurance Carrier</th>
                                <th class="text-xs font-semibold py-4 pl-5 pr-3 border-l border-t border-gray-200"
                                    style="min-width: 10rem;">Referral Source</th>
                                <th class="text-xs font-semibold py-4 pl-5 pr-3 border-l border-t border-gray-200"
                                    style="min-width: 10rem;">Unscheduled Tx $</th>
                                <th class="text-xs font-semibold py-4 pl-5 pr-3 border-l border-t border-gray-200"
                                    style="min-width: 10rem;">Last Visit Date</th>
                            </tr>
                        </x-slot:head>

                        <x-slot:foot>
                            <tr class="bg-gray-200">
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white dt-col-sticky"
                                    style="min-width: 12rem;"><span class="">--</span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white dt-col-sticky"
                                    style="min-width: 12rem; width: 12rem; left: 12rem;"><span class="">--</span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white dt-col-sticky shadow-[2px_0_5px_-2px_rgba(0,0,0,0.10)]"
                                    style="min-width: 12rem; width: 12rem; left: 24rem;">Average:</td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="">--</span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class=""></span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="">--</span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="">--</span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="">--</span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="">()--</span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="">--</span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="">--</span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="">--</span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="">--</span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="">--</span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="">--</span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class=""></span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="">--</span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="">--</span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="">--</span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class=""></span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="">-</span></td>
                            </tr>
                            <tr class="bg-gray-200">
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white dt-col-sticky"
                                    style="min-width: 12rem;"><span class="block flex justify-end"><span><strong
                                                class="block truncate">--</strong> <!----></span> </span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white dt-col-sticky"
                                    style="min-width: 12rem; width: 12rem; left: 12rem;"><span
                                        class="block flex justify-end"><span><strong class="block truncate">--</strong>
                                            <!----></span> </span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white dt-col-sticky shadow-[2px_0_5px_-2px_rgba(0,0,0,0.10)]"
                                    style="min-width: 12rem; width: 12rem; left: 24rem;">Total:</td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="block flex justify-end"><span><strong
                                                class="block truncate">--</strong> <!----></span> </span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="block flex justify-end"><span><strong
                                                class="block truncate"><span class=""></span></strong>
                                            <!----></span> </span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="block flex justify-end"><span><strong
                                                class="block truncate">--</strong> <!----></span> </span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="block flex justify-end"><span><strong
                                                class="block truncate">--</strong> <!----></span> </span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="block flex justify-end"><span><strong
                                                class="block truncate">--</strong> <!----></span> </span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="block flex justify-end"><span><strong
                                                class="block truncate">--</strong> <!----></span> </span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="block flex justify-end"><span><strong
                                                class="block truncate">--</strong> <!----></span> </span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="block flex justify-end"><span><strong
                                                class="block truncate">--</strong> <!----></span> </span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="block flex justify-end"><span><strong
                                                class="block truncate">--</strong> <!----></span> </span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="block flex justify-end"><span><strong
                                                class="block truncate">--</strong> <!----></span> </span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="block flex justify-end"><span><strong
                                                class="block truncate">--</strong> <!----></span> </span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="block flex justify-end"><span><strong
                                                class="block truncate">--</strong> <!----></span> </span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="block flex justify-end"><span><strong
                                                class="block truncate"><span class=""></span></strong>
                                            <!----></span> </span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="block flex justify-end"><span><strong
                                                class="block truncate">--</strong> <!----></span> </span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="block flex justify-end"><span><strong
                                                class="block truncate">--</strong> <!----></span> </span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="block flex justify-end"><span><strong
                                                class="block truncate">--</strong> <!----></span> </span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="block flex justify-end"><span><strong
                                                class="block truncate"><span class=""></span></strong>
                                            <!----></span> </span></td>
                                <td class="text-right text-xs font-semibold py-2 px-3 border-l border-t border-white"
                                    style="min-width: 10rem;"><span class="block flex justify-end"><span><strong
                                                class="block truncate">--</strong> <!----></span> </span></td>
                            </tr>
                        </x-slot:foot>
                    </x-data-table>
                </div>
            </div>
        </div>

        <div id="view-capacity" class="hidden flex-col flex-1 bg-slate-50 p-6 relative">
            <div class="bg-white rounded-lg shadow-sm w-full p-5 border border-slate-200 flex flex-col flex-1">

                {{-- Header Actions --}}
                <div class="flex items-center justify-between mb-4 flex-shrink-0">
                    <div class="flex items-center gap-1 text-xs">
                        <span class="px-3 py-1.5 font-semibold text-emerald-700 bg-emerald-100 rounded-sm">Top
                            20%</span>
                        <span class="px-3 py-1.5 font-semibold text-yellow-700 bg-yellow-100 rounded-sm">Mid Tier</span>
                        <span class="px-3 py-1.5 font-semibold text-red-700 bg-red-100/70 rounded-sm">Bottom 20%</span>
                    </div>

                    <div class="flex items-center gap-3 text-sm">
                        <div class="relative">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <input type="text" id="capacitySearch" placeholder="Search"
                                class="border border-slate-300 pl-9 pr-3 py-1.5 rounded-sm text-slate-700 w-48 focus:outline-emerald-500">
                        </div>
                        <button id="exportCapacityCsvBtn" onclick="exportAppointmentCapacityCsv()"
                            class="border border-emerald-500 text-emerald-600 font-semibold px-4 py-1.5 rounded-sm hover:bg-emerald-50 transition shadow-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export CSV
                        </button>
                    </div>
                </div>

                {{-- Table UI --}}
                <div class="flex-1 w-full relative">
                    <x-table-skeleton />
                    <x-data-table id="appointmentCapacityTable" min-width="1200px" max-height="100%">
                        <x-slot:head>
                            <tr class="bg-slate-100 border-b border-t border-slate-200">
                                <th class="text-xs font-bold text-slate-700 py-3 pl-4 pr-3 dt-col-sticky text-left border-r border-slate-200"
                                    style="width: 14rem;">
                                    Location
                                </th>
                                <th
                                    class="text-xs font-bold text-slate-700 py-3 px-3 text-center border-r border-slate-200 w-40">
                                    <div class="flex flex-col items-center gap-0.5 justify-center">
                                        <div class="flex items-center gap-1"><span>Scheduled Appointments</span> <svg
                                                class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="10" stroke-width="2" />
                                                <path d="M12 16v-4m0-4h.01" stroke-width="2" stroke-linecap="round" />
                                            </svg></div>
                                    </div>
                                </th>
                                <th
                                    class="text-xs font-bold text-slate-700 py-3 px-3 text-center border-r border-slate-200 w-32">
                                    <div class="flex flex-col items-center gap-0.5 justify-center">
                                        <div class="flex items-center gap-1"><span># of Providers</span> <svg
                                                class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="10" stroke-width="2" />
                                                <path d="M12 16v-4m0-4h.01" stroke-width="2" stroke-linecap="round" />
                                            </svg></div>
                                    </div>
                                </th>
                                <th
                                    class="text-xs font-bold text-slate-700 py-3 px-3 text-center border-r border-slate-200 w-32">
                                    <div class="flex flex-col items-center gap-0.5 justify-center">
                                        <div class="flex items-center gap-1"><span>Booked Hours</span> <svg
                                                class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="10" stroke-width="2" />
                                                <path d="M12 16v-4m0-4h.01" stroke-width="2" stroke-linecap="round" />
                                            </svg></div>
                                    </div>
                                </th>
                                <th
                                    class="text-xs font-bold text-slate-700 py-3 px-3 text-center border-r border-slate-200 w-48">
                                    <div class="flex flex-col items-center gap-0.5 justify-center">
                                        <div class="flex items-center gap-1"><span>Avg. Lead Time - All<br>Appointments
                                                (days)</span> <svg class="w-3.5 h-3.5 text-slate-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="10" stroke-width="2" />
                                                <path d="M12 16v-4m0-4h.01" stroke-width="2" stroke-linecap="round" />
                                            </svg></div>
                                    </div>
                                </th>
                                <th
                                    class="text-xs font-bold text-slate-700 py-3 px-3 text-center border-r border-slate-200 w-48">
                                    <div class="flex flex-col items-center gap-0.5 justify-center">
                                        <div class="flex items-center gap-1"><span>Avg. Lead Time - New<br>Patient
                                                Appointments (days)</span> <svg class="w-3.5 h-3.5 text-slate-400"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="10" stroke-width="2" />
                                                <path d="M12 16v-4m0-4h.01" stroke-width="2" stroke-linecap="round" />
                                            </svg></div>
                                    </div>
                                </th>
                                <th class="text-xs font-bold text-slate-700 py-3 px-3 text-center w-48">
                                    <div class="flex flex-col items-center gap-0.5 justify-center">
                                        <div class="flex items-center gap-1"><span>Avg. Lead Time -<br>Emergency
                                                Appointments (days)</span> <svg class="w-3.5 h-3.5 text-slate-400"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="10" stroke-width="2" />
                                                <path d="M12 16v-4m0-4h.01" stroke-width="2" stroke-linecap="round" />
                                            </svg></div>
                                    </div>
                                </th>
                            </tr>
                        </x-slot:head>

                        <x-slot:foot>
                            <tr class="bg-gray-50 border-t border-slate-200">
                                <td class="text-left font-bold text-xs py-3 px-4 dt-col-sticky border-r border-slate-200 shadow-sm"
                                    style="width:14rem">Total:</td>
                                <td onclick="openCapacityBreakdown('scheduled_appointments')"
                                    class="text-right font-bold text-xs py-3 px-6 border-r border-slate-200 capacity-total-1 cursor-pointer hover:bg-slate-100 transition">
                                    -</td>
                                <td onclick="openCapacityBreakdown('provider_count')"
                                    class="text-right font-bold text-xs py-3 px-6 border-r border-slate-200 capacity-total-2 cursor-pointer hover:bg-slate-100 transition">
                                    -</td>
                                <td onclick="openCapacityBreakdown('booked_hours')"
                                    class="text-right font-bold text-xs py-3 px-6 border-r border-slate-200 capacity-total-3 cursor-pointer hover:bg-slate-100 transition">
                                    -</td>
                                <td class="border-r border-slate-200 bg-white"></td>
                                <td class="border-r border-slate-200 bg-white"></td>
                                <td class="bg-white"></td>
                            </tr>
                            <tr class="bg-white border-t border-slate-50">
                                <td class="text-left font-bold text-xs py-3 px-4 dt-col-sticky border-r border-slate-200 shadow-sm"
                                    style="width:14rem">Average:</td>
                                <td onclick="openCapacityBreakdown('scheduled_appointments')"
                                    class="text-right font-bold text-xs py-3 px-6 border-r border-slate-200 capacity-avg-1 cursor-pointer hover:bg-slate-50 transition">
                                    -</td>
                                <td onclick="openCapacityBreakdown('provider_count')"
                                    class="text-right font-bold text-xs py-3 px-6 border-r border-slate-200 capacity-avg-2 cursor-pointer hover:bg-slate-50 transition">
                                    -</td>
                                <td onclick="openCapacityBreakdown('booked_hours')"
                                    class="text-right font-bold text-xs py-3 px-6 border-r border-slate-200 capacity-avg-3 cursor-pointer hover:bg-slate-50 transition">
                                    -</td>
                                <td onclick="openCapacityBreakdown('avg_lead_all')"
                                    class="text-right font-bold text-xs py-3 px-6 border-r border-slate-200 capacity-avg-4 cursor-pointer hover:bg-slate-50 transition">
                                    -</td>
                                <td onclick="openCapacityBreakdown('avg_lead_new')"
                                    class="text-right font-bold text-xs py-3 px-6 border-r border-slate-200 capacity-avg-5 cursor-pointer hover:bg-slate-50 transition">
                                    -</td>
                                <td onclick="openCapacityBreakdown('avg_lead_emerg')"
                                    class="text-right font-bold text-xs py-3 px-6 capacity-avg-6 cursor-pointer hover:bg-slate-50 transition">-</td>
                            </tr>
                        </x-slot:foot>
                    </x-data-table>
                </div>
            </div>
        </div>

    </div>

    <script>
        const baseUrl = "{{ url('') }}";
        let calendar;

        // ── Skeleton builder ─────────────────────────────────────────────
        function buildSkeleton(viewType) {
            const currentView = viewType || (calendar && calendar.view ? calendar.view.type : 'resourceTimeGridDay');

            if (currentView === 'dayGridMonth') {
                const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                const headerCols = daysOfWeek.map(day => `
                    <div class="flex-1 py-2 text-center border-r border-slate-200 last:border-0 bg-slate-50">
                        <span class="text-xs font-bold text-slate-700">${day}</span>
                    </div>
                `).join('');

                let monthGridRows = '';
                for (let week = 0; week < 5; week++) {
                    let weekCells = '';
                    for (let d = 0; d < 7; d++) {
                        const delay = (week * 7 + d) * 20;
                        weekCells += `
                            <div class="border-r border-b border-slate-200 last:border-r-0 p-2 flex flex-col justify-between min-h-[110px] bg-white">
                                <div class="flex justify-between items-start">
                                    <div class="h-3 w-4 bg-slate-200 rounded skel-pulse" style="animation-delay:${delay}ms"></div>
                                    <div class="flex flex-col items-end gap-1">
                                        <div class="h-2 w-12 bg-slate-100 rounded skel-pulse" style="animation-delay:${delay + 10}ms"></div>
                                        <div class="h-2.5 w-6 bg-slate-200 rounded skel-pulse" style="animation-delay:${delay + 20}ms"></div>
                                    </div>
                                </div>
                                <div class="space-y-1 mt-2">
                                    <div class="flex items-center justify-between">
                                        <div class="h-2 w-10 bg-slate-100 rounded skel-pulse"></div>
                                        <div class="h-2 w-4 bg-slate-200 rounded skel-pulse"></div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="h-2 w-8 bg-slate-100 rounded skel-pulse"></div>
                                        <div class="h-2 w-12 bg-emerald-100 rounded skel-pulse"></div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="h-2 w-8 bg-slate-100 rounded skel-pulse"></div>
                                        <div class="h-2 w-10 bg-slate-200 rounded skel-pulse"></div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="h-2 w-8 bg-slate-100 rounded skel-pulse"></div>
                                        <div class="h-2 w-12 bg-emerald-200 rounded skel-pulse"></div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    monthGridRows += `<div class="grid grid-cols-7 flex-1">${weekCells}</div>`;
                }

                return `
<div id="cal-skeleton" class="absolute inset-0 z-30 bg-white flex flex-col overflow-hidden">
    <div class="flex-shrink-0 px-3 pt-3 pb-2">
        <div class="flex items-center justify-between mb-1.5">
            <span id="skel-label" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Loading Month...</span>
            <span id="skel-pct" class="text-sm font-bold text-emerald-600 tabular-nums">0%</span>
        </div>
        <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
            <div id="skel-bar" class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-teal-500" style="width:0%"></div>
        </div>
    </div>
    <div class="flex-shrink-0 flex border-y border-slate-200 bg-slate-50">
        ${headerCols}
    </div>
    <div class="flex flex-col flex-1 overflow-hidden">
        ${monthGridRows}
    </div>
</div>`;
            }

            const numCols = currentView === 'resourceTimeGridWeek' ? 7 : 5;
            const slotH = 32;
            const times = ['6:00 AM', '6:30 AM', '7:00 AM', '7:30 AM', '8:00 AM', '8:30 AM',
                '9:00 AM', '9:30 AM', '10:00 AM', '10:30 AM', '11:00 AM', '11:30 AM'];

            const headerCols = Array.from({ length: numCols }, (_, i) => `
        <div class="flex-1 flex flex-col items-center gap-1.5 py-3 border-r border-slate-200 last:border-0">
            <div class="h-2 w-8 bg-slate-200 rounded skel-pulse" style="animation-delay:${i * 80}ms"></div>
            <div class="h-3 w-14 bg-slate-200 rounded skel-pulse" style="animation-delay:${i * 80 + 40}ms"></div>
        </div>`).join('');

            const timeRows = times.map(t => `
        <div class="flex-shrink-0 flex items-start justify-end pr-2 pt-0.5 border-b border-slate-100" style="height:${slotH}px">
            <div class="h-2 w-10 bg-slate-100 rounded skel-pulse"></div>
        </div>`).join('');

            const gridRows = times.map(() =>
                `<div class="border-b border-slate-100" style="height:${slotH}px;grid-column:1/-1;"></div>`
            ).join('');

            const fakeApts = [
                { col: 0, start: 2, span: 2 }, { col: 1, start: 4, span: 3 },
                { col: 2, start: 1, span: 2 }, { col: 3, start: 5, span: 2 },
                { col: 4, start: 3, span: 4 }, { col: 1, start: 8, span: 2 },
                { col: Math.min(numCols - 1, 5), start: 2, span: 3 },
                { col: Math.min(numCols - 1, 6), start: 6, span: 2 },
            ].filter(a => a.col < numCols).map(({ col, start, span }) => {
                const colW = 100 / numCols;
                const leftPc = col * colW;
                const inner = span > 1
                    ? `<div class="h-2 w-14 bg-emerald-100 rounded mt-1 skel-pulse"></div>` : '';
                return `<div class="absolute rounded skel-pulse overflow-hidden"
                     style="top:${start * slotH + 2}px;height:${span * slotH - 4}px;
                            left:calc(${leftPc}% + 3px);width:calc(${colW}% - 6px);
                            background:linear-gradient(160deg,#d1fae5,#a7f3d0);
                            border-left:3px solid #10b981;">
                    <div class="p-1.5">
                        <div class="h-2.5 w-20 bg-emerald-200 rounded skel-pulse"></div>
                        ${inner}
                    </div>
                </div>`;
            }).join('');

            return `
<div id="cal-skeleton" class="absolute inset-0 z-30 bg-white flex flex-col overflow-hidden">
    <div class="flex-shrink-0 px-3 pt-3 pb-2">
        <div class="flex items-center justify-between mb-1.5">
            <span id="skel-label" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Loading...</span>
            <span id="skel-pct" class="text-sm font-bold text-emerald-600 tabular-nums">0%</span>
        </div>
        <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
            <div id="skel-bar" class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-teal-500" style="width:0%"></div>
        </div>
    </div>
    <div class="flex-shrink-0 flex border-y border-slate-200 bg-slate-50">
        <div class="w-16 flex-shrink-0 border-r border-slate-200"></div>
        <div class="flex-1 flex">${headerCols}</div>
    </div>
    <div class="flex flex-1 overflow-hidden">
        <div class="w-16 flex-shrink-0 border-r border-slate-200 flex flex-col overflow-hidden bg-white">
            ${timeRows}
        </div>
        <div class="flex-1 overflow-hidden relative">
            <div class="absolute inset-0 grid pointer-events-none" style="grid-template-columns:repeat(${numCols},1fr)">
                ${gridRows}
            </div>
            ${fakeApts}
        </div>
    </div>
</div>`;
        }

        // ── Progress helpers ─────────────────────────────────────────────
        function setProgress(pct, label) {
            const bar = document.getElementById('skel-bar');
            const pctEl = document.getElementById('skel-pct');
            const lblEl = document.getElementById('skel-label');
            if (!bar) return;
            bar.style.width = pct + '%';
            if (pctEl) pctEl.textContent = pct + '%';
            if (lblEl && label) lblEl.textContent = label;
            if (pct >= 100) setTimeout(hideSkeleton, 420);
        }

        function hideSkeleton() {
            const skel = document.getElementById('cal-skeleton');
            if (!skel) return;
            skel.style.opacity = '0';
            setTimeout(() => skel.remove(), 460);
        }

        function showCalSkeleton(label, viewType) {
            let skel = document.getElementById('cal-skeleton');
            if (skel) {
                skel.remove();
            }
            const wrap = document.getElementById('calendar-wrap');
            if (wrap) {
                wrap.insertAdjacentHTML('afterbegin', buildSkeleton(viewType));
                skel = document.getElementById('cal-skeleton');
            }
            if (skel) {
                skel.style.opacity = '1';
                setProgress(10, label || 'Loading...');
            }
        }

        // ── View Date Range Helper ────────────────────────────────────────
        function getViewDateRange(view) {
            const viewType = view?.type || 'resourceTimeGridDay';
            const fmt = d => {
                const y = d.getFullYear();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${y}-${m}-${day}`;
            };

            if (viewType === 'dayGridMonth') {
                const cur = view.currentStart || view.activeStart || new Date();
                const firstDay = new Date(cur.getFullYear(), cur.getMonth(), 1);
                const lastDay = new Date(cur.getFullYear(), cur.getMonth() + 1, 0);
                return {
                    type: 'month',
                    start: fmt(firstDay),
                    end: fmt(lastDay),
                    label: 'Month'
                };
            } else if (viewType === 'resourceTimeGridWeek') {
                const aStart = view.activeStart || view.currentStart || new Date();
                const aEnd = view.activeEnd ? new Date(view.activeEnd.getTime() - 86400000) : aStart;
                return {
                    type: 'week',
                    start: fmt(aStart),
                    end: fmt(aEnd),
                    label: 'Week'
                };
            } else {
                const cur = view.currentStart || view.activeStart || new Date();
                const dateStr = fmt(cur);
                return {
                    type: 'day',
                    start: dateStr,
                    end: dateStr,
                    label: 'Day'
                };
            }
        }

        const STATUS_MAP = {
            1: 'Scheduled', 2: 'Complete', 3: 'UnschedList',
            4: 'ASAP', 5: 'Broken', 6: 'Planned',
            7: 'PtNote', 8: 'PtNoteCompleted'
        };

        let currentCalDate = null;

        document.addEventListener('DOMContentLoaded', function () {

            const calEl = document.getElementById('calendar');

            // Show skeleton immediately before the calendar even starts constructing
            showCalSkeleton('Initializing...', 'resourceTimeGridDay');

            calendar = new FullCalendar.Calendar(calEl, {
                schedulerLicenseKey: 'CC-Attribution-NonCommercialNoDerivatives',
                initialView: 'resourceTimeGridDay',
                initialDate: '{{ date("Y-m-d") }}',
                headerToolbar: false,
                nowIndicator: true,
                slotDuration: '00:30:00',
                slotMinTime: '06:00:00',
                slotMaxTime: '20:00:00',
                height: 'auto',
                expandRows: false,
                allDaySlot: false,
                resourceOrder: 'title',

                // ── Resources (provider columns) ──────────────────────────
                resources: function (info, success, fail) {
                    setProgress(20, 'Loading providers...');
                    const date = (info.startStr || info.start?.toISOString() || document.getElementById('calDate').value || '{{ date("Y-m-d") }}').substring(0, 10);
                    const activeOnly = document.getElementById('activeColumnsToggle').checked ? '1' : '0';
                    fetch(baseUrl + '/calendar/resources?date=' + date + '&active_only=' + activeOnly)
                        .then(r => r.json())
                        .then(data => {
                            console.log('[FC Resources]', data);
                            setProgress(48, 'Loading appointments...');
                            success(data);
                        })
                        .catch(err => { setProgress(100, 'Failed to load providers'); fail(err); });
                },

                // ── Events (appointments) ─────────────────────────────────
                events: function (info, success, fail) {
                    const currentViewType = this.view ? this.view.type : (info.view ? info.view.type : '');
                    if (currentViewType === 'dayGridMonth') {
                        success([]);
                        return;
                    }
                    setProgress(55, 'Fetching appointments...');
                    const start = (info.startStr || info.start?.toISOString() || '{{ date("Y-m-d") }}').substring(0, 10);
                    const end = info.end
                        ? new Date(info.end - 1).toISOString().substring(0, 10)
                        : start;
                    fetch(baseUrl + '/calendar/data?start=' + start + '&end=' + end)
                        .then(r => r.json())
                        .then(data => {
                            console.log('[FC Events] count:', data.length);
                            if (data.length) console.log('[FC Events] sample:', { id: data[0].id, title: data[0].title, start: data[0].start, resourceId: data[0].resourceId });
                            setProgress(82, 'Rendering...');
                            success(data);
                        })
                        .catch(err => { setProgress(100, 'Failed to load appointments'); fail(err); });
                },

                // ── Loading complete → finish progress bar ────────────────
                loading: function (isLoading) {
                    if (!isLoading) setProgress(100, 'Ready');
                },

                // ── Custom event card rendering ───────────────────────────
                eventContent: function (arg) {
                    const ext = arg.event.extendedProps;
                    const npBadge = ext.isNewPatient
                        ? '<span style="background:#ef4444;color:#ffffff;font-size:9.5px;padding:1px 5.5px;border-radius:3px;font-weight:800;margin-left:4px;display:inline-block;vertical-align:middle;">NP</span>'
                        : '';
                    const proc = ext.procedure
                        ? `<div style="font-size:10px;font-weight:600;margin-top:2px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;opacity:.95;">${ext.procedure}</div>`
                        : '';
                    const phone = ext.phone
                        ? `<div style="font-size:9.5px;margin-top:1.5px;opacity:.9;font-weight:500;">${ext.phone}</div>`
                        : '';

                    // Clean note text to prevent duplicate procedure code output on card
                    let noteText = (ext.note || '').trim();
                    if (ext.procedure && noteText) {
                        const cleanProc = ext.procedure.trim();
                        if (noteText.toLowerCase() === cleanProc.toLowerCase()) {
                            noteText = '';
                        } else if (cleanProc.length > 0) {
                            const escapedProc = cleanProc.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                            noteText = noteText.replace(new RegExp('^' + escapedProc + '\\s*', 'i'), '').trim();
                        }
                    }

                    const note = noteText
                        ? `<div style="font-size:9.5px;margin-top:1.5px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;text-overflow:ellipsis;opacity:.75;line-height:1.2;">${noteText}</div>`
                        : '';
                    return {
                        html: `<div style="padding:4px 6px;height:100%;overflow:hidden;box-sizing:border-box;display:flex;flex-direction:column;justify-content:flex-start;">
                           <div style="font-size:11.5px;font-weight:850;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                               ${arg.event.title}${npBadge}
                           </div>
                           ${proc}
                           ${phone}
                           ${note}
                       </div>`
                    };
                },

                // ── Resource column headers ───────────────────────────────
                resourceLabelContent: function (arg) {
                    return {
                        html: `<div style="text-align:center;padding:6px 2px;">
                           <div style="font-size:11px;font-weight:700;color:#0f172a;">${arg.resource.title}</div>
                       </div>`
                    };
                },

                // ── On event click — open sidebar ─────────────────────────
                eventClick: function (info) {
                    showSidebar(info.event);
                    // Highlight selected event
                    document.querySelectorAll('.fc-event').forEach(el => el.style.opacity = '0.5');
                    info.el.style.opacity = '1';
                },

                // ── Keep date picker in sync + re-fetch resources for new date ─
                datesSet: function (info) {
                    const d = info.view.currentStart;

                    // Extract local year, month, and day without toISOString() 
                    // which modifies output based on timezone UTC offsets (e.g. -1 Day).
                    const year = d.getFullYear();
                    const month = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    const dateStr = `${year}-${month}-${day}`;

                    const dateChanged = currentCalDate !== null && currentCalDate !== dateStr;
                    currentCalDate = dateStr;

                    document.getElementById('calDate').value = dateStr;
                    updateDateLabel(d, info.view?.type);

                    const range = getViewDateRange(info.view);

                    if (info.view?.type === 'dayGridMonth') {
                        fetchMonthlySummary(info);
                        fetchCalendarStats(range.start, range.end, range.type);
                    } else {
                        if (dateChanged) {
                            calendar.refetchResources();
                        }
                        fetchCalendarStats(range.start, range.end, range.type);
                    }
                },
            });

            calendar.render();

            // initialise date label & clock
            updateDateLabel(new Date('{{ date("Y-m-d") }}T00:00:00'), calendar.view?.type);
            startClock();

            // ── Active Columns Toggle ───────────────────────────────────────
            document.getElementById('activeColumnsToggle').addEventListener('change', function () {
                showCalSkeleton('Updating columns...', calendar?.view?.type);
                calendar.refetchResources();
            });

            // ── Navigation buttons ────────────────────────────────────────
            document.getElementById('prevBtn').addEventListener('click', () => {
                showCalSkeleton('Loading...', calendar?.view?.type);
                calendar.prev();
            });
            document.getElementById('nextBtn').addEventListener('click', () => {
                showCalSkeleton('Loading...', calendar?.view?.type);
                calendar.next();
            });

            // ── Refresh ───────────────────────────────────────────────────
            document.getElementById('refreshBtn').addEventListener('click', () => {
                const activeTab = document.querySelector('.cal-tab.font-bold')?.getAttribute('data-target');
                if (activeTab === 'view-details') {
                    if (aptDetailsTable) aptDetailsTable.ajax.reload();
                } else if (activeTab === 'view-capacity') {
                    if (aptCapacityTable) aptCapacityTable.ajax.reload();
                } else {
                    const range = getViewDateRange(calendar?.view);
                    showCalSkeleton('Refreshing...', calendar?.view?.type);
                    if (calendar?.view?.type === 'dayGridMonth') {
                        fetchMonthlySummary(calendar.view);
                        fetchCalendarStats(range.start, range.end, range.type);
                    } else {
                        calendar.refetchEvents();
                        calendar.refetchResources();
                        fetchCalendarStats(range.start, range.end, range.type);
                    }
                }
            });

            // ── Date picker ───────────────────────────────────────────────
            document.getElementById('calDate').addEventListener('change', function () {
                showCalSkeleton('Loading date...', calendar?.view?.type);
                calendar.gotoDate(this.value);
            });

            // ── View toggle ───────────────────────────────────────────────
            document.querySelectorAll('.view-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const targetView = this.dataset.view;
                    if (calendar && calendar.view && calendar.view.type === targetView) return;

                    document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const viewLabel = targetView === 'dayGridMonth'
                        ? 'Loading Month View...'
                        : (targetView === 'resourceTimeGridWeek' ? 'Loading Week View...' : 'Loading Day View...');
                    showCalSkeleton(viewLabel, targetView);

                    calendar.changeView(targetView);
                });
            });

            // ── Clicking month cell opens day view / clicking outside restores opacity ──
            document.getElementById('calendar-wrap').addEventListener('click', function (e) {
                if (calendar && calendar.view.type === 'dayGridMonth') {
                    const dayCell = e.target.closest('.fc-daygrid-day');
                    if (dayCell) {
                        const clickedDate = dayCell.getAttribute('data-date');
                        if (clickedDate) {
                            showCalSkeleton('Loading Day View...', 'resourceTimeGridDay');
                            calendar.gotoDate(clickedDate);
                            calendar.changeView('resourceTimeGridDay');
                            document.querySelectorAll('.view-btn').forEach(b => {
                                if (b.dataset.view === 'resourceTimeGridDay') {
                                    b.classList.add('active');
                                } else {
                                    b.classList.remove('active');
                                }
                            });
                        }
                    }
                } else {
                    if (!e.target.closest('.fc-event')) {
                        document.querySelectorAll('.fc-event').forEach(el => el.style.opacity = '1');
                    }
                }
            });
        });

        // ── Date label formatter ──────────────────────────────────────────
        function updateDateLabel(date, viewType) {
            if (viewType === 'dayGridMonth') {
                document.getElementById('calDateLabel').textContent = date.toLocaleDateString('en-US', {
                    month: 'long', year: 'numeric'
                });
            } else {
                document.getElementById('calDateLabel').textContent = date.toLocaleDateString('en-US', {
                    weekday: 'long', month: 'long', day: 'numeric', year: 'numeric'
                });
            }
        }

        // ── Month View Summary Helpers ────────────────────────────────────
        function formatMonthCurrency(val) {
            if (val === 0 || val === null || val === undefined) return '$ 0';
            const isNeg = val < 0;
            const absVal = Math.abs(val);
            const formatted = absVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            if (isNeg) {
                return `$ (${formatted})`;
            }
            return `$ ${formatted}`;
        }

        function fetchMonthlySummary(infoOrView) {
            setProgress(30, 'Loading monthly summary...');
            let startStr = '';
            let endStr = '';

            if (infoOrView) {
                if (infoOrView.startStr) {
                    startStr = infoOrView.startStr.substring(0, 10);
                } else if (infoOrView.activeStart) {
                    startStr = infoOrView.activeStart.toISOString().substring(0, 10);
                }
                if (infoOrView.endStr) {
                    endStr = infoOrView.endStr.substring(0, 10);
                } else if (infoOrView.activeEnd) {
                    endStr = new Date(infoOrView.activeEnd.getTime() - 86400000).toISOString().substring(0, 10);
                }
            }

            if (!startStr) {
                startStr = document.getElementById('calDate').value || '{{ date("Y-m-d") }}';
            }
            if (!endStr) {
                endStr = startStr;
            }

            fetch(baseUrl + '/calendar/monthly-summary?start=' + startStr + '&end=' + endStr)
                .then(r => r.json())
                .then(data => {
                    renderMonthlySummary(data);
                    setProgress(100, 'Ready');
                })
                .catch(err => {
                    console.error('[Monthly Summary] Error:', err);
                    setProgress(100, 'Error loading summary');
                });
        }

        function renderMonthlySummary(data) {
            Object.keys(data).forEach(dateStr => {
                const dayCell = document.querySelector(`.fc-daygrid-day[data-date="${dateStr}"]`);
                if (!dayCell) return;

                const dayFrame = dayCell.querySelector('.fc-daygrid-day-frame');
                if (!dayFrame) return;

                const isOtherMonth = dayCell.classList.contains('fc-day-other');

                if (isOtherMonth) {
                    let metricsEl = dayFrame.querySelector('.month-day-metrics');
                    if (metricsEl) metricsEl.remove();

                    const dayTop = dayCell.querySelector('.fc-daygrid-day-top');
                    if (dayTop) {
                        dayTop.innerHTML = '';
                    }
                    const eventsEl = dayFrame.querySelector('.fc-daygrid-day-events');
                    if (eventsEl) eventsEl.style.display = 'none';
                    return;
                }

                const dayTop = dayCell.querySelector('.fc-daygrid-day-top');
                const dayNumberEl = dayCell.querySelector('.fc-daygrid-day-number');
                const dayNumText = dayNumberEl ? dayNumberEl.textContent.trim() : parseInt(dateStr.split('-')[2], 10);

                const dayData = data[dateStr];

                if (dayTop) {
                    dayTop.style.display = 'flex';
                    dayTop.style.justifyContent = 'space-between';
                    dayTop.style.alignItems = 'flex-start';
                    dayTop.style.padding = '4px 6px';
                    dayTop.style.flexDirection = 'row-reverse';

                    dayTop.innerHTML = `
                        <a class="fc-daygrid-day-number text-xs font-semibold text-slate-700 hover:text-emerald-600">${dayNumText}</a>
                        <div class="text-right leading-none">
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-tight">Appointments</div>
                            <div class="text-xs font-black text-slate-800">${dayData ? dayData.appointments : 0}</div>
                        </div>
                    `;
                }

                let metricsEl = dayFrame.querySelector('.month-day-metrics');
                if (metricsEl) metricsEl.remove();

                if (dayData) {
                    metricsEl = document.createElement('div');
                    metricsEl.className = 'month-day-metrics p-2 space-y-1 text-[11px] select-none';
                    metricsEl.innerHTML = `
                        <div class="flex items-center gap-1.5">
                            <span class="w-16 text-right text-slate-500 font-medium">New Pts:</span>
                            <span class="font-bold text-slate-900">${dayData.new_pts}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-16 text-right text-slate-500 font-medium">Sched:</span>
                            <span class="font-bold text-slate-900">${formatMonthCurrency(dayData.sched)}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-16 text-right text-slate-500 font-medium">Goal:</span>
                            <span class="font-bold text-slate-900">${formatMonthCurrency(dayData.goal)}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-16 text-right text-slate-500 font-medium">Prod:</span>
                            <span class="font-bold text-slate-900">${formatMonthCurrency(dayData.prod)}</span>
                        </div>
                    `;
                    dayFrame.appendChild(metricsEl);
                }

                const eventsEl = dayFrame.querySelector('.fc-daygrid-day-events');
                if (eventsEl) eventsEl.style.display = 'none';
            });
        }

        // ── Live clock ───────────────────────────────────────────────────
        function startClock() {
            function tick() {
                const now = new Date();
                const h = now.getHours();
                const m = now.getMinutes().toString().padStart(2, '0');
                const ampm = h >= 12 ? 'PM' : 'AM';
                document.getElementById('liveTime').textContent = `${(h % 12) || 12}:${m} ${ampm}`;
            }
            tick();
            setInterval(tick, 30000);
        }

        // ── Stats bar ─────────────────────────────────────────────────────
        // Production / Scheduled Production are computed server-side for the
        // selected date range (day, week, or month, see CalendarController@stats).
        function fetchCalendarStats(start, end, viewType) {
            const usd = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
            const prodEl = document.getElementById('stat-production');
            const schedEl = document.getElementById('stat-scheduled');

            const sDate = start || document.getElementById('calDate')?.value || '{{ date("Y-m-d") }}';
            const eDate = end || sDate;
            const vType = viewType || (calendar && calendar.view ? getViewDateRange(calendar.view).type : 'day');

            // Show animated skeleton pulse during loading
            if (prodEl) {
                prodEl.innerHTML = '<span class="inline-block h-6 w-24 bg-slate-200 rounded animate-pulse"></span>';
            }
            if (schedEl) {
                schedEl.innerHTML = '<span class="inline-block h-6 w-24 bg-slate-200 rounded animate-pulse"></span>';
            }

            // Update tooltip / subtitle based on view
            const periodName = vType === 'month' ? 'the month' : (vType === 'week' ? 'the week' : 'the day');
            const prodHelp = document.getElementById('stat-production-help');
            if (prodHelp) {
                prodHelp.setAttribute('title', `Display $ amount of what has been produced for ${periodName}`);
            }

            const schedTitle = document.getElementById('stat-scheduled-title');
            if (schedTitle) {
                schedTitle.innerHTML = vType === 'month'
                    ? 'Scheduled Production <span class="text-[10px] text-slate-400 font-normal">(Month)</span>'
                    : (vType === 'week' ? 'Scheduled Production <span class="text-[10px] text-slate-400 font-normal">(Week)</span>' : 'Scheduled Production');
            }

            fetch(baseUrl + '/calendar/stats?start=' + encodeURIComponent(sDate) + '&end=' + encodeURIComponent(eDate) + '&date=' + encodeURIComponent(sDate))
                .then(r => r.json())
                .then(s => {
                    if (prodEl) prodEl.textContent = usd.format(parseFloat(s.production) || 0);
                    if (schedEl) schedEl.textContent = usd.format(parseFloat(s.scheduled_production) || 0);
                    renderProviderHeader(s.providers, vType);
                })
                .catch(() => {
                    if (prodEl) prodEl.textContent = '—';
                    if (schedEl) schedEl.textContent = '—';
                    renderProviderHeader([], vType);
                });
        }

        // ── Provider Header Renderer ──────────────────────────────────────
        function renderProviderHeader(providers, viewType) {
            const container = document.getElementById('provider-header');
            if (!container) return;

            if (!providers || providers.length === 0) {
                const period = viewType === 'month' ? 'this month' : (viewType === 'week' ? 'this week' : 'today');
                container.innerHTML = `<p class="text-xs text-slate-400 py-1.5 align-middle">No active providers scheduled for ${period}</p>`;
                return;
            }

            container.innerHTML = providers.map(p => {
                const initialsColor = p.color;
                const textColor = initialsColor === '#6DE5C1' ? 'text-slate-800' : 'text-white';
                const specialtyText = p.specialty ? `${p.specialty} - ` : '';
                return `
                    <div class="flex items-center gap-2.5 bg-slate-50 border border-slate-200 rounded-full pl-2.5 pr-4 py-1.5 flex-shrink-0 shadow-sm transition hover:bg-slate-100">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm ${textColor}" style="background-color: ${initialsColor};">
                            ${p.initials}
                        </div>
                        <div class="flex flex-col">
                            <div class="text-xs font-bold text-slate-800">${p.name}</div>
                            <div class="text-[10px] text-slate-500 font-medium">${specialtyText}(${p.count})</div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // ── Sidebar: show ─────────────────────────────────────────────────
        function showSidebar(event) {
            const ext = event.extendedProps;

            const fmtTime = dt => dt ? dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : '';
            const start = fmtTime(event.start);
            const end = fmtTime(event.end);

            const status = STATUS_MAP[ext.status] || (ext.status ?? 'Unknown');
            const statusCls = status === 'Complete' ? 'bg-emerald-100 text-emerald-700'
                : status === 'Broken' ? 'bg-red-100 text-red-700'
                    : status === 'ASAP' ? 'bg-amber-100 text-amber-700'
                        : 'bg-blue-100 text-blue-700';

            const npBadge = ext.isNewPatient
                ? '<span class="ml-1 bg-emerald-100 text-emerald-700 text-[10px] font-bold px-1.5 py-0.5 rounded">New Patient</span>'
                : '';

            const noteBlock = ext.note ? `
        <div class="mt-3 pt-3 border-t border-slate-100">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Notes</p>
            <p class="text-xs text-slate-600 bg-slate-50 p-2 rounded border border-slate-100 leading-relaxed italic">${ext.note}</p>
        </div>` : '';

            document.getElementById('sidebar-body').innerHTML = `
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mb-4 shadow-sm">
            <div class="flex items-start justify-between mb-3 pb-2 border-b border-slate-200">
                <h4 class="text-sm font-bold text-slate-900 leading-tight">${event.title}${npBadge}</h4>
                <span class="text-[10px] text-slate-400 font-semibold whitespace-nowrap ml-2">Pat ID: ${ext.patNum || '—'}</span>
            </div>
            <div class="space-y-2 text-xs text-slate-600">
                <p><span class="font-bold text-slate-700">Appointment Date:</span> ${ext.date || '—'}</p>
                <p><span class="font-bold text-slate-700">Start Time:</span> ${start}</p>
                <p><span class="font-bold text-slate-700">End Time:</span> ${end}</p>
                <p><span class="font-bold text-slate-700">Operatory ID:</span> ${ext.operatoryId || '—'}</p>
                <p><span class="font-bold text-slate-700">Operatory Title:</span> ${ext.operatoryTitle || '—'}</p>
                <p class="flex items-center gap-1.5">
                    <span class="font-bold text-slate-700">Status:</span>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold ${statusCls}">${status}</span>
                </p>
                <div class="pt-2 border-t border-slate-100 space-y-2">
                    <p><span class="font-bold text-slate-700">Provider ID:</span> ${ext.providerId || '—'}</p>
                    <p><span class="font-bold text-slate-700">Provider Name:</span> ${ext.providerName || '—'}</p>
                    <p><span class="font-bold text-slate-700">Duration:</span> ${ext.duration || '—'} mins</p>
                    <p><span class="font-bold text-slate-700">Procedures:</span> ${ext.procedure || '—'}</p>
                </div>
            </div>
            ${noteBlock}
        </div>

        <div class="space-y-2">
            <button onclick="openPatient(${ext.patNum})"
               class="block w-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-2 rounded text-sm text-center transition shadow-sm cursor-pointer">
               View Patient
            </button>
            <button onclick="closeSidebar()"
                class="w-full bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-medium py-2 rounded text-xs transition">
                Close
            </button>
        </div>
    `;

            const sidebar = document.getElementById('apt-sidebar');
            sidebar.classList.remove('hidden');
            sidebar.classList.add('flex');
        }

        // ── Sidebar: close ────────────────────────────────────────────────
        function closeSidebar() {
            const sidebar = document.getElementById('apt-sidebar');
            sidebar.classList.add('hidden');
            sidebar.classList.remove('flex');
            document.querySelectorAll('.fc-event').forEach(el => el.style.opacity = '1');
        }

        function syncDateRangeFromSinglePicker() {
            const singleDate = document.getElementById('calDate')?.value || '{{ date("Y-m-d") }}';
            if (!singleDate) return;
            if (window.jQuery && jQuery('#calDateRange').data('daterangepicker') && typeof moment !== 'undefined') {
                const drp = jQuery('#calDateRange').data('daterangepicker');
                const m = moment(singleDate, 'YYYY-MM-DD');
                drp.setStartDate(m);
                drp.setEndDate(m);
                jQuery('#calDateRange').val(m.format('MMM D, YYYY') + ' – ' + m.format('MMM D, YYYY'));
            }
        }

        function getCalendarDateRange() {
            const activeTab = document.querySelector('.cal-tab.font-bold')?.getAttribute('data-target');
            if (activeTab === 'view-details' || activeTab === 'view-capacity') {
                const drp = window.jQuery && jQuery('#calDateRange').data('daterangepicker');
                if (drp && drp.startDate && drp.endDate) {
                    return {
                        start: drp.startDate.format('YYYY-MM-DD'),
                        end: drp.endDate.format('YYYY-MM-DD')
                    };
                }
            }
            const singleDate = document.getElementById('calDate')?.value || "{{ date('Y-m-d') }}";
            return { start: singleDate, end: singleDate };
        }

        window.onCalendarRangeApply = function(start, end) {
            const activeTab = document.querySelector('.cal-tab.font-bold')?.getAttribute('data-target');
            if (activeTab === 'view-details' && aptDetailsTable) {
                aptDetailsTable.ajax.reload();
            } else if (activeTab === 'view-capacity' && aptCapacityTable) {
                aptCapacityTable.ajax.reload();
            }
        };

        document.addEventListener('daterange:changed', function(e) {
            if (e.detail && e.detail.id === 'calDateRange') {
                window.onCalendarRangeApply(e.detail.start, e.detail.end);
            }
        });

        // ── Tabs logic ────────────────────────────────────────────────────
        document.querySelectorAll('.cal-tab').forEach(tab => {
            tab.addEventListener('click', function (e) {
                // Reset old tabs styles
                document.querySelectorAll('.cal-tab').forEach(t => {
                    t.classList.remove('font-bold', 'text-slate-900', 'border-emerald-500');
                    t.classList.add('font-medium', 'text-slate-400', 'border-transparent');
                });

                // Mark new tab as active
                this.classList.remove('font-medium', 'text-slate-400', 'border-transparent');
                this.classList.add('font-bold', 'text-slate-900', 'border-emerald-500');

                // Hide all views
                document.getElementById('view-calendar').classList.add('hidden');
                document.getElementById('view-calendar').classList.remove('flex');

                document.getElementById('view-details').classList.add('hidden');
                document.getElementById('view-details').classList.remove('flex');

                document.getElementById('view-capacity').classList.add('hidden');
                document.getElementById('view-capacity').classList.remove('flex');

                const target = this.getAttribute('data-target');

                // Toggle date selection input vs range picker & calendar view buttons based on tab
                const singleDateWrapper = document.getElementById('singleDateWrapper');
                const rangeDateWrapper = document.getElementById('rangeDateWrapper');
                const viewToggleWrapper = document.getElementById('viewToggleWrapper');

                if (target === 'view-calendar') {
                    singleDateWrapper?.classList.remove('hidden');
                    rangeDateWrapper?.classList.add('hidden');
                    viewToggleWrapper?.classList.remove('hidden');
                } else {
                    syncDateRangeFromSinglePicker();
                    singleDateWrapper?.classList.add('hidden');
                    rangeDateWrapper?.classList.remove('hidden');
                    viewToggleWrapper?.classList.add('hidden');
                }

                // Show target view
                const viewEl = document.getElementById(target);
                if (viewEl) {
                    viewEl.classList.remove('hidden');
                    viewEl.classList.add('flex');

                    // Re-render calendar if that tab was chosen and calendar exists
                    if (target === 'view-calendar' && calendar) {
                        setTimeout(() => calendar.render(), 10);
                    }

                    if (target === 'view-details') {
                        if (aptDetailsTable) {
                            aptDetailsTable.ajax.reload();
                        } else {
                            initAptDetailsTable();
                        }
                    }

                    if (target === 'view-capacity') {
                        if (aptCapacityTable) {
                            aptCapacityTable.ajax.reload();
                        } else {
                            initAptCapacityTable();
                        }
                    }
                }
            });
        });

        let aptDetailsTable = null;
        function initAptDetailsTable() {
            if (aptDetailsTable) return;

            // Populate provider dropdown if empty
            const provSelect = document.getElementById('detailsFilterProvider');
            if (provSelect && provSelect.options.length <= 1) {
                fetch(baseUrl + '/calendar/resources?active_only=0')
                    .then(r => r.json())
                    .then(providers => {
                        providers.forEach(p => {
                            if (p.id) {
                                const opt = document.createElement('option');
                                opt.value = p.id;
                                opt.textContent = p.title;
                                provSelect.appendChild(opt);
                            }
                        });
                    })
                    .catch(() => {});
            }

            aptDetailsTable = DDS.dataTable(document.getElementById('appointmentDetailsTable'), {
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('calendar.appointments-details-data') }}",
                    data: function (d) {
                        const range = getCalendarDateRange();
                        d.start = range.start;
                        d.end = range.end;
                        const provVal = $('#detailsFilterProvider').val();
                        if (provVal) d.provider_id = provVal;
                        const statusVal = $('#detailsFilterStatus').val();
                        if (statusVal) d.status = statusVal;
                    }
                },
                columns: [
                    { data: 'location', name: 'location' },
                    { data: 'patient_name', name: 'patient_name' },
                    { data: 'appointment_date', name: 'appointment_date' },
                    { data: 'appointment_time', name: 'appointment_time' },
                    { data: 'appointment_duration', name: 'appointment_duration' },
                    { data: 'operatory_name', name: 'operatory_name' },
                    { data: 'appointment_status', name: 'appointment_status' },
                    { data: 'patient_age', name: 'patient_age' },
                    { data: 'patient_phone', name: 'patient_phone' },
                    { data: 'email_address', name: 'email_address' },
                    { data: 'patient_type', name: 'patient_type' },
                    { data: 'appointment_notes', name: 'appointment_notes' },
                    { data: 'confirmation_status', name: 'confirmation_status' },
                    { data: 'provider_name', name: 'provider_name' },
                    { data: 'procedure_codes', name: 'procedure_codes' },
                    { data: 'production', name: 'production' },
                    { data: 'primary_insurance', name: 'primary_insurance' },
                    { data: 'secondary_insurance', name: 'secondary_insurance' },
                    { data: 'referral_source', name: 'referral_source' },
                    { data: 'unscheduled_tx', name: 'unscheduled_tx' },
                    { data: 'last_visit_date', name: 'last_visit_date' }
                ],
                dom: 'rt<"flex justify-between items-center px-4 py-3 border-t border-slate-200"ip>',
                pagingType: 'simple_numbers',
                pageLength: 25,
                language: {
                    paginate: {
                        previous: "Prev",
                        next: "Next"
                    },
                    processing: ""
                },
                createdRow: function (row, data, dataIndex) {
                    $(row).addClass('hover:bg-slate-50 transition-colors');
                    $('td', row).addClass('px-4 py-2.5 border-r border-slate-200 text-xs bg-white text-right');

                    // Style first three fixed columns
                    $('td:eq(0), td:eq(1), td:eq(2)', row).removeClass('text-right').addClass('dt-col-sticky text-left font-medium');
                    $('td:eq(0)', row).css('left', '0');
                    $('td:eq(1)', row).css('left', '12rem');
                    $('td:eq(2)', row).css('left', '24rem').addClass('shadow-[2px_0_5px_-2px_rgba(0,0,0,0.10)]');

                    // Left align specific text columns
                    $('td:eq(5), td:eq(9), td:eq(10), td:eq(11), td:eq(12), td:eq(13), td:eq(14), td:eq(16), td:eq(17), td:eq(18)', row).removeClass('text-right').addClass('text-left');

                    // Notes column truncation and hover attribute
                    $('td:eq(11)', row).addClass('notes-cell').attr('data-note', data.appointment_notes || '');
                },
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api();
                    var intVal = function (i) {
                        return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
                    };

                    // Average duration
                    var durationData = api.column(4, { page: 'current' }).data();
                    if (durationData.length > 0) {
                        var totalDur = durationData.reduce((a, b) => intVal(a) + intVal(b), 0);
                        $(api.column(4).footer()).html((totalDur / durationData.length).toFixed(2));
                    }

                    // Total production
                    var prodData = api.column(15, { page: 'current' }).data();
                    if (prodData.length > 0) {
                        var totalProd = prodData.reduce((a, b) => intVal(a) + intVal(b), 0);
                        $(api.column(15).footer()).html('$ ' + totalProd.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    }
                }
            });

            // Toggle Skeleton
            aptDetailsTable.on('processing.dt', function (e, settings, processing) {
                if (processing) {
                    $('#tableSkeleton').removeClass('hidden');
                } else {
                    $('#tableSkeleton').addClass('hidden');
                }
            });

            $('#calDate').on('change', function () {
                syncDateRangeFromSinglePicker();
                if (aptDetailsTable) aptDetailsTable.ajax.reload();
            });

            $('#detailsFilterProvider, #detailsFilterStatus').on('change', function () {
                if (aptDetailsTable) aptDetailsTable.ajax.reload();
            });

            $('#detailsSearch').on('keyup', function () {
                if (aptDetailsTable) aptDetailsTable.search(this.value).draw();
            });

            // Notes hover cards
            $(document).on('mouseenter', '.notes-cell', function (e) {
                const note = $(this).attr('data-note');
                if (!note || note === '—' || note.trim() === '') return;
                const $card = $('#notes-hover-card');
                $card.text(note);
                $card.removeClass('hidden');

                const rect = this.getBoundingClientRect();
                const cardWidth = $card.outerWidth();
                const cardHeight = $card.outerHeight();

                let top = rect.top - cardHeight - 8;
                let left = rect.left + (rect.width - cardWidth) / 2;

                if (top < 10) {
                    top = rect.bottom + 8;
                }
                if (left < 10) left = 10;
                if (left + cardWidth > window.innerWidth - 10) {
                    left = window.innerWidth - cardWidth - 10;
                }
                $card.css({
                    top: top + 'px',
                    left: left + 'px'
                });
            });

            $(document).on('mouseleave', '.notes-cell', function () {
                $('#notes-hover-card').addClass('hidden');
            });
        }

        let aptCapacityTable = null;
        function initAptCapacityTable() {
            if (aptCapacityTable) return;

            aptCapacityTable = DDS.dataTable(document.getElementById('appointmentCapacityTable'), {
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('calendar.appointment-capacity-data') }}",
                    data: function (d) {
                        const range = getCalendarDateRange();
                        d.start = range.start;
                        d.end = range.end;
                    }
                },
                columns: [
                    { data: 'location', name: 'location' },
                    { data: 'scheduled_appointments', name: 'scheduled_appointments' },
                    { data: 'provider_count', name: 'provider_count' },
                    { data: 'booked_hours', name: 'booked_hours' },
                    { data: 'avg_lead_all', name: 'avg_lead_all' },
                    { data: 'avg_lead_new', name: 'avg_lead_new' },
                    { data: 'avg_lead_emerg', name: 'avg_lead_emerg' }
                ],
                dom: 'rt<"flex justify-between items-center px-5 py-4 border-t border-slate-200 bg-white"ip>',
                pagingType: 'simple_numbers',
                pageLength: 10,
                language: { paginate: { previous: "Prev", next: "Next" }, processing: "" },
                createdRow: function (row, data, dataIndex) {
                    $(row).addClass('hover:bg-slate-50 transition-colors bg-white');
                    $('td', row).addClass('border-r border-slate-200 text-sm');
                    $('td:eq(0)', row).addClass('dt-col-sticky text-left font-medium px-4 py-3 bg-white shadow-sm border-white');

                    const columns = ['scheduled_appointments', 'provider_count', 'booked_hours', 'avg_lead_all', 'avg_lead_new', 'avg_lead_emerg'];

                    columns.forEach((col, index) => {
                        const td = $('td:eq(' + (index + 1) + ')', row);
                        const tier = data._tiers ? data._tiers[col] : 'mid';
                        td.addClass('px-4 py-3 text-right font-medium text-slate-800 cursor-pointer hover:opacity-80 transition');

                        let bgColor = 'bg-yellow-100/70';
                        let arrow = '<svg class="w-3.5 h-3.5 text-yellow-600 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>';

                        if (tier === 'top') {
                            bgColor = 'bg-[#c5f5dd]';
                            arrow = '<svg class="w-3.5 h-3.5 text-emerald-600 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>';
                        } else if (tier === 'bottom') {
                            bgColor = 'bg-[#ffcfd2]';
                            arrow = '<svg class="w-3.5 h-3.5 text-red-600 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>';
                        }

                        td.addClass(bgColor);
                        td.attr('onclick', "openCapacityBreakdown('" + col + "')");
                        const val = td.html();
                        td.html('<div class="flex items-center justify-end gap-2 w-full"><span class="truncate">' + val + '</span> <div class="border border-black/10 rounded-sm p-0.5 bg-black/5 flex-shrink-0">' + arrow + '</div></div>');
                    });
                },
                footerCallback: function (row, data, start, end, display) {
                    if (data.length > 0) {
                        const first = data[0];
                        ['scheduled_appointments', 'provider_count', 'booked_hours', 'avg_lead_all', 'avg_lead_new', 'avg_lead_emerg'].forEach((col, idx) => {
                            $('.capacity-total-' + (idx + 1)).text(first[col]);
                            $('.capacity-avg-' + (idx + 1)).text(first[col]);
                        });
                    }
                }
            });

            $('#calDate').on('change', function () {
                if (aptCapacityTable) aptCapacityTable.ajax.reload();
            });
            $('#capacitySearch').on('keyup', function () {
                if (aptCapacityTable) aptCapacityTable.search(this.value).draw();
            });
        }

        // ── CSV Export Helpers ───────────────────────────────────────────
        function downloadCsv(filename, rows) {
            const csvContent = rows.map(row =>
                row.map(cell => {
                    const str = (cell === null || cell === undefined) ? '' : String(cell);
                    return '"' + str.replace(/"/g, '""') + '"';
                }).join(',')
            ).join('\r\n');

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.setAttribute('download', filename);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function exportAppointmentDetailsCsv() {
            const btn = document.getElementById('exportDetailsCsvBtn');
            const originalHtml = btn ? btn.innerHTML : 'Export CSV';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = 'Exporting...';
            }

            const range = getCalendarDateRange();
            const url = `${baseUrl}/calendar/appointments-details-data?start=${range.start}&end=${range.end}&length=-1`;

            fetch(url)
                .then(r => r.json())
                .then(res => {
                    const data = res.data || [];
                    if (data.length === 0) {
                        alert('No appointment details found for the selected date range.');
                        return;
                    }

                    const headers = [
                        "Location", "Patient Name", "Appointment Date", "Appointment Time",
                        "Appointment Duration", "Operatory Name", "Appointment Status", "Patient Age",
                        "Patient Phone", "Email Address", "Patient Type", "Appointment Notes",
                        "Confirmation Status", "Provider Name", "Procedure Codes", "Production",
                        "Primary Insurance Carrier", "Secondary Insurance Carrier", "Referral Source",
                        "Unscheduled Tx $", "Last Visit Date"
                    ];

                    const rows = [headers];

                    let totalDuration = 0;
                    let totalProduction = 0;
                    let totalUnscheduled = 0;
                    const parseMoney = (val) => {
                        if (!val) return 0;
                        const num = parseFloat(String(val).replace(/[^0-9.-]+/g, ''));
                        return isNaN(num) ? 0 : num;
                    };

                    data.forEach(item => {
                        const dur = parseFloat(item.appointment_duration) || 0;
                        const prod = parseMoney(item.production);
                        const unsched = parseMoney(item.unscheduled_tx);

                        totalDuration += dur;
                        totalProduction += prod;
                        totalUnscheduled += unsched;

                        rows.push([
                            item.location || '',
                            item.patient_name || '',
                            item.appointment_date || '',
                            item.appointment_time || '',
                            item.appointment_duration || '',
                            item.operatory_name || '',
                            item.appointment_status || '',
                            item.patient_age || '',
                            item.patient_phone || '',
                            item.email_address || '',
                            item.patient_type || '',
                            item.appointment_notes || '',
                            item.confirmation_status || '',
                            item.provider_name || '',
                            item.procedure_codes || '',
                            item.production || '$ 0',
                            item.primary_insurance || 'N/A',
                            item.secondary_insurance || 'N/A',
                            item.referral_source || 'No Source Listed',
                            item.unscheduled_tx || '$ 0',
                            item.last_visit_date || 'N/A'
                        ]);
                    });

                    // Add summary footer rows matching Jarvis Analytics
                    const count = data.length;
                    const avgDuration = count > 0 ? (totalDuration / count).toFixed(2) : '0.00';
                    const avgProduction = count > 0 ? (totalProduction / count) : 0;
                    const avgUnscheduled = count > 0 ? (totalUnscheduled / count) : 0;

                    const formatMoney = (val) => {
                        if (val === 0) return '$ 0';
                        return '$ ' + Number(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    };

                    // Average row
                    rows.push([
                        "Average:", "-", "-", "-",
                        avgDuration,
                        "-", "-", "-", "-", "-", "-", "-", "-", "-", "-",
                        formatMoney(avgProduction),
                        "-", "-", "-",
                        formatMoney(avgUnscheduled),
                        "-"
                    ]);

                    // Total row
                    rows.push([
                        "Total:", "-", "-", "-",
                        Number(totalDuration).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                        "-", "-", "-", "-", "-", "-", "-", "-", "-", "-",
                        formatMoney(totalProduction),
                        "-", "-", "-",
                        formatMoney(totalUnscheduled),
                        "-"
                    ]);

                    downloadCsv(`appointment-details-${range.start}-to-${range.end}.csv`, rows);
                })
                .catch(err => {
                    console.error('Failed to export CSV:', err);
                    alert('Failed to export CSV. Please try again.');
                })
                .finally(() => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                });
        }

        function exportAppointmentCapacityCsv() {
            const btn = document.getElementById('exportCapacityCsvBtn');
            const originalHtml = btn ? btn.innerHTML : 'Export CSV';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = 'Exporting...';
            }

            const range = getCalendarDateRange();
            const url = `${baseUrl}/calendar/appointment-capacity-data?start=${range.start}&end=${range.end}&length=-1`;

            fetch(url)
                .then(r => r.json())
                .then(res => {
                    const data = res.data || [];
                    if (data.length === 0) {
                        alert('No capacity data found for the selected date range.');
                        return;
                    }

                    const headers = [
                        "Location", "Scheduled Appointments", "# of Providers", "Booked Hours",
                        "Avg. Lead Time - All (days)", "Avg. Lead Time - New (days)", "Avg. Lead Time - Emergency (days)"
                    ];

                    const rows = [headers];
                    data.forEach(item => {
                        rows.push([
                            item.location || '',
                            item.scheduled_appointments || 0,
                            item.provider_count || 0,
                            item.booked_hours || 0,
                            item.avg_lead_all || 0,
                            item.avg_lead_new || 0,
                            item.avg_lead_emerg || 0
                        ]);
                    });

                    downloadCsv(`appointment-capacity-${range.start}-to-${range.end}.csv`, rows);
                })
                .catch(err => {
                    console.error('Failed to export capacity CSV:', err);
                    alert('Failed to export CSV. Please try again.');
                })
                .finally(() => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                });
        }

        // ── Appointment Capacity Breakdown Modal ─────────────────────────
        function openCapacityBreakdown(type) {
            const range = getCalendarDateRange();
            const date = range.start || "{{ date('Y-m-d') }}";

            let title = 'Capacity Breakdown';
            let columns = [];

            const patientRender = function (data, t, row) {
                if (row.patient_id && row.patient_id !== 'N/A' && row.patient_id !== 0) {
                    return `<a href="javascript:void(0)" onclick="openPatient(${row.patient_id})" class="text-emerald-600 hover:text-emerald-700 font-semibold hover:underline">${data}</a>`;
                }
                return data || '—';
            };

            if (type === 'scheduled_appointments') {
                title = 'Scheduled Appointments Breakdown';
                columns = [
                    { data: 'patient', title: 'Patient', render: patientRender },
                    { data: 'patient_id', title: 'Patient ID' },
                    { data: 'date', title: 'Date' },
                    { data: 'provider', title: 'Provider' },
                    { data: 'provider_id', title: 'Provider ID' }
                ];
            } else if (type === 'provider_count') {
                title = '# of Providers Breakdown';
                columns = [
                    { data: 'provider_name', title: 'Provider Name' },
                    { data: 'provider_id', title: 'Provider ID' }
                ];
            } else if (type === 'booked_hours') {
                title = 'Booked Hours Breakdown';
                columns = [
                    { data: 'patient', title: 'Patient', render: patientRender },
                    { data: 'patient_id', title: 'Patient ID' },
                    { data: 'duration', title: 'Duration (hrs)' },
                    { data: 'provider', title: 'Provider' },
                    { data: 'provider_id', title: 'Provider ID' }
                ];
            } else if (type === 'avg_lead_all') {
                title = 'Avg. Lead Time - All Appointments Breakdown';
                columns = [
                    { data: 'patient', title: 'Patient', render: patientRender },
                    { data: 'patient_id', title: 'Patient ID' },
                    { data: 'lead_time', title: 'Lead Time (days)' },
                    { data: 'provider', title: 'Provider' },
                    { data: 'provider_id', title: 'Provider ID' }
                ];
            } else if (type === 'avg_lead_new') {
                title = 'Avg. Lead Time - New Patients Breakdown';
                columns = [
                    { data: 'patient', title: 'Patient', render: patientRender },
                    { data: 'patient_id', title: 'Patient ID' },
                    { data: 'lead_time', title: 'Lead Time (days)' },
                    { data: 'provider', title: 'Provider' },
                    { data: 'provider_id', title: 'Provider ID' }
                ];
            } else if (type === 'avg_lead_emerg') {
                title = 'Avg. Lead Time - Emergency Breakdown';
                columns = [
                    { data: 'patient', title: 'Patient', render: patientRender },
                    { data: 'patient_id', title: 'Patient ID' },
                    { data: 'lead_time', title: 'Lead Time (days)' },
                    { data: 'provider', title: 'Provider' },
                    { data: 'provider_id', title: 'Provider ID' }
                ];
            }

            setDataTableModalLoading('capacity-breakdown-modal', title);

            fetch(`${baseUrl}/calendar/capacity-breakdown?start=${encodeURIComponent(range.start)}&end=${encodeURIComponent(range.end)}&date=${encodeURIComponent(date)}&type=${encodeURIComponent(type)}`)
                .then(r => r.json())
                .then(data => {
                    openDataTableModal('capacity-breakdown-modal', title, columns, data);
                })
                .catch(err => {
                    console.error('Failed to load capacity breakdown data:', err);
                });
        }

        // ── Scheduled Production Breakdown Modal ─────────────────────────
        let rawSchedData = null;

        function openScheduledProductionModal() {
            const range = calendar && calendar.view
                ? getViewDateRange(calendar.view)
                : { start: document.getElementById('calDate')?.value || '{{ date("Y-m-d") }}', end: document.getElementById('calDate')?.value || '{{ date("Y-m-d") }}' };
            const modal = document.getElementById('scheduled-prod-modal');
            modal.classList.remove('hidden');

            document.getElementById('sched-modal-date').textContent = 'Loading...';
            document.getElementById('sched-modal-total').textContent = '…';
            document.getElementById('sched-modal-count').textContent = '…';
            document.getElementById('sched-modal-prov-count').textContent = '…';
            document.getElementById('sched-view-provider').innerHTML = '<div class="p-8 text-center text-xs text-slate-400">Loading breakdown data...</div>';

            fetch(baseUrl + '/calendar/scheduled-production-breakdown?start=' + encodeURIComponent(range.start) + '&end=' + encodeURIComponent(range.end) + '&date=' + encodeURIComponent(range.start))
                .then(r => r.json())
                .then(data => {
                    rawSchedData = data;
                    const usd = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });

                    document.getElementById('sched-modal-date').textContent = data.date;
                    document.getElementById('sched-modal-total').textContent = usd.format(data.total_scheduled || 0);
                    document.getElementById('sched-modal-count').textContent = data.appointment_count;
                    document.getElementById('sched-modal-prov-count').textContent = data.by_provider.length;

                    renderSchedProviderView(data.by_provider, data.total_scheduled);
                    renderSchedProcedureView(data.by_procedure, data.total_scheduled);
                    renderSchedAppointmentsTable(data.appointments);
                })
                .catch(err => {
                    document.getElementById('sched-modal-date').textContent = 'Error loading breakdown';
                });
        }

        function closeSchedProdModal() {
            document.getElementById('scheduled-prod-modal').classList.add('hidden');
        }

        function switchSchedTab(tabName) {
            document.querySelectorAll('.sched-modal-tab').forEach(t => {
                t.classList.remove('font-bold', 'text-slate-900', 'border-emerald-500');
                t.classList.add('font-medium', 'text-slate-400', 'border-transparent');
            });
            const activeTab = document.getElementById('sched-tab-' + tabName);
            if (activeTab) {
                activeTab.classList.remove('font-medium', 'text-slate-400', 'border-transparent');
                activeTab.classList.add('font-bold', 'text-slate-900', 'border-emerald-500');
            }

            document.querySelectorAll('.sched-modal-view').forEach(v => v.classList.add('hidden'));
            const targetView = document.getElementById('sched-view-' + tabName);
            if (targetView) targetView.classList.remove('hidden');
        }

        function renderSchedProviderView(providers, grandTotal) {
            const container = document.getElementById('sched-view-provider');
            if (!providers || providers.length === 0) {
                container.innerHTML = '<div class="p-6 text-center text-xs text-slate-400">No scheduled providers found for this date</div>';
                return;
            }

            const usd = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
            container.innerHTML = providers.map(p => {
                const pct = grandTotal > 0 ? Math.round((p.total / grandTotal) * 100) : 0;
                return `
                    <div class="bg-white border border-slate-200 rounded-lg p-4 shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs">
                                    ${p.abbr.substring(0, 3)}
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-900">${p.name}</div>
                                    <div class="text-[10px] text-slate-500 font-medium">${p.count} appointment${p.count === 1 ? '' : 's'} scheduled</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-bold text-slate-900">${usd.format(p.total)}</div>
                                <div class="text-[10px] font-semibold text-emerald-600">${pct}% of total</div>
                            </div>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full rounded-full transition-all duration-500" style="width:${pct}%"></div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function renderSchedProcedureView(procedures, grandTotal) {
            const container = document.getElementById('sched-view-procedure');
            if (!procedures || procedures.length === 0) {
                container.innerHTML = '<div class="p-6 text-center text-xs text-slate-400">No procedure codes scheduled for this date</div>';
                return;
            }

            const usd = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
            container.innerHTML = procedures.map(pr => {
                const pct = grandTotal > 0 ? Math.round((pr.total / grandTotal) * 100) : 0;
                return `
                    <div class="bg-white border border-slate-200 rounded-lg p-3.5 shadow-sm flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-slate-900">${pr.code}</div>
                            <div class="text-[10px] text-slate-500">Frequency: ${pr.count} procedure${pr.count === 1 ? '' : 's'}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-bold text-slate-900">${usd.format(pr.total)}</div>
                            <div class="text-[10px] font-medium text-slate-400">${pct}%</div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function renderSchedAppointmentsTable(appointments) {
            const tbody = document.getElementById('sched-apts-tbody');
            if (!appointments || appointments.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-6 text-center text-xs text-slate-400">No scheduled appointments found for this date</td></tr>';
                return;
            }

            const usd = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });

            const drawRows = (items) => {
                if (items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-4 text-center text-xs text-slate-400">No matching appointments found</td></tr>';
                    return;
                }
                tbody.innerHTML = items.map(a => `
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-2.5 font-bold text-slate-900">${a.patient_name}</td>
                        <td class="px-4 py-2.5 font-medium text-slate-600">${a.time}</td>
                        <td class="px-4 py-2.5 text-slate-600">${a.operatory}</td>
                        <td class="px-4 py-2.5 font-medium text-slate-800">${a.provider}</td>
                        <td class="px-4 py-2.5 text-slate-600 max-w-xs truncate" title="${a.procedures}">${a.procedures}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-emerald-600">${usd.format(a.fee)}</td>
                    </tr>
                `).join('');
            };

            drawRows(appointments);

            const searchInput = document.getElementById('schedAptsSearch');
            if (searchInput) {
                searchInput.onkeyup = function () {
                    const q = this.value.toLowerCase().trim();
                    if (!q) {
                        drawRows(appointments);
                        return;
                    }
                    const filtered = appointments.filter(a =>
                        a.patient_name.toLowerCase().includes(q) ||
                        a.provider.toLowerCase().includes(q) ||
                        a.procedures.toLowerCase().includes(q) ||
                        a.operatory.toLowerCase().includes(q)
                    );
                    drawRows(filtered);
                };
            }
        }
    </script>

    <x-app-components.patient-modal />
    <x-app-components.datatable-modal id="capacity-breakdown-modal" />

    {{-- Scheduled Production Breakdown Modal --}}
    <div id="scheduled-prod-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative w-full max-w-4xl bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
            {{-- Modal Header --}}
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-emerald-500/20 text-emerald-400 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Scheduled Production Breakdown</h3>
                        <p class="text-xs text-slate-400 font-medium" id="sched-modal-date">—</p>
                    </div>
                </div>
                <button onclick="closeSchedProdModal()" class="text-slate-400 hover:text-white transition p-1.5 rounded-lg hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18" stroke-width="2" />
                        <line x1="6" y1="6" x2="18" y2="18" stroke-width="2" />
                    </svg>
                </button>
            </div>

            {{-- Summary Cards Header --}}
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 grid grid-cols-3 gap-4 flex-shrink-0">
                <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-sm">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Scheduled Production</p>
                    <p class="text-2xl font-black text-emerald-600" id="sched-modal-total">—</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-sm">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Scheduled Appointments</p>
                    <p class="text-2xl font-black text-slate-800" id="sched-modal-count">—</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-sm">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Active Providers</p>
                    <p class="text-2xl font-black text-slate-800" id="sched-modal-prov-count">—</p>
                </div>
            </div>

            {{-- Modal Navigation Tabs --}}
            <div class="bg-white border-b border-slate-200 px-6 flex-shrink-0">
                <nav class="flex gap-6">
                    <button onclick="switchSchedTab('provider')" id="sched-tab-provider" class="sched-modal-tab py-3 text-xs font-bold text-slate-900 border-b-2 border-emerald-500">
                        By Provider
                    </button>
                    <button onclick="switchSchedTab('procedure')" id="sched-tab-procedure" class="sched-modal-tab py-3 text-xs font-medium text-slate-400 border-b-2 border-transparent hover:text-slate-600 transition">
                        By Procedure
                    </button>
                    <button onclick="switchSchedTab('appointments')" id="sched-tab-appointments" class="sched-modal-tab py-3 text-xs font-medium text-slate-400 border-b-2 border-transparent hover:text-slate-600 transition">
                        Itemized Appointments
                    </button>
                </nav>
            </div>

            {{-- Modal Body Content --}}
            <div class="p-6 flex-1 overflow-y-auto bg-slate-50">
                {{-- Tab 1: By Provider --}}
                <div id="sched-view-provider" class="sched-modal-view space-y-3">
                    <!-- Rendered dynamically -->
                </div>

                {{-- Tab 2: By Procedure --}}
                <div id="sched-view-procedure" class="sched-modal-view hidden space-y-3">
                    <!-- Rendered dynamically -->
                </div>

                {{-- Tab 3: Itemized Appointments --}}
                <div id="sched-view-appointments" class="sched-modal-view hidden">
                    <div class="mb-3 flex justify-between items-center">
                        <input type="text" id="schedAptsSearch" placeholder="Search patient, provider, procedure..." class="border border-slate-300 rounded px-3 py-1.5 text-xs text-slate-700 bg-white w-64 focus:outline-emerald-500">
                    </div>
                    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="bg-slate-100 uppercase text-[10px] font-bold text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">Patient Name</th>
                                    <th class="px-4 py-3">Time</th>
                                    <th class="px-4 py-3">Operatory</th>
                                    <th class="px-4 py-3">Provider</th>
                                    <th class="px-4 py-3">Scheduled Procedures</th>
                                    <th class="px-4 py-3 text-right">Fee</th>
                                </tr>
                            </thead>
                            <tbody id="sched-apts-tbody" class="divide-y divide-slate-100">
                                <!-- Rendered dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-3 bg-white border-t border-slate-200 flex justify-end flex-shrink-0">
                <button onclick="closeSchedProdModal()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-5 py-1.5 rounded text-xs transition">
                    Close
                </button>
            </div>
        </div>
    </div>

    <div id="notes-hover-card"
        class="fixed hidden bg-slate-900/95 backdrop-blur-sm text-white rounded-lg p-2.5 shadow-xl max-w-xs z-50 leading-relaxed text-xs border border-slate-800 pointer-events-none transition-opacity duration-150 font-normal">
    </div>

</x-app-layout>