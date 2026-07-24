<x-app-layout>

  <style>
    /* ── Skeleton loader ─────────────────────────────── */
    @keyframes skel-pulse {

      0%,
      100% {
        opacity: 1
      }

      50% {
        opacity: .4
      }
    }

    .skel {
      display: inline-block;
      background: #e5e7eb;
      border-radius: .375rem;
      animation: skel-pulse 1.5s ease-in-out infinite;
    }

    /* ── Tooltip arrow helper ─────────────────────────── */
    .tt-arrow {
      position: absolute;
      top: -5px;
      right: 10px;
      width: 10px;
      height: 10px;
      background: #0f172a;
      transform: rotate(45deg);
      border-radius: 2px;
    }
  </style>
  <header class="bg-white border-b border-gray-100 px-8 py-4 flex justify-between items-center">

  </header>
  <!-- ── HEADER ─────────────────────────────────────────── -->
  <header
    class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between shrink-0 sticky top-0 z-20">
    <div class="flex items-center gap-3">
      <h2 class="text-3xl font-semibold text-slate-700 tracking-wide">Dashboard</h2>
      <!-- Location selector -->
      <div class="relative">
        <select
          class="appearance-none bg-slate-50 border border-slate-200 rounded-lg pl-3 pr-9 py-1.5 text-sm font-semibold text-slate-900 focus:outline-none focus:border-emerald-500 focus:bg-white cursor-pointer transition-colors">
          <option value="all">All Locations</option>
          <option value="8mile" selected>8 Mile</option>
        </select>
        <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-slate-400">
          <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
        </div>
      </div>

      <!-- Date range picker -->
      <x-daterange-picker id="dashDateRange" on-apply="onDrpApply" />

    </div>

    <!-- Right: status + user -->
    <div class="flex items-center gap-4">
      <div
        class="text-xs bg-emerald-50 text-emerald-700 font-semibold px-2.5 py-1 rounded border border-emerald-200 flex items-center gap-1.5">
        <i data-lucide="check-circle" class="w-3 h-3"></i> Open Dental Inbound Match 100%
      </div>
      <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
        <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs">JA
        </div>
        <span class="text-sm font-medium text-slate-700 hidden md:inline">Admin Controller</span>
      </div>
    </div>
  </header>

  <!-- ── MAIN ───────────────────────────────────────────── -->
  <main class="py-3 px-6 space-y-6 w-full mx-auto">

    <!-- Error banner -->
    <div id="dashError"
      class="hidden items-center gap-2 bg-red-50 border border-red-200 text-red-700 text-xs font-medium px-4 py-2.5 rounded-lg">
      <i data-lucide="alert-triangle" class="w-4 h-4 flex-shrink-0"></i>
      Failed to load dashboard data. Check your connection and try again.
    </div>

    <!-- ── KPI CARDS ──────────────────────────────────── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

      <!-- 1 · Gross Production -->
      <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
        <div class="flex items-start justify-between mb-3">
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider leading-tight pr-2">Gross Production</p>
          <div class="relative group flex-shrink-0">
            <button class="text-slate-300 hover:text-emerald-500 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="16" x2="12" y2="12" />
                <line x1="12" y1="8" x2="12.01" y2="8" />
              </svg>
            </button>
            <div
              class="absolute right-0 top-6 w-60 bg-slate-900 text-white text-xs rounded-xl p-3 shadow-2xl opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-150 z-30 leading-relaxed">
              <div class="tt-arrow"></div>
              The total production before any writeoffs or adjustments.
            </div>
          </div>
        </div>
        <div class="text-3xl font-extrabold text-slate-900 mb-2 tabular-nums" id="kpi-gross">
          <span class="skel h-9 w-36 rounded-lg"></span>
        </div>
        <p class="text-xs text-slate-400">Completed procedures in period</p>
      </div>

      <!-- 2 · Net Production -->
      <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
        <div class="flex items-start justify-between mb-3">
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider leading-tight pr-2">Net Production</p>
          <div class="relative group flex-shrink-0">
            <button class="text-slate-300 hover:text-emerald-500 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="16" x2="12" y2="12" />
                <line x1="12" y1="8" x2="12.01" y2="8" />
              </svg>
            </button>
            <div
              class="absolute right-0 top-6 w-64 bg-slate-900 text-white text-xs rounded-xl p-3 shadow-2xl opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-150 z-30 leading-relaxed">
              <div class="tt-arrow"></div>
              The breakdown of the amount of adjustment types in $ relative to both Production and Collections.
            </div>
          </div>
        </div>
        <div class="text-3xl font-extrabold text-slate-900 mb-2 tabular-nums" id="kpi-net">
          <span class="skel h-9 w-36 rounded-lg"></span>
        </div>
        <p class="text-xs text-slate-400">Gross minus adjustments &amp; write-offs</p>
      </div>

      <!-- 3 · Total Adjustment -->
      <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
        <div class="flex items-start justify-between mb-3">
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider leading-tight pr-2">Total Adjustment</p>
          <div class="relative group flex-shrink-0">
            <button class="text-slate-300 hover:text-emerald-500 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="16" x2="12" y2="12" />
                <line x1="12" y1="8" x2="12.01" y2="8" />
              </svg>
            </button>
            <div
              class="absolute right-0 top-6 w-64 bg-slate-900 text-white text-xs rounded-xl p-3 shadow-2xl opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-150 z-30 leading-relaxed">
              <div class="tt-arrow"></div>
              The total amount of adjustment types in $ relative to both Production and Collections.
            </div>
          </div>
        </div>
        <div class="text-3xl font-extrabold text-slate-900 mb-2 tabular-nums" id="kpi-adj">
          <span class="skel h-9 w-36 rounded-lg"></span>
        </div>
        <div class="text-xs text-slate-400">
          Adjustment rate: <span id="kpi-adj-rate" class="font-semibold text-red-500 tabular-nums">
            <span class="skel h-3 w-10 rounded"></span>
          </span>
        </div>
      </div>

      <!-- 4 · Total Collection -->
      <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
        <div class="flex items-start justify-between mb-3">
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider leading-tight pr-2">Total Collection</p>
          <div class="relative group flex-shrink-0">
            <button class="text-slate-300 hover:text-emerald-500 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="16" x2="12" y2="12" />
                <line x1="12" y1="8" x2="12.01" y2="8" />
              </svg>
            </button>
            <div
              class="absolute right-0 top-6 w-64 bg-slate-900 text-white text-xs rounded-xl p-3 shadow-2xl opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-150 z-30 leading-relaxed">
              <div class="tt-arrow"></div>
              The total payment collected in $, after all Collections Adjustments and Refunds.
            </div>
          </div>
        </div>
        <div class="text-3xl font-extrabold text-slate-900 mb-2 tabular-nums" id="kpi-collections">
          <span class="skel h-9 w-36 rounded-lg"></span>
        </div>
        <div class="text-xs text-slate-400">
          Collection rate: <span id="kpi-coll-rate" class="font-semibold text-emerald-600 tabular-nums">
            <span class="skel h-3 w-10 rounded"></span>
          </span>
        </div>
      </div>

      <!-- 5 · Total New Patient Visits -->
      <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
        <div class="flex items-start justify-between mb-3">
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider leading-tight pr-2">Total New Patient
            Visits</p>
          <div class="relative group flex-shrink-0">
            <button class="text-slate-300 hover:text-emerald-500 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="16" x2="12" y2="12" />
                <line x1="12" y1="8" x2="12.01" y2="8" />
              </svg>
            </button>
            <div
              class="absolute right-0 top-6 w-56 bg-slate-900 text-white text-xs rounded-xl p-3 shadow-2xl opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-150 z-30 leading-relaxed">
              <div class="tt-arrow"></div>
              The Total New Patients — patients whose first-ever completed procedure falls in the selected period.
            </div>
          </div>
        </div>
        <div class="text-3xl font-extrabold text-slate-900 mb-2 tabular-nums" id="kpi-new-patients">
          <span class="skel h-9 w-20 rounded-lg"></span>
        </div>
        <p class="text-xs text-slate-400">First-time patients in period</p>
      </div>

      <!-- 6 · Total Patient Visits -->
      <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
        <div class="flex items-start justify-between mb-3">
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider leading-tight pr-2">Total Patient Visits
          </p>
          <div class="relative group flex-shrink-0">
            <button class="text-slate-300 hover:text-emerald-500 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="16" x2="12" y2="12" />
                <line x1="12" y1="8" x2="12.01" y2="8" />
              </svg>
            </button>
            <div
              class="absolute right-0 top-6 w-56 bg-slate-900 text-white text-xs rounded-xl p-3 shadow-2xl opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-150 z-30 leading-relaxed">
              <div class="tt-arrow"></div>
              The total existing patients — distinct patients with at least one completed procedure in the selected
              period.
            </div>
          </div>
        </div>
        <div class="text-3xl font-extrabold text-slate-900 mb-2 tabular-nums" id="kpi-visits">
          <span class="skel h-9 w-20 rounded-lg"></span>
        </div>
        <p class="text-xs text-slate-400">Distinct patients with completed procedures</p>
      </div>

    </div>{{-- /kpi grid --}}

    <!-- ── FINANCIAL PER LOCATION ─────────────────────── -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-6">
      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
        <h2 class="text-lg font-bold text-slate-800">Financials Per Location</h2>
        <button id="exportFinPerLocBtn"
          class="text-xs border border-emerald-500 text-emerald-600 font-semibold px-3 py-1 rounded-lg hover:bg-emerald-50 transition flex-shrink-0 ml-3">
          Export CSV
        </button>
      </div>
      <div class="p-5">
        <div class="relative w-full h-[400px]" id="finPerLocChartContainer">
          <div id="finPerLocSkel" class="absolute inset-0 flex items-end justify-around pb-6 px-10 gap-4">
            <span class="skel w-full h-1/4 rounded-t-sm"></span>
            <span class="skel w-full h-2/4 rounded-t-sm"></span>
            <span class="skel w-full h-1/3 rounded-t-sm"></span>
            <span class="skel w-full h-3/4 rounded-t-sm"></span>
            <span class="skel w-full h-1/2 rounded-t-sm"></span>
            <span class="skel w-full h-[60%] rounded-t-sm"></span>
            <span class="skel w-full h-1/4 rounded-t-sm"></span>
          </div>
          <canvas id="finPerLocChart" class="opacity-0 transition-opacity duration-300"></canvas>
        </div>
      </div>
    </div>

    <!-- ── PATIENT VISITS PER LOCATION ──────────────────── -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

      <!-- New Patient Visit Per Location -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
          <h2 class="text-lg font-bold text-slate-800">New Patient Visit Per Location</h2>
          <button id="exportNewPatVisitsBtn"
            class="text-xs border border-emerald-500 text-emerald-600 font-semibold px-3 py-1 rounded-lg hover:bg-emerald-50 transition flex-shrink-0 ml-3">
            Export CSV
          </button>
        </div>
        <div class="p-5 flex-1 relative">
          <div class="relative w-full h-[400px]">
            <div id="newPatVisitsSkel" class="absolute inset-0 flex items-end justify-around pb-6 px-10 gap-4">
              <span class="skel w-full h-1/4 rounded-t-sm"></span>
              <span class="skel w-full h-2/4 rounded-t-sm"></span>
            </div>
            <canvas id="newPatVisitsChart" class="opacity-0 transition-opacity duration-300"></canvas>
          </div>
        </div>
      </div>

      <!-- Patient Visit Per Location -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
          <h2 class="text-lg font-bold text-slate-800">Patient Visit Per Location</h2>
          <button id="exportPatVisitsBtn"
            class="text-xs border border-emerald-500 text-emerald-600 font-semibold px-3 py-1 rounded-lg hover:bg-emerald-50 transition flex-shrink-0 ml-3">
            Export CSV
          </button>
        </div>
        <div class="p-5 flex-1 relative">
          <div class="relative w-full h-[400px]">
            <div id="patVisitsSkel" class="absolute inset-0 flex items-end justify-around pb-6 px-10 gap-4">
              <span class="skel w-full h-1/4 rounded-t-sm"></span>
              <span class="skel w-full h-2/4 rounded-t-sm"></span>
            </div>
            <canvas id="patVisitsChart" class="opacity-0 transition-opacity duration-300"></canvas>
          </div>
        </div>
      </div>

    </div>

    <!-- ── ANALYTICS: Location + Providers ──────────── -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5 items-start">

      <!-- LEFT: Avg Production Per Patient by Location -->
      <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
          <h2 class="text-lg font-bold text-slate-800">Average Production Per Patient by Location</h2>
          <button id="exportLocationBtn"
            class="text-xs border border-emerald-500 text-emerald-600 font-semibold px-3 py-1 rounded-lg hover:bg-emerald-50 transition flex-shrink-0 ml-3">
            Export CSV
          </button>
        </div>
        <!-- Table header -->
        <div class="grid grid-cols-[2rem_1fr_auto] gap-x-4 px-5 py-2.5 bg-slate-50 border-b border-slate-100">
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">#</span>
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Location</span>
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide text-right">Avg Production</span>
        </div>
        <div id="locationTable">
          <div class="grid grid-cols-[2rem_1fr_auto] gap-x-4 items-center px-5 py-3.5">
            <span class="skel h-4 w-4 rounded"></span>
            <span class="skel h-4 w-24 rounded"></span>
            <span class="skel h-4 w-20 rounded"></span>
          </div>
        </div>
      </div>

      <!-- RIGHT: Provider Performance by Location -->
      <div class="lg:col-span-3 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">

        <!-- Card header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
          <h2 class="text-lg font-bold text-slate-800">Provider Performance by Location</h2>
          <button id="exportProvidersBtn"
            class="text-xs border border-emerald-500 text-emerald-600 font-semibold px-3 py-1 rounded-lg hover:bg-emerald-50 transition flex-shrink-0 ml-3">
            Export CSV
          </button>
        </div>

        <!-- Search + Sort controls -->
        <div class="flex items-center gap-3 px-5 py-3 border-b border-slate-100 bg-slate-50/60">
          <div class="relative flex-1">
            <label class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide block mb-1">Search</label>
            <div class="relative">
              <input type="text" id="providerSearch" placeholder="Search providers…"
                class="w-full border border-slate-200 rounded-lg pl-3 pr-8 py-1.5 text-xs focus:outline-none focus:border-emerald-400">
              <svg class="absolute right-2.5 top-2 w-3.5 h-3.5 text-slate-400 pointer-events-none"
                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.35-4.35" />
              </svg>
            </div>
          </div>
          <div class="flex-shrink-0">
            <label class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide block mb-1">Sort</label>
            <select id="providerSort"
              class="border border-slate-200 rounded-lg px-3 py-1.5 text-xs font-medium text-slate-700 focus:outline-none focus:border-emerald-400 bg-white">
              <option value="gross_desc">Total Gross Prod. (Desc)</option>
              <option value="gross_asc">Total Gross Prod. (Asc)</option>
              <option value="net_desc">Total Net Prod. (Desc)</option>
              <option value="net_asc">Total Net Prod. (Asc)</option>
              <option value="coll_desc">Total Collection (Desc)</option>
              <option value="coll_asc">Total Collection (Asc)</option>
              <option value="adj_desc">Total Adjustment (Desc)</option>
              <option value="adj_asc">Total Adjustment (Asc)</option>
            </select>
          </div>
        </div>

        <!-- Aggregate totals strip -->
        <div class="grid grid-cols-4 border-b border-slate-100">
          <div class="px-4 py-3 border-r border-slate-100">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide leading-tight mb-1">Total
              Gross<br>Production</p>
            <p id="tot-gross" class="text-sm font-extrabold text-slate-900 tabular-nums">
              <span class="skel h-4 w-20 rounded"></span>
            </p>
          </div>
          <div class="px-4 py-3 border-r border-slate-100">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide leading-tight mb-1">Total
              Net<br>Production</p>
            <p id="tot-net" class="text-sm font-extrabold text-slate-900 tabular-nums">
              <span class="skel h-4 w-20 rounded"></span>
            </p>
          </div>
          <div class="px-4 py-3 border-r border-slate-100">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide leading-tight mb-1">
              Total<br>Collection</p>
            <p id="tot-coll" class="text-sm font-extrabold text-slate-900 tabular-nums">
              <span class="skel h-4 w-20 rounded"></span>
            </p>
          </div>
          <div class="px-4 py-3">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide leading-tight mb-1">
              Total<br>Adjustment</p>
            <p id="tot-adj" class="text-sm font-extrabold text-slate-900 tabular-nums">
              <span class="skel h-4 w-20 rounded"></span>
            </p>
          </div>
        </div>

        <!-- Provider card list -->
        <div id="providerList" class="divide-y divide-slate-100 max-h-[480px] overflow-y-auto">
          {{-- skeleton --}}
          @foreach(range(1, 3) as $i)
            <div class="flex items-center gap-4 px-5 py-4">
              <span class="skel h-4 w-4 rounded"></span>
              <span class="skel w-10 h-10 rounded-full flex-shrink-0"></span>
              <div class="flex-1 space-y-1.5">
                <span class="skel h-3 w-32 rounded block"></span>
                <span class="skel h-3 w-20 rounded block"></span>
                <span class="skel h-3.5 w-40 rounded block"></span>
              </div>
              <div class="grid grid-cols-4 gap-4 text-right">
                @foreach(range(1, 4) as $j)
                  <div><span class="skel h-3 w-16 rounded block mb-1"></span><span
                      class="skel h-4 w-16 rounded block"></span></div>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>

      </div>
    </div>{{-- /analytics --}}

    <!-- ── LOCATION UTILIZATION ───────────────────────── -->
    <section id="location-utilization"
      class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-6">
      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
        <h2 class="text-lg font-bold text-slate-800">Location Utilization</h2>
        <button id="exportLocUtilizationBtn"
          class="text-xs border border-emerald-500 text-emerald-600 font-semibold px-3 py-1 rounded-lg hover:bg-emerald-50 transition flex-shrink-0 ml-3">
          Export CSV
        </button>
      </div>
      <div class="p-5">
        <div class="relative w-full h-[400px]" id="locUtilizationChartContainer">
          <div id="locUtilizationSkel" class="absolute inset-0 flex items-end justify-around pb-6 px-10 gap-4 hidden">
            <span class="skel w-full h-1/4 rounded-t-sm"></span>
            <span class="skel w-full h-2/4 rounded-t-sm"></span>
            <span class="skel w-full h-1/3 rounded-t-sm"></span>
            <span class="skel w-full h-3/4 rounded-t-sm"></span>
            <span class="skel w-full h-1/2 rounded-t-sm"></span>
          </div>
          <canvas id="locUtilizationChart" class="transition-opacity duration-300"></canvas>
        </div>
      </div>
    </section>
  </main>

  {{-- Chart.js --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

  <script>
    /* ── State ────────────────────────────────────────── */
    var _providerData = [];
    var _locationData = [];
    var _currentStart = '';
    var _currentEnd = '';
    var _provProductionChart = null;
    var _provVisitsChart = null;
    var _provTxChart = null;
    var _finPerLocChartInstance = null;
    var _finPerLocData = [];
    var _patVisitsPerLocChartInstance = null;
    var _newPatVisitsPerLocChartInstance = null;
    var _patVisitsPerLocData = [];
    var _locUtilizationChartInstance = null;

    /* ── Helpers ──────────────────────────────────────── */
    // Canonical formatter (single source: DDS.fmt.money in ui.js).
    function fmtMoney(v) {
      return DDS.fmt.money(v);
    }

    function fmtMoneyCompact(v) {
      var n = Number(v ?? 0);
      var neg = n < 0;
      var abs = Math.abs(n);
      var str;
      if (abs >= 1000) {
        str = '$' + (abs / 1000).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 1 }) + 'K';
      } else {
        str = '$' + abs.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      }
      return neg ? '(' + str + ')' : str;
    }

    var AVATAR_COLORS = [
      '#10b981', '#3b82f6', '#8b5cf6', '#ec4899',
      '#f59e0b', '#06b6d4', '#ef4444', '#84cc16'
    ];

    function avatarColor(provNum) {
      return AVATAR_COLORS[parseInt(provNum) % AVATAR_COLORS.length];
    }

    function initials(row) {
      var l = (row.LName || '').charAt(0).toUpperCase();
      var p = (row.PName || row.Abbr || '').charAt(0).toUpperCase();
      return l + p;
    }

    function escHtml(s) {
      return String(s ?? '').replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
      });
    }

    /* ── KPI skeletons ────────────────────────────────── */
    function showSkeletons() {
      $('#kpi-gross').html('<span class="skel h-9 w-36 rounded-lg"></span>');
      $('#kpi-net').html('<span class="skel h-9 w-36 rounded-lg"></span>');
      $('#kpi-adj').html('<span class="skel h-9 w-36 rounded-lg"></span>');
      $('#kpi-adj-rate').html('<span class="skel h-3 w-10 rounded"></span>');
      $('#kpi-collections').html('<span class="skel h-9 w-36 rounded-lg"></span>');
      $('#kpi-coll-rate').html('<span class="skel h-3 w-10 rounded"></span>');
      $('#kpi-new-patients').html('<span class="skel h-9 w-20 rounded-lg"></span>');
      $('#kpi-visits').html('<span class="skel h-9 w-20 rounded-lg"></span>');
      $('#dashError').addClass('hidden').removeClass('flex');

      $('#finPerLocSkel').removeClass('hidden');
      $('#finPerLocChart').removeClass('opacity-100');

      $('#newPatVisitsSkel, #patVisitsSkel').removeClass('hidden');
      $('#newPatVisitsChart, #patVisitsChart').removeClass('opacity-100');

      $('#locUtilizationSkel').removeClass('hidden');
      $('#locUtilizationChart').removeClass('opacity-100');
    }

    function populateKpis(data) {
      $('#kpi-gross').text(fmtMoney(data.gross_production));
      $('#kpi-net').text(fmtMoney(data.net_production));
      $('#kpi-adj').text(fmtMoney(data.adjustments));
      $('#kpi-adj-rate').text((data.adjustment_rate ?? 0) + '%');
      $('#kpi-collections').text(fmtMoney(data.collections));
      $('#kpi-coll-rate').text((data.collection_rate ?? 0) + '%');
      $('#kpi-new-patients').text(
        data.new_patient_visit != null ? Number(data.new_patient_visit).toLocaleString() : '—'
      );
      $('#kpi-visits').text(
        data.patient_visits != null ? Number(data.patient_visits).toLocaleString() : '—'
      );
    }

    function showKpiError() {
      ['#kpi-gross', '#kpi-net', '#kpi-adj', '#kpi-collections', '#kpi-new-patients', '#kpi-visits']
        .forEach(function (id) { $(id).text('—'); });
      $('#kpi-adj-rate, #kpi-coll-rate').text('—');
      $('#dashError').removeClass('hidden').addClass('flex');
    }

    /* ── Location table ───────────────────────────────── */
    function renderLocationTable(rows) {
      if (!rows || !rows.length) {
        $('#locationTable').html(
          '<p class="px-5 py-6 text-xs text-slate-400 text-center">No location data for this period.</p>'
        );
        return;
      }
      var html = '';
      rows.forEach(function (row) {
        html += '<div class="grid grid-cols-[2rem_1fr_auto] gap-x-4 items-center px-5 py-3.5 hover:bg-slate-50 transition-colors">';
        html += '<span class="text-xs font-bold text-slate-400">' + escHtml(row.rank) + '</span>';
        html += '<span class="text-sm font-semibold text-slate-800">' + escHtml(row.location) + '</span>';
        html += '<span class="text-sm font-extrabold text-slate-900 tabular-nums text-right">' + fmtMoney(row.avg_production) + '</span>';
        html += '</div>';
      });
      $('#locationTable').html(html);
    }

    /* ── Provider list ────────────────────────────────── */
    function sortedFiltered() {
      var search = $('#providerSearch').val().toLowerCase().trim();
      var sortKey = $('#providerSort').val();

      var data = _providerData.filter(function (r) {
        if (!search) return true;
        var name = ((r.LName || '') + ' ' + (r.PName || '') + ' ' + (r.Abbr || '') + ' ' + (r.specialty || '')).toLowerCase();
        return name.indexOf(search) !== -1;
      });

      var sortMap = {
        gross_desc: ['gross_production', -1],
        gross_asc: ['gross_production', 1],
        net_desc: ['net_production', -1],
        net_asc: ['net_production', 1],
        coll_desc: ['collections', -1],
        coll_asc: ['collections', 1],
        adj_desc: ['adjustments', -1],
        adj_asc: ['adjustments', 1],
      };
      var sm = sortMap[sortKey] || ['gross_production', -1];
      data.sort(function (a, b) {
        return sm[1] * (Number(b[sm[0]]) - Number(a[sm[0]]));
      });
      return data;
    }

    function renderProviders() {
      var data = sortedFiltered();

      /* Totals strip */
      var totals = data.reduce(function (acc, r) {
        acc.gross += Number(r.gross_production || 0);
        acc.net += Number(r.net_production || 0);
        acc.coll += Number(r.collections || 0);
        acc.adj += Number(r.adjustments || 0);
        return acc;
      }, { gross: 0, net: 0, coll: 0, adj: 0 });

      $('#tot-gross').text(fmtMoney(totals.gross));
      $('#tot-net').text(fmtMoney(totals.net));
      $('#tot-coll').text(fmtMoney(totals.coll));
      $('#tot-adj').text(fmtMoneyCompact(totals.adj));

      if (!data.length) {
        $('#providerList').html('<p class="px-5 py-8 text-xs text-slate-400 text-center">No providers match your search.</p>');
        return;
      }

      var html = '';
      data.forEach(function (row, idx) {
        var color = avatarColor(row.ProvNum);
        var initStr = initials(row);
        var name = escHtml(row.LName || '') + (row.PName ? ', ' + escHtml(row.PName) : '');
        var adjVal = Number(row.adjustments || 0);
        var adjFmt = fmtMoneyCompact(adjVal);
        var adjColor = adjVal < 0 ? 'text-red-500' : 'text-slate-900';

        html += '<div class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50/70 transition-colors">';

        /* Rank */
        html += '<span class="text-xs font-bold text-slate-400 w-5 text-center flex-shrink-0">' + (idx + 1) + '</span>';

        /* Avatar */
        html += '<div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center text-white text-xs font-extrabold" style="background:' + color + '">' + escHtml(initStr) + '</div>';

        /* Name + meta */
        html += '<div class="flex-1 min-w-0">';
        html += '<p class="text-[10px] text-slate-400 font-medium">' + escHtml(row.specialty || row.Abbr || '') + '</p>';
        html += '<p class="text-[10px] text-slate-400">' + escHtml(row.location || '8 Mile') + '</p>';
        html += '<p class="text-sm font-bold text-slate-900 truncate">' + name + ' <span class="text-slate-400 font-normal">(' + escHtml(row.appointment_count ?? 0) + ')</span></p>';
        html += '</div>';

        /* Stats */
        html += '<div class="grid grid-cols-4 gap-5 text-right flex-shrink-0">';

        html += '<div>';
        html += '<p class="text-[10px] text-slate-400 font-medium leading-tight">Total Gross<br>Production</p>';
        html += '<p class="text-xs font-bold text-slate-900 tabular-nums mt-0.5">' + fmtMoney(row.gross_production) + '</p>';
        html += '</div>';

        html += '<div>';
        html += '<p class="text-[10px] text-slate-400 font-medium leading-tight">Total Net<br>Production</p>';
        html += '<p class="text-xs font-bold text-slate-900 tabular-nums mt-0.5">' + fmtMoney(row.net_production) + '</p>';
        html += '</div>';

        html += '<div>';
        html += '<p class="text-[10px] text-slate-400 font-medium leading-tight">Total<br>Collection</p>';
        html += '<p class="text-xs font-bold text-slate-900 tabular-nums mt-0.5">' + fmtMoney(row.collections) + '</p>';
        html += '</div>';

        html += '<div>';
        html += '<p class="text-[10px] text-slate-400 font-medium leading-tight">Total<br>Adjustment</p>';
        html += '<p class="text-xs font-bold ' + adjColor + ' tabular-nums mt-0.5">' + adjFmt + '</p>';
        html += '</div>';

        html += '</div>';

        /* Arrow button */
        html += '<button data-provnum="' + row.ProvNum + '" class="provArrow ml-3 flex-shrink-0 text-slate-300 hover:text-emerald-500 transition-colors p-1">';
        html += '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7"/><path d="M7 7h10v10"/></svg>';
        html += '</button>';

        html += '</div>';
      });

      $('#providerList').html(html);
    }

    /* ── CSV exports ──────────────────────────────────── */
    function downloadCsv(filename, rows) {
      var blob = new Blob([rows.join('\n')], { type: 'text/csv' });
      var a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = filename;
      a.click();
      URL.revokeObjectURL(a.href);
    }

    function exportLocationCsv() {
      var rows = ['#,Location,Total Production,Patient Count,Avg Production Per Patient'];
      _locationData.forEach(function (r) {
        rows.push([r.rank, r.location, r.total_production, r.patient_count, r.avg_production].join(','));
      });
      downloadCsv('location-production.csv', rows);
    }

    function exportProvidersCsv() {
      var data = sortedFiltered();
      var rows = ['#,Name,Abbr,Gross Production,Net Production,Collections,Adjustments'];
      data.forEach(function (r, i) {
        var name = (r.LName || '') + (r.PName ? ' ' + r.PName : '');
        rows.push([
          i + 1,
          '"' + name.replace(/"/g, '""') + '"',
          r.Abbr,
          r.gross_production,
          r.net_production,
          r.collections,
          r.adjustments
        ].join(','));
      });
      downloadCsv('provider-performance.csv', rows);
    }

    /* ── Data fetching ────────────────────────────────── */
    function fetchAll(start, end) {
      _currentStart = start;
      _currentEnd = end;

      showSkeletons();

      /* KPIs */
      $.get("{{ route('dashboard.data') }}", { start_date: start, end_date: end })
        .done(populateKpis)
        .fail(showKpiError);

      /* Financials Per Location */
      $.get("{{ route('dashboard.financials-per-location') }}", { start_date: start, end_date: end })
        .done(function (data) {
          _finPerLocData = data;
          renderFinPerLocChart(data);
        })
        .fail(function () {
          console.error("Failed to load financials per location.");
        });

      /* Patient Visits Per Location */
      $.get("{{ route('dashboard.patient-visits-per-location') }}", { start_date: start, end_date: end })
        .done(function (data) {
          _patVisitsPerLocData = data;
          renderPatVisitsCharts(data);
        })
        .fail(function () {
          console.error("Failed to load patient visits per location.");
        });

      /* Location stats */
      $.get("{{ route('dashboard.location-stats') }}", { start_date: start, end_date: end })
        .done(function (data) {
          _locationData = data;
          renderLocationTable(data);
        })
        .fail(function () {
          $('#locationTable').html('<p class="px-5 py-6 text-xs text-red-400 text-center">Failed to load location data.</p>');
        });

      /* Providers */
      $('#tot-gross,#tot-net,#tot-coll,#tot-adj').html('<span class="skel h-4 w-20 rounded"></span>');
      $.get("{{ route('dashboard.providers') }}", { start_date: start, end_date: end })
        .done(function (data) {
          _providerData = data;
          renderProviders();
          renderLocUtilizationChart(data);
        })
        .fail(function () {
          $('#providerList').html('<p class="px-5 py-8 text-xs text-red-400 text-center">Failed to load provider data.</p>');
        });
    }

    /* ── Financials Per Location Chart ────────────────── */
    function renderFinPerLocChart(data) {
      $('#finPerLocSkel').addClass('hidden');
      $('#finPerLocChart').addClass('opacity-100');

      var labels = data.map(function (d) { return d.location; });
      var dsNet = data.map(function (d) { return d.net_production; });
      var dsNetLast = data.map(function (d) { return d.net_production_last; });
      var dsGross = data.map(function (d) { return d.gross_production; });
      var dsGrossLast = data.map(function (d) { return d.gross_production_last; });
      var dsAdj = data.map(function (d) { return d.adjustments; });
      var dsColl = data.map(function (d) { return d.collections; });
      var dsCollLast = data.map(function (d) { return d.collections_last; });

      var ctx = document.getElementById('finPerLocChart');
      if (!ctx) return;

      if (_finPerLocChartInstance) {
        _finPerLocChartInstance.destroy();
      }

      var opts = chartOptions(function (v) { return v >= 1000 || v <= -1000 ? (v / 1000) + 'k' : v; });
      opts.scales.y.min = undefined; // allow negative values

      _finPerLocChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [
            { label: 'Net Production', data: dsNet, backgroundColor: '#fbbf24' },
            { label: 'Net Production Last year', data: dsNetLast, backgroundColor: '#d97706' },
            { label: 'Gross Production', data: dsGross, backgroundColor: '#6ee7b7' },
            { label: 'Gross Production Last year', data: dsGrossLast, backgroundColor: '#34d399' },
            { label: 'Adjustment', data: dsAdj, backgroundColor: '#a855f7' },
            { label: 'Collection', data: dsColl, backgroundColor: '#38bdf8' },
            { label: 'Collection Last year', data: dsCollLast, backgroundColor: '#0284c7' },
          ]
        },
        options: opts
      });
    }

    function exportFinPerLocCsv() {
      var rows = ['Location,Net Production,Net Production Last year,Gross Production,Gross Production Last year,Adjustment,Collection,Collection Last year'];
      _finPerLocData.forEach(function (r) {
        rows.push([
          r.location,
          r.net_production,
          r.net_production_last,
          r.gross_production,
          r.gross_production_last,
          r.adjustments,
          r.collections,
          r.collections_last
        ].join(','));
      });
      downloadCsv('financials-per-location.csv', rows);
    }

    /* ── Patient Visits Per Location Charts ───────────── */
    function renderPatVisitsCharts(data) {
      $('#newPatVisitsSkel, #patVisitsSkel').addClass('hidden');
      $('#newPatVisitsChart, #patVisitsChart').addClass('opacity-100');

      var labels = data.map(function (d) { return d.location; });
      var dsNewPat = data.map(function (d) { return d.new_patient_visits; });
      var dsNewPatLast = data.map(function (d) { return d.new_patient_visits_last; });
      var dsPat = data.map(function (d) { return d.patient_visits; });
      var dsPatLast = data.map(function (d) { return d.patient_visits_last; });

      var ctxNew = document.getElementById('newPatVisitsChart');
      var ctxPat = document.getElementById('patVisitsChart');

      if (_newPatVisitsPerLocChartInstance) _newPatVisitsPerLocChartInstance.destroy();
      if (_patVisitsPerLocChartInstance) _patVisitsPerLocChartInstance.destroy();

      if (ctxNew) {
        _newPatVisitsPerLocChartInstance = new Chart(ctxNew, {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [
              { label: 'New Patient Visits', data: dsNewPat, backgroundColor: '#3b82f6' },
              { label: 'New Patient Visits Last Year', data: dsNewPatLast, backgroundColor: '#51ca8e' }
            ]
          },
          options: chartOptions(function (v) { return v; })
        });
      }

      if (ctxPat) {
        _patVisitsPerLocChartInstance = new Chart(ctxPat, {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [
              { label: 'Patient Visits', data: dsPat, backgroundColor: '#3b82f6' },
              { label: 'Patient Visits Last Year', data: dsPatLast, backgroundColor: '#51ca8e' }
            ]
          },
          options: chartOptions(function (v) { return v; })
        });
      }
    }

    function exportNewPatVisitsCsv() {
      var rows = ['Location,New Patient Visits,New Patient Visits Last Year'];
      _patVisitsPerLocData.forEach(function (r) {
        rows.push([r.location, r.new_patient_visits, r.new_patient_visits_last].join(','));
      });
      downloadCsv('new-patient-visits-per-location.csv', rows);
    }

    function exportPatVisitsCsv() {
      var rows = ['Location,Patient Visits,Patient Visits Last Year'];
      _patVisitsPerLocData.forEach(function (r) {
        rows.push([r.location, r.patient_visits, r.patient_visits_last].join(','));
      });
      downloadCsv('patient-visits-per-location.csv', rows);
    }

    /* ── Location Utilization Chart ───────────────────── */
    function renderLocUtilizationChart(data) {
      $('#locUtilizationSkel').addClass('hidden');
      $('#locUtilizationChart').addClass('opacity-100');

      // Sort data by net production desc
      var sortedData = data.slice().sort(function (a, b) {
        return Number(b.net_production) - Number(a.net_production);
      });

      var labels = sortedData.map(function (d) {
        return (d.LName || '') + (d.PName ? ', ' + d.PName : '');
      });

      var dsNet = sortedData.map(function (d) { return d.net_production; });
      var dsAdj = sortedData.map(function (d) { return d.adjustments; });

      var ctx = document.getElementById('locUtilizationChart');
      if (!ctx) return;

      if (_locUtilizationChartInstance) {
        _locUtilizationChartInstance.destroy();
      }

      var opts = chartOptions(function (v) { return v >= 1000 || v <= -1000 ? (v / 1000) + 'k' : v; });
      opts.scales.x.stacked = false;
      opts.scales.y.stacked = false;
      opts.scales.y.min = undefined; // allow negative
      opts.scales.x.ticks = {
        autoSkip: false,
        font: { size: 11, weight: 'bold' },
        color: '#1e293b',
        maxRotation: 45,
        minRotation: 0
      };

      _locUtilizationChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [
            { label: 'Net Production', data: dsNet, backgroundColor: '#fbbf24' },
            { label: 'Adjustment', data: dsAdj, backgroundColor: '#a855f7' }
          ]
        },
        options: opts
      });
    }

    function exportLocUtilizationCsv() {
      var rows = ['Provider,Net Production,Adjustment'];
      var sortedData = _providerData.slice().sort(function (a, b) {
        return Number(b.net_production) - Number(a.net_production);
      });
      sortedData.forEach(function (r) {
        var name = (r.LName || '') + (r.PName ? ', ' + r.PName : '');
        rows.push(['"' + name.replace(/"/g, '""') + '"', r.net_production, r.adjustments].join(','));
      });
      downloadCsv('location-utilization.csv', rows);
    }

    /* ── Daterangepicker callback (picker initialised by x-daterange-picker component) ── */
    window.onDrpApply = function (start, end) { fetchAll(start, end); };

    $(document).ready(function () {

      /* Search + sort handlers */
      var searchTimer;
      $('#providerSearch').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(renderProviders, 200);
      });
      $('#providerSort').on('change', renderProviders);

      /* Export handlers */
      $('#exportFinPerLocBtn').on('click', exportFinPerLocCsv);
      $('#exportNewPatVisitsBtn').on('click', exportNewPatVisitsCsv);
      $('#exportPatVisitsBtn').on('click', exportPatVisitsCsv);
      $('#exportLocUtilizationBtn').on('click', exportLocUtilizationCsv);
      $('#exportLocationBtn').on('click', exportLocationCsv);
      $('#exportProvidersBtn').on('click', exportProvidersCsv);

      /* Provider arrow click (event delegation) */
      $(document).on('click', '.provArrow', function () {
        openProvider($(this).data('provnum'));
      });

      /* Provider modal: tab switching */
      $(document).on('click', '.prov-tab-btn', function () {
        var tab = $(this).data('tab');
        $('.prov-tab-btn')
          .removeClass('border-emerald-500 text-emerald-600')
          .addClass('border-transparent text-slate-400');
        $(this).addClass('border-emerald-500 text-emerald-600').removeClass('border-transparent text-slate-400');
        $('.prov-tab-content').addClass('hidden');
        $('#ptab-' + tab).removeClass('hidden');
      });

      /* Provider modal: close */
      $(document).on('click', '#closeProviderModal, #providerModalBackdrop', function () {
        $('#providerModal').addClass('hidden');
      });

      /* Initial load */
      fetchAll(
        moment().startOf('month').format('YYYY-MM-DD'),
        moment().format('YYYY-MM-DD')
      );
    });

    /* ── Chart helpers ────────────────────────────────── */
    function allDatesInRange(start, end) {
      var dates = [], cur = moment(start), endM = moment(end);
      while (cur.isSameOrBefore(endM, 'day')) {
        dates.push(cur.format('YYYY-MM-DD'));
        cur.add(1, 'days');
      }
      return dates;
    }

    function buildDailyData(allDates, dataArray, dateKey, valueKey) {
      var map = {};
      (dataArray || []).forEach(function (r) { map[r[dateKey]] = Number(r[valueKey]) || 0; });
      return allDates.map(function (d) { return map[d] || 0; });
    }

    function chartOptions(yCallback, yMax) {
      var opts = {
        responsive: true,
        maintainAspectRatio: false,
        animation: false,
        plugins: {
          legend: {
            position: 'top', align: 'start',
            labels: { usePointStyle: true, pointStyle: 'rect', boxWidth: 12, boxHeight: 8, font: { size: 11 }, color: '#374151' }
          },
          tooltip: { mode: 'index', intersect: false }
        },
        scales: {
          x: {
            grid: { display: false },
            border: { display: false },
            ticks: { autoSkip: true, maxTicksLimit: 26, maxRotation: 90, minRotation: 45, font: { size: 9 }, color: '#9ca3af' }
          },
          y: {
            min: 0,
            grid: { color: '#f1f5f9' },
            border: { display: false },
            ticks: { font: { size: 10 }, color: '#9ca3af', callback: yCallback }
          }
        }
      };
      if (yMax != null) opts.scales.y.max = yMax;
      return opts;
    }

    /* ── Provider modal ───────────────────────────────── */
    function openProvider(provNum) {
      var prov = _providerData.find(function (p) { return String(p.ProvNum) === String(provNum); });
      if (!prov) return;

      /* Avatar + name */
      $('#provModalAvatar').css('background', avatarColor(prov.ProvNum)).text(initials(prov));
      $('#provModalName').text((prov.LName || '') + (prov.PName ? ', ' + prov.PName : ''));
      $('#provModalAbbr').text(prov.Abbr || '');

      /* Reset to Info tab */
      $('.prov-tab-btn').removeClass('border-emerald-500 text-emerald-600').addClass('border-transparent text-slate-400');
      $('.prov-tab-btn[data-tab="info"]').addClass('border-emerald-500 text-emerald-600').removeClass('border-transparent text-slate-400');
      $('.prov-tab-content').addClass('hidden');
      $('#ptab-info').removeClass('hidden');

      /* Show modal */
      $('#providerModal').removeClass('hidden');

      /* Destroy old charts */
      if (_provProductionChart) { _provProductionChart.destroy(); _provProductionChart = null; }
      if (_provVisitsChart) { _provVisitsChart.destroy(); _provVisitsChart = null; }
      if (_provTxChart) { _provTxChart.destroy(); _provTxChart = null; }

      /* Immediately populate Info tab with what we already have */
      $('#pov-net').text(fmtMoney(prov.net_production));
      $('#pov-avg-day, #pov-per-visit, #pov-patient-visits, #pov-new-visits, #pov-specialty, #pov-tx-rate')
        .html('<span class="skel h-5 w-20 rounded"></span>');

      /* Fetch detail data from API */
      $.get("{{ url('/dashboard/providers') }}/" + provNum, { start_date: _currentStart, end_date: _currentEnd })
        .done(function (d) {
          /* ── Info tab ── */
          $('#pov-specialty').text(d.provider.Specialty || 'N/A');
          $('#pov-avg-day').text(fmtMoney(d.stats.avg_production_per_day));
          $('#pov-per-visit').text(fmtMoney(d.stats.production_per_visit));
          $('#pov-patient-visits').text(Number(d.stats.patient_visits).toLocaleString());
          $('#pov-new-visits').text(Number(d.stats.new_patient_visits).toLocaleString());
          $('#pov-tx-rate').text(d.stats.tx_accepted_rate != null ? d.stats.tx_accepted_rate + '%' : 'N/A');

          /* ── Production tab stats ── */
          $('#ptab-prod-net').text(fmtMoney(d.stats.net_production));
          $('#ptab-prod-avg').text(fmtMoney(d.stats.avg_production_per_day));
          $('#ptab-prod-per-visit').text(fmtMoney(d.stats.production_per_visit));

          /* ── Visits tab stats ── */
          $('#ptab-visits-total').text(Number(d.stats.patient_visits).toLocaleString());
          $('#ptab-visits-new').text(Number(d.stats.new_patient_visits).toLocaleString());

          /* ── TX tab stats ── */
          $('#ptab-tx-rate').text(d.stats.tx_accepted_rate != null ? d.stats.tx_accepted_rate + '%' : 'N/A');

          /* ── Build daily date arrays ── */
          var allDates = allDatesInRange(_currentStart, _currentEnd);
          var dateLabels = allDates.map(function (dt) { return moment(dt).format('MMM DD'); });

          /* Production chart */
          _provProductionChart = new Chart(document.getElementById('provProductionChart'), {
            type: 'bar',
            data: {
              labels: dateLabels,
              datasets: [
                {
                  label: 'Production',
                  data: buildDailyData(allDates, d.daily_production, 'date', 'production'),
                  backgroundColor: '#10b981', barPercentage: 1.0, categoryPercentage: 0.6
                },
                {
                  label: 'Production per Visit',
                  data: buildDailyData(allDates, d.daily_production, 'date', 'per_visit'),
                  backgroundColor: '#8b5cf6', barPercentage: 1.0, categoryPercentage: 0.6
                },
              ]
            },
            options: chartOptions(function (v) { return v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v; })
          });

          /* Visits chart */
          _provVisitsChart = new Chart(document.getElementById('provVisitsChart'), {
            type: 'bar',
            data: {
              labels: dateLabels,
              datasets: [
                {
                  label: 'Patient Visits',
                  data: buildDailyData(allDates, d.daily_visits, 'date', 'patient_visits'),
                  backgroundColor: '#10b981', barPercentage: 1.0, categoryPercentage: 0.6
                },
                {
                  label: 'New Patient Visits',
                  data: buildDailyData(allDates, d.daily_visits, 'date', 'new_patient_visits'),
                  backgroundColor: '#8b5cf6', barPercentage: 1.0, categoryPercentage: 0.6
                },
              ]
            },
            options: chartOptions(function (v) { return v; })
          });

          /* TX Accepted chart */
          _provTxChart = new Chart(document.getElementById('provTxChart'), {
            type: 'bar',
            data: {
              labels: dateLabels,
              datasets: [
                {
                  label: 'Accepted TX Plan',
                  data: buildDailyData(allDates, d.daily_tx, 'date', 'rate'),
                  backgroundColor: '#10b981', barPercentage: 1.0, categoryPercentage: 0.6
                },
              ]
            },
            options: chartOptions(function (v) { return v + '%'; }, 100)
          });
        })
        .fail(function () {
          $('#pov-specialty, #pov-avg-day, #pov-per-visit, #pov-patient-visits, #pov-new-visits, #pov-tx-rate').text('—');
          $('#ptab-prod-net, #ptab-prod-avg, #ptab-prod-per-visit, #ptab-visits-total, #ptab-visits-new, #ptab-tx-rate').text('—');
        });
    }
  </script>

  <!-- ── PROVIDER MODAL ──────────────────────────────── -->
  <div id="providerModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div id="providerModalBackdrop" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col">

      <!-- Modal header -->
      <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 flex-shrink-0">
        <h3 class="text-base font-bold text-slate-900">Provider Information</h3>
        <button id="closeProviderModal" class="text-slate-400 hover:text-slate-700 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </button>
      </div>

      <!-- Provider identity -->
      <div class="flex items-center gap-4 px-6 py-4 border-b border-slate-100 flex-shrink-0">
        <div id="provModalAvatar"
          class="w-12 h-12 rounded-full flex items-center justify-center text-white text-sm font-extrabold flex-shrink-0">
        </div>
        <div>
          <h4 id="provModalName" class="text-xl font-bold text-slate-900"></h4>
          <p id="provModalAbbr" class="text-xs text-slate-400 font-medium mt-0.5"></p>
        </div>
      </div>

      <!-- Tabs -->
      <div class="flex border-b border-slate-100 px-6 flex-shrink-0">
        <button class="prov-tab-btn border-b-2 border-emerald-500 text-emerald-600 font-semibold py-3 pr-6 text-sm"
          data-tab="info">Info</button>
        <button
          class="prov-tab-btn border-b-2 border-transparent text-slate-400 hover:text-slate-600 font-medium py-3 pr-6 text-sm"
          data-tab="production">Production</button>
        <button
          class="prov-tab-btn border-b-2 border-transparent text-slate-400 hover:text-slate-600 font-medium py-3 pr-6 text-sm"
          data-tab="visits">Visits</button>
        <button
          class="prov-tab-btn border-b-2 border-transparent text-slate-400 hover:text-slate-600 font-medium py-3 text-sm"
          data-tab="tx-accepted">TX Accepted</button>
      </div>

      <!-- Tab panels -->
      <div class="flex-1 overflow-y-auto">

        <!-- Info tab -->
        <div id="ptab-info" class="prov-tab-content p-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Left: provider info + address -->
            <div>
              <h5 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Provider Information</h5>
              <div class="border border-slate-200 rounded-xl overflow-hidden">
                <div class="grid grid-cols-2 divide-x divide-slate-100 border-b border-slate-100">
                  <div class="p-4">
                    <p class="text-xs text-slate-400 mb-1">Gender</p>
                    <p class="text-sm font-bold text-slate-800">N/A</p>
                  </div>
                  <div class="p-4">
                    <p class="text-xs text-slate-400 mb-1">Specialty</p>
                    <p id="pov-specialty" class="text-sm font-bold text-slate-800">
                      <span class="skel h-4 w-20 rounded"></span>
                    </p>
                  </div>
                </div>
                <!-- Map placeholder -->
                <div class="h-44 bg-slate-100 flex items-center justify-center border-b border-slate-100">
                  <div class="text-center">
                    <svg class="w-8 h-8 text-slate-300 mx-auto mb-1" xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                      <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                      <circle cx="12" cy="10" r="3" />
                    </svg>
                    <p class="text-xs text-slate-400">Map not available</p>
                  </div>
                </div>
                <!-- Address -->
                <div class="grid grid-cols-4 divide-x divide-slate-100 bg-slate-50/50">
                  <div class="p-3">
                    <p class="text-[10px] text-slate-400 mb-1">Address</p>
                    <p class="text-xs font-semibold text-slate-700">--</p>
                  </div>
                  <div class="p-3">
                    <p class="text-[10px] text-slate-400 mb-1">City</p>
                    <p class="text-xs font-semibold text-slate-700">--</p>
                  </div>
                  <div class="p-3">
                    <p class="text-[10px] text-slate-400 mb-1">State</p>
                    <p class="text-xs font-semibold text-slate-700">--</p>
                  </div>
                  <div class="p-3">
                    <p class="text-[10px] text-slate-400 mb-1">Zip</p>
                    <p class="text-xs font-semibold text-slate-700">--</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right: overview stats -->
            <div>
              <h5 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Overview</h5>
              <div class="grid grid-cols-2 gap-3">
                <div class="border border-slate-200 rounded-xl p-4">
                  <p class="text-xs text-slate-400 mb-1.5">Production</p>
                  <p id="pov-net" class="text-xl font-extrabold text-slate-900 tabular-nums leading-tight"></p>
                </div>
                <div class="border border-slate-200 rounded-xl p-4">
                  <p class="text-xs text-slate-400 mb-1.5">Avg. Production / Day</p>
                  <p id="pov-avg-day" class="text-xl font-extrabold text-slate-900 tabular-nums leading-tight"></p>
                </div>
                <div class="border border-slate-200 rounded-xl p-4">
                  <p class="text-xs text-slate-400 mb-1.5">Production / Patient Visit</p>
                  <p id="pov-per-visit" class="text-xl font-extrabold text-slate-900 tabular-nums leading-tight">
                    <span class="skel h-6 w-24 rounded"></span>
                  </p>
                </div>
                <div class="border border-slate-200 rounded-xl p-4">
                  <p class="text-xs text-slate-400 mb-1.5">Accepted TX Plan</p>
                  <p id="pov-tx-rate" class="text-xl font-extrabold text-slate-900 tabular-nums leading-tight">
                    <span class="skel h-6 w-16 rounded"></span>
                  </p>
                </div>
                <div class="border border-slate-200 rounded-xl p-4">
                  <p class="text-xs text-slate-400 mb-1.5">Patient Visits</p>
                  <p id="pov-patient-visits" class="text-xl font-extrabold text-slate-900 tabular-nums leading-tight">
                    <span class="skel h-6 w-12 rounded"></span>
                  </p>
                </div>
                <div class="border border-slate-200 rounded-xl p-4">
                  <p class="text-xs text-slate-400 mb-1.5">New Patient Visits</p>
                  <p id="pov-new-visits" class="text-xl font-extrabold text-slate-900 tabular-nums leading-tight">
                    <span class="skel h-6 w-12 rounded"></span>
                  </p>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- Production tab -->
        <div id="ptab-production" class="prov-tab-content hidden p-6">
          <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 mb-5 flex flex-wrap gap-8">
            <div>
              <p class="text-xs text-slate-400 mb-1">Production</p>
              <p id="ptab-prod-net" class="text-2xl font-extrabold text-slate-900 tabular-nums">—</p>
            </div>
            <div class="border-l border-slate-200 pl-8">
              <p class="text-xs text-slate-400 mb-1">Avg. Production / Day</p>
              <p id="ptab-prod-avg" class="text-2xl font-extrabold text-slate-900 tabular-nums">—</p>
            </div>
            <div class="border-l border-slate-200 pl-8">
              <p class="text-xs text-slate-400 mb-1">Production / Patient Visit</p>
              <p id="ptab-prod-per-visit" class="text-2xl font-extrabold text-slate-900 tabular-nums">—</p>
            </div>
          </div>
          <div style="position:relative; height:280px;">
            <canvas id="provProductionChart"></canvas>
          </div>
        </div>

        <!-- Visits tab -->
        <div id="ptab-visits" class="prov-tab-content hidden p-6">
          <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 mb-5 flex flex-wrap gap-8">
            <div>
              <p class="text-xs text-slate-400 mb-1">Patient Visits</p>
              <p id="ptab-visits-total" class="text-2xl font-extrabold text-slate-900 tabular-nums">—</p>
            </div>
            <div class="border-l border-slate-200 pl-8">
              <p class="text-xs text-slate-400 mb-1">New Patient Visits</p>
              <p id="ptab-visits-new" class="text-2xl font-extrabold text-slate-900 tabular-nums">—</p>
            </div>
          </div>
          <div style="position:relative; height:280px;">
            <canvas id="provVisitsChart"></canvas>
          </div>
        </div>

        <!-- TX Accepted tab -->
        <div id="ptab-tx-accepted" class="prov-tab-content hidden p-6">
          <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 mb-5 inline-block">
            <p class="text-xs text-slate-400 mb-1">Accepted TX Plan</p>
            <p id="ptab-tx-rate" class="text-2xl font-extrabold text-slate-900 tabular-nums">—</p>
          </div>
          <div style="position:relative; height:280px;">
            <canvas id="provTxChart"></canvas>
          </div>
        </div>

      </div>
    </div>
  </div>


</x-app-layout>