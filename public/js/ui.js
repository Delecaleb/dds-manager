/* ============================================================================
   DDS Manager — shared UI module (single source of truth for front-end behavior)
   Loaded once in layouts/app.blade.php, after jQuery + DataTables.
   Exposes a single global: window.DDS
   No build step; plain ES5-safe-ish JS so it runs straight from the CDN stack.
   ============================================================================ */
(function (window, document) {
    'use strict';

    var DDS = window.DDS || {};

    /* ── Formatters (mirror the PHP ops_fmt so client + server agree) ───────── */
    DDS.fmt = {
        money: function (v) {
            if (v === null || v === undefined || v === '') return '—';
            var n = parseFloat(v);
            if (isNaN(n)) return '—';
            if (n === 0) return '$ 0';
            var abs = Math.abs(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            return n < 0 ? '$ (' + abs + ')' : '$ ' + abs;
        },
        percent: function (v, dp) {
            if (v === null || v === undefined || v === '') return '—';
            var n = parseFloat(v);
            if (isNaN(n)) return '—';
            return n.toLocaleString('en-US', { minimumFractionDigits: dp == null ? 2 : dp, maximumFractionDigits: dp == null ? 2 : dp }) + '%';
        },
        number: function (v) {
            if (v === null || v === undefined || v === '') return '—';
            var n = parseFloat(v);
            if (isNaN(n)) return '—';
            return Math.floor(n) === n ? n.toLocaleString('en-US') : n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    };
    // Back-compat aliases for the ~4 copy-pasted globals being retired.
    window.fmtMoney = DDS.fmt.money;

    /* ── Inject HTML and (re)activate its scripts + icons ───────────────────── */
    DDS.swapHtml = function (container, html) {
        var el = typeof container === 'string' ? document.querySelector(container) : container;
        if (!el) return;
        el.innerHTML = html;
        // Re-execute <script> tags that innerHTML leaves inert.
        el.querySelectorAll('script').forEach(function (old) {
            var s = document.createElement('script');
            if (old.src) { s.src = old.src; } else { s.textContent = old.textContent; }
            document.body.appendChild(s);
            old.remove();
        });
        if (window.lucide) window.lucide.createIcons();
        // Swapped-in markup (tab panels, drilldowns) gets its sortable tables wired
        // here, so no call site has to remember to do it.
        if (DDS.sortableAll) DDS.sortableAll(el);
    };

    /* ── URL-as-state helpers ───────────────────────────────────────────────── */
    DDS.url = {
        get: function (key) { return new URLSearchParams(window.location.search).get(key); },
        merge: function (params) {
            var u = new URL(window.location.href);
            Object.keys(params).forEach(function (k) {
                if (params[k] == null || params[k] === '') u.searchParams.delete(k);
                else u.searchParams.set(k, params[k]);
            });
            return u.pathname + u.search;
        }
    };

    /* ── Cross-page Date & Date Range Persistence (sessionStorage) ──────────── */
    var DATE_TTL_MS = 120 * 60 * 1000; // 2 hours (matches Laravel session lifetime)
    var RANGE_KEY = 'dds_active_date_range';
    var DATE_KEY = 'dds_selected_date';

    DDS.date = {
        getRange: function (defaultStart, defaultEnd) {
            try {
                var raw = window.sessionStorage.getItem(RANGE_KEY);
                if (raw) {
                    var parsed = JSON.parse(raw);
                    if (parsed && parsed.start && parsed.end) {
                        if (!parsed.ts || (Date.now() - parsed.ts) < DATE_TTL_MS) {
                            return { start: parsed.start, end: parsed.end, isDefault: false };
                        } else {
                            window.sessionStorage.removeItem(RANGE_KEY);
                        }
                    }
                }
            } catch (e) {}
            var s = defaultStart || (window.moment ? window.moment().startOf('month').format('YYYY-MM-DD') : '');
            var e = defaultEnd || (window.moment ? window.moment().format('YYYY-MM-DD') : '');
            return { start: s, end: e, isDefault: true };
        },

        setRange: function (start, end) {
            if (!start || !end) return;
            try {
                window.sessionStorage.setItem(RANGE_KEY, JSON.stringify({
                    start: start,
                    end: end,
                    ts: Date.now()
                }));
                window.sessionStorage.setItem(DATE_KEY, JSON.stringify({
                    date: start,
                    ts: Date.now()
                }));
            } catch (e) {}
        },

        getDate: function (defaultDate) {
            try {
                var raw = window.sessionStorage.getItem(DATE_KEY);
                if (raw) {
                    var parsed = JSON.parse(raw);
                    if (parsed && parsed.date) {
                        if (!parsed.ts || (Date.now() - parsed.ts) < DATE_TTL_MS) {
                            return parsed.date;
                        } else {
                            window.sessionStorage.removeItem(DATE_KEY);
                        }
                    }
                }
                var range = DDS.date.getRange();
                if (range && !range.isDefault && range.start) {
                    return range.start;
                }
            } catch (e) {}
            return defaultDate || (window.moment ? window.moment().format('YYYY-MM-DD') : new Date().toISOString().split('T')[0]);
        },

        setDate: function (date) {
            if (!date) return;
            try {
                window.sessionStorage.setItem(DATE_KEY, JSON.stringify({
                    date: date,
                    ts: Date.now()
                }));
                var rawRange = window.sessionStorage.getItem(RANGE_KEY);
                var curRange = rawRange ? JSON.parse(rawRange) : null;
                if (!curRange || curRange.start === curRange.end) {
                    window.sessionStorage.setItem(RANGE_KEY, JSON.stringify({
                        start: date,
                        end: date,
                        ts: Date.now()
                    }));
                }
            } catch (e) {}
        },

        clear: function () {
            try {
                window.sessionStorage.removeItem(RANGE_KEY);
                window.sessionStorage.removeItem(DATE_KEY);
            } catch (e) {}
        }
    };

    // Auto-clear date storage on logout or session expiration (401/419)
    if (window.jQuery) {
        $(document).ajaxError(function (event, jqXHR) {
            if (jqXHR.status === 401 || jqXHR.status === 419) {
                DDS.date.clear();
            }
        });
        $(document).on('submit', 'form[action*="logout"]', function () {
            DDS.date.clear();
        });
    }

    /* ── Date range picker: one read/wire helper (retires copy-pasted onDrpApply) */
    DDS.getRange = function (id) {
        var drp = window.jQuery && window.jQuery('#' + id).data('daterangepicker');
        if (!drp) return null;
        return { start: drp.startDate.format('YYYY-MM-DD'), end: drp.endDate.format('YYYY-MM-DD') };
    };
    // Listen for a picker's apply (the x-daterange-picker dispatches 'daterange:changed').
    // Persists the range into sessionStorage, then invokes cb({start,end}). Pass id=null for any picker.
    DDS.onDateRange = function (id, cb) {
        document.addEventListener('daterange:changed', function (e) {
            if (id && e.detail.id !== id) return;
            DDS.date.setRange(e.detail.start, e.detail.end);
            cb({ start: e.detail.start, end: e.detail.end });
        });
    };

    /* ── Stacking modal system (canonical home for openLimitlessModal) ──────────
       Supports BOTH the existing server markup (`.ds-limitless-modal`, which ships its own
       close button) and new inline panels (`.dds-modal`). Stacking via a shared z counter;
       ESC closes the topmost of either kind. --------------------------------------------- */
    var MODAL_SEL = '.dds-modal, .ds-limitless-modal';
    DDS.modal = (function () {
        window._limitlessZIndex = window._limitlessZIndex || baseZ();
        function baseZ() {
            var v = getComputedStyle(document.documentElement).getPropertyValue('--dds-modal-base-z');
            return parseInt(v, 10) || 120;
        }
        function bumpZ(node) {
            window._limitlessZIndex += 10;
            node.style.zIndex = window._limitlessZIndex;
        }
        function topModal() {
            var all = document.querySelectorAll(MODAL_SEL);
            return all.length ? all[all.length - 1] : null;
        }
        function close(node) {
            node = node || topModal();
            if (!node) return;
            node.remove();
            window._limitlessZIndex = Math.max(baseZ(), window._limitlessZIndex - 10);
            if (!document.querySelector(MODAL_SEL)) document.body.style.overflow = '';
        }
        // Append already-built modal HTML (server-rendered .ds-limitless-modal or .dds-modal).
        function openHtml(html) {
            var wrap = document.createElement('div');
            wrap.innerHTML = String(html).trim();
            var node = wrap.firstElementChild;
            if (!node) return null;
            if (!node.classList.contains('ds-limitless-modal') && !node.classList.contains('dds-modal')) {
                // Bare content → wrap in a standard dds-modal panel with a close button.
                var shell = document.createElement('div');
                shell.className = 'dds-modal';
                shell.setAttribute('role', 'dialog');
                shell.setAttribute('aria-modal', 'true');
                shell.innerHTML = '<div class="dds-modal-panel"><button type="button" data-dds-close ' +
                    'class="self-end m-2 text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>' +
                    '<div class="overflow-y-auto px-6 pb-6">' + html + '</div></div>';
                node = shell;
            }
            // Backdrop click closes THIS modal (dds-modal only; ds-limitless ships its own).
            if (node.classList.contains('dds-modal')) {
                node.addEventListener('mousedown', function (e) { if (e.target === node) close(node); });
            }
            document.body.appendChild(node);
            bumpZ(node);
            document.body.style.overflow = 'hidden';
            DDS.swapHtml(node, node.innerHTML); // activate injected scripts/icons
            if (DDS.dataTableAll) DDS.dataTableAll(node); // sortable drilldown tables
            return node;
        }
        // Fetch a server-rendered modal fragment and stack it (the openLimitlessModal behavior).
        function open(url) {
            return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.text(); })
                .then(function (html) { return openHtml(html); })
                .catch(function (e) { if (window.console) console.error('Drilldown fetch failed:', e); });
        }
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-dds-close]');
            if (btn) { e.preventDefault(); close(btn.closest(MODAL_SEL)); }
        });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(topModal()); });
        return { open: open, openHtml: openHtml, close: close, closeTop: function () { close(topModal()); } };
    })();
    // Canonical global (retires the per-partial copies of openLimitlessModal).
    window.openLimitlessModal = DDS.modal.open;

    // ── Canonical DataTable initializer ───────────────────────────────────────
    // The ONE config for every sortable table (drill-downs included). Numeric columns sort by
    // their cell's data-order value (raw number), not the formatted display text.
    DDS.dataTable = function (el, opts) {
        if (!el || !window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) return null;
        var $el = jQuery(el);
        opts = opts || {};
        // Already initialised → reuse it, UNLESS the caller wants a rebuild (destroy:true,
        // e.g. a modal table re-opened with fresh data).
        if (jQuery.fn.DataTable.isDataTable($el) && !opts.destroy) return $el.DataTable();

        // Check if a reusable pagination container is attached via data-pagination-id
        var paginationId = opts.dataPaginationId || opts.paginationId || $el.data('paginationId') || $el.attr('data-pagination-id');
        if (!paginationId) {
            var $modal = $el.closest('.ds-limitless-modal, .dds-modal');
            if ($modal.length) {
                var $pager = $modal.find('[id$="-pagination-container"]');
                if ($pager.length) {
                    var containerId = $pager.attr('id') || '';
                    paginationId = containerId.replace(/-pagination-container$/, '');
                }
            }
        }
        if (!paginationId) {
            var $scroll = $el.closest('.dds-table-scroll, .overflow-x-auto, .overflow-y-auto');
            var $pager = $scroll.length ? $scroll.siblings('[id$="-pagination-container"]') : jQuery();
            if (!$pager.length) {
                $pager = $el.closest('.bg-white, .border, [class*="rounded"]').find('[id$="-pagination-container"]');
            }
            if ($pager.length) {
                var containerId = $pager.attr('id') || '';
                paginationId = containerId.replace(/-pagination-container$/, '');
            }
        }

        var defaultOpts = {
            paging: true,
            pageLength: opts.pageLength || 10,
            lengthChange: true,
            lengthMenu: [[10, 20, 25, 30, 40, 50, 60, 70, 80, 90, 100, 150, 200, 250, 500, 1000, -1], [10, 20, 25, 30, 40, 50, 60, 70, 80, 90, 100, 150, 200, 250, 500, 1000, 'All']],
            searching: true,
            ordering: true,          // every column sortable
            info: true,
            autoWidth: false,
            order: [],
            language: {
                search: '',
                searchPlaceholder: 'Search…',
                emptyTable: 'No records found.',
                lengthMenu: '_MENU_',
                paginate: {
                    first: '«',
                    previous: '‹',
                    next: '›',
                    last: '»'
                }
            }
        };

        if (paginationId) {
            var $itemsPerPage = jQuery('#' + paginationId + 'ItemsPerPage');
            if ($itemsPerPage.length && !opts.pageLength) {
                var parsedLen = parseInt($itemsPerPage.val(), 10);
                if (!isNaN(parsedLen) && parsedLen > 0) {
                    defaultOpts.pageLength = parsedLen;
                }
            }
            // When connected to <x-table-pagination>, suppress DataTables default duplicate pager controls
            defaultOpts.dom = 'rt';
            defaultOpts.layout = { topStart: null, topEnd: null, bottomStart: null, bottomEnd: null };
            defaultOpts.lengthChange = false;
            defaultOpts.info = false;
        }

        var dt = $el.DataTable(Object.assign(defaultOpts, opts));

        if (paginationId) {
            DDS.bindPagination(dt, paginationId, {
                onLoading: opts.onLoading
            });
        }

        return dt;
    };
    // Init every not-yet-initialised .dds-datatable within a root (called after a modal opens).
    DDS.dataTableAll = function (root) {
        (root || document).querySelectorAll('table.dds-datatable').forEach(function (t) { DDS.dataTable(t); });
    };

    // ── Helper to render reusable pagination markup dynamically in JS ─────────
    DDS.renderPaginationHtml = function (id, defaultLen, lengths) {
        defaultLen = defaultLen || 10;
        lengths = lengths || [10, 20, 25, 30, 40, 50, 60, 70, 80, 90, 100, 150, 200, 250, 500, 1000, -1];
        var lenOptions = lengths.map(function (len) {
            var sel = (String(len) === String(defaultLen)) ? ' selected' : '';
            var label = (len === -1) ? ' All ' : ' ' + len + ' ';
            return '<option value="' + len + '"' + sel + '>' + label + '</option>';
        }).join('');

        return '<div id="' + id + '-pagination-container" class="p-4 bg-white border-t border-slate-100 flex flex-wrap items-center justify-between text-xs text-slate-600 gap-3 rounded-b-lg">' +
            '<div class="flex items-center">' +
                '<div tabindex="-1" class="flex items-center px-0">' +
                    '<label for="' + id + 'ItemsPerPage" class="hidden md:mr-2 md:inline-block font-medium text-slate-600">Items per page</label>' +
                    '<select id="' + id + 'ItemsPerPage" class="p-1.5 px-2 rounded border border-slate-300 bg-white text-slate-700 focus:outline-none focus:border-emerald-500 text-xs cursor-pointer">' +
                        lenOptions +
                    '</select>' +
                '</div>' +
                '<div class="md:px-3 md:flex md:items-center text-slate-600">' +
                    '<span class="hidden md:inline md:mr-1" id="' + id + 'RangeInfo">0-0</span> of <span id="' + id + 'TotalCount" class="font-bold text-slate-800 ml-1 mr-1">0</span> items' +
                '</div>' +
            '</div>' +
            '<div class="flex items-center gap-2">' +
                '<div class="flex items-center px-2">' +
                    '<div class="mr-2">' +
                        '<select id="' + id + 'PageSelect" class="p-1.5 px-2 rounded border border-slate-300 bg-white text-slate-700 focus:outline-none focus:border-emerald-500 text-xs cursor-pointer">' +
                            '<option value="1" selected> 1 </option>' +
                        '</select>' +
                    '</div>' +
                    '<span class="text-slate-600">of <span id="' + id + 'TotalPages" class="font-medium text-slate-800">1</span> <span class="ml-1 hidden md:inline">pages</span></span>' +
                '</div>' +
                '<div class="flex items-center">' +
                    '<button id="' + id + 'PrevBtn" disabled type="button" class="py-1.5 px-3 rounded-l border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 focus:outline-none disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors" title="Previous Page">' +
                        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="flex-shrink-0 stroke-current"><path d="M15 3l-8 9 8 9" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>' +
                    '</button>' +
                    '<button id="' + id + 'NextBtn" disabled type="button" class="py-1.5 px-3 rounded-r border-t border-b border-r border-slate-300 bg-white text-slate-600 hover:bg-slate-50 focus:outline-none disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors" title="Next Page">' +
                        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="flex-shrink-0 stroke-current"><path d="M9 21l8-9-8-9" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>' +
                    '</button>' +
                '</div>' +
            '</div>' +
        '</div>';
    };

    // ── Reusable Custom Pagination Binder ──────────────────────────────────────
    // Connects <x-table-pagination :id="$prefix" /> controls to a DataTables instance.
    DDS.bindPagination = function (tableApi, idPrefix, opts) {
        if (!tableApi || !idPrefix || !window.jQuery) return null;
        var api = (tableApi && tableApi.page && typeof tableApi.page.info === 'function')
            ? tableApi
            : (window.jQuery && jQuery.fn && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable(tableApi) ? jQuery(tableApi).DataTable() : null);
        if (!api) return null;

        opts = opts || {};
        var onLoading = typeof opts.onLoading === 'function' ? opts.onLoading : null;

        var $itemsPerPage = jQuery('#' + idPrefix + 'ItemsPerPage');
        var $rangeInfo = jQuery('#' + idPrefix + 'RangeInfo');
        var $totalCount = jQuery('#' + idPrefix + 'TotalCount');
        var $pageSelect = jQuery('#' + idPrefix + 'PageSelect');
        var $totalPages = jQuery('#' + idPrefix + 'TotalPages');
        var $prevBtn = jQuery('#' + idPrefix + 'PrevBtn');
        var $nextBtn = jQuery('#' + idPrefix + 'NextBtn');
        var $selectAll = jQuery('#' + idPrefix + 'SelectAll');

        function update() {
            var info = api.page.info();
            var totalRecords = info.recordsDisplay;
            var totalPages = info.pages > 0 ? info.pages : 1;
            var currentPage = info.page + 1; // 1-indexed

            // 1. Items Range & Total Count
            if (totalRecords === 0) {
                $rangeInfo.text('0-0');
                $totalCount.text('0');
            } else {
                var start = info.start + 1;
                var end = info.end;
                $rangeInfo.text(start + '-' + end);
                $totalCount.text(Number(totalRecords).toLocaleString());
            }

            // 2. Items per page select sync
            $itemsPerPage.val(info.length);

            // 3. Page select dropdown options
            var existingPages = $pageSelect.data('total-pages');
            if (existingPages !== totalPages) {
                var optionsHtml = '';
                for (var p = 1; p <= totalPages; p++) {
                    optionsHtml += '<option value="' + p + '">' + p + '</option>';
                }
                $pageSelect.html(optionsHtml);
                $pageSelect.data('total-pages', totalPages);
            }
            $pageSelect.val(currentPage);
            $totalPages.text(totalPages.toLocaleString());

            // 4. Prev / Next buttons
            var isFirst = (info.page === 0);
            var isLast = (info.page >= totalPages - 1 || totalPages <= 1);
            $prevBtn.prop('disabled', isFirst);
            $nextBtn.prop('disabled', isLast);

            // 5. Reset select all if present
            if ($selectAll.length) {
                $selectAll.prop('checked', false);
            }
        }

        // Event Listeners
        $itemsPerPage.off('change.ddsPagination').on('change.ddsPagination', function () {
            var val = parseInt(jQuery(this).val(), 10);
            if (onLoading) onLoading();
            api.page.len(val).draw('page');
        });

        $pageSelect.off('change.ddsPagination').on('change.ddsPagination', function () {
            var targetPage = parseInt(jQuery(this).val(), 10) - 1;
            if (targetPage >= 0) {
                if (onLoading) onLoading();
                api.page(targetPage).draw('page');
            }
        });

        $prevBtn.off('click.ddsPagination').on('click.ddsPagination', function () {
            if (!jQuery(this).prop('disabled')) {
                if (onLoading) onLoading();
                api.page('previous').draw('page');
            }
        });

        $nextBtn.off('click.ddsPagination').on('click.ddsPagination', function () {
            if (!jQuery(this).prop('disabled')) {
                if (onLoading) onLoading();
                api.page('next').draw('page');
            }
        });

        // Listen for table draw events automatically
        api.off('draw.ddsPagination').on('draw.ddsPagination', function () {
            update();
        });

        // Initial sync
        update();

        return {
            update: update
        };
    };

    /* ── Sorting-only preset ───────────────────────────────────────────────────
       Same DataTable, ORDER behavior only: no pager, no search box, no info line.
       This is what server-rendered analytics tables need — every row is already on
       the page under a sticky header with sticky footer totals, so paging/search
       would fight the design, but the columns must still be click-sortable.

       Numeric columns sort on each cell's data-order (the raw number), never on
       the formatted text — "$ 1,200.00" and "$ (900.00)" are not sortable strings.

       Markup contract: add class "dds-sortable" to the <table>. Anything inside a
       <tfoot> is left where it is (DataTables never sorts footer rows), so Average/
       Total rows stay pinned.
    -------------------------------------------------------------------------- */
    DDS.sortable = function (el, opts) {
        opts = opts || {};
        var paginationId = opts.paginationId || opts.dataPaginationId ||
            (el && (el.getAttribute('data-pagination-id') || (el.dataset && el.dataset.paginationId)));
        if (!paginationId && el && window.jQuery) {
            var $c = jQuery(el).closest('.dds-table-scroll').siblings('[id$="-pagination-container"]');
            if (!$c.length) {
                $c = jQuery(el).closest('.bg-white, .border, [class*="rounded"]').find('[id$="-pagination-container"]');
            }
            if ($c.length) {
                paginationId = $c.attr('id').replace(/-pagination-container$/, '');
            }
        }
        var hasPagination = !!paginationId || (opts.paging === true);

        var dt = DDS.dataTable(el, Object.assign({
            paging: hasPagination,
            searching: hasPagination ? true : false,
            info: false,
            ordering: true,
            order: [],           // keep the server's row order until a header is clicked
            // Mark our own redraws so the tbody observer below can tell a DataTables
            // sort apart from the page repainting the table with new data.
            preDrawCallback: function () { el.__ddsDrawing = true; },
            drawCallback: function () { el.__ddsDrawing = false; }
        }, opts));
        DDS.sortableObserve(el);
        return dt;
    };
    // Init every not-yet-initialised .dds-sortable within a root. Safe to call repeatedly:
    // DDS.dataTable returns the existing instance rather than rebuilding.
    DDS.sortableAll = function (root) {
        (root || document).querySelectorAll('table.dds-sortable').forEach(function (t) {
            // A table rendered empty (loading/placeholder row) can't be initialised yet, but
            // the observer will pick it up the moment its rows arrive.
            if (hasSortableRows(t)) DDS.sortable(t); else DDS.sortableObserve(t);
        });
    };
    // A table is sortable once it holds a real data row — not a single colspan
    // "Loading…" / "No records" placeholder, which has no cell in column 2.
    function hasSortableRows(el) {
        var tr = el.querySelector('tbody tr:not(.animate-pulse)');
        return !!(tr && tr.querySelector('td:nth-child(2)'));
    }
    // Rebuild for tables whose rows are injected by page JS (tbody.innerHTML = …).
    // DataTables caches its rows, so new markup needs a rebuild, not a reuse.
    DDS.sortableRefresh = function (el) {
        if (!el) return null;
        if (el.__ddsDrawing) return null;
        if (window.jQuery && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable(jQuery(el))) {
            return jQuery(el).DataTable();
        }
        var dt = hasSortableRows(el) ? DDS.sortable(el) : null;
        return dt;
    };
    /* Watch a sortable table's <tbody> for un-initialized tables whose rows arrive asynchronously.
       Once DataTables is active, observer returns immediately to prevent re-initialization loops. */
    DDS.sortableObserve = function (el) {
        if (!el || el.__ddsObserved || !window.MutationObserver) return;
        var tbody = el.querySelector('tbody');
        if (!tbody) return;
        el.__ddsObserved = true;
        new MutationObserver(function () {
            if (el.__ddsDrawing) return;
            if (window.jQuery && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable(jQuery(el))) return;
            if (!hasSortableRows(el)) return;
            clearTimeout(el.__ddsReinit);
            el.__ddsReinit = setTimeout(function () {
                if (el.__ddsDrawing) return;
                if (window.jQuery && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable(jQuery(el))) return;
                DDS.sortableRefresh(el);
            }, 50);
        }).observe(tbody, { childList: true });
    };

    // Embedded-details drilldown from a rows[] array — the ONE implementation, replacing the
    // duplicated openOpsDrilldown / openMarketingDrilldown. Renders the shared, SORTABLE
    // DataTable (DDS.dataTable) with reusable pagination. Stackable; money/count auto-format + sort numerically.
    DDS.modal.details = function (title, rows) {
        rows = rows || [];
        var body, tableId = 'dds-dt-' + (DDS._dtSeq = (DDS._dtSeq || 0) + 1);
        var pagerHtml = '';
        if (!rows.length) {
            body = '<div class="py-8 text-center text-gray-400 text-sm">No records found.</div>';
        } else {
            var keys = Object.keys(rows[0]);
            var money = /production|amount|fee|total|collection|\$/i;
            var count = /visits|count|#|patients|procedures/i;
            var head = '<tr>' + keys.map(function (k) {
                var r = (money.test(k) || count.test(k)) ? ' text-right' : '';
                return '<th class="py-2.5 px-4 font-bold text-gray-900 capitalize' + r + '">' + k + '</th>';
            }).join('') + '</tr>';
            var rowsHtml = rows.map(function (item) {
                return '<tr>' + keys.map(function (k) {
                    var v = item[k];
                    if (money.test(k) && typeof v === 'number') return '<td data-order="' + v + '" class="py-3 px-4 text-right font-medium text-gray-900">' + DDS.fmt.money(v) + '</td>';
                    if (/note/i.test(k) && v) {
                        var safeNote = String(v).replace(/"/g, '&quot;');
                        return '<td class="py-3 px-4 text-gray-700 font-medium"><div class="group relative cursor-help max-w-[200px] inline-block align-middle" title="' + safeNote + '"><div class="truncate text-gray-700 font-normal max-w-[200px]">' + safeNote + '</div><div class="pointer-events-none absolute bottom-full left-1/2 mb-2 hidden -translate-x-1/2 group-hover:block z-[150] w-64 rounded-lg bg-gray-900 px-3 py-2 text-xs font-normal text-white shadow-xl whitespace-normal break-words">' + safeNote + '<div class="absolute top-full left-1/2 -mt-1 -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div></div></div></td>';
                    }
                    return '<td class="py-3 px-4 text-gray-700 font-semibold">' + (v == null || v === '' ? '—' : v) + '</td>';
                }).join('') + '</tr>';
            }).join('');
            body = '<table id="' + tableId + '" data-pagination-id="' + tableId + '" class="dds-table dds-datatable w-full text-left text-xs whitespace-nowrap">' +
                '<thead>' + head + '</thead><tbody>' + rowsHtml + '</tbody></table>';
            pagerHtml = '<div class="shrink-0 border-t border-slate-100 bg-white rounded-b-lg">' +
                DDS.renderPaginationHtml(tableId, 10) +
                '</div>';
        }
        var html = '<div class="dds-modal"><div class="dds-modal-panel flex flex-col max-h-[85vh]">' +
            '<div class="flex justify-between items-center p-5 border-b border-gray-100 bg-gray-50/50 rounded-t-lg shrink-0">' +
            '<h4 class="text-sm font-bold text-gray-900">Breakdown | ' + (title || 'Details') + '</h4>' +
            '<div class="flex items-center gap-3">' +
            (rows.length ? '<button type="button" onclick="exportDrilldownModalCsv(this)" class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded border border-emerald-500 text-emerald-600 hover:bg-emerald-50 focus:outline-none transition-colors cursor-pointer shadow-xs"><svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>Export CSV</button>' : '') +
            '<button type="button" data-dds-close class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button></div></div>' +
            '<div class="flex-1 overflow-y-auto p-6">' + body + '</div>' +
            pagerHtml +
            '</div></div>';
        var modal = DDS.modal.openHtml(html);
        return modal;
    };

    // Export CSV helper for drilldown modals (both server-rendered and DDS.modal.details)
    function exportDrilldownModalCsv(btn) {
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
    }
    window.exportDrilldownModalCsv = exportDrilldownModalCsv;
    DDS.modal.exportCsv = exportDrilldownModalCsv;

    // Back-compat aliases so existing markup keeps working while callers migrate.
    window.openOpsDrilldown = function (t, d) { return DDS.modal.details(t, d); };
    window.openMarketingDrilldown = function (t, d) { return DDS.modal.details(t, d); };

    /* ── URL-driven tabs (generalizes the Operations loadTab pattern) ───────────
       Markup contract:
         <nav data-dds-tabs data-content="#panel">
           <a data-dds-tab="offices" data-url="/operations/data/offices" href="/operations/offices">Offices</a>
           ...
         </nav>
         <div id="panel" data-dds-tab-content>…server-rendered active tab…</div>
       - click: fetch data-url, swap into content, pushState href (no reload)
       - popstate: re-swap for the URL
       - deep-link: server renders the right tab; JS just marks it active
    ------------------------------------------------------------------------------ */
    DDS.tabs = {
        init: function (nav) {
            nav = typeof nav === 'string' ? document.querySelector(nav) : nav;
            if (!nav || nav.__ddsTabsInit) return;
            nav.__ddsTabsInit = true;
            var content = document.querySelector(nav.getAttribute('data-content') || '[data-dds-tab-content]');

            function activate(tabKey) {
                nav.querySelectorAll('[data-dds-tab]').forEach(function (t) {
                    t.setAttribute('aria-selected', t.getAttribute('data-dds-tab') === tabKey ? 'true' : 'false');
                    t.classList.add('dds-tab');
                    t.setAttribute('role', 'tab');
                });
            }
            function load(tab, push) {
                if (!content || !tab.getAttribute('data-url')) return;
                content.setAttribute('aria-busy', 'true');
                fetch(tab.getAttribute('data-url'), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.text(); })
                    .then(function (html) {
                        DDS.swapHtml(content, html);
                        content.removeAttribute('aria-busy');
                        activate(tab.getAttribute('data-dds-tab'));
                        if (push && tab.getAttribute('href')) history.pushState({ ddsTab: tab.getAttribute('data-dds-tab') }, '', tab.getAttribute('href'));
                    });
            }
            nav.addEventListener('click', function (e) {
                var tab = e.target.closest('[data-dds-tab]');
                if (!tab) return;
                e.preventDefault();
                load(tab, true);
            });
            window.addEventListener('popstate', function (e) {
                var key = (e.state && e.state.ddsTab);
                var tab = key ? nav.querySelector('[data-dds-tab="' + key + '"]') : null;
                if (tab) load(tab, false);
            });
            // Mark the server-rendered active tab (from a data-active attr or first tab).
            var initKey = nav.getAttribute('data-active');
            activate(initKey || (nav.querySelector('[data-dds-tab]') || {}).getAttribute && nav.querySelector('[data-dds-tab]').getAttribute('data-dds-tab'));
        }
    };

    /* Deep-link helper for pages that PRE-RENDER their tab panels and show/hide them
       (e.g. Aging/Financials with lazy DataTables). Keeps the page's own show/hide logic;
       adds URL sync (?<param>=mode), deep-linking on load, and back/forward support.
         var t = DDS.tabs.deeplink('tab', function (mode) { ...show/hide + lazy init... });
         // on a tab click:  t.go(mode)
         // on page load:     activate(t.initial || 'defaultMode')
    */
    DDS.tabs.deeplink = function (param, activate) {
        window.addEventListener('popstate', function (e) {
            var m = (e.state && e.state['ddsTab_' + param]) || DDS.url.get(param);
            if (m) activate(m, false);
        });
        return {
            initial: DDS.url.get(param),
            go: function (mode) {
                var st = history.state || {};
                st['ddsTab_' + param] = mode;
                var patch = {}; patch[param] = mode;
                history.pushState(st, '', DDS.url.merge(patch));
                activate(mode, true);
            }
        };
    };

    /* ── Location multi-select (canonical behavior for <x-location-picker>) ─────────
       Checkbox changes are a draft; nothing is emitted until Apply. Closing the menu without
       applying restores the last applied selection. At least one location must stay selected.
         DDS.getLocations('opsLocations')            -> ['1', '3', '8:2']
         DDS.onLocations('opsLocations', function (keys) { ...reload... });
    */
    DDS.locationPicker = (function () {
        var pickers = {};

        function init(root) {
            if (root.__ddsLocationPicker) return root.__ddsLocationPicker;
            var id = root.getAttribute('data-dds-location-picker');
            var menu = root.querySelector('[data-lp-menu]');
            var toggle = root.querySelector('[data-lp-toggle]');
            var search = root.querySelector('[data-lp-search]');
            var applyBtn = root.querySelector('[data-lp-apply]');
            var label = root.querySelector('[data-lp-label]');
            var boxes = Array.prototype.slice.call(root.querySelectorAll('[data-lp-option]'));
            var applied = boxes.filter(function (b) { return b.checked; }).map(function (b) { return b.value; });

            function checked() { return boxes.filter(function (b) { return b.checked; }); }
            function setChecked(keys) { boxes.forEach(function (b) { b.checked = keys.indexOf(b.value) !== -1; }); }

            function render() {
                var n = checked().length;
                applyBtn.disabled = n === 0;
                applyBtn.classList.toggle('opacity-50', n === 0);
                applyBtn.classList.toggle('cursor-not-allowed', n === 0);

                var names = boxes.filter(function (b) { return applied.indexOf(b.value) !== -1; })
                    .map(function (b) { return b.getAttribute('data-label'); });
                label.textContent = applied.length === boxes.length ? 'All Locations'
                    : (applied.length === 1 ? names[0] : applied.length + ' Locations');
                label.title = names.join(', ');
            }

            function open() {
                root.classList.add('is-open');
                menu.classList.remove('hidden');
                toggle.setAttribute('aria-expanded', 'true');
                if (search) { search.value = ''; filter(''); search.focus(); }
            }
            function close() {
                if (menu.classList.contains('hidden')) return;
                root.classList.remove('is-open');
                menu.classList.add('hidden');
                toggle.setAttribute('aria-expanded', 'false');
                setChecked(applied); // discard an unapplied draft
                render();
            }
            function filter(q) {
                q = q.toLowerCase().trim();
                boxes.forEach(function (b) {
                    b.closest('label').style.display = b.getAttribute('data-label').toLowerCase().indexOf(q) !== -1 ? '' : 'none';
                });
            }

            toggle.addEventListener('click', function () {
                if (menu.classList.contains('hidden')) open(); else close();
            });
            document.addEventListener('mousedown', function (e) { if (!root.contains(e.target)) close(); });
            root.addEventListener('keydown', function (e) { if (e.key === 'Escape') { close(); toggle.focus(); } });
            if (search) search.addEventListener('input', function () { filter(search.value); });
            boxes.forEach(function (b) { b.addEventListener('change', render); });
            root.querySelector('[data-lp-all]').addEventListener('click', function () { boxes.forEach(function (b) { b.checked = true; }); render(); });
            root.querySelector('[data-lp-none]').addEventListener('click', function () { boxes.forEach(function (b) { b.checked = false; }); render(); });
            function syncToServer(keys) {
                try {
                    localStorage.setItem('dds_selected_locations', JSON.stringify(keys));
                    var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        || document.querySelector('input[name="_token"]')?.value
                        || (window.jQuery && jQuery('meta[name="csrf-token"]').attr('content'));
                    var base = (window.APP_URL || '').replace(/\/+$/, '');
                    var url = (base ? base : '') + '/locations/select';
                    if (window.fetch) {
                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': token || ''
                            },
                            body: JSON.stringify({ locations: keys.join(',') })
                        }).catch(function () {});
                    } else if (window.jQuery) {
                        jQuery.ajax({
                            url: url,
                            type: 'POST',
                            headers: { 'X-CSRF-TOKEN': token || '' },
                            contentType: 'application/json',
                            data: JSON.stringify({ locations: keys.join(',') })
                        });
                    }
                } catch (e) {}
            }

            applyBtn.addEventListener('click', function () {
                if (!checked().length) return;
                applied = checked().map(function (b) { return b.value; });
                root.classList.remove('is-open');
                menu.classList.add('hidden');
                toggle.setAttribute('aria-expanded', 'false');
                render();
                syncToServer(applied);
                document.dispatchEvent(new CustomEvent('locations:changed', { detail: { id: id, keys: applied.slice() } }));
            });

            render();
            var api = {
                keys: function () { return applied.slice(); },
                set: function (keys) {
                    setChecked(keys);
                    applied = checked().map(function (b) { return b.value; });
                    render();
                }
            };
            root.__ddsLocationPicker = pickers[id] = api;
            return api;
        }

        return {
            init: init,
            initAll: function (scope) {
                (scope || document).querySelectorAll('[data-dds-location-picker]').forEach(init);
            },
            // Lazily initialises, so inline page scripts can read a picker before DOMContentLoaded.
            get: function (id) {
                if (pickers[id]) return pickers[id];
                var root = document.querySelector('[data-dds-location-picker="' + id + '"]');
                return root ? init(root) : null;
            }
        };
    })();
    DDS.getLocations = function (id) {
        var p = DDS.locationPicker.get(id);
        return p ? p.keys() : [];
    };
    DDS.onLocations = function (id, cb) {
        document.addEventListener('locations:changed', function (e) {
            if (id && e.detail.id !== id) return;
            cb(e.detail.keys);
        });
    };

    // Auto-init any declarative tab bars, location pickers, and every sortable table, on load.
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-dds-tabs]').forEach(function (n) { DDS.tabs.init(n); });
        DDS.locationPicker.initAll(document);
        DDS.sortableAll(document);
    });

    window.DDS = DDS;
})(window, document);
