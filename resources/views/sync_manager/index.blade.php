<x-app-layout>
  <div class="p-6 space-y-6 max-w-[1400px] mx-auto">
    
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
      <div class="flex items-center gap-3.5">
        <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-600">
          <i data-lucide="refresh-cw" class="w-6 h-6"></i>
        </div>
        <div>
          <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
            Data Synchronization
            <span id="syncEngineBadge" class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Ready
            </span>
          </h1>
          <p class="text-sm text-slate-500 mt-0.5">
            Sync your OpenDental records by selecting a data type and date range, or manage sync start dates per location.
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2.5">
        <button onclick="openResetModal()" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-xl transition focus:outline-none cursor-pointer">
          <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset Sync Start Date
        </button>
        <button onclick="loadSyncRequests()" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-700 bg-slate-100 rounded-xl hover:bg-slate-200 transition focus:outline-none cursor-pointer">
          <i data-lucide="rotate-cw" class="w-4 h-4"></i> Refresh
        </button>
      </div>
    </div>

    <!-- On-Demand Sync Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-6 space-y-5">
      <div class="border-b border-slate-100 pb-3">
        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
          <i data-lucide="play-circle" class="w-5 h-5 text-amber-500"></i>
          Run On-Demand Sync
        </h2>
      </div>

      <form id="syncManagerForm" onsubmit="submitSyncManagerForm(event)" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
          
          <!-- Module / Data Type -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Data Type</label>
            <select id="smModuleSelect" required class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-amber-500 p-3 font-semibold shadow-xs transition">
              @foreach($modules as $val => $label)
                <option value="{{ $val }}" @if($val === 'appointments') selected @endif>{{ $label }}</option>
              @endforeach
            </select>
          </div>

          <!-- Start Date -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Start Date</label>
            <input type="date" id="smStartDate" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-amber-500 p-3 font-medium shadow-xs transition">
          </div>

          <!-- End Date -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">End Date</label>
            <input type="date" id="smEndDate" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-amber-500 p-3 font-medium shadow-xs transition">
          </div>

        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-3 border-t border-slate-100">
          <label class="inline-flex items-center gap-2.5 cursor-pointer select-none text-xs font-semibold text-slate-700">
            <input type="checkbox" id="smPruneDeleted" class="w-4 h-4 text-amber-600 border-slate-300 rounded focus:ring-amber-500">
            <span>Clean up removed records from OpenDental</span>
          </label>

          <button type="submit" id="smSubmitBtn" class="inline-flex items-center justify-center gap-2 px-7 py-2.5 text-sm font-bold text-white bg-amber-600 rounded-xl hover:bg-amber-700 shadow-md shadow-amber-500/20 transition cursor-pointer">
            <i data-lucide="zap" class="w-4 h-4 fill-current"></i> Start Sync
          </button>
        </div>
      </form>
    </div>

    <!-- Recent Sync Activity Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
        <span class="text-xs font-extrabold uppercase tracking-wider text-slate-700 flex items-center gap-2">
          <i data-lucide="history" class="w-4 h-4 text-slate-600"></i> Sync Activity
        </span>
        <button onclick="loadSyncRequests()" class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition cursor-pointer">
          <i data-lucide="rotate-cw" class="w-3.5 h-3.5"></i> Refresh
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-slate-700">
          <thead class="text-xs uppercase bg-slate-100/80 text-slate-600 font-bold border-b border-slate-200">
            <tr>
              <th class="px-4 py-3.5">Job ID</th>
              <th class="px-4 py-3.5">Data Type</th>
              <th class="px-4 py-3.5">Date Range</th>
              <th class="px-4 py-3.5">Clean Deleted</th>
              <th class="px-4 py-3.5">Status</th>
              <th class="px-4 py-3.5">Started</th>
              <th class="px-4 py-3.5">Finished</th>
              <th class="px-4 py-3.5 text-right">Action</th>
            </tr>
          </thead>
          <tbody id="smRequestsTbody" class="divide-y divide-slate-100 font-medium">
            <tr>
              <td colspan="8" class="p-8 text-center text-slate-400 text-sm">Loading sync activity...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Sync Checkpoints & Watermarks Section (Collapsible) -->
    <details class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden group" open>
      <summary class="p-4 bg-slate-50 hover:bg-slate-100/80 transition cursor-pointer font-bold text-xs text-slate-700 uppercase tracking-wider flex items-center justify-between select-none">
        <span class="flex items-center gap-2">
          <i data-lucide="sliders" class="w-4 h-4 text-slate-500"></i> Location Sync Checkpoints & Watermarks
        </span>
        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-open:rotate-180 transition"></i>
      </summary>

      <div class="p-6 space-y-4 border-t border-slate-200">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2">
          <p class="text-xs text-slate-500">
            Where each module's background sync will continue from. You can reset the sync start date for any location or module below.
          </p>
          <div class="flex items-center gap-2">
            <button onclick="openResetModal()" class="px-3 py-1.5 text-xs font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-lg transition inline-flex items-center gap-1.5">
              <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Reset Start Date
            </button>
            <button onclick="loadSyncCheckpoints()" class="px-3 py-1.5 text-xs font-bold text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition">
              Refresh List
            </button>
          </div>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-xl">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-100 border-b border-slate-200 font-bold text-slate-700 uppercase tracking-wider">
                <th class="px-4 py-3">Module</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Sync Start / Watermark Date</th>
                <th class="px-4 py-3">Last Primary Key</th>
                <th class="px-4 py-3">Total Synced</th>
                <th class="px-4 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody id="smCheckpointsTbody" class="divide-y divide-slate-100 font-medium text-slate-800">
              <tr>
                <td colspan="6" class="p-6 text-center text-slate-400 text-sm">Loading watermarks...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </details>

  </div>

  <!-- Reset Sync Start Date Modal -->
  <div id="resetSyncModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
      <div class="p-5 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
          <i data-lucide="rotate-ccw" class="w-4 h-4 text-amber-600"></i>
          Reset Sync Start Date
        </h3>
        <button onclick="closeResetModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>

      <form id="resetSyncForm" onsubmit="submitResetSyncForm(event)" class="p-6 space-y-4">
        <!-- Office Location -->
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Office Location</label>
          <select id="resetOfficeSelect" required class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-amber-500 p-2.5 font-semibold shadow-xs">
            @foreach($offices as $office)
              <option value="{{ $office->id }}" @if($office->id == ($currentOffice->id ?? 1)) selected @endif>{{ $office->name }} (#{{ $office->id }})</option>
            @endforeach
          </select>
        </div>

        <!-- Target Module -->
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Target Module</label>
          <select id="resetModuleSelect" required class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-amber-500 p-2.5 font-semibold shadow-xs">
            <option value="all">⚡ All Modules (Complete Location Reset)</option>
            {{-- Keys are OpenDental table names, matching the checkpoint rows below. --}}
            @foreach($resetModules as $val => $label)
              <option value="{{ $val }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>

        <!-- Sync Start Date -->
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">New Sync Start Date</label>
          <input type="date" id="resetStartDateInput" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-amber-500 p-2.5 font-medium shadow-xs">
          <p class="text-[11px] text-slate-500 mt-1">Incremental sync will re-scan and update all records modified since this date.</p>
        </div>

        <!-- Full Initial Resync Toggle -->
        <div class="pt-2">
          <label class="inline-flex items-center gap-2 cursor-pointer select-none text-xs font-semibold text-slate-700">
            <input type="checkbox" id="resetBeginningToggle" onchange="toggleResetBeginning(this.checked)" class="w-4 h-4 text-amber-600 border-slate-300 rounded focus:ring-amber-500">
            <span>Reset completely from the beginning (Full Initial Scan from ID 0)</span>
          </label>
        </div>

        <div id="resetModalNotice" class="hidden p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800"></div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
          <button type="button" onclick="closeResetModal()" class="px-4 py-2 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
            Cancel
          </button>
          <button type="submit" id="resetSubmitBtn" class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-sm transition inline-flex items-center gap-1.5">
            <i data-lucide="check" class="w-4 h-4"></i> Apply Reset
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- JavaScript Engine -->
  <script>
    var resetModuleKeys = @json(array_keys($resetModules));

    var friendlyModuleNames = {
      'appointments': 'Appointments',
      'procedurelogs': 'Procedures',
      'patients': 'Patients',
      'adjustments': 'Adjustments',
      'payments': 'Payments',
      'claimprocs': 'Insurance Claims',
      'treatmentplans': 'Treatment Plans',
      'all': 'All Modules'
    };

    document.addEventListener('DOMContentLoaded', function () {
      loadSyncRequests();
      loadSyncCheckpoints();
    });

    function escHtml(s) {
      return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function loadSyncRequests() {
      var tbody = document.getElementById('smRequestsTbody');
      if (!tbody) return;

      fetch('{{ url("/sync-manager/requests") }}')
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (!res.requests || !res.requests.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="p-8 text-center text-slate-400 text-sm">No recent sync activity logged.</td></tr>';
            return;
          }

          var html = res.requests.map(function (req) {
            var statusBadge = '';
            if (req.status === 'pending') {
              statusBadge = '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200"><span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span> Queued</span>';
            } else if (req.status === 'running') {
              statusBadge = '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200"><svg class="animate-spin w-3 h-3 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> In Progress</span>';
            } else if (req.status === 'completed') {
              statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">✓ Completed</span>';
            } else if (req.status === 'failed') {
              statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200" title="' + escHtml(req.error_message) + '">✕ Failed</span>';
            } else {
              statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">Cancelled</span>';
            }

            var moduleLabel = friendlyModuleNames[req.module] || req.module;
            var windowStr = (req.start_date || 'All past') + ' → ' + (req.end_date || 'Today');
            var pruneStr = req.prune_deleted ? '<span class="text-amber-600 font-bold">Yes</span>' : '<span class="text-slate-400">No</span>';
            var startedStr = req.started_at ? new Date(req.started_at).toLocaleString([], { dateStyle: 'short', timeStyle: 'short' }) : '—';
            var completedStr = req.completed_at ? new Date(req.completed_at).toLocaleString([], { dateStyle: 'short', timeStyle: 'short' }) : '—';

            var cancelBtn = (req.status === 'pending' || req.status === 'running')
              ? '<button onclick="cancelSyncRequest(' + req.id + ')" class="px-2.5 py-1 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 rounded border border-red-200 transition cursor-pointer">Cancel</button>'
              : '—';

            var errHtml = req.error_message ? '<div class="text-[11px] text-red-600 mt-0.5 max-w-xs truncate" title="' + escHtml(req.error_message) + '">' + escHtml(req.error_message) + '</div>' : '';

            return '<tr>' +
              '<td class="px-4 py-3.5 font-bold text-slate-800">#' + req.id + '</td>' +
              '<td class="px-4 py-3.5 font-extrabold text-slate-900">' + escHtml(moduleLabel) + '</td>' +
              '<td class="px-4 py-3.5 text-xs text-slate-600">' + windowStr + '</td>' +
              '<td class="px-4 py-3.5 text-xs">' + pruneStr + '</td>' +
              '<td class="px-4 py-3.5">' + statusBadge + errHtml + '</td>' +
              '<td class="px-4 py-3.5 text-xs text-slate-500">' + startedStr + '</td>' +
              '<td class="px-4 py-3.5 text-xs text-slate-500">' + completedStr + '</td>' +
              '<td class="px-4 py-3.5 text-right">' + cancelBtn + '</td>' +
              '</tr>';
          }).join('');

          tbody.innerHTML = html;
        });
    }

    function submitSyncManagerForm(e) {
      e.preventDefault();
      var btn = document.getElementById('smSubmitBtn');
      btn.disabled = true;
      btn.innerHTML = '<svg class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Starting...';

      fetch('{{ url("/sync-manager/trigger") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          module: document.getElementById('smModuleSelect').value,
          start_date: document.getElementById('smStartDate').value || null,
          end_date: document.getElementById('smEndDate').value || null,
          prune_deleted: document.getElementById('smPruneDeleted').checked
        })
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          btn.disabled = false;
          btn.innerHTML = '<i data-lucide="zap" class="w-4 h-4 fill-current"></i> Start Sync';
          if (window.lucide) lucide.createIcons();

          if (res.error) {
            alert('Error: ' + res.error);
            return;
          }
          alert(res.message || 'Sync job started successfully.');
          loadSyncRequests();
        })
        .catch(function (err) {
          btn.disabled = false;
          btn.innerHTML = '<i data-lucide="zap" class="w-4 h-4 fill-current"></i> Start Sync';
          if (window.lucide) lucide.createIcons();
          alert('Failed to start sync: ' + err.message);
        });
    }

    function cancelSyncRequest(id) {
      if (!confirm('Are you sure you want to cancel sync job #' + id + '?')) return;

      fetch('{{ url("/sync-manager/cancel") }}', {
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
          loadSyncRequests();
        });
    }

    function loadSyncCheckpoints() {
      var tbody = document.getElementById('smCheckpointsTbody');
      if (!tbody) return;
      tbody.innerHTML = '<tr><td colspan="6" class="p-6 text-center text-slate-400 text-sm">Loading watermarks...</td></tr>';

      fetch('{{ url("/sync-manager/checkpoints") }}')
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var logs = data.logs || [];
          if (!logs.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="p-6 text-center text-slate-400 text-sm">No watermark logs recorded yet.</td></tr>';
            return;
          }
          var html = '';
          logs.forEach(function (log) {
            var statusBadge = '<span class="px-2 py-0.5 text-[11px] font-bold rounded-full uppercase bg-slate-100 text-slate-700 border border-slate-200">' + escHtml(log.status) + '</span>';
            if (log.status === 'completed') statusBadge = '<span class="px-2 py-0.5 text-[11px] font-bold rounded-full uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">Completed</span>';
            if (log.status === 'running') statusBadge = '<span class="px-2 py-0.5 text-[11px] font-bold rounded-full uppercase bg-blue-100 text-blue-800 border border-blue-200 animate-pulse">Running</span>';
            if (log.status === 'failed') statusBadge = '<span class="px-2 py-0.5 text-[11px] font-bold rounded-full uppercase bg-rose-100 text-rose-800 border border-rose-200">Failed</span>';

            var pkVal = log.last_primary_key || 0;
            var cleanModule = log.module.replace(/^office_\d+:/, '');
            var label = friendlyModuleNames[cleanModule] || cleanModule;
            var dateVal = log.last_synced_at ? log.last_synced_at.substring(0, 10) : '';

            html += '<tr class="hover:bg-slate-50 transition">';
            html += '<td class="px-4 py-3 font-bold text-slate-900">' + escHtml(label) + ' <span class="text-[10px] text-slate-400 font-mono font-normal">(' + escHtml(log.module) + ')</span></td>';
            html += '<td class="px-4 py-3">' + statusBadge + '</td>';
            html += '<td class="px-4 py-3 font-mono text-slate-700">' + escHtml(log.last_synced_at || '—') + '</td>';
            html += '<td class="px-4 py-3 font-mono text-slate-700">' + escHtml(String(pkVal)) + '</td>';
            html += '<td class="px-4 py-3 font-semibold text-slate-700">' + (log.total_processed || 0).toLocaleString() + '</td>';
            // Only scheduled-sync rows can be reset (not prune or date-range backfill rows).
            var canReset = resetModuleKeys.indexOf(cleanModule) !== -1;
            html += '<td class="px-4 py-3 text-right">' + (canReset
              ? '<button data-module="' + escHtml(cleanModule) + '" data-date="' + escHtml(dateVal) + '" data-office="' + Number(log.office_id || 0) + '" onclick="openResetModal(this.dataset.module, this.dataset.date, this.dataset.office)" class="px-2.5 py-1 text-xs font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-lg transition cursor-pointer">Reset Date</button>'
              : '<span class="text-slate-300 text-xs">—</span>') + '</td>';
            html += '</tr>';
          });
          tbody.innerHTML = html;
        });
    }

    function openResetModal(moduleKey, currentDate, officeId) {
      if (moduleKey) {
        document.getElementById('resetModuleSelect').value = moduleKey;
      }
      if (currentDate) {
        document.getElementById('resetStartDateInput').value = currentDate;
      }
      if (officeId) {
        document.getElementById('resetOfficeSelect').value = officeId;
      }
      document.getElementById('resetBeginningToggle').checked = false;
      document.getElementById('resetStartDateInput').disabled = false;
      document.getElementById('resetModalNotice').classList.add('hidden');
      document.getElementById('resetSyncModal').classList.remove('hidden');
      if (window.lucide) lucide.createIcons();
    }

    function closeResetModal() {
      document.getElementById('resetSyncModal').classList.add('hidden');
    }

    function toggleResetBeginning(checked) {
      var dateInput = document.getElementById('resetStartDateInput');
      dateInput.disabled = checked;
      if (checked) {
        dateInput.value = '';
      }
    }

    function submitResetSyncForm(e) {
      e.preventDefault();
      var btn = document.getElementById('resetSubmitBtn');
      var notice = document.getElementById('resetModalNotice');
      notice.classList.add('hidden');

      var officeId = document.getElementById('resetOfficeSelect').value;
      var module = document.getElementById('resetModuleSelect').value;
      var isBeginning = document.getElementById('resetBeginningToggle').checked;
      var startDate = isBeginning ? null : (document.getElementById('resetStartDateInput').value || null);

      btn.disabled = true;
      btn.innerHTML = '<svg class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Applying...';

      fetch('{{ url("/sync-manager/reset-checkpoint") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          office_id: officeId,
          module: module,
          start_date: startDate,
          last_primary_key: 0
        })
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          btn.disabled = false;
          btn.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i> Apply Reset';
          if (window.lucide) lucide.createIcons();

          if (res.error || res.errors) {
            notice.innerText = 'Error: ' + (res.error || res.message);
            notice.classList.remove('hidden');
            return;
          }

          alert(res.message || 'Sync start date reset successfully.');
          closeResetModal();
          loadSyncCheckpoints();
        })
        .catch(function (err) {
          btn.disabled = false;
          btn.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i> Apply Reset';
          if (window.lucide) lucide.createIcons();
          notice.innerText = 'Network error: ' + err.message;
          notice.classList.remove('hidden');
        });
    }

  </script>
</x-app-layout>
