{{--
x-patient-modal — Reusable Patient Detail Modal
─────────────────────────────────────────────────
Drop-in component; include once per page, at the bottom before </x-app-layout>.

Usage:
<x-app-components.patient-modal />

Open from JavaScript:
openPatient(patientId) — opens modal and loads patient data

The component declares the following globals:
currentPatientId — tracks the open patient for lazy tab loads
openPatient(id) — opens modal (callable from DataTables inline onclick)
activatePmTab(tabId) — switches active tab (callable from page JS if needed)

All AJAX calls use inline {{ url() }} — no dependency on a page-level baseUrl.
--}}

<div id="patientModal"
    class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[60] flex items-start justify-center pt-10 pb-10 overflow-y-auto">
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xl w-full max-w-4xl mx-4 flex flex-col">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800">Patient Information</h3>
            <button id="closePatientModal" class="text-slate-400 hover:text-slate-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>

        {{-- Identity row --}}
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-3">
                <div id="patientAvatar"
                    class="w-11 h-11 rounded-full bg-violet-500 flex items-center justify-center text-white text-sm font-bold shrink-0">
                    ??</div>
                <span id="patientModalName" class="text-xl font-bold text-slate-900">—</span>
            </div>
            <button id="addReminderBtn"
                class="border border-emerald-500 text-emerald-700 text-xs font-semibold px-4 py-1.5 rounded hover:bg-emerald-50 transition-colors">+
                Add Reminder</button>
        </div>

        {{-- Tabs --}}
        <div class="px-6 border-b border-slate-100">
            <nav class="flex gap-5" id="patientTabNav">
                <button data-tab="pm-info"
                    class="pm-tab pb-3 text-sm font-semibold border-b-2 border-emerald-500 text-slate-900">Info</button>
                <button data-tab="pm-family"
                    class="pm-tab pb-3 text-sm font-medium border-b-2 border-transparent text-slate-400 hover:text-slate-600">Family</button>
                <button data-tab="pm-employer"
                    class="pm-tab pb-3 text-sm font-medium border-b-2 border-transparent text-slate-400 hover:text-slate-600">Employer</button>
                <button data-tab="pm-ledger"
                    class="pm-tab pb-3 text-sm font-medium border-b-2 border-transparent text-slate-400 hover:text-slate-600">Ledger</button>
                <button data-tab="pm-txplans"
                    class="pm-tab pb-3 text-sm font-medium border-b-2 border-transparent text-slate-400 hover:text-slate-600">TX
                    Plans</button>
                <button data-tab="pm-ar"
                    class="pm-tab pb-3 text-sm font-medium border-b-2 border-transparent text-slate-400 hover:text-slate-600">AR
                    Summary</button>
                <button data-tab="pm-notes"
                    class="pm-tab pb-3 text-sm font-medium border-b-2 border-transparent text-slate-400 hover:text-slate-600">Activities
                    and Notes</button>
                <button data-tab="pm-reminder"
                    class="pm-tab pb-3 text-sm font-medium border-b-2 border-transparent text-slate-400 hover:text-slate-600">Reminder</button>
            </nav>
        </div>

        {{-- Tab panels --}}
        <div class="px-6 py-5 flex-1 min-h-[380px]">

            {{-- INFO --}}
            <div id="pm-info" class="pm-panel">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="border border-slate-200 rounded-lg p-4">
                        <p class="text-xs font-bold text-slate-700 mb-4">Patient Information</p>
                        <div class="grid grid-cols-2 gap-x-6 gap-y-4 text-xs mb-4">
                            <div>
                                <p class="text-slate-400 mb-0.5">Age</p>
                                <p class="font-bold text-slate-800" id="pi-age">—</p>
                            </div>
                            <div>
                                <p class="text-slate-400 mb-0.5">Gender</p>
                                <p class="font-bold text-slate-800" id="pi-gender">—</p>
                            </div>
                            <div>
                                <p class="text-slate-400 mb-0.5">Birthdate</p>
                                <p class="font-bold text-slate-800" id="pi-birthdate">—</p>
                            </div>
                            <div>
                                <p class="text-slate-400 mb-0.5">Status</p>
                                <p class="font-bold text-slate-800" id="pi-status">—</p>
                            </div>
                        </div>
                        <div class="border-t border-slate-100 pt-4 grid grid-cols-2 gap-x-6 gap-y-4 text-xs mb-4">
                            <div>
                                <p class="text-slate-400 mb-0.5">Mobile Phone</p>
                                <p class="font-bold text-slate-800" id="pi-mobile">—</p>
                            </div>
                            <div>
                                <p class="text-slate-400 mb-0.5">Work Phone</p>
                                <p class="font-bold text-slate-800" id="pi-work">—</p>
                            </div>
                            <div>
                                <p class="text-slate-400 mb-0.5">Home Phone</p>
                                <p class="font-bold text-slate-800" id="pi-home">—</p>
                            </div>
                            <div>
                                <p class="text-slate-400 mb-0.5">Email Address</p>
                                <p class="font-bold text-slate-800 truncate" id="pi-email">—</p>
                            </div>
                        </div>
                        <div class="border border-slate-200 rounded overflow-hidden h-36 bg-slate-100 mb-3">
                            <iframe id="pi-map" class="w-full h-full" frameborder="0" style="border:0" allowfullscreen
                                loading="lazy" src="https://www.google.com/maps/embed/v1/place?key=&q=United+States"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                        <div class="grid grid-cols-3 gap-3 text-xs">
                            <div>
                                <p class="text-slate-400 mb-0.5">Address</p>
                                <p class="font-bold text-slate-800" id="pi-address">—</p>
                            </div>
                            <div>
                                <p class="text-slate-400 mb-0.5">City</p>
                                <p class="font-bold text-slate-800" id="pi-city">—</p>
                            </div>
                            <div>
                                <p class="text-slate-400 mb-0.5">Zip</p>
                                <p class="font-bold text-slate-800" id="pi-zip">—</p>
                            </div>
                        </div>
                    </div>
                    <div class="border border-slate-200 rounded-lg p-4">
                        <p class="text-xs font-bold text-slate-700 mb-4">Overview</p>
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="border border-slate-100 rounded p-3">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-slate-400">Next Visit</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-300"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M7 17L17 7" />
                                        <path d="M7 7h10v10" />
                                    </svg>
                                </div>
                                <p class="font-bold text-slate-800" id="ov-next-date">—</p>
                                <p class="text-slate-400" id="ov-next-label">N/A</p>
                            </div>
                            <div class="border border-slate-100 rounded p-3">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-slate-400">Last Visit</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-300"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M7 17L17 7" />
                                        <path d="M7 7h10v10" />
                                    </svg>
                                </div>
                                <p class="font-bold text-slate-800" id="ov-last-date">—</p>
                                <p class="text-slate-400" id="ov-last-label">N/A</p>
                            </div>
                            <div class="border border-slate-100 rounded p-3">
                                <p class="text-slate-400 mb-1">Remaining Insurance</p>
                                <p class="font-bold text-slate-800" id="ov-insurance">$ 0</p>
                            </div>
                            <div class="border border-slate-100 rounded p-3">
                                <p class="text-slate-400 mb-1">Treatment Plans</p>
                                <div class="flex gap-4">
                                    <div>
                                        <p class="font-bold text-slate-800" id="ov-tp-sched">$ 0</p>
                                        <p class="text-slate-400 text-[10px]">Scheduled</p>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800" id="ov-tp-unsched">$ 0</p>
                                        <p class="text-slate-400 text-[10px]">Unsched</p>
                                    </div>
                                </div>
                            </div>
                            <div class="border border-slate-100 rounded p-3">
                                <p class="text-slate-400 mb-1">Hygiene Due</p>
                                <p class="font-bold text-slate-800" id="ov-hygiene">—</p>
                                <p class="text-slate-400 text-[10px]">Scheduled:</p>
                            </div>
                            <div class="border border-slate-100 rounded p-3">
                                <p class="text-slate-400 mb-1">Lifetime Value</p>
                                <p class="font-bold text-slate-800" id="ov-lifetime">$ 0</p>
                            </div>
                            <div class="col-span-2 border border-slate-100 rounded p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-slate-400">Appointments</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-300"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M7 17L17 7" />
                                        <path d="M7 7h10v10" />
                                    </svg>
                                </div>
                                <div class="grid grid-cols-3 text-center gap-2">
                                    <div>
                                        <p class="font-bold text-slate-800 text-base" id="ov-apt-completed-pct">0.00%
                                        </p>
                                        <p class="text-slate-400 text-[10px]">Completed: <span
                                                id="ov-apt-completed-cnt">0</span></p>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 text-base" id="ov-apt-sched-pct">0.00%</p>
                                        <p class="text-slate-400 text-[10px]">Scheduled: <span
                                                id="ov-apt-sched-cnt">0</span></p>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 text-base" id="ov-apt-broken-pct">0.00%</p>
                                        <p class="text-slate-400 text-[10px]">Broken: <span
                                                id="ov-apt-broken-cnt">0</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FAMILY --}}
            <div id="pm-family" class="pm-panel hidden">
                <div class="border border-slate-200 rounded-lg overflow-hidden">
                    <table class="w-full text-xs text-slate-700">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="p-3 text-left font-semibold">Name</th>
                                <th class="p-3 text-left font-semibold">Status</th>
                                <th class="p-3 text-left font-semibold">Gender</th>
                                <th class="p-3 text-left font-semibold">Last Visit</th>
                                <th class="p-3 text-left font-semibold">Next Visit</th>
                                <th class="p-3 text-left font-semibold">Hygiene Due</th>
                            </tr>
                        </thead>
                        <tbody id="pm-family-body">
                            <tr>
                                <td colspan="6" class="p-4 text-center text-slate-400">No Data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- EMPLOYER --}}
            <div id="pm-employer" class="pm-panel hidden">
                <div class="border border-slate-200 rounded-lg p-4 bg-white">
                    <p class="text-xs font-semibold text-slate-500 mb-1">Employer Name</p>
                    <p class="text-sm font-bold text-slate-800" id="pm-employer-name">No employer information available.
                    </p>
                </div>
            </div>

            {{-- LEDGER --}}
            <div id="pm-ledger" class="pm-panel hidden">
                <div class="border border-slate-200 rounded-lg overflow-hidden">
                    <table class="w-full text-xs text-slate-700">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="p-3 text-left font-semibold">Code</th>
                                <th class="p-3 text-left font-semibold">Description</th>
                                <th class="p-3 text-left font-semibold">Tooth</th>
                                <th class="p-3 text-left font-semibold">Surface</th>
                                <th class="p-3 text-left font-semibold">Amount</th>
                                <th class="p-3 text-left font-semibold">Provider</th>
                                <th class="p-3 text-left font-semibold">Date</th>
                            </tr>
                        </thead>
                        <tbody id="pm-ledger-body">
                            <tr>
                                <td colspan="7" class="p-4 text-center text-slate-400">No Data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TX PLANS --}}
            <div id="pm-txplans" class="pm-panel hidden">
                <div class="border border-slate-200 rounded-lg overflow-hidden overflow-x-auto">
                    <table class="w-full text-xs text-slate-700 min-w-[780px]">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="p-3 text-left font-semibold">Code</th>
                                <th class="p-3 text-left font-semibold">Description</th>
                                <th class="p-3 text-left font-semibold">Tooth</th>
                                <th class="p-3 text-left font-semibold">Surface</th>
                                <th class="p-3 text-left font-semibold">Amount</th>
                                <th class="p-3 text-left font-semibold">Provider</th>
                                <th class="p-3 text-left font-semibold">Status</th>
                                <th class="p-3 text-left font-semibold">Planned</th>
                                <th class="p-3 text-left font-semibold">Scheduled</th>
                                <th class="p-3 text-left font-semibold">Completed</th>
                                <th class="p-3 text-left font-semibold">Date Created</th>
                            </tr>
                        </thead>
                        <tbody id="pm-txplans-body">
                            <tr>
                                <td colspan="11" class="p-4 text-center text-slate-400">No Data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- AR SUMMARY --}}
            <div id="pm-ar" class="pm-panel hidden">
                <p class="text-xs font-bold text-slate-800 mb-4">Accounts Receivable Summary</p>
                <div class="border border-slate-200 rounded-lg p-4 mb-4">
                    <div class="grid grid-cols-7 gap-3 text-xs" id="ar-card-grid">
                        <div>
                            <p class="text-slate-400 mb-0.5">Total</p>
                            <p class="font-bold text-slate-800" id="ar-total">$ 0</p>
                        </div>
                        <div>
                            <p class="text-slate-400 mb-0.5">Insurance Claims</p>
                            <p class="font-bold text-slate-800" id="ar-insurance">$ 0</p>
                        </div>
                        <div>
                            <p class="text-slate-400 mb-0.5">Estimated Patients</p>
                            <p class="font-bold text-slate-800" id="ar-estimated">$ 0</p>
                        </div>
                        <div>
                            <p class="text-slate-400 mb-0.5">Current</p>
                            <p class="font-bold text-slate-800" id="ar-current">$ 0</p>
                        </div>
                        <div>
                            <p class="text-slate-400 mb-0.5">30+ Days</p>
                            <p class="font-bold text-slate-800" id="ar-30">$ 0</p>
                        </div>
                        <div>
                            <p class="text-slate-400 mb-0.5">60+ Days</p>
                            <p class="font-bold text-slate-800" id="ar-60">$ 0</p>
                        </div>
                        <div>
                            <p class="text-slate-400 mb-0.5">90+ Days</p>
                            <p class="font-bold text-slate-800" id="ar-90">$ 0</p>
                        </div>
                    </div>
                </div>
                <div class="border border-slate-200 rounded-lg overflow-hidden">
                    <table class="w-full text-xs text-slate-700">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="p-3 text-left font-semibold">Description</th>
                                <th class="p-3 text-left font-semibold">Code</th>
                                <th class="p-3 text-left font-semibold">Amount</th>
                                <th class="p-3 text-left font-semibold">Provider</th>
                                <th class="p-3 text-left font-semibold">Date</th>
                            </tr>
                        </thead>
                        <tbody id="pm-ar-body">
                            <tr>
                                <td colspan="5" class="p-4 text-center text-slate-400">No Data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ACTIVITIES & NOTES --}}
            <div id="pm-notes" class="pm-panel hidden">
                <div class="border border-slate-200 rounded-lg p-4 bg-white">
                    <p class="text-xs font-semibold text-slate-500 mb-1">Patient Notes</p>
                    <p class="text-sm text-slate-700 whitespace-pre-line" id="pm-patient-notes">No activities or notes
                        available.</p>
                </div>
            </div>

            {{-- REMINDER --}}
            <div id="pm-reminder" class="pm-panel hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm font-semibold text-slate-800 mb-4">Add a Reminder</p>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs text-slate-500 mb-1.5">Date</label>
                                <div
                                    class="relative inline-flex items-center border border-slate-300 rounded px-3 py-2 text-xs text-slate-700 bg-white gap-2 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                        <line x1="16" y1="2" x2="16" y2="6" />
                                        <line x1="8" y1="2" x2="8" y2="6" />
                                        <line x1="3" y1="10" x2="21" y2="10" />
                                    </svg>
                                    <input type="date" id="reminder-date"
                                        class="border-0 outline-none text-xs bg-transparent text-slate-700 cursor-pointer"
                                        value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1.5">Type</label>
                                    <select id="reminder-type"
                                        class="w-full border border-slate-300 rounded px-3 py-2 text-xs text-slate-700 bg-white focus:outline-none focus:border-emerald-500 appearance-none">
                                        <option value="">Select option</option>
                                        <option>Call</option>
                                        <option>Email</option>
                                        <option>Text</option>
                                        <option>Follow Up</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1.5">Estimate $</label>
                                    <input type="number" id="reminder-estimate" placeholder="$0.00" step="0.01" min="0"
                                        class="w-full border border-slate-300 rounded px-3 py-2 text-xs text-slate-700 focus:outline-none focus:border-emerald-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1.5">Assignee</label>
                                <select id="reminder-assignee"
                                    class="w-full border border-slate-300 rounded px-3 py-2 text-xs text-slate-700 bg-white focus:outline-none focus:border-emerald-500 appearance-none">
                                    <option value="">Select user</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1.5">Notes</label>
                                <textarea id="reminder-notes" rows="5" placeholder="Say something about this reminder"
                                    class="w-full border border-slate-300 rounded px-3 py-2 text-xs text-slate-700 focus:outline-none focus:border-emerald-500 resize-none"></textarea>
                            </div>
                            <div class="flex justify-end">
                                <button id="submitReminderBtn"
                                    class="bg-emerald-400 hover:bg-emerald-500 text-white text-xs font-semibold px-6 py-2 rounded transition-colors">Add
                                    Reminder</button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800 mb-4">Activity</p>
                        <div class="bg-slate-100 rounded-lg flex items-center justify-center min-h-[200px]">
                            <p class="text-xs text-slate-400">No Data Available</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /tab panels --}}
    </div>
