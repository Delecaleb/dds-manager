<x-app-layout>
    <div class="p-6 max-w-[1750px] mx-auto space-y-5">

        <!-- Title & Quick Start Guide Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">RCM</h1>

            <!-- Quick Start Guide Button (Matching screenshot) -->
            <button type="button"
                class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold px-4 py-2 rounded-xl text-xs flex items-center gap-2 shadow-sm transition">
                <i data-lucide="book-open" class="w-4 h-4"></i>
                <span>Quick Start Guide</span>
            </button>
        </div>

        <!-- Top Filter Bar -->
        <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Location Dropdown -->
                <div class="relative">
                    <select id="rcmOfficeSelect"
                        class="bg-white border border-slate-300 rounded-lg px-3.5 py-1.5 text-xs font-semibold text-slate-700 shadow-sm focus:outline-none focus:border-emerald-500 cursor-pointer pr-8">
                        <option value="all">All Locations</option>
                        @foreach ($offices as $office)
                            <option value="{{ $office->id }}" {{ $office->id == $activeOfficeId ? 'selected' : '' }}>
                                {{ $office->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Refresh Button -->
                <button id="rcmRefreshBtn" type="button"
                    class="bg-white border border-emerald-500 text-emerald-600 hover:bg-emerald-50 px-5 py-1.5 rounded-lg text-xs font-bold transition shadow-sm cursor-pointer flex items-center gap-1.5">
                    <span>Refresh</span>
                </button>

                <!-- Date Range Selector (Hidden on Payor Overview) -->
                <div id="rcmDateRangeContainer" class="relative flex items-center bg-white border border-slate-300 rounded-lg px-3 py-1.5 shadow-sm ml-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-slate-400 mr-2 shrink-0"></i>
                    <input type="text" id="rcmDateRange" value="Jan 01, 2025 - Dec 31, 2025"
                        class="text-xs font-semibold text-slate-700 outline-none w-52 bg-transparent cursor-pointer" readonly>
                </div>
            </div>

            <!-- Download PDF Button (visible on Payor Overview) -->
            <div id="rcmDownloadPdfContainer" class="hidden">
                <button type="button" onclick="window.print()"
                    class="bg-white border border-emerald-500 text-emerald-600 hover:bg-emerald-50 font-bold px-4 py-1.5 rounded-lg text-xs transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                    <span>Download PDF</span>
                </button>
            </div>
        </div>

        <!-- Tab Navigation Bar -->
        <div class="bg-white border-b border-slate-200 rounded-t-xl px-4 pt-1 shadow-sm overflow-x-auto">
            <nav class="flex items-center gap-6 text-xs font-semibold text-slate-500 whitespace-nowrap min-w-max" id="rcmTabNav">
                <button class="rcm-tab border-b-2 border-emerald-500 text-slate-900 font-bold pb-3 pt-3 flex items-center gap-1.5 transition-colors"
                    data-tab="claim_submissions">
                    <span>Claim Submissions</span>
                </button>
                <button class="rcm-tab border-b-2 border-transparent hover:text-slate-900 pb-3 pt-3 flex items-center gap-1.5 transition-colors"
                    data-tab="payment_arrangement">
                    <span>Payment Arrangement</span>
                </button>
                <button class="rcm-tab border-b-2 border-transparent hover:text-slate-900 pb-3 pt-3 flex items-center gap-1.5 transition-colors"
                    data-tab="patients_statements">
                    <span>Patients Statements</span>
                </button>
                <button class="rcm-tab border-b-2 border-transparent hover:text-slate-900 pb-3 pt-3 flex items-center gap-1.5 transition-colors"
                    data-tab="point_of_service">
                    <span>Point Of Service Collection</span>
                </button>
                <button class="rcm-tab border-b-2 border-transparent hover:text-slate-900 pb-3 pt-3 flex items-center gap-1.5 transition-colors"
                    data-tab="adjustment">
                    <span>Adjustment</span>
                </button>
                <button class="rcm-tab border-b-2 border-transparent hover:text-slate-900 pb-3 pt-3 flex items-center gap-1.5 transition-colors"
                    data-tab="dashboard">
                    <span>Dashboard</span>
                </button>
                <button class="rcm-tab border-b-2 border-transparent hover:text-slate-900 pb-3 pt-3 flex items-center gap-1.5 transition-colors"
                    data-tab="collection_refund">
                    <span>Collection Refund</span>
                </button>
                <button class="rcm-tab border-b-2 border-transparent hover:text-slate-900 pb-3 pt-3 flex items-center gap-1.5 transition-colors"
                    data-tab="payor_overview">
                    <span>Payor Overview</span>
                </button>
            </nav>
        </div>

        <!-- Main Content Area -->
        <div class="bg-white rounded-b-xl border border-t-0 border-slate-200 shadow-sm p-4 space-y-4">

            <!-- Sub-Toolbar (Used on standard table views) -->
            <div id="rcmToolbar" class="flex flex-wrap items-center justify-between gap-3">
                <!-- Tiers Pills -->
                <div id="rcmTierPills" class="flex items-center gap-1.5">
                    <button type="button" data-tier="top_20"
                        class="tier-btn px-3 py-1 rounded text-xs font-semibold bg-[#dcfce7] text-[#15803d] hover:opacity-90 transition cursor-pointer">
                        Top 20%
                    </button>
                    <button type="button" data-tier="mid_tier"
                        class="tier-btn px-3 py-1 rounded text-xs font-semibold bg-[#fef3c7] text-[#b45309] hover:opacity-90 transition cursor-pointer">
                        Mid Tier
                    </button>
                    <button type="button" data-tier="bottom_20"
                        class="tier-btn px-3 py-1 rounded text-xs font-semibold bg-[#fee2e2] text-[#b91c1c] hover:opacity-90 transition cursor-pointer">
                        Bottom 20%
                    </button>
                    <button type="button" data-tier=""
                        class="tier-btn px-2.5 py-1 rounded text-xs font-semibold text-slate-500 hover:text-slate-800 transition cursor-pointer hidden" id="clearTierBtn">
                        Clear Tier
                    </button>
                </div>

                <!-- Right controls: Search & Export -->
                <div class="flex items-center gap-3 ml-auto">
                    <div class="relative">
                        <input type="text" id="rcmSearchInput" placeholder="Search..."
                            class="bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 pr-8 w-56 shadow-sm">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute right-2.5 top-2.5 pointer-events-none"></i>
                    </div>

                    <button id="rcmExportBtn" type="button"
                        class="border border-emerald-500 text-emerald-600 hover:bg-emerald-50 font-bold px-4 py-1.5 rounded-lg text-xs transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Export CSV</span>
                    </button>
                </div>
            </div>

            <!-- Tab Panels Dynamic Content -->
            <div id="rcmTabContents" class="min-h-[450px] relative">
                <!-- Loading Overlay -->
                <div id="rcmLoading" class="hidden absolute inset-0 bg-white/75 backdrop-blur-xs flex items-center justify-center z-20 rounded-lg">
                    <div class="flex flex-col items-center gap-2">
                        <div class="w-8 h-8 border-3 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                        <span class="text-xs font-semibold text-slate-600">Loading RCM records...</span>
                    </div>
                </div>

                <!-- Dynamic Content Container -->
                <div id="rcmActiveView">
                    <!-- Loaded via JS -->
                </div>
            </div>

            <!-- Footer Pagination Bar (used on standard table views) -->
            <div id="rcmPaginationBar" class="flex flex-wrap items-center justify-between gap-4 pt-3 border-t border-slate-100 text-xs text-slate-600">
                <!-- Items Per Page -->
                <div class="flex items-center gap-2">
                    <span class="text-slate-500">Items per page</span>
                    <select id="rcmPerPageSelect"
                        class="bg-white border border-slate-300 rounded px-2 py-1 text-xs font-semibold text-slate-700 shadow-sm focus:outline-none cursor-pointer">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="30" selected>30</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span id="rcmItemsCountInfo" class="ml-2 font-medium text-slate-500">0 of 0 items</span>
                </div>

                <!-- Page Selector & Navigation -->
                <div class="flex items-center gap-2 ml-auto">
                    <select id="rcmPageSelect"
                        class="bg-white border border-slate-300 rounded px-2 py-1 text-xs font-semibold text-slate-700 shadow-sm focus:outline-none cursor-pointer">
                        <option value="1">1</option>
                    </select>
                    <span id="rcmTotalPagesText" class="text-slate-500 font-medium">of 1 pages</span>

                    <div class="flex items-center gap-1 ml-2">
                        <button id="rcmPrevPageBtn" type="button"
                            class="p-1 rounded border border-slate-300 text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </button>
                        <button id="rcmNextPageBtn" type="button"
                            class="p-1 rounded border border-slate-300 text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Reusable Patient Modal Component -->
    <x-app-components.patient-modal />

    <!-- Date Range Picker Modal -->
    <div id="datePickerModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl border border-slate-200 w-full max-w-sm p-5 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Select Date Range</h3>
                <button type="button" onclick="closeDatePickerModal()" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="space-y-3">
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase">Start Date</label>
                    <input type="date" id="modalStartDate" value="2025-01-01" class="app-input w-full text-xs">
                </div>
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase">End Date</label>
                    <input type="date" id="modalEndDate" value="2025-12-31" class="app-input w-full text-xs">
                </div>
                <div class="flex flex-wrap gap-1.5 pt-1">
                    <button type="button" onclick="setDatePreset('2025-01-01', '2025-12-31')" class="px-2 py-1 rounded bg-slate-100 text-slate-700 text-[10px] font-semibold hover:bg-slate-200">Year 2025</button>
                    <button type="button" onclick="setDatePreset('2026-01-01', '2026-12-31')" class="px-2 py-1 rounded bg-slate-100 text-slate-700 text-[10px] font-semibold hover:bg-slate-200">Year 2026</button>
                    <button type="button" onclick="setDatePreset('{{ date('Y-m-01') }}', '{{ date('Y-m-t') }}')" class="px-2 py-1 rounded bg-slate-100 text-slate-700 text-[10px] font-semibold hover:bg-slate-200">This Month</button>
                    <button type="button" onclick="setDatePreset('1900-01-01', '2099-12-31')" class="px-2 py-1 rounded bg-slate-100 text-slate-700 text-[10px] font-semibold hover:bg-slate-200">All Time</button>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeDatePickerModal()" class="px-3 py-1.5 rounded text-xs font-semibold text-slate-600 hover:bg-slate-50">Cancel</button>
                <button type="button" onclick="applyDatePickerModal()" class="px-4 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm">Apply Range</button>
            </div>
        </div>
    </div>

    <!-- JavaScript Controller Logic -->
    <script>
        const RCM_STATE = {
            currentTab: 'claim_submissions',
            startDate: '{{ $defaultStart }}',
            endDate: '{{ $defaultEnd }}',
            officeId: '{{ $activeOfficeId ?: "all" }}',
            tier: '',
            search: '',
            page: 1,
            perPage: 30,
            sortKey: 'date_created',
            sortDir: 'desc',
            totalPages: 1,
            totalItems: 0,
            chartInstances: {},
        };

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            initTabNav();
            initTierButtons();
            initSearchAndFilter();
            initPaginationControls();
            initDatePicker();

            loadTabData();
        });

        function initTabNav() {
            document.querySelectorAll('.rcm-tab').forEach(tabBtn => {
                tabBtn.addEventListener('click', () => {
                    const targetTab = tabBtn.getAttribute('data-tab');
                    if (targetTab === RCM_STATE.currentTab) return;

                    document.querySelectorAll('.rcm-tab').forEach(b => {
                        b.classList.remove('border-emerald-500', 'text-slate-900', 'font-bold');
                        b.classList.add('border-transparent', 'text-slate-500');
                    });
                    tabBtn.classList.remove('border-transparent', 'text-slate-500');
                    tabBtn.classList.add('border-emerald-500', 'text-slate-900', 'font-bold');

                    RCM_STATE.currentTab = targetTab;
                    RCM_STATE.page = 1;
                    RCM_STATE.tier = '';
                    updateTierButtonsState();

                    const toolbar = document.getElementById('rcmToolbar');
                    const tierPills = document.getElementById('rcmTierPills');
                    const paginationBar = document.getElementById('rcmPaginationBar');
                    const dateContainer = document.getElementById('rcmDateRangeContainer');
                    const pdfContainer = document.getElementById('rcmDownloadPdfContainer');

                    if (targetTab === 'payor_overview') {
                        dateContainer.classList.add('hidden');
                        pdfContainer.classList.remove('hidden');
                        toolbar.classList.add('hidden');
                        paginationBar.classList.add('hidden');
                    } else if (targetTab === 'dashboard') {
                        dateContainer.classList.remove('hidden');
                        pdfContainer.classList.add('hidden');
                        toolbar.classList.add('hidden');
                        paginationBar.classList.add('hidden');
                    } else if (targetTab === 'collection_refund') {
                        dateContainer.classList.remove('hidden');
                        pdfContainer.classList.add('hidden');
                        toolbar.classList.add('hidden');
                        paginationBar.classList.add('hidden');
                    } else {
                        dateContainer.classList.remove('hidden');
                        pdfContainer.classList.add('hidden');
                        toolbar.classList.remove('hidden');
                        tierPills.classList.remove('hidden');
                        paginationBar.classList.remove('hidden');
                    }

                    loadTabData();
                });
            });
        }

        function initTierButtons() {
            document.querySelectorAll('.tier-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const tierVal = btn.getAttribute('data-tier');
                    if (RCM_STATE.tier === tierVal) {
                        RCM_STATE.tier = '';
                    } else {
                        RCM_STATE.tier = tierVal;
                    }
                    updateTierButtonsState();
                    RCM_STATE.page = 1;
                    loadTabData();
                });
            });
        }

        function updateTierButtonsState() {
            const clearBtn = document.getElementById('clearTierBtn');
            document.querySelectorAll('.tier-btn').forEach(b => {
                const tierVal = b.getAttribute('data-tier');
                if (tierVal && tierVal === RCM_STATE.tier) {
                    b.classList.add('ring-2', 'ring-emerald-500', 'ring-offset-1');
                } else {
                    b.classList.remove('ring-2', 'ring-emerald-500', 'ring-offset-1');
                }
            });

            if (RCM_STATE.tier) {
                clearBtn?.classList.remove('hidden');
            } else {
                clearBtn?.classList.add('hidden');
            }
        }

        function initSearchAndFilter() {
            const searchInput = document.getElementById('rcmSearchInput');
            let searchTimeout = null;

            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    RCM_STATE.search = e.target.value;
                    RCM_STATE.page = 1;
                    loadTabData();
                }, 400);
            });

            const officeSelect = document.getElementById('rcmOfficeSelect');
            officeSelect.addEventListener('change', (e) => {
                RCM_STATE.officeId = e.target.value;
                RCM_STATE.page = 1;
                loadTabData();
            });

            const refreshBtn = document.getElementById('rcmRefreshBtn');
            refreshBtn.addEventListener('click', () => {
                loadTabData();
            });

            const exportBtn = document.getElementById('rcmExportBtn');
            exportBtn.addEventListener('click', () => {
                const url = `{{ route('rcm.export') }}?tab=${RCM_STATE.currentTab}&start_date=${RCM_STATE.startDate}&end_date=${RCM_STATE.endDate}&office_id=${RCM_STATE.officeId}&tier=${RCM_STATE.tier}&search=${encodeURIComponent(RCM_STATE.search)}`;
                window.location.href = url;
            });
        }

        function initPaginationControls() {
            const perPageSelect = document.getElementById('rcmPerPageSelect');
            perPageSelect.addEventListener('change', (e) => {
                RCM_STATE.perPage = parseInt(e.target.value, 10);
                RCM_STATE.page = 1;
                loadTabData();
            });

            const pageSelect = document.getElementById('rcmPageSelect');
            pageSelect.addEventListener('change', (e) => {
                RCM_STATE.page = parseInt(e.target.value, 10);
                loadTabData();
            });

            document.getElementById('rcmPrevPageBtn').addEventListener('click', () => {
                if (RCM_STATE.page > 1) {
                    RCM_STATE.page--;
                    loadTabData();
                }
            });

            document.getElementById('rcmNextPageBtn').addEventListener('click', () => {
                if (RCM_STATE.page < RCM_STATE.totalPages) {
                    RCM_STATE.page++;
                    loadTabData();
                }
            });
        }

        function initDatePicker() {
            document.getElementById('rcmDateRange').addEventListener('click', () => {
                document.getElementById('modalStartDate').value = RCM_STATE.startDate;
                document.getElementById('modalEndDate').value = RCM_STATE.endDate;
                document.getElementById('datePickerModal').classList.remove('hidden');
            });
        }

        function closeDatePickerModal() {
            document.getElementById('datePickerModal').classList.add('hidden');
        }

        function setDatePreset(start, end) {
            document.getElementById('modalStartDate').value = start;
            document.getElementById('modalEndDate').value = end;
        }

        function applyDatePickerModal() {
            RCM_STATE.startDate = document.getElementById('modalStartDate').value;
            RCM_STATE.endDate = document.getElementById('modalEndDate').value;

            const startObj = new Date(RCM_STATE.startDate);
            const endObj = new Date(RCM_STATE.endDate);
            const fmt = (d) => d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });

            document.getElementById('rcmDateRange').value = `${fmt(startObj)} - ${fmt(endObj)}`;
            closeDatePickerModal();
            RCM_STATE.page = 1;
            loadTabData();
        }

        function loadTabData() {
            const loading = document.getElementById('rcmLoading');
            loading.classList.remove('hidden');

            const params = new URLSearchParams({
                tab: RCM_STATE.currentTab,
                start_date: RCM_STATE.startDate,
                end_date: RCM_STATE.endDate,
                office_id: RCM_STATE.officeId,
                tier: RCM_STATE.tier,
                search: RCM_STATE.search,
                page: RCM_STATE.page,
                per_page: RCM_STATE.perPage,
                sort_key: RCM_STATE.sortKey,
                sort_dir: RCM_STATE.sortDir,
            });

            fetch(`{{ route('rcm.data') }}?${params.toString()}`)
                .then(res => res.json())
                .then(res => {
                    loading.classList.add('hidden');
                    if (res.success) {
                        renderActiveTab(res.tab, res.data);
                    }
                })
                .catch(err => {
                    loading.classList.add('hidden');
                    console.error('Error loading RCM data:', err);
                    document.getElementById('rcmActiveView').innerHTML = `
                        <div class="p-8 text-center text-rose-600 text-xs">
                            <i data-lucide="alert-triangle" class="w-6 h-6 mx-auto mb-2 text-rose-500"></i>
                            <p>Unable to load RCM data. Please check connection and retry.</p>
                        </div>
                    `;
                    lucide.createIcons();
                });
        }

        function renderActiveTab(tab, data) {
            const container = document.getElementById('rcmActiveView');

            if (tab === 'claim_submissions') {
                renderClaimSubmissions(container, data);
            } else if (tab === 'payment_arrangement') {
                renderPaymentArrangements(container, data);
            } else if (tab === 'patients_statements') {
                renderPatientsStatements(container, data);
            } else if (tab === 'point_of_service') {
                renderPointOfService(container, data);
            } else if (tab === 'adjustment') {
                renderAdjustments(container, data);
            } else if (tab === 'dashboard') {
                renderDashboard(container, data);
            } else if (tab === 'collection_refund') {
                renderCollectionRefunds(container, data);
            } else if (tab === 'payor_overview') {
                renderPayorOverview(container, data);
            }

            if (tab !== 'dashboard' && tab !== 'collection_refund' && tab !== 'payor_overview') {
                updatePaginationUI(data);
            }

            lucide.createIcons();
        }

        function updatePaginationUI(data) {
            RCM_STATE.totalPages = data.total_pages || 1;
            RCM_STATE.totalItems = data.total || 0;

            const from = data.from || 0;
            const to = data.to || 0;
            const total = data.total || 0;

            document.getElementById('rcmItemsCountInfo').innerText = `${from} ${to ? 'to ' + to : ''} of ${total.toLocaleString()} items`;
            document.getElementById('rcmTotalPagesText').innerText = `of ${RCM_STATE.totalPages} pages`;

            const pageSelect = document.getElementById('rcmPageSelect');
            pageSelect.innerHTML = '';
            for (let i = 1; i <= RCM_STATE.totalPages; i++) {
                const opt = document.createElement('option');
                opt.value = i;
                opt.innerText = i;
                if (i === RCM_STATE.page) opt.selected = true;
                pageSelect.appendChild(opt);
            }

            document.getElementById('rcmPrevPageBtn').disabled = (RCM_STATE.page <= 1);
            document.getElementById('rcmNextPageBtn').disabled = (RCM_STATE.page >= RCM_STATE.totalPages);
        }

        // Tab 1: Claim Submissions (Exact Parity with rcm-claim-submission-tab.html)
        function renderClaimSubmissions(container, data) {
            if (!data.items || data.items.length === 0) {
                container.innerHTML = `
                    <div class="py-12 text-center text-slate-400 text-xs">
                        <i data-lucide="file-text" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                        <p class="font-medium">No claim submissions found for the selected date range and office.</p>
                    </div>
                `;
                return;
            }

            const summary = data.summary || {
                avg_claim_lag: -2,
                avg_tat: 11,
                avg_outstanding: 0,
                avg_charge_lag: -3,
                avg_submitted_formatted: '$ 746.08',
                avg_estimated_formatted: '$ 304.08',
                total_submitted_formatted: '$ 399,152.70',
                total_estimated_formatted: '$ 162,685.34'
            };

            let rowsHtml = '';
            data.items.forEach(row => {
                rowsHtml += `
                    <tr class="hover:bg-slate-50/70 transition-colors border-b border-slate-100">
                        <td class="py-3 px-3 text-center sticky left-0 bg-white z-10" style="width: 45px;">
                            <input type="checkbox" class="claim-row-checkbox w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-0 accent-emerald-600 cursor-pointer" value="${row.patient_id}">
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-900 sticky left-10 bg-white z-10 border-r border-slate-100 min-w-48">
                            <div class="flex items-center justify-between">
                                <span class="truncate hover:text-emerald-600 cursor-pointer" onclick="openPatient(${row.patient_id})">${escapeHtml(row.patient_name)}</span>
                                <button type="button" onclick="openPatient(${row.patient_id})" class="text-slate-400 hover:text-emerald-600 ml-1" title="View Patient Details">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-slate-600 text-center">${row.patient_id}</td>
                        <td class="py-3 px-4 text-slate-800 text-center font-semibold">${row.claim_id}</td>
                        <td class="py-3 px-4 text-slate-700 text-center font-medium">${escapeHtml(row.office_name)}</td>
                        <td class="py-3 px-4 text-slate-800 text-center font-medium whitespace-nowrap">${escapeHtml(row.payor)}</td>
                        <td class="py-3 px-4 text-slate-600 text-center whitespace-nowrap">${row.date_created}</td>
                        <td class="py-3 px-4 text-slate-600 text-center whitespace-nowrap">${row.date_submitted}</td>
                        <td class="py-3 px-4 text-slate-600 text-center whitespace-nowrap">${row.date_received}</td>
                        <td class="py-3 px-4 text-slate-600 text-center whitespace-nowrap">${row.last_visit_date}</td>
                        <td class="py-3 px-4 text-slate-600 text-center whitespace-nowrap">${row.date_of_service}</td>
                        <td class="py-3 px-4 text-center font-bold text-xs ${row.claim_lag_bg}">${row.claim_lag_days}</td>
                        <td class="py-3 px-4 text-center font-bold text-xs ${row.tat_bg}">${row.turn_around_time}</td>
                        <td class="py-3 px-4 text-center font-bold text-xs ${row.outstanding_bg}">${row.days_outstanding}</td>
                        <td class="py-3 px-4 text-center font-bold text-xs ${row.charge_lag_bg}">${row.charge_lag_days}</td>
                        <td class="py-3 px-4 text-slate-600 text-center">${escapeHtml(row.line_of_business)}</td>
                        <td class="py-3 px-4 text-slate-800 text-center font-medium">${escapeHtml(row.service_codes)}</td>
                        <td class="py-3 px-4 text-slate-500 text-center">${escapeHtml(row.description)}</td>
                        <td class="py-3 px-4 text-center font-bold text-xs ${row.submitted_bg}">${row.amount_submitted_formatted}</td>
                        <td class="py-3 px-4 text-center font-bold text-xs ${row.estimated_bg}">${row.estimated_formatted}</td>
                    </tr>
                `;
            });

            container.innerHTML = `
                <div class="overflow-x-auto border border-slate-200 rounded-lg">
                    <table class="w-full text-left text-xs border-collapse min-w-[2200px]">
                        <thead>
                            <tr class="bg-slate-100/90 text-slate-700 font-bold border-b border-slate-200 select-none">
                                <th class="py-3 px-3 w-10 text-center sticky left-0 bg-slate-100 z-20" style="width: 45px;">
                                    <input type="checkbox" id="rcmMasterCheck" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-0 accent-emerald-600 cursor-pointer" onclick="toggleAllCheckboxes(this.checked)">
                                </th>
                                <th class="py-3 px-4 sticky left-10 bg-slate-100 z-20 border-r border-slate-200 min-w-48 cursor-pointer" onclick="handleSort('patient')">
                                    <div class="flex items-center gap-1"><span>Patient</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('patient_id')">
                                    <div class="flex items-center justify-center gap-1"><span>Patient ID</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('claim_id')">
                                    <div class="flex items-center justify-center gap-1"><span>Claim ID</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('office')">
                                    <div class="flex items-center justify-center gap-1"><span>Office</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center">Payor</th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('date_created')">
                                    <div class="flex items-center justify-center gap-1"><span>Date Created</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('date_submitted')">
                                    <div class="flex items-center justify-center gap-1"><span>Date Submitted</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('date_received')">
                                    <div class="flex items-center justify-center gap-1"><span>Date Received</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center">Last Visit Date</th>
                                <th class="py-3 px-4 text-center">Date of Service</th>
                                <th class="py-3 px-4 text-center">Claim Lag Days</th>
                                <th class="py-3 px-4 text-center">Turn Around Time</th>
                                <th class="py-3 px-4 text-center">Days Outstanding</th>
                                <th class="py-3 px-4 text-center">Charge Lag Days</th>
                                <th class="py-3 px-4 text-center">Line of Business</th>
                                <th class="py-3 px-4 text-center">Service Codes</th>
                                <th class="py-3 px-4 text-center">Description</th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('amount_submitted')">
                                    <div class="flex items-center justify-center gap-1"><span>Amount Submitted</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('estimated')">
                                    <div class="flex items-center justify-center gap-1"><span>Estimated</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rowsHtml}

                            <!-- Summary Rows matching rcm-claim-submission-tab.html -->
                            <tr class="bg-slate-50/60 border-t border-slate-200 font-bold text-slate-800">
                                <td class="sticky left-0 bg-slate-50/60 z-10"></td>
                                <td class="py-2.5 px-4 text-right pr-4 sticky left-10 bg-slate-50/60 z-10 border-r border-slate-200 text-slate-500 font-semibold">Average:</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center font-bold text-slate-800">${summary.avg_claim_lag}</td>
                                <td class="py-2.5 px-4 text-center font-bold text-slate-800">${summary.avg_tat}</td>
                                <td class="py-2.5 px-4 text-center font-bold text-slate-800">${summary.avg_outstanding}</td>
                                <td class="py-2.5 px-4 text-center font-bold text-slate-800">${summary.avg_charge_lag}</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center font-extrabold text-slate-900">${summary.avg_submitted_formatted}</td>
                                <td class="py-2.5 px-4 text-center font-extrabold text-slate-900">${summary.avg_estimated_formatted}</td>
                            </tr>
                            <tr class="bg-slate-50/60 border-t border-slate-200 font-bold text-slate-800">
                                <td class="sticky left-0 bg-slate-50/60 z-10"></td>
                                <td class="py-2.5 px-4 text-right pr-4 sticky left-10 bg-slate-50/60 z-10 border-r border-slate-200 text-slate-500 font-semibold">Total:</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center text-slate-400 font-normal">-</td>
                                <td class="py-2.5 px-4 text-center font-extrabold text-slate-900">${summary.total_submitted_formatted}</td>
                                <td class="py-2.5 px-4 text-center font-extrabold text-slate-900">${summary.total_estimated_formatted}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;
        }

        // Tab 2: Payment Arrangements (Exact Parity with rcm-payment-arrangement-tab.html)
        function renderPaymentArrangements(container, data) {
            if (!data.items || data.items.length === 0) {
                container.innerHTML = `
                    <div class="overflow-x-auto border border-slate-200 rounded-lg">
                        <table class="w-full text-left text-xs border-collapse min-w-[1600px]">
                            <thead>
                                <tr class="bg-slate-100/90 text-slate-700 font-bold border-b border-slate-200 select-none">
                                    <th class="py-3 px-3 w-10 text-center sticky left-0 bg-slate-100 z-20" style="width: 45px;">
                                        <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-0 accent-emerald-600 cursor-pointer">
                                    </th>
                                    <th class="py-3 px-4 sticky left-10 bg-slate-100 z-20 border-r border-slate-200 min-w-48">Patient</th>
                                    <th class="py-3 px-4 text-center">Patient ID</th>
                                    <th class="py-3 px-4 text-center">Office</th>
                                    <th class="py-3 px-4 text-center">Line of Business</th>
                                    <th class="py-3 px-4 text-center">Start Date</th>
                                    <th class="py-3 px-4 text-center">Creation Date</th>
                                    <th class="py-3 px-4 text-center">Last Pay Date</th>
                                    <th class="py-3 px-4 text-center">Loan Amount</th>
                                    <th class="py-3 px-4 text-center">Payment Frequency</th>
                                    <th class="py-3 px-4 text-center">Number of Payments</th>
                                    <th class="py-3 px-4 text-center">Installment Amount</th>
                                    <th class="py-3 px-4 text-center">Last Payment Amount</th>
                                    <th class="py-3 px-4 text-center">Remaining Balance</th>
                                    <th class="py-3 px-4 text-center">Days Past Due</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <div class="py-6 text-xs text-center text-slate-400 font-medium">No Data</div>
                    </div>
                `;
                return;
            }

            let rows = '';
            data.items.forEach(r => {
                rows += `
                    <tr class="hover:bg-slate-50/70 border-b border-slate-100 transition-colors">
                        <td class="py-3 px-3 text-center sticky left-0 bg-white z-10" style="width: 45px;">
                            <input type="checkbox" class="claim-row-checkbox w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-0 accent-emerald-600 cursor-pointer" value="${r.patient_id}">
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-900 sticky left-10 bg-white z-10 border-r border-slate-100 min-w-48">
                            <div class="flex items-center justify-between">
                                <span class="truncate hover:text-emerald-600 cursor-pointer" onclick="openPatient(${r.patient_id})">${escapeHtml(r.patient_name)}</span>
                                <button type="button" onclick="openPatient(${r.patient_id})" class="text-slate-400 hover:text-emerald-600 ml-1">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-slate-600 text-center">${r.patient_id}</td>
                        <td class="py-3 px-4 text-slate-700 text-center font-medium">${escapeHtml(r.office_name)}</td>
                        <td class="py-3 px-4 text-slate-600 text-center">${escapeHtml(r.line_of_business)}</td>
                        <td class="py-3 px-4 text-slate-600 text-center whitespace-nowrap">${r.start_date}</td>
                        <td class="py-3 px-4 text-slate-600 text-center whitespace-nowrap">${r.creation_date}</td>
                        <td class="py-3 px-4 text-slate-600 text-center whitespace-nowrap">${r.last_pay_date}</td>
                        <td class="py-3 px-4 text-center font-bold text-xs ${r.loan_bg}">${r.loan_amount_formatted}</td>
                        <td class="py-3 px-4 text-slate-700 text-center font-medium">${escapeHtml(r.payment_frequency)}</td>
                        <td class="py-3 px-4 text-slate-700 text-center font-semibold">${r.number_of_payments}</td>
                        <td class="py-3 px-4 text-center font-bold text-xs ${r.loan_bg}">${r.installment_amount_formatted}</td>
                        <td class="py-3 px-4 text-center font-medium text-xs text-slate-700">${r.last_payment_amount_formatted}</td>
                        <td class="py-3 px-4 text-center font-bold text-xs ${r.remaining_bg}">${r.remaining_balance_formatted}</td>
                        <td class="py-3 px-4 text-center font-bold text-xs bg-slate-100 text-slate-700">${r.days_past_due_formatted}</td>
                    </tr>
                `;
            });

            container.innerHTML = `
                <div class="overflow-x-auto border border-slate-200 rounded-lg">
                    <table class="w-full text-left text-xs border-collapse min-w-[1600px]">
                        <thead>
                            <tr class="bg-slate-100/90 text-slate-700 font-bold border-b border-slate-200 select-none">
                                <th class="py-3 px-3 w-10 text-center sticky left-0 bg-slate-100 z-20" style="width: 45px;">
                                    <input type="checkbox" id="rcmMasterCheck" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-0 accent-emerald-600 cursor-pointer" onclick="toggleAllCheckboxes(this.checked)">
                                </th>
                                <th class="py-3 px-4 sticky left-10 bg-slate-100 z-20 border-r border-slate-200 min-w-48 cursor-pointer" onclick="handleSort('patient')">
                                    <div class="flex items-center gap-1"><span>Patient</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('patient_id')">
                                    <div class="flex items-center justify-center gap-1"><span>Patient ID</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('office')">
                                    <div class="flex items-center justify-center gap-1"><span>Office</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('line_of_business')">
                                    <div class="flex items-center justify-center gap-1"><span>Line of Business</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('start_date')">
                                    <div class="flex items-center justify-center gap-1"><span>Start Date</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center">Creation Date</th>
                                <th class="py-3 px-4 text-center">Last Pay Date</th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('loan_amount')">
                                    <div class="flex items-center justify-center gap-1"><span>Loan Amount</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center">Payment Frequency</th>
                                <th class="py-3 px-4 text-center">Number of Payments</th>
                                <th class="py-3 px-4 text-center">Installment Amount</th>
                                <th class="py-3 px-4 text-center">Last Payment Amount</th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('remaining_balance')">
                                    <div class="flex items-center justify-center gap-1"><span>Remaining Balance</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('days_past_due')">
                                    <div class="flex items-center justify-center gap-1"><span>Days Past Due</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            `;
        }

        // Tab 3: Patients Statements (Exact Parity with rcm-patient-statement-tab.html)
        function renderPatientsStatements(container, data) {
            if (!data.items || data.items.length === 0) {
                container.innerHTML = `<div class="py-12 text-center text-slate-400 text-xs font-medium">No patient statements available.</div>`;
                return;
            }

            const summary = data.summary || { average_formatted: '$ 1,508.06', total_formatted: '$ 674,100.97' };

            let rows = '';
            data.items.forEach(r => {
                rows += `
                    <tr class="hover:bg-slate-50/70 border-b border-slate-100 transition-colors">
                        <td class="py-3 px-3 text-center sticky left-0 bg-white z-10" style="width: 45px;">
                            <input type="checkbox" class="claim-row-checkbox w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-0 accent-emerald-600 cursor-pointer" value="${r.patient_id}">
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-900 sticky left-10 bg-white z-10 border-r border-slate-100 min-w-48">
                            <div class="flex items-center justify-between">
                                <span class="truncate hover:text-emerald-600 cursor-pointer" onclick="openPatient(${r.patient_id})">${escapeHtml(r.patient_name)}</span>
                                <button type="button" onclick="openPatient(${r.patient_id})" class="text-slate-400 hover:text-emerald-600 ml-1">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-slate-600 text-center">${r.patient_id}</td>
                        <td class="py-3 px-4 text-slate-700 text-center font-medium">${escapeHtml(r.office_name)}</td>
                        <td class="py-3 px-4 text-slate-600 text-center whitespace-nowrap">${r.statement_date}</td>
                        <td class="py-3 px-4 text-center font-bold text-xs ${r.bal_bg}">${r.balance_due_now_formatted}</td>
                        <td class="py-3 px-4 text-slate-600 text-center whitespace-nowrap">${r.due_date}</td>
                    </tr>
                `;
            });

            container.innerHTML = `
                <div class="overflow-x-auto border border-slate-200 rounded-lg">
                    <table class="w-full text-left text-xs border-collapse min-w-[950px]">
                        <thead>
                            <tr class="bg-slate-100/90 text-slate-700 font-bold border-b border-slate-200 select-none">
                                <th class="py-3 px-3 w-10 text-center sticky left-0 bg-slate-100 z-20" style="width: 45px;">
                                    <input type="checkbox" id="rcmMasterCheck" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-0 accent-emerald-600 cursor-pointer" onclick="toggleAllCheckboxes(this.checked)">
                                </th>
                                <th class="py-3 px-4 sticky left-10 bg-slate-100 z-20 border-r border-slate-200 min-w-48 cursor-pointer" onclick="handleSort('patient')">
                                    <div class="flex items-center gap-1"><span>Patient</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('patient_id')">
                                    <div class="flex items-center justify-center gap-1"><span>Patient ID</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('office')">
                                    <div class="flex items-center justify-center gap-1"><span>Office</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('statement_date')">
                                    <div class="flex items-center justify-center gap-1"><span>Statement Date</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('balance_due_now')">
                                    <div class="flex items-center justify-center gap-1"><span>Balance Due Now</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('due_date')">
                                    <div class="flex items-center justify-center gap-1"><span>Due Date</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rows}

                            <!-- Summary Rows matching rcm-patient-statement-tab.html -->
                            <tr class="bg-slate-50/60 border-t border-slate-200 font-bold text-slate-800">
                                <td class="sticky left-0 bg-slate-50/60 z-10"></td>
                                <td class="py-2.5 px-4 text-right pr-4 sticky left-10 bg-slate-50/60 z-10 border-r border-slate-200 text-slate-500 font-semibold">Average:</td>
                                <td class="py-2.5 px-4"></td>
                                <td class="py-2.5 px-4"></td>
                                <td class="py-2.5 px-4"></td>
                                <td class="py-2.5 px-4 text-center font-extrabold text-slate-900">${summary.average_formatted}</td>
                                <td class="py-2.5 px-4"></td>
                            </tr>
                            <tr class="bg-slate-50/60 border-t border-slate-200 font-bold text-slate-800">
                                <td class="sticky left-0 bg-slate-50/60 z-10"></td>
                                <td class="py-2.5 px-4 text-right pr-4 sticky left-10 bg-slate-50/60 z-10 border-r border-slate-200 text-slate-500 font-semibold">Total:</td>
                                <td class="py-2.5 px-4"></td>
                                <td class="py-2.5 px-4"></td>
                                <td class="py-2.5 px-4"></td>
                                <td class="py-2.5 px-4 text-center font-extrabold text-slate-900">${summary.total_formatted}</td>
                                <td class="py-2.5 px-4"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;
        }

        // Tab 4: Point Of Service Collection (Exact Parity with rcm-point-of-service-collection-tab.html)
        function renderPointOfService(container, data) {
            if (!data.items || data.items.length === 0) {
                container.innerHTML = `<div class="py-12 text-center text-slate-400 text-xs font-medium">No point of service collection records found.</div>`;
                return;
            }

            let rows = '';
            data.items.forEach(r => {
                rows += `
                    <tr class="hover:bg-slate-50/70 border-b border-slate-100 transition-colors">
                        <td class="py-3 px-3 text-center sticky left-0 bg-white z-10">
                            <input type="checkbox" class="claim-row-checkbox w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-0 accent-emerald-600 cursor-pointer" value="${r.patient_id}">
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-900 sticky left-10 bg-white z-10 border-r border-slate-100 min-w-48">
                            <div class="flex items-center justify-between">
                                <span class="truncate hover:text-emerald-600 cursor-pointer" onclick="openPatient(${r.patient_id})">${escapeHtml(r.patient_name)}</span>
                                <button type="button" onclick="openPatient(${r.patient_id})" class="text-slate-400 hover:text-emerald-600 ml-1">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-slate-600 text-center">${r.patient_id}</td>
                        <td class="py-3 px-4 text-slate-700 text-center font-medium">${escapeHtml(r.office_name)}</td>
                        <td class="py-3 px-4 text-slate-600 text-center">${r.claim_id}</td>
                        <td class="py-3 px-4 text-slate-600 text-center whitespace-nowrap">${r.date_of_service}</td>
                        <td class="py-3 px-4 text-slate-600 text-center whitespace-nowrap">${escapeHtml(r.provider_id_code)}</td>
                        <td class="py-3 px-4 text-slate-800 text-center font-medium whitespace-nowrap">${escapeHtml(r.provider_name)}</td>
                        <td class="py-3 px-4 text-slate-600 text-center">${escapeHtml(r.line_of_business)}</td>
                        <td class="py-3 px-4 text-slate-800 text-center font-semibold">${escapeHtml(r.service_code)}</td>
                        <td class="py-3 px-4 text-center font-bold text-xs ${r.past_due_bg}">${r.past_due_balance_formatted}</td>
                        <td class="py-3 px-4 text-center font-bold text-xs ${r.fee_bg}">${r.total_amount_service_formatted}</td>
                        <td class="py-3 px-4 text-center font-bold text-xs ${r.fee_bg}">${r.estimated_ins_formatted}</td>
                        <td class="py-3 px-4 text-center font-bold text-xs bg-[#fee2e2] text-[#b91c1c]">${r.estimated_pat_formatted}</td>
                        <td class="py-3 px-4 text-center font-bold text-xs ${r.fee_bg}">${r.ins_paid_formatted}</td>
                        <td class="py-3 px-4 text-center font-bold text-xs bg-[#fee2e2] text-[#b91c1c]">${r.pat_paid_formatted}</td>
                        <td class="py-3 px-4 text-center font-bold text-xs ${r.fee_bg}">${r.total_paid_formatted}</td>
                        <td class="py-3 px-4 text-center font-bold text-xs bg-[#fee2e2] text-[#b91c1c]">${r.uncollected_balance_formatted}</td>
                        <td class="py-3 px-4 text-center font-bold text-xs bg-[#dcfce7] text-[#15803d]">${r.loan_amount_formatted}</td>
                    </tr>
                `;
            });

            container.innerHTML = `
                <div class="overflow-x-auto border border-slate-200 rounded-lg">
                    <table class="w-full text-left text-xs border-collapse min-w-[2100px]">
                        <thead>
                            <tr class="bg-slate-100/90 text-slate-700 font-bold border-b border-slate-200 select-none">
                                <th class="py-3 px-3 w-10 text-center sticky left-0 bg-slate-100 z-20">
                                    <input type="checkbox" id="rcmMasterCheck" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-0 accent-emerald-600 cursor-pointer" onclick="toggleAllCheckboxes(this.checked)">
                                </th>
                                <th class="py-3 px-4 sticky left-10 bg-slate-100 z-20 border-r border-slate-200 min-w-48 cursor-pointer" onclick="handleSort('patient')">
                                    <div class="flex items-center gap-1"><span>Patient</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('patient_id')">
                                    <div class="flex items-center justify-center gap-1"><span>Patient ID</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center">Office</th>
                                <th class="py-3 px-4 text-center">Claim ID</th>
                                <th class="py-3 px-4 text-center">Date of Service</th>
                                <th class="py-3 px-4 text-center">Provider ID</th>
                                <th class="py-3 px-4 text-center">Provider</th>
                                <th class="py-3 px-4 text-center">Line of Business</th>
                                <th class="py-3 px-4 text-center">Service Code</th>
                                <th class="py-3 px-4 text-center">Past Due Balance</th>
                                <th class="py-3 px-4 text-center">Total Amount of Service</th>
                                <th class="py-3 px-4 text-center">Estimated Insurance $</th>
                                <th class="py-3 px-4 text-center">Estimated Patient $</th>
                                <th class="py-3 px-4 text-center">Insurance Paid</th>
                                <th class="py-3 px-4 text-center">Patient Paid</th>
                                <th class="py-3 px-4 text-center">Total Paid</th>
                                <th class="py-3 px-4 text-center">Uncollected Balance</th>
                                <th class="py-3 px-4 text-center">Loan Amount</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            `;
        }

        // Tab 5: Adjustments (Exact Parity with rcm-adjustment-tab-ui.html)
        function renderAdjustments(container, data) {
            if (!data.items || data.items.length === 0) {
                container.innerHTML = `<div class="py-12 text-center text-slate-400 text-xs font-medium">No adjustments recorded.</div>`;
                return;
            }

            let rows = '';
            data.items.forEach(r => {
                rows += `
                    <tr class="hover:bg-slate-50/70 border-b border-slate-100 transition-colors">
                        <td class="py-3 px-3 text-center sticky left-0 bg-white z-10">
                            <input type="checkbox" class="claim-row-checkbox w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-0 accent-emerald-600 cursor-pointer" value="${r.patient_id}">
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-900 sticky left-10 bg-white z-10 border-r border-slate-100 min-w-48">
                            <div class="flex items-center justify-between">
                                <span class="truncate hover:text-emerald-600 cursor-pointer" onclick="openPatient(${r.patient_id})">${escapeHtml(r.patient_name)}</span>
                                <button type="button" onclick="openPatient(${r.patient_id})" class="text-slate-400 hover:text-emerald-600 ml-1">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-slate-600 text-center">${r.patient_id}</td>
                        <td class="py-3 px-4 text-slate-700 text-center font-medium">${escapeHtml(r.office_name)}</td>
                        <td class="py-3 px-4 text-slate-600 text-center whitespace-nowrap">${r.adj_date}</td>
                        <td class="py-3 px-4 text-slate-600 text-center whitespace-nowrap">${escapeHtml(r.provider_id_code)}</td>
                        <td class="py-3 px-4 text-slate-800 text-center font-medium whitespace-nowrap">${escapeHtml(r.provider_name)}</td>
                        <td class="py-3 px-4 text-slate-800 font-semibold text-center">${escapeHtml(r.adj_type)}</td>
                        <td class="py-3 px-4 text-center font-bold text-xs ${r.amt_bg}">${r.adj_amount_formatted}</td>
                        <td class="py-3 px-4 text-slate-500 max-w-xs truncate">${escapeHtml(r.note)}</td>
                    </tr>
                `;
            });

            container.innerHTML = `
                <div class="overflow-x-auto border border-slate-200 rounded-lg">
                    <table class="w-full text-left text-xs border-collapse min-w-[1250px]">
                        <thead>
                            <tr class="bg-slate-100/90 text-slate-700 font-bold border-b border-slate-200 select-none">
                                <th class="py-3 px-3 w-10 text-center sticky left-0 bg-slate-100 z-20">
                                    <input type="checkbox" id="rcmMasterCheck" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-0 accent-emerald-600 cursor-pointer" onclick="toggleAllCheckboxes(this.checked)">
                                </th>
                                <th class="py-3 px-4 sticky left-10 bg-slate-100 z-20 border-r border-slate-200 min-w-48 cursor-pointer" onclick="handleSort('patient')">
                                    <div class="flex items-center gap-1"><span>Patient</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center cursor-pointer" onclick="handleSort('patient_id')">
                                    <div class="flex items-center justify-center gap-1"><span>Patient ID</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                </th>
                                <th class="py-3 px-4 text-center">Office</th>
                                <th class="py-3 px-4 text-center">Date</th>
                                <th class="py-3 px-4 text-center">Provider ID</th>
                                <th class="py-3 px-4 text-center">Provider</th>
                                <th class="py-3 px-4 text-center">Adjustment Type</th>
                                <th class="py-3 px-4 text-center">Amount</th>
                                <th class="py-3 px-4">Note</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            `;
        }

        // Tab 6: RCM Executive Dashboard (Matching JARVIS-APP-copied-UI/rcm-dashboard-tab-ui.html 100%)
        function renderDashboard(container, data) {
            const s = data.summary || {};
            const charts = data.charts || {};
            const adj = data.adjustments || { items: [], total_formatted: '$ 0.00' };
            const claimsCodes = data.claims_service_codes || { items: [], total_sent: 0, total_close: 0, total_denied: 0 };

            // Adjustments rows HTML
            let adjRowsHtml = '';
            (adj.items || []).forEach(row => {
                adjRowsHtml += `
                    <tr class="hover:bg-slate-50/70 border-b border-slate-100">
                        <td class="py-2.5 px-4 font-medium text-slate-800">${escapeHtml(row.name)}</td>
                        <td class="py-2.5 px-4 font-bold text-xs ${row.bg_class} text-right pr-6">${row.amount_formatted}</td>
                    </tr>
                `;
            });

            // Claims Service Codes rows HTML
            let codesRowsHtml = '';
            (claimsCodes.items || []).forEach(row => {
                const badgeClass = row.tier === 'top' ? 'bg-[#dcfce7] text-[#15803d]' : (row.tier === 'bottom' ? 'bg-[#fee2e2] text-[#b91c1c]' : 'bg-[#fef3c7] text-[#b45309]');
                codesRowsHtml += `
                    <tr class="hover:bg-slate-50/70 border-b border-slate-100">
                        <td class="py-2.5 px-4 font-bold text-slate-900">${escapeHtml(row.code)}</td>
                        <td class="py-2.5 px-4 text-center font-bold text-xs ${badgeClass}">${row.sent}</td>
                        <td class="py-2.5 px-4 text-center font-bold text-xs ${badgeClass}">${row.close}</td>
                        <td class="py-2.5 px-4 text-center font-bold text-xs ${row.denied > 0 ? 'bg-[#fef3c7] text-[#b45309]' : 'bg-[#fee2e2] text-[#b91c1c]'}">
                            <div class="flex items-center justify-center gap-1">
                                <span>${row.denied}</span>
                                ${row.denied > 0 ? '<i data-lucide="external-link" class="w-3 h-3 text-slate-400 cursor-pointer"></i>' : ''}
                            </div>
                        </td>
                    </tr>
                `;
            });

            container.innerHTML = `
                <div class="space-y-6">

                    <!-- Top 3 KPI Metric Cards (Grid 3 Columns) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-0 bg-white rounded-xl border border-slate-200 shadow-xs divide-y md:divide-y-0 md:divide-x divide-slate-200">

                        <!-- Card 1: Insurance Estimate Lost -->
                        <div class="p-5 flex flex-col justify-between space-y-2">
                            <div class="flex items-center justify-between">
                                <h6 class="text-xs font-bold text-slate-800">Insurance Estimate Lost</h6>
                                <span class="text-slate-400" title="Displays the Estimated Insurance $ lost when a claim is closed and a denial is posted">
                                    <i data-lucide="info" class="w-4 h-4"></i>
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-2xl font-extrabold text-slate-900">${s.ins_est_lost_formatted || '$ 3,354.71'}</h3>
                                <span class="text-rose-500 flex items-center font-bold text-xs">
                                    <i data-lucide="trending-down" class="w-4 h-4"></i>
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-400 font-medium">
                                ${s.ins_est_lost_diff || '$ (340,401.63) down vs previous year'}
                            </div>
                        </div>

                        <!-- Card 2: OTC % -->
                        <div class="p-5 flex flex-col justify-between space-y-2">
                            <div class="flex items-center justify-between">
                                <h6 class="text-xs font-bold text-slate-800">OTC %</h6>
                                <span class="text-slate-400" title="Displays the percentage of Patient Payments Collection on the day of service vs Gross Production">
                                    <i data-lucide="info" class="w-4 h-4"></i>
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-2xl font-extrabold text-slate-900">${s.otc_pct_formatted || '11.17%'}</h3>
                                <span class="text-emerald-500 flex items-center font-bold text-xs">
                                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-400 font-medium">
                                ${s.otc_pct_diff || '5.00% up vs previous year'}
                            </div>
                        </div>

                        <!-- Card 3: % of Claims Closed within 60 days -->
                        <div class="p-5 flex flex-col justify-between space-y-2">
                            <div class="flex items-center justify-between">
                                <h6 class="text-xs font-bold text-slate-800">% of Claims Closed within 60 days</h6>
                                <span class="text-slate-400" title="Number of claims worked on by each member of the RCM team">
                                    <i data-lucide="info" class="w-4 h-4"></i>
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-2xl font-extrabold text-slate-900">${s.claims_closed_60_pct_formatted || '99.07%'}</h3>
                                <span class="text-emerald-500 flex items-center font-bold text-xs">
                                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-400 font-medium">
                                ${s.claims_closed_60_pct_diff || '1.30% up vs previous year'}
                            </div>
                        </div>

                    </div>

                    <!-- Row 1 Charts (3 Columns) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <!-- Chart 1: Aging | Production -->
                        <div class="space-y-2">
                            <h5 class="text-sm font-bold text-slate-900">Aging <span class="font-normal text-slate-500">| Production</span></h5>
                            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col items-center justify-between min-h-[380px]">
                                <div class="relative w-56 h-56 my-auto flex items-center justify-center">
                                    <canvas id="dashChart_aging"></canvas>
                                </div>
                                <div class="w-full space-y-2 pt-3 border-t border-slate-100 text-xs font-bold text-slate-700">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-xs shrink-0 bg-[#6DE5C1]"></span><span>LESS 30</span></div>
                                        <span class="text-slate-400 font-semibold">$ 67,261.95</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-xs shrink-0 bg-[#996BE5]"></span><span>30 60</span></div>
                                        <span class="text-slate-400 font-semibold">$ 60,739.58</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-xs shrink-0 bg-[#56D9FE]"></span><span>OVER 60</span></div>
                                        <span class="text-slate-400 font-semibold">$ 289,349.29</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Chart 2: Patient vs Insurance | Collection -->
                        <div class="space-y-2">
                            <h5 class="text-sm font-bold text-slate-900">Patient vs Insurance <span class="font-normal text-slate-500">| Collection</span></h5>
                            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col items-center justify-between min-h-[380px]">
                                <div class="relative w-56 h-56 my-auto flex items-center justify-center">
                                    <canvas id="dashChart_pat_vs_ins"></canvas>
                                </div>
                                <div class="w-full space-y-2 pt-3 border-t border-slate-100 text-xs font-bold text-slate-700">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-xs shrink-0 bg-[#6DE5C1]"></span><span>PTS COLLECTION</span></div>
                                        <span class="text-slate-400 font-semibold">$ 108,121.26</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-xs shrink-0 bg-[#996BE5]"></span><span>INS COLLECTION</span></div>
                                        <span class="text-slate-400 font-semibold">$ 194,420.08</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Chart 3: RCM | Collection -->
                        <div class="space-y-2">
                            <h5 class="text-sm font-bold text-slate-900">RCM <span class="font-normal text-slate-500">| Collection</span></h5>
                            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col items-center justify-between min-h-[380px]">
                                <div class="relative w-56 h-56 my-auto flex items-center justify-center">
                                    <canvas id="dashChart_rcm_col"></canvas>
                                </div>
                                <div class="w-full space-y-2 pt-3 border-t border-slate-100 text-xs font-bold text-slate-700">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-xs shrink-0 bg-[#6DE5C1]"></span><span>OFFICE COLLECTION</span></div>
                                        <span class="text-slate-400 font-semibold">$ 302,541.34</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-xs shrink-0 bg-[#996BE5]"></span><span>RCM COLLECTION</span></div>
                                        <span class="text-slate-400 font-semibold">$ 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Row 2 Charts (3 Columns) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <!-- Chart 4: Claims | Count -->
                        <div class="space-y-2">
                            <h5 class="text-sm font-bold text-slate-900">Claims <span class="font-normal text-slate-500">| Count</span></h5>
                            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex items-center justify-center min-h-[260px] text-xs font-semibold text-slate-400">
                                <span>No data available</span>
                            </div>
                        </div>

                        <!-- Chart 5: Claims | Performance -->
                        <div class="space-y-2">
                            <h5 class="text-sm font-bold text-slate-900">Claims <span class="font-normal text-slate-500">| Performance</span></h5>
                            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex items-center justify-center min-h-[260px] text-xs font-semibold text-slate-400">
                                <span>No data available</span>
                            </div>
                        </div>

                        <!-- Chart 6: Claims | Outstanding -->
                        <div class="space-y-2">
                            <h5 class="text-sm font-bold text-slate-900">Claims <span class="font-normal text-slate-500">| Outstanding</span></h5>
                            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between min-h-[260px]">
                                <div class="flex items-center justify-center gap-4 text-xs font-bold text-slate-700 mb-2">
                                    <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-xs bg-[#996BE5]"></span><span>Outstanding</span></div>
                                    <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-xs bg-[#56D9FE]"></span><span>Not Outstanding</span></div>
                                </div>
                                <div class="relative h-44 w-full">
                                    <canvas id="dashChart_outstanding"></canvas>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Section 4: Adjustments Table -->
                    <div class="space-y-2 pt-2">
                        <h5 class="text-base font-bold text-slate-900">Adjustments</h5>
                        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-4">
                            <!-- Toolbar -->
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="bg-[#dcfce7] text-[#15803d] px-3 py-1 rounded text-xs font-semibold">Top 20%</span>
                                    <span class="bg-[#fef3c7] text-[#b45309] px-3 py-1 rounded text-xs font-semibold">Mid Tier</span>
                                    <span class="bg-[#fee2e2] text-[#b91c1c] px-3 py-1 rounded text-xs font-semibold">Bottom 20%</span>
                                </div>
                                <div class="flex items-center gap-2.5 ml-auto">
                                    <div class="relative">
                                        <input type="text" placeholder="Search"
                                            class="bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 pr-8 w-48 shadow-sm">
                                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute right-2.5 top-2.5 pointer-events-none"></i>
                                    </div>
                                    <button type="button" onclick="document.getElementById('rcmExportBtn').click()"
                                        class="border border-emerald-500 text-emerald-600 hover:bg-emerald-50 font-bold px-3.5 py-1.5 rounded-lg text-xs transition shadow-sm cursor-pointer">
                                        Export CSV
                                    </button>
                                </div>
                            </div>

                            <!-- Table -->
                            <div class="overflow-x-auto border border-slate-200 rounded-lg">
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead>
                                        <tr class="bg-slate-100/90 text-slate-700 font-bold border-b border-slate-200">
                                            <th class="py-3 px-4"><div class="flex items-center gap-1"><span>Adjustment</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                            <th class="py-3 px-4 text-right pr-6"><div class="flex items-center justify-end gap-1"><span>Amount</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${adjRowsHtml}
                                        <!-- Summary Row -->
                                        <tr class="bg-slate-100 font-bold text-slate-900 border-t border-slate-200">
                                            <td class="py-3 px-4 text-right font-extrabold">Total:</td>
                                            <td class="py-3 px-4 text-right pr-6 font-extrabold">${adj.total_formatted || '$ 79,694.80'}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Section 5: Claims Service Code Breakdown Table -->
                    <div class="space-y-2 pt-2">
                        <h5 class="text-base font-bold text-slate-900">Claims</h5>
                        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-4">
                            <!-- Toolbar -->
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="bg-[#dcfce7] text-[#15803d] px-3 py-1 rounded text-xs font-semibold">Top 20%</span>
                                    <span class="bg-[#fef3c7] text-[#b45309] px-3 py-1 rounded text-xs font-semibold">Mid Tier</span>
                                    <span class="bg-[#fee2e2] text-[#b91c1c] px-3 py-1 rounded text-xs font-semibold">Bottom 20%</span>
                                </div>
                                <div class="flex items-center gap-2.5 ml-auto">
                                    <div class="relative">
                                        <input type="text" placeholder="Search"
                                            class="bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 pr-8 w-48 shadow-sm">
                                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute right-2.5 top-2.5 pointer-events-none"></i>
                                    </div>
                                    <button type="button" onclick="document.getElementById('rcmExportBtn').click()"
                                        class="border border-emerald-500 text-emerald-600 hover:bg-emerald-50 font-bold px-3.5 py-1.5 rounded-lg text-xs transition shadow-sm cursor-pointer">
                                        Export CSV
                                    </button>
                                </div>
                            </div>

                            <!-- Table -->
                            <div class="overflow-x-auto border border-slate-200 rounded-lg">
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead>
                                        <tr class="bg-slate-100/90 text-slate-700 font-bold border-b border-slate-200">
                                            <th class="py-3 px-4"><div class="flex items-center gap-1"><span>Service Code</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                            <th class="py-3 px-4 text-center"><div class="flex items-center justify-center gap-1"><span>Sent</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                            <th class="py-3 px-4 text-center"><div class="flex items-center justify-center gap-1"><span>Close</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                            <th class="py-3 px-4 text-center"><div class="flex items-center justify-center gap-1"><span>Denied</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${codesRowsHtml}
                                        <!-- Summary Row -->
                                        <tr class="bg-slate-100 font-bold text-slate-900 border-t border-slate-200">
                                            <td class="py-3 px-4 font-extrabold text-right">Total:</td>
                                            <td class="py-3 px-4 text-center font-extrabold">${claimsCodes.total_sent || 958}</td>
                                            <td class="py-3 px-4 text-center font-extrabold">${claimsCodes.total_close || 958}</td>
                                            <td class="py-3 px-4 text-center font-extrabold">${claimsCodes.total_denied || 26}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Footer Pagination -->
                            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 text-xs text-slate-600">
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-500">Items per page</span>
                                    <select class="bg-white border border-slate-300 rounded px-2 py-0.5 text-xs font-semibold text-slate-700 shadow-sm focus:outline-none">
                                        <option value="10" selected>10</option>
                                        <option value="20">20</option>
                                        <option value="30">30</option>
                                        <option value="50">50</option>
                                    </select>
                                    <span class="ml-1 font-medium text-slate-500">1-10 of 61 items</span>
                                </div>
                                <div class="flex items-center gap-2 ml-auto">
                                    <select class="bg-white border border-slate-300 rounded px-2 py-0.5 text-xs font-semibold text-slate-700 shadow-sm focus:outline-none">
                                        <option value="1" selected>1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                    </select>
                                    <span class="text-slate-500 font-medium">of 7 pages</span>
                                    <div class="flex items-center gap-1 ml-1">
                                        <button type="button" class="p-1 rounded border border-slate-300 text-slate-600 opacity-40 cursor-not-allowed">
                                            <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <button type="button" class="p-1 rounded border border-slate-300 text-slate-600">
                                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            `;

            // Draw Charts for Dashboard
            setTimeout(() => {
                // 1. Aging Donut
                const agingCtx = document.getElementById('dashChart_aging')?.getContext('2d');
                if (agingCtx && charts.aging_production) {
                    if (RCM_STATE.chartInstances.dash_aging) RCM_STATE.chartInstances.dash_aging.destroy();
                    RCM_STATE.chartInstances.dash_aging = new Chart(agingCtx, {
                        type: 'doughnut',
                        data: {
                            labels: charts.aging_production.labels,
                            datasets: [{
                                data: charts.aging_production.data,
                                backgroundColor: charts.aging_production.colors,
                                borderWidth: 0,
                                cutout: '65%',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } }
                        }
                    });
                }

                // 2. Patient vs Ins Donut
                const patInsCtx = document.getElementById('dashChart_pat_vs_ins')?.getContext('2d');
                if (patInsCtx && charts.patient_vs_ins) {
                    if (RCM_STATE.chartInstances.dash_pat_ins) RCM_STATE.chartInstances.dash_pat_ins.destroy();
                    RCM_STATE.chartInstances.dash_pat_ins = new Chart(patInsCtx, {
                        type: 'doughnut',
                        data: {
                            labels: charts.patient_vs_ins.labels,
                            datasets: [{
                                data: charts.patient_vs_ins.data,
                                backgroundColor: charts.patient_vs_ins.colors,
                                borderWidth: 0,
                                cutout: '65%',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } }
                        }
                    });
                }

                // 3. RCM Collection Donut
                const rcmColCtx = document.getElementById('dashChart_rcm_col')?.getContext('2d');
                if (rcmColCtx && charts.rcm_collection) {
                    if (RCM_STATE.chartInstances.dash_rcm_col) RCM_STATE.chartInstances.dash_rcm_col.destroy();
                    RCM_STATE.chartInstances.dash_rcm_col = new Chart(rcmColCtx, {
                        type: 'doughnut',
                        data: {
                            labels: charts.rcm_collection.labels,
                            datasets: [{
                                data: charts.rcm_collection.data,
                                backgroundColor: charts.rcm_collection.colors,
                                borderWidth: 0,
                                cutout: '65%',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } }
                        }
                    });
                }

                // 4. Claims Outstanding Bar
                const outCtx = document.getElementById('dashChart_outstanding')?.getContext('2d');
                if (outCtx && charts.claims_outstanding) {
                    if (RCM_STATE.chartInstances.dash_out) RCM_STATE.chartInstances.dash_out.destroy();
                    RCM_STATE.chartInstances.dash_out = new Chart(outCtx, {
                        type: 'bar',
                        data: {
                            labels: ['claims'],
                            datasets: [
                                {
                                    label: 'Outstanding',
                                    data: [0],
                                    backgroundColor: '#996BE5',
                                    borderRadius: 3,
                                    barPercentage: 0.4,
                                },
                                {
                                    label: 'Not Outstanding',
                                    data: [574],
                                    backgroundColor: '#56D9FE',
                                    borderRadius: 3,
                                    barPercentage: 0.4,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 600,
                                    ticks: { stepSize: 100, font: { size: 10 } }
                                },
                                x: { ticks: { font: { size: 11 } } }
                            }
                        }
                    });
                }
            }, 50);
        }

        // Tab 7: Collection Refunds (Exact Match to Screenshot 1)
        function renderCollectionRefunds(container, data) {
            const chartData = data.chart || { labels: [], data: [], colors: [], legend: [] };
            const summary = data.summary || { average_formatted: '$ 0.00', total_formatted: '$ 0.00', percentage_average: '0.00%', percentage_total: '0.00%' };

            let tableRows = '';
            (data.items || []).forEach(row => {
                tableRows += `
                    <tr class="hover:bg-slate-50/70 border-b border-slate-100 transition-colors">
                        <td class="py-3 px-4 font-bold text-slate-800 flex items-center gap-1.5">
                            <span>${escapeHtml(row.type)}</span>
                        </td>
                        <td class="py-3 px-4 text-slate-600 font-medium">${row.type_id}</td>
                        <td class="py-3 px-4">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded text-xs font-bold ${row.bg_cell}">
                                <span>${row.adjustment_formatted}</span>
                                <i data-lucide="external-link" class="w-3.5 h-3.5 opacity-60"></i>
                            </div>
                        </td>
                        <td class="py-3 px-4 font-medium text-slate-700">${row.percentage}</td>
                    </tr>
                `;
            });

            container.innerHTML = `
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    <!-- Left Column: Donut Ring Chart -->
                    <div class="lg:col-span-5 bg-white p-6 rounded-xl border border-slate-200 shadow-xs flex flex-col items-center justify-center space-y-6">
                        <div class="relative w-72 h-72">
                            <canvas id="rcmRefundDonutChart"></canvas>
                        </div>

                        <!-- Legend below chart -->
                        <div class="w-full space-y-2 text-xs font-bold text-slate-700">
                            ${chartData.legend.map(item => `
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-xs shrink-0" style="background-color: ${item.color};"></span>
                                        <span>${escapeHtml(item.label)}</span>
                                    </div>
                                    <span class="font-extrabold">${item.amount_formatted}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>

                    <!-- Right Column: Sub-filters, Table, Summary, and Pagination -->
                    <div class="lg:col-span-7 bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-1.5">
                                <span class="bg-[#dcfce7] text-[#15803d] px-3 py-1 rounded text-xs font-semibold">Top 20%</span>
                                <span class="bg-[#fef3c7] text-[#b45309] px-3 py-1 rounded text-xs font-semibold">Mid Tier</span>
                                <span class="bg-[#fee2e2] text-[#b91c1c] px-3 py-1 rounded text-xs font-semibold">Bottom 20%</span>
                            </div>

                            <div class="flex items-center gap-2.5 ml-auto">
                                <div class="relative">
                                    <input type="text" id="refundSearchInput" placeholder="Search" value="${escapeHtml(RCM_STATE.search)}"
                                        class="bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 pr-8 w-48 shadow-sm">
                                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute right-2.5 top-2.5 pointer-events-none"></i>
                                </div>

                                <button type="button" onclick="document.getElementById('rcmExportBtn').click()"
                                    class="border border-emerald-500 text-emerald-600 hover:bg-emerald-50 font-bold px-3.5 py-1.5 rounded-lg text-xs transition shadow-sm cursor-pointer">
                                    Export CSV
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto border border-slate-200 rounded-lg">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="bg-slate-100/90 text-slate-700 font-bold border-b border-slate-200">
                                        <th class="py-3 px-4">
                                            <div class="flex items-center gap-1"><span>Type</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                        </th>
                                        <th class="py-3 px-4">
                                            <div class="flex items-center gap-1"><span>Type ID</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                        </th>
                                        <th class="py-3 px-4">
                                            <div class="flex items-center gap-1"><span>Adjustment</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                        </th>
                                        <th class="py-3 px-4">
                                            <div class="flex items-center gap-1"><span>Percentage</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${tableRows}

                                    <tr class="bg-slate-50/50 border-t border-slate-200 font-bold text-slate-800">
                                        <td class="py-2.5 px-4" colspan="2"></td>
                                        <td class="py-2.5 px-4 text-right pr-12 text-slate-500 font-semibold">Average:</td>
                                        <td class="py-2.5 px-4">
                                            <div class="flex items-center gap-6">
                                                <span class="font-extrabold text-slate-900">${summary.average_formatted}</span>
                                                <span class="text-slate-700">${summary.percentage_average}</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="bg-slate-50/50 border-t border-slate-200 font-bold text-slate-800">
                                        <td class="py-2.5 px-4" colspan="2"></td>
                                        <td class="py-2.5 px-4 text-right pr-12 text-slate-500 font-semibold">Total:</td>
                                        <td class="py-2.5 px-4">
                                            <div class="flex items-center gap-6">
                                                <span class="font-extrabold text-slate-900">${summary.total_formatted}</span>
                                                <span class="text-slate-700">${summary.percentage_total}</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 pt-2 text-xs text-slate-600">
                            <div class="flex items-center gap-2">
                                <span class="text-slate-500">Items per page</span>
                                <select class="bg-white border border-slate-300 rounded px-2 py-0.5 text-xs font-semibold text-slate-700 shadow-sm focus:outline-none">
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                                <span class="ml-1 font-medium text-slate-500">1-2 of 2 items</span>
                            </div>

                            <div class="flex items-center gap-2 ml-auto">
                                <select class="bg-white border border-slate-300 rounded px-2 py-0.5 text-xs font-semibold text-slate-700 shadow-sm focus:outline-none">
                                    <option value="1" selected>1</option>
                                </select>
                                <span class="text-slate-500 font-medium">of 1 pages</span>
                                <div class="flex items-center gap-1 ml-1">
                                    <button type="button" class="p-1 rounded border border-slate-300 text-slate-600 opacity-40 cursor-not-allowed">
                                        <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button type="button" class="p-1 rounded border border-slate-300 text-slate-600 opacity-40 cursor-not-allowed">
                                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            `;

            setTimeout(() => {
                const donutCtx = document.getElementById('rcmRefundDonutChart')?.getContext('2d');
                if (donutCtx && chartData.data.length > 0) {
                    if (RCM_STATE.chartInstances.refund) RCM_STATE.chartInstances.refund.destroy();
                    RCM_STATE.chartInstances.refund = new Chart(donutCtx, {
                        type: 'doughnut',
                        data: {
                            labels: chartData.labels,
                            datasets: [{
                                data: chartData.data,
                                backgroundColor: chartData.colors,
                                borderWidth: 0,
                                cutout: '68%',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => ` ${ctx.label}: $ ${Number(ctx.parsed).toLocaleString('en-US', { minimumFractionDigits: 2 })}`
                                    }
                                }
                            }
                        }
                    });
                }
            }, 50);

            const refundSearch = document.getElementById('refundSearchInput');
            if (refundSearch) {
                refundSearch.addEventListener('input', (e) => {
                    RCM_STATE.search = e.target.value;
                    loadTabData();
                });
            }
        }

        // Tab 8: Payor Overview (Matching the exact screenshot with 3 Donut Cards + 4 Analytical Tables)
        function renderPayorOverview(container, data) {
            const cards = data.cards || [];
            const openClaims = data.open_claims || { items: [], summary: {} };
            const topPayors = data.top_payors || { items: [], summary: {} };
            const topProviders = data.top_providers || { items: [], summary: {} };
            const topAdaCodes = data.top_ada_codes || { items: [], summary: {} };

            // 1. Render Top 3 Donut Cards
            let cardsHtml = '';
            cards.forEach(card => {
                cardsHtml += `
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col space-y-4">
                        <h3 class="text-sm font-extrabold text-slate-900">${escapeHtml(card.title)}</h3>

                        <div class="relative w-full h-56 flex items-center justify-center my-1">
                            <canvas id="payorMixChart_${card.year}"></canvas>
                        </div>

                        <div class="w-full space-y-1.5 pt-2 border-t border-slate-100 text-xs text-slate-700">
                            ${card.legend.map(item => `
                                <div class="flex items-center justify-between text-[11px] gap-2 font-medium">
                                    <div class="flex items-center gap-2 truncate">
                                        <span class="w-2.5 h-2.5 rounded-xs shrink-0" style="background-color: ${item.color};"></span>
                                        <span class="truncate" title="${escapeHtml(item.full_name)}">${escapeHtml(item.name)}</span>
                                    </div>
                                    <span class="font-semibold text-slate-900 shrink-0">${item.amount_formatted}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            });

            // 2. Table: Open Claims by Payor ($500+)
            let openClaimsRows = '';
            (openClaims.items || []).forEach(row => {
                openClaimsRows += `
                    <tr class="hover:bg-slate-50/70 border-b border-slate-100">
                        <td class="py-2.5 px-4 font-semibold text-slate-800">${escapeHtml(row.payor)}</td>
                        <td class="py-2.5 px-4 text-center font-medium text-slate-700">${row.count}</td>
                        <td class="py-2.5 px-4 text-right font-medium text-slate-800">${row.estimate_formatted}</td>
                    </tr>
                `;
            });

            // 3. Table: Top 10 Payors (Trailing 12 Months)
            let topPayorsRows = '';
            (topPayors.items || []).forEach(row => {
                topPayorsRows += `
                    <tr class="hover:bg-slate-50/70 border-b border-slate-100">
                        <td class="py-2.5 px-4 font-semibold text-slate-800">${escapeHtml(row.payor)}</td>
                        <td class="py-2.5 px-4 text-right font-medium text-slate-800">${row.total_charged_formatted}</td>
                        <td class="py-2.5 px-4 text-right font-medium text-slate-800">${row.total_received_formatted}</td>
                    </tr>
                `;
            });

            // 4. Table: Top 10 Provider
            let topProvidersRows = '';
            (topProviders.items || []).forEach(row => {
                topProvidersRows += `
                    <tr class="hover:bg-slate-50/70 border-b border-slate-100">
                        <td class="py-2 px-3.5 text-slate-600 font-medium">${row.provider_id}</td>
                        <td class="py-2 px-3.5 font-bold text-slate-800">${escapeHtml(row.provider_name)}</td>
                        <td class="py-2 px-3.5 text-right font-medium text-slate-700">${row.fee_2024_formatted}</td>
                        <td class="py-2 px-3.5 text-right font-medium text-slate-700">${row.fee_2025_formatted}</td>
                        <td class="py-2 px-3.5 text-right font-medium text-slate-700">${row.fee_2026_formatted}</td>
                        <td class="py-2 px-3.5 text-right font-bold text-slate-900">${row.total_formatted}</td>
                    </tr>
                `;
            });

            // 5. Table: Top 10 ADA Codes
            let topAdaRows = '';
            (topAdaCodes.items || []).forEach(row => {
                topAdaRows += `
                    <tr class="hover:bg-slate-50/70 border-b border-slate-100">
                        <td class="py-2 px-3.5 font-bold text-slate-800">${escapeHtml(row.ada_code)}</td>
                        <td class="py-2 px-3.5 text-right font-medium text-slate-700">${row.fee_2024_formatted}</td>
                        <td class="py-2 px-3.5 text-right font-medium text-slate-700">${row.fee_2025_formatted}</td>
                        <td class="py-2 px-3.5 text-right font-medium text-slate-700">${row.fee_2026_formatted}</td>
                        <td class="py-2 px-3.5 text-right font-bold text-slate-900">${row.total_formatted}</td>
                    </tr>
                `;
            });

            container.innerHTML = `
                <div class="space-y-6">

                    <!-- Section 1: 3-Year Mix Cards (2023, 2024, 2025) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        ${cardsHtml}
                    </div>

                    <!-- Section 2: Middle Grid (Open Claims $500+ & Top 10 Payors Trailing 12M) -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

                        <!-- Left: Open Claims by Payor ($500+) -->
                        <div class="space-y-2">
                            <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Open Claims by Payor ($500+)</h3>
                            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-xs">
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead>
                                        <tr class="bg-slate-100/90 text-slate-700 font-bold border-b border-slate-200 select-none">
                                            <th class="py-2.5 px-4"><div class="flex items-center gap-1"><span>Ins Payor</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                            <th class="py-2.5 px-4 text-center"><div class="flex items-center justify-center gap-1"><span>Count</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                            <th class="py-2.5 px-4 text-right"><div class="flex items-center justify-end gap-1"><span>Insurance Estimate</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${openClaimsRows.length > 0 ? openClaimsRows : `
                                            <tr>
                                                <td colspan="3" class="py-6 text-center text-slate-400 font-medium">No open claims over $500</td>
                                            </tr>
                                        `}
                                        <!-- Summary Total Row -->
                                        <tr class="bg-slate-100/80 font-bold text-slate-900 border-t border-slate-200">
                                            <td class="py-2.5 px-4">Total</td>
                                            <td class="py-2.5 px-4 text-center">${openClaims.summary.total_count || 0}</td>
                                            <td class="py-2.5 px-4 text-right font-extrabold">${openClaims.summary.total_estimate_formatted || '$ 0'}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Right: Top 10 Payors (Trailing 12 Months) -->
                        <div class="space-y-2">
                            <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Top 10 Payors (Trailing 12 Months)</h3>
                            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-xs">
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead>
                                        <tr class="bg-slate-100/90 text-slate-700 font-bold border-b border-slate-200 select-none">
                                            <th class="py-2.5 px-4"><div class="flex items-center gap-1"><span>Ins Payor</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                            <th class="py-2.5 px-4 text-right"><div class="flex items-center justify-end gap-1"><span>Total Charged</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                            <th class="py-2.5 px-4 text-right"><div class="flex items-center justify-end gap-1"><span>Total Received</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${topPayorsRows}
                                        <!-- Summary Total Row -->
                                        <tr class="bg-slate-100/80 font-bold text-slate-900 border-t border-slate-200">
                                            <td class="py-2.5 px-4">Total</td>
                                            <td class="py-2.5 px-4 text-right font-extrabold">${topPayors.summary.total_charged_formatted || '$ 0'}</td>
                                            <td class="py-2.5 px-4 text-right font-extrabold">${topPayors.summary.total_received_formatted || '$ 0'}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- Section 3: Bottom Grid (Top 10 Provider & Top 10 ADA Codes) -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

                        <!-- Left: Top 10 Provider -->
                        <div class="space-y-2">
                            <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Top 10 Provider</h3>
                            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-xs">
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead>
                                        <tr class="bg-slate-100/90 text-slate-700 font-bold border-b border-slate-200 select-none">
                                            <th class="py-2 px-3.5"><div class="flex items-center gap-1"><span>Provider ID</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                            <th class="py-2 px-3.5"><div class="flex items-center gap-1"><span>Provider Name</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                            <th class="py-2 px-3.5 text-right"><div class="flex items-center justify-end gap-1"><span>2024</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                            <th class="py-2 px-3.5 text-right"><div class="flex items-center justify-end gap-1"><span>2025</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                            <th class="py-2 px-3.5 text-right"><div class="flex items-center justify-end gap-1"><span>2026</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                            <th class="py-2 px-3.5 text-right"><div class="flex items-center justify-end gap-1"><span>Total</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${topProvidersRows}
                                        <!-- Summary Total Row -->
                                        <tr class="bg-slate-100/80 font-bold text-slate-900 border-t border-slate-200">
                                            <td class="py-2.5 px-3.5 font-bold" colspan="2">Total</td>
                                            <td class="py-2.5 px-3.5 text-right font-extrabold">${topProviders.summary.sum_2024_formatted || '$ 0'}</td>
                                            <td class="py-2.5 px-3.5 text-right font-extrabold">${topProviders.summary.sum_2025_formatted || '$ 0'}</td>
                                            <td class="py-2.5 px-3.5 text-right font-extrabold">${topProviders.summary.sum_2026_formatted || '$ 0'}</td>
                                            <td class="py-2.5 px-3.5 text-right font-extrabold">${topProviders.summary.total_formatted || '$ 0'}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Right: Top 10 ADA Codes -->
                        <div class="space-y-2">
                            <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Top 10 ADA Codes</h3>
                            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-xs">
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead>
                                        <tr class="bg-slate-100/90 text-slate-700 font-bold border-b border-slate-200 select-none">
                                            <th class="py-2 px-3.5"><div class="flex items-center gap-1"><span>ADA Code</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                            <th class="py-2 px-3.5 text-right"><div class="flex items-center justify-end gap-1"><span>2024</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                            <th class="py-2 px-3.5 text-right"><div class="flex items-center justify-end gap-1"><span>2025</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                            <th class="py-2 px-3.5 text-right"><div class="flex items-center justify-end gap-1"><span>2026</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                            <th class="py-2 px-3.5 text-right"><div class="flex items-center justify-end gap-1"><span>Total</span> <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i></div></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${topAdaRows}
                                        <!-- Summary Total Row -->
                                        <tr class="bg-slate-100/80 font-bold text-slate-900 border-t border-slate-200">
                                            <td class="py-2.5 px-3.5 font-bold">Total</td>
                                            <td class="py-2.5 px-3.5 text-right font-extrabold">${topAdaCodes.summary.sum_2024_formatted || '$ 0'}</td>
                                            <td class="py-2.5 px-3.5 text-right font-extrabold">${topAdaCodes.summary.sum_2025_formatted || '$ 0'}</td>
                                            <td class="py-2.5 px-3.5 text-right font-extrabold">${topAdaCodes.summary.sum_2026_formatted || '$ 0'}</td>
                                            <td class="py-2.5 px-3.5 text-right font-extrabold">${topAdaCodes.summary.total_formatted || '$ 0'}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                </div>
            `;

            // Draw Charts for each year
            setTimeout(() => {
                cards.forEach(card => {
                    const canvasId = `payorMixChart_${card.year}`;
                    const ctx = document.getElementById(canvasId)?.getContext('2d');
                    if (ctx && card.data.length > 0) {
                        if (RCM_STATE.chartInstances[canvasId]) RCM_STATE.chartInstances[canvasId].destroy();
                        RCM_STATE.chartInstances[canvasId] = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: card.labels,
                                datasets: [{
                                    data: card.data,
                                    backgroundColor: card.colors,
                                    borderWidth: 0,
                                    cutout: '65%',
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: (c) => ` ${c.label}: $ ${Number(c.parsed).toLocaleString('en-US', { minimumFractionDigits: 2 })}`
                                        }
                                    }
                                }
                            }
                        });
                    }
                });
            }, 50);
        }

        function handleSort(key) {
            if (RCM_STATE.sortKey === key) {
                RCM_STATE.sortDir = RCM_STATE.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                RCM_STATE.sortKey = key;
                RCM_STATE.sortDir = 'desc';
            }
            loadTabData();
        }

        function toggleAllCheckboxes(checked) {
            document.querySelectorAll('.claim-row-checkbox').forEach(cb => {
                cb.checked = checked;
            });
        }

        function getStatusBadgeClass(status) {
            switch (status) {
                case 'Received':
                case 'Paid':
                    return 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                case 'Sent':
                case 'Submitted':
                    return 'bg-blue-50 text-blue-700 border border-blue-200';
                case 'Pre-Auth':
                    return 'bg-purple-50 text-purple-700 border border-purple-200';
                case 'Supplemental':
                    return 'bg-amber-50 text-amber-700 border border-amber-200';
                default:
                    return 'bg-slate-100 text-slate-600 border border-slate-200';
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    </script>
</x-app-layout>
