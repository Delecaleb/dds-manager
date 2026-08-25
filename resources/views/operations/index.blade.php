<x-app-layout>
    <div class="bg-[#f8fafc] text-[#475569] font-sans antialiased text-[13px] min-h-full">
        <div class="p-6 space-y-5 max-w-[1600px] mx-auto w-full">

            {{-- Title --}}
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold text-black tracking-tight">Operations</h1>
            </div>

            {{-- Filters --}}
            <div class="flex flex-wrap items-center gap-2.5">
                <div id="opsDateRangeWrapper">
                    <x-daterange-picker id="opsDateRange" on-apply="opsDateApplied" />
                </div>
                <div id="opsMonthPickerWrapper" class="hidden">
                    <input type="month" id="opsMonthPicker" value="{{ date('Y-m') }}"
                        class="appearance-none bg-white border border-slate-300 rounded px-3 py-1.5 font-bold text-slate-700 shadow-sm focus:outline-none focus:border-[#00bfa5] h-[34px] min-w-[150px]">
                </div>

                {{-- Trends Metric Filter (shown only on Trends tab) --}}
                <div id="opsTrendsMetricWrapper" class="{{ $activeTab === 'trends' ? '' : 'hidden' }} relative min-w-[260px]">
                    <div id="opsTrendsDropdown" class="relative">
                        <button type="button" id="opsTrendsDropdownBtn"
                            class="w-full flex items-center justify-between bg-white border border-slate-300 rounded px-3 py-1.5 font-medium text-slate-700 shadow-sm text-left text-[13px] hover:border-slate-400 focus:outline-none focus:border-[#00bfa5]">
                            <span id="opsTrendsSelectedLabel" class="truncate">By Office - Production</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 shrink-0 ml-2"></i>
                        </button>

                        <div id="opsTrendsDropdownMenu"
                            class="hidden absolute left-0 top-full mt-1 w-full bg-white border border-slate-200 rounded-md shadow-lg z-50 max-h-[300px] flex flex-col">
                            <div class="p-2 border-b border-slate-100 bg-slate-50">
                                <input type="text" id="opsTrendsSearch" placeholder="Search metrics..."
                                    class="w-full px-2.5 py-1 text-xs border border-slate-300 rounded focus:outline-none focus:border-[#00bfa5] bg-white">
                            </div>
                            <ul id="opsTrendsOptionsList" class="overflow-y-auto py-1 text-xs text-slate-700 max-h-[240px]">
                                @php
                                    $trendsOptions = [
                                        'BYO Active Pts',
                                        'BYO Active Pts Count',
                                        'BYO Avg # of Tx Plans Presented',
                                        'BYO Close Percent',
                                        'BYO Collection',
                                        'BYO Co Pay Coll',
                                        'BYO Doc Collection',
                                        'BYO Doc Production',
                                        'BYO $ in Pen. Tx',
                                        'BYO Hyg Collection',
                                        'BYO Hyg Production',
                                        'BYO Npts Visits',
                                        'BYO No Show Rate',
                                        'BYO Number of Treatment Plans Presented',
                                        'BYO Pts Appointment',
                                        'BYO Patient Retention',
                                        'BYO Pts Visits',
                                        'BYO Production',
                                        'Cancellation Rate',
                                        'Coll per Doc',
                                        'Coll per Hyg',
                                        'Collection VS production $',
                                        'Collection VS production %',
                                        'DOC Avg Treatment Plan Existing',
                                        'DOC Avg Treatment Plan New Patients',
                                        'DOC Avg. Treatment plan ($) per Existing Pts',
                                        'DOC Avg. Treatment plan ($) per New Pts',
                                        'DOC Comprehensive Exam',
                                        'DOC Limited Exam',
                                        'DOC Pts Visits',
                                        'DOC Periodic Exam',
                                        'DOC Production Per Exam',
                                        'HYG Adjunctive Aid',
                                        'HYG Avg FMX',
                                        'HYG Avg Production Per Day',
                                        'HYG Avg Production Per Patient',
                                        'HYG Avg Production Per Procedure',
                                        'HYG Avg Production Per Provider',
                                        'HYG Avg SRP Per Day',
                                        'HYG Pts Visits',
                                        'HYG Perio Appointments',
                                        'HYG % Perio to Prophy',
                                        'HYG Periochip Placements',
                                        'HYG Production Per Exam',
                                        'HYG Reappointment Rate',
                                        'HYG Ret. (Adult - past 12 months)',
                                        'HYG Ret. (Adult - past 6 months)',
                                        'HYG Ret. (Child - past 12 months)',
                                        'HYG Ret. (Child - past 6 months)',
                                        'HYG Sealants',
                                        'HYG Varnish Applications Per Day',
                                        'HYG Whitening Procedure',
                                        'Medicaid Percentage',
                                        'OS Collection',
                                        'ORT Active Pts Count',
                                        'ORT Active Pts %',
                                        'ORT Avg. # of Tx Plans Presented',
                                        'ORT Collection',
                                        'ORT Npts Visits',
                                        'ORT No Show Rate',
                                        'ORT Pts Appts',
                                        'ORT Patient Retention',
                                        'ORT Pts Visits',
                                        'ORT Production',
                                        'ORT U Pts Visits/day',
                                        'ORT U Pts Visits/mo',
                                        'PPV Collection',
                                        'PPV Procedures',
                                        'PPV Production',
                                        'PP Collection',
                                        'PP Production',
                                        'PWD Collection',
                                        'PWD Npts Visits',
                                        'PWD Pts Visits',
                                        'PWD Production',
                                        'Perio Collection',
                                        'Prod per Doc',
                                        'Prod per Hyg',
                                        'Prov Prod Exam Codes',
                                    ];
                                @endphp
                                @foreach ($trendsOptions as $opt)
                                    @php
                                        $label = $opt === 'BYO Production' ? 'By Office - Production' : $opt;
                                    @endphp
                                    <li class="ops-trends-option px-3 py-1.5 hover:bg-emerald-50 hover:text-emerald-700 cursor-pointer flex items-center justify-between {{ $opt === 'BYO Production' ? 'bg-emerald-50/60 font-bold text-emerald-700' : '' }}"
                                        data-value="{{ $opt }}" data-label="{{ $label }}">
                                        <span>{{ $label }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <input type="hidden" id="opsTrendsMetric" value="BYO Production">
                    </div>
                </div>

                <div class="relative min-w-[140px]">
                    <select id="opsLocation"
                        class="w-full appearance-none bg-white border border-slate-300 rounded px-3 py-1.5 font-medium text-slate-700 pr-8 shadow-sm focus:outline-none">
                        <option value="0">8 Mile</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-slate-400">
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </div>
                </div>

                <div class="relative min-w-[170px]">
                    <select id="opsLineOfBusiness"
                        class="w-full appearance-none bg-white border border-slate-300 rounded px-3 py-1.5 font-medium text-slate-700 pr-8 shadow-sm focus:outline-none">
                        <option value="">Line of Business: All</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-slate-400">
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </div>
                </div>

                <button id="opsUpdateBtn"
                    class="bg-white border border-[#00bfa5] text-[#00bfa5] font-bold px-5 py-1.5 rounded shadow-sm text-xs hover:bg-[#00bfa5] hover:text-white transition-colors">
                    Update
                </button>
            </div>

            {{-- Main tab nav --}}
            <div
                class="border-b border-slate-200 w-full flex flex-wrap gap-x-6 gap-y-2 text-slate-400 font-medium text-sm pt-2">
                @foreach ($tabs as $slug => $label)
                    <a href="{{ route('operations.tab', $slug) }}" data-ops-tab="{{ $slug }}"
                        class="ops-tab pb-2 border-b-2 transition-all duration-150 whitespace-nowrap
                                              {{ $slug === $activeTab ? 'border-[#00bfa5] text-black font-bold' : 'border-transparent hover:text-slate-600' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Active tab content (fragment injected here) --}}
            <div id="ops-content" class="min-h-[300px]">
                <div class="flex items-center justify-center py-20 text-slate-400 text-sm gap-2">
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Loading…
                </div>
            </div>

        </div>
    </div>

    <script>
        (function () {
            const CONFIG = {
                activeTab: @json($activeTab),
                activeSubtab: @json($activeSubtab),
                dataBase: "{{ url('operations/data') }}",
                pageBase: "{{ url('operations') }}",
            };

            const content = document.getElementById('ops-content');
            let current = { tab: CONFIG.activeTab, subtab: CONFIG.activeSubtab, extra: {} };

            function dateParams() {
                const isClaims = current.tab === 'claims';
                const isTrends = current.tab === 'trends';
                const clinic = document.getElementById('opsLocation')?.value ?? '';
                const params = new URLSearchParams();

                if (isClaims) {
                    const monthVal = document.getElementById('opsMonthPicker').value;
                    if (monthVal) {
                        params.set('start_date', monthVal + '-01');
                        // Calculate last day of the chosen month
                        const parts = monthVal.split('-');
                        const d = new Date(parts[0], parts[1], 0);
                        const endStr = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                        params.set('end_date', endStr);
                    }
                } else {
                    const drp = window.jQuery && jQuery('#opsDateRange').data('daterangepicker');
                    if (drp) {
                        params.set('start_date', drp.startDate.format('YYYY-MM-DD'));
                        params.set('end_date', drp.endDate.format('YYYY-MM-DD'));
                    }
                }

                if (clinic !== '') params.set('clinics', clinic);

                const lob = document.getElementById('opsLineOfBusiness')?.value ?? '';
                if (lob) params.set('lob', lob);

                if (isTrends) {
                    const metric = document.getElementById('opsTrendsMetric')?.value ?? '';
                    if (metric) params.set('metric', metric);
                }

                return params;
            }

            function setActiveTab(tab) {
                document.querySelectorAll('.ops-tab').forEach(el => {
                    const on = el.dataset.opsTab === tab;
                    el.classList.toggle('border-[#00bfa5]', on);
                    el.classList.toggle('text-black', on);
                    el.classList.toggle('font-bold', on);
                    el.classList.toggle('border-transparent', !on);
                    el.classList.toggle('hover:text-slate-600', !on);
                });
            }

            function pageUrl(tab, subtab) {
                return subtab && subtab !== 'default'
                    ? `${CONFIG.pageBase}/${tab}/${subtab}`
                    : `${CONFIG.pageBase}/${tab}`;
            }

            function loadTab(tab, subtab, push, extra) {
                current = { tab, subtab: subtab || 'default', extra: extra || {} };
                setActiveTab(tab);

                const isClaims = tab === 'claims';
                const isTrends = tab === 'trends';
                document.getElementById('opsDateRangeWrapper').classList.toggle('hidden', isClaims);
                document.getElementById('opsMonthPickerWrapper').classList.toggle('hidden', !isClaims);
                document.getElementById('opsTrendsMetricWrapper')?.classList.toggle('hidden', !isTrends);

                content.innerHTML =
                    '<div class="flex items-center justify-center py-20 text-slate-400 text-sm gap-2">' +
                    '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Loading…</div>';
                if (window.lucide) lucide.createIcons();

                const params = dateParams();
                Object.entries(current.extra).forEach(([k, v]) => v && params.set(k, v));
                params.set('_cb', new Date().getTime()); // cache buster
                const url = `${CONFIG.dataBase}/${tab}/${current.subtab}?${params.toString()}`;
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Cache-Control': 'no-cache' } })
                    .then(r => r.text())
                    .then(html => {
                        content.innerHTML = html;
                        Array.from(content.querySelectorAll("script")).forEach(oldScript => {
                            const newScript = document.createElement("script");
                            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                            newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                            oldScript.parentNode.replaceChild(newScript, oldScript);
                        });
                        if (window.lucide) lucide.createIcons();
                        if (window.DDS && DDS.sortableAll) DDS.sortableAll(content);
                    })
                    .catch(() => {
                        content.innerHTML =
                            '<div class="py-20 text-center text-red-500 text-sm">Failed to load this tab.</div>';
                    });

                if (push) history.pushState({ tab, subtab: current.subtab }, '', pageUrl(tab, current.subtab));
            }

            // Main tab clicks
            document.querySelectorAll('.ops-tab').forEach(el => {
                el.addEventListener('click', e => {
                    e.preventDefault();
                    loadTab(el.dataset.opsTab, 'default', true);
                });
            });

            // Delegated: subtab clicks + search inside the injected fragment
            content.addEventListener('click', e => {
                const sub = e.target.closest('[data-ops-subtab]');
                if (sub) {
                    e.preventDefault();
                    loadTab(current.tab, sub.dataset.opsSubtab, true);
                }
            });
            content.addEventListener('input', e => {
                if (e.target.matches('[data-ops-search]')) {
                    const q = e.target.value.toLowerCase();
                    content.querySelectorAll('tbody tr').forEach(tr => {
                        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
                    });
                }
            });
            content.addEventListener('click', e => {
                if (e.target.closest('[data-ops-export]')) exportCsv();
            });
            // Grouping toggles (e.g. Production Details Date/Provider)
            content.addEventListener('change', e => {
                if (e.target.matches('[data-ops-group]')) {
                    const groups = [...content.querySelectorAll('[data-ops-group]:checked')]
                        .map(c => c.dataset.opsGroup);
                    loadTab(current.tab, current.subtab, false, { group: groups.join(',') });
                }
            });

            function exportCsv() {
                const table = content.querySelector('table');
                if (!table) return;
                const rows = [...table.querySelectorAll('tr')].map(tr =>
                    [...tr.querySelectorAll('th,td')]
                        .map(c => `"${c.textContent.trim().replace(/\s+/g, ' ').replace(/"/g, '""')}"`)
                        .join(',')
                ).join('\n');
                const a = document.createElement('a');
                a.href = URL.createObjectURL(new Blob([rows], { type: 'text/csv' }));
                a.download = `operations-${current.tab}-${current.subtab}.csv`;
                a.click();
            }

            // Trends Metric dropdown handling
            const trendsDropdownBtn = document.getElementById('opsTrendsDropdownBtn');
            const trendsDropdownMenu = document.getElementById('opsTrendsDropdownMenu');
            const trendsSearchInput = document.getElementById('opsTrendsSearch');
            const trendsSelectedLabel = document.getElementById('opsTrendsSelectedLabel');
            const trendsHiddenInput = document.getElementById('opsTrendsMetric');

            if (trendsDropdownBtn && trendsDropdownMenu) {
                trendsDropdownBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    trendsDropdownMenu.classList.toggle('hidden');
                    if (!trendsDropdownMenu.classList.contains('hidden')) {
                        if (trendsSearchInput) {
                            trendsSearchInput.value = '';
                            document.querySelectorAll('.ops-trends-option').forEach(opt => opt.style.display = '');
                            trendsSearchInput.focus();
                        }
                    }
                });

                document.addEventListener('click', (e) => {
                    if (!e.target.closest('#opsTrendsDropdown')) {
                        trendsDropdownMenu.classList.add('hidden');
                    }
                });

                trendsSearchInput?.addEventListener('input', (e) => {
                    const query = e.target.value.toLowerCase();
                    document.querySelectorAll('.ops-trends-option').forEach(opt => {
                        const txt = opt.textContent.toLowerCase();
                        opt.style.display = txt.includes(query) ? '' : 'none';
                    });
                });

                document.querySelectorAll('.ops-trends-option').forEach(opt => {
                    opt.addEventListener('click', () => {
                        const val = opt.dataset.value;
                        const lbl = opt.dataset.label;
                        if (trendsHiddenInput) trendsHiddenInput.value = val;
                        if (trendsSelectedLabel) trendsSelectedLabel.textContent = lbl;
                        document.querySelectorAll('.ops-trends-option').forEach(o => {
                            o.classList.remove('bg-emerald-50/60', 'font-bold', 'text-emerald-700');
                        });
                        opt.classList.add('bg-emerald-50/60', 'font-bold', 'text-emerald-700');
                        trendsDropdownMenu.classList.add('hidden');

                        if (current.tab === 'trends') {
                            loadTab('trends', current.subtab, false, current.extra);
                        }
                    });
                });
            }

            // Filters re-fetch the current tab, preserving any grouping state
            window.opsDateApplied = () => loadTab(current.tab, current.subtab, false, current.extra);
            document.getElementById('opsUpdateBtn').addEventListener('click',
                () => loadTab(current.tab, current.subtab, false, current.extra));
            document.getElementById('opsLocation').addEventListener('change',
                () => loadTab(current.tab, current.subtab, false, current.extra));
            document.getElementById('opsMonthPicker').addEventListener('change',
                () => loadTab(current.tab, current.subtab, false, current.extra));
            document.getElementById('opsLineOfBusiness').addEventListener('change',
                () => loadTab(current.tab, current.subtab, false, current.extra));

            // Browser back/forward
            window.addEventListener('popstate', e => {
                const s = e.state || {};
                loadTab(s.tab || CONFIG.activeTab, s.subtab || 'default', false);
            });

            // Initial load — wait for the date picker to initialise so params are correct
            (function boot() {
                const ready = window.jQuery && jQuery('#opsDateRange').data('daterangepicker');
                if (!ready) return setTimeout(boot, 30);
                history.replaceState({ tab: current.tab, subtab: current.subtab }, '',
                    pageUrl(current.tab, current.subtab));
                loadTab(current.tab, current.subtab, false);
            })();
        })();
    </script>

    <x-app-components.patient-modal />

</x-app-layout>