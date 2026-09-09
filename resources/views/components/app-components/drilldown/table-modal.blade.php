@props(['title', 'providerInfo' => null])

<div
    class="fixed inset-0 z-[120] flex items-center justify-center bg-black/40 backdrop-blur-sm p-4 animate-in fade-in zoom-in duration-200 ds-limitless-modal">
    <div class="bg-white rounded-lg shadow-xl border border-gray-200 w-full max-w-5xl flex flex-col max-h-[85vh]">
        <div class="p-6 border-b border-gray-100 bg-white rounded-t-lg">
            <div class="flex justify-between items-start">
                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ $title }}</h2>
                <button onclick="this.closest('.ds-limitless-modal').remove()"
                    class="text-gray-900 hover:text-gray-600 transition-colors p-1 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            @if (!empty($providerInfo))
                <div class="grid grid-cols-2 gap-8 pt-4">
                    <div>
                        <p class="text-xs font-bold text-gray-900">Provider</p>
                        <p class="text-xs text-gray-600 font-medium mt-0.5">{{ $providerInfo['name'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-900">Provider ID</p>
                        <p class="text-xs text-gray-600 font-medium mt-0.5">{{ $providerInfo['id'] }}</p>
                    </div>
                </div>
            @endif
        </div>
        <div class="flex-1 overflow-y-auto p-5 chunk-scrollbar">
            <div class="flex items-center justify-end mb-3">
                <button type="button" onclick="exportDrilldownModalCsv(this)"
                    class="dds-btn-accent font-bold px-4 py-1.5 rounded text-xs shrink-0 flex items-center gap-1.5 cursor-pointer shadow-sm hover:opacity-90 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Export CSV
                </button>
            </div>
            {{ $slot }}
        </div>
    </div>
</div>

<script>
    if (typeof window.exportDrilldownModalCsv !== 'function') {
        window.exportDrilldownModalCsv = function (btn) {
            var modal = (btn && btn.closest ? btn.closest('.ds-limitless-modal, .dds-modal') : null) || document.querySelector('.ds-limitless-modal, .dds-modal') || document;
            var table = modal.querySelector('table');
            if (!table) return;

            var titleEl = modal.querySelector('h2, h3, h4');
            var title = (titleEl ? titleEl.textContent : '').replace(/^Breakdown\s*\|\s*/i, '').trim() || 'breakdown';
            var filename = title.toLowerCase().replace(/[^a-z0-9]/g, '-') + '-' + new Date().toISOString().slice(0, 10) + '.csv';

            var headers = [];
            table.querySelectorAll('thead tr th').forEach(function (th) {
                var clone = th.cloneNode(true);
                clone.querySelectorAll('.pointer-events-none, svg, button, .dt-column-order').forEach(function (el) { el.remove(); });
                var txt = clone.textContent.trim().replace(/\s+/g, ' ');
                if (txt) {
                    headers.push('"' + txt.replace(/"/g, '""') + '"');
                }
            });

            var rows = [];
            if (window.jQuery && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable(table)) {
                var dt = jQuery(table).DataTable();
                var nodes = dt.rows({ search: 'applied' }).nodes();
                for (var i = 0; i < nodes.length; i++) {
                    var rowCells = [];
                    nodes[i].querySelectorAll('td').forEach(function (td) {
                        var clone = td.cloneNode(true);
                        clone.querySelectorAll('.pointer-events-none, svg, button').forEach(function (el) { el.remove(); });
                        var txt = clone.textContent.trim().replace(/\s+/g, ' ');
                        rowCells.push('"' + txt.replace(/"/g, '""') + '"');
                    });
                    if (rowCells.length) {
                        rows.push(rowCells.join(','));
                    }
                }
            }

            if (!rows.length) {
                table.querySelectorAll('tbody tr').forEach(function (tr) {
                    var rowCells = [];
                    tr.querySelectorAll('td').forEach(function (td) {
                        var clone = td.cloneNode(true);
                        clone.querySelectorAll('.pointer-events-none, svg, button').forEach(function (el) { el.remove(); });
                        var txt = clone.textContent.trim().replace(/\s+/g, ' ');
                        rowCells.push('"' + txt.replace(/"/g, '""') + '"');
                    });
                    if (rowCells.length && rowCells.join('') !== '""') {
                        rows.push(rowCells.join(','));
                    }
                });
            }

            var tfoot = table.querySelector('tfoot');
            if (tfoot) {
                var footCells = [];
                tfoot.querySelectorAll('td, th').forEach(function (c) {
                    var clone = c.cloneNode(true);
                    clone.querySelectorAll('.pointer-events-none, svg, button').forEach(function (el) { el.remove(); });
                    var txt = clone.textContent.trim().replace(/\s+/g, ' ');
                    footCells.push('"' + txt.replace(/"/g, '""') + '"');
                });
                if (footCells.length && footCells.some(function (v) { return v !== '""' && v !== '"Total:"'; })) {
                    rows.push(footCells.join(','));
                }
            }

            var csvContent = (headers.length ? [headers.join(',')] : []).concat(rows).join('\r\n');
            var blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            setTimeout(function () {
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }, 200);
        };
    }
</script>