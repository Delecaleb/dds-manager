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
            Sync your OpenDental records by selecting a data type and date range.
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2.5">
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

    <!-- Advanced Settings (Collapsible) -->
    <details class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden group">
      <summary class="p-4 bg-slate-50 hover:bg-slate-100/80 transition cursor-pointer font-bold text-xs text-slate-700 uppercase tracking-wider flex items-center justify-between select-none">
        <span class="flex items-center gap-2">
          <i data-lucide="sliders" class="w-4 h-4 text-slate-500"></i> Advanced Watermarks & Checkpoints
        </span>
        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-open:rotate-180 transition"></i>
      </summary>

      <div class="p-6 space-y-4 border-t border-slate-200">
        <div class="flex items-center justify-between pb-2">
          <p class="text-xs text-slate-500">View or adjust low-level timestamp watermarks per module.</p>
          <div class="flex items-center gap-2">
            <button onclick="loadSyncCheckpoints()" class="px-3 py-1.5 text-xs font-bold text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition">Refresh List</button>
            <button onclick="resetAllCheckpoints()" class="px-3 py-1.5 text-xs font-bold text-rose-700 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-100 transition">Reset All Checkpoints</button>
          </div>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-xl">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-100 border-b border-slate-200 font-bold text-slate-700 uppercase tracking-wider">
                <th class="px-4 py-3">Module</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Last Synced Timestamp</th>
                <th class="px-4 py-3">Last Primary Key</th>
                <th class="px-4 py-3">Total Synced</th>
                <th class="px-4 py-3 text-right">Action</th>
              </tr>
            </thead>
            <tbody id="smCheckpointsTbody" class="divide-y divide-slate-100 font-medium text-slate-800">
              <tr>
                <td colspan="6" class="p-6 text-center text-slate-400 text-sm">Expand to inspect watermarks.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </details>

  </div>

  <!-- JavaScript Engine -->
  <script>
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

          var hasActiveJobs = false;

          var html = res.requests.map(function (req) {
            var statusBadge = '';
            if (req.status === 'pending') {
              hasActiveJobs = true;
              statusBadge = '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200"><span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span> Queued</span>';
            } else if (req.status === 'running') {
              hasActiveJobs = true;
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

            var dtVal = log.last_synced_at ? escHtml(log.last_synced_at).replace(' ', 'T') : '';
            var pkVal = log.last_primary_key || 0;
            var label = friendlyModuleNames[log.module] || log.module;

            html += '<tr class="hover:bg-slate-50 transition">';
            html += '<td class="px-4 py-3 font-bold text-slate-900">' + escHtml(label) + '</td>';
            html += '<td class="px-4 py-3">' + statusBadge + '</td>';
            html += '<td class="px-4 py-3"><input type="datetime-local" id="dt_' + escHtml(log.module) + '" value="' + dtVal + '" class="bg-slate-50 border border-slate-300 rounded-lg px-2 py-1 text-xs font-mono text-slate-800 focus:ring-indigo-500"></td>';
            html += '<td class="px-4 py-3"><input type="number" id="pk_' + escHtml(log.module) + '" value="' + pkVal + '" class="w-28 bg-slate-50 border border-slate-300 rounded-lg px-2 py-1 text-xs font-mono text-slate-800 focus:ring-indigo-500"></td>';
            html += '<td class="px-4 py-3 font-semibold text-slate-700">' + (log.total_processed || 0).toLocaleString() + '</td>';
            html += '<td class="px-4 py-3 text-right"><button onclick="saveCheckpoint(\'' + escHtml(log.module) + '\')" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs transition cursor-pointer">Save</button></td>';
            html += '</tr>';
          });
          tbody.innerHTML = html;
        });
    }

    function saveCheckpoint(mod) {
      var dateVal = document.getElementById('dt_' + mod).value;
      var pkVal = document.getElementById('pk_' + mod).value;

      fetch('{{ url("/sync-manager/reset-checkpoint") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          module: mod,
          last_synced_at: dateVal ? dateVal.replace('T', ' ') : null,
          last_primary_key: parseInt(pkVal, 10) || 0
        })
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.error) {
            alert('Error: ' + res.error);
            return;
          }
          alert(res.message || 'Updated checkpoint.');
          loadSyncCheckpoints();
        });
    }

    function resetAllCheckpoints() {
      if (!confirm('Are you sure you want to reset all sync checkpoints?')) return;

      fetch('{{ url("/sync-manager/reset-checkpoint") }}', {
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
          alert(res.message || 'Reset all sync checkpoints.');
          loadSyncCheckpoints();
        });
    }
  </script>
</x-app-layout>
