<x-app-layout>

    <!-- ── HEADER ─────────────────────────────────────────── -->
    <header
        class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between shrink-0 sticky top-0 z-20">
        <div class="flex items-center gap-4">
            <h1 class="text-lg font-bold text-slate-800 tracking-tight">Treatment Miner</h1>

            <!-- Location Filter -->
            <div class="border-l border-slate-200 pl-4">
                <select id="txMinerLocation"
                    class="appearance-none bg-slate-50 border border-slate-200 rounded-lg pl-3 pr-9 py-1.5 text-sm font-semibold text-slate-900 focus:outline-none focus:border-emerald-500 focus:bg-white cursor-pointer transition-colors">
                    <option value="all">All Locations</option>
                    @if(isset($clinics))
                        @foreach($clinics as $clinicId => $clinicName)
                            <option value="{{ $clinicId }}">{{ $clinicName }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <x-daterange-picker id="txMinerDateRange" />
        </div>
    </header>

    <!-- ── MAIN ───────────────────────────────────────────── -->
    <main class="p-6 space-y-6 max-w-full mx-auto">

        <!-- Tabs -->
        <div class="border-b border-slate-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button type="button"
                    class="tab-btn active border-emerald-500 text-emerald-600 whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm transition-colors"
                    data-target="#tab-month" data-tab="month">
                    By month
                </button>
                <button type="button"
                    class="tab-btn border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm transition-colors"
                    data-target="#tab-provider" data-tab="provider">
                    By Provider
                </button>
                <button type="button"
                    class="tab-btn border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm transition-colors"
                    data-target="#tab-location" data-tab="location">
                    By Location
                </button>
            </nav>
        </div>

        <!-- Tab contents -->
        <div class="tab-content relative">

            <!-- ══════════════════ TAB 1: BY MONTH ══════════════════ -->
            <div id="tab-month" class="tab-pane active space-y-4">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 overflow-x-hidden">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
                        <div class="flex items-center gap-1.5 text-xs font-medium">
                            <span class="dds-heat-top text-[#1e4620] px-2.5 py-1 rounded font-bold">Top 20%</span>
                            <span class="dds-heat-mid text-[#78350f] px-2.5 py-1 rounded font-bold">Mid Tier</span>
                            <span class="dds-heat-bottom text-[#9f1239] px-2.5 py-1 rounded font-bold">Bottom 20%</span>
                        </div>
                        <div>
                            <button type="button" id="exportMonthBtn"
                                class="border border-emerald-500 text-emerald-600 hover:bg-emerald-500 hover:text-white font-bold py-1.5 px-4 rounded-lg text-xs transition-colors cursor-pointer">
                                Export CSV
                            </button>
                        </div>
                    </div>

                    <x-data-table id="tableByMonth" min-width="1200px">
                        <x-slot:head>
                            <tr>
                                <th class="dt-col-sticky px-4 py-3 min-w-[120px]">Month</th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        Total TX Plan
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                Displays the $ amount of treatment plans presented or refreshed. To refresh a treatment plan, the treatment plan date must be updated
                                                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        Tx Scheduled
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                Displays the $ amount of procedures scheduled from a treatment plan (excludes broken appointments)
                                                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        Tx Unscheduled
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                Displays the $ amount of unscheduled procedures from a treatment plan (includes broken appointments)
                                                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        Completed Tx
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                Displays the $ amount of procedure codes completed from a treatment plan
                                                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        Case Acceptance %
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                Displays the percentage of treatment plans closed or accepted: (Completed Treatment Plan + Scheduled Treatment Plan) / Total Tx Plan Proposed * 100
                                                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        # TX Plan Presented
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                # of treatment plans presented or refreshed
                                                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        Average Treatment Plan $
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full right-0 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                Average amount of treatment plans: Total Tx Plan $ / # of Tx Plan Presented
                                                <div class="absolute -bottom-1 right-3 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        Patients with Tx Plan %
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full right-0 mb-2 w-72 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                % of patients seen that also received a treatment plan: # of Patient with a Treatment Plan / # of Patients Seen in the selected date range * 100
                                                <div class="absolute -bottom-1 right-4 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                        </x-slot:head>
                        <x-slot:foot>
                            <tr class="bg-gray-100 font-semibold text-xs text-slate-700">
                                <td class="dt-col-sticky px-4 py-2 text-right">Average:</td>
                                <td class="px-4 py-2 text-right" id="monthFootAvg-totalTx">—</td>
                                <td class="px-4 py-2 text-right" id="monthFootAvg-scheduled">—</td>
                                <td class="px-4 py-2 text-right" id="monthFootAvg-unscheduled">—</td>
                                <td class="px-4 py-2 text-right" id="monthFootAvg-completed">—</td>
                                <td class="px-4 py-2 text-right" id="monthFootAvg-acceptance">—</td>
                                <td class="px-4 py-2 text-right" id="monthFootAvg-presented">—</td>
                                <td class="px-4 py-2 text-right" id="monthFootAvg-avgTx">—</td>
                                <td class="px-4 py-2 text-right" id="monthFootAvg-patientsTx">—</td>
                            </tr>
                            <tr class="bg-gray-200 font-bold text-xs text-slate-900">
                                <td class="dt-col-sticky px-4 py-2 text-right">Total:</td>
                                <td class="px-4 py-2 text-right" id="monthFootTot-totalTx">—</td>
                                <td class="px-4 py-2 text-right" id="monthFootTot-scheduled">—</td>
                                <td class="px-4 py-2 text-right" id="monthFootTot-unscheduled">—</td>
                                <td class="px-4 py-2 text-right" id="monthFootTot-completed">—</td>
                                <td class="px-4 py-2 text-right" id="monthFootTot-acceptance">—</td>
                                <td class="px-4 py-2 text-right" id="monthFootTot-presented">—</td>
                                <td class="px-4 py-2 text-right" id="monthFootTot-avgTx">—</td>
                                <td class="px-4 py-2 text-right" id="monthFootTot-patientsTx">—</td>
                            </tr>
                        </x-slot:foot>
                    </x-data-table>

                </div>
            </div>

            <!-- ══════════════════ TAB 2: BY PROVIDER ══════════════════ -->
            <div id="tab-provider" class="tab-pane hidden space-y-4">
                
                <!-- Filter Section for Provider Tab -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <div class="flex flex-wrap items-center gap-3">
                        
                        <!-- Providers Filter Dropdown -->
                        <div class="relative" id="provFilterDropdownWrap">
                            <span class="block text-xs font-semibold text-slate-600 mb-1">Provider(s)</span>
                            <button type="button" id="provDropdownBtn"
                                class="w-48 py-1.5 px-3 text-xs text-left truncate border border-slate-300 rounded-lg bg-white hover:bg-slate-50 flex items-center justify-between focus:outline-none focus:border-emerald-500 h-9 transition-colors">
                                <span class="font-medium text-slate-700" id="provDropdownLabel">All Providers</span>
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div id="provDropdownMenu"
                                class="hidden absolute top-full left-0 mt-1 w-64 bg-white border border-slate-200 rounded-lg shadow-xl z-50 p-3 space-y-3">
                                <input type="search" id="provSearchInput" placeholder="Search Providers..."
                                    class="w-full px-2.5 py-1 text-xs border border-slate-200 rounded-md focus:outline-none focus:border-emerald-500">
                                <div class="flex items-center justify-between text-xs border-b border-slate-100 pb-2">
                                    <span class="font-bold text-slate-700">All (<span id="provTotalCount">{{ count($providers ?? []) }}</span>)</span>
                                    <div class="space-x-2">
                                        <button type="button" id="provSelectAll" class="text-emerald-600 hover:underline">All</button>
                                        <button type="button" id="provClearAll" class="text-slate-400 hover:underline">Clear</button>
                                    </div>
                                </div>
                                <div class="max-h-48 overflow-y-auto space-y-1.5 text-xs" id="provCheckboxList">
                                    @if(isset($providers))
                                        @foreach($providers as $p)
                                            <label class="flex items-center gap-2 cursor-pointer hover:bg-slate-50 p-1 rounded">
                                                <input type="checkbox" name="tx_providers[]" value="{{ $p->ProvNum }}"
                                                    class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 prov-cb">
                                                <span class="truncate text-slate-700">{{ $p->LName }}{{ $p->PName ? ', ' . $p->PName : '' }}</span>
                                            </label>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                                    <button type="button" id="provCancelBtn" class="px-2.5 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded">Cancel</button>
                                    <button type="button" id="provApplyBtn" class="px-3 py-1 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded transition-colors">Apply</button>
                                </div>
                            </div>
                        </div>

                        <!-- Line Of Business Filter Dropdown -->
                        <div class="relative" id="provLobDropdownWrap">
                            <span class="block text-xs font-semibold text-slate-600 mb-1">Line Of Business(es)</span>
                            <button type="button" id="provLobDropdownBtn"
                                class="w-52 py-1.5 px-3 text-xs text-left truncate border border-slate-300 rounded-lg bg-white hover:bg-slate-50 flex items-center justify-between focus:outline-none focus:border-emerald-500 h-9 transition-colors">
                                <span class="font-medium text-slate-700" id="provLobDropdownLabel">Line of Business: All</span>
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div id="provLobDropdownMenu"
                                class="hidden absolute top-full left-0 mt-1 w-60 bg-white border border-slate-200 rounded-lg shadow-xl z-50 p-3 space-y-3">
                                <input type="search" id="provLobSearchInput" placeholder="Search Line of Business"
                                    class="w-full px-2.5 py-1 text-xs border border-slate-200 rounded-md focus:outline-none focus:border-emerald-500">
                                <div class="flex items-center justify-between text-xs border-b border-slate-100 pb-2">
                                    <span class="font-bold text-slate-700">All ({{ count($lineOfBusinesses ?? []) }})</span>
                                    <div class="space-x-2">
                                        <button type="button" id="provLobSelectAll" class="text-emerald-600 hover:underline">All</button>
                                        <button type="button" id="provLobClearAll" class="text-slate-400 hover:underline">Clear</button>
                                    </div>
                                </div>
                                <div class="max-h-48 overflow-y-auto space-y-1.5 text-xs" id="provLobCheckboxList">
                                    @if(isset($lineOfBusinesses))
                                        @foreach($lineOfBusinesses as $lob)
                                            <label class="flex items-center gap-2 cursor-pointer hover:bg-slate-50 p-1 rounded">
                                                <input type="checkbox" name="tx_prov_lob[]" value="{{ $lob }}"
                                                    class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 prov-lob-cb">
                                                <span class="text-slate-700">{{ $lob }}</span>
                                            </label>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                                    <button type="button" id="provLobCancelBtn" class="px-2.5 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded">Cancel</button>
                                    <button type="button" id="provLobApplyBtn" class="px-3 py-1 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded transition-colors">Apply</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Provider Table Card -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 overflow-x-hidden">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
                        <div class="flex items-center gap-1.5 text-xs font-medium">
                            <span class="dds-heat-top text-[#1e4620] px-2.5 py-1 rounded font-bold">Top 20%</span>
                            <span class="dds-heat-mid text-[#78350f] px-2.5 py-1 rounded font-bold">Mid Tier</span>
                            <span class="dds-heat-bottom text-[#9f1239] px-2.5 py-1 rounded font-bold">Bottom 20%</span>
                        </div>
                        <div>
                            <button type="button" id="exportProviderBtn"
                                class="border border-emerald-500 text-emerald-600 hover:bg-emerald-500 hover:text-white font-bold py-1.5 px-4 rounded-lg text-xs transition-colors cursor-pointer">
                                Export CSV
                            </button>
                        </div>
                    </div>

                    <x-data-table id="tableByProvider" min-width="1200px">
                        <x-slot:head>
                            <tr>
                                <th class="dt-col-sticky px-4 py-3 min-w-[150px]">Provider</th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        Total TX Plan
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                Displays the $ amount of treatment plans presented or refreshed. To refresh a treatment plan, the treatment plan date must be updated
                                                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        Tx Scheduled
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                Displays the $ amount of procedures scheduled from a treatment plan (excludes broken appointments)
                                                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        Tx Unscheduled
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                Displays the $ amount of unscheduled procedures from a treatment plan (includes broken appointments)
                                                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        Completed Tx
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                Displays the $ amount of procedure codes completed from a treatment plan
                                                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        Case Acceptance %
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                Displays the percentage of treatment plans closed or accepted: (Completed Treatment Plan + Scheduled Treatment Plan) / Total Tx Plan Proposed * 100
                                                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        # TX Plan Presented
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                # of treatment plans presented or refreshed
                                                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        Average Treatment Plan $
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full right-0 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                Average amount of treatment plans: Total Tx Plan $ / # of Tx Plan Presented
                                                <div class="absolute -bottom-1 right-3 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        Patients with Tx Plan %
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full right-0 mb-2 w-72 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                % of patients seen that also received a treatment plan: # of Patient with a Treatment Plan / # of Patients Seen in the selected date range * 100
                                                <div class="absolute -bottom-1 right-4 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                        </x-slot:head>
                        <x-slot:foot>
                            <tr class="bg-gray-100 font-semibold text-xs text-slate-700">
                                <td class="dt-col-sticky px-4 py-2 text-right">Average:</td>
                                <td class="px-4 py-2 text-right" id="provFootAvg-totalTx">—</td>
                                <td class="px-4 py-2 text-right" id="provFootAvg-scheduled">—</td>
                                <td class="px-4 py-2 text-right" id="provFootAvg-unscheduled">—</td>
                                <td class="px-4 py-2 text-right" id="provFootAvg-completed">—</td>
                                <td class="px-4 py-2 text-right" id="provFootAvg-acceptance">—</td>
                                <td class="px-4 py-2 text-right" id="provFootAvg-presented">—</td>
                                <td class="px-4 py-2 text-right" id="provFootAvg-avgTx">—</td>
                                <td class="px-4 py-2 text-right" id="provFootAvg-patientsTx">—</td>
                            </tr>
                            <tr class="bg-gray-200 font-bold text-xs text-slate-900">
                                <td class="dt-col-sticky px-4 py-2 text-right">Total:</td>
                                <td class="px-4 py-2 text-right" id="provFootTot-totalTx">—</td>
                                <td class="px-4 py-2 text-right" id="provFootTot-scheduled">—</td>
                                <td class="px-4 py-2 text-right" id="provFootTot-unscheduled">—</td>
                                <td class="px-4 py-2 text-right" id="provFootTot-completed">—</td>
                                <td class="px-4 py-2 text-right" id="provFootTot-acceptance">—</td>
                                <td class="px-4 py-2 text-right" id="provFootTot-presented">—</td>
                                <td class="px-4 py-2 text-right" id="provFootTot-avgTx">—</td>
                                <td class="px-4 py-2 text-right" id="provFootTot-patientsTx">—</td>
                            </tr>
                        </x-slot:foot>
                    </x-data-table>

                </div>
            </div>

            <!-- ══════════════════ TAB 3: BY LOCATION ══════════════════ -->
            <div id="tab-location" class="tab-pane hidden space-y-4">

                <!-- Filter Section for Location Tab -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <div class="flex flex-wrap items-center gap-3">

                        <!-- Line Of Business Filter Dropdown -->
                        <div class="relative" id="locLobDropdownWrap">
                            <span class="block text-xs font-semibold text-slate-600 mb-1">Line Of Business(es)</span>
                            <button type="button" id="locLobDropdownBtn"
                                class="w-52 py-1.5 px-3 text-xs text-left truncate border border-slate-300 rounded-lg bg-white hover:bg-slate-50 flex items-center justify-between focus:outline-none focus:border-emerald-500 h-9 transition-colors">
                                <span class="font-medium text-slate-700" id="locLobDropdownLabel">Line of Business: All</span>
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div id="locLobDropdownMenu"
                                class="hidden absolute top-full left-0 mt-1 w-60 bg-white border border-slate-200 rounded-lg shadow-xl z-50 p-3 space-y-3">
                                <input type="search" id="locLobSearchInput" placeholder="Search Line of Business"
                                    class="w-full px-2.5 py-1 text-xs border border-slate-200 rounded-md focus:outline-none focus:border-emerald-500">
                                <div class="flex items-center justify-between text-xs border-b border-slate-100 pb-2">
                                    <span class="font-bold text-slate-700">All ({{ count($lineOfBusinesses ?? []) }})</span>
                                    <div class="space-x-2">
                                        <button type="button" id="locLobSelectAll" class="text-emerald-600 hover:underline">All</button>
                                        <button type="button" id="locLobClearAll" class="text-slate-400 hover:underline">Clear</button>
                                    </div>
                                </div>
                                <div class="max-h-48 overflow-y-auto space-y-1.5 text-xs" id="locLobCheckboxList">
                                    @if(isset($lineOfBusinesses))
                                        @foreach($lineOfBusinesses as $lob)
                                            <label class="flex items-center gap-2 cursor-pointer hover:bg-slate-50 p-1 rounded">
                                                <input type="checkbox" name="tx_loc_lob[]" value="{{ $lob }}"
                                                    class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 loc-lob-cb">
                                                <span class="text-slate-700">{{ $lob }}</span>
                                            </label>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                                    <button type="button" id="locLobCancelBtn" class="px-2.5 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded">Cancel</button>
                                    <button type="button" id="locLobApplyBtn" class="px-3 py-1 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded transition-colors">Apply</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Location Table Card -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 overflow-x-hidden">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
                        <div class="flex items-center gap-1.5 text-xs font-medium">
                            <span class="dds-heat-top text-[#1e4620] px-2.5 py-1 rounded font-bold">Top 20%</span>
                            <span class="dds-heat-mid text-[#78350f] px-2.5 py-1 rounded font-bold">Mid Tier</span>
                            <span class="dds-heat-bottom text-[#9f1239] px-2.5 py-1 rounded font-bold">Bottom 20%</span>
                        </div>
                        <div>
                            <button type="button" id="exportLocationBtn"
                                class="border border-emerald-500 text-emerald-600 hover:bg-emerald-500 hover:text-white font-bold py-1.5 px-4 rounded-lg text-xs transition-colors cursor-pointer">
                                Export CSV
                            </button>
                        </div>
                    </div>

                    <x-data-table id="tableByLocation" min-width="1200px">
                        <x-slot:head>
                            <tr>
                                <th class="dt-col-sticky px-4 py-3 min-w-[150px]">Location</th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        Total TX Plan
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                Displays the $ amount of treatment plans presented or refreshed. To refresh a treatment plan, the treatment plan date must be updated
                                                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        Tx Scheduled
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                Displays the $ amount of procedures scheduled from a treatment plan (excludes broken appointments)
                                                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        Tx Unscheduled
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                Displays the $ amount of unscheduled procedures from a treatment plan (includes broken appointments)
                                                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        Completed Tx
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                Displays the $ amount of procedure codes completed from a treatment plan
                                                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        Case Acceptance %
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                Displays the percentage of treatment plans closed or accepted: (Completed Treatment Plan + Scheduled Treatment Plan) / Total Tx Plan Proposed * 100
                                                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        # TX Plan Presented
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                # of treatment plans presented or refreshed
                                                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        Average Treatment Plan $
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full right-0 mb-2 w-64 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                Average amount of treatment plans: Total Tx Plan $ / # of Tx Plan Presented
                                                <div class="absolute -bottom-1 right-3 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        Patients with Tx Plan %
                                        <div class="relative group flex-shrink-0 font-normal normal-case">
                                            <button type="button" class="text-slate-300 hover:text-emerald-500 transition-colors focus:outline-none cursor-help">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full right-0 mb-2 w-72 bg-slate-900 text-white text-xs rounded p-2.5 shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 whitespace-normal text-left">
                                                % of patients seen that also received a treatment plan: # of Patient with a Treatment Plan / # of Patients Seen in the selected date range * 100
                                                <div class="absolute -bottom-1 right-4 w-2 h-2 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                        </x-slot:head>
                        <x-slot:foot>
                            <tr class="bg-gray-100 font-semibold text-xs text-slate-700">
                                <td class="dt-col-sticky px-4 py-2 text-right">Average:</td>
                                <td class="px-4 py-2 text-right" id="locFootAvg-totalTx">—</td>
                                <td class="px-4 py-2 text-right" id="locFootAvg-scheduled">—</td>
                                <td class="px-4 py-2 text-right" id="locFootAvg-unscheduled">—</td>
                                <td class="px-4 py-2 text-right" id="locFootAvg-completed">—</td>
                                <td class="px-4 py-2 text-right" id="locFootAvg-acceptance">—</td>
                                <td class="px-4 py-2 text-right" id="locFootAvg-presented">—</td>
                                <td class="px-4 py-2 text-right" id="locFootAvg-avgTx">—</td>
                                <td class="px-4 py-2 text-right" id="locFootAvg-patientsTx">—</td>
                            </tr>
                            <tr class="bg-gray-200 font-bold text-xs text-slate-900">
                                <td class="dt-col-sticky px-4 py-2 text-right">Total:</td>
                                <td class="px-4 py-2 text-right" id="locFootTot-totalTx">—</td>
                                <td class="px-4 py-2 text-right" id="locFootTot-scheduled">—</td>
                                <td class="px-4 py-2 text-right" id="locFootTot-unscheduled">—</td>
                                <td class="px-4 py-2 text-right" id="locFootTot-completed">—</td>
                                <td class="px-4 py-2 text-right" id="locFootTot-acceptance">—</td>
                                <td class="px-4 py-2 text-right" id="locFootTot-presented">—</td>
                                <td class="px-4 py-2 text-right" id="locFootTot-avgTx">—</td>
                                <td class="px-4 py-2 text-right" id="locFootTot-patientsTx">—</td>
                            </tr>
                        </x-slot:foot>
                    </x-data-table>

                </div>
            </div>

        </div>

    </main>

    <script>
        const baseUrl = "{{ url('') }}";

        // Wait for jQuery
        $(document).ready(function () {
            let tables = {};
            let activeTabMode = 'month';

            function getFilters(tab) {
                let startDate = null;
                let endDate = null;
                const drpInput = $('#txMinerDateRange').data('daterangepicker');
                if (drpInput && drpInput.startDate && drpInput.endDate) {
                    startDate = drpInput.startDate.format('YYYY-MM-DD');
                    endDate = drpInput.endDate.format('YYYY-MM-DD');
                }

                const clinic = $('#txMinerLocation').val();

                let params = {
                    start_date: startDate,
                    end_date: endDate,
                    clinic: clinic
                };

                if (tab === 'provider') {
                    // Providers filter
                    let checkedProvs = [];
                    $('.prov-cb:checked').each(function () { checkedProvs.push($(this).val()); });
                    if (checkedProvs.length > 0) {
                        params.providers = checkedProvs;
                    }
                    // Provider LOB filter
                    let checkedProvLobs = [];
                    $('.prov-lob-cb:checked').each(function () { checkedProvLobs.push($(this).val()); });
                    if (checkedProvLobs.length > 0) {
                        params.lobs = checkedProvLobs;
                    }
                } else if (tab === 'location') {
                    // Location LOB filter
                    let checkedLocLobs = [];
                    $('.loc-lob-cb:checked').each(function () { checkedLocLobs.push($(this).val()); });
                    if (checkedLocLobs.length > 0) {
                        params.lobs = checkedLocLobs;
                    }
                }

                return params;
            }

            function updateFooterValues(tab, totals, averages) {
                const prefix = tab === 'month' ? 'monthFoot' : (tab === 'provider' ? 'provFoot' : 'locFoot');
                if (averages) {
                    $(`#${prefix}Avg-totalTx`).text(averages.total_tx_plan ?? '—');
                    $(`#${prefix}Avg-scheduled`).text(averages.tx_scheduled ?? '—');
                    $(`#${prefix}Avg-unscheduled`).text(averages.tx_unscheduled ?? '—');
                    $(`#${prefix}Avg-completed`).text(averages.completed_tx ?? '—');
                    $(`#${prefix}Avg-acceptance`).text(averages.case_acceptance ?? '—');
                    $(`#${prefix}Avg-presented`).text(averages.tx_presented ?? '—');
                    $(`#${prefix}Avg-avgTx`).text(averages.avg_tx_plan ?? '—');
                    $(`#${prefix}Avg-patientsTx`).text(averages.patients_with_tx ?? '—');
                }
                if (totals) {
                    $(`#${prefix}Tot-totalTx`).text(totals.total_tx_plan ?? '—');
                    $(`#${prefix}Tot-scheduled`).text(totals.tx_scheduled ?? '—');
                    $(`#${prefix}Tot-unscheduled`).text(totals.tx_unscheduled ?? '—');
                    $(`#${prefix}Tot-completed`).text(totals.completed_tx ?? '—');
                    $(`#${prefix}Tot-acceptance`).text(totals.case_acceptance ?? '—');
                    $(`#${prefix}Tot-presented`).text(totals.tx_presented ?? '—');
                    $(`#${prefix}Tot-avgTx`).text(totals.avg_tx_plan ?? '—');
                    $(`#${prefix}Tot-patientsTx`).text(totals.patients_with_tx ?? '—');
                }
            }

            function getHeatClass(tier) {
                if (tier === 'top') return 'dds-heat-top';
                if (tier === 'bottom') return 'dds-heat-bottom';
                if (tier === 'mid') return 'dds-heat-mid';
                return '';
            }

            window.openTxDrilldown = function (metric, scopeParams) {
                const filters = getFilters(activeTabMode);
                const params = Object.assign({}, filters, scopeParams || {}, { metric: metric });
                const url = baseUrl + '/tx-miner/drilldown?' + $.param(params);
                if (window.DDS && DDS.modal && typeof DDS.modal.open === 'function') {
                    DDS.modal.open(url);
                } else if (typeof openLimitlessModal === 'function') {
                    openLimitlessModal(url);
                }
            };

            function renderHeatCell(data, tier, metric, scopeParams, rawVal, align = 'text-right') {
                const cls = getHeatClass(tier);
                const orderAttr = rawVal !== undefined && rawVal !== null ? `data-order="${rawVal}"` : '';

                if (metric) {
                    const paramsJson = JSON.stringify(scopeParams || {}).replace(/"/g, '&quot;');
                    return `<button type="button" ${orderAttr} class="w-full py-1 px-2.5 rounded ${cls} ${align} font-medium hover:underline hover:opacity-90 transition-opacity focus:outline-none cursor-pointer" onclick="openTxDrilldown('${metric}', ${paramsJson})">${data}</button>`;
                }

                return `<div ${orderAttr} class="w-full py-1 px-2.5 rounded ${cls} ${align} font-medium">${data}</div>`;
            }

            function initTable(mode) {
                if (tables[mode]) return tables[mode];

                if (mode === 'month') {
                    tables['month'] = DDS.dataTable(document.getElementById('tableByMonth'), {
                        processing: true,
                        serverSide: true,
                        searching: false,
                        ordering: false,
                        ajax: {
                            url: baseUrl + '/tx-miner/data',
                            type: 'GET',
                            data: function (d) {
                                Object.assign(d, getFilters('month'));
                            },
                            dataSrc: function (json) {
                                updateFooterValues('month', json.total, json.average);
                                return json.data;
                            }
                        },
                        columns: [
                            {
                                data: 'month',
                                render: (data) => `<span class="font-bold text-gray-900">${data}</span>`
                            },
                            {
                                data: 'total_tx_plan',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.total_tx_plan, 'total_tx_plan', { month: row.month_group }, row.raw?.total_tx_plan)
                            },
                            {
                                data: 'tx_scheduled',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.tx_scheduled, 'tx_scheduled', { month: row.month_group }, row.raw?.tx_scheduled)
                            },
                            {
                                data: 'tx_unscheduled',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.tx_unscheduled, 'tx_unscheduled', { month: row.month_group }, row.raw?.tx_unscheduled)
                            },
                            {
                                data: 'completed_tx',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.completed_tx, 'completed_tx', { month: row.month_group }, row.raw?.completed_tx)
                            },
                            {
                                data: 'case_acceptance',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.case_acceptance, null, null, row.raw?.case_acceptance)
                            },
                            {
                                data: 'tx_presented',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.tx_presented, 'tx_presented', { month: row.month_group }, row.raw?.tx_presented)
                            },
                            {
                                data: 'avg_tx_plan',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.avg_tx_plan, null, null, row.raw?.avg_tx_plan)
                            },
                            {
                                data: 'patients_with_tx',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.patients_with_tx, null, null, row.raw?.patients_with_tx)
                            }
                        ]
                    });
                } else if (mode === 'provider') {
                    tables['provider'] = DDS.dataTable(document.getElementById('tableByProvider'), {
                        processing: true,
                        serverSide: true,
                        searching: false,
                        ordering: false,
                        ajax: {
                            url: baseUrl + '/tx-miner/data-provider',
                            type: 'GET',
                            data: function (d) {
                                Object.assign(d, getFilters('provider'));
                            },
                            dataSrc: function (json) {
                                updateFooterValues('provider', json.total, json.average);
                                return json.data;
                            }
                        },
                        columns: [
                            {
                                data: 'provider',
                                render: function (data, type, row) {
                                    const provNum = row.prov_num;
                                    return `
                                        <div class="flex items-center justify-between gap-2 font-bold text-gray-900">
                                            <span class="truncate">${data}</span>
                                            ${provNum ? `
                                                <button type="button" class="text-emerald-500 hover:text-emerald-700 transition-colors focus:outline-none shrink-0"
                                                    onclick="if(typeof openProviderModal === 'function') openProviderModal('${provNum}'); else alert('Provider modal not loaded.');"
                                                    title="View Provider Details">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                                        <polyline points="15 3 21 3 21 9"></polyline>
                                                        <line x1="10" y1="14" x2="21" y2="3"></line>
                                                    </svg>
                                                </button>
                                            ` : ''}
                                        </div>
                                    `;
                                }
                            },
                            {
                                data: 'total_tx_plan',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.total_tx_plan, 'total_tx_plan', { prov_num: row.prov_num }, row.raw?.total_tx_plan)
                            },
                            {
                                data: 'tx_scheduled',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.tx_scheduled, 'tx_scheduled', { prov_num: row.prov_num }, row.raw?.tx_scheduled)
                            },
                            {
                                data: 'tx_unscheduled',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.tx_unscheduled, 'tx_unscheduled', { prov_num: row.prov_num }, row.raw?.tx_unscheduled)
                            },
                            {
                                data: 'completed_tx',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.completed_tx, 'completed_tx', { prov_num: row.prov_num }, row.raw?.completed_tx)
                            },
                            {
                                data: 'case_acceptance',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.case_acceptance, null, null, row.raw?.case_acceptance)
                            },
                            {
                                data: 'tx_presented',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.tx_presented, 'tx_presented', { prov_num: row.prov_num }, row.raw?.tx_presented)
                            },
                            {
                                data: 'avg_tx_plan',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.avg_tx_plan, null, null, row.raw?.avg_tx_plan)
                            },
                            {
                                data: 'patients_with_tx',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.patients_with_tx, null, null, row.raw?.patients_with_tx)
                            }
                        ]
                    });
                } else if (mode === 'location') {
                    tables['location'] = DDS.dataTable(document.getElementById('tableByLocation'), {
                        processing: true,
                        serverSide: true,
                        searching: false,
                        ordering: false,
                        ajax: {
                            url: baseUrl + '/tx-miner/data-location',
                            type: 'GET',
                            data: function (d) {
                                Object.assign(d, getFilters('location'));
                            },
                            dataSrc: function (json) {
                                updateFooterValues('location', json.total, json.average);
                                return json.data;
                            }
                        },
                        columns: [
                            {
                                data: 'location',
                                render: (data) => `<span class="font-bold text-gray-900">${data}</span>`
                            },
                            {
                                data: 'total_tx_plan',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.total_tx_plan, 'total_tx_plan', { clinic_num: row.clinic_num }, row.raw?.total_tx_plan)
                            },
                            {
                                data: 'tx_scheduled',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.tx_scheduled, 'tx_scheduled', { clinic_num: row.clinic_num }, row.raw?.tx_scheduled)
                            },
                            {
                                data: 'tx_unscheduled',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.tx_unscheduled, 'tx_unscheduled', { clinic_num: row.clinic_num }, row.raw?.tx_unscheduled)
                            },
                            {
                                data: 'completed_tx',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.completed_tx, 'completed_tx', { clinic_num: row.clinic_num }, row.raw?.completed_tx)
                            },
                            {
                                data: 'case_acceptance',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.case_acceptance, null, null, row.raw?.case_acceptance)
                            },
                            {
                                data: 'tx_presented',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.tx_presented, 'tx_presented', { clinic_num: row.clinic_num }, row.raw?.tx_presented)
                            },
                            {
                                data: 'avg_tx_plan',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.avg_tx_plan, null, null, row.raw?.avg_tx_plan)
                            },
                            {
                                data: 'patients_with_tx',
                                render: (data, type, row) => renderHeatCell(data, row.heat?.patients_with_tx, null, null, row.raw?.patients_with_tx)
                            }
                        ]
                    });
                }

                return tables[mode];
            }

            function reloadActiveTable() {
                if (tables[activeTabMode]) {
                    tables[activeTabMode].ajax.reload();
                }
            }

            // Tab switching logic
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabPanes = document.querySelectorAll('.tab-pane');

            function activateTxTab(tab) {
                activeTabMode = tab;
                var targetSelect = '#tab-' + tab;
                tabBtns.forEach(b => {
                    b.classList.remove('active', 'border-emerald-500', 'text-emerald-600');
                    b.classList.add('border-transparent', 'text-slate-500');
                });
                var btn = document.querySelector('.tab-btn[data-target="' + targetSelect + '"]');
                if (btn) {
                    btn.classList.add('active', 'border-emerald-500', 'text-emerald-600');
                    btn.classList.remove('border-transparent', 'text-slate-500');
                }
                tabPanes.forEach(pane => { pane.classList.add('hidden'); pane.classList.remove('active'); });
                var target = document.querySelector(targetSelect);
                if (target) { target.classList.remove('hidden'); target.classList.add('active'); }

                // Initialize table for active tab
                const table = initTable(tab);
                if (table) {
                    table.columns.adjust();
                }
            }

            var txTabs = DDS.tabs.deeplink('tab', activateTxTab);
            tabBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const tabKey = btn.getAttribute('data-tab') || btn.getAttribute('data-target').replace('#tab-', '');
                    txTabs.go(tabKey);
                });
            });

            // Deep-link: honor ?tab= on load, else default 'month'
            activateTxTab(txTabs.initial || 'month');

            // Location Change & Date Range change
            $('#txMinerLocation').on('change', reloadActiveTable);

            // Hook onto DateRangePicker apply event and DDS canonical event
            $('#txMinerDateRange').on('apply.daterangepicker', function () {
                reloadActiveTable();
            });
            if (window.DDS && typeof DDS.onDateRange === 'function') {
                DDS.onDateRange('txMinerDateRange', function () {
                    reloadActiveTable();
                });
            }

            // ─── Dropdown Toggles and Filters ─────────────────────────

            // Provider Dropdown
            $('#provDropdownBtn').on('click', function (e) {
                e.stopPropagation();
                $('#provDropdownMenu').toggleClass('hidden');
            });
            $('#provCancelBtn').on('click', function () {
                $('#provDropdownMenu').addClass('hidden');
            });
            $('#provSelectAll').on('click', function () {
                $('.prov-cb').prop('checked', true);
            });
            $('#provClearAll').on('click', function () {
                $('.prov-cb').prop('checked', false);
            });
            $('#provSearchInput').on('input', function () {
                const term = $(this).val().toLowerCase();
                $('#provCheckboxList label').each(function () {
                    const txt = $(this).text().toLowerCase();
                    $(this).toggle(txt.indexOf(term) > -1);
                });
            });
            $('#provApplyBtn').on('click', function () {
                $('#provDropdownMenu').addClass('hidden');
                const checked = $('.prov-cb:checked').length;
                const total = $('.prov-cb').length;
                if (checked === 0 || checked === total) {
                    $('#provDropdownLabel').text('All Providers');
                } else {
                    $('#provDropdownLabel').text(`${checked} selected`);
                }
                reloadActiveTable();
            });

            // Provider LOB Dropdown
            $('#provLobDropdownBtn').on('click', function (e) {
                e.stopPropagation();
                $('#provLobDropdownMenu').toggleClass('hidden');
            });
            $('#provLobCancelBtn').on('click', function () {
                $('#provLobDropdownMenu').addClass('hidden');
            });
            $('#provLobSelectAll').on('click', function () {
                $('.prov-lob-cb').prop('checked', true);
            });
            $('#provLobClearAll').on('click', function () {
                $('.prov-lob-cb').prop('checked', false);
            });
            $('#provLobSearchInput').on('input', function () {
                const term = $(this).val().toLowerCase();
                $('#provLobCheckboxList label').each(function () {
                    const txt = $(this).text().toLowerCase();
                    $(this).toggle(txt.indexOf(term) > -1);
                });
            });
            $('#provLobApplyBtn').on('click', function () {
                $('#provLobDropdownMenu').addClass('hidden');
                const checked = $('.prov-lob-cb:checked').length;
                const total = $('.prov-lob-cb').length;
                if (checked === 0 || checked === total) {
                    $('#provLobDropdownLabel').text('Line of Business: All');
                } else {
                    $('#provLobDropdownLabel').text(`LOB: ${checked} selected`);
                }
                reloadActiveTable();
            });

            // Location LOB Dropdown
            $('#locLobDropdownBtn').on('click', function (e) {
                e.stopPropagation();
                $('#locLobDropdownMenu').toggleClass('hidden');
            });
            $('#locLobCancelBtn').on('click', function () {
                $('#locLobDropdownMenu').addClass('hidden');
            });
            $('#locLobSelectAll').on('click', function () {
                $('.loc-lob-cb').prop('checked', true);
            });
            $('#locLobClearAll').on('click', function () {
                $('.loc-lob-cb').prop('checked', false);
            });
            $('#locLobSearchInput').on('input', function () {
                const term = $(this).val().toLowerCase();
                $('#locLobCheckboxList label').each(function () {
                    const txt = $(this).text().toLowerCase();
                    $(this).toggle(txt.indexOf(term) > -1);
                });
            });
            $('#locLobApplyBtn').on('click', function () {
                $('#locLobDropdownMenu').addClass('hidden');
                const checked = $('.loc-lob-cb:checked').length;
                const total = $('.loc-lob-cb').length;
                if (checked === 0 || checked === total) {
                    $('#locLobDropdownLabel').text('Line of Business: All');
                } else {
                    $('#locLobDropdownLabel').text(`LOB: ${checked} selected`);
                }
                reloadActiveTable();
            });

            // Click outside to close dropdowns
            $(document).on('click', function (e) {
                if (!$(e.target).closest('#provFilterDropdownWrap').length) {
                    $('#provDropdownMenu').addClass('hidden');
                }
                if (!$(e.target).closest('#provLobDropdownWrap').length) {
                    $('#provLobDropdownMenu').addClass('hidden');
                }
                if (!$(e.target).closest('#locLobDropdownWrap').length) {
                    $('#locLobDropdownMenu').addClass('hidden');
                }
            });

            // ─── CSV Export Handlers ──────────────────────────────────
            function triggerExport(tab) {
                const params = getFilters(tab);
                params.tab = tab;
                const url = baseUrl + '/tx-miner/export?' + $.param(params);
                window.location.href = url;
            }

            $('#exportMonthBtn').on('click', () => triggerExport('month'));
            $('#exportProviderBtn').on('click', () => triggerExport('provider'));
            $('#exportLocationBtn').on('click', () => triggerExport('location'));
        });
    </script>

    <x-app-components.patient-modal />
</x-app-layout>