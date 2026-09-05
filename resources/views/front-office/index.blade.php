<x-app-layout>
    <header
        class="bg-white border-b border-gray-200 sticky top-0 z-50 px-6 py-4 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-6">
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Front Office</h1>
            <div class="flex items-center gap-3">
                <select id="foDateType"
                    class="appearance-none bg-gray-100 border border-gray-300 rounded-lg px-2.5 py-1.5 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 text-gray-700 cursor-pointer">
                    <option value="month" selected>Month</option>
                    <option value="range">Date Range</option>
                </select>

                <div id="foMonthContainer">
                    <input type="month" id="frontOfficeMonth" value="{{ date('Y-m') }}"
                        class="appearance-none bg-gray-100 border border-gray-300 rounded-lg px-3 py-1.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 text-gray-700">
                </div>

                <div id="foRangeContainer" class="hidden flex items-center gap-1.5">
                    <input type="date" id="frontOfficeStartDate" value="{{ date('Y-m-01') }}"
                        class="appearance-none bg-gray-100 border border-gray-300 rounded-lg px-2.5 py-1.5 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 text-gray-700">
                    <span class="text-xs text-gray-400 font-bold">to</span>
                    <input type="date" id="frontOfficeEndDate" value="{{ date('Y-m-t') }}"
                        class="appearance-none bg-gray-100 border border-gray-300 rounded-lg px-2.5 py-1.5 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 text-gray-700">
                </div>

                <div class="relative">
                    
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
                <button id="updateStatsBtn"
                    class="bg-white hover:bg-gray-50 text-emerald-600 border border-emerald-500 font-medium text-sm px-4 py-1.5 rounded-lg transition-colors cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-arrows-rotate text-xs"></i> Update
                </button>
            </div>
        </div>

        <button
            class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm px-4 py-2 rounded-lg flex items-center gap-2 shadow-sm transition-colors">
            <i class="fa-solid fa-book-open"></i> Quick Start Guide
        </button>
    </header>

    <nav class="bg-white border-b border-gray-200 px-6 flex gap-6 text-sm font-medium text-gray-500">
        <a href="{{ route('front-office.index') }}"
            class="fo-nav-link border-b-2 py-3.5 px-1 transition-colors {{ ($activeTab ?? 'schedule') === 'schedule' ? 'border-emerald-500 text-emerald-600' : 'border-transparent hover:text-gray-700' }}">Schedule</a>
        <a href="{{ route('front-office.tasks') }}"
            class="fo-nav-link border-b-2 py-3.5 px-1 transition-colors {{ ($activeTab ?? '') === 'tasks' ? 'border-emerald-500 text-emerald-600' : 'border-transparent hover:text-gray-700' }}">Tasks</a>
        <a href="{{ route('front-office.collections') }}"
            class="fo-nav-link border-b-2 py-3.5 px-1 transition-colors {{ ($activeTab ?? '') === 'collections' ? 'border-emerald-500 text-emerald-600' : 'border-transparent hover:text-gray-700' }}">Collections</a>
        <a href="{{ route('front-office.kpis') }}"
            class="fo-nav-link border-b-2 py-3.5 px-1 transition-colors {{ ($activeTab ?? '') === 'kpis' ? 'border-emerald-500 text-emerald-600' : 'border-transparent hover:text-gray-700' }}">KPIs</a>
        <a href="{{ route('front-office.performance') }}"
            class="fo-nav-link border-b-2 py-3.5 px-1 transition-colors {{ ($activeTab ?? '') === 'performance' ? 'border-emerald-500 text-emerald-600' : 'border-transparent hover:text-gray-700' }}">Performance</a>
    </nav>

    <div id="foContentContainer">
        @if(($activeTab ?? 'schedule') === 'tasks')
            @include('front-office.partials.tasks')
        @elseif(($activeTab ?? '') === 'collections')
            @include('front-office.partials.collections')
        @elseif(($activeTab ?? '') === 'kpis')
            @include('front-office.partials.kpis')
        @elseif(($activeTab ?? '') === 'performance')
            @include('front-office.partials.performance')
        @else
            @include('front-office.partials.schedule')
        @endif
    </div>

    <script>
        window.getFoDateParams = function (extraParams) {
            let params = extraParams || {};
            let dateType = $('#foDateType').val() || 'month';

            if (dateType === 'range') {
                params.start_date = $('#frontOfficeStartDate').val();
                params.end_date = $('#frontOfficeEndDate').val();
            } else {
                params.month = $('#frontOfficeMonth').val();
                params.month_year = $('#frontOfficeMonth').val();
            }
            return params;
        };

        window.reloadAllFoData = function () {
            if (typeof window.hydrateDashboard === 'function') {
                window.hydrateDashboard();
            }
            if (typeof window.reloadFoTables === 'function') {
                window.reloadFoTables();
            }
        };

        window.exportTableToCSV = function ($table, filename) {
            if (!$table || !$table.length) return;

            let dtApi = ($.fn.DataTable && $.fn.DataTable.isDataTable($table[0])) ? $table.DataTable() : null;

            if (dtApi && dtApi.page && dtApi.page.info && dtApi.page.info().serverSide) {
                let ajaxParams = dtApi.ajax.params ? (dtApi.ajax.params() || {}) : {};
                ajaxParams.length = -1; // Request all records without pagination limits
                let ajaxUrl = typeof dtApi.ajax.url === 'function' ? dtApi.ajax.url() : (dtApi.ajax.url || window.location.href);

                $.get(ajaxUrl, ajaxParams, function (response) {
                    let data = response.data || response;
                    if (!Array.isArray(data) || data.length === 0) {
                        alert('No data available to export.');
                        return;
                    }

                    let columns = dtApi.settings()[0].aoColumns;
                    let rows = [];

                    // Header Row
                    let headerRow = [];
                    $table.find('thead th').each(function () {
                        let text = $(this).text().trim().replace(/\s+/g, ' ');
                        headerRow.push('"' + text.replace(/"/g, '""') + '"');
                    });
                    if (headerRow.length) {
                        rows.push(headerRow.join(','));
                    }

                    // Data Rows
                    data.forEach(function (row) {
                        let rowData = [];
                        columns.forEach(function (col) {
                            let val = '';
                            if (col.mData !== undefined && col.mData !== null) {
                                if (typeof col.mData === 'string') {
                                    val = row[col.mData] !== undefined && row[col.mData] !== null ? row[col.mData] : '';
                                } else if (typeof col.mData === 'function') {
                                    val = col.mData(row, 'display');
                                }
                            }
                            let tmp = document.createElement('DIV');
                            tmp.innerHTML = val;
                            val = (tmp.textContent || tmp.innerText || '').trim().replace(/\s+/g, ' ');
                            rowData.push('"' + val.replace(/"/g, '""') + '"');
                        });
                        rows.push(rowData.join(','));
                    });

                    let csvString = "\uFEFF" + rows.join("\n");
                    let blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
                    let link = document.createElement('a');
                    let url = URL.createObjectURL(blob);
                    let today = new Date().toISOString().slice(0, 10);
                    link.setAttribute('href', url);
                    link.setAttribute('download', (filename || 'front_office_export') + '_' + today + '.csv');
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }).fail(function () {
                    alert('Failed to export CSV. Please try again.');
                });
                return;
            }

            let rows = [];

            let headerRow = [];
            $table.find('thead th').each(function () {
                let text = $(this).text().trim().replace(/\s+/g, ' ');
                headerRow.push('"' + text.replace(/"/g, '""') + '"');
            });
            if (headerRow.length) {
                rows.push(headerRow.join(','));
            }

            if (dtApi) {
                let data = dtApi.rows({ search: 'applied' }).data().toArray();
                let columns = dtApi.settings()[0].aoColumns;

                data.forEach(function (row) {
                    let rowData = [];
                    columns.forEach(function (col) {
                        let val = '';
                        if (col.mData !== undefined && col.mData !== null) {
                            if (typeof col.mData === 'string') {
                                val = row[col.mData] !== undefined && row[col.mData] !== null ? row[col.mData] : '';
                            } else if (typeof col.mData === 'function') {
                                val = col.mData(row, 'display');
                            }
                        }
                        let tmp = document.createElement('DIV');
                        tmp.innerHTML = val;
                        val = (tmp.textContent || tmp.innerText || '').trim().replace(/\s+/g, ' ');
                        rowData.push('"' + val.replace(/"/g, '""') + '"');
                    });
                    rows.push(rowData.join(','));
                });
            } else {
                $table.find('tbody tr').each(function () {
                    let rowData = [];
                    $(this).find('td').each(function () {
                        let text = $(this).text().trim().replace(/\s+/g, ' ');
                        rowData.push('"' + text.replace(/"/g, '""') + '"');
                    });
                    if (rowData.length) {
                        rows.push(rowData.join(','));
                    }
                });
            }

            if (rows.length === 0 || (rows.length === 1 && headerRow.length > 0)) {
                alert('No data available to export.');
                return;
            }

            let csvString = "\uFEFF" + rows.join("\n");
            let blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
            let link = document.createElement('a');
            let url = URL.createObjectURL(blob);
            let today = new Date().toISOString().slice(0, 10);
            link.setAttribute('href', url);
            link.setAttribute('download', (filename || 'front_office_export') + '_' + today + '.csv');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };

        document.addEventListener('DOMContentLoaded', function () {
            $(document).on('change', '#foDateType', function () {
                if ($(this).val() === 'range') {
                    $('#foMonthContainer').addClass('hidden');
                    $('#foRangeContainer').removeClass('hidden');
                } else {
                    $('#foRangeContainer').addClass('hidden');
                    $('#foMonthContainer').removeClass('hidden');
                }
                window.reloadAllFoData();
            });

            $(document).on('change', '#frontOfficeMonth, #frontOfficeStartDate, #frontOfficeEndDate', function () {
                window.reloadAllFoData();
            });

            $(document).on('click', '#updateStatsBtn', function () {
                window.reloadAllFoData();
            });

            // SPA Tab Switching Engine
            $('.fo-nav-link').on('click', function (e) {
                e.preventDefault();
                let $this = $(this);
                let url = $this.attr('href');

                if ($this.hasClass('border-emerald-500')) return; // already active

                // Update styling
                $('.fo-nav-link').removeClass('border-emerald-500 text-emerald-600').addClass('border-transparent hover:text-gray-700');
                $this.removeClass('border-transparent hover:text-gray-700').addClass('border-emerald-500 text-emerald-600');

                // Push history state to URL bar
                history.pushState(null, '', url);

                // Fetch new tab content & replace
                $('#foContentContainer').html('<div class="p-16 flex justify-center items-center"><div class="animate-pulse text-emerald-600 font-semibold text-lg flex items-center gap-2"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</div></div>');

                $.get(url, function (html) {
                    $('#foContentContainer').html(html);
                }).fail(function () {
                    $('#foContentContainer').html('<div class="p-8 text-center text-red-500">Failed to load content. Please try again.</div>');
                });
            });

            window.addEventListener('popstate', function () {
                window.location.reload();
            });
        });
    </script>
</x-app-layout>