</div>

<script>
    /* ── Patient Modal globals ─────────────────────────────────────── */
    var currentPatientId = null;

    function activatePmTab(tabId) {
        $('#patientTabNav .pm-tab').removeClass('border-emerald-500 text-slate-900 font-semibold').addClass('border-transparent text-slate-400 font-medium');
        $('#patientTabNav .pm-tab[data-tab="' + tabId + '"]').removeClass('border-transparent text-slate-400 font-medium').addClass('border-emerald-500 text-slate-900 font-semibold');

        $('.pm-panel').addClass('hidden');
        $('#' + tabId).removeClass('hidden');
    }

    /* Public: open the modal for a given patient ID. Safe to call from
       DataTables inline onclick (e.g. onclick="openPatient(${row.id})"). */
    function openPatient(id) {
        currentPatientId = id;
        activatePmTab('pm-info');
        $('#patientModal').removeClass('hidden');

        // Dynamically inflate z-index per instance to stack over limitless modals
        window._limitlessZIndex = (window._limitlessZIndex || 120) + 10;
        document.getElementById('patientModal').style.zIndex = window._limitlessZIndex;

        $('#patientModalName').text('Loading…');
        $('#patientAvatar').text('…');

        // Reset lazy-loaded tabs
        $('#pm-family-body').html('<tr><td colspan="6" class="p-4 text-center text-slate-400">No Data</td></tr>');
        $('#pm-employer-name').text('—');
        ['ar-total', 'ar-insurance', 'ar-estimated', 'ar-current', 'ar-30', 'ar-60', 'ar-90']
            .forEach(function (aid) { $('#' + aid).text('$ 0'); });
        $('#pm-ar-body').html('<tr><td colspan="5" class="p-4 text-center text-slate-400">No Data</td></tr>');

        $.ajax({
            url: "{{ url('/patients') }}/" + id,
            type: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (p) {
                var nameParts = p.name ? p.name.split(', ') : ['', ''];
                var initials = ((nameParts[1] || '').charAt(0) + (nameParts[0] || '').charAt(0)).toUpperCase() || '??';
                $('#patientAvatar').text(initials);
                $('#patientModalName').text(p.name || '—');

                $('#pi-age').text(p.age || '—');
                $('#pi-gender').text(p.gender || '—');
                $('#pi-birthdate').text(p.birthdate || '—');
                $('#pi-status').text(p.status || '—');
                $('#pi-mobile').text(p.mobile_phone || 'N/A');
                $('#pi-work').text(p.work_phone || 'N/A');
                $('#pi-home').text(p.home_phone || 'N/A');
                $('#pi-email').text(p.email || 'N/A');
                $('#pi-address').text(p.address || '—');
                $('#pi-city').text(p.city || '—');
                $('#pi-zip').text(p.zip || '—');

                var mapQuery = encodeURIComponent([p.address, p.city, p.state, p.zip].filter(Boolean).join(', '));
                $('#pi-map').attr('src', 'https://maps.google.com/maps?q=' + mapQuery + '&output=embed&z=14');

                var ov = p.overview || {};
                var nv = ov.next_visit || {};
                var lv = ov.last_visit || {};
                var tp = ov.treatment_plans || {};
                var apts = ov.appointments || {};
                var comp = apts.completed || {};
                var sched = apts.scheduled || {};
                var broken = apts.broken || {};

                $('#ov-next-date').text(nv.date || '—');
                $('#ov-next-label').text(nv.label || 'N/A');
                $('#ov-last-date').text(lv.date || '—');
                $('#ov-last-label').text(lv.label || 'N/A');
                $('#ov-insurance').text('$ ' + Number(ov.remaining_insurance || 0).toLocaleString());
                $('#ov-tp-sched').text('$ ' + Number(tp.scheduled || 0).toLocaleString());
                $('#ov-tp-unsched').text('$ ' + Number(tp.unscheduled || 0).toLocaleString());
                $('#ov-hygiene').text(ov.hygiene_due || '—');
                $('#ov-lifetime').text('$ ' + Number(ov.lifetime_production || 0).toLocaleString());

                $('#ov-apt-completed-pct').text((comp.percent || '0.00') + '%');
                $('#ov-apt-completed-cnt').text(comp.count || 0);
                $('#ov-apt-sched-pct').text((sched.percent || '0.00') + '%');
                $('#ov-apt-sched-cnt').text(sched.count || 0);
                $('#ov-apt-broken-pct').text((broken.percent || '0.00') + '%');
                $('#ov-apt-broken-cnt').text(broken.count || 0);

                var ledger = p.ledger || [];
                if (ledger.length) {
                    var rows = '';
                    ledger.forEach(function (itm) {
                        var providerHtml = itm.provider_id
                            ? '<div class="flex items-center gap-2"><svg onclick="openProviderModal(' + itm.provider_id + ')" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-500 hover:text-emerald-700 cursor-pointer shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" title="Provider Information"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg><span>' + (itm.provider || '—') + '</span></div>'
                            : (itm.provider || '—');

                        rows += '<tr class="border-t border-slate-100 hover:bg-slate-50/50">'
                            + '<td class="p-3 font-medium">' + (itm.code || '—') + '</td>'
                            + '<td class="p-3 text-slate-500">' + (itm.description || '—') + '</td>'
                            + '<td class="p-3 text-center">' + (itm.tooth || '—') + '</td>'
                            + '<td class="p-3 text-center">' + (itm.surface || '—') + '</td>'
                            + '<td class="p-3 font-semibold ' + (itm.amount && itm.amount.startsWith('-') ? 'text-emerald-600' : 'text-slate-700') + '">' + (itm.amount || '—') + '</td>'
                            + '<td class="p-3">' + providerHtml + '</td>'
                            + '<td class="p-3 text-slate-500">' + (itm.date || '—') + '</td>'
                            + '</tr>';
                    });
                    $('#pm-ledger-body').html(rows);
                } else {
                    $('#pm-ledger-body').html('<tr><td colspan="7" class="p-4 text-center text-slate-400">No Data</td></tr>');
                }

                $('#pm-patient-notes').text(p.notes || 'No activities or notes available.');
            },
            error: function () { $('#patientModalName').text('Could not load patient'); }
        });
    }

    /* Public: switch active tab (can be called from page JS too). */
    function activatePmTab(tabId) {
        $('.pm-tab').removeClass('border-emerald-500 text-slate-900').addClass('border-transparent text-slate-400');
        $('[data-tab="' + tabId + '"]').addClass('border-emerald-500 text-slate-900').removeClass('border-transparent text-slate-400');
        $('.pm-panel').addClass('hidden');
        $('#' + tabId).removeClass('hidden');
    }

    /* ── Lazy-load helpers ─────────────────────────────────────────── */
    function _txSkeletonRows() {
        var s = '';
        for (var i = 0; i < 5; i++) {
            s += '<tr class="border-t border-slate-100">';
            for (var j = 0; j < 11; j++) {
                s += '<td class="p-3"><div class="h-3 ' + (j === 1 ? 'w-28' : 'w-14') + ' bg-slate-200 rounded animate-pulse"></div></td>';
            }
            s += '</tr>';
        }
        return s;
    }

    function _arTableSkeleton() {
        var rows = '';
        for (var i = 0; i < 5; i++) {
            rows += '<tr class="border-t border-slate-100">';
            for (var j = 0; j < 5; j++) {
                rows += '<td class="p-3"><div class="h-3 ' + (j === 0 ? 'w-28' : 'w-16') + ' bg-slate-200 rounded animate-pulse"></div></td>';
            }
            rows += '</tr>';
        }
        return rows;
    }

    function _arCardSkeleton() {
        return Array(7).fill(0).map(function () {
            return '<div><div class="h-3 w-14 bg-slate-200 rounded animate-pulse mb-2"></div><div class="h-4 w-20 bg-slate-200 rounded animate-pulse"></div></div>';
        }).join('');
    }

    function _loadPatientTXPlans(patientId) {
        $('#pm-txplans-body').html(_txSkeletonRows());
        $.ajax({
            url: "{{ url('/patients') }}/" + patientId + '/treatment-plans',
            type: 'GET',
            success: function (data) {
                var txplans = data || [];
                if (txplans.length) {
                    var rows = '';
                    txplans.forEach(function (itm) {
                        var badge = itm.status === 'Completed'
                            ? '<span class="px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">Completed</span>'
                            : itm.status === 'Scheduled'
                                ? '<span class="px-2 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200">Scheduled</span>'
                                : '<span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-50 text-slate-600 border border-slate-200">Unscheduled</span>';
                        var providerHtml = itm.provider_id
                            ? '<div class="flex items-center gap-2"><svg onclick="openProviderModal(' + itm.provider_id + ')" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-500 hover:text-emerald-700 cursor-pointer shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" title="Provider Information"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg><span>' + (itm.provider || '—') + '</span></div>'
                            : (itm.provider || '—');

                        rows += '<tr class="border-t border-slate-100 hover:bg-slate-50/50">'
                            + '<td class="p-3 font-medium">' + (itm.code || '—') + '</td>'
                            + '<td class="p-3 text-slate-500">' + (itm.description || '—') + '</td>'
                            + '<td class="p-3 text-center">' + (itm.tooth || '—') + '</td>'
                            + '<td class="p-3 text-center">' + (itm.surface || '—') + '</td>'
                            + '<td class="p-3 font-semibold">' + (itm.amount || '—') + '</td>'
                            + '<td class="p-3">' + providerHtml + '</td>'
                            + '<td class="p-3">' + badge + '</td>'
                            + '<td class="p-3 text-slate-500">' + (itm.date_planned || '—') + '</td>'
                            + '<td class="p-3 text-slate-500">' + (itm.date_scheduled || '—') + '</td>'
                            + '<td class="p-3 text-slate-500">' + (itm.date_completed || '—') + '</td>'
                            + '<td class="p-3 text-slate-500">' + (itm.date_created || '—') + '</td>'
                            + '</tr>';
                    });
                    $('#pm-txplans-body').html(rows);
                } else {
                    $('#pm-txplans-body').html('<tr><td colspan="11" class="p-4 text-center text-slate-400">No Data</td></tr>');
                }
            },
            error: function () {
                $('#pm-txplans-body').html('<tr><td colspan="11" class="p-4 text-center text-slate-400">Failed to load data</td></tr>');
            }
        });
    }

    function _loadPatientAR(patientId) {
        $('#ar-card-grid').html(_arCardSkeleton());
        $('#pm-ar-body').html(_arTableSkeleton());
        $.ajax({
            url: "{{ url('/patients') }}/" + patientId + '/ar',
            type: 'GET',
            success: function (ar) {
                $('#ar-card-grid').html(
                    '<div><p class="text-slate-400 mb-0.5">Total</p><p class="font-bold text-slate-800" id="ar-total">$ ' + ar.total + '</p></div>'
                    + '<div><p class="text-slate-400 mb-0.5">Insurance Claims</p><p class="font-bold text-slate-800" id="ar-insurance">$ ' + ar.insurance_claims + '</p></div>'
                    + '<div><p class="text-slate-400 mb-0.5">Estimated Patients</p><p class="font-bold text-slate-800" id="ar-estimated">$ ' + ar.estimated_patient + '</p></div>'
                    + '<div><p class="text-slate-400 mb-0.5">Current</p><p class="font-bold text-slate-800" id="ar-current">$ ' + ar.current + '</p></div>'
                    + '<div><p class="text-slate-400 mb-0.5">30+ Days</p><p class="font-bold text-slate-800" id="ar-30">$ ' + ar.thirty_days + '</p></div>'
                    + '<div><p class="text-slate-400 mb-0.5">60+ Days</p><p class="font-bold text-slate-800" id="ar-60">$ ' + ar.sixty_days + '</p></div>'
                    + '<div><p class="text-slate-400 mb-0.5">90+ Days</p><p class="font-bold text-slate-800" id="ar-90">$ ' + ar.ninety_days + '</p></div>'
                );
                var txs = ar.transactions || [];
                if (txs.length) {
                    var rows = '';
                    txs.forEach(function (itm) {
                        rows += '<tr class="border-t border-slate-100 hover:bg-slate-50/50">'
                            + '<td class="p-3 text-slate-500">' + (itm.description || '—') + '</td>'
                            + '<td class="p-3 font-medium">' + (itm.code || '—') + '</td>'
                            + '<td class="p-3 font-semibold">' + (itm.amount || '—') + '</td>'
                            + '<td class="p-3">' + (itm.provider || '—') + '</td>'
                            + '<td class="p-3 text-slate-500">' + (itm.date || '—') + '</td>'
                            + '</tr>';
                    });
                    $('#pm-ar-body').html(rows);
                } else {
                    $('#pm-ar-body').html('<tr><td colspan="5" class="p-4 text-center text-slate-400">No Data</td></tr>');
                }
            },
            error: function () {
                $('#ar-card-grid').html('<p class="text-xs text-slate-400 col-span-7">Failed to load AR data.</p>');
                $('#pm-ar-body').html('<tr><td colspan="5" class="p-4 text-center text-slate-400">Failed to load data</td></tr>');
            }
        });
    }

    function _loadPatientFamily(patientId) {
        var skeleton = '';
        for (var i = 0; i < 3; i++) {
            skeleton += '<tr class="border-t border-slate-100">';
            for (var j = 0; j < 6; j++) {
                skeleton += '<td class="p-3"><div class="h-3 ' + (j === 0 ? 'w-28' : 'w-16') + ' bg-slate-200 rounded animate-pulse"></div></td>';
            }
            skeleton += '</tr>';
        }
        $('#pm-family-body').html(skeleton);
        $.ajax({
            url: "{{ url('/patients') }}/" + patientId + '/family',
            type: 'GET',
            success: function (family) {
                if (family.length) {
                    var rows = '';
                    family.forEach(function (m) {
                        rows += '<tr class="border-t border-slate-100 hover:bg-slate-50/50">'
                            + '<td class="p-3">' + (m.name || '—') + '</td>'
                            + '<td class="p-3">' + (m.status || '—') + '</td>'
                            + '<td class="p-3">' + (m.gender || '—') + '</td>'
                            + '<td class="p-3">' + (m.last_visit || '—') + '</td>'
                            + '<td class="p-3">' + (m.next_visit || '—') + '</td>'
                            + '<td class="p-3">' + (m.hygiene_due || '—') + '</td>'
                            + '</tr>';
                    });
                    $('#pm-family-body').html(rows);
                } else {
                    $('#pm-family-body').html('<tr><td colspan="6" class="p-4 text-center text-slate-400">No Data</td></tr>');
                }
            },
            error: function () {
                $('#pm-family-body').html('<tr><td colspan="6" class="p-4 text-center text-slate-400">Failed to load data</td></tr>');
            }
        });
    }

    function _loadPatientEmployer(patientId) {
        $('#pm-employer-name').html('<div class="h-4 w-40 bg-slate-200 rounded animate-pulse"></div>');
        $.ajax({
            url: "{{ url('/patients') }}/" + patientId + '/employer',
            type: 'GET',
            success: function (data) {
                $('#pm-employer-name').text(data.name || 'No employer information available.');
                if (data.note) {
                    $('#pm-employer-name').after('<p class="text-xs text-slate-500 mt-2">' + data.note + '</p>');
                }
            },
            error: function () { $('#pm-employer-name').text('Failed to load employer data.'); }
        });
    }

    /* ── Event wiring ──────────────────────────────────────────────── */
    $(function () {
        $('#patientTabNav').on('click', '.pm-tab', function () {
            var tab = $(this).data('tab');
            activatePmTab(tab);
            if (tab === 'pm-txplans' && currentPatientId) _loadPatientTXPlans(currentPatientId);
            if (tab === 'pm-ar' && currentPatientId) _loadPatientAR(currentPatientId);
            if (tab === 'pm-family' && currentPatientId) _loadPatientFamily(currentPatientId);
            if (tab === 'pm-employer' && currentPatientId) _loadPatientEmployer(currentPatientId);
        });

        $('#closePatientModal').on('click', function () {
            $('#patientModal').addClass('hidden');
        });

        $('#patientModal').on('click', function (e) {
            if (e.target === this) $(this).addClass('hidden');
        });

        $('#addReminderBtn').on('click', function () {
            activatePmTab('pm-reminder');
        });
    });

    /* ── Provider Detail Modal logic ──────────────────────────────── */
    var _pmProdChart = null, _pmVisitsChart = null, _pmTxChart = null;
    var _pmCurrentData = null;

    function switchPmTab(tabName) {
        $('.pm-tab-btn').removeClass('border-emerald-500 text-emerald-600 font-semibold').addClass('border-transparent text-slate-400 font-medium');
        $('.pm-tab-btn[data-tab="' + tabName + '"]').addClass('border-emerald-500 text-emerald-600 font-semibold').removeClass('border-transparent text-slate-400 font-medium');

        $('.pm-tab-panel').addClass('hidden');
        $('#pm-tab-' + tabName).removeClass('hidden');

        if (_pmCurrentData && typeof Chart !== 'undefined') {
            renderPmCharts(tabName);
        }
    }

    function renderPmCharts(tabName) {
        if (!_pmCurrentData) return;
        var d = _pmCurrentData;

        var dates = (d.daily_production || []).map(function (item) {
            return item.date ? moment(item.date).format('MMM DD') : '';
        });

        if (tabName === 'production' && document.getElementById('provModalProductionChart')) {
            if (_pmProdChart) { _pmProdChart.destroy(); _pmProdChart = null; }
            _pmProdChart = new Chart(document.getElementById('provModalProductionChart'), {
                type: 'bar',
                data: {
                    labels: dates,
                    datasets: [
                        {
                            label: 'Production ($)',
                            data: (d.daily_production || []).map(function (i) { return i.production || 0; }),
                            backgroundColor: '#10b981', barPercentage: 1.0, categoryPercentage: 0.6
                        },
                        {
                            label: 'Per Visit ($)',
                            data: (d.daily_production || []).map(function (i) { return i.per_visit || 0; }),
                            backgroundColor: '#8b5cf6', barPercentage: 1.0, categoryPercentage: 0.6
                        }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        if (tabName === 'visits' && document.getElementById('provModalVisitsChart')) {
            if (_pmVisitsChart) { _pmVisitsChart.destroy(); _pmVisitsChart = null; }
            _pmVisitsChart = new Chart(document.getElementById('provModalVisitsChart'), {
                type: 'bar',
                data: {
                    labels: dates,
                    datasets: [
                        {
                            label: 'Patient Visits',
                            data: (d.daily_visits || []).map(function (i) { return i.patient_visits || 0; }),
                            backgroundColor: '#10b981', barPercentage: 1.0, categoryPercentage: 0.6
                        },
                        {
                            label: 'New Patient Visits',
                            data: (d.daily_visits || []).map(function (i) { return i.new_patient_visits || 0; }),
                            backgroundColor: '#8b5cf6', barPercentage: 1.0, categoryPercentage: 0.6
                        }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        if (tabName === 'tx-accepted' && document.getElementById('provModalTxChart')) {
            if (_pmTxChart) { _pmTxChart.destroy(); _pmTxChart = null; }
            _pmTxChart = new Chart(document.getElementById('provModalTxChart'), {
                type: 'bar',
                data: {
                    labels: dates,
                    datasets: [
                        {
                            label: 'Accepted TX Plan (%)',
                            data: (d.daily_tx || []).map(function (i) { return i.rate || 0; }),
                            backgroundColor: '#10b981', barPercentage: 1.0, categoryPercentage: 0.6
                        }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
    }

    function openProviderModal(id) {
        $('#providerModal').removeClass('hidden');

        window._limitlessZIndex = (window._limitlessZIndex || 120) + 10;
        document.getElementById('providerModal').style.zIndex = window._limitlessZIndex;

        switchPmTab('info');
        _pmCurrentData = null;

        $('#pm-avatar').css('background', '#6366f1').text('..');
        $('#pm-name').text('Loading...');
        $('#pm-abbr').text('');

        $('#pm-info-gender').text('N/A');
        $('#pm-info-specialty').text('Loading...');
        $('#pm-info-net').text('$ 0');
        $('#pm-info-avg-day').text('$ 0');
        $('#pm-info-per-visit').text('$ 0');
        $('#pm-info-tx-rate').text('0.00%');
        $('#pm-info-patient-visits').text('0');
        $('#pm-info-new-visits').text('0');

        var start = window._currentStart || moment().startOf('month').format('YYYY-MM-DD');
        var end = window._currentEnd || moment().format('YYYY-MM-DD');

        $.ajax({
            url: "{{ url('/dashboard/providers') }}/" + id,
            type: 'GET',
            data: { start_date: start, end_date: end },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (res) {
                if (!res || !res.provider) return;
                _pmCurrentData = res;
                var p = res.provider;
                var s = res.stats || {};

                var fullName = (p.LName || '') + (p.PName ? ', ' + p.PName : '');
                if (!fullName) fullName = p.Abbr || ('Provider ' + id);

                var initials = (p.PName ? p.PName.charAt(0) : '') + (p.LName ? p.LName.charAt(0) : '');
                if (!initials) initials = (p.Abbr || 'PR').substring(0, 2);

                $('#pm-avatar').text(initials.toUpperCase());
                $('#pm-name').text(fullName);
                $('#pm-abbr').text(p.Abbr ? '(' + p.Abbr + ')' : '');

                $('#pm-info-gender').text(p.Gender || 'N/A');
                $('#pm-info-specialty').text(p.Specialty || 'General');

                var netVal = s.net_production != null ? Number(s.net_production) : 0;
                var avgDayVal = s.avg_production_per_day != null ? Number(s.avg_production_per_day) : 0;
                var perVisitVal = s.production_per_visit != null ? Number(s.production_per_visit) : 0;
                var txRateVal = s.tx_accepted_rate != null ? Number(s.tx_accepted_rate) : 0;
                var visitsVal = s.patient_visits != null ? Number(s.patient_visits) : 0;
                var newVisitsVal = s.new_patient_visits != null ? Number(s.new_patient_visits) : 0;

                $('#pm-info-net').text('$ ' + netVal.toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 0}));
                $('#pm-info-avg-day').text('$ ' + avgDayVal.toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 0}));
                $('#pm-info-per-visit').text('$ ' + perVisitVal.toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 0}));
                $('#pm-info-tx-rate').text(txRateVal.toFixed(2) + '%');
                $('#pm-info-patient-visits').text(visitsVal.toLocaleString());
                $('#pm-info-new-visits').text(newVisitsVal.toLocaleString());

                $('#pm-prod-net').text('$ ' + netVal.toLocaleString());
                $('#pm-prod-avg').text('$ ' + avgDayVal.toLocaleString());
                $('#pm-prod-per-visit').text('$ ' + perVisitVal.toLocaleString());

                $('#pm-visits-total').text(visitsVal.toLocaleString());
                $('#pm-visits-new').text(newVisitsVal.toLocaleString());

                $('#pm-tx-rate').text(txRateVal.toFixed(2) + '%');
            }
        });
    }

    window.openProvider = function(id) {
        openProviderModal(id);
    };

    $(function () {
        $('#providerModal').on('click', function (e) {
            if (e.target === this) $(this).addClass('hidden');
        });
    });
</script>

{{-- Provider Detail Modal (Stackable & Shared Reusable Modal) --}}
<div id="providerModal"
    class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[92vh] flex flex-col overflow-hidden transform transition-all border border-slate-200/60">

        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 pt-5 pb-3 bg-white flex-shrink-0">
            <h3 class="text-base font-bold text-slate-900 tracking-tight">Provider Information</h3>
            <button type="button" onclick="$('#providerModal').addClass('hidden')"
                class="text-slate-400 hover:text-slate-700 transition p-1 rounded-lg hover:bg-slate-100 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>

        <!-- Provider identity -->
        <div class="flex items-center gap-3.5 px-6 pb-4 bg-white flex-shrink-0">
            <div id="pm-avatar"
                class="w-11 h-11 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-extrabold shrink-0 shadow-sm">
                HA
            </div>
            <div>
                <h4 id="pm-name" class="text-lg font-bold text-slate-900 leading-snug">Haddow, Mason</h4>
                <p id="pm-abbr" class="text-xs text-slate-400 font-medium"></p>
            </div>
        </div>

        <!-- Tabs Navigation Bar -->
        <div class="flex border-b border-slate-200/80 px-6 flex-shrink-0 bg-white">
            <button type="button" onclick="switchPmTab('info')"
                class="pm-tab-btn border-b-2 border-emerald-500 text-emerald-600 font-semibold py-2.5 pr-6 text-sm transition-colors focus:outline-none"
                data-tab="info">Info</button>
            <button type="button" onclick="switchPmTab('production')"
                class="pm-tab-btn border-b-2 border-transparent text-slate-400 hover:text-slate-600 font-medium py-2.5 pr-6 text-sm transition-colors focus:outline-none"
                data-tab="production">Production</button>
            <button type="button" onclick="switchPmTab('visits')"
                class="pm-tab-btn border-b-2 border-transparent text-slate-400 hover:text-slate-600 font-medium py-2.5 pr-6 text-sm transition-colors focus:outline-none"
                data-tab="visits">Visits</button>
            <button type="button" onclick="switchPmTab('tx-accepted')"
                class="pm-tab-btn border-b-2 border-transparent text-slate-400 hover:text-slate-600 font-medium py-2.5 text-sm transition-colors focus:outline-none"
                data-tab="tx-accepted">TX Accepted</button>
        </div>

        <!-- Tab Content Body (Gray background container matching screenshot) -->
        <div class="flex-1 overflow-y-auto bg-[#f8fafc] p-6">

            <!-- 1. Info Tab Panel (Matches Screenshot Pixel for Pixel) -->
            <div id="pm-tab-info" class="pm-tab-panel">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                    <!-- Left Column: Provider Information + Address -->
                    <div class="lg:col-span-7 space-y-4">
                        <h5 class="text-[13px] font-bold text-slate-600 uppercase tracking-wider">Provider Information</h5>

                        <!-- Top Card: Gender & Specialty -->
                        <div class="bg-white rounded-lg border border-slate-200/90 shadow-sm p-4">
                            <div class="grid grid-cols-2 divide-x divide-slate-100">
                                <div class="pr-4">
                                    <p class="text-xs text-slate-400 font-medium mb-1">Gender</p>
                                    <p id="pm-info-gender" class="text-sm font-extrabold text-slate-900">N/A</p>
                                </div>
                                <div class="pl-4">
                                    <p class="text-xs text-slate-400 font-medium mb-1">Specialty</p>
                                    <p id="pm-info-specialty" class="text-sm font-extrabold text-slate-900">Invisalign</p>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Card: Map & Address -->
                        <div class="bg-white rounded-lg border border-slate-200/90 shadow-sm p-4 space-y-4">
                            <!-- Map placeholder canvas -->
                            <div class="h-44 bg-gradient-to-b from-slate-100 to-slate-200/80 rounded-md flex items-center justify-center border border-slate-200/60 relative overflow-hidden">
                                <div class="text-center p-4">
                                    <svg class="w-8 h-8 text-slate-300 mx-auto mb-1" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                    <p class="text-xs font-medium text-slate-400">Map not available</p>
                                </div>
                            </div>
                            <!-- Address 4-column footer -->
                            <div class="grid grid-cols-4 divide-x divide-slate-100 bg-slate-50/60 rounded p-2.5 text-left">
                                <div class="pr-2">
                                    <p class="text-[10px] text-slate-400 font-medium mb-0.5">Address</p>
                                    <p class="text-xs font-bold text-slate-800 truncate">--</p>
                                </div>
                                <div class="px-2">
                                    <p class="text-[10px] text-slate-400 font-medium mb-0.5">City</p>
                                    <p class="text-xs font-bold text-slate-800 truncate">--</p>
                                </div>
                                <div class="px-2">
                                    <p class="text-[10px] text-slate-400 font-medium mb-0.5">State</p>
                                    <p class="text-xs font-bold text-slate-800 truncate">--</p>
                                </div>
                                <div class="pl-2">
                                    <p class="text-[10px] text-slate-400 font-medium mb-0.5">Zip</p>
                                    <p class="text-xs font-bold text-slate-800 truncate">--</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Overview -->
                    <div class="lg:col-span-5 space-y-4">
                        <h5 class="text-[13px] font-bold text-slate-600 uppercase tracking-wider">Overview</h5>

                        <div class="bg-white rounded-lg border border-slate-200/90 shadow-sm p-4">
                            <div class="divide-y divide-slate-100">
                                <!-- Row 1 -->
                                <div class="grid grid-cols-2 divide-x divide-slate-100 pb-3.5">
                                    <div class="pr-3">
                                        <p class="text-xs text-slate-400 font-medium mb-1">Production</p>
                                        <p id="pm-info-net" class="text-base font-extrabold text-slate-900">$ 0</p>
                                    </div>
                                    <div class="pl-3">
                                        <p class="text-xs text-slate-400 font-medium mb-1">Avg. Production / Day</p>
                                        <p id="pm-info-avg-day" class="text-base font-extrabold text-slate-900">$ 0</p>
                                    </div>
                                </div>
                                <!-- Row 2 -->
                                <div class="grid grid-cols-2 divide-x divide-slate-100 py-3.5">
                                    <div class="pr-3">
                                        <p class="text-xs text-slate-400 font-medium mb-1">Production / Patient Visit</p>
                                        <p id="pm-info-per-visit" class="text-base font-extrabold text-slate-900">$ 0</p>
                                    </div>
                                    <div class="pl-3">
                                        <p class="text-xs text-slate-400 font-medium mb-1">Accepted TX Plan</p>
                                        <p id="pm-info-tx-rate" class="text-base font-extrabold text-slate-900">0.00%</p>
                                    </div>
                                </div>
                                <!-- Row 3 -->
                                <div class="grid grid-cols-2 divide-x divide-slate-100 pt-3.5">
                                    <div class="pr-3">
                                        <p class="text-xs text-slate-400 font-medium mb-1">Patient Visits</p>
                                        <p id="pm-info-patient-visits" class="text-base font-extrabold text-slate-900">1</p>
                                    </div>
                                    <div class="pl-3">
                                        <p class="text-xs text-slate-400 font-medium mb-1">New Patient Visits</p>
                                        <p id="pm-info-new-visits" class="text-base font-extrabold text-slate-900">0</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Production Tab Panel -->
            <div id="pm-tab-production" class="pm-tab-panel hidden space-y-4">
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-white p-3.5 rounded-lg border border-slate-200/80 shadow-sm">
                        <p class="text-xs text-slate-400 font-medium mb-1">Net Production</p>
                        <p id="pm-prod-net" class="text-base font-extrabold text-slate-900">$ 0</p>
                    </div>
                    <div class="bg-white p-3.5 rounded-lg border border-slate-200/80 shadow-sm">
                        <p class="text-xs text-slate-400 font-medium mb-1">Avg. Production / Day</p>
                        <p id="pm-prod-avg" class="text-base font-extrabold text-slate-900">$ 0</p>
                    </div>
                    <div class="bg-white p-3.5 rounded-lg border border-slate-200/80 shadow-sm">
                        <p class="text-xs text-slate-400 font-medium mb-1">Production / Visit</p>
                        <p id="pm-prod-per-visit" class="text-base font-extrabold text-slate-900">$ 0</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg border border-slate-200/80 shadow-sm">
                    <canvas id="provModalProductionChart" class="w-full h-64"></canvas>
                </div>
            </div>

            <!-- 3. Visits Tab Panel -->
            <div id="pm-tab-visits" class="pm-tab-panel hidden space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-white p-3.5 rounded-lg border border-slate-200/80 shadow-sm">
                        <p class="text-xs text-slate-400 font-medium mb-1">Total Patient Visits</p>
                        <p id="pm-visits-total" class="text-base font-extrabold text-slate-900">0</p>
                    </div>
                    <div class="bg-white p-3.5 rounded-lg border border-slate-200/80 shadow-sm">
                        <p class="text-xs text-slate-400 font-medium mb-1">New Patient Visits</p>
                        <p id="pm-visits-new" class="text-base font-extrabold text-slate-900">0</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg border border-slate-200/80 shadow-sm">
                    <canvas id="provModalVisitsChart" class="w-full h-64"></canvas>
                </div>
            </div>

            <!-- 4. TX Accepted Tab Panel -->
            <div id="pm-tab-tx-accepted" class="pm-tab-panel hidden space-y-4">
                <div class="bg-white p-3.5 rounded-lg border border-slate-200/80 shadow-sm max-w-xs">
                    <p class="text-xs text-slate-400 font-medium mb-1">Accepted TX Plan</p>
                    <p id="pm-tx-rate" class="text-base font-extrabold text-slate-900">0.00%</p>
                </div>
                <div class="bg-white p-4 rounded-lg border border-slate-200/80 shadow-sm">
                    <canvas id="provModalTxChart" class="w-full h-64"></canvas>
                </div>
            </div>

        </div>
    </div>
</div>
</div>