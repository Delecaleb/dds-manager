@php
    $allOfficesForModal = \App\Models\Office::orderBy('id')->get();
    $activeOfficeIdForModal = \App\Models\Office::getActiveOfficeId();
@endphp

<!-- Global Office Sync Report Modal -->
<div id="global-sync-report-modal" class="fixed inset-0 z-[400] hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-all duration-200" onclick="if(event.target === this) closeGlobalSyncReportModal()">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-4xl max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/90 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-blue-100 text-blue-700 rounded-xl shadow-xs">
                    <i data-lucide="activity" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h3 class="font-bold text-slate-900 text-lg" id="gsr-modal-title">Sync Report</h3>
                        <span id="gsr-office-badge" class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">Active</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">Live Open Dental sync telemetry, heartbeat freshness & record counts</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <!-- Location Selector inside modal -->
                <div class="flex items-center gap-1.5 px-3 py-1.5 bg-white rounded-lg border border-slate-200 text-xs shadow-xs">
                    <i data-lucide="building" class="w-3.5 h-3.5 text-slate-400"></i>
                    <select id="gsr-office-switcher" onchange="loadGlobalSyncReport(this.value)" class="bg-transparent font-medium text-slate-700 text-xs focus:outline-none cursor-pointer">
                        @foreach($allOfficesForModal as $off)
                            <option value="{{ $off->id }}" {{ $off->id == $activeOfficeIdForModal ? 'selected' : '' }}>{{ $off->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button onclick="closeGlobalSyncReportModal()" class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 focus:outline-none transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        <!-- Summary Status Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 p-4 bg-slate-100/70 border-b border-slate-200 shrink-0">
            <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-xs flex items-center gap-3">
                <span class="text-xl">🟢</span>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Running</div>
                    <div class="text-lg font-bold text-emerald-700" id="gsr-stat-running">-</div>
                </div>
            </div>
            <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-xs flex items-center gap-3">
                <span class="text-xl">🟡</span>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Slow</div>
                    <div class="text-lg font-bold text-amber-700" id="gsr-stat-slow">-</div>
                </div>
            </div>
            <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-xs flex items-center gap-3">
                <span class="text-xl">🔴</span>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Stuck</div>
                    <div class="text-lg font-bold text-rose-700" id="gsr-stat-stuck">-</div>
                </div>
            </div>
            <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-xs flex items-center gap-3">
                <span class="text-xl">⚪</span>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Idle</div>
                    <div class="text-lg font-bold text-slate-700" id="gsr-stat-idle">-</div>
                </div>
            </div>
            <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-xs flex items-center gap-3 col-span-2 sm:col-span-1">
                <span class="text-xl">📦</span>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Records</div>
                    <div class="text-lg font-bold text-blue-700 font-mono" id="gsr-stat-records">-</div>
                </div>
            </div>
        </div>

        <!-- Filter / Search toolbar -->
        <div class="px-6 py-2.5 bg-white border-b border-slate-100 flex items-center justify-between gap-4 shrink-0">
            <div class="relative w-64 sm:w-80">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" id="gsr-search-input" onkeyup="filterGlobalSyncReportTable()" placeholder="Filter sync table (e.g. Patients, Payments)..."
                    class="w-full pl-9 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
            </div>
            <div class="flex items-center gap-3 text-xs text-slate-500">
                <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                    <input type="checkbox" id="gsr-autorefresh-toggle" checked onchange="toggleGlobalAutoRefresh(this.checked)" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                    <span class="font-medium text-slate-600">Auto-refresh (4s)</span>
                </label>
                <button onclick="refreshGlobalSyncReportNow()" class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-md font-medium transition-colors" title="Refresh Now">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5" id="gsr-refresh-icon"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Table Body Container -->
        <div class="flex-1 overflow-y-auto chunk-scrollbar p-6 bg-slate-50/50">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-left text-sm" id="gsr-table">
                    <thead class="bg-slate-100 text-slate-700 font-bold text-xs uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Sync</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5 text-right">Last heartbeat</th>
                            <th class="px-6 py-3.5 text-right">Records</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="gsr-table-body" class="divide-y divide-slate-100">
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i data-lucide="loader-2" class="w-6 h-6 animate-spin text-blue-600"></i>
                                    <span class="text-xs font-medium">Fetching sync report data...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-3.5 border-t border-slate-200 bg-white flex items-center justify-between shrink-0">
            <div class="text-xs text-slate-500" id="gsr-last-updated">
                Telemetry ready
            </div>
            <div class="flex items-center gap-3">
                <button onclick="triggerGlobalOfficeFullSyncModal()" id="gsr-sync-all-btn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-lg shadow-sm transition-colors inline-flex items-center gap-1.5">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Sync All Modules
                </button>
                <button onclick="closeGlobalSyncReportModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs rounded-lg transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const syncReportUrlPattern = "{{ route('offices.sync-report', ['office' => ':id']) }}";
        const syncModuleUrlPattern = "{{ route('offices.sync-module', ['office' => ':id']) }}";
        const syncOfficeUrlPattern = "{{ route('offices.sync', ['office' => ':id']) }}";
        const defaultOfficeId = {{ $activeOfficeIdForModal ?? ($allOfficesForModal->first()->id ?? 1) }};

        let currentOfficeId = defaultOfficeId;
        let timer = null;
        let cachedItems = [];

        window.openGlobalSyncReport = function(officeId, officeName) {
            currentOfficeId = officeId || defaultOfficeId;
            const switcher = document.getElementById('gsr-office-switcher');
            if (switcher) switcher.value = currentOfficeId;

            const modal = document.getElementById('global-sync-report-modal');
            if (modal) {
                modal.classList.remove('hidden');
                loadGlobalSyncReport(currentOfficeId);
                startAutoRefresh();
            }
        };

        window.closeGlobalSyncReportModal = function() {
            const modal = document.getElementById('global-sync-report-modal');
            if (modal) modal.classList.add('hidden');
            stopAutoRefresh();
        };

        function startAutoRefresh() {
            stopAutoRefresh();
            const autoToggle = document.getElementById('gsr-autorefresh-toggle');
            if (autoToggle && autoToggle.checked) {
                timer = setInterval(() => {
                    const modal = document.getElementById('global-sync-report-modal');
                    if (currentOfficeId && modal && !modal.classList.contains('hidden')) {
                        loadGlobalSyncReport(currentOfficeId, true);
                    }
                }, 4000);
            }
        }

        function stopAutoRefresh() {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        }

        window.toggleGlobalAutoRefresh = function(enabled) {
            if (enabled) {
                startAutoRefresh();
            } else {
                stopAutoRefresh();
            }
        };

        window.refreshGlobalSyncReportNow = function() {
            const icon = document.getElementById('gsr-refresh-icon');
            if (icon) icon.classList.add('animate-spin');
            if (currentOfficeId) {
                loadGlobalSyncReport(currentOfficeId, false, () => {
                    if (icon) icon.classList.remove('animate-spin');
                });
            }
        };

        window.loadGlobalSyncReport = function(officeId, isBackground = false, callback = null) {
            currentOfficeId = officeId;
            const url = syncReportUrlPattern.replace(':id', officeId);

            if (!isBackground && cachedItems.length === 0) {
                const tbody = document.getElementById('gsr-table-body');
                if (tbody) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i data-lucide="loader-2" class="w-6 h-6 animate-spin text-blue-600"></i>
                                    <span class="text-xs font-medium">Fetching sync report data...</span>
                                </div>
                            </td>
                        </tr>
                    `;
                    if (window.lucide) lucide.createIcons();
                }
            }

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.json();
            })
            .then(data => {
                renderGlobalSyncReport(data);
                if (callback) callback();
            })
            .catch(err => {
                console.error('Failed to load sync report:', err);
                if (!isBackground) {
                    const tbody = document.getElementById('gsr-table-body');
                    if (tbody) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-rose-600 text-xs">
                                    Failed to fetch sync telemetry: ${err.message}
                                </td>
                            </tr>
                        `;
                    }
                }
                if (callback) callback();
            });
        };

        function renderGlobalSyncReport(data) {
            const office = data.office;
            const summary = data.summary;
            const items = data.items;

            cachedItems = items;

            const title = document.getElementById('gsr-modal-title');
            if (title) title.innerText = `Sync Report: ${office.name}`;

            const badge = document.getElementById('gsr-office-badge');
            if (badge) {
                if (office.is_active) {
                    badge.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200';
                    badge.innerText = 'Active Location';
                } else {
                    badge.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200';
                    badge.innerText = 'Disabled';
                }
            }

            const statRun = document.getElementById('gsr-stat-running');
            const statSlow = document.getElementById('gsr-stat-slow');
            const statStuck = document.getElementById('gsr-stat-stuck');
            const statIdle = document.getElementById('gsr-stat-idle');
            const statRecs = document.getElementById('gsr-stat-records');

            if (statRun) statRun.innerText = summary.running || 0;
            if (statSlow) statSlow.innerText = summary.slow || 0;
            if (statStuck) statStuck.innerText = summary.stuck || 0;
            if (statIdle) statIdle.innerText = summary.idle || 0;
            if (statRecs) statRecs.innerText = Number(summary.total_records || 0).toLocaleString();

            const lastUp = document.getElementById('gsr-last-updated');
            if (lastUp) {
                const d = new Date();
                lastUp.innerText = `Last updated: ${d.toLocaleTimeString()}`;
            }

            renderGlobalSyncTableRows(items);
        }

        function renderGlobalSyncTableRows(items) {
            const tbody = document.getElementById('gsr-table-body');
            if (!tbody) return;

            const searchInput = document.getElementById('gsr-search-input');
            const searchVal = (searchInput?.value || '').toLowerCase().trim();

            const filtered = items.filter(item => {
                if (!searchVal) return true;
                return item.sync.toLowerCase().includes(searchVal) ||
                       item.status.toLowerCase().includes(searchVal) ||
                       item.last_heartbeat.toLowerCase().includes(searchVal);
            });

            if (filtered.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-400 text-xs">
                            No sync modules match the search filter.
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = filtered.map(item => {
                return `
                    <tr class="hover:bg-slate-50/90 transition-colors">
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            <div class="flex items-center gap-2.5 font-medium text-slate-900">
                                <i data-lucide="${item.icon || 'database'}" class="w-4 h-4 text-blue-600 shrink-0"></i>
                                <span>${item.sync}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border ${item.status_badge}">
                                ${item.status}
                            </span>
                            ${item.last_error ? `
                                <span class="inline-block ml-1 text-rose-500 cursor-pointer" title="Error: ${escapeHtml(item.last_error)}">
                                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 inline"></i>
                                </span>
                            ` : ''}
                        </td>
                        <td class="px-6 py-3.5 text-right whitespace-nowrap">
                            <span class="font-mono text-xs font-medium text-slate-700" title="${item.last_heartbeat_timestamp || ''}">
                                ${item.last_heartbeat}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-right whitespace-nowrap">
                            <span class="font-mono text-xs font-bold text-slate-800">
                                ${item.records_formatted}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-right whitespace-nowrap">
                            ${item.can_sync ? `
                                <button onclick="syncGlobalSingleModule('${item.key}', '${escapeHtml(item.sync)}')"
                                    class="px-2.5 py-1 bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 text-xs font-medium rounded-md transition-colors border border-slate-200 inline-flex items-center gap-1"
                                    id="btn-gsr-sync-${item.key}">
                                    <i data-lucide="refresh-cw" class="w-3 h-3"></i> Sync
                                </button>
                            ` : `
                                <span class="text-[11px] text-slate-400 italic">Auto</span>
                            `}
                        </td>
                    </tr>
                `;
            }).join('');

            if (window.lucide) lucide.createIcons();
        }

        window.filterGlobalSyncReportTable = function() {
            renderGlobalSyncTableRows(cachedItems);
        };

        window.syncGlobalSingleModule = function(moduleKey, moduleLabel) {
            if (!currentOfficeId) return;

            const btn = document.getElementById(`btn-gsr-sync-${moduleKey}`);
            let origContent = '';
            if (btn) {
                origContent = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = `<i data-lucide="loader-2" class="w-3 h-3 animate-spin"></i> Syncing...`;
                if (window.lucide) lucide.createIcons();
            }

            fetch(syncModuleUrlPattern.replace(':id', currentOfficeId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ module: moduleKey })
            })
            .then(res => res.json())
            .then(data => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = origContent;
                    if (window.lucide) lucide.createIcons();
                }

                if (data.success) {
                    loadGlobalSyncReport(currentOfficeId, true);
                } else {
                    alert(data.error || `Sync failed for ${moduleLabel}.`);
                }
            })
            .catch(err => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = origContent;
                    if (window.lucide) lucide.createIcons();
                }
                alert(`Sync error: ${err.message}`);
            });
        };

        window.triggerGlobalOfficeFullSyncModal = function() {
            if (!currentOfficeId) return;

            const btn = document.getElementById('gsr-sync-all-btn');
            const origHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i> Syncing All...`;
            if (window.lucide) lucide.createIcons();

            fetch(syncOfficeUrlPattern.replace(':id', currentOfficeId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = origHtml;
                if (window.lucide) lucide.createIcons();

                if (data.success) {
                    loadGlobalSyncReport(currentOfficeId, true);
                } else {
                    alert(data.error || 'Sync failed.');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = origHtml;
                if (window.lucide) lucide.createIcons();
                alert(`Sync error: ${err.message}`);
            });
        };

        function escapeHtml(text) {
            if (!text) return '';
            return String(text).replace(/[&<>"']/g, function(m) {
                return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[m];
            });
        }
    })();
</script>
