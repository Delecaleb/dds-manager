<x-app-layout>

    {{-- FullCalendar Scheduler (includes resource-timegrid) --}}
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.15/index.global.min.css' rel='stylesheet'>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.15/index.global.min.js'></script>

    <style>
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
    </style>

    <div class="flex flex-col bg-slate-50" style="min-height: calc(100vh - 64px);">

        {{-- ══════════════════ TOP TOOLBAR ══════════════════ --}}
        <div class="bg-white border-b border-slate-200 px-6 py-3 flex items-center justify-between gap-4 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div
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

                <select id="clinicFilter"
                    class="border border-slate-300 rounded px-3 py-1.5 text-sm font-medium text-slate-700 bg-white shadow-sm focus:outline-none focus:border-emerald-500 min-w-[120px]">
                    <option>8 Mile</option>
                </select>

                <button id="refreshBtn"
                    class="border border-emerald-500 text-emerald-600 px-5 py-1.5 rounded text-sm font-semibold hover:bg-emerald-50 transition shadow-sm">
                    Refresh
                </button>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex bg-slate-100 rounded-md border border-slate-200 p-0.5 gap-0.5">
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
                        Production
                        <span class="text-slate-400 cursor-help"
                            title="Display $ amount of what has been produced for the day">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </p>
                    <p class="text-xl font-bold text-slate-900" id="stat-production">—</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 mb-0.5 flex items-center gap-1">
                        Scheduled Production
                        <span class="text-slate-400 cursor-help"
                            title="Display $ amount of what has been scheduled for the day">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </p>
                    <p class="text-xl font-bold text-slate-900" id="stat-scheduled">—</p>
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
                    <select
                        class="border border-slate-300 rounded px-3 py-1.5 text-sm text-slate-700 bg-white uppercase font-semibold">
                        <option>PROVIDERS 0 selected</option>
                    </select>
                </div>
                <div class="flex flex-col flex-1">
                    <label class="text-xs font-bold text-slate-900 mb-1">Procedure(s)</label>
                    <select
                        class="border border-slate-300 rounded px-3 py-1.5 text-sm text-slate-700 bg-white uppercase font-semibold">
                        <option>PROCEDURES 0 selected</option>
                    </select>
                </div>
                <div class="flex flex-col flex-1">
                    <label class="text-xs font-bold text-slate-900 mb-1">Patient(s)</label>
                    <select
                        class="border border-slate-300 rounded px-3 py-1.5 text-sm text-slate-700 bg-white uppercase font-semibold">
                        <option>PATIENTS 0 selected</option>
                    </select>
                </div>
                <div class="flex flex-col flex-1">
                    <label class="text-xs font-bold text-slate-900 mb-1">Appointment Status</label>
                    <select
                        class="border border-slate-300 rounded px-3 py-1.5 text-sm text-slate-700 bg-white uppercase font-semibold">
                        <option>APPOINTMENT STATUS 0 selected</option>
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
                            <input type="text" placeholder="Search"
                                class="border border-slate-300 pl-9 pr-3 py-1.5 text-slate-700 w-64 focus:outline-emerald-500">
                        </div>
                        <select class="border border-slate-300 px-3 py-1.5 text-slate-700 bg-white font-medium">
                            <option>Export CSV</option>
                        </select>
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

                {{-- Footer Pagination --}}
                <div
                    class="border-t border-slate-200 px-5 py-4 flex items-center justify-between text-sm text-slate-600 bg-white">
                    <div class="flex items-center gap-2">
                        <span class="font-medium">Items per location</span>
                        <select class="border border-slate-300 rounded px-2 py-1 text-slate-700 bg-white">
                            <option>20</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="font-medium">20 items of <span
                                class="bg-emerald-500 text-white px-2 py-0.5 rounded font-bold ml-1 text-xs tracking-wide">Page
                                Capacity 20</span> <span
                                class="bg-cyan-500 text-white px-2 py-0.5 rounded font-bold ml-1 text-xs tracking-wide">Total
                                Items 52</span></span>
                        <div class="flex items-center gap-2">
                            <select
                                class="border border-slate-300 rounded px-2 py-1 text-slate-700 bg-white font-medium">
                                <option>1</option>
                            </select>
                            <span class="font-medium">of 3 pages</span>
                            <div class="flex border border-slate-300 bg-white">
                                <button
                                    class="px-3 py-1 hover:bg-slate-50 border-r border-slate-300 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <button class="px-3 py-1 hover:bg-slate-50 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
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
                        <button
                            class="border border-emerald-500 text-emerald-600 font-semibold px-4 py-1.5 rounded-sm hover:bg-emerald-50 transition shadow-sm">
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
                                <td
                                    class="text-right font-bold text-xs py-3 px-6 border-r border-slate-200 capacity-total-1">
                                    -</td>
                                <td
                                    class="text-right font-bold text-xs py-3 px-6 border-r border-slate-200 capacity-total-2">
                                    -</td>
                                <td
                                    class="text-right font-bold text-xs py-3 px-6 border-r border-slate-200 capacity-total-3">
                                    -</td>
                                <td class="border-r border-slate-200 bg-white"></td>
                                <td class="border-r border-slate-200 bg-white"></td>
                                <td class="bg-white"></td>
                            </tr>
                            <tr class="bg-white border-t border-slate-50">
                                <td class="text-left font-bold text-xs py-3 px-4 dt-col-sticky border-r border-slate-200 shadow-sm"
                                    style="width:14rem">Average:</td>
                                <td
                                    class="text-right font-bold text-xs py-3 px-6 border-r border-slate-200 capacity-avg-1">
                                    -</td>
                                <td
                                    class="text-right font-bold text-xs py-3 px-6 border-r border-slate-200 capacity-avg-2">
                                    -</td>
                                <td
                                    class="text-right font-bold text-xs py-3 px-6 border-r border-slate-200 capacity-avg-3">
                                    -</td>
                                <td
                                    class="text-right font-bold text-xs py-3 px-6 border-r border-slate-200 capacity-avg-4">
                                    -</td>
                                <td
                                    class="text-right font-bold text-xs py-3 px-6 border-r border-slate-200 capacity-avg-5">
                                    -</td>
                                <td class="text-right font-bold text-xs py-3 px-6 capacity-avg-6">-</td>
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
        function buildSkeleton() {
            const slotH = 32;
            const numCols = 5;
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
                `<div class="border-b border-slate-100 col-span-5" style="height:${slotH}px"></div>`
            ).join('');

            // Fake appointment blocks: { col 0-4, startSlot index, spanSlots }
            const fakeApts = [
                { col: 0, start: 6, span: 2 }, { col: 1, start: 8, span: 3 },
                { col: 2, start: 6, span: 1 }, { col: 3, start: 7, span: 2 },
                { col: 4, start: 8, span: 4 }, { col: 1, start: 4, span: 1 },
                { col: 3, start: 4, span: 1 }, { col: 0, start: 9, span: 2 },
            ].map(({ col, start, span }) => {
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

    {{-- Progress bar --}}
    <div class="flex-shrink-0 px-3 pt-3 pb-2">
        <div class="flex items-center justify-between mb-1.5">
            <span id="skel-label" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Initializing...</span>
            <span id="skel-pct" class="text-sm font-bold text-emerald-600 tabular-nums">0%</span>
        </div>
        <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
            <div id="skel-bar" class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-teal-500" style="width:0%"></div>
        </div>
    </div>

    {{-- Column headers --}}
    <div class="flex-shrink-0 flex border-y border-slate-200 bg-slate-50">
        <div class="w-16 flex-shrink-0 border-r border-slate-200"></div>
        <div class="flex-1 flex">${headerCols}</div>
    </div>

    {{-- Grid body --}}
    <div class="flex flex-1 overflow-hidden">
        {{-- Time labels --}}
        <div class="w-16 flex-shrink-0 border-r border-slate-200 flex flex-col overflow-hidden bg-white">
            ${timeRows}
        </div>
        {{-- Appointment area --}}
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

        function showCalSkeleton(label) {
            let skel = document.getElementById('cal-skeleton');
            if (!skel) {
                document.getElementById('calendar-wrap').insertAdjacentHTML('afterbegin', buildSkeleton());
                skel = document.getElementById('cal-skeleton');
            }
            skel.style.opacity = '1';
            setProgress(5, label || 'Loading...');
        }

        const STATUS_MAP = {
            1: 'Scheduled', 2: 'Complete', 3: 'UnschedList',
            4: 'ASAP', 5: 'Broken', 6: 'Planned',
            7: 'PtNote', 8: 'PtNoteCompleted'
        };

        document.addEventListener('DOMContentLoaded', function () {

            const calEl = document.getElementById('calendar');

            // Show skeleton immediately before the calendar even starts constructing
            showCalSkeleton('Initializing...');

            calendar = new FullCalendar.Calendar(calEl, {
                schedulerLicenseKey: 'CC-Attribution-NonCommercialNoDerivatives',
                initialView: 'resourceTimeGridDay',
                initialDate: '{{ date("Y-m-d") }}',
                headerToolbar: false,
                nowIndicator: true,
                slotDuration: '00:30:00',
                slotMinTime: '06:00:00',
                slotMaxTime: '21:00:00',
                height: 'auto',
                expandRows: false,
                allDaySlot: false,
                resourceOrder: 'title',

                // ── Resources (provider columns) ──────────────────────────
                resources: function (info, success, fail) {
                    setProgress(20, 'Loading providers...');
                    const date = (info.startStr || info.start?.toISOString() || document.getElementById('calDate').value || '{{ date("Y-m-d") }}').substring(0, 10);
                    fetch(baseUrl + '/calendar/resources?date=' + date)
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
                        ? '<span style="background:#d1fae5;color:#065f46;font-size:9px;padding:1px 5px;border-radius:3px;font-weight:700;margin-left:4px;">NP</span>'
                        : '';
                    const proc = ext.procedure
                        ? `<div style="font-size:10px;margin-top:2px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;opacity:.85;">${ext.procedure}</div>`
                        : '';
                    const note = ext.note
                        ? `<div style="font-size:9px;margin-top:1px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;opacity:.7;">${ext.note.substring(0, 55)}</div>`
                        : '';
                    return {
                        html: `<div style="padding:4px 6px;height:100%;overflow:hidden;box-sizing:border-box;">
                           <div style="font-size:11px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                               ${arg.event.title}${npBadge}
                           </div>
                           ${proc}${note}
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

                    document.getElementById('calDate').value = dateStr;
                    updateDateLabel(d);

                    // Resources are provider columns for the CURRENT date; they must be
                    // re-fetched whenever the visible date changes (FC does not do this automatically).
                    calendar.refetchResources();
                    // Production stats are computed server-side for the visible day.
                    fetchCalendarStats(dateStr);
                },
            });

            calendar.render();

            // initialise date label & clock
            updateDateLabel(new Date('{{ date("Y-m-d") }}T00:00:00'));
            startClock();

            // ── Navigation buttons ────────────────────────────────────────
            document.getElementById('prevBtn').addEventListener('click', () => {
                showCalSkeleton('Loading...');
                calendar.prev();
            });
            document.getElementById('nextBtn').addEventListener('click', () => {
                showCalSkeleton('Loading...');
                calendar.next();
            });

            // ── Refresh ───────────────────────────────────────────────────
            document.getElementById('refreshBtn').addEventListener('click', () => {
                showCalSkeleton('Refreshing...');
                calendar.refetchEvents();
                calendar.refetchResources();
                fetchCalendarStats(document.getElementById('calDate').value);
            });

            // ── Date picker ───────────────────────────────────────────────
            document.getElementById('calDate').addEventListener('change', function () {
                calendar.gotoDate(this.value);
            });

            // ── View toggle ───────────────────────────────────────────────
            document.querySelectorAll('.view-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    calendar.changeView(this.dataset.view);
                });
            });

            // ── Clicking outside events restores opacity ──────────────────
            document.getElementById('calendar-wrap').addEventListener('click', function (e) {
                if (!e.target.closest('.fc-event')) {
                    document.querySelectorAll('.fc-event').forEach(el => el.style.opacity = '1');
                }
            });
        });

        // ── Date label formatter ──────────────────────────────────────────
        function updateDateLabel(date) {
            document.getElementById('calDateLabel').textContent = date.toLocaleDateString('en-US', {
                weekday: 'long', month: 'long', day: 'numeric', year: 'numeric'
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
        // selected day (see CalendarController@stats) so the figures reflect
        // real produced/scheduled dollars rather than a client-side sum.
        function fetchCalendarStats(date) {
            const usd = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
            const prodEl = document.getElementById('stat-production');
            const schedEl = document.getElementById('stat-scheduled');
            prodEl.textContent = schedEl.textContent = '…';

            fetch(baseUrl + '/calendar/stats?date=' + date)
                .then(r => r.json())
                .then(s => {
                    prodEl.textContent = usd.format(parseFloat(s.production) || 0);
                    schedEl.textContent = usd.format(parseFloat(s.scheduled_production) || 0);
                })
                .catch(() => { prodEl.textContent = schedEl.textContent = '—'; });
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
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 mb-4">
            <div class="flex items-start justify-between mb-2">
                <h4 class="text-sm font-bold text-slate-900 leading-tight">${event.title}${npBadge}</h4>
                <span class="text-[10px] text-slate-400 font-semibold whitespace-nowrap ml-2">ID: ${ext.patNum || '—'}</span>
            </div>
            <div class="space-y-1 text-xs text-slate-500">
                <p><span class="font-semibold text-slate-700">Provider:</span> ${ext.doctor || '—'}</p>
                <p><span class="font-semibold text-slate-700">Time:</span> ${start} – ${end}</p>
                <p><span class="font-semibold text-slate-700">Operatory:</span> ${ext.operator || '—'}</p>
                <p><span class="font-semibold text-slate-700">Procedure:</span> ${ext.procedure || '—'}</p>
                <p class="flex items-center gap-1">
                    <span class="font-semibold text-slate-700">Status:</span>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold ${statusCls}">${status}</span>
                </p>
            </div>
            ${noteBlock}
        </div>

        <div class="space-y-2">
            <a href="${baseUrl}/patients/${ext.patNum}"
               class="block w-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-2 rounded text-sm text-center transition shadow-sm">
               View Patient
            </a>
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

                // Show target view
                const target = this.getAttribute('data-target');
                const viewEl = document.getElementById(target);
                if (viewEl) {
                    viewEl.classList.remove('hidden');
                    viewEl.classList.add('flex');

                    // Re-render calendar if that tab was chosen and calendar exists
                    if (target === 'view-calendar' && calendar) {
                        setTimeout(() => calendar.render(), 10);
                    }

                    if (target === 'view-details') {
                        initAptDetailsTable();
                    }

                    if (target === 'view-capacity') {
                        initAptCapacityTable();
                    }
                }
            });
        });

        let aptDetailsTable = null;
        function initAptDetailsTable() {
            if (aptDetailsTable) return;

            aptDetailsTable = $('#appointmentDetailsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('calendar.appointments-details-data') }}",
                    data: function (d) {
                        const datePicker = document.getElementById('calDate');
                        d.start = datePicker ? datePicker.value : "{{ date('Y-m-d') }}";
                        d.end = d.start;
                    }
                },
                columns: [
                    { data: 'location', name: 'location' },
                    { data: 'patient_name', name: 'patient_name' },
                    { data: 'appointment_date', name: 'appointment_date', orderable: false },
                    { data: 'appointment_time', name: 'appointment_time', orderable: false },
                    { data: 'appointment_duration', name: 'appointment_duration', orderable: false },
                    { data: 'operatory_name', name: 'operatory_name' },
                    { data: 'appointment_status', name: 'appointment_status' },
                    { data: 'patient_age', name: 'patient_age', orderable: false },
                    { data: 'patient_phone', name: 'patient_phone', orderable: false },
                    { data: 'email_address', name: 'email_address', orderable: false },
                    { data: 'patient_type', name: 'patient_type', orderable: false },
                    { data: 'appointment_notes', name: 'appointment_notes', orderable: false },
                    { data: 'confirmation_status', name: 'confirmation_status', orderable: false },
                    { data: 'provider_name', name: 'provider_name' },
                    { data: 'procedure_codes', name: 'procedure_codes', orderable: false },
                    { data: 'production', name: 'production', orderable: false },
                    { data: 'primary_insurance', name: 'primary_insurance', orderable: false },
                    { data: 'secondary_insurance', name: 'secondary_insurance', orderable: false },
                    { data: 'referral_source', name: 'referral_source', orderable: false },
                    { data: 'unscheduled_tx', name: 'unscheduled_tx', orderable: false },
                    { data: 'last_visit_date', name: 'last_visit_date', orderable: false }
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
                if (aptDetailsTable) aptDetailsTable.ajax.reload();
            });
        }

        let aptCapacityTable = null;
        function initAptCapacityTable() {
            if (aptCapacityTable) return;

            aptCapacityTable = $('#appointmentCapacityTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('calendar.appointment-capacity-data') }}",
                    data: function (d) {
                        const datePicker = document.getElementById('calDate');
                        d.start = datePicker ? datePicker.value : "{{ date('Y-m-d') }}";
                        d.end = d.start;
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
                        td.addClass('px-4 py-3 text-right font-medium text-slate-800');

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
    </script>

</x-app-layout>