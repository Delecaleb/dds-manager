<x-app-layout>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- CDN dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

  <style>
    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange,
    .flatpickr-day.selected:hover,
    .flatpickr-day.startRange:hover,
    .flatpickr-day.endRange:hover {
      background: #059669;
      border-color: #059669;
    }

    .flatpickr-day.inRange {
      background: #d1fae5;
      border-color: #d1fae5;
      box-shadow: -5px 0 0 #d1fae5, 5px 0 0 #d1fae5;
    }

    .flatpickr-day.today {
      border-color: #059669;
    }

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
      background: #e5e7eb;
      border-radius: .375rem;
      animation: skel-pulse 1.5s ease-in-out infinite;
      display: inline-block;
    }

    /* ── Breakdown modal table ── */
    #bkTable th {
      background: #e5e7eb;
      font-size: .75rem;
      font-weight: 700;
      color: #111827;
      border-bottom: 1px solid #d1d5db;
      border-right: 1px solid #d1d5db;
      padding: .75rem 1rem;
      white-space: nowrap;
    }

    #bkTable td {
      font-size: .8rem;
      color: #374151;
      padding: .625rem 1rem;
      border-bottom: 1px solid #f3f4f6;
      border-right: 1px solid #f3f4f6;
      white-space: nowrap;
    }

    #bkTable tbody tr:hover td {
      background: #f9fafb;
    }

    #bkTable tfoot td {
      font-size: .8rem;
      font-weight: 700;
      color: #111827;
      padding: .625rem 1rem;
      border-top: 2px solid #e5e7eb;
      background: #f9fafb;
    }

    .bk-patient {
      color: #d97706;
    }

    .bk-provider {
      color: #0d9488;
    }

    .bk-prov-id {
      color: #0d9488;
    }

    .bk-count {
      color: #3b82f6;
    }

    .bk-money {
      text-align: right;
      font-variant-numeric: tabular-nums;
    }

    .bk-center {
      text-align: center;
    }

    /* expand button */
    .kpi-expand-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 1.5rem;
      height: 1.5rem;
      border-radius: .375rem;
      color: #9ca3af;
      transition: color .15s, background .15s;
    }

    .kpi-expand-btn:hover {
      color: #059669;
      background: #d1fae5;
    }

    /* ── Score Cards table ── */
    #scTable th {
      background: #f9fafb;
      font-size: .7rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .05em;
      color: #6b7280;
      border-bottom: 1px solid #e5e7eb;
      padding: .625rem 1rem;
      white-space: nowrap;
    }

    #scTable td {
      font-size: .8rem;
      color: #374151;
      padding: .5rem 1rem;
      border-bottom: 1px solid #f3f4f6;
      white-space: nowrap;
    }

    #scTable tfoot td {
      font-size: .8rem;
      font-weight: 700;
      color: #111827;
      padding: .625rem 1rem;
      background: #f9fafb;
    }

    #scTable tfoot tr:first-child td {
      border-top: 2px solid #e5e7eb;
    }

    .sc-row-top {
      background: #f0fdf4;
    }

    .sc-row-mid {
      background: #fefce8;
    }

    .sc-row-bottom {
      background: #fef2f2;
    }

    /* tier filter buttons */
    .sc-tier-btn {
      padding: .375rem .875rem;
      border-radius: .375rem;
      font-size: .75rem;
      font-weight: 600;
      border: 1px solid #d1d5db;
      background: #fff;
      color: #4b5563;
      cursor: pointer;
      transition: background .15s, color .15s, border-color .15s;
    }

    .sc-tier-btn:hover {
      background: #f3f4f6;
    }

    .sc-tier-btn.active-all {
      background: #6b7280;
      color: #fff;
      border-color: #6b7280;
    }

    .sc-tier-btn.active-top {
      background: #059669;
      color: #fff;
      border-color: #059669;
    }

    .sc-tier-btn.active-mid {
      background: #d97706;
      color: #fff;
      border-color: #d97706;
    }

    .sc-tier-btn.active-bottom {
      background: #dc2626;
      color: #fff;
      border-color: #dc2626;
    }

    /* main tab active */
    .main-tab {
      border-bottom: 2px solid transparent;
      padding: .875rem 0 .75rem;
      font-size: .875rem;
      font-weight: 500;
      color: #6b7280;
      cursor: pointer;
      background: none;
      border-left: none;
      border-right: none;
      border-top: none;
    }

    .main-tab:hover {
      color: #374151;
    }

    .main-tab.active {
      border-bottom-color: #059669;
      color: #059669;
      font-weight: 700;
    }

    /* score sub-tab */
    .sc-sub-tab {
      padding: .375rem 1.25rem;
      border-radius: 9999px;
      font-size: .8rem;
      font-weight: 600;
      cursor: pointer;
      border: 1px solid #d1d5db;
      background: #fff;
      color: #4b5563;
      transition: all .15s;
    }

    .sc-sub-tab:hover {
      background: #f3f4f6;
    }

    .sc-sub-tab.active {
      background: #059669;
      color: #fff;
      border-color: #059669;
    }
  </style>

  <header class="bg-white border-b border-gray-100 px-8 py-4 flex justify-between items-center">
    <div class="flex items-center space-x-2">
      <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">Financials</h1>
    </div>
  </header>

  <section class="bg-white border-b border-gray-200 px-8 py-4">
    <div class="flex flex-wrap items-center gap-3">
      <x-daterange-picker on-apply="onDrpApply" />
      <select
        class="border border-gray-300 rounded px-4 py-1.5 text-sm bg-white focus:outline-none focus:border-emerald-500 shadow-sm font-medium text-gray-700">
        <option selected>8 Mile</option>
      </select>
      <span id="fetchError" class="hidden text-xs text-red-600 font-medium">
        <i class="fa-solid fa-triangle-exclamation mr-1"></i>Failed to load data.
      </span>
    </div>
  </section>

  <section class="px-8 bg-white border-b border-gray-200 flex gap-6 text-sm font-medium text-gray-500">
    <button class="main-tab active" id="tabSummary" onclick="switchMainTab('summary')">Summary</button>
    <button class="main-tab" id="tabScoreCards" onclick="switchMainTab('score-cards')">Score Cards</button>
  </section>

  {{-- ── Summary Panel ──────────────────────────────────────────────────────── --}}
  <main id="summaryPanel" class="p-6 space-y-6 max-w-[1600px] mx-auto">

    <div class="font-bold border-b p-3 text-sm text-gray-700">Revenue</div>
    <section class="grid grid-cols-1 md:grid-cols-4 gap-6">

      {{-- Gross Production --}}
      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div class="flex-1">
            <div class="flex items-center gap-1.5">
              <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Gross Production</p>
              <button class="kpi-expand-btn" onclick="openBreakdown('gross_production','Gross Production')">
                <i data-lucide="arrow-up-right-square" class="w-4 h-4"></i>
              </button>
            </div>
            <h4 class="text-3xl font-black text-gray-900 mt-2" id="gross-production">
              <span class="skel h-8 w-32"></span>
            </h4>
          </div>
          <div class="p-3 bg-emerald-50 rounded-lg text-emerald-600">
            <i class="fa-solid fa-wallet text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
          Completed procedures in the selected period
        </div>
      </div>

      {{-- Net Production --}}
      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div class="flex-1">
            <div class="flex items-center gap-1.5">
              <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Net Production</p>
              <button class="kpi-expand-btn" onclick="openBreakdown('net_production','Production')">
                <i data-lucide="arrow-up-right-square" class="w-4 h-4"></i>
              </button>
            </div>
            <h4 class="text-3xl font-black text-gray-900 mt-2" id="net-production">
              <span class="skel h-8 w-32"></span>
            </h4>
          </div>
          <div class="p-3 bg-teal-50 rounded-lg text-teal-600">
            <i class="fa-solid fa-calendar-day text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
          Gross minus adjustments and write-offs
        </div>
      </div>

      {{-- Adjustment --}}
      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div class="flex-1">
            <div class="flex items-center gap-1.5">
              <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Adjustment</p>
              <button class="kpi-expand-btn" onclick="openBreakdown('adjustment','Adjustment')">
                <i data-lucide="arrow-up-right-square" class="w-4 h-4"></i>
              </button>
            </div>
            <h4 class="text-3xl font-black text-gray-900 mt-2" id="adjustment">
              <span class="skel h-8 w-28"></span>
            </h4>
          </div>
          <div class="p-3 bg-red-50 rounded-lg text-red-500">
            <i class="fa-solid fa-sliders text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
          <span id="adjustment-rate" class="text-red-600 font-semibold"><span class="skel h-3 w-14"></span></span>
          adjustment rate
        </div>
      </div>

      {{-- Collection --}}
      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div class="flex-1">
            <div class="flex items-center gap-1.5">
              <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Collection</p>
              <button class="kpi-expand-btn" onclick="openBreakdown('collection','Collection')">
                <i data-lucide="arrow-up-right-square" class="w-4 h-4"></i>
              </button>
            </div>
            <h4 class="text-3xl font-black text-gray-900 mt-2" id="collections-amt">
              <span class="skel h-8 w-24"></span>
            </h4>
          </div>
          <div class="p-3 bg-amber-50 rounded-lg text-amber-600">
            <i class="fa-solid fa-percent text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
          Target: 80.00% &nbsp;|&nbsp;
          <span id="collection-rate" class="font-semibold text-gray-700"><span class="skel h-3 w-20"></span></span>
          collected
        </div>
      </div>
    </section>

    <div class="font-bold border-b p-3 text-sm text-gray-700">Patients</div>
    <section class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6">

      {{-- Patient Visits --}}
      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div class="flex-1">
            <div class="flex items-center gap-1.5">
              <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Patient Visits</p>
              <button class="kpi-expand-btn" onclick="openPatientVisitsBreakdown('Pts Visits')">
                <i data-lucide="arrow-up-right-square" class="w-4 h-4"></i>
              </button>
            </div>
            <h4 class="text-3xl font-black text-gray-900 mt-2" id="patient-visits">
              <span class="skel h-8 w-16"></span>
            </h4>
          </div>
          <div class="p-3 bg-blue-50 rounded-lg text-blue-600">
            <i class="fa-solid fa-user-plus text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500"></div>
      </div>

      {{-- New Patient Visits --}}
      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div class="flex-1">
            <div class="flex items-center gap-1.5">
              <p class="text-xs font-bold uppercase tracking-wider text-gray-500">New Patient Visits</p>
              <button class="kpi-expand-btn" onclick="openNewPatientVisitsBreakdown('New Patient Visits')">
                <i data-lucide="arrow-up-right-square" class="w-4 h-4"></i>
              </button>
            </div>
            <h4 class="text-3xl font-black text-gray-900 mt-2" id="new-patient-visits">
              <span class="skel h-8 w-16"></span>
            </h4>
          </div>
          <div class="p-3 bg-blue-50 rounded-lg text-blue-600">
            <i class="fa-solid fa-user-plus text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500"></div>
      </div>

      {{-- Patient Scheduled --}}
      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div class="flex-1">
            <div class="flex items-center gap-1.5">
              <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Patient Scheduled</p>
              <button class="kpi-expand-btn" onclick="openPatientsScheduledBreakdown('Patients Scheduled')">
                <i data-lucide="arrow-up-right-square" class="w-4 h-4"></i>
              </button>
            </div>
            <h4 class="text-3xl font-black text-gray-900 mt-2" id="patients-scheduled">
              <span class="skel h-8 w-16"></span>
            </h4>
          </div>
          <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600">
            <i class="fa-solid fa-user-check text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
          Appointments scheduled in the period
        </div>
      </div>

      {{-- New Patient Scheduled --}}
      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div class="flex-1">
            <div class="flex items-center gap-1.5">
              <p class="text-xs font-bold uppercase tracking-wider text-gray-500">New Patient Scheduled</p>
              <button class="kpi-expand-btn" onclick="openNewPatientsScheduledBreakdown('New Patient Scheduled')">
                <i data-lucide="arrow-up-right-square" class="w-4 h-4"></i>
              </button>
            </div>
            <h4 class="text-3xl font-black text-gray-900 mt-2" id="new-patients-scheduled">
              <span class="skel h-8 w-16"></span>
            </h4>
          </div>
          <div class="p-3 bg-gray-50 rounded-lg text-gray-600">
            <i class="fa-solid fa-user-slash text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
          First-time patients booked in the period
        </div>
      </div>

      {{-- Broken / Cancelled --}}
      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div class="flex-1">
            <div class="flex items-center gap-1.5">
              <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Broken/Cancelled</p>
              <button class="kpi-expand-btn" onclick="openBrokenCancelledBreakdown('Broken & Cancelled')">
                <i data-lucide="arrow-up-right-square" class="w-4 h-4"></i>
              </button>
            </div>
            <h4 class="text-3xl font-black text-gray-900 mt-2" id="broken-cancelled">
              <span class="skel h-8 w-16"></span>
            </h4>
          </div>
          <div class="p-3 bg-red-50 rounded-lg text-red-600">
            <i class="fa-solid fa-user-xmark text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
          D9986 / D9987 procedures
        </div>
      </div>

      {{-- Avg Production Per Patient --}}
      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div class="flex-1">
            <div class="flex items-center gap-1.5">
              <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Avg Production / Patient</p>
              <button class="kpi-expand-btn"
                onclick="openBreakdown('avg_production_per_patient','Average Production Per Patient')">
                <i data-lucide="arrow-up-right-square" class="w-4 h-4"></i>
              </button>
            </div>
            <h4 class="text-3xl font-black text-gray-900 mt-2" id="patient-avg-production">
              <span class="skel h-8 w-16"></span>
            </h4>
          </div>
          <div class="p-3 bg-gray-50 rounded-lg text-gray-600">
            <i class="fa-solid fa-dollar-sign text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">--</div>
      </div>

    </section>

    {{-- ── Utilization Data Chart (Production by Provider) ────────────────────── --}}
    <div class="mt-8">
      <div class="font-bold border-b p-3 text-lg text-black bg-gray-50 border-gray-100 flex items-center">
        Production (by Provider)
      </div>
      <div class="bg-white p-8 border border-gray-100 border-t-0 shadow-sm relative pt-[60px]">
        <div class="h-[400px] w-full">
          <canvas id="utilizationChart"></canvas>
        </div>
      </div>
    </div>

    {{-- ── Adjustment & Top Services Charts ──────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">

      <div>
        <div class="font-bold border-b p-3 text-lg text-black bg-gray-50 border-gray-100 flex items-center">
          Adjustment Percent
        </div>
        <div
          class="bg-white p-6 border border-gray-100 border-t-0 shadow-sm relative pt-[60px] flex items-center justify-center">
          <div class="w-full h-[320px]">
            <canvas id="adjustmentChart"></canvas>
          </div>
        </div>
      </div>

      <div>
        <div class="font-bold border-b p-3 text-lg text-black bg-gray-50 border-gray-100 flex items-center">
          Top Services
        </div>
        <div
          class="bg-white p-6 border border-gray-100 border-t-0 shadow-sm relative pt-[60px] flex items-center justify-center">
          <div class="w-full h-[320px]">
            <canvas id="topServicesChart"></canvas>
          </div>
        </div>
      </div>

    </div>

    {{-- ── Daily Revenue Chart ───────────────────────────────────────────────── --}}
    <div class="mt-8">
      <div class="font-bold border-b p-3 text-lg text-black bg-gray-50 border-gray-100 flex items-center">
        Daily Revenue Numbers
      </div>
      <div class="bg-white p-6 border border-gray-100 border-t-0 shadow-sm relative pt-[20px]">
        <div class="h-[400px] w-full">
          <canvas id="dailyRevenueChart"></canvas>
        </div>
      </div>
    </div>

    {{-- ── Daily Patient Statistics ──────────────────────────────────────────── --}}
    <div class="mt-8">
      <div class="font-bold border-b p-3 text-lg text-black bg-gray-50 border-gray-100 flex items-center">
        Daily Patient Statistics
      </div>
      <div class="bg-white p-6 border border-gray-100 border-t-0 shadow-sm relative pt-[20px]">
        <div class="h-[400px] w-full">
          <canvas id="dailyPatientStatsChart"></canvas>
        </div>
      </div>
    </div>
  </main>

  {{-- ── Score Cards Panel ───────────────────────────────────────────────────── --}}
  <div id="scoreCardsPanel" class="hidden p-6 space-y-5 max-w-[1600px] mx-auto">

    {{-- Header row: sub-tabs + filters --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
      <div class="flex items-center gap-2">
        <h2 class="text-lg font-bold text-gray-800 mr-3">Score Cards</h2>
        <button class="sc-sub-tab active" id="scTabProd" onclick="switchScTab('production')">Production</button>
        <button class="sc-sub-tab" id="scTabColl" onclick="switchScTab('collection')">Collection</button>
      </div>
      <div class="flex items-center gap-2">
        <select id="scEntity"
          class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-emerald-500 text-gray-700">
          <option value="provider">Provider</option>
          <option value="office">Office</option>
        </select>
        <select id="scProvider"
          class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-emerald-500 text-gray-700"
          onchange="loadScoreCards()">
          <option value="">All</option>
        </select>
      </div>
    </div>

    {{-- KPI metrics row --}}
    <div id="scKpiRow" class="flex flex-wrap gap-4">
      <div class="text-sm text-gray-400">Loading&hellip;</div>
    </div>

    {{-- Charts row --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-bold text-gray-700 mb-4">Top Counts</h3>
        <div class="flex justify-center" style="height:260px">
          <canvas id="scChartCounts"></canvas>
        </div>
      </div>
      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 id="scChart2Title" class="text-sm font-bold text-gray-700 mb-4">Top Services</h3>
        <div class="flex justify-center" style="height:260px">
          <canvas id="scChartValues"></canvas>
        </div>
      </div>
    </div>

    {{-- Table card --}}
    <div class="bg-white rounded-xl border border-gray-200">

      {{-- Tier tabs + search + export --}}
      <div class="flex items-center justify-between flex-wrap gap-3 px-5 py-4 border-b border-gray-200">
        <div class="flex items-center gap-2">
          <button class="sc-tier-btn active-all" data-tier="all" onclick="scSetTier('all')">All</button>
          <button class="sc-tier-btn" data-tier="top" onclick="scSetTier('top')">Top 20%</button>
          <button class="sc-tier-btn" data-tier="mid" onclick="scSetTier('mid')">Mid Tier</button>
          <button class="sc-tier-btn" data-tier="bottom" onclick="scSetTier('bottom')">Bottom 20%</button>
        </div>
        <div class="flex items-center gap-2">
          <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
              fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input id="scSearch" type="text" placeholder="Search"
              class="pl-8 pr-3 py-1.5 text-sm border border-gray-300 rounded focus:outline-none focus:border-emerald-400 w-44"
              oninput="scOnSearch(this.value)">
          </div>
          <button id="scExportBtn"
            class="text-sm font-semibold border border-emerald-500 text-emerald-600 hover:bg-emerald-50 px-4 py-1.5 rounded transition-colors"
            onclick="scExportCsv()">
            Export CSV
          </button>
        </div>
      </div>

      {{-- Table --}}
      <div class="overflow-x-auto">
        <table id="scTable" class="w-full border-collapse">
          <thead id="scThead"></thead>
          <tbody id="scTbody">
            <tr>
              <td colspan="6" class="text-center py-10 text-gray-400 text-sm">Loading&hellip;</td>
            </tr>
          </tbody>
          <tfoot id="scTfoot"></tfoot>
        </table>
      </div>

      {{-- Pagination --}}
      <div
        class="flex items-center justify-between px-5 py-3 border-t border-gray-200 text-xs text-gray-600 bg-gray-50 rounded-b-xl">
        <div class="flex items-center gap-2">
          <span>Items per page:</span>
          <select id="scPageSize"
            class="border border-gray-300 rounded px-2 py-0.5 text-xs focus:outline-none focus:border-emerald-400"
            onchange="scOnPageSize(this.value)">
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="50">50</option>
          </select>
          <span id="scInfo" class="text-gray-500">—</span>
        </div>
        <div class="flex items-center gap-2">
          <select id="scPageNum"
            class="border border-gray-300 rounded px-2 py-0.5 text-xs focus:outline-none focus:border-emerald-400"
            onchange="scOnPageSelect(this.value)"></select>
          <span id="scPageOf" class="text-gray-500">of 1 pages</span>
          <button id="scPrev"
            class="w-7 h-7 flex items-center justify-center border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-40"
            onclick="scChangePage(-1)">&lsaquo;</button>
          <button id="scNext"
            class="w-7 h-7 flex items-center justify-center border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-40"
            onclick="scChangePage(1)">&rsaquo;</button>
        </div>
      </div>
    </div>
  </div>

  {{-- ── Breakdown Modal ─────────────────────────────────────────────────────── --}}
  <div id="bkOverlay" class="fixed inset-0 z-50 hidden bg-black/60 flex items-center justify-center p-4">
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-5xl flex flex-col" style="max-height:90vh">

      <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200 shrink-0">
        <h2 id="bkTitle" class="text-2xl font-bold text-slate-900 tracking-tight">Financial Breakdown</h2>
        <button onclick="closeBkModal()"
          class="text-slate-900 hover:text-slate-600 text-3xl font-bold font-sans transition-colors cursor-pointer leading-none">&times;</button>
      </div>

      <div class="flex items-center justify-end gap-3 px-6 py-3.5 border-b border-gray-100 shrink-0">
        <div class="relative">
          <input id="bkSearch" type="text" placeholder="Search"
            class="pl-3 pr-8 py-1.5 text-sm border border-slate-400 rounded focus:outline-none focus:border-emerald-500 w-52 shadow-xs">
          <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <button id="bkExportBtn"
          class="text-xs font-bold border border-emerald-500 text-emerald-600 hover:bg-emerald-50 px-4 py-2 rounded transition-colors shadow-xs">
          Export CSV
        </button>
      </div>

      <div class="flex-1 overflow-auto">
        <table id="bkTable" class="w-full border-collapse">
          <thead id="bkThead"></thead>
          <tbody id="bkTbody">
            <tr>
              <td colspan="10" class="text-center py-10 text-gray-400 text-sm">Loading…</td>
            </tr>
          </tbody>
          <tfoot id="bkTfoot"></tfoot>
        </table>
      </div>

      <div
        class="flex items-center justify-between px-6 py-3 border-t border-gray-200 text-xs text-gray-600 shrink-0 bg-gray-50 rounded-b-xl">
        <div class="flex items-center gap-2">
          <span>Items per page:</span>
          <select id="bkPageSize"
            class="border border-gray-300 rounded px-2 py-0.5 text-xs focus:outline-none focus:border-emerald-400">
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="50">50</option>
          </select>
          <span id="bkInfo" class="text-gray-500">—</span>
        </div>
        <div class="flex items-center gap-2">
          <select id="bkPageNum"
            class="border border-gray-300 rounded px-2 py-0.5 text-xs focus:outline-none focus:border-emerald-400"></select>
          <span id="bkPageOf" class="text-gray-500">of 1 pages</span>
          <button id="bkPrev"
            class="w-7 h-7 flex items-center justify-center border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-40"
            onclick="bkChangePage(-1)">&lsaquo;</button>
          <button id="bkNext"
            class="w-7 h-7 flex items-center justify-center border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-40"
            onclick="bkChangePage(1)">&rsaquo;</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    const baseUrl = "{{ url('') }}";

    // Canonical formatter (single source: DDS.fmt.money in ui.js). Also fixes null -> "$NaN".
    function fmtMoney(v) {
      return DDS.fmt.money(v);
    }

    /* ── Main tab switching (URL-driven + deep-linkable via DDS.tabs.deeplink) ─── */
    function activateMainTab(tab) {
      var isSummary = tab !== 'score-cards';
      document.getElementById('summaryPanel').classList.toggle('hidden', !isSummary);
      document.getElementById('scoreCardsPanel').classList.toggle('hidden', isSummary);
      document.getElementById('tabSummary').classList.toggle('active', isSummary);
      document.getElementById('tabScoreCards').classList.toggle('active', !isSummary);
      if (!isSummary && !_sc.data) loadScoreCards();
    }

    var finTabs = DDS.tabs.deeplink('tab', activateMainTab);
    // Public entry kept for the existing onclick="switchMainTab(...)" handlers.
    function switchMainTab(tab) { finTabs.go(tab); }

    /* ── Summary KPI fetching ────────────────────────────────────────────────── */
    function showSkeletons() {
      $('#gross-production').html('<span class="skel h-8 w-32"></span>');
      $('#net-production').html('<span class="skel h-8 w-32"></span>');
      $('#adjustment').html('<span class="skel h-8 w-28"></span>');
      $('#adjustment-rate').html('<span class="skel h-3 w-14"></span>');
      $('#collection-rate').html('<span class="skel h-8 w-24"></span>');
      $('#collections-amt').html('<span class="skel h-3 w-20"></span>');
      $('#patient-visits, #patients-scheduled, #new-patients-scheduled, #new-patient-visits, #patient-avg-production, #broken-cancelled')
        .html('<span class="skel h-8 w-16"></span>');
      $('#fetchError').addClass('hidden');
    }

    function populate(data) {
      if (data.gross_production !== undefined) $('#gross-production').text(fmtMoney(data.gross_production));
      if (data.net_production !== undefined) $('#net-production').text(fmtMoney(data.net_production));
      if (data.adjustments !== undefined) $('#adjustment').text(fmtMoney(data.adjustments));
      if (data.adjustment_rate !== undefined) $('#adjustment-rate').text(data.adjustment_rate + '%');
      if (data.collection_rate !== undefined) $('#collection-rate').text(data.collection_rate + '%');
      if (data.collections !== undefined) $('#collections-amt').text(fmtMoney(data.collections));
      if (data.patient_visits !== undefined) $('#patient-visits').text(data.patient_visits);
      if (data.patient_scheduled !== undefined) $('#patients-scheduled').text(data.patient_scheduled);
      if (data.new_patients_scheduled !== undefined) $('#new-patients-scheduled').text(data.new_patients_scheduled);
      if (data.new_patient_visit !== undefined) $('#new-patient-visits').text(data.new_patient_visit);
      if (data.broken_cancelled !== undefined) $('#broken-cancelled').text(data.broken_cancelled);
      if (data.patient_avg_production !== undefined) $('#patient-avg-production').text(fmtMoney(data.patient_avg_production));

      if (data.daily_revenue !== undefined) renderDailyRevenueChart(data.daily_revenue);
      if (data.daily_patient_stats !== undefined) renderDailyPatientStatsChart(data.daily_patient_stats);
      if (data.utilization !== undefined) renderUtilizationChart(data.utilization);
      if (data.adjustments_breakdown !== undefined) renderAdjustmentChart(data.adjustments_breakdown);
      if (data.top_services !== undefined) renderTopServicesChart(data.top_services);
    }

    function fetchAnalytics(start, end) {
      showSkeletons();

      // Load blocks concurrently to prevent massive response sizes generating bottlenecks
      var sections = ['revenue-kpis', 'patient-kpis', 'utilization-chart', 'adjustment-chart', 'top-services-chart', 'daily-revenue-chart', 'daily-patient-chart'];
      sections.forEach(function (section) {
        $.get(baseUrl + '/financials/data', { start_date: start, end_date: end, section: section })
          .done(function (data) {
            populate(data);
          })
          .fail(function (err) {
            console.error('Failed to load section:', section, err);
          });
      });
    }

    var utilizationChartInstance = null;
    function renderUtilizationChart(utilData) {
      var ctx = document.getElementById('utilizationChart').getContext('2d');
      var labels = utilData.map(function (item) { return item.provider; });
      var values = utilData.map(function (item) { return parseFloat(item.production); });

      if (utilizationChartInstance) {
        utilizationChartInstance.data.labels = labels;
        utilizationChartInstance.data.datasets[0].data = values;
        utilizationChartInstance.update();
      } else {
        utilizationChartInstance = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [{
              label: 'Production',
              data: values,
              backgroundColor: '#996BE5',
              barThickness: 100, // Slightly adjusted for better representation
              borderRadius: 2
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: true,
                position: 'top',
                align: 'start',
                labels: { boxWidth: 12, usePointStyle: false, font: { weight: 'bold' }, padding: 30 }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                grid: { color: '#f3f4f6' },
                border: { display: false },
                ticks: {
                  callback: function (value) {
                    if (value >= 1000) return '$' + (value / 1000) + 'k';
                    return '$' + value;
                  },
                  color: '#6b7280',
                  font: { size: 11, family: 'Inter, sans-serif' },
                  padding: 20
                }
              },
              x: {
                grid: { display: false },
                ticks: { color: '#374151', font: { size: 12, family: 'Inter, sans-serif' } },
                border: { color: '#c7d2fe', width: 1.5 }
              }
            }
          }
        });
      }
    }

    var dailyRevenueChartInstance = null;
    function renderDailyRevenueChart(dailyData) {
      var ctx = document.getElementById('dailyRevenueChart').getContext('2d');

      var labels = dailyData.map(function (item) {
        var parts = item.date.split('-');
        var monthName = new Date(parts[0], parts[1] - 1, parts[2]).toLocaleDateString('en-US', { month: 'short' });
        return monthName + ' ' + parts[2];
      });

      var grossVals = dailyData.map(function (item) { return item.gross; });
      var netVals = dailyData.map(function (item) { return item.net; });
      var adjVals = dailyData.map(function (item) { return item.adjustments; });
      var collVals = dailyData.map(function (item) { return item.collections; });

      if (dailyRevenueChartInstance) {
        dailyRevenueChartInstance.data.labels = labels;
        dailyRevenueChartInstance.data.datasets[0].data = grossVals;
        dailyRevenueChartInstance.data.datasets[1].data = netVals;
        dailyRevenueChartInstance.data.datasets[2].data = adjVals;
        dailyRevenueChartInstance.data.datasets[3].data = collVals;
        dailyRevenueChartInstance.update();
      } else {
        dailyRevenueChartInstance = new Chart(ctx, {
          type: 'line',
          data: {
            labels: labels,
            datasets: [
              {
                label: 'Gross Production',
                data: grossVals,
                borderColor: '#6DE5C1',
                backgroundColor: 'rgba(109, 229, 193, 0.4)',
                fill: true,
                tension: 0,
                pointRadius: 0,
                borderWidth: 2
              },
              {
                label: 'Net Production',
                data: netVals,
                borderColor: '#996BE5',
                backgroundColor: 'rgba(153, 107, 229, 0.4)',
                fill: true,
                tension: 0,
                pointRadius: 0,
                borderWidth: 2
              },
              {
                label: 'Adjustment',
                data: adjVals,
                borderColor: '#56D9FE',
                backgroundColor: 'rgba(86, 217, 254, 0.4)',
                fill: true,
                tension: 0,
                pointRadius: 0,
                borderWidth: 2
              },
              {
                label: 'Collection',
                data: collVals,
                borderColor: '#FF8373',
                backgroundColor: 'rgba(255, 131, 115, 0.4)',
                fill: true,
                tension: 0,
                pointRadius: 0,
                borderWidth: 2
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
              mode: 'index',
              intersect: false,
            },
            plugins: {
              legend: {
                display: true,
                position: 'top',
                align: 'start',
                labels: { boxWidth: 12, usePointStyle: false, font: { weight: 'bold' }, padding: 30 }
              },
              tooltip: {
                callbacks: {
                  label: function (context) {
                    return ' ' + context.dataset.label + ': ' + fmtMoney(context.raw);
                  }
                }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                grid: { color: '#f3f4f6' },
                border: { display: false },
                ticks: {
                  callback: function (value) {
                    if (Math.abs(value) >= 1000) return (value / 1000) + 'k';
                    return value;
                  },
                  color: '#6b7280',
                  font: { size: 11, family: 'Inter, sans-serif' },
                  padding: 20
                }
              },
              x: {
                grid: { display: false },
                ticks: {
                  maxRotation: 45,
                  minRotation: 45,
                  autoSkip: true,
                  maxTicksLimit: 30,
                  color: '#374151',
                  font: { size: 11, family: 'Inter, sans-serif' }
                },
                border: { color: '#e5e7eb', width: 2 }
              }
            }
          }
        });
      }
    }

    var dailyPatientStatsChartInstance = null;
    function renderDailyPatientStatsChart(statsData) {
      var ctx = document.getElementById('dailyPatientStatsChart').getContext('2d');

      var labels = statsData.map(function (item) {
        var parts = item.date.split('-');
        var monthName = new Date(parts[0], parts[1] - 1, parts[2]).toLocaleDateString('en-US', { month: 'short' });
        return monthName + ' ' + parts[2];
      });

      var pVisitsVals = statsData.map(function (item) { return item.patient_visits; });
      var npVisitsVals = statsData.map(function (item) { return item.new_patient_visits; });
      var pSchedVals = statsData.map(function (item) { return item.patient_scheduled; });
      var npSchedVals = statsData.map(function (item) { return item.new_patient_scheduled; });

      if (dailyPatientStatsChartInstance) {
        dailyPatientStatsChartInstance.data.labels = labels;
        dailyPatientStatsChartInstance.data.datasets[0].data = pVisitsVals;
        dailyPatientStatsChartInstance.data.datasets[1].data = npVisitsVals;
        dailyPatientStatsChartInstance.data.datasets[2].data = pSchedVals;
        dailyPatientStatsChartInstance.data.datasets[3].data = npSchedVals;
        dailyPatientStatsChartInstance.update();
      } else {
        dailyPatientStatsChartInstance = new Chart(ctx, {
          type: 'line',
          data: {
            labels: labels,
            datasets: [
              {
                label: 'Patient Visits',
                data: pVisitsVals,
                borderColor: '#69e0a2', // Green
                backgroundColor: 'rgba(105, 224, 162, 0.4)',
                fill: true,
                tension: 0,
                pointRadius: 0,
                borderWidth: 2
              },
              {
                label: 'New Patient Visits',
                data: npVisitsVals,
                borderColor: '#a855f7', // Purple
                backgroundColor: 'rgba(168, 85, 247, 0.4)',
                fill: true,
                tension: 0,
                pointRadius: 0,
                borderWidth: 2
              },
              {
                label: 'Patient Scheduled',
                data: pSchedVals,
                borderColor: '#67e8f9', // Blue
                backgroundColor: 'rgba(103, 232, 249, 0.4)',
                fill: true,
                tension: 0,
                pointRadius: 0,
                borderWidth: 2
              },
              {
                label: 'New Patient Scheduled',
                data: npSchedVals,
                borderColor: '#f87171', // Red
                backgroundColor: 'rgba(248, 113, 113, 0.4)',
                fill: true,
                tension: 0,
                pointRadius: 0,
                borderWidth: 2
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
              mode: 'index',
              intersect: false,
            },
            plugins: {
              legend: {
                display: true,
                position: 'top',
                align: 'start',
                labels: { boxWidth: 12, usePointStyle: false, font: { weight: 'bold' }, padding: 30 }
              },
              tooltip: {
                callbacks: {
                  label: function (context) {
                    return ' ' + context.dataset.label + ': ' + context.raw;
                  }
                }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                grid: { color: '#f3f4f6' },
                border: { display: false },
                ticks: {
                  color: '#6b7280',
                  font: { size: 11, family: 'Inter, sans-serif' },
                  padding: 20
                }
              },
              x: {
                grid: { display: false },
                ticks: {
                  maxRotation: 45,
                  minRotation: 45,
                  autoSkip: true,
                  maxTicksLimit: 30,
                  color: '#374151',
                  font: { size: 11, family: 'Inter, sans-serif' }
                },
                border: { color: '#e5e7eb', width: 2 }
              }
            }
          }
        });
      }
    }

    var adjustmentChartInstance = null;
    var topServicesChartInstance = null;

    // Core color palette matching screenshots
    const donutColors = ['#69e0a2', '#a855f7', '#67e8f9', '#f87171', '#fde047', '#14b8a6', '#db2777', '#f97316'];

    function createDonutConfig(labels, dataValues) {
      return {
        type: 'doughnut',
        data: {
          labels: labels,
          datasets: [{
            data: dataValues,
            backgroundColor: donutColors,
            borderWidth: 0,
            hoverOffset: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '65%',
          plugins: {
            legend: {
              display: true,
              position: 'right',
              align: 'center',
              labels: {
                boxWidth: 12,
                usePointStyle: false,
                font: { weight: 'bold', size: 12, family: 'Inter, sans-serif' },
                padding: 15,
                color: '#111827'
              }
            },
            tooltip: {
              callbacks: {
                label: function (item) {
                  return ' ' + item.label + ': ' + fmtMoney(item.raw);
                }
              }
            }
          }
        }
      };
    }

    function renderAdjustmentChart(breakdownData) {
      var ctx = document.getElementById('adjustmentChart').getContext('2d');
      var labels = breakdownData.map(function (item) { return item.label; });
      var values = breakdownData.map(function (item) { return parseFloat(item.value); }); // Positive or absolute values for charting

      if (adjustmentChartInstance) {
        adjustmentChartInstance.data.labels = labels;
        adjustmentChartInstance.data.datasets[0].data = values;
        adjustmentChartInstance.update();
      } else {
        adjustmentChartInstance = new Chart(ctx, createDonutConfig(labels, values));
      }
    }

    function renderTopServicesChart(servicesData) {
      var ctx = document.getElementById('topServicesChart').getContext('2d');
      var labels = servicesData.map(function (item) { return item.label; });
      var values = servicesData.map(function (item) { return parseFloat(item.value); });

      if (topServicesChartInstance) {
        topServicesChartInstance.data.labels = labels;
        topServicesChartInstance.data.datasets[0].data = values;
        topServicesChartInstance.update();
      } else {
        topServicesChartInstance = new Chart(ctx, createDonutConfig(labels, values));
      }
    }



    var _currentStartDate = moment().startOf('month').format('YYYY-MM-DD');
    var _currentEndDate = moment().format('YYYY-MM-DD');

    window.onDrpApply = function (start, end) {
      _currentStartDate = start;
      _currentEndDate = end;
      fetchAnalytics(start, end);
      if (_sc.data || !document.getElementById('scoreCardsPanel').classList.contains('hidden')) {
        _sc.data = null;
        loadScoreCards();
      }
    };

    $(document).ready(function () {
      fetchAnalytics(_currentStartDate, _currentEndDate);
      // Deep-link: honor ?tab= on load (Summary by default).
      activateMainTab(finTabs.initial || 'summary');
    });

    /* ── Score Cards ─────────────────────────────────────────────────────────── */
    var _sc = { tab: 'production', data: null, tier: 'all', search: '', filtered: [], page: 1, pageSize: 10 };
    var _scChartCounts = null;
    var _scChartValues = null;
    var SC_COLORS = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'];

    var SC_COLS = {
      production: [
        { key: 'provider', title: 'Provider' },
        { key: 'service', title: 'Service' },
        { key: 'service_code', title: 'Service Code', cls: 'text-center' },
        { key: 'count', title: 'Count', cls: 'text-center', fmt: 'int' },
        { key: 'service_fee', title: 'Service Fee', cls: 'text-right', fmt: 'money' },
        { key: 'total_production', title: 'Total Production', cls: 'text-right', fmt: 'money' },
      ],
      collection: [
        { key: 'provider', title: 'Provider' },
        { key: 'payment_date', title: 'Date', cls: 'text-center' },
        { key: 'description', title: 'Description' },
        { key: 'type', title: 'Type', cls: 'text-center' },
        { key: 'count', title: 'Count', cls: 'text-center', fmt: 'int' },
        { key: 'service_fee', title: 'Service Fee', cls: 'text-right', fmt: 'money' },
        { key: 'total_payments', title: 'Total Payment', cls: 'text-right', fmt: 'money' },
      ],
    };

    function switchScTab(tab) {
      _sc.tab = tab;
      _sc.data = null;
      _sc.tier = 'all';
      _sc.search = '';
      _sc.page = 1;
      document.getElementById('scSearch').value = '';
      document.getElementById('scTabProd').classList.toggle('active', tab === 'production');
      document.getElementById('scTabColl').classList.toggle('active', tab === 'collection');
      loadScoreCards();
    }

    function loadScoreCards() {
      var start = _currentStartDate;
      var end = _currentEndDate;
      var prov = document.getElementById('scProvider').value;
      var params = '?tab=' + _sc.tab + '&start_date=' + start + '&end_date=' + end + (prov ? '&provider_num=' + encodeURIComponent(prov) : '');

      document.getElementById('scKpiRow').innerHTML =
        '<div class="text-sm text-gray-400 py-2">Loading&hellip;</div>';
      document.getElementById('scTbody').innerHTML =
        '<tr><td colspan="6" class="text-center py-10 text-gray-400 text-sm">Loading&hellip;</td></tr>';

      fetch(baseUrl + '/financials/score-cards' + params)
        .then(function (r) { return r.json(); })
        .then(function (data) {
          _sc.data = data;
          _sc.filtered = data.rows.slice();
          _sc.tier = 'all';
          _sc.page = 1;

          scRenderKpis(data.kpis);
          scRenderCharts(data);
          scRenderProviders(data.providers);
          scApplyFilters();
        })
        .catch(function () {
          document.getElementById('scKpiRow').innerHTML =
            '<div class="text-sm text-red-500">Failed to load data.</div>';
          document.getElementById('scTbody').innerHTML =
            '<tr><td colspan="6" class="text-center py-8 text-red-500 text-sm">Failed to load data.</td></tr>';
        });
    }

    function scRenderKpis(kpis) {
      var html = '';
      if (_sc.tab === 'production') {
        html = scKpiCard('Total Count', kpis.total_count, false) +
          scKpiCard('Unique Services By Pricing', kpis.unique_by_pricing, false) +
          scKpiCard('Total Production', kpis.total_production, true);
      } else {
        html = scKpiCard('Total Count', kpis.total_count, false) +
          scKpiCard('Total Payments', kpis.total_payments, true);
      }
      document.getElementById('scKpiRow').innerHTML = html;
    }

    function scKpiCard(label, value, isMoney) {
      var display = isMoney ? fmtMoney(value) : Number(value).toLocaleString('en-US');
      return '<div class="bg-white rounded-xl border border-gray-200 px-6 py-4 flex-1 min-w-[160px]">' +
        '<p class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">' + label + '</p>' +
        '<p class="text-2xl font-black text-gray-900">' + display + '</p>' +
        '</div>';
    }

    function scRenderCharts(data) {
      var countsData = data.chart_counts || [];
      var valuesData = (_sc.tab === 'production' ? data.chart_services : data.chart_payments) || [];
      document.getElementById('scChart2Title').textContent = _sc.tab === 'production' ? 'Top Services' : 'Top Payments';

      var chartColors = _sc.tab === 'production' ? SC_COLORS : ['#6DE5C1', '#996BE5', '#56D9FE', '#FF8373', '#FFDA83', '#07A48D'];
      var chartType = _sc.tab === 'production' ? 'doughnut' : 'pie';

      if (_scChartCounts) _scChartCounts.destroy();
      _scChartCounts = new Chart(document.getElementById('scChartCounts').getContext('2d'), {
        type: chartType,
        data: {
          labels: countsData.map(function (d) {
            return _sc.tab === 'collection' ? (d.label + ' ' + d.value) : d.label;
          }),
          datasets: [{ data: countsData.map(function (d) { return d.value; }), backgroundColor: chartColors, borderWidth: 2, borderColor: '#fff' }]
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom', labels: { font: { size: 11 }, boxWidth: 12, padding: 8 } },
            tooltip: {
              callbacks: {
                label: function (item) {
                  var val = item.raw;
                  var total = item.dataset.data.reduce(function (a, b) { return a + b }, 0);
                  var pct = total > 0 ? ((val / total) * 100).toFixed(2) + '%' : '0%';
                  return ' ' + val + ' (' + pct + ')';
                }
              }
            }
          }
        }
      });

      if (_scChartValues) _scChartValues.destroy();
      _scChartValues = new Chart(document.getElementById('scChartValues').getContext('2d'), {
        type: chartType,
        data: {
          labels: valuesData.map(function (d) {
            return _sc.tab === 'collection' ? (d.label + ' ' + fmtMoney(d.value)) : d.label;
          }),
          datasets: [{ data: valuesData.map(function (d) { return d.value; }), backgroundColor: chartColors, borderWidth: 2, borderColor: '#fff' }]
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom', labels: { font: { size: 11 }, boxWidth: 12, padding: 8 } },
            tooltip: {
              callbacks: {
                label: function (item) {
                  var val = item.raw;
                  var total = item.dataset.data.reduce(function (a, b) { return a + b }, 0);
                  var pct = total > 0 ? ((val / total) * 100).toFixed(2) + '%' : '0%';
                  return ' ' + fmtMoney(val) + ' (' + pct + ')';
                }
              }
            }
          }
        }
      });
    }

    function scRenderProviders(providers) {
      var sel = document.getElementById('scProvider');
      var current = sel.value;
      sel.innerHTML = '<option value="">All</option>' +
        (providers || []).map(function (p) {
          return '<option value="' + p.id + '"' + (String(current) === String(p.id) ? ' selected' : '') + '>' + p.name + '</option>';
        }).join('');
    }

    function scSetTier(tier) {
      _sc.tier = tier;
      _sc.page = 1;
      document.querySelectorAll('.sc-tier-btn').forEach(function (btn) {
        btn.className = 'sc-tier-btn';
        if (btn.dataset.tier === tier) btn.classList.add('active-' + tier);
      });
      scApplyFilters();
    }

    function scApplyFilters() {
      if (!_sc.data) return;
      var rows = _sc.data.rows.slice();
      if (_sc.tier !== 'all') rows = rows.filter(function (r) { return r.tier === _sc.tier; });
      var q = _sc.search.toLowerCase().trim();
      if (q) rows = rows.filter(function (r) {
        return Object.values(r).some(function (v) { return v !== null && String(v).toLowerCase().includes(q); });
      });
      _sc.filtered = rows;
      _sc.page = 1;
      scRenderTable();
    }

    function scOnSearch(val) {
      _sc.search = val;
      scApplyFilters();
    }

    function scRenderTable() {
      var cols = SC_COLS[_sc.tab];
      var total = _sc.filtered.length;
      var pages = Math.max(1, Math.ceil(total / _sc.pageSize));
      if (_sc.page > pages) _sc.page = pages;
      var start = (_sc.page - 1) * _sc.pageSize;
      var pageRows = _sc.filtered.slice(start, start + _sc.pageSize);

      document.getElementById('scThead').innerHTML =
        '<tr>' + cols.map(function (c) { return '<th>' + c.title + '</th>'; }).join('') + '</tr>';

      var rowsHtml = '';
      if (pageRows.length === 0) {
        rowsHtml = '<tr><td colspan="' + cols.length + '" class="text-center py-8 text-gray-400 text-sm">No data for this period.</td></tr>';
      } else {
        rowsHtml = pageRows.map(function (row) {
          var tierCls = row.tier === 'top' ? 'sc-row-top' : row.tier === 'bottom' ? 'sc-row-bottom' : 'sc-row-mid';
          return '<tr class="' + tierCls + '">' + cols.map(function (c) {
            var v = row[c.key];
            var display = (v === null || v === undefined) ? '—'
              : c.fmt === 'money' ? fmtMoney(v)
                : c.fmt === 'int' ? Number(v).toLocaleString('en-US')
                  : String(v);
            return '<td class="' + (c.cls || '') + '">' + display + '</td>';
          }).join('') + '</tr>';
        }).join('');
      }
      document.getElementById('scTbody').innerHTML = rowsHtml;

      // Footer: Average + Total
      var valueKey = _sc.tab === 'production' ? 'total_production' : 'total_payments';
      var allRows = _sc.data.rows; // Compute grand totals against the entire dataset
      var totalVal = allRows.reduce(function (s, r) { return s + (r[valueKey] || 0); }, 0);
      var avgVal = allRows.length > 0 ? totalVal / allRows.length : 0;
      var totalCnt = allRows.reduce(function (s, r) { return s + (r.count || 0); }, 0);
      var avgCnt = allRows.length > 0 ? Math.round(totalCnt / allRows.length) : 0;

      var tfoot = '';
      if (allRows.length > 0) {
        tfoot = '<tr>' + cols.map(function (c, i) {
          if (i === 0) return '<td>Average</td>';
          if (c.key === 'count') return '<td class="' + (c.cls || '') + '">' + avgCnt + '</td>';
          if (c.key === valueKey) return '<td class="' + (c.cls || '') + '">' + fmtMoney(avgVal) + '</td>';
          return '<td></td>';
        }).join('') + '</tr>' +
          '<tr>' + cols.map(function (c, i) {
            if (i === 0) return '<td>Total</td>';
            if (c.key === 'count') return '<td class="' + (c.cls || '') + '">' + totalCnt.toLocaleString('en-US') + '</td>';
            if (c.key === valueKey) return '<td class="' + (c.cls || '') + '">' + fmtMoney(totalVal) + '</td>';
            return '<td></td>';
          }).join('') + '</tr>';
      }
      document.getElementById('scTfoot').innerHTML = tfoot;

      // Pagination
      var endItem = Math.min(start + _sc.pageSize, total);
      document.getElementById('scInfo').textContent =
        total === 0 ? '0 items' : (start + 1) + '-' + endItem + ' of ' + total + ' items';

      var sel = document.getElementById('scPageNum');
      sel.innerHTML = '';
      for (var i = 1; i <= pages; i++) {
        var opt = document.createElement('option');
        opt.value = i; opt.textContent = i;
        if (i === _sc.page) opt.selected = true;
        sel.appendChild(opt);
      }
      document.getElementById('scPageOf').textContent = 'of ' + pages + ' pages';
      document.getElementById('scPrev').disabled = _sc.page <= 1;
      document.getElementById('scNext').disabled = _sc.page >= pages;
    }

    function scChangePage(delta) {
      var pages = Math.max(1, Math.ceil(_sc.filtered.length / _sc.pageSize));
      _sc.page = Math.min(pages, Math.max(1, _sc.page + delta));
      scRenderTable();
    }

    function scOnPageSelect(val) {
      _sc.page = parseInt(val, 10);
      scRenderTable();
    }

    function scOnPageSize(val) {
      _sc.pageSize = parseInt(val, 10);
      _sc.page = 1;
      scRenderTable();
    }

    function scExportCsv() {
      if (!_sc.filtered.length) return;
      var cols = SC_COLS[_sc.tab];
      var headers = cols.map(function (c) { return c.title; }).join(',');
      var rows = _sc.filtered.map(function (r) {
        return cols.map(function (c) { return JSON.stringify(r[c.key] !== null ? r[c.key] : ''); }).join(',');
      });
      var csv = [headers].concat(rows).join('\n');
      var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      var url = URL.createObjectURL(blob);
      var a = document.createElement('a');
      a.href = url;
      a.download = 'score-cards-' + _sc.tab + '-' + new Date().toISOString().slice(0, 10) + '.csv';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    }

    /* ── Breakdown Modal ──────────────────────────────────────────────────────── */
    var _bk = { allData: [], filtered: [], type: '', page: 1, pageSize: 10, sortKey: null, sortAsc: true };

    function bkBuildHeader(cols) {
      return '<tr class="bg-slate-200 border-b border-slate-300">' + cols.map(function (c) {
        var isSorted = _bk.sortKey === c.key;
        var arrow = isSorted ? (_bk.sortAsc ? '▲' : '▼') : '↕';
        var arrowClass = isSorted ? 'text-emerald-700 font-bold' : 'text-slate-500 opacity-60';
        return '<th onclick="bkSortBy(\'' + c.key + '\')" class="cursor-pointer select-none text-xs font-bold text-slate-900 px-4 py-3 border-r border-slate-300 hover:bg-slate-300 transition-colors whitespace-nowrap">' +
          '<span class="inline-flex items-center gap-1.5">' +
          '<span class="' + arrowClass + ' text-[11px]">' + arrow + '</span>' +
          '<span>' + c.title + '</span>' +
          '</span>' +
          '</th>';
      }).join('') + '</tr>';
    }

    function bkSortBy(key) {
      if (_bk.sortKey === key) {
        _bk.sortAsc = !_bk.sortAsc;
      } else {
        _bk.sortKey = key;
        _bk.sortAsc = true;
      }

      _bk.filtered.sort(function (a, b) {
        var valA = a[key];
        var valB = b[key];
        if (valA === valB) return 0;
        if (valA === null || valA === undefined) return 1;
        if (valB === null || valB === undefined) return -1;
        if (typeof valA === 'number' && typeof valB === 'number') {
          return _bk.sortAsc ? valA - valB : valB - valA;
        }
        return _bk.sortAsc ? String(valA).localeCompare(String(valB)) : String(valB).localeCompare(String(valA));
      });

      _bk.page = 1;
      bkRenderPage();
    }

    var BK_COLS = {
      gross_production: [
        { key: 'patient_id', title: 'Patient ID' },
        { key: 'patient_name', title: 'Patient Name', cls: 'bk-patient' },
        { key: 'provider_ids', title: "Provider ID's", cls: 'bk-prov-id' },
        { key: 'providers', title: 'Providers', cls: 'bk-provider' },
        { key: 'dates', title: 'Dates' },
        { key: 'amount', title: 'Gross Production', cls: 'bk-money', fmt: 'money' },
      ],
      net_production: [
        { key: 'patient_id', title: 'Patient ID' },
        { key: 'patient_name', title: 'Patient Name', cls: 'bk-patient' },
        { key: 'provider_ids', title: "Provider ID's", cls: 'bk-prov-id' },
        { key: 'providers', title: 'Providers', cls: 'bk-provider' },
        { key: 'dates', title: 'Dates' },
        { key: 'amount', title: 'Production', cls: 'bk-money', fmt: 'money' },
      ],
      adjustment: [
        { key: 'patient_id', title: 'Patient ID' },
        { key: 'patient_name', title: 'Patient Name', cls: 'bk-patient' },
        { key: 'provider_ids', title: "Provider ID's", cls: 'bk-prov-id' },
        { key: 'providers', title: 'Providers', cls: 'bk-provider' },
        { key: 'dates', title: 'Dates' },
        { key: 'adj_type', title: 'Adjustment Type' },
        { key: 'amount', title: 'Adjustment', cls: 'bk-money', fmt: 'money' },
      ],
      collection: [
        { key: 'patient_id', title: 'Patient ID' },
        { key: 'patient_name', title: 'Patient Name', cls: 'bk-patient' },
        { key: 'provider_ids', title: "Provider ID's", cls: 'bk-prov-id' },
        { key: 'providers', title: 'Providers', cls: 'bk-provider' },
        { key: 'dates', title: 'Dates' },
        { key: 'amount', title: 'Collection', cls: 'bk-money', fmt: 'money' },
      ],
      patient_visits: [
        { key: 'patient_id', title: 'Patient ID' },
        { key: 'patient_name', title: 'Patient Name', cls: 'bk-patient' },
        { key: 'dates', title: 'Visit Date' },
        { key: 'count', title: 'Number of Visits', cls: 'bk-count bk-center' },
      ],
      new_patient_visits: [
        { key: 'patient_id', title: 'Patient ID' },
        { key: 'patient_name', title: 'Patient Name', cls: 'bk-patient' },
        { key: 'dates', title: 'First Visit Date' },
        { key: 'service_codes', title: 'Service Codes', cls: 'bk-count' },
        { key: 'amount', title: 'Production', cls: 'bk-money', fmt: 'money' },
      ],
      patients_scheduled: [
        { key: 'patient_id', title: 'Patient ID' },
        { key: 'patient_name', title: 'Patient Name', cls: 'bk-patient' },
        { key: 'dates', title: 'Appointment Dates' },
        { key: 'count', title: 'Appointment Count', cls: 'bk-count bk-center' },
      ],
      new_patients_scheduled: [
        { key: 'patient_id', title: 'Patient ID' },
        { key: 'patient_name', title: 'Patient Name', cls: 'bk-patient' },
        { key: 'dates', title: 'Appointment Dates' },
        { key: 'count', title: 'Appointment Count', cls: 'bk-count bk-center' },
      ],
      avg_production_per_patient: [
        { key: 'patient_id', title: 'Patient ID' },
        { key: 'patient_name', title: 'Patient Name', cls: 'bk-patient' },
        { key: 'count', title: 'Number of Visits', cls: 'bk-count bk-center' },
        { key: 'amount', title: 'Production', cls: 'bk-money', fmt: 'money' },
      ],
    };

    function bkFmtCell(col, row) {
      var v = row[col.key];
      if (v === null || v === undefined) return '—';
      if (col.fmt === 'money') return fmtMoney(v);
      if (col.key === 'patient_name' && row.patient_id) {
        return '<div class="flex items-center justify-between gap-2">' +
          '<span class="font-bold text-slate-800">' + v + '</span>' +
          '<button type="button" onclick="openPatient(' + row.patient_id + ')" class="inline-flex items-center text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50 p-1 rounded transition-colors ml-1" title="View Patient Details">' +
          '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>' +
          '</button>' +
          '</div>';
      }
      if (col.key === 'patient_id' && v) {
        return '<button type="button" onclick="openPatient(' + v + ')" class="text-emerald-600 hover:text-emerald-800 font-semibold hover:underline cursor-pointer" title="View Patient Details">' + v + '</button>';
      }
      return String(v);
    }

    function bkBuildHeader(cols) {
      return '<tr>' + cols.map(function (c) { return '<th>' + c.title + '</th>'; }).join('') + '</tr>';
    }

    function bkBuildRows(cols, rows) {
      if (rows.length === 0) {
        return '<tr><td colspan="' + cols.length + '" class="text-center py-8 text-gray-400">No data for this period.</td></tr>';
      }
      return rows.map(function (row) {
        return '<tr>' + cols.map(function (c) {
          return '<td class="' + (c.cls || '') + '">' + bkFmtCell(c, row) + '</td>';
        }).join('') + '</tr>';
      }).join('');
    }

    function bkBuildFooter(type, rows) {
      var cols = BK_COLS[type];
      var span = cols.length;
      if (rows.length === 0) return '';

      if (type === 'avg_production_per_patient') {
        var totalAmt = rows.reduce(function (s, r) { return s + (r.amount || 0); }, 0);
        var totalCount = rows.reduce(function (s, r) { return s + (r.count || 0); }, 0);
        var avg = rows.length > 0 ? totalAmt / rows.length : 0;
        return '<tr><td></td><td>Avg:</td><td class="bk-count bk-center">' + totalCount + '</td><td class="bk-money">' + fmtMoney(avg) + '</td></tr>' +
          '<tr><td></td><td>Total:</td><td class="bk-count bk-center">' + totalCount + '</td><td class="bk-money">' + fmtMoney(totalAmt) + '</td></tr>';
      }

      if (type === 'patient_visits' || type === 'patients_scheduled' || type === 'new_patients_scheduled') {
        var total = rows.reduce(function (s, r) { return s + (r.count || 0); }, 0);
        return '<tr><td colspan="' + (span - 1) + '">Total:</td><td class="bk-count bk-center">' + total + '</td></tr>';
      }

      if (type === 'new_patient_visits') {
        var totalAmt = rows.reduce(function (s, r) { return s + (r.amount || 0); }, 0);
        return '<tr><td colspan="3">Total:</td><td></td><td class="bk-money">' + fmtMoney(totalAmt) + '</td></tr>';
      }

      var total = rows.reduce(function (s, r) { return s + (r.amount || 0); }, 0);
      return '<tr><td colspan="' + (span - 1) + '">Total:</td><td class="bk-money"><strong>' + fmtMoney(total) + '</strong></td></tr>';
    }

    function bkRenderPage() {
      var cols = BK_COLS[_bk.type];
      var total = _bk.filtered.length;
      var pages = Math.max(1, Math.ceil(total / _bk.pageSize));
      if (_bk.page > pages) _bk.page = pages;
      var start = (_bk.page - 1) * _bk.pageSize;
      var pageRows = _bk.filtered.slice(start, start + _bk.pageSize);

      document.getElementById('bkThead').innerHTML = bkBuildHeader(cols);
      document.getElementById('bkTbody').innerHTML = bkBuildRows(cols, pageRows);
      document.getElementById('bkTfoot').innerHTML = bkBuildFooter(_bk.type, _bk.filtered);

      var end = Math.min(start + _bk.pageSize, total);
      document.getElementById('bkInfo').textContent = total === 0 ? '0 items' : (start + 1) + '-' + end + ' of ' + total + ' items';

      var sel = document.getElementById('bkPageNum');
      sel.innerHTML = '';
      for (var i = 1; i <= pages; i++) {
        var opt = document.createElement('option');
        opt.value = i; opt.textContent = i;
        if (i === _bk.page) opt.selected = true;
        sel.appendChild(opt);
      }
      document.getElementById('bkPageOf').textContent = 'of ' + pages + ' pages';
      document.getElementById('bkPrev').disabled = _bk.page <= 1;
      document.getElementById('bkNext').disabled = _bk.page >= pages;
    }

    function bkApplySearch(q) {
      q = q.toLowerCase().trim();
      _bk.filtered = q
        ? _bk.allData.filter(function (r) {
          return Object.values(r).some(function (v) { return v !== null && String(v).toLowerCase().includes(q); });
        })
        : _bk.allData.slice();
      _bk.page = 1;
      bkRenderPage();
    }

    function bkChangePage(delta) {
      var pages = Math.max(1, Math.ceil(_bk.filtered.length / _bk.pageSize));
      _bk.page = Math.min(pages, Math.max(1, _bk.page + delta));
      bkRenderPage();
    }

    document.getElementById('bkPageNum').addEventListener('change', function () {
      _bk.page = parseInt(this.value, 10); bkRenderPage();
    });
    document.getElementById('bkPageSize').addEventListener('change', function () {
      _bk.pageSize = parseInt(this.value, 10); _bk.page = 1; bkRenderPage();
    });
    document.getElementById('bkSearch').addEventListener('input', function () {
      bkApplySearch(this.value);
    });
    document.getElementById('bkExportBtn').addEventListener('click', function () {
      if (_bk.filtered.length === 0) return;
      var cols = BK_COLS[_bk.type];
      var headers = cols.map(function (c) { return c.title; }).join(',');
      var rows = _bk.filtered.map(function (r) {
        return cols.map(function (c) { return JSON.stringify(r[c.key] !== null ? r[c.key] : ''); }).join(',');
      });
      var csv = [headers].concat(rows).join('\n');
      var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      var url = URL.createObjectURL(blob);
      var a = document.createElement('a');
      a.href = url;
      a.download = 'financial-breakdown-' + _bk.type + '-' + new Date().toISOString().slice(0, 10) + '.csv';
      document.body.appendChild(a); a.click(); document.body.removeChild(a);
      URL.revokeObjectURL(url);
    });

    document.getElementById('bkOverlay').addEventListener('click', function (e) {
      if (e.target === this) closeBkModal();
    });

    function openBreakdown(type, title) {
      if (!BK_COLS[type]) return;
      _bk.type = type;
      _bk.allData = [];
      _bk.filtered = [];
      _bk.page = 1;
      _bk.sortKey = null;
      _bk.sortAsc = true;
      _bk.pageSize = parseInt(document.getElementById('bkPageSize').value, 10);

      document.getElementById('bkTitle').textContent = 'Financial Breakdown - ' + title;
      document.getElementById('bkSearch').value = '';
      document.getElementById('bkTbody').innerHTML = '<tr><td colspan="10" class="text-center py-10 text-gray-400 text-sm">Loading…</td></tr>';
      document.getElementById('bkThead').innerHTML = '';
      document.getElementById('bkTfoot').innerHTML = '';
      document.getElementById('bkInfo').textContent = '—';
      document.getElementById('bkOverlay').classList.remove('hidden');
      document.body.style.overflow = 'hidden';

      var start = _currentStartDate;
      var end = _currentEndDate;

      fetch(baseUrl + '/financials/breakdown?type=' + type + '&start_date=' + start + '&end_date=' + end)
        .then(function (r) { return r.json(); })
        .then(function (data) {
          _bk.allData = data;
          _bk.filtered = data.slice();
          bkRenderPage();
        })
        .catch(function () {
          document.getElementById('bkTbody').innerHTML =
            '<tr><td colspan="10" class="text-center py-8 text-red-500 text-sm">Failed to load data.</td></tr>';
        });
    }

    function buildPatientIdColumn(title) {
      return {
        data: 'patient_id',
        title: title || 'Patient ID',
        render: function (data) {
          if (!data) return '—';
          return '<button type="button" onclick="openPatient(' + data + ')" class="text-emerald-600 hover:text-emerald-800 font-semibold hover:underline cursor-pointer" title="View Patient Details">' + data + '</button>';
        }
      };
    }

    function buildPatientNameColumn(title) {
      return {
        data: 'patient_name',
        title: title || 'Patient Name',
        render: function (data, type, row) {
          if (type !== 'display') return data;
          var patId = row.patient_id || row.id;
          if (!patId) return data || '—';
          return '<div class="flex items-center justify-between gap-2">' +
            '<span class="font-bold text-slate-800">' + (data || '—') + '</span>' +
            '<button type="button" onclick="openPatient(' + patId + ')" class="inline-flex items-center text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50 p-1 rounded transition-colors" title="View Patient Details">' +
            '<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>' +
            '</button>' +
            '</div>';
        }
      };
    }

    function openPatientVisitsBreakdown(title) {
      var modalId = 'breakdown-modal';
      setDataTableModalLoading(modalId, 'Financial Breakdown - ' + title);

      var start = _currentStartDate;
      var end = _currentEndDate;

      fetch(baseUrl + '/financials/breakdown?type=patient_visits&start_date=' + start + '&end_date=' + end)
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var columns = [
            buildPatientIdColumn('Patient ID'),
            buildPatientNameColumn('Patient Name'),
            { data: 'dates', title: 'Visit Date' },
            { data: 'count', title: 'Number of visits' }
          ];
          openDataTableModal(modalId, 'Financial Breakdown - ' + title, columns, data);
        })
        .catch(function () {
          alert('Failed to load data.');
          closeDataTableModal(modalId);
        });
    }

    function openNewPatientVisitsBreakdown(title) {
      var modalId = 'breakdown-modal';
      setDataTableModalLoading(modalId, 'Financial Breakdown - ' + title);

      var start = _currentStartDate;
      var end = _currentEndDate;

      fetch(baseUrl + '/financials/breakdown?type=new_patient_visits&start_date=' + start + '&end_date=' + end)
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var columns = [
            buildPatientIdColumn('Patient ID'),
            buildPatientNameColumn('Patient Name'),
            { data: 'dates', title: 'First Visit Date' },
            { data: 'service_codes', title: 'Service Code' },
            {
              data: 'amount', title: 'Production', render: function (val) {
                return fmtMoney(val);
              }
            }
          ];
          openDataTableModal(modalId, 'Financial Breakdown - ' + title, columns, data);
        })
        .catch(function () {
          alert('Failed to load data.');
          closeDataTableModal(modalId);
        });
    }

    function openBrokenCancelledBreakdown(title) {
      var modalId = 'breakdown-modal';
      setDataTableModalLoading(modalId, 'Financial Breakdown - ' + title);

      var start = _currentStartDate;
      var end = _currentEndDate;

      fetch(baseUrl + '/financials/breakdown?type=broken_cancelled&start_date=' + start + '&end_date=' + end)
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var columns = [
            buildPatientIdColumn('Patient ID'),
            buildPatientNameColumn('Patient Name'),
            { data: 'dates', title: 'Procedure Date' },
            { data: 'type', title: 'Type' }
          ];
          openDataTableModal(modalId, 'Financial Breakdown - ' + title, columns, data);
        })
        .catch(function () {
          alert('Failed to load data.');
          closeDataTableModal(modalId);
        });
    }

    function openPatientsScheduledBreakdown(title) {
      var modalId = 'breakdown-modal';
      setDataTableModalLoading(modalId, 'Financial Breakdown - ' + title);

      var start = _currentStartDate;
      var end = _currentEndDate;

      fetch(baseUrl + '/financials/breakdown?type=patients_scheduled&start_date=' + start + '&end_date=' + end)
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var columns = [
            buildPatientIdColumn('Patient ID'),
            buildPatientNameColumn('Patient Name'),
            { data: 'dates', title: 'Appointment Date(s)' },
            { data: 'count', title: 'Number of visits' }
          ];
          openDataTableModal(modalId, 'Financial Breakdown - ' + title, columns, data);
        })
        .catch(function () {
          alert('Failed to load data.');
          closeDataTableModal(modalId);
        });
    }

    function openNewPatientsScheduledBreakdown(title) {
      var modalId = 'breakdown-modal';
      setDataTableModalLoading(modalId, 'Financial Breakdown - ' + title);

      var start = _currentStartDate;
      var end = _currentEndDate;

      fetch(baseUrl + '/financials/breakdown?type=new_patients_scheduled&start_date=' + start + '&end_date=' + end)
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var columns = [
            buildPatientIdColumn('Patient ID'),
            buildPatientNameColumn('Patient Name'),
            { data: 'dates', title: 'Appointment Date(s)' },
            { data: 'count', title: 'Number of visits' }
          ];
          openDataTableModal(modalId, 'Financial Breakdown - ' + title, columns, data);
        })
        .catch(function () {
          alert('Failed to load data.');
          closeDataTableModal(modalId);
        });
    }

    function closeBkModal() {
      document.getElementById('bkOverlay').classList.add('hidden');
      document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeBkModal();
    });
  </script>

  <x-app-components.datatable-modal id="breakdown-modal" />
  <x-app-components.patient-modal />

</x-app-layout>