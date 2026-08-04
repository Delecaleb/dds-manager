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
                document.getElementById('opsDateRangeWrapper').classList.toggle('hidden', isClaims);
                document.getElementById('opsMonthPickerWrapper').classList.toggle('hidden', !isClaims);

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

            // Filters re-fetch the current tab, preserving any grouping state
            window.opsDateApplied = () => loadTab(current.tab, current.subtab, false, current.extra);
            document.getElementById('opsUpdateBtn').addEventListener('click',
                () => loadTab(current.tab, current.subtab, false, current.extra));
            document.getElementById('opsLocation').addEventListener('change',
                () => loadTab(current.tab, current.subtab, false, current.extra));
            document.getElementById('opsMonthPicker').addEventListener('change',
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