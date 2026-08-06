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
            <button onclick="openOfficeModal()"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-lg shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Add Office Location
            </button>
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
                            <tr class="hover:bg-slate-50/80 transition-colors {{ $office->id == $activeOfficeId ? 'bg-blue-50/40' : '' }}">
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
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="building" class="w-4 h-4 text-blue-600"></i>
                                        <span>{{ $office->name }}</span>
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
                                    <div class="flex items-center justify-end gap-2">
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
                                            class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-slate-100 rounded-lg transition-colors">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>

                                        <!-- Delete Button -->
                                        @if(count($offices) > 1)
                                            <form method="POST" action="{{ route('offices.destroy', $office->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this office?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
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

    <!-- Office Modal -->
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
            const method = document.getElementById('form-method').value;
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
            if (!confirm(`Trigger data sync for '${officeName}'?`)) return;

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
                    setTimeout(() => window.location.reload(), 600);
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
    </script>
</x-app-layout>
