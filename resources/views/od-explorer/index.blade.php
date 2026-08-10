<x-app-layout>
  <div class="p-6 space-y-6 max-w-[1600px] mx-auto">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
      <div class="flex items-center gap-3.5">
        <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100 text-emerald-600">
          <i data-lucide="database" class="w-7 h-7"></i>
        </div>
        <div>
          <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
            OpenDental Realtime Data Explorer
            <span id="sourceLiveBadge" class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> OpenDental Live Connected
            </span>
          </h1>
          <p class="text-sm text-slate-500 mt-0.5">
            Interactively build queries, filter data with custom conditions (WHERE, AND, OR), inspect schema, and preview live records directly from OpenDental.
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2.5">
        <button id="resetBtn" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition focus:outline-none">
          <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset Builder
        </button>
        <button id="exportCsvBtn" disabled class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-slate-400 rounded-xl cursor-not-allowed transition shadow-sm focus:outline-none disabled:opacity-60">
          <i data-lucide="download" class="w-4 h-4"></i> Export CSV
        </button>
        <button id="syncToLocalBtn" disabled class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-white bg-slate-400 rounded-xl cursor-not-allowed transition shadow-sm focus:outline-none disabled:opacity-60" title="Sync fetched OpenDental records into local database table">
          <i data-lucide="cloud-download" class="w-4 h-4"></i> Sync to Local DB
        </button>
        <button id="runQueryBtn" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-bold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 shadow-md shadow-emerald-500/20 transition focus:outline-none">
          <i data-lucide="play" class="w-4 h-4 fill-current"></i> Fetch Realtime Data
        </button>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 bg-white px-4 py-2 rounded-xl shadow-xs">
      <button id="tabQueryBuilderBtn" onclick="switchOdTab('queryBuilder')" class="flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 transition">
        <i data-lucide="sliders" class="w-4 h-4"></i> Realtime Query Builder
      </button>
      <button id="tabSyncCheckpointsBtn" onclick="switchOdTab('syncCheckpoints')" class="flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-lg text-slate-600 hover:bg-slate-100 transition">
        <i data-lucide="history" class="w-4 h-4"></i> Sync Checkpoints & Reset Start Date
      </button>
    </div>

    <!-- Query Builder View (Tab 1) -->
    <div id="tabQueryBuilderView">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      
      <!-- Query Builder Sidebar (Left Column) -->
      <div class="lg:col-span-4 space-y-5">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <span class="text-xs font-extrabold uppercase tracking-wider text-slate-700 flex items-center gap-2">
              <i data-lucide="sliders" class="w-4 h-4 text-emerald-600"></i> Query Builder Controls
            </span>
            <span id="schemaStatus" class="text-xs font-medium text-slate-500">Select a table</span>
          </div>

          <div class="p-5 space-y-5">
            
            <!-- 0. Data Source Selector Toggle -->
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">0. Database Source</label>
              <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl border border-slate-200">
                <button type="button" id="srcLiveBtn" class="py-2 px-3 text-xs font-extrabold rounded-lg transition bg-white text-emerald-700 shadow-sm border border-slate-200 flex items-center justify-center gap-1.5">
                  <span class="w-2 h-2 rounded-full bg-emerald-500"></span> OpenDental Live API
                </button>
                <button type="button" id="srcLocalBtn" class="py-2 px-3 text-xs font-semibold rounded-lg text-slate-600 transition hover:bg-white/60 flex items-center justify-center gap-1.5">
                  <i data-lucide="database" class="w-3.5 h-3.5"></i> Local DB Snapshot
                </button>
              </div>
            </div>

            <!-- 1. Table Selection -->
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">1. Target OpenDental Table</label>
              <div class="relative">
                <select id="tableSelect" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 p-3 pr-10 font-semibold shadow-sm transition">
                  <option value="" disabled selected>-- Select an OpenDental Table --</option>
                  
                  <optgroup label="⚡ OpenDental Native Live Tables">
                    @foreach($openDentalTables as $tbl)
                      <option value="{{ $tbl }}">{{ $tbl }}</option>
                    @endforeach
                  </optgroup>

                  <optgroup label="💾 Local Synced Tables">
                    @foreach($localTables as $tbl)
                      <option value="{{ $tbl }}">{{ $tbl }}</option>
                    @endforeach
                  </optgroup>
                </select>
              </div>
            </div>

            <!-- 2. Column Selection -->
            <div>
              <div class="flex items-center justify-between mb-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">2. Selected Columns</label>
                <div class="flex items-center gap-2 text-xs">
                  <button type="button" id="selectAllColsBtn" class="text-emerald-600 hover:text-emerald-800 font-semibold hover:underline">Select All</button>
                  <span class="text-slate-300">|</span>
                  <button type="button" id="clearColsBtn" class="text-slate-500 hover:text-slate-700 font-semibold hover:underline">Clear</button>
                </div>
              </div>
              <div id="columnsContainer" class="max-h-48 overflow-y-auto p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1.5 text-xs">
                <p class="text-slate-400 italic">Select a table first to inspect available columns.</p>
              </div>
            </div>

            <!-- 3. Where Filter Conditions Builder -->
            <div>
              <div class="flex items-center justify-between mb-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">3. Filter Conditions (WHERE / AND / OR)</label>
                <button type="button" id="addConditionBtn" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition border border-emerald-200">
                  <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Condition
                </button>
              </div>
              
              <div id="conditionsList" class="space-y-2.5">
                <div id="noConditionsMsg" class="p-3 bg-slate-50 border border-dashed border-slate-300 rounded-xl text-center text-xs text-slate-400">
                  No filter conditions added. Showing all matching records.
                </div>
              </div>
            </div>

            <!-- 4. Ordering & Limit -->
            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-100">
              <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Order By</label>
                <select id="orderBySelect" class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl p-2.5 font-medium focus:ring-emerald-500">
                  <option value="">(Default Order)</option>
                </select>
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Direction</label>
                <select id="orderDirSelect" class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl p-2.5 font-medium focus:ring-emerald-500">
                  <option value="asc">Ascending (ASC)</option>
                  <option value="desc">Descending (DESC)</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Max Record Limit</label>
              <select id="limitSelect" class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl p-2.5 font-semibold focus:ring-emerald-500">
                <option value="10">10 Records</option>
                <option value="25">25 Records</option>
                <option value="50" selected>50 Records</option>
                <option value="100">100 Records</option>
                <option value="250">250 Records</option>
                <option value="500">500 Records</option>
                <option value="1000">1,000 Records</option>
              </select>
            </div>

          </div>
        </div>
      </div>

      <!-- Results & Preview Panel (Right Column) -->
      <div class="lg:col-span-8 space-y-5">
        <!-- Stat Cards Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl">
              <i data-lucide="rows-3" class="w-5 h-5"></i>
            </div>
            <div>
              <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Rows</p>
              <p id="statRows" class="text-xl font-extrabold text-slate-900 tabular-nums">0</p>
            </div>
          </div>
          <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="p-2.5 bg-amber-50 text-amber-600 rounded-xl">
              <i data-lucide="zap" class="w-5 h-5"></i>
            </div>
            <div>
              <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Query Time</p>
              <p id="statTime" class="text-xl font-extrabold text-slate-900 tabular-nums">0 ms</p>
            </div>
          </div>
          <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="p-2.5 bg-purple-50 text-purple-600 rounded-xl">
              <i data-lucide="columns-3" class="w-5 h-5"></i>
            </div>
            <div>
              <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Columns</p>
              <p id="statCols" class="text-xl font-extrabold text-slate-900 tabular-nums">0</p>
            </div>
          </div>
          <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl">
              <i data-lucide="filter" class="w-5 h-5"></i>
            </div>
            <div>
              <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Source</p>
              <p id="statSource" class="text-xs font-bold text-emerald-700 tracking-tight">OpenDental Live</p>
            </div>
          </div>
        </div>

        <!-- Fallback Notice Banner -->
        <div id="noticeBanner" class="hidden p-4 bg-amber-50 border border-amber-200 rounded-2xl text-amber-800 text-xs flex items-center gap-2">
          <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 flex-shrink-0"></i>
          <span id="noticeText">Notice details...</span>
        </div>

        <!-- Raw SQL Query Generated Preview (Collapsible) -->
        <div class="bg-slate-900 rounded-2xl shadow-sm overflow-hidden text-slate-200 border border-slate-800">
          <div class="px-5 py-3 bg-slate-950/80 border-b border-slate-800 flex items-center justify-between cursor-pointer" onclick="document.getElementById('sqlDrawer').classList.toggle('hidden')">
            <span class="text-xs font-mono font-bold text-emerald-400 flex items-center gap-2">
              <i data-lucide="code-2" class="w-4 h-4"></i> Generated OpenDental SQL Statement
            </span>
            <span class="text-[11px] text-slate-500 flex items-center gap-1 font-mono">
              Click to toggle SQL preview <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
            </span>
          </div>
          <div id="sqlDrawer" class="p-4 font-mono text-xs overflow-x-auto text-emerald-400 bg-slate-900/90 whitespace-pre-wrap leading-relaxed">
            SELECT * FROM [Select Table Above]
          </div>
        </div>

        <!-- Data Results Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
          <!-- Table Toolbar -->
          <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
              <h2 id="tableTitle" class="text-base font-bold text-slate-800">Data Results Preview</h2>
              <span id="loadingSpinner" class="hidden text-xs text-emerald-600 font-semibold flex items-center gap-1.5 ml-2">
                <svg class="animate-spin w-4 h-4 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Fetching realtime data from OpenDental API...
              </span>
            </div>

            <div class="relative min-w-[220px]">
              <input type="text" id="quickFilterInput" placeholder="Quick search results..." class="w-full pl-9 pr-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm">
              <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2"></i>
            </div>
          </div>

          <!-- Table Content Container -->
          <div class="overflow-x-auto overflow-y-auto max-h-[600px] min-h-[350px] relative">
            <table class="dds-table dds-sortable w-full text-left text-xs border-collapse" id="resultsTable">
              <thead class="bg-slate-100 sticky top-0 z-10 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider">
                <tr id="resultsTheadTr">
                  <th class="p-4 text-slate-400 italic font-normal">Select a table and click "Fetch Realtime Data" to query OpenDental.</th>
                </tr>
              </thead>
              <tbody id="resultsTbody" class="divide-y divide-slate-100 text-slate-800">
                <tr>
                  <td class="p-12 text-center text-slate-400">
                    <div class="flex flex-col items-center justify-center gap-2">
                      <i data-lucide="database-backup" class="w-10 h-10 text-slate-300 stroke-1"></i>
                      <p class="font-medium text-sm text-slate-500">No Realtime Query Executed Yet</p>
                      <p class="text-xs text-slate-400 max-w-sm">Choose an OpenDental table from the left controls, select columns or filters, and click <strong class="text-emerald-600">Fetch Realtime Data</strong>.</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Footer / Pagination -->
          <div class="p-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between text-xs text-slate-500 font-medium">
            <span id="pageInfo">Showing 0 of 0 records</span>
            <div class="flex items-center gap-2">
              <button id="prevPageBtn" disabled class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-600 disabled:opacity-50 hover:bg-slate-50">Previous</button>
              <button id="nextPageBtn" disabled class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-600 disabled:opacity-50 hover:bg-slate-50">Next</button>
            </div>
          </div>

        </div>
      </div>

    </div>
    <!-- End Query Builder View (Tab 1) -->

    <!-- Sync Checkpoints Management View (Tab 2) -->
    <div id="tabSyncCheckpointsView" class="hidden space-y-6">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
          <div>
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
              <i data-lucide="refresh-cw" class="w-5 h-5 text-indigo-600"></i> OpenDental Sync Checkpoints & Start Date Reset
            </h2>
            <p class="text-xs text-slate-500 mt-1">
              Reset or alter the sync start date (`last_synced_at`) and primary key watermark (`last_primary_key`) for any OpenDental sync module. When sync next executes, existing records will be updated and non-existent records will be inserted without duplicate rows being created.
            </p>
          </div>
          <div class="flex items-center gap-3">
            <button onclick="loadSyncCheckpoints()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-slate-700 bg-slate-100 rounded-xl hover:bg-slate-200 transition">
              <i data-lucide="rotate-cw" class="w-3.5 h-3.5"></i> Refresh List
            </button>
            <button onclick="resetAllCheckpoints()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-rose-700 bg-rose-50 border border-rose-200 rounded-xl hover:bg-rose-100 transition">
              <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i> Reset All Checkpoints to Start
            </button>
          </div>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-xl">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-100 border-b border-slate-200 text-xs font-bold text-slate-700 uppercase tracking-wider">
                <th class="px-4 py-3">Module / Table</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Last Synced Date (`last_synced_at`)</th>
                <th class="px-4 py-3">Last Primary Key (`last_primary_key`)</th>
                <th class="px-4 py-3">Total Records Synced</th>
                <th class="px-4 py-3 text-right">Action</th>
              </tr>
            </thead>
            <tbody id="checkpointsTbody">
              <tr>
                <td colspan="6" class="p-8 text-center text-slate-400 text-sm">Loading sync checkpoints...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Lucide & Dynamic JavaScript Engine -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var _selectedSource = 'opendental_live';
      var _availableColumns = [];
      var _queryResultData = null;

      var tableSelect = document.getElementById('tableSelect');
      var columnsContainer = document.getElementById('columnsContainer');
      var orderBySelect = document.getElementById('orderBySelect');
      var orderDirSelect = document.getElementById('orderDirSelect');
      var limitSelect = document.getElementById('limitSelect');
      var conditionsList = document.getElementById('conditionsList');
      var noConditionsMsg = document.getElementById('noConditionsMsg');
      var addConditionBtn = document.getElementById('addConditionBtn');
      var runQueryBtn = document.getElementById('runQueryBtn');
      var resetBtn = document.getElementById('resetBtn');
      var exportCsvBtn = document.getElementById('exportCsvBtn');
      var syncToLocalBtn = document.getElementById('syncToLocalBtn');
      var loadingSpinner = document.getElementById('loadingSpinner');
      var quickFilterInput = document.getElementById('quickFilterInput');
      var srcLiveBtn = document.getElementById('srcLiveBtn');
      var srcLocalBtn = document.getElementById('srcLocalBtn');
      var noticeBanner = document.getElementById('noticeBanner');
      var noticeText = document.getElementById('noticeText');

      // Source Toggle Buttons
      srcLiveBtn.addEventListener('click', function () {
        _selectedSource = 'opendental_live';
        srcLiveBtn.className = 'py-2 px-3 text-xs font-extrabold rounded-lg transition bg-white text-emerald-700 shadow-sm border border-slate-200 flex items-center justify-center gap-1.5';
        srcLocalBtn.className = 'py-2 px-3 text-xs font-semibold rounded-lg text-slate-600 transition hover:bg-white/60 flex items-center justify-center gap-1.5';
        document.getElementById('statSource').textContent = 'OpenDental Live';
        document.getElementById('statSource').className = 'text-xs font-bold text-emerald-700 tracking-tight';
      });

      srcLocalBtn.addEventListener('click', function () {
        _selectedSource = 'local_db';
        srcLocalBtn.className = 'py-2 px-3 text-xs font-extrabold rounded-lg transition bg-white text-blue-700 shadow-sm border border-slate-200 flex items-center justify-center gap-1.5';
        srcLiveBtn.className = 'py-2 px-3 text-xs font-semibold rounded-lg text-slate-600 transition hover:bg-white/60 flex items-center justify-center gap-1.5';
        document.getElementById('statSource').textContent = 'Local DB Snapshot';
        document.getElementById('statSource').className = 'text-xs font-bold text-blue-700 tracking-tight';
      });

      // Table Change Handler
      tableSelect.addEventListener('change', function () {
        var table = this.value;
        if (!table) return;

        document.getElementById('schemaStatus').textContent = 'Loading schema...';
        columnsContainer.innerHTML = '<p class="text-emerald-600 italic">Fetching columns for ' + table + '...</p>';

        fetch('{{ url("/open-dental-explorer/columns") }}?table=' + encodeURIComponent(table))
          .then(function (r) { return r.json(); })
          .then(function (res) {
            if (res.error) {
              columnsContainer.innerHTML = '<p class="text-rose-600 font-semibold">' + res.error + '</p>';
              document.getElementById('schemaStatus').textContent = 'Error';
              return;
            }
            _availableColumns = res.columns || [];
            document.getElementById('schemaStatus').textContent = _availableColumns.length + ' columns found';
            renderColumnsCheckboxList();
            renderOrderByOptions();
            resetConditions();
          })
          .catch(function () {
            columnsContainer.innerHTML = '<p class="text-rose-600 font-semibold">Failed to fetch columns schema.</p>';
            document.getElementById('schemaStatus').textContent = 'Error';
          });
      });

      function renderColumnsCheckboxList() {
        if (!_availableColumns.length) {
          columnsContainer.innerHTML = '<p class="text-slate-400 italic">No columns found.</p>';
          return;
        }

        var html = '<div class="space-y-1.5">';
        _availableColumns.forEach(function (col) {
          html += '<label class="flex items-center gap-2 cursor-pointer hover:bg-slate-100 p-1 rounded transition">';
          html += '<input type="checkbox" class="col-checkbox rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" value="' + escHtml(col) + '" checked>';
          html += '<span class="font-mono text-[11px] text-slate-700">' + escHtml(col) + '</span>';
          html += '</label>';
        });
        html += '</div>';
        columnsContainer.innerHTML = html;
      }

      function renderOrderByOptions() {
        var html = '<option value="">(Default Order)</option>';
        _availableColumns.forEach(function (col) {
          html += '<option value="' + escHtml(col) + '">' + escHtml(col) + '</option>';
        });
        orderBySelect.innerHTML = html;
      }

      document.getElementById('selectAllColsBtn').addEventListener('click', function () {
        document.querySelectorAll('.col-checkbox').forEach(function (cb) { cb.checked = true; });
      });
      document.getElementById('clearColsBtn').addEventListener('click', function () {
        document.querySelectorAll('.col-checkbox').forEach(function (cb) { cb.checked = false; });
      });

      function resetConditions() {
        conditionsList.innerHTML = '';
        conditionsList.appendChild(noConditionsMsg);
        noConditionsMsg.classList.remove('hidden');
      }

      addConditionBtn.addEventListener('click', function () {
        if (!_availableColumns.length) {
          alert('Please select a table first before adding filter conditions.');
          return;
        }
        noConditionsMsg.classList.add('hidden');
        var condId = 'cond_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4);
        var isFirst = conditionsList.querySelectorAll('.condition-row').length === 0;

        var div = document.createElement('div');
        div.className = 'condition-row p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-2 text-xs relative group transition hover:border-slate-300';
        div.dataset.id = condId;

        var html = '<div class="flex items-center gap-2">';
        if (!isFirst) {
          html += '<select class="cond-logical bg-white border border-slate-300 text-slate-700 font-bold rounded-lg p-1 text-[11px] focus:ring-emerald-500">';
          html += '<option value="and">AND</option>';
          html += '<option value="or">OR</option>';
          html += '</select>';
        } else {
          html += '<span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider px-1">WHERE</span>';
        }

        html += '<select class="cond-col flex-1 bg-white border border-slate-300 text-slate-800 font-medium rounded-lg p-1.5 text-xs focus:ring-emerald-500">';
        _availableColumns.forEach(function (c) {
          html += '<option value="' + escHtml(c) + '">' + escHtml(c) + '</option>';
        });
        html += '</select>';

        html += '<button type="button" class="remove-cond-btn p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>';
        html += '</div>';

        html += '<div class="grid grid-cols-12 gap-2 items-center">';
        html += '<div class="col-span-5">';
        html += '<select class="cond-op w-full bg-white border border-slate-300 text-slate-800 font-semibold rounded-lg p-1.5 text-[11px] focus:ring-emerald-500">';
        html += '<option value="=">= (Equals)</option>';
        html += '<option value="!=">!= (Not Equal)</option>';
        html += '<option value="LIKE">LIKE (Contains)</option>';
        html += '<option value="NOT LIKE">NOT LIKE</option>';
        html += '<option value=">">&gt; (Greater Than)</option>';
        html += '<option value=">=">&gt;= (Greater or Equal)</option>';
        html += '<option value="<">&lt; (Less Than)</option>';
        html += '<option value="<=">&lt;= (Less or Equal)</option>';
        html += '<option value="IN">IN (Comma separated)</option>';
        html += '<option value="NOT IN">NOT IN</option>';
        html += '<option value="IS NULL">IS NULL</option>';
        html += '<option value="IS NOT NULL">IS NOT NULL</option>';
        html += '<option value="BETWEEN">BETWEEN (val1, val2)</option>';
        html += '</select>';
        html += '</div>';

        html += '<div class="col-span-7">';
        html += '<input type="text" class="cond-val w-full bg-white border border-slate-300 rounded-lg p-1.5 text-xs font-medium text-slate-900 focus:ring-emerald-500 placeholder-slate-400" placeholder="Filter value...">';
        html += '</div>';
        html += '</div>';

        div.innerHTML = html;
        conditionsList.appendChild(div);
        lucide.createIcons();

        div.querySelector('.remove-cond-btn').addEventListener('click', function () {
          div.remove();
          if (conditionsList.querySelectorAll('.condition-row').length === 0) {
            noConditionsMsg.classList.remove('hidden');
          }
        });

        div.querySelector('.cond-op').addEventListener('change', function () {
          var valInput = div.querySelector('.cond-val');
          if (this.value === 'IS NULL' || this.value === 'IS NOT NULL') {
            valInput.value = '';
            valInput.disabled = true;
            valInput.classList.add('bg-slate-100');
          } else {
            valInput.disabled = false;
            valInput.classList.remove('bg-slate-100');
          }
        });
      });

      function buildPayload() {
        var table = tableSelect.value;
        if (!table) return null;

        var selectedCols = [];
        document.querySelectorAll('.col-checkbox:checked').forEach(function (cb) {
          selectedCols.push(cb.value);
        });

        var conditions = [];
        conditionsList.querySelectorAll('.condition-row').forEach(function (row) {
          var col = row.querySelector('.cond-col').value;
          var op = row.querySelector('.cond-op').value;
          var val = row.querySelector('.cond-val').value;
          var logicalEl = row.querySelector('.cond-logical');
          var logical = logicalEl ? logicalEl.value : 'and';

          if (col) {
            conditions.push({
              logical: logical,
              column: col,
              operator: op,
              value: val
            });
          }
        });

        return {
          source: _selectedSource,
          table: table,
          columns: selectedCols.length ? selectedCols : ['*'],
          conditions: conditions,
          order_by: orderBySelect.value || null,
          order_direction: orderDirSelect.value || 'asc',
          limit: parseInt(limitSelect.value, 10) || 50
        };
      }

      runQueryBtn.addEventListener('click', function () {
        var payload = buildPayload();
        if (!payload) {
          alert('Please select an OpenDental table to fetch data.');
          return;
        }

        loadingSpinner.classList.remove('hidden');
        runQueryBtn.disabled = true;
        runQueryBtn.classList.add('opacity-75');

        fetch('{{ url("/open-dental-explorer/query") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify(payload)
        })
          .then(function (r) { return r.json(); })
          .then(function (res) {
            loadingSpinner.classList.add('hidden');
            runQueryBtn.disabled = false;
            runQueryBtn.classList.remove('opacity-75');

            if (res.error) {
              alert('Error executing OpenDental query: ' + res.error);
              return;
            }

            _queryResultData = res;
            renderResults();
          })
          .catch(function (err) {
            loadingSpinner.classList.add('hidden');
            runQueryBtn.disabled = false;
            runQueryBtn.classList.remove('opacity-75');
            alert('Failed to fetch data: ' + err.message);
          });
      });

      function renderResults() {
        if (!_queryResultData) return;

        document.getElementById('statRows').textContent = _queryResultData.count.toLocaleString();
        document.getElementById('statTime').textContent = _queryResultData.execution_time_ms + ' ms';
        document.getElementById('statCols').textContent = _queryResultData.columns.length;
        document.getElementById('statSource').textContent = _queryResultData.source_type || 'OpenDental Live';

        if (_queryResultData.notice) {
          noticeText.textContent = _queryResultData.notice;
          noticeBanner.classList.remove('hidden');
        } else {
          noticeBanner.classList.add('hidden');
        }

        var formattedSql = _queryResultData.sql;
        if (_queryResultData.bindings && _queryResultData.bindings.length) {
          formattedSql += '\n\n-- Bindings: ' + JSON.stringify(_queryResultData.bindings);
        }
        document.getElementById('sqlDrawer').textContent = formattedSql;

        exportCsvBtn.disabled = _queryResultData.count === 0;
        syncToLocalBtn.disabled = _queryResultData.count === 0;

        if (_queryResultData.count > 0) {
          exportCsvBtn.classList.remove('bg-slate-400', 'cursor-not-allowed');
          exportCsvBtn.classList.add('bg-emerald-600', 'hover:bg-emerald-700', 'shadow-md', 'shadow-emerald-500/20');
          syncToLocalBtn.classList.remove('bg-slate-400', 'cursor-not-allowed');
          syncToLocalBtn.classList.add('bg-indigo-600', 'hover:bg-indigo-700', 'shadow-md', 'shadow-indigo-500/20');
        } else {
          exportCsvBtn.classList.add('bg-slate-400', 'cursor-not-allowed');
          exportCsvBtn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
          syncToLocalBtn.classList.add('bg-slate-400', 'cursor-not-allowed');
          syncToLocalBtn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
        }

        document.getElementById('tableTitle').textContent = 'Results: ' + _queryResultData.table + ' (' + _queryResultData.count + ' rows)';

        var cols = _queryResultData.columns;
        var rows = _queryResultData.rows || [];

        var q = (quickFilterInput.value || '').toLowerCase().trim();
        if (q) {
          rows = rows.filter(function (r) {
            return Object.values(r).some(function (v) {
              return String(v).toLowerCase().includes(q);
            });
          });
        }

        var thHtml = '';
        cols.forEach(function (c) {
          thHtml += '<th class="px-4 py-3 border-r border-slate-200 whitespace-nowrap bg-slate-100 font-bold text-[11px] text-slate-700">' + escHtml(c) + '</th>';
        });
        document.getElementById('resultsTheadTr').innerHTML = thHtml;

        var tbHtml = '';
        if (rows.length === 0) {
          tbHtml = '<tr><td colspan="' + cols.length + '" class="p-8 text-center text-slate-400 font-medium">No matching records returned for this OpenDental query.</td></tr>';
        } else {
          rows.forEach(function (row) {
            tbHtml += '<tr class="hover:bg-emerald-50/40 transition duration-150">';
            cols.forEach(function (col) {
              var val = row[col];
              var displayVal = (val === null || val === undefined) ? '<span class="text-slate-300 italic">NULL</span>' : escHtml(String(val));
              tbHtml += '<td class="px-4 py-2.5 border-r border-b border-slate-100 font-mono text-[11px] whitespace-nowrap overflow-hidden max-w-xs text-ellipsis" title="' + escHtml(String(val ?? '')) + '">' + displayVal + '</td>';
            });
            tbHtml += '</tr>';
          });
        }
        document.getElementById('resultsTbody').innerHTML = tbHtml;

        document.getElementById('pageInfo').textContent = 'Showing ' + rows.length.toLocaleString() + ' of ' + _queryResultData.count.toLocaleString() + ' fetched records';
      }

      quickFilterInput.addEventListener('input', function () {
        renderResults();
      });

      exportCsvBtn.addEventListener('click', function () {
        if (!_queryResultData || !_queryResultData.rows || !_queryResultData.rows.length) return;

        var cols = _queryResultData.columns;
        var rows = _queryResultData.rows;

        var csvContent = "data:text/csv;charset=utf-8,";
        csvContent += cols.map(function(c) { return '"' + String(c).replace(/"/g, '""') + '"'; }).join(",") + "\n";

        rows.forEach(function(row) {
          var rowStr = cols.map(function(col) {
            var val = row[col];
            if (val === null || val === undefined) return '""';
            return '"' + String(val).replace(/"/g, '""') + '"';
          }).join(",");
          csvContent += rowStr + "\n";
        });

        var encodedUri = encodeURI(csvContent);
        var link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", _queryResultData.table + "_opendental_export_" + new Date().toISOString().slice(0, 10) + ".csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      });

      syncToLocalBtn.addEventListener('click', function () {
        if (!_queryResultData || !_queryResultData.rows || !_queryResultData.rows.length) return;

        syncToLocalBtn.disabled = true;
        syncToLocalBtn.innerHTML = '<svg class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Syncing to Local DB...';

        fetch('{{ url("/open-dental-explorer/sync-to-local") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({
            table: _queryResultData.table,
            rows: _queryResultData.rows
          })
        })
          .then(function (r) { return r.json(); })
          .then(function (res) {
            syncToLocalBtn.disabled = false;
            syncToLocalBtn.innerHTML = '<i data-lucide="cloud-download" class="w-4 h-4"></i> Sync to Local DB';
            lucide.createIcons();

            if (res.error) {
              alert('Error syncing to local database: ' + res.error);
              return;
            }

            noticeText.textContent = '✅ ' + (res.message || ('Successfully synced ' + res.synced_count + ' record(s) into local table.'));
            noticeBanner.className = 'p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs flex items-center gap-2 shadow-sm font-medium';
            noticeBanner.classList.remove('hidden');
          })
          .catch(function (err) {
            syncToLocalBtn.disabled = false;
            syncToLocalBtn.innerHTML = '<i data-lucide="cloud-download" class="w-4 h-4"></i> Sync to Local DB';
            lucide.createIcons();
            alert('Failed to sync data: ' + err.message);
          });
      });

      resetBtn.addEventListener('click', function () {
        tableSelect.value = '';
        columnsContainer.innerHTML = '<p class="text-slate-400 italic">Select a table first to inspect available columns.</p>';
        orderBySelect.innerHTML = '<option value="">(Default Order)</option>';
        orderDirSelect.value = 'asc';
        limitSelect.value = '50';
        resetConditions();
        document.getElementById('schemaStatus').textContent = 'Select a table';
        document.getElementById('statRows').textContent = '0';
        document.getElementById('statTime').textContent = '0 ms';
        document.getElementById('statCols').textContent = '0';
        document.getElementById('sqlDrawer').textContent = 'SELECT * FROM [Select Table Above]';
        document.getElementById('resultsTheadTr').innerHTML = '<th class="p-4 text-slate-400 italic font-normal">Select a table and click "Fetch Realtime Data" to query OpenDental.</th>';
        document.getElementById('resultsTbody').innerHTML = '<tr><td class="p-12 text-center text-slate-400"><div class="flex flex-col items-center justify-center gap-2"><i data-lucide="database-backup" class="w-10 h-10 text-slate-300 stroke-1"></i><p class="font-medium text-sm text-slate-500">No Realtime Query Executed Yet</p></div></td></tr>';
        exportCsvBtn.disabled = true;
        exportCsvBtn.classList.add('bg-slate-400', 'cursor-not-allowed');
        exportCsvBtn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
        syncToLocalBtn.disabled = true;
        syncToLocalBtn.classList.add('bg-slate-400', 'cursor-not-allowed');
        syncToLocalBtn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
        noticeBanner.classList.add('hidden');
        _queryResultData = null;
        lucide.createIcons();
      });

      function escHtml(s) {
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
      }
    });

    function switchOdTab(tab) {
      var queryView = document.getElementById('tabQueryBuilderView');
      var checkView = document.getElementById('tabSyncCheckpointsView');
      var dateView = document.getElementById('tabDateSyncView');

      var qBtn = document.getElementById('tabQueryBuilderBtn');
      var cBtn = document.getElementById('tabSyncCheckpointsBtn');
      var dBtn = document.getElementById('tabDateSyncBtn');

      queryView.classList.add('hidden');
      checkView.classList.add('hidden');
      dateView.classList.add('hidden');

      qBtn.className = 'flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-lg text-slate-600 hover:bg-slate-100 transition';
      cBtn.className = 'flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-lg text-slate-600 hover:bg-slate-100 transition';
      dBtn.className = 'flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-lg text-slate-600 hover:bg-slate-100 transition';

      if (tab === 'syncCheckpoints') {
        checkView.classList.remove('hidden');
        cBtn.className = 'flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 transition';
        loadSyncCheckpoints();
      } else if (tab === 'dateSync') {
        dateView.classList.remove('hidden');
        dBtn.className = 'flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-lg bg-amber-50 text-amber-700 border border-amber-200 transition';
        loadDateSyncRequests();
      } else {
        queryView.classList.remove('hidden');
        qBtn.className = 'flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 transition';
      }
      if (window.lucide) lucide.createIcons();
    }

    function loadDateSyncRequests() {
      var tbody = document.getElementById('syncRequestsTbody');
      if (!tbody) return;

      fetch('{{ url("/open-dental-explorer/sync-requests") }}')
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (!res.requests || !res.requests.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="p-8 text-center text-slate-400 text-sm">No server-to-server sync requests logged yet.</td></tr>';
            return;
          }

          var hasActiveJobs = false;

          var html = res.requests.map(function (req) {
            var statusBadge = '';
            if (req.status === 'pending') {
              hasActiveJobs = true;
              statusBadge = '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200"><span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span> Pending</span>';
            } else if (req.status === 'running') {
              hasActiveJobs = true;
              statusBadge = '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200"><svg class="animate-spin w-3 h-3 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Running</span>';
            } else if (req.status === 'completed') {
              statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">✓ Completed</span>';
            } else if (req.status === 'failed') {
              statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200" title="' + escHtml(req.error_message || '') + '">✕ Failed</span>';
            } else {
              statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">Cancelled</span>';
            }

            var windowStr = (req.start_date || 'All past') + ' → ' + (req.end_date || 'Today');
            var pruneStr = req.prune_deleted ? '<span class="text-amber-600 font-bold">Yes</span>' : '<span class="text-slate-400">No</span>';
            var startedStr = req.started_at ? new Date(req.started_at).toLocaleString() : '—';
            var completedStr = req.completed_at ? new Date(req.completed_at).toLocaleString() : '—';

            var cancelBtn = (req.status === 'pending' || req.status === 'running')
              ? '<button onclick="cancelSyncReq(' + req.id + ')" class="px-2.5 py-1 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 rounded border border-red-200 transition">Cancel</button>'
              : '—';

            var errHtml = req.error_message ? '<div class="text-[11px] text-red-600 mt-1 max-w-md truncate" title="' + escHtml(req.error_message) + '">' + escHtml(req.error_message) + '</div>' : '';

            return '<tr>' +
              '<td class="px-4 py-3 font-bold text-slate-800">#' + req.id + '</td>' +
              '<td class="px-4 py-3 font-extrabold text-slate-900">' + escHtml(req.module) + '</td>' +
              '<td class="px-4 py-3 text-xs text-slate-600">' + windowStr + '</td>' +
              '<td class="px-4 py-3 text-xs">' + pruneStr + '</td>' +
              '<td class="px-4 py-3">' + statusBadge + errHtml + '</td>' +
              '<td class="px-4 py-3 text-xs text-slate-500">' + startedStr + '</td>' +
              '<td class="px-4 py-3 text-xs text-slate-500">' + completedStr + '</td>' +
              '<td class="px-4 py-3 text-right">' + cancelBtn + '</td>' +
              '</tr>';
          }).join('');

          tbody.innerHTML = html;

          if (hasActiveJobs) {
            setTimeout(loadDateSyncRequests, 4000);
          }
        });
    }

    function submitDateSyncForm(e) {
      e.preventDefault();
      var btn = document.getElementById('submitSyncReqBtn');
      btn.disabled = true;
      btn.innerHTML = '<svg class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Launching...';

      fetch('{{ url("/open-dental-explorer/trigger-date-sync") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          module: document.getElementById('syncModuleSelect').value,
          start_date: document.getElementById('syncStartDate').value || null,
          end_date: document.getElementById('syncEndDate').value || null,
          prune_deleted: document.getElementById('syncPruneDeleted').checked
        })
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          btn.disabled = false;
          btn.innerHTML = '<i data-lucide="zap" class="w-4 h-4 fill-current"></i> Launch Server-to-Server Sync';
          if (window.lucide) lucide.createIcons();

          if (res.error) {
            alert('Error: ' + res.error);
            return;
          }
          alert(res.message || 'Server-to-server sync launched successfully.');
          loadDateSyncRequests();
        })
        .catch(function (err) {
          btn.disabled = false;
          btn.innerHTML = '<i data-lucide="zap" class="w-4 h-4 fill-current"></i> Launch Server-to-Server Sync';
          if (window.lucide) lucide.createIcons();
          alert('Failed to launch sync: ' + err.message);
        });
    }

    function cancelSyncReq(id) {
      if (!confirm('Are you sure you want to cancel sync request #' + id + '?')) return;

      fetch('{{ url("/open-dental-explorer/cancel-sync-request") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ id: id })
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.error) {
            alert('Error: ' + res.error);
            return;
          }
          loadDateSyncRequests();
        });
    }

    function loadSyncCheckpoints() {
      var tbody = document.getElementById('checkpointsTbody');
      if (!tbody) return;
      tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-slate-400 text-sm">Loading sync checkpoints...</td></tr>';

      fetch('{{ url("/open-dental-explorer/sync-checkpoints") }}')
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var logs = data.logs || [];
          if (!logs.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-slate-400 text-sm">No sync logs recorded in sync_logs table yet.</td></tr>';
            return;
          }
          var html = '';
          logs.forEach(function (log) {
            var statusBadge = '<span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full uppercase bg-slate-100 text-slate-700 border border-slate-200">' + escHtml(log.status) + '</span>';
            if (log.status === 'completed') statusBadge = '<span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">Completed</span>';
            if (log.status === 'running') statusBadge = '<span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full uppercase bg-blue-100 text-blue-800 border border-blue-200 animate-pulse">Running</span>';
            if (log.status === 'failed') statusBadge = '<span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full uppercase bg-rose-100 text-rose-800 border border-rose-200">Failed</span>';

            var escMod = escHtml(log.module);
            var dateVal = log.last_synced_at ? String(log.last_synced_at).replace(' ', 'T').slice(0, 16) : '';

            html += '<tr class="border-b border-slate-100 hover:bg-slate-50/70 transition text-xs" data-module="' + escMod + '">';
            html += '<td class="px-4 py-3 font-bold text-slate-900">' + escMod + '</td>';
            html += '<td class="px-4 py-3">' + statusBadge + '</td>';
            html += '<td class="px-4 py-3"><input type="datetime-local" class="cp-date bg-white border border-slate-300 rounded-lg px-2.5 py-1 text-xs text-slate-800 focus:ring-indigo-500 shadow-2xs" value="' + dateVal + '"></td>';
            html += '<td class="px-4 py-3"><input type="number" class="cp-pk w-28 bg-white border border-slate-300 rounded-lg px-2.5 py-1 text-xs text-slate-800 focus:ring-indigo-500 shadow-2xs" value="' + (log.last_primary_key || 0) + '"></td>';
            html += '<td class="px-4 py-3 font-mono font-semibold text-slate-700">' + Number(log.total_processed || 0).toLocaleString() + '</td>';
            html += '<td class="px-4 py-3 text-right"><button onclick="saveCheckpointRow(\'' + escMod + '\', this)" class="px-3.5 py-1.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition shadow-xs">Save / Reset Date</button></td>';
            html += '</tr>';
          });
          tbody.innerHTML = html;
          if (window.lucide) lucide.createIcons();
        })
        .catch(function (err) {
          tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-rose-500 text-sm">Failed to load sync checkpoints: ' + escHtml(err.message) + '</td></tr>';
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
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          module: module,
          last_synced_at: dateVal ? dateVal.replace('T', ' ') : null,
          last_primary_key: parseInt(pkVal, 10) || 0
        })
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          btn.disabled = false;
          btn.textContent = 'Save / Reset Date';
          if (res.error) {
            alert('Error: ' + res.error);
            return;
          }
          alert(res.message || 'Successfully updated sync checkpoint.');
          loadSyncCheckpoints();
        })
        .catch(function (err) {
          btn.disabled = false;
          btn.textContent = 'Save / Reset Date';
          alert('Failed to save checkpoint: ' + err.message);
        });
    }

    function resetAllCheckpoints() {
      if (!confirm('Are you sure you want to reset ALL sync checkpoints? This will cause all modules to restart sync from the beginning on their next execution, updating existing rows and inserting new ones without duplicates.')) return;

      fetch('{{ url("/open-dental-explorer/reset-sync-checkpoint") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          module: 'all',
          last_synced_at: null,
          last_primary_key: 0
        })
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.error) {
            alert('Error: ' + res.error);
            return;
          }
          alert(res.message || 'Successfully reset all sync checkpoints.');
          loadSyncCheckpoints();
        })
        .catch(function (err) {
          alert('Failed to reset all checkpoints: ' + err.message);
        });
    }
  </script>
</x-app-layout>
