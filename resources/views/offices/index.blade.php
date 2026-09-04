<x-app-layout>
    <div class="p-6 max-w-7xl mx-auto space-y-6">

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div>
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-blue-50 text-blue-600 rounded-lg">
                        <i data-lucide="building-2" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Office Locations</h1>
                        <p class="text-sm text-slate-500">Manage multiple dental office locations, API keys, and location-scoped data sync.</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="openSyncReportModal({{ $activeOfficeId ?? ($offices->first()->id ?? 1) }}, '{{ addslashes($offices->firstWhere('id', $activeOfficeId)->name ?? ($offices->first()->name ?? 'Office')) }}')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm rounded-lg shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    <i data-lucide="activity" class="w-4 h-4"></i> Live Sync Report
                </button>
                <button onclick="openOfficeModal()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-lg shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Add Office Location
                </button>
            </div>
        </div>

        <!-- Feedback Alert Container -->
        <div id="alert-container">
            @if(session('status'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center gap-3 text-emerald-800 text-sm">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-lg flex items-center gap-3 text-rose-800 text-sm">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600 shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
        </div>

        <!-- Offices Table Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="table" class="w-4 h-4 text-slate-400"></i>
                    <h2 class="font-semibold text-slate-800">All Configured Offices</h2>
                </div>
                <span class="text-xs bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full font-medium">
                    {{ count($offices) }} Location(s)
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 uppercase font-semibold text-xs border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5">Office Name</th>
                            <th class="px-6 py-3.5">Developer Key</th>
                            <th class="px-6 py-3.5">Customer Key</th>
                            <th class="px-6 py-3.5">API Endpoint</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($offices as $office)
                            <tr class="hover:bg-slate-50/80 transition-colors cursor-pointer {{ $office->id == $activeOfficeId ? 'bg-blue-50/40' : '' }}"
                                onclick="if(!event.target.closest('button, form, a, input, select')) openSyncReportModal({{ $office->id }}, '{{ addslashes($office->name) }}')">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        @if($office->id == $activeOfficeId)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Active
                                            </span>
                                        @elseif($office->is_active)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                                Enabled
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-400">
                                                Disabled
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-900">
                                    <div class="flex items-center gap-2 group">
                                        <i data-lucide="building" class="w-4 h-4 text-blue-600"></i>
                                        <span class="group-hover:text-blue-600 group-hover:underline transition-all">{{ $office->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-600">
                                    @if($office->developer_key)
                                        <span class="bg-slate-100 px-2 py-1 rounded text-slate-700" title="{{ $office->developer_key }}">
                                            {{ Str::limit($office->developer_key, 16, '...') }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic">Not set (Uses default)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-600">
                                    @if($office->customer_key)
                                        <span class="bg-slate-100 px-2 py-1 rounded text-slate-700" title="{{ $office->customer_key }}">
                                            {{ Str::limit($office->customer_key, 16, '...') }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic">Not set (Uses default)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs font-mono text-slate-500">
                                    {{ $office->api_url ?: config('opendental.url') }}
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2" onclick="event.stopPropagation()">
                                        <!-- Sync Report Button -->
                                        <button onclick="openSyncReportModal({{ $office->id }}, '{{ addslashes($office->name) }}')"
                                            class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-lg transition-colors inline-flex items-center gap-1.5 border border-emerald-200"
                                            title="View Live Sync Report">
                                            <i data-lucide="activity" class="w-3.5 h-3.5 text-emerald-600"></i> Sync Report
                                        </button>

                                        <!-- Switch Active Button -->
                                        @if($office->id != $activeOfficeId)
                                            <form method="POST" action="{{ route('offices.switch') }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="office_id" value="{{ $office->id }}">
                                                <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-lg transition-colors inline-flex items-center gap-1">
                                                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Select
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Sync Button -->
                                        <button onclick="triggerSync({{ $office->id }}, '{{ addslashes($office->name) }}')"
                                            class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-medium rounded-lg transition-colors inline-flex items-center gap-1">
                                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Sync Data
                                        </button>

                                        <!-- Edit Button -->
                                        <button onclick="editOffice({{ json_encode($office) }})"
                                            class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-slate-100 rounded-lg transition-colors"
                                            title="Edit Office">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>

                                        <!-- Delete Button -->
                                        @if(count($offices) > 1)
                                            <form method="POST" action="{{ route('offices.destroy', $office->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this office?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete Office">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                    No office locations configured.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Office Edit/Create Modal -->
    <div id="office-modal" class="fixed inset-0 z-[300] hidden flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden transform transition-all">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 id="modal-title" class="font-bold text-slate-900 text-base">Add Office Location</h3>
                <button onclick="closeOfficeModal()" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 focus:outline-none">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Error Box -->
            <div id="modal-error-box" class="hidden mx-6 mt-4 p-3 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-700"></div>

            <form id="office-form" method="POST" action="{{ route('offices.store') }}" class="p-6 space-y-4">
                @csrf
                <input type="hidden" id="form-method" name="_method" value="POST">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Office Name *</label>
                    <input type="text" id="office-name" name="name" required placeholder="e.g. Downtown Branch"
                        class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Developer Key</label>
                    <input type="text" id="office-dev-key" name="developer_key" placeholder="OpenDental Developer API Key"
                        class="w-full px-3.5 py-2 text-sm font-mono rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Customer Key</label>
                    <input type="text" id="office-cust-key" name="customer_key" placeholder="OpenDental Customer API Key"
                        class="w-full px-3.5 py-2 text-sm font-mono rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">API Base URL (Optional)</label>
                    <input type="url" id="office-api-url" name="api_url" placeholder="https://api.opendental.com/api/v1"
                        class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" id="office-active" name="is_active" value="1" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="office-active" class="text-sm font-medium text-slate-700">Location Active</label>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeOfficeModal()" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" id="save-btn" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-lg shadow-sm transition-colors inline-flex items-center gap-2">
                        <span>Save Office Location</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Office Sync Report Modal -->
    <div id="sync-report-modal" class="fixed inset-0 z-[350] hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-all duration-200">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-4xl max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/90 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-blue-100 text-blue-700 rounded-xl shadow-xs">
                        <i data-lucide="activity" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h3 class="font-bold text-slate-900 text-lg" id="sr-modal-title">Sync Report</h3>
                            <span id="sr-office-badge" class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">Active</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Live Open Dental sync telemetry, heartbeat freshness & record counts</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Location Selector inside modal -->
                    <div class="flex items-center gap-1.5 px-3 py-1.5 bg-white rounded-lg border border-slate-200 text-xs shadow-xs">
                        <i data-lucide="building" class="w-3.5 h-3.5 text-slate-400"></i>
                        <select id="sr-office-switcher" onchange="loadSyncReport(this.value)" class="bg-transparent font-medium text-slate-700 text-xs focus:outline-none cursor-pointer">
                            @foreach($offices as $off)
                                <option value="{{ $off->id }}">{{ $off->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button onclick="closeSyncReportModal()" class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 focus:outline-none transition-colors">
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
                        <div class="text-lg font-bold text-emerald-700" id="sr-stat-running">-</div>
                    </div>
                </div>
                <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-xs flex items-center gap-3">
                    <span class="text-xl">🟡</span>
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Slow</div>
                        <div class="text-lg font-bold text-amber-700" id="sr-stat-slow">-</div>
                    </div>
                </div>
                <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-xs flex items-center gap-3">
                    <span class="text-xl">🔴</span>
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Stuck</div>
                        <div class="text-lg font-bold text-rose-700" id="sr-stat-stuck">-</div>
                    </div>
                </div>
                <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-xs flex items-center gap-3">
                    <span class="text-xl">⚪</span>
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Idle</div>
                        <div class="text-lg font-bold text-slate-700" id="sr-stat-idle">-</div>
                    </div>
                </div>
                <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-xs flex items-center gap-3 col-span-2 sm:col-span-1">
                    <span class="text-xl">📦</span>
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Records</div>
                        <div class="text-lg font-bold text-blue-700 font-mono" id="sr-stat-records">-</div>
                    </div>
                </div>
            </div>

            <!-- Filter / Search toolbar -->
            <div class="px-6 py-2.5 bg-white border-b border-slate-100 flex items-center justify-between gap-4 shrink-0">
                <div class="relative w-64 sm:w-80">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" id="sr-search-input" onkeyup="filterSyncReportTable()" placeholder="Filter sync table (e.g. Patients, Payments)..."
                        class="w-full pl-9 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-500">
                    <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                        <input type="checkbox" id="sr-autorefresh-toggle" checked onchange="toggleAutoRefresh(this.checked)" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                        <span class="font-medium text-slate-600">Auto-refresh (4s)</span>
                    </label>
                    <button onclick="refreshSyncReportNow()" class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-md font-medium transition-colors" title="Refresh Now">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5" id="sr-refresh-icon"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- Table Body Container -->
            <div class="flex-1 overflow-y-auto chunk-scrollbar p-6 bg-slate-50/50">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <table class="w-full text-left text-sm" id="sr-table">
                        <thead class="bg-slate-100 text-slate-700 font-bold text-xs uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3.5">Sync</th>
                                <th class="px-6 py-3.5">Status</th>
                                <th class="px-6 py-3.5 text-right">Last heartbeat</th>
                                <th class="px-6 py-3.5 text-right">Records</th>
                                <th class="px-6 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sr-table-body" class="divide-y divide-slate-100">
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
                <div class="text-xs text-slate-500" id="sr-last-updated">
                    Telemetry ready
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="triggerOfficeFullSyncModal()" id="sr-sync-all-btn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-lg shadow-sm transition-colors inline-flex items-center gap-1.5">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Sync All Modules
                    </button>
                    <button onclick="closeSyncReportModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs rounded-lg transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openOfficeModal() {
            document.getElementById('modal-title').innerText = 'Add Office Location';
            document.getElementById('office-form').action = "{{ route('offices.store') }}";
            document.getElementById('form-method').value = 'POST';
            document.getElementById('office-name').value = '';
            document.getElementById('office-dev-key').value = '';
            document.getElementById('office-cust-key').value = '';
            document.getElementById('office-api-url').value = '';
            document.getElementById('office-active').checked = true;
            hideModalError();
            document.getElementById('office-modal').classList.remove('hidden');
        }

        const updateUrlPattern = "{{ route('offices.update', ['office' => ':id']) }}";
        const syncUrlPattern = "{{ route('offices.sync', ['office' => ':id']) }}";
        const syncReportUrlPattern = "{{ route('offices.sync-report', ['office' => ':id']) }}";
        const syncModuleUrlPattern = "{{ route('offices.sync-module', ['office' => ':id']) }}";

        function editOffice(office) {
            document.getElementById('modal-title').innerText = 'Edit Office Location';
            document.getElementById('office-form').action = updateUrlPattern.replace(':id', office.id);
            document.getElementById('form-method').value = 'PUT';
            document.getElementById('office-name').value = office.name || '';
            document.getElementById('office-dev-key').value = office.developer_key || '';
            document.getElementById('office-cust-key').value = office.customer_key || '';
            document.getElementById('office-api-url').value = office.api_url || '';
            document.getElementById('office-active').checked = office.is_active ? true : false;
            hideModalError();
            document.getElementById('office-modal').classList.remove('hidden');
        }

        function closeOfficeModal() {
            document.getElementById('office-modal').classList.add('hidden');
            hideModalError();
        }

        function showModalError(msg) {
            const errBox = document.getElementById('modal-error-box');
            errBox.innerText = msg;
            errBox.classList.remove('hidden');
        }

        function hideModalError() {
            const errBox = document.getElementById('modal-error-box');
            errBox.innerText = '';
            errBox.classList.add('hidden');
        }

        function showAlert(msg, isError = false) {
            const container = document.getElementById('alert-container');
            const bgClass = isError ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-emerald-50 border-emerald-200 text-emerald-800';
            const iconClass = isError ? 'text-rose-600' : 'text-emerald-600';
            const icon = isError ? 'alert-triangle' : 'check-circle-2';

            container.innerHTML = `
                <div class="p-4 ${bgClass} border rounded-lg flex items-center gap-3 text-sm">
                    <i data-lucide="${icon}" class="w-5 h-5 ${iconClass} shrink-0"></i>
                    <span>${msg}</span>
                </div>
            `;
            lucide.createIcons();
        }

        // AJAX Form Handler for Creating & Editing Offices
        document.getElementById('office-form').addEventListener('submit', function(e) {
            e.preventDefault();
            hideModalError();

            const form = this;
            const actionUrl = form.action;
            const saveBtn = document.getElementById('save-btn');

            saveBtn.disabled = true;
            saveBtn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Saving...`;
            lucide.createIcons();

            const formData = new FormData(form);

            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async (res) => {
                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (parseErr) {
                    if (!res.ok) {
                        throw new Error(`Server returned error (${res.status}). Please check field entries.`);
                    }
                    throw new Error('Unexpected non-JSON response received from server.');
                }

                if (!res.ok) {
                    if (res.status === 422 && data.errors) {
                        const firstError = Object.values(data.errors).flat()[0];
                        throw new Error(firstError || 'Validation failed.');
                    }
                    throw new Error(data.message || 'Failed to save office location.');
                }
                return data;
            })
            .then(data => {
                closeOfficeModal();
                showAlert(data.message || 'Office location saved successfully.');
                setTimeout(() => window.location.reload(), 600);
            })
            .catch(err => {
                showModalError(err.message);
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = `<span>Save Office Location</span>`;
            });
        });

        function triggerSync(officeId, officeName) {
            if (!confirm(`Trigger full data sync for '${officeName}'?`)) return;

            const btn = event.currentTarget;
            const origText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i> Syncing...`;
            lucide.createIcons();

            fetch(syncUrlPattern.replace(':id', officeId), {
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
                btn.innerHTML = origText;
                lucide.createIcons();

                if (data.success) {
                    showAlert(data.message);
                    if (currentSyncReportOfficeId == officeId) {
                        loadSyncReport(officeId);
                    }
                } else {
                    showAlert(data.error || 'Sync failed.', true);
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = origText;
                lucide.createIcons();
                showAlert('Sync error: ' + err.message, true);
            });
        }

        // ==========================================
        // SYNC REPORT MODAL ENGINE
        // ==========================================
        let currentSyncReportOfficeId = null;
        let syncReportTimer = null;
        let syncReportCachedItems = [];

        function openSyncReportModal(officeId, officeName) {
            currentSyncReportOfficeId = officeId;
            document.getElementById('sr-modal-title').innerText = `Sync Report: ${officeName || 'Office'}`;
            document.getElementById('sr-office-switcher').value = officeId;
            document.getElementById('sync-report-modal').classList.remove('hidden');

            loadSyncReport(officeId);
            startAutoRefresh();
        }

        function closeSyncReportModal() {
            document.getElementById('sync-report-modal').classList.add('hidden');
            stopAutoRefresh();
        }

        function startAutoRefresh() {
            stopAutoRefresh();
            const autoToggle = document.getElementById('sr-autorefresh-toggle');
            if (autoToggle && autoToggle.checked) {
                syncReportTimer = setInterval(() => {
                    if (currentSyncReportOfficeId && !document.getElementById('sync-report-modal').classList.contains('hidden')) {
                        loadSyncReport(currentSyncReportOfficeId, true);
                    }
                }, 4000);
            }
        }

        function stopAutoRefresh() {
            if (syncReportTimer) {
                clearInterval(syncReportTimer);
                syncReportTimer = null;
            }
        }

        function toggleAutoRefresh(enabled) {
            if (enabled) {
                startAutoRefresh();
            } else {
                stopAutoRefresh();
            }
        }

        function refreshSyncReportNow() {
            const icon = document.getElementById('sr-refresh-icon');
            if (icon) icon.classList.add('animate-spin');
            if (currentSyncReportOfficeId) {
                loadSyncReport(currentSyncReportOfficeId, false, () => {
                    if (icon) icon.classList.remove('animate-spin');
                });
            }
        }

        function loadSyncReport(officeId, isBackground = false, callback = null) {
            currentSyncReportOfficeId = officeId;
            const url = syncReportUrlPattern.replace(':id', officeId);

            if (!isBackground && syncReportCachedItems.length === 0) {
                document.getElementById('sr-table-body').innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i data-lucide="loader-2" class="w-6 h-6 animate-spin text-blue-600"></i>
                                <span class="text-xs font-medium">Fetching sync report data...</span>
                            </div>
                        </td>
                    </tr>
                `;
                lucide.createIcons();
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
                renderSyncReport(data);
                if (callback) callback();
            })
            .catch(err => {
                console.error('Failed to load sync report:', err);
                if (!isBackground) {
                    document.getElementById('sr-table-body').innerHTML = `
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-rose-600 text-xs">
                                Failed to fetch sync telemetry: ${err.message}
                            </td>
                        </tr>
                    `;
                }
                if (callback) callback();
            });
        }

        function renderSyncReport(data) {
            const office = data.office;
            const summary = data.summary;
            const items = data.items;

            syncReportCachedItems = items;

            // Update title & badges
            document.getElementById('sr-modal-title').innerText = `Sync Report: ${office.name}`;
            const badge = document.getElementById('sr-office-badge');
            if (office.is_active) {
                badge.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200';
                badge.innerText = 'Active Location';
            } else {
                badge.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200';
                badge.innerText = 'Disabled';
            }

            // Update KPI cards
            document.getElementById('sr-stat-running').innerText = summary.running || 0;
            document.getElementById('sr-stat-slow').innerText = summary.slow || 0;
            document.getElementById('sr-stat-stuck').innerText = summary.stuck || 0;
            document.getElementById('sr-stat-idle').innerText = summary.idle || 0;
            document.getElementById('sr-stat-records').innerText = Number(summary.total_records || 0).toLocaleString();

            const d = new Date();
            document.getElementById('sr-last-updated').innerText = `Last updated: ${d.toLocaleTimeString()}`;

            // Render table items
            renderSyncTableRows(items);
        }

        function renderSyncTableRows(items) {
            const tbody = document.getElementById('sr-table-body');
            const searchVal = (document.getElementById('sr-search-input')?.value || '').toLowerCase().trim();

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
                                <button onclick="syncSingleModule('${item.key}', '${escapeHtml(item.sync)}')"
                                    class="px-2.5 py-1 bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 text-xs font-medium rounded-md transition-colors border border-slate-200 inline-flex items-center gap-1"
                                    id="btn-sync-${item.key}">
                                    <i data-lucide="refresh-cw" class="w-3 h-3"></i> Sync
                                </button>
                            ` : `
                                <span class="text-[11px] text-slate-400 italic">Auto</span>
                            `}
                        </td>
                    </tr>
                `;
            }).join('');

            lucide.createIcons();
        }

        function filterSyncReportTable() {
            renderSyncTableRows(syncReportCachedItems);
        }

        function syncSingleModule(moduleKey, moduleLabel) {
            if (!currentSyncReportOfficeId) return;

            const btn = document.getElementById(`btn-sync-${moduleKey}`);
            let origContent = '';
            if (btn) {
                origContent = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = `<i data-lucide="loader-2" class="w-3 h-3 animate-spin"></i> Syncing...`;
                lucide.createIcons();
            }

            fetch(syncModuleUrlPattern.replace(':id', currentSyncReportOfficeId), {
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
                    lucide.createIcons();
                }

                if (data.success) {
                    showAlert(data.message);
                    loadSyncReport(currentSyncReportOfficeId, true);
                } else {
                    showAlert(data.error || `Sync failed for ${moduleLabel}.`, true);
                }
            })
            .catch(err => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = origContent;
                    lucide.createIcons();
                }
                showAlert(`Sync error: ${err.message}`, true);
            });
        }

        function triggerOfficeFullSyncModal() {
            if (!currentSyncReportOfficeId) return;
            const officeSelect = document.getElementById('sr-office-switcher');
            const officeName = officeSelect.options[officeSelect.selectedIndex]?.text || 'Office';

            const btn = document.getElementById('sr-sync-all-btn');
            const origHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i> Syncing All...`;
            lucide.createIcons();

            fetch(syncUrlPattern.replace(':id', currentSyncReportOfficeId), {
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
                lucide.createIcons();

                if (data.success) {
                    showAlert(data.message);
                    loadSyncReport(currentSyncReportOfficeId, true);
                } else {
                    showAlert(data.error || 'Sync failed.', true);
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = origHtml;
                lucide.createIcons();
                showAlert(`Sync error: ${err.message}`, true);
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            return String(text).replace(/[&<>"']/g, function(m) {
                return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[m];
            });
        }

        // Global helper for opening sync report modal from anywhere
        window.openOfficeSyncReport = openSyncReportModal;
    </script>
</x-app-layout>
