<x-app-layout>

{{-- ── Chart.js ─────────────────────────────────────────────────────────────── --}}
@once('chartjs')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
@endonce

<div class="flex flex-col h-full">

    {{-- ── Page header ──────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between px-6 py-4 bg-white border-b border-slate-200 shrink-0">
        <div class="flex items-center gap-3">
            <i data-lucide="stethoscope" class="w-5 h-5 text-blue-600"></i>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Provider Portal</h1>
        </div>
        <button class="inline-flex items-center gap-2 bg-slate-800 text-emerald-400 hover:bg-slate-700 text-xs font-semibold px-4 py-2 rounded-lg transition-colors">
            <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
            Quick Start Guide
        </button>
    </div>

    {{-- ── Scrollable body ──────────────────────────────────────────────────── --}}
    <div class="flex-1 overflow-y-auto p-6 space-y-5">

        {{-- ── Filter bar ───────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
            <div class="flex flex-wrap items-center gap-3">

                {{-- Location --}}

                {{-- Provider type --}}
                <div class="flex flex-col gap-0.5">
                    <label class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Provider Type</label>
                    <select id="provTypeSelect"
                        class="text-sm border border-slate-300 rounded-lg px-3 py-1.5 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-300 min-w-[110px]">
                        <option value="all">All</option>
                        <option value="hygiene">Hygienist</option>
                        <option value="doctor">Doctor</option>
                    </select>
                </div>

                {{-- Provider multi-select --}}
                <div class="flex flex-col gap-0.5" id="provMultiWrap" style="position:relative">
                    <label class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Providers</label>
                    <button type="button" id="provDropBtn"
                        class="inline-flex items-center gap-2 text-sm border border-slate-300 rounded-lg px-3 py-1.5 bg-white text-slate-700 hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-300 min-w-[170px] justify-between">
                        <span>All Providers</span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                    </button>
                    {{-- Dropdown panel --}}
                    <div id="provDropPanel"
                        class="hidden absolute top-full left-0 mt-1 w-72 bg-white border border-slate-200 rounded-xl shadow-xl z-50">
                        <div class="p-2 border-b border-slate-100">
                            <input type="text" id="provDropSearch" placeholder="Search providers…"
                                class="w-full text-sm border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        </div>
                        <div id="provDropList" class="max-h-52 overflow-y-auto py-1 text-sm">
                            <div class="px-4 py-6 text-center text-slate-400 text-xs">Loading…</div>
                        </div>
                        <div class="p-2 border-t border-slate-100 flex items-center justify-between">
                            <button id="provClearBtn"
                                class="text-xs text-red-500 hover:text-red-700 font-medium px-2 py-1 rounded hover:bg-red-50">Clear all</button>
                            <button id="provApplyBtn"
                                class="text-xs bg-emerald-500 hover:bg-emerald-600 text-white font-medium px-4 py-1.5 rounded-lg">Apply</button>
                        </div>
                    </div>
                </div>

                {{-- Date range --}}
                <div class="flex flex-col gap-0.5">
                    <label class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Date Range</label>
                    <x-daterange-picker id="portalDateRange" />
                </div>

                {{-- Refresh --}}
                <div class="flex flex-col gap-0.5 pt-[18px]">
                    <button id="refreshBtn"
                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-1.5 rounded-lg transition-colors shadow-sm">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                        Refresh
                    </button>
                </div>

                {{-- Mode toggle pushed to right --}}
                <div class="flex flex-col gap-0.5 pt-[18px] ml-auto">
                    <div class="flex gap-0 bg-slate-100 rounded-lg p-0.5">
                        <button class="mode-btn active-mode text-xs font-semibold px-4 py-1.5 rounded-md bg-white text-slate-800 shadow-sm transition-all" data-mode="daily">Daily</button>
                        <button class="mode-btn text-xs font-semibold px-4 py-1.5 rounded-md text-slate-500 hover:text-slate-700 transition-all" data-mode="weekly">Weekly</button>
                        <button class="mode-btn text-xs font-semibold px-4 py-1.5 rounded-md text-slate-500 hover:text-slate-700 transition-all" data-mode="monthly">Monthly</button>
                    </div>
                </div>
            </div>

            {{-- Provider chips --}}
            <div id="provChipsRow" class="hidden flex-wrap gap-2 mt-3 pt-3 border-t border-slate-100"></div>
        </div>

        {{-- ── Chart ────────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-slate-800">Daily Financials</h2>
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span class="inline-block w-3 h-3 rounded-full bg-blue-500"></span> Production
                </div>
            </div>
            {{-- Skeleton --}}
            <div id="chartSkeleton" class="w-full rounded-lg bg-slate-100 animate-pulse" style="height:280px"></div>
            <div style="position:relative; height:280px; display:none" id="chartWrapper">
                <canvas id="portalChart"></canvas>
            </div>
        </div>

        {{-- ── Table ────────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="relative flex-1 max-w-xs">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none"></i>
                    <input type="text" id="portalSearch" placeholder="Search provider…"
                        class="w-full pl-8 pr-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300">
                </div>
                <button id="exportCsvBtn"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold border border-slate-300 text-slate-600 hover:border-slate-400 hover:bg-slate-50 px-3 py-1.5 rounded-lg transition-colors">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    Export CSV
                </button>
            </div>

            {{-- Table skeleton --}}
            <div id="tableSkeleton" class="space-y-2">
                @for($i=0;$i<6;$i++)
                <div class="h-8 bg-slate-100 rounded animate-pulse w-full" style="opacity:{{ 1 - $i*0.12 }}"></div>
                @endfor
            </div>

            <div id="provTableWrap" class="hidden transition-opacity duration-200">
                <x-data-table id="portalTable" min-width="1640px" max-height="520px">
                    <x-slot:head>
                        <tr>
                            <th rowspan="2" class="dt-col-sticky px-4 py-2.5 min-w-[160px] whitespace-nowrap text-left">Provider</th>
                            <th rowspan="2" class="px-4 py-2.5 whitespace-nowrap text-left">Office</th>
                            <th rowspan="2" class="px-4 py-2.5 whitespace-nowrap text-left">Provider Type</th>
                            <th rowspan="2" class="px-4 py-2.5 whitespace-nowrap text-left">Date</th>
                            <th rowspan="2" class="px-4 py-2.5 whitespace-nowrap text-right min-w-[120px]">Avg. Rev / Hyg Day</th>
                            <th rowspan="2" class="px-4 py-2.5 whitespace-nowrap text-right min-w-[100px]">Prod. / Visit</th>
                            <th rowspan="2" class="px-4 py-2.5 whitespace-nowrap text-right">Visits/Day</th>
                            <th colspan="7" class="px-4 py-2 text-center border-l border-slate-300 bg-slate-100 text-slate-600">Adjunctive Services</th>
                            <th rowspan="2" class="px-4 py-2.5 whitespace-nowrap text-right border-l border-slate-200">Retention</th>
                        </tr>
                        <tr>
                            <th class="px-4 py-2.5 text-center border-l border-slate-300">Whitening</th>
                            <th class="px-4 py-2.5 text-center">Irrigation</th>
                            <th class="px-4 py-2.5 text-center">Fluoride</th>
                            <th class="px-4 py-2.5 text-center">Sealants</th>
                            <th class="px-4 py-2.5 text-center">Laser</th>
                            <th class="px-4 py-2.5 text-center">Toothbrushes</th>
                            <th class="px-4 py-2.5 text-center font-bold">Total</th>
                        </tr>
                    </x-slot:head>
                </x-data-table>
            </div>
        </div>

    </div>{{-- /scrollable body --}}
</div>

{{-- ── Scripts ─────────────────────────────────────────────────────────────── --}}
<script>
(function () {
    'use strict';

    var _mode      = 'daily';
    var _chart     = null;
    var _table     = null;
    var _providers = [];          // [{id, name, type, is_hyg}]
    var _selected  = new Set();   // selected ProvNums (integers)
    var _dropOpen  = false;
    var BASE = '';

    /* ── Format helpers ─────────────────────────────────────────────────────── */
    // Canonical formatter (single source: DDS.fmt.money in ui.js).
    function fmtMoney(v) {
        return DDS.fmt.money(v);
    }
    function fmtDec1(v) {
        if (v === null || v === undefined) return '—';
        return parseFloat(v).toFixed(1);
    }

    /* ── Params builder ─────────────────────────────────────────────────────── */
    function buildParams(s, e) {
        var p = new URLSearchParams({
            start_date:    s,
            end_date:      e,
            mode:          _mode,
            provider_type: document.getElementById('provTypeSelect').value,
        });
        _selected.forEach(function (id) { p.append('providers[]', id); });
        return p.toString();
    }

    /* ── Multi-select ───────────────────────────────────────────────────────── */
    function loadProviders() {
        fetch(BASE + '/provider-portal/providers')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                _providers = data;
                renderDropdownList('');
            });
    }

    function renderDropdownList(search) {
        var list  = document.getElementById('provDropList');
        var ptype = document.getElementById('provTypeSelect').value;
        var q     = search.toLowerCase();
        var items = _providers.filter(function (p) {
            if (ptype === 'hygiene' && !p.is_hyg) return false;
            if (ptype === 'doctor'  &&  p.is_hyg) return false;
            if (q && !p.name.toLowerCase().includes(q)) return false;
            return true;
        });
        if (items.length === 0) {
            list.innerHTML = '<div class="px-4 py-4 text-center text-slate-400 text-xs">No providers found</div>';
            return;
        }
        list.innerHTML = items.map(function (p) {
            var chk = _selected.has(p.id) ? 'checked' : '';
            return '<label class="flex items-center gap-2.5 px-3 py-2 hover:bg-slate-50 cursor-pointer">' +
                '<input type="checkbox" class="prov-chk w-3.5 h-3.5 accent-emerald-500" value="' + p.id + '" ' + chk + '>' +
                '<span class="flex-1 text-sm text-slate-700">' + p.name + '</span>' +
                '<span class="text-[10px] text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">' + p.type + '</span>' +
                '</label>';
        }).join('');
        list.querySelectorAll('.prov-chk').forEach(function (chk) {
            chk.addEventListener('change', function () {
                var id = parseInt(this.value, 10);
                if (this.checked) _selected.add(id);
                else _selected.delete(id);
                renderChips();
                updateDropLabel();
            });
        });
    }

    function renderChips() {
        var row = document.getElementById('provChipsRow');
        if (_selected.size === 0) {
            row.classList.add('hidden');
            row.innerHTML = '';
            return;
        }
        row.classList.remove('hidden');
        row.classList.add('flex');
        row.innerHTML = Array.from(_selected).map(function (id) {
            var p    = _providers.find(function (x) { return x.id === id; });
            var name = p ? p.name : '#' + id;
            return '<span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full px-3 py-0.5 text-xs font-medium">' +
                name +
                '<button type="button" data-id="' + id + '" class="remove-chip text-emerald-400 hover:text-emerald-700 text-base leading-none">×</button>' +
                '</span>';
        }).join('');
        row.querySelectorAll('.remove-chip').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = parseInt(this.dataset.id, 10);
                _selected.delete(id);
                renderChips();
                updateDropLabel();
                renderDropdownList(document.getElementById('provDropSearch').value);
            });
        });
    }

    function updateDropLabel() {
        var span = document.querySelector('#provDropBtn span');
        span.textContent = _selected.size === 0
            ? 'All Providers'
            : _selected.size + ' Provider' + (_selected.size > 1 ? 's' : '') + ' selected';
    }

    function toggleDrop() {
        _dropOpen = !_dropOpen;
        document.getElementById('provDropPanel').classList.toggle('hidden', !_dropOpen);
        if (_dropOpen) document.getElementById('provDropSearch').focus();
    }

    document.addEventListener('click', function (e) {
        if (!document.getElementById('provMultiWrap').contains(e.target)) {
            document.getElementById('provDropPanel').classList.add('hidden');
            _dropOpen = false;
        }
    });

    document.getElementById('provDropBtn').addEventListener('click', function (e) {
        e.stopPropagation();
        toggleDrop();
    });
    document.getElementById('provDropSearch').addEventListener('input', function () {
        renderDropdownList(this.value);
    });
    document.getElementById('provClearBtn').addEventListener('click', function () {
        _selected.clear();
        renderChips();
        updateDropLabel();
        renderDropdownList(document.getElementById('provDropSearch').value);
    });
    document.getElementById('provApplyBtn').addEventListener('click', function () {
        document.getElementById('provDropPanel').classList.add('hidden');
        _dropOpen = false;
        refresh();
    });

    document.getElementById('provTypeSelect').addEventListener('change', function () {
        _selected.clear();
        renderChips();
        updateDropLabel();
        renderDropdownList('');
    });

    /* ── Chart ──────────────────────────────────────────────────────────────── */
    function fetchChart(start, end) {
        document.getElementById('chartSkeleton').style.display = '';
        document.getElementById('chartWrapper').style.display  = 'none';

        fetch(BASE + '/provider-portal/chart?' + buildParams(start, end))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                document.getElementById('chartSkeleton').style.display  = 'none';
                document.getElementById('chartWrapper').style.display   = '';

                var labels = data.map(function (d) { return d.label; });
                var values = data.map(function (d) { return d.production; });

                if (_chart) {
                    _chart.data.labels             = labels;
                    _chart.data.datasets[0].data   = values;
                    _chart.data.datasets[0].pointRadius = labels.length > 90 ? 0 : 2;
                    _chart.update('active');
                    return;
                }

                _chart = new Chart(
                    document.getElementById('portalChart').getContext('2d'),
                    {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Production',
                                data: values,
                                borderColor: 'rgb(59,130,246)',
                                backgroundColor: 'rgba(59,130,246,0.10)',
                                fill: true,
                                tension: 0.2,
                                pointRadius: labels.length > 90 ? 0 : 2,
                                pointHoverRadius: 5,
                                borderWidth: 2,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: 'rgba(15,23,42,0.9)',
                                    titleFont: { size: 11 },
                                    bodyFont:  { size: 12, weight: 'bold' },
                                    callbacks: {
                                        label: function (ctx) {
                                            return ' Production: ' + fmtMoney(ctx.raw);
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        maxTicksLimit: 14,
                                        font: { size: 10 },
                                        color: '#94a3b8',
                                    },
                                    grid: { color: 'rgba(0,0,0,0.04)' }
                                },
                                y: {
                                    ticks: {
                                        font: { size: 10 },
                                        color: '#94a3b8',
                                        callback: function (v) {
                                            if (v < 0) return '-$' + Math.abs(v / 1000).toFixed(0) + 'k';
                                            return '$' + (v / 1000).toFixed(0) + 'k';
                                        }
                                    },
                                    grid: { color: 'rgba(0,0,0,0.05)' }
                                }
                            }
                        }
                    }
                );
            });
    }

    /* ── Table ──────────────────────────────────────────────────────────────── */
    function fetchTable(start, end) {
        document.getElementById('tableSkeleton').classList.remove('hidden');
        document.getElementById('provTableWrap').classList.add('hidden');

        fetch(BASE + '/provider-portal/table?' + buildParams(start, end))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                document.getElementById('tableSkeleton').classList.add('hidden');
                document.getElementById('provTableWrap').classList.remove('hidden');

                if (_table) {
                    _table.clear().rows.add(data).draw();
                    return;
                }

                _table = DDS.dataTable(document.getElementById('portalTable'), {
                    data: data,
                    dom: 'tip',
                    pageLength: 25,
                    columns: [
                        {
                            data: 'provider',
                            className: 'dt-col-sticky px-4 py-3 whitespace-nowrap'
                        },
                        { data: 'office',        className: 'px-4 py-3 whitespace-nowrap' },
                        { data: 'provider_type', className: 'px-4 py-3 whitespace-nowrap' },
                        { data: 'date',          className: 'px-4 py-3 whitespace-nowrap tabular-nums' },
                        {
                            data: 'avg_rev_hyg',
                            className: 'px-4 py-3 text-right tabular-nums',
                            render: function (v) { return fmtMoney(v); }
                        },
                        {
                            data: 'prod_per_visit',
                            className: 'px-4 py-3 text-right tabular-nums',
                            render: function (v) { return fmtMoney(v); }
                        },
                        {
                            data: 'visits_day',
                            className: 'px-4 py-3 text-right tabular-nums',
                            render: function (v) { return fmtDec1(v); }
                        },
                        { data: 'whitening',    className: 'px-4 py-3 text-center tabular-nums border-l border-slate-200' },
                        { data: 'irrigation',   className: 'px-4 py-3 text-center tabular-nums' },
                        { data: 'fluoride',     className: 'px-4 py-3 text-center tabular-nums' },
                        { data: 'sealants',     className: 'px-4 py-3 text-center tabular-nums' },
                        { data: 'laser',        className: 'px-4 py-3 text-center tabular-nums' },
                        { data: 'toothbrushes', className: 'px-4 py-3 text-center tabular-nums' },
                        {
                            data: 'adj_total',
                            className: 'px-4 py-3 text-center tabular-nums font-semibold text-slate-700'
                        },
                        {
                            data: 'retention',
                            className: 'px-4 py-3 text-right tabular-nums border-l border-slate-200',
                            render: function (v) { return v !== null ? v + '%' : '—'; }
                        },
                    ],
                    language: {
                        emptyTable: 'No data for this period.',
                        info: 'Showing _START_ – _END_ of _TOTAL_ rows',
                        infoEmpty: 'No records',
                        paginate: { previous: '‹', next: '›' }
                    }
                });
            });
    }

    /* ── Custom search input ────────────────────────────────────────────────── */
    document.getElementById('portalSearch').addEventListener('input', function () {
        if (_table) _table.search(this.value).draw();
    });

    /* ── Export CSV ─────────────────────────────────────────────────────────── */
    document.getElementById('exportCsvBtn').addEventListener('click', function () {
        if (!_table) return;
        var keys    = ['provider','office','provider_type','date','avg_rev_hyg','prod_per_visit','visits_day','whitening','irrigation','fluoride','sealants','laser','toothbrushes','adj_total','retention'];
        var headers = ['Provider','Office','Provider Type','Date','Avg Rev/Hyg Day','Prod/Visit','Visits/Day','Whitening','Irrigation','Fluoride','Sealants','Laser','Toothbrushes','Adj Total','Retention'];
        var rows    = _table.rows({ search: 'applied' }).data().toArray();
        var csv     = [headers.join(',')]
            .concat(rows.map(function (r) {
                return keys.map(function (k) { return JSON.stringify(r[k] !== null ? r[k] : ''); }).join(',');
            }))
            .join('\n');
        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        var url  = URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href   = url;
        a.download = 'provider-portal-' + new Date().toISOString().slice(0,10) + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });

    /* ── Mode toggle ────────────────────────────────────────────────────────── */
    document.querySelectorAll('.mode-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            _mode = this.dataset.mode;
            document.querySelectorAll('.mode-btn').forEach(function (b) {
                b.classList.remove('bg-white', 'text-slate-800', 'shadow-sm', 'active-mode');
                b.classList.add('text-slate-500');
            });
            this.classList.remove('text-slate-500');
            this.classList.add('bg-white', 'text-slate-800', 'shadow-sm', 'active-mode');
            refresh();
        });
    });

    /* ── Refresh ────────────────────────────────────────────────────────────── */
    function refresh() {
        var drp = $('#portalDateRange').data('daterangepicker');
        if (!drp) return;
        var s = drp.startDate.format('YYYY-MM-DD');
        var e = drp.endDate.format('YYYY-MM-DD');
        fetchChart(s, e);
        fetchTable(s, e);
    }

    document.getElementById('refreshBtn').addEventListener('click', refresh);

    /* ── Bootstrap ──────────────────────────────────────────────────────────── */
    loadProviders();

    var _initInterval = setInterval(function () {
        if (typeof moment === 'undefined' || typeof Chart === 'undefined') return;
        clearInterval(_initInterval);
        if (typeof $ !== 'undefined' && $.fn.daterangepicker) {
            var drp = $('#portalDateRange').data('daterangepicker');
            if (drp) {
                fetchChart(drp.startDate.format('YYYY-MM-DD'), drp.endDate.format('YYYY-MM-DD'));
                fetchTable(drp.startDate.format('YYYY-MM-DD'), drp.endDate.format('YYYY-MM-DD'));
                return;
            }
        }
        var s = moment().startOf('year').format('YYYY-MM-DD');
        var e = moment().format('YYYY-MM-DD');
        fetchChart(s, e);
        fetchTable(s, e);
    }, 50);

    lucide.createIcons();
})();
</script>

</x-app-layout>
