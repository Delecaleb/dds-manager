<x-app-layout>
  <div class="p-6 space-y-5 max-w-[1600px] mx-auto text-slate-800">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
          <i data-lucide="database" class="w-5 h-5 text-slate-700"></i> OpenDental Realtime Data Explorer
        </h1>
        <p class="text-xs text-slate-500 mt-0.5">
          Realtime database querying, API synchronization, and data reconciliation for <span class="font-semibold text-slate-800">{{ $currentOffice->name ?? 'Active Location' }}</span>
        </p>
      </div>
      <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
          <i data-lucide="building-2" class="w-3.5 h-3.5 text-emerald-600"></i>
          Location: {{ $currentOffice->name ?? 'Default Office' }}
        </span>
      </div>
    </div>

    <!-- Top Action Card: Mode Selector & Contextual Filters -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-4">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 pb-4">
        
        <!-- Mode Selector -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
          <label for="intentModeSelect" class="text-xs font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">
            I want to:
          </label>
          <div class="relative">
            <select id="intentModeSelect" class="bg-white border border-slate-300 text-slate-800 text-sm font-semibold rounded-lg px-3.5 py-2 pr-10 focus:ring-1 focus:ring-slate-400 focus:border-slate-400 focus:outline-none shadow-2xs cursor-pointer transition appearance-none">
              <option value="compare_side_by_side" selected>Compare Side-by-Side (Diff & Reconcile)</option>
              <option value="opendental_live">Check Live OpenDental</option>
              <option value="local_db">Check Local Synced Database</option>
              <option value="sync_checkpoints">Manage Sync Checkpoints & Dates</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
              <i data-lucide="chevron-down" class="w-4 h-4"></i>
            </div>
          </div>
        </div>

        <!-- Mode Status Badges & Action Button -->
        <div class="flex items-center gap-3">
          <span id="activeModeBadge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
            <span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span> Mode: Side-by-Side Reconciliation
          </span>
          <button id="primaryActionBtn" onclick="executeCurrentMode()" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-xs transition focus:outline-none cursor-pointer">
            <i data-lucide="play" class="w-3.5 h-3.5 fill-current"></i> Run Comparison
          </button>
        </div>
      </div>

      <!-- Contextual Filter Controls (For Query & Compare Modes) -->
      <div id="filterControlsContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
        
        <!-- 1. Table Select -->
        <div class="lg:col-span-4">
          <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Table</label>
          <select id="activeTableSelect" class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded-lg p-2 font-medium focus:ring-1 focus:ring-slate-400 focus:border-slate-400 shadow-2xs h-[38px]">
            <optgroup label="📅 Appointments & Schedules">
              <option value="appointment" selected>appointment (od_appointments)</option>
              <option value="histappointment">histappointment (od_histappointments)</option>
              <option value="schedule">schedule (od_schedules)</option>
              <option value="recall">recall (od_recalls)</option>
              <option value="recalltype">recalltype (od_recall_types)</option>
            </optgroup>
            <optgroup label="🩺 Clinical & Procedures">
              <option value="procedurelog">procedurelog (od_procedure_logs)</option>
              <option value="procedurecode">procedurecode (od_procedures)</option>
              <option value="treatmentplan">treatmentplan (treatment_plans)</option>
            </optgroup>
            <optgroup label="💳 Financials, Billing & Claims">
              <option value="adjustment">adjustment (od_adjustments)</option>
              <option value="payment">payment (od_payments)</option>
              <option value="paysplit">paysplit (od_pay_splits)</option>
              <option value="claimproc">claimproc (od_claim_procs)</option>
              <option value="claim">claim (od_claims)</option>
              <option value="claimpayment">claimpayment (od_claim_payments)</option>
              <option value="statement">statement (od_statements)</option>
              <option value="payplan">payplan (od_pay_plans)</option>
              <option value="payplancharge">payplancharge (od_pay_plan_charges)</option>
              <option value="deposit">deposit (od_deposits)</option>
            </optgroup>
            <optgroup label="🏢 Practice & Patient Setup">
              <option value="patient">patient (od_patients)</option>
              <option value="provider">provider (od_providers)</option>
              <option value="insplan">insplan (od_ins_plans)</option>
              <option value="carrier">carrier (od_carriers)</option>
              <option value="definition">definition (od_definitions)</option>
              <option value="clinic">clinic (od_clinics)</option>
              <option value="operatory">operatory (od_operatories)</option>
              <option value="userod">userod (od_user_ods)</option>
            </optgroup>
          </select>
        </div>

        <!-- 2. Date Range Picker -->
        <div class="lg:col-span-4">
          <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Date Range</label>
          <x-daterange-picker id="odExplorerDateRange" on-apply="odExplorerDateApplied" class="w-full h-[38px] !bg-white shadow-2xs text-xs" />
          <input type="hidden" id="filterStartDate" value="2026-08-01">
          <input type="hidden" id="filterEndDate" value="2026-08-19">
        </div>

        <!-- 3. Status Filter (Visible for appointments) -->
        <div id="statusFilterWrapper" class="lg:col-span-2">
          <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Status</label>
          <select id="filterStatusSelect" class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded-lg p-2 font-medium focus:ring-1 focus:ring-slate-400 shadow-2xs h-[38px]">
            <option value="1_2" selected>Scheduled & Complete (1, 2)</option>
            <option value="all">All Statuses (No Filter)</option>
            <option value="1">Scheduled Only (1)</option>
            <option value="2">Complete Only (2)</option>
            <option value="5">Broken / Cancelled (5)</option>
          </select>
        </div>

        <!-- 4. Max Rows Limit -->
        <div class="lg:col-span-2">
          <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Limit</label>
          <select id="filterLimitSelect" class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded-lg p-2 font-medium focus:ring-1 focus:ring-slate-400 shadow-2xs h-[38px]">
            <option value="200">200 rows</option>
            <option value="500" selected>500 rows</option>
            <option value="1000">1,000 rows</option>
            <option value="2000">2,000 rows</option>
          </select>
        </div>

      </div>

      <!-- Quick Preset Dates -->
      <div id="quickDatePresetsWrapper" class="flex items-center gap-1.5 pt-1 border-t border-slate-100 text-xs flex-wrap">
        <span class="text-slate-400 font-medium text-[11px] mr-1">Quick Dates:</span>
        <button type="button" onclick="setDatePreset('aug_2026')" class="px-2.5 py-1 text-[11px] font-semibold rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition cursor-pointer">Aug 1–19, 2026 (Live Data)</button>
        <button type="button" onclick="setDatePreset('this_month')" class="px-2.5 py-1 text-[11px] font-medium rounded-md bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 transition cursor-pointer">This Month</button>
        <button type="button" onclick="setDatePreset('last_month')" class="px-2.5 py-1 text-[11px] font-medium rounded-md bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 transition cursor-pointer">Last Month</button>
        <button type="button" onclick="setDatePreset('last_30_days')" class="px-2.5 py-1 text-[11px] font-medium rounded-md bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 transition cursor-pointer">Last 30 Days</button>
        <button type="button" onclick="setDatePreset('today')" class="px-2.5 py-1 text-[11px] font-medium rounded-md bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 transition cursor-pointer">Today</button>
      </div>

    </div>

    <!-- VIEW 1: SIDE-BY-SIDE RECONCILIATION CONTAINER -->
    <div id="viewCompareContainer" class="space-y-5">
      
      <!-- Metrics Grid -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3.5">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs flex items-center justify-between">
          <div>
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Live OpenDental</span>
            <span id="statLiveCount" class="text-xl font-bold text-emerald-700 mt-0.5 block">0</span>
            <span class="text-[11px] text-slate-400">Remote API rows</span>
          </div>
          <div class="w-9 h-9 bg-emerald-50 rounded-lg flex items-center justify-center text-emerald-600 border border-emerald-100">
            <i data-lucide="cloud" class="w-4 h-4"></i>
          </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs flex items-center justify-between">
          <div>
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Local Synced DB</span>
            <span id="statLocalCount" class="text-xl font-bold text-blue-700 mt-0.5 block">0</span>
            <span class="text-[11px] text-slate-400">Local MySQL rows</span>
          </div>
          <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600 border border-blue-100">
            <i data-lucide="database" class="w-4 h-4"></i>
          </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs flex items-center justify-between">
          <div>
            <span class="text-[11px] font-bold text-rose-700 uppercase tracking-wider block">Deleted in OD</span>
            <span id="statOrphanCount" class="text-xl font-bold text-rose-600 mt-0.5 block">0</span>
            <span class="text-[11px] text-slate-400">Orphans to Prune</span>
          </div>
          <div class="w-9 h-9 bg-rose-50 rounded-lg flex items-center justify-center text-rose-600 border border-rose-100">
            <i data-lucide="trash-2" class="w-4 h-4"></i>
          </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs flex items-center justify-between">
          <div>
            <span class="text-[11px] font-bold text-amber-800 uppercase tracking-wider block">Missing Locally</span>
            <span id="statMissingCount" class="text-xl font-bold text-amber-600 mt-0.5 block">0</span>
            <span class="text-[11px] text-slate-400">Records to Sync</span>
          </div>
          <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center text-amber-600 border border-amber-100">
            <i data-lucide="download-cloud" class="w-4 h-4"></i>
          </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs flex items-center justify-between col-span-2 md:col-span-1">
          <div>
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Match Rate</span>
            <span id="statMatchRate" class="text-xl font-bold text-slate-900 mt-0.5 block">100%</span>
            <span id="statExecTime" class="text-[11px] text-slate-400">0 ms</span>
          </div>
          <div class="w-9 h-9 bg-slate-100 rounded-lg flex items-center justify-center text-slate-600 border border-slate-200">
            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
          </div>
        </div>
      </div>

      <!-- Diff Table Card -->
      <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        
        <!-- Table Action Header -->
        <div class="p-3.5 bg-slate-50/70 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-3">
          
          <!-- Filter Buttons -->
          <div class="flex items-center gap-1.5 flex-wrap">
            <button type="button" id="pillAll" onclick="setDiffFilter('all')" class="px-3 py-1 text-xs font-bold rounded-lg bg-slate-900 text-white shadow-2xs transition cursor-pointer">
              All (<span id="countPillAll">0</span>)
            </button>
            <button type="button" id="pillOrphan" onclick="setDiffFilter('orphan')" class="px-3 py-1 text-xs font-medium rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition cursor-pointer">
              Deleted in OD (<span id="countPillOrphan">0</span>)
            </button>
            <button type="button" id="pillMissing" onclick="setDiffFilter('missing')" class="px-3 py-1 text-xs font-medium rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition cursor-pointer">
              Missing Locally (<span id="countPillMissing">0</span>)
            </button>
            <button type="button" id="pillMatched" onclick="setDiffFilter('matched')" class="px-3 py-1 text-xs font-medium rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition cursor-pointer">
              Matched (<span id="countPillMatched">0</span>)
            </button>
          </div>

          <!-- Actions & Search -->
          <div class="flex items-center gap-2">
            <div class="relative w-52">
              <input type="text" id="diffSearchInput" placeholder="Search rows..." class="w-full bg-white border border-slate-300 rounded-lg pl-8 pr-3 py-1 text-xs font-medium text-slate-800 placeholder-slate-400 focus:ring-1 focus:ring-slate-400 shadow-2xs">
              <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2"></i>
            </div>

            <button type="button" id="btnPruneAll" onclick="pruneAllOrphans()" disabled class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold text-white bg-slate-400 rounded-lg cursor-not-allowed transition shadow-2xs disabled:opacity-60">
              <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Prune Orphans
            </button>

            <button type="button" id="btnSyncAll" onclick="syncAllMissing()" disabled class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold text-white bg-slate-400 rounded-lg cursor-not-allowed transition shadow-2xs disabled:opacity-60">
              <i data-lucide="download-cloud" class="w-3.5 h-3.5"></i> Sync Missing
            </button>

            <button type="button" id="btnExportDiff" onclick="exportDiffCsv()" disabled class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 rounded-lg transition shadow-2xs disabled:opacity-60">
              <i data-lucide="download" class="w-3.5 h-3.5"></i> Export CSV
            </button>
          </div>

        </div>

        <!-- Table -->
        <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead class="sticky top-0 bg-slate-100 border-b border-slate-200 z-10 font-bold text-slate-700 uppercase tracking-wider text-[11px]">
              <tr>
                <th class="px-4 py-2.5">Status</th>
                <th class="px-4 py-2.5">Primary Key</th>
                <th class="px-4 py-2.5">Patient ID</th>
                <th class="px-4 py-2.5">Date & Time</th>
                <th class="px-4 py-2.5">Record Status</th>
                <th class="px-4 py-2.5">Provider</th>
                <th class="px-4 py-2.5">Notes / Details</th>
                <th class="px-4 py-2.5 text-right">Action</th>
              </tr>
            </thead>
            <tbody id="diffTableBody">
              <tr>
                <td colspan="8" class="p-12 text-center text-slate-400">
                  <div class="flex flex-col items-center justify-center gap-2">
                    <svg class="animate-spin w-6 h-6 text-slate-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                    <p class="font-medium text-xs text-slate-600">Comparing OpenDental Live vs Local Database...</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>

    </div>

    <!-- VIEW 2: SINGLE SOURCE QUERY CONTAINER (Live OD or Local DB) -->
    <div id="viewSingleQueryContainer" class="hidden space-y-4">
      <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-3.5 bg-slate-50/70 border-b border-slate-200 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span id="singleSourceTitle" class="text-xs font-bold uppercase tracking-wider text-slate-800">Results</span>
            <span id="singleSourceCount" class="text-xs font-medium text-slate-500">(0 rows)</span>
          </div>
          <div class="flex items-center gap-2">
            <button type="button" id="btnSyncSingleLive" onclick="syncSingleQueryToLocal()" class="hidden inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition shadow-2xs cursor-pointer">
              <i data-lucide="cloud-download" class="w-3.5 h-3.5"></i> Sync Fetched to Local DB
            </button>
            <button type="button" onclick="exportSingleQueryCsv()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 rounded-lg transition shadow-2xs cursor-pointer">
              <i data-lucide="download" class="w-3.5 h-3.5"></i> Export CSV
            </button>
          </div>
        </div>

        <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead class="sticky top-0 bg-slate-100 border-b border-slate-200 z-10 font-bold text-slate-700 uppercase tracking-wider text-[11px]" id="singleQueryThead">
              <tr>
                <th class="p-4 text-slate-400 font-normal">Click "Run Query" to fetch data.</th>
              </tr>
            </thead>
            <tbody id="singleQueryTbody">
              <tr>
                <td class="p-12 text-center text-slate-400 text-xs">No query executed yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- VIEW 3: SYNC CHECKPOINTS CONTAINER -->
    <div id="viewCheckpointsContainer" class="hidden space-y-4">
      <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
          <div>
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
              <i data-lucide="refresh-cw" class="w-4 h-4 text-slate-600"></i> OpenDental Sync Watermarks & Start Dates
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
              Reset or update the sync checkpoint (`last_synced_at` and `last_primary_key`) for each module.
            </p>
          </div>
          <div class="flex items-center gap-2">
            <button onclick="loadSyncCheckpoints()" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition cursor-pointer">
              Refresh
            </button>
            <button onclick="resetAllCheckpoints()" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 rounded-lg transition cursor-pointer">
              Reset All to Start
            </button>
          </div>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-lg">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-slate-100 border-b border-slate-200 text-xs font-bold text-slate-700 uppercase tracking-wider">
                <th class="px-4 py-2.5">Module</th>
                <th class="px-4 py-2.5">Status</th>
                <th class="px-4 py-2.5">Last Synced Date (`last_synced_at`)</th>
                <th class="px-4 py-2.5">Last Primary Key</th>
                <th class="px-4 py-2.5">Records Synced</th>
                <th class="px-4 py-2.5 text-right">Action</th>
              </tr>
            </thead>
            <tbody id="checkpointsTbody">
              <tr>
                <td colspan="6" class="p-8 text-center text-slate-400 text-xs">Loading sync checkpoints...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

  <!-- JavaScript Application Engine -->
  <script>
    var _diffResult = null;
    var _activeDiffFilter = 'all';
    var _singleQueryResult = null;

    window.odExplorerDateApplied = function (startYMD, endYMD) {
      document.getElementById('filterStartDate').value = startYMD;
      document.getElementById('filterEndDate').value = endYMD;
      executeCurrentMode();
    };

    document.addEventListener('daterange:changed', function (e) {
      if (e.detail && e.detail.id === 'odExplorerDateRange') {
        document.getElementById('filterStartDate').value = e.detail.start;
        document.getElementById('filterEndDate').value = e.detail.end;
        executeCurrentMode();
      }
    });

    document.addEventListener('DOMContentLoaded', function () {
      var modeSelect = document.getElementById('intentModeSelect');
      var tableSelect = document.getElementById('activeTableSelect');

      modeSelect.addEventListener('change', function () {
        handleModeChange(this.value);
      });

      tableSelect.addEventListener('change', function () {
        var isApt = (this.value === 'appointment' || this.value === 'od_appointments' || this.value === 'histappointment' || this.value === 'od_histappointments');
        var statusWrapper = document.getElementById('statusFilterWrapper');
        if (statusWrapper) {
          statusWrapper.style.display = isApt ? 'block' : 'none';
        }
        executeCurrentMode();
      });

      // Initial run: compare side-by-side
      executeCurrentMode();
    });

    function handleModeChange(mode) {
      var viewCompare = document.getElementById('viewCompareContainer');
      var viewSingle = document.getElementById('viewSingleQueryContainer');
      var viewCheckpoints = document.getElementById('viewCheckpointsContainer');
      var filterControls = document.getElementById('filterControlsContainer');
      var quickDates = document.getElementById('quickDatePresetsWrapper');
      var modeBadge = document.getElementById('activeModeBadge');
      var primaryBtn = document.getElementById('primaryActionBtn');

      viewCompare.classList.add('hidden');
      viewSingle.classList.add('hidden');
      viewCheckpoints.classList.add('hidden');

      if (mode === 'compare_side_by_side') {
        viewCompare.classList.remove('hidden');
        filterControls.classList.remove('hidden');
        quickDates.classList.remove('hidden');
        modeBadge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200';
        modeBadge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span> Mode: Side-by-Side Reconciliation';
        primaryBtn.className = 'inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-xs transition cursor-pointer';
        primaryBtn.innerHTML = '<i data-lucide="play" class="w-3.5 h-3.5 fill-current"></i> Run Comparison';
        executeCompare();
      } else if (mode === 'opendental_live' || mode === 'local_db') {
        viewSingle.classList.remove('hidden');
        filterControls.classList.remove('hidden');
        quickDates.classList.remove('hidden');
        var isLive = (mode === 'opendental_live');
        modeBadge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200';
        modeBadge.innerHTML = isLive
          ? '<span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span> Mode: Live OpenDental API'
          : '<span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span> Mode: Local MySQL Snapshot';
        primaryBtn.className = 'inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-xs transition cursor-pointer';
        primaryBtn.innerHTML = '<i data-lucide="play" class="w-3.5 h-3.5 fill-current"></i> Fetch ' + (isLive ? 'Live OD' : 'Local DB');
        document.getElementById('btnSyncSingleLive').style.display = isLive ? 'inline-flex' : 'none';
        executeSingleQuery(mode);
      } else if (mode === 'sync_checkpoints') {
        viewCheckpoints.classList.remove('hidden');
        filterControls.classList.add('hidden');
        quickDates.classList.add('hidden');
        modeBadge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200';
        modeBadge.innerHTML = '<i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-slate-600"></i> Mode: Sync Checkpoints';
        primaryBtn.className = 'inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-xs transition cursor-pointer';
        primaryBtn.innerHTML = '<i data-lucide="rotate-cw" class="w-3.5 h-3.5"></i> Refresh Checkpoints';
        loadSyncCheckpoints();
      }

      if (window.lucide) lucide.createIcons();
    }

    function executeCurrentMode() {
      var mode = document.getElementById('intentModeSelect').value;
      if (mode === 'compare_side_by_side') {
        executeCompare();
      } else if (mode === 'opendental_live' || mode === 'local_db') {
        executeSingleQuery(mode);
      } else if (mode === 'sync_checkpoints') {
        loadSyncCheckpoints();
      }
    }

    function setDatePreset(preset) {
      var s = document.getElementById('filterStartDate');
      var e = document.getElementById('filterEndDate');
      var now = new Date();
      var y = now.getFullYear();
      var m = String(now.getMonth() + 1).padStart(2, '0');
      var d = String(now.getDate()).padStart(2, '0');

      var startStr = '';
      var endStr = '';

      if (preset === 'aug_2026') {
        startStr = '2026-08-01';
        endStr = '2026-08-19';
      } else if (preset === 'this_month') {
        startStr = y + '-' + m + '-01';
        endStr = y + '-' + m + '-' + d;
      } else if (preset === 'last_month') {
        var prevM = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        var lastPrev = new Date(now.getFullYear(), now.getMonth(), 0);
        startStr = prevM.toISOString().slice(0, 7) + '-01';
        endStr = lastPrev.toISOString().slice(0, 10);
      } else if (preset === 'last_30_days') {
        var past30 = new Date(now.getTime() - (30 * 24 * 60 * 60 * 1000));
        startStr = past30.toISOString().slice(0, 10);
        endStr = y + '-' + m + '-' + d;
      } else if (preset === 'today') {
        startStr = y + '-' + m + '-' + d;
        endStr = y + '-' + m + '-' + d;
      }

      s.value = startStr;
      e.value = endStr;

      if (typeof $ !== 'undefined' && $('#odExplorerDateRange').data('daterangepicker') && typeof moment !== 'undefined') {
        $('#odExplorerDateRange').data('daterangepicker').setStartDate(moment(startStr, 'YYYY-MM-DD'));
        $('#odExplorerDateRange').data('daterangepicker').setEndDate(moment(endStr, 'YYYY-MM-DD'));
      }

      executeCurrentMode();
    }

    // ─── 1. SIDE-BY-SIDE RECONCILIATION ENGINE ─────────────────────────────────
    function executeCompare() {
      var table = document.getElementById('activeTableSelect').value;
      var startDate = document.getElementById('filterStartDate').value;
      var endDate = document.getElementById('filterEndDate').value;
      var statusVal = document.getElementById('filterStatusSelect').value;
      var limit = parseInt(document.getElementById('filterLimitSelect').value, 10) || 500;
      var btn = document.getElementById('primaryActionBtn');

      btn.disabled = true;
      btn.innerHTML = '<svg class="animate-spin w-3.5 h-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Comparing...';

      var conditions = [];
      if (table === 'appointment' || table === 'od_appointments') {
        if (statusVal === '1_2') conditions.push({ column: 'AptStatus', operator: 'IN', value: '1, 2' });
        else if (statusVal === '1') conditions.push({ column: 'AptStatus', operator: '=', value: '1' });
        else if (statusVal === '2') conditions.push({ column: 'AptStatus', operator: '=', value: '2' });
        else if (statusVal === '5') conditions.push({ column: 'AptStatus', operator: '=', value: '5' });
      }

      var tbody = document.getElementById('diffTableBody');
      tbody.innerHTML = '<tr><td colspan="8" class="p-12 text-center text-slate-400"><div class="flex flex-col items-center justify-center gap-2"><svg class="animate-spin w-6 h-6 text-slate-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg><p class="font-medium text-xs text-slate-600">Comparing OpenDental Live vs Local Database...</p></div></td></tr>';

      fetch('{{ url("/open-dental-explorer/reconcile-diff") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          table: table,
          start_date: startDate || null,
          end_date: endDate || null,
          limit: limit,
          conditions: conditions
        })
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          btn.disabled = false;
          btn.innerHTML = '<i data-lucide="play" class="w-3.5 h-3.5 fill-current"></i> Run Comparison';
          if (window.lucide) lucide.createIcons();

          if (res.error) {
            tbody.innerHTML = '<tr><td colspan="8" class="p-8 text-center text-slate-600 font-semibold text-xs">Error: ' + escHtml(res.error) + '</td></tr>';
            return;
          }

          _diffResult = res;
          renderCompareMetrics();
          renderCompareTable();
        })
        .catch(function (err) {
          btn.disabled = false;
          btn.innerHTML = '<i data-lucide="play" class="w-3.5 h-3.5 fill-current"></i> Run Comparison';
          if (window.lucide) lucide.createIcons();
          tbody.innerHTML = '<tr><td colspan="8" class="p-8 text-center text-slate-600 font-semibold text-xs">Failed: ' + escHtml(err.message) + '</td></tr>';
        });
    }

    function renderCompareMetrics() {
      if (!_diffResult) return;
      var sum = _diffResult.summary || {};
      document.getElementById('statLiveCount').textContent = Number(sum.live_count || 0).toLocaleString();
      document.getElementById('statLocalCount').textContent = Number(sum.local_count || 0).toLocaleString();
      document.getElementById('statOrphanCount').textContent = Number(sum.orphan_count || 0).toLocaleString();
      document.getElementById('statMissingCount').textContent = Number(sum.missing_count || 0).toLocaleString();
      document.getElementById('statMatchRate').textContent = (sum.match_rate_pct || 100) + '%';
      document.getElementById('statExecTime').textContent = (_diffResult.execution_time_ms || 0) + ' ms';

      document.getElementById('countPillAll').textContent = (_diffResult.diff_rows || []).length;
      document.getElementById('countPillOrphan').textContent = (sum.orphan_count || 0);
      document.getElementById('countPillMissing').textContent = (sum.missing_count || 0);
      document.getElementById('countPillMatched').textContent = (sum.matched_count || 0);

      var pruneBtn = document.getElementById('btnPruneAll');
      var syncBtn = document.getElementById('btnSyncAll');
      var exportBtn = document.getElementById('btnExportDiff');

      if ((sum.orphan_count || 0) > 0) {
        pruneBtn.disabled = false;
        pruneBtn.innerHTML = '<i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Prune Orphans';
        pruneBtn.className = 'inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-2xs transition cursor-pointer';
      } else {
        pruneBtn.disabled = true;
        pruneBtn.innerHTML = '<i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Prune Orphans';
        pruneBtn.className = 'inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold text-white bg-slate-400 rounded-lg cursor-not-allowed transition shadow-2xs disabled:opacity-60';
      }

      if ((sum.missing_count || 0) > 0) {
        syncBtn.disabled = false;
        syncBtn.innerHTML = '<i data-lucide="download-cloud" class="w-3.5 h-3.5"></i> Sync Missing';
        syncBtn.className = 'inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow-2xs transition cursor-pointer';
      } else {
        syncBtn.disabled = true;
        syncBtn.innerHTML = '<i data-lucide="download-cloud" class="w-3.5 h-3.5"></i> Sync Missing';
        syncBtn.className = 'inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold text-white bg-slate-400 rounded-lg cursor-not-allowed transition shadow-2xs disabled:opacity-60';
      }

      exportBtn.disabled = false;
      exportBtn.className = 'inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 rounded-lg transition shadow-2xs cursor-pointer';
      if (window.lucide) lucide.createIcons();
    }

    function setDiffFilter(status) {
      _activeDiffFilter = status;
      var pills = {
        all: document.getElementById('pillAll'),
        orphan: document.getElementById('pillOrphan'),
        missing: document.getElementById('pillMissing'),
        matched: document.getElementById('pillMatched')
      };

      pills.all.className = 'px-3 py-1 text-xs font-medium rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition cursor-pointer';
      pills.orphan.className = 'px-3 py-1 text-xs font-medium rounded-lg bg-white border border-rose-200 text-rose-700 hover:bg-rose-50 transition cursor-pointer';
      pills.missing.className = 'px-3 py-1 text-xs font-medium rounded-lg bg-white border border-amber-200 text-amber-800 hover:bg-amber-50 transition cursor-pointer';
      pills.matched.className = 'px-3 py-1 text-xs font-medium rounded-lg bg-white border border-emerald-200 text-emerald-800 hover:bg-emerald-50 transition cursor-pointer';

      if (status === 'orphan') pills.orphan.className = 'px-3 py-1 text-xs font-bold rounded-lg bg-rose-600 text-white shadow-2xs transition cursor-pointer';
      else if (status === 'missing') pills.missing.className = 'px-3 py-1 text-xs font-bold rounded-lg bg-amber-600 text-white shadow-2xs transition cursor-pointer';
      else if (status === 'matched') pills.matched.className = 'px-3 py-1 text-xs font-bold rounded-lg bg-emerald-600 text-white shadow-2xs transition cursor-pointer';
      else pills.all.className = 'px-3 py-1 text-xs font-bold rounded-lg bg-slate-900 text-white shadow-2xs transition cursor-pointer';

      renderCompareTable();
    }

    function renderCompareTable() {
      var tbody = document.getElementById('diffTableBody');
      if (!_diffResult || !_diffResult.diff_rows || !_diffResult.diff_rows.length) {
        tbody.innerHTML = '<tr><td colspan="8" class="p-8 text-center text-slate-400 text-xs">No records found matching criteria.</td></tr>';
        return;
      }

      var searchVal = (document.getElementById('diffSearchInput').value || '').toLowerCase().trim();
      var rows = _diffResult.diff_rows;

      if (_activeDiffFilter !== 'all') {
        rows = rows.filter(function (r) { return r.status === _activeDiffFilter; });
      }

      if (searchVal) {
        rows = rows.filter(function (r) {
          return JSON.stringify(r).toLowerCase().indexOf(searchVal) !== -1;
        });
      }

      if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="8" class="p-8 text-center text-slate-400 text-xs">No rows found matching active filter/search.</td></tr>';
        return;
      }

      var html = rows.map(function (item) {
        var d = item.data || {};
        var statusBadge = '';
        var actionBtn = '';

        if (item.status === 'orphan') {
          statusBadge = '<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">🔴 Deleted in OD</span>';
          actionBtn = '<button onclick="pruneSingleOrphan(' + item.pk + ')" class="px-2.5 py-0.5 text-[11px] font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-md transition cursor-pointer" title="Delete from local database">Prune</button>';
        } else if (item.status === 'missing') {
          statusBadge = '<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">🟡 Missing Locally</span>';
          actionBtn = '<button onclick="syncSingleMissing(' + item.pk + ')" class="px-2.5 py-0.5 text-[11px] font-semibold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-md transition cursor-pointer" title="Sync into local database">Sync</button>';
        } else {
          statusBadge = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-200">🟢 Matched</span>';
          actionBtn = '<span class="text-slate-400 text-[11px]">Synced</span>';
        }

        var patId = d.PatNum || d.patient_id || d.PatientNum || '—';
        var dateVal = d.AptDateTime || d.ProcDate || d.AdjDate || d.DatePay || d.PayDate || d.DateTP || d.DateDue || '—';
        var statusVal = (d.AptStatus !== undefined) ? 'Status ' + d.AptStatus : (d.ProcStatus || d.ProcCode || '—');
        var provVal = d.ProvNum ? 'Prov #' + d.ProvNum : '—';
        var descVal = d.Note || d.ProcDescript || d.Descript || d.ToothNum || '—';

        return '<tr class="border-b border-slate-100 hover:bg-slate-50 transition">' +
          '<td class="px-4 py-2.5">' + statusBadge + '</td>' +
          '<td class="px-4 py-2.5 font-mono font-bold text-slate-900">' + escHtml(item.pk) + '</td>' +
          '<td class="px-4 py-2.5 font-medium text-slate-700">' + escHtml(patId) + '</td>' +
          '<td class="px-4 py-2.5 font-mono text-[11px] text-slate-600">' + escHtml(dateVal) + '</td>' +
          '<td class="px-4 py-2.5 font-medium text-slate-700">' + escHtml(statusVal) + '</td>' +
          '<td class="px-4 py-2.5 text-slate-600">' + escHtml(provVal) + '</td>' +
          '<td class="px-4 py-2.5 text-slate-500 max-w-xs truncate" title="' + escHtml(descVal) + '">' + escHtml(descVal) + '</td>' +
          '<td class="px-4 py-2.5 text-right">' + actionBtn + '</td>' +
          '</tr>';
      }).join('');

      tbody.innerHTML = html;
      if (window.lucide) lucide.createIcons();
    }

    document.getElementById('diffSearchInput').addEventListener('input', renderCompareTable);

    function pruneAllOrphans() {
      if (!_diffResult || !_diffResult.orphan_keys || !_diffResult.orphan_keys.length) return;
      var count = _diffResult.orphan_keys.length;
      if (!confirm('Delete all ' + count + ' orphan record(s) from your local table "' + _diffResult.local_table + '"?')) return;

      var btn = document.getElementById('btnPruneAll');
      btn.disabled = true;
      btn.innerHTML = '<svg class="animate-spin w-3.5 h-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Pruning...';

      fetch('{{ url("/open-dental-explorer/prune-orphans") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ table: _diffResult.table, keys: _diffResult.orphan_keys })
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.error) alert('Error: ' + res.error);
          else alert(res.message || 'Successfully pruned orphan records.');
          executeCompare();
        })
        .catch(function (err) {
          alert('Prune failed: ' + err.message);
          executeCompare();
        });
    }

    function pruneSingleOrphan(pk) {
      if (!confirm('Delete orphan record #' + pk + ' from local DB?')) return;
      fetch('{{ url("/open-dental-explorer/prune-orphans") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ table: _diffResult.table, keys: [pk] })
      })
        .then(function (r) { return r.json(); })
        .then(function () { executeCompare(); });
    }

    function syncAllMissing() {
      if (!_diffResult || !_diffResult.missing_keys || !_diffResult.missing_keys.length) return;
      var missingRows = _diffResult.diff_rows
        .filter(function (r) { return r.status === 'missing'; })
        .map(function (r) { return r.data; });

      if (!confirm('Sync ' + missingRows.length + ' missing records from OpenDental into local DB?')) return;
      var btn = document.getElementById('btnSyncAll');
      btn.disabled = true;
      btn.innerHTML = '<svg class="animate-spin w-3.5 h-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Syncing...';

      fetch('{{ url("/open-dental-explorer/sync-to-local") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ table: _diffResult.table, rows: missingRows })
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.error) alert('Error: ' + res.error);
          else alert(res.message || 'Successfully synced records.');
          executeCompare();
        })
        .catch(function (err) {
          alert('Sync failed: ' + err.message);
          executeCompare();
        });
    }

    function syncSingleMissing(pk) {
      if (!_diffResult) return;
      var match = _diffResult.diff_rows.find(function (r) { return r.pk == pk && r.status === 'missing'; });
      if (!match || !match.data) return;

      fetch('{{ url("/open-dental-explorer/sync-to-local") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ table: _diffResult.table, rows: [match.data] })
      })
        .then(function (r) { return r.json(); })
        .then(function () { executeCompare(); });
    }

    function exportDiffCsv() {
      if (!_diffResult || !_diffResult.diff_rows) return;
      var rows = _diffResult.diff_rows;
      if (_activeDiffFilter !== 'all') {
        rows = rows.filter(function (r) { return r.status === _activeDiffFilter; });
      }

      var csvContent = "data:text/csv;charset=utf-8,";
      csvContent += '"Status","Primary Key","Data Source","JSON Payload"\r\n';
      rows.forEach(function (r) {
        csvContent += '"' + r.status_label + '","' + r.pk + '","' + r.source + '","' + JSON.stringify(r.data || {}).replace(/"/g, '""') + '"\r\n';
      });

      var link = document.createElement("a");
      link.setAttribute("href", encodeURI(csvContent));
      link.setAttribute("download", "opendental_diff_" + _diffResult.table + "_" + new Date().toISOString().slice(0, 10) + ".csv");
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }

    // ─── 2. SINGLE SOURCE QUERY ENGINE (LIVE OD / LOCAL DB) ───────────────────
    function executeSingleQuery(source) {
      var table = document.getElementById('activeTableSelect').value;
      var startDate = document.getElementById('filterStartDate').value;
      var endDate = document.getElementById('filterEndDate').value;
      var statusVal = document.getElementById('filterStatusSelect').value;
      var limit = parseInt(document.getElementById('filterLimitSelect').value, 10) || 50;
      var btn = document.getElementById('primaryActionBtn');

      btn.disabled = true;
      btn.innerHTML = '<svg class="animate-spin w-3.5 h-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Fetching...';

      var conditions = [];
      if (startDate && endDate) {
        var dateCol = (table.indexOf('appointment') !== -1) ? 'AptDateTime' : 'ProcDate';
        conditions.push({ column: dateCol, operator: 'BETWEEN', value: startDate + ' 00:00:00, ' + endDate + ' 23:59:59' });
      }

      if (table === 'appointment' || table === 'od_appointments') {
        if (statusVal === '1_2') conditions.push({ column: 'AptStatus', operator: 'IN', value: '1, 2' });
        else if (statusVal === '1') conditions.push({ column: 'AptStatus', operator: '=', value: '1' });
        else if (statusVal === '2') conditions.push({ column: 'AptStatus', operator: '=', value: '2' });
        else if (statusVal === '5') conditions.push({ column: 'AptStatus', operator: '=', value: '5' });
      }

      fetch('{{ url("/open-dental-explorer/query") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
          source: source,
          table: table,
          columns: ['*'],
          conditions: conditions,
          limit: limit
        })
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          btn.disabled = false;
          btn.innerHTML = '<i data-lucide="play" class="w-3.5 h-3.5 fill-current"></i> Fetch Data';
          if (window.lucide) lucide.createIcons();

          if (res.error) {
            alert('Error: ' + res.error);
            return;
          }

          _singleQueryResult = res;
          renderSingleQueryResults();
        })
        .catch(function (err) {
          btn.disabled = false;
          btn.innerHTML = '<i data-lucide="play" class="w-3.5 h-3.5 fill-current"></i> Fetch Data';
          if (window.lucide) lucide.createIcons();
          alert('Fetch error: ' + err.message);
        });
    }

    function renderSingleQueryResults() {
      if (!_singleQueryResult) return;
      var count = _singleQueryResult.count || 0;
      var cols = _singleQueryResult.columns || [];
      var rows = _singleQueryResult.rows || [];

      document.getElementById('singleSourceCount').textContent = '(' + count.toLocaleString() + ' rows - ' + (_singleQueryResult.execution_time_ms || 0) + ' ms)';

      var theadTr = document.getElementById('singleQueryThead');
      var tbody = document.getElementById('singleQueryTbody');

      if (!rows.length) {
        theadTr.innerHTML = '<tr><th class="p-4 text-slate-400 font-normal text-xs">No records found.</th></tr>';
        tbody.innerHTML = '<tr><td class="p-12 text-center text-slate-400 text-xs">No records returned from this query.</td></tr>';
        return;
      }

      var thHtml = '<tr>' + cols.map(function (c) {
        return '<th class="px-4 py-2.5 whitespace-nowrap">' + escHtml(c) + '</th>';
      }).join('') + '</tr>';
      theadTr.innerHTML = thHtml;

      var tbodyHtml = rows.map(function (row) {
        return '<tr class="hover:bg-slate-50 transition border-b border-slate-100">' + cols.map(function (c) {
          var val = (row[c] !== undefined && row[c] !== null) ? String(row[c]) : '';
          return '<td class="px-4 py-2 max-w-xs truncate text-slate-700" title="' + escHtml(val) + '">' + escHtml(val) + '</td>';
        }).join('') + '</tr>';
      }).join('');

      tbody.innerHTML = tbodyHtml;
    }

    function syncSingleQueryToLocal() {
      if (!_singleQueryResult || !_singleQueryResult.rows || !_singleQueryResult.rows.length) return;
      if (!confirm('Sync ' + _singleQueryResult.rows.length + ' live records into local database?')) return;

      var btn = document.getElementById('btnSyncSingleLive');
      btn.disabled = true;
      btn.textContent = 'Syncing...';

      fetch('{{ url("/open-dental-explorer/sync-to-local") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
          table: _singleQueryResult.table,
          rows: _singleQueryResult.rows
        })
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          btn.disabled = false;
          btn.innerHTML = '<i data-lucide="cloud-download" class="w-3.5 h-3.5"></i> Sync Fetched to Local DB';
          if (window.lucide) lucide.createIcons();
          if (res.error) alert('Error: ' + res.error);
          else alert(res.message || 'Synced successfully.');
        });
    }

    function exportSingleQueryCsv() {
      if (!_singleQueryResult || !_singleQueryResult.rows || !_singleQueryResult.rows.length) return;
      var cols = _singleQueryResult.columns || Object.keys(_singleQueryResult.rows[0]);
      var rows = _singleQueryResult.rows;

      var csvContent = "data:text/csv;charset=utf-8,";
      csvContent += cols.map(function (c) { return '"' + c + '"'; }).join(',') + "\r\n";

      rows.forEach(function (r) {
        csvContent += cols.map(function (c) {
          var v = (r[c] !== undefined && r[c] !== null) ? String(r[c]) : '';
          return '"' + v.replace(/"/g, '""') + '"';
        }).join(',') + "\r\n";
      });

      var link = document.createElement("a");
      link.setAttribute("href", encodeURI(csvContent));
      link.setAttribute("download", "opendental_export_" + (_singleQueryResult.table || 'query') + ".csv");
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }

    // ─── 3. SYNC CHECKPOINTS ENGINE ───────────────────────────────────────────
    function loadSyncCheckpoints() {
      var tbody = document.getElementById('checkpointsTbody');
      if (!tbody) return;
      tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-slate-400 text-xs">Loading sync checkpoints...</td></tr>';

      fetch('{{ url("/open-dental-explorer/sync-checkpoints") }}')
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var logs = data.logs || [];
          if (!logs.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-slate-400 text-xs">No sync logs recorded yet.</td></tr>';
            return;
          }
          var html = logs.map(function (log) {
            var statusBadge = '<span class="px-2 py-0.5 text-[11px] font-medium rounded-md bg-slate-100 text-slate-700 border border-slate-200">' + escHtml(log.status) + '</span>';
            if (log.status === 'completed') statusBadge = '<span class="px-2 py-0.5 text-[11px] font-medium rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200">Completed</span>';
            if (log.status === 'running') statusBadge = '<span class="px-2 py-0.5 text-[11px] font-medium rounded-md bg-blue-50 text-blue-800 border border-blue-200 animate-pulse">Running</span>';
            if (log.status === 'failed') statusBadge = '<span class="px-2 py-0.5 text-[11px] font-semibold rounded-md bg-rose-50 text-rose-700 border border-rose-200">Failed</span>';

            var errorHtml = '';
            if (log.last_error) {
              errorHtml = '<div class="mt-1 text-[11px] text-rose-700 bg-rose-50 border border-rose-200 rounded p-1.5 font-mono break-words max-w-xs leading-tight">' +
                '<span class="font-bold">Error:</span> ' + escHtml(log.last_error) +
                '</div>';
            }

            var escMod = escHtml(log.module);
            var dateVal = log.last_synced_at ? String(log.last_synced_at).replace(' ', 'T').slice(0, 16) : '';

            return '<tr class="border-b border-slate-100 hover:bg-slate-50 transition text-xs">' +
              '<td class="px-4 py-2.5 font-bold text-slate-900">' + escMod + '</td>' +
              '<td class="px-4 py-2.5">' + statusBadge + errorHtml + '</td>' +
              '<td class="px-4 py-2.5"><input type="datetime-local" class="cp-date bg-white border border-slate-300 rounded-md px-2 py-1 text-xs text-slate-800 focus:ring-slate-400 shadow-2xs" value="' + dateVal + '"></td>' +
              '<td class="px-4 py-2.5"><input type="number" class="cp-pk w-28 bg-white border border-slate-300 rounded-md px-2 py-1 text-xs text-slate-800 focus:ring-slate-400 shadow-2xs" value="' + (log.last_primary_key || 0) + '"></td>' +
              '<td class="px-4 py-2.5 font-mono font-semibold text-slate-700">' + Number(log.total_processed || 0).toLocaleString() + '</td>' +
              '<td class="px-4 py-2.5 text-right"><button onclick="saveCheckpointRow(\'' + escMod + '\', this)" class="px-3 py-1 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 rounded-md transition shadow-2xs cursor-pointer">Reset Date</button></td>' +
              '</tr>';
          }).join('');
          tbody.innerHTML = html;
        });
    }

    function saveCheckpointRow(module, btn) {
      var tr = btn.closest('tr');
      var dateVal = tr.querySelector('.cp-date').value;
      var pkVal = tr.querySelector('.cp-pk').value;
      btn.disabled = true;
      btn.textContent = 'Saving...';

      fetch('{{ url("/open-dental-explorer/reset-sync-checkpoint") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
          module: module,
          last_synced_at: dateVal ? dateVal.replace('T', ' ') : null,
          last_primary_key: parseInt(pkVal, 10) || 0
        })
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          btn.disabled = false;
          btn.textContent = 'Reset Date';
          if (res.error) alert('Error: ' + res.error);
          else alert(res.message || 'Saved.');
          loadSyncCheckpoints();
        })
        .catch(function (err) {
          btn.disabled = false;
          btn.textContent = 'Reset Date';
          alert('Failed: ' + err.message);
        });
    }

    function resetAllCheckpoints() {
      if (!confirm('Reset ALL sync checkpoints?')) return;
      fetch('{{ url("/open-dental-explorer/reset-sync-checkpoint") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ module: 'all', last_synced_at: null, last_primary_key: 0 })
      })
        .then(function (r) { return r.json(); })
        .then(function () {
          alert('All checkpoints reset.');
          loadSyncCheckpoints();
        });
    }

    function escHtml(s) {
      return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
  </script>
</x-app-layout>
