@props(['id'])

{{--
x-app-components.datatable-modal
─────────────────────────────────────────────────
A highly reusable DataTables modal for displaying grid data.
Automatically un-paginates (paging: false) and makes each column sortable on the frontend.
Call from JS via: openDataTableModal('modalId', 'Title', columnsConfig, dataArray);
--}}

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div onclick="closeDataTableModal('{{ $id }}')"
        class="absolute inset-0 bg-black/60 backdrop-blur-sm cursor-pointer"></div>

    <!-- Modal Box -->
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-5xl flex flex-col max-h-[90vh]">

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 id="{{ $id }}Title" class="text-xl font-bold text-slate-800">Data Table</h3>
            <button onclick="closeDataTableModal('{{ $id }}')"
                class="text-slate-400 hover:text-slate-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 overflow-y-auto">
            <div id="{{ $id }}Loading" class="text-center py-10 text-slate-400 hidden">Loading...</div>
            <div id="{{ $id }}TableContainer">
                <table id="{{ $id }}Table" class="w-full text-sm text-left text-slate-700 stripe hover row-border"
                    style="width:100%">
                    <thead class="text-xs uppercase bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    /**
     * openDataTableModal
     * @param {string} modalId - The ID of the modal (passed to component)
     * @param {string} title - Modal Title
     * @param {array} columns - DataTables column definitions e.g. [{ data: 'col_key', title: 'Column Name' }]
     * @param {array} data - The array of objects for the table
     */
    function openDataTableModal(modalId, title, columns, data) {
        $('#' + modalId).removeClass('hidden');
        $('body').css('overflow', 'hidden');
        $('#' + modalId + 'Title').text(title);

        $('#' + modalId + 'Loading').addClass('hidden');
        $('#' + modalId + 'TableContainer').removeClass('hidden');

        var tableId = '#' + modalId + 'Table';

        // Destroy existing DataTables instance if exists
        if ($.fn.DataTable.isDataTable(tableId)) {
            $(tableId).DataTable().clear().destroy();
            $(tableId + ' thead').empty(); // Remove old headers since columns might change
            $(tableId + ' tbody').empty();
        }

        // Setup tfoot element with matching cell count before DataTable initialization
        var tfoot = $(tableId + ' tfoot');
        if (tfoot.length === 0) {
            $(tableId).append('<tfoot class="bg-slate-50 border-t border-slate-200"></tfoot>');
            tfoot = $(tableId + ' tfoot');
        }
        tfoot.empty();

        var footerRowHtml = '<tr>';
        columns.forEach(function () {
            footerRowHtml += '<th class="text-left font-bold text-slate-800 py-3 px-4 border-t border-slate-200 text-sm"></th>';
        });
        footerRowHtml += '</tr>';
        tfoot.html(footerRowHtml);

        // Initialize DataTable
        DDS.dataTable(tableId, {
            data: data,
            columns: columns,
            paging: false,           // Show all data
            info: true,
            searching: true,         // Allows client-side filtering via search box
            ordering: true,          // Allow column sorting
            order: [],               // Default to no initial sort to maintain API order, or set to [[0, 'asc']]
            destroy: true,
            autoWidth: false,
            language: {
                search: "",
                searchPlaceholder: "Search...",
                info: "Showing _TOTAL_ entries",
                infoEmpty: "Showing 0 entries"
            },
            dom: '<"flex flex-wrap items-center justify-between mb-4"f<"text-sm text-slate-500"i>>rt<"mt-4"p>',
            footerCallback: function (row, data, start, end) {
                var api = this.api();
                var footer = $(api.table().footer());
                if (footer.length === 0) return;

                var intVal = function (i) {
                    return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
                };

                var footerRow = footer.find('tr').first();
                if (footerRow.length === 0) return;

                columns.forEach(function (col, idx) {
                    var cell = footerRow.find('th').eq(idx);
                    if (idx === 0) {
                        cell.html('Total').addClass('text-slate-800 font-bold bg-slate-50 border-t border-slate-200 py-3 px-4 text-sm');
                    } else if (col.data === 'count') {
                        var total = api.column(idx).data().reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                        cell.html(total).addClass('text-slate-800 font-bold bg-slate-50 border-t border-slate-200 py-3 px-4 text-sm');
                    } else if (col.data === 'amount') {
                        var total = api.column(idx).data().reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                        var formatted = total < 0
                            ? '($' + Math.abs(total).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ')'
                            : '$' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        cell.html(formatted).addClass('text-slate-800 font-bold bg-slate-50 border-t border-slate-200 py-3 px-4 text-sm');
                    } else {
                        cell.html('').addClass('bg-slate-50 border-t border-slate-200 py-3 px-4');
                    }
                });
            }
        });

        // Add Tailwind classes to DataTable components post-init
        $(tableId + '_wrapper .dataTables_filter input')
            .addClass('border border-slate-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500')
            .attr('placeholder', 'Search rows...');
    }

    /**
     * setModalLoading
     * @param {string} modalId - The ID of the modal
     * @param {string} title - The title while loading
     */
    function setDataTableModalLoading(modalId, title) {
        $('#' + modalId).removeClass('hidden');
        $('body').css('overflow', 'hidden');
        $('#' + modalId + 'Title').text(title);
        $('#' + modalId + 'Loading').removeClass('hidden');
        $('#' + modalId + 'TableContainer').addClass('hidden');
    }

    function closeDataTableModal(modalId) {
        $('#' + modalId).addClass('hidden');
        $('body').css('overflow', '');
    }

    // Escape to close
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            $('.fixed.z-50').not('.hidden').addClass('hidden');
            $('body').css('overflow', '');
        }
    });
</script>

<style>
    /* Striped styling options */
    #{{ $id }}Table tbody tr {
        transition: background-color 0.15s ease-in-out;
    }

    #{{ $id }}Table tbody tr:nth-child(even) {
        background-color: #f8fafc !important;
    }

    #{{ $id }}Table tbody tr:nth-child(odd) {
        background-color: #ffffff !important;
    }

    #{{ $id }}Table tbody tr:hover {
        background-color: #f1f5f9 !important;
    }

    /* Sort headers custom arrows */
    #{{ $id }}Table th {
        position: relative;
        cursor: pointer;
        vertical-align: middle;
        padding-right: 28px !important;
    }

    /* Hide default 2.0 Tailwind order element if present */
    #{{ $id }}Table th .dt-column-order {
        display: none !important;
    }

    /* Add default sort decoration wrapper font */
    #{{ $id }}Table th.dt-orderable-asc::after,
    #{{ $id }}Table th.dt-orderable-desc::after {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        content: "↕";
        font-size: 13px;
        color: #cbd5e1;
        font-weight: normal;
        opacity: 0.7;
    }

    /* Active sorting colors and direction decoration */
    #{{ $id }}Table th.dt-ordering-asc::after {
        content: "▲" !important;
        color: #10b981 !important;
        font-size: 10px !important;
        opacity: 1;
    }

    #{{ $id }}Table th.dt-ordering-desc::after {
        content: "▼" !important;
        color: #10b981 !important;
        font-size: 10px !important;
        opacity: 1;
    }
</style>