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

    /* ── Date range picker: one read/wire helper (retires copy-pasted onDrpApply) */
    DDS.getRange = function (id) {
        var drp = window.jQuery && window.jQuery('#' + id).data('daterangepicker');
        if (!drp) return null;
        return { start: drp.startDate.format('YYYY-MM-DD'), end: drp.endDate.format('YYYY-MM-DD') };
    };
    // Listen for a picker's apply (the x-daterange-picker dispatches 'daterange:changed').
    // Syncs the range into the URL, then invokes cb({start,end}). Pass id=null for any picker.
    DDS.onDateRange = function (id, cb) {
        document.addEventListener('daterange:changed', function (e) {
            if (id && e.detail.id !== id) return;
            history.replaceState(history.state, '', DDS.url.merge({ start_date: e.detail.start, end_date: e.detail.end }));
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
        return $el.DataTable(Object.assign({
            paging: true,
            pageLength: 10,
            lengthChange: true,
            lengthMenu: [10, 20, 50, 100],
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
        }, opts || {}));
    };
    // Init every not-yet-initialised .dds-datatable within a root (called after a modal opens).
    DDS.dataTableAll = function (root) {
        (root || document).querySelectorAll('table.dds-datatable').forEach(function (t) { DDS.dataTable(t); });
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
        var dt = DDS.dataTable(el, Object.assign({
            paging: false,
            searching: false,
            info: false,
            ordering: true,
            order: [],           // keep the server's row order until a header is clicked
            // Mark our own redraws so the tbody observer below can tell a DataTables
            // sort apart from the page repainting the table with new data.
            preDrawCallback: function () { el.__ddsDrawing = true; },
            drawCallback: function () { el.__ddsDrawing = false; }
        }, opts || {}));
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
    // DataTable (DDS.dataTable). Stackable; money/count auto-format + sort numerically.
    DDS.modal.details = function (title, rows) {
        rows = rows || [];
        var body, tableId = 'dds-dt-' + (DDS._dtSeq = (DDS._dtSeq || 0) + 1);
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
            body = '<table id="' + tableId + '" class="dds-table dds-datatable w-full text-left text-xs whitespace-nowrap">' +
                '<thead>' + head + '</thead><tbody>' + rowsHtml + '</tbody></table>';
        }
        var html = '<div class="dds-modal"><div class="dds-modal-panel">' +
            '<div class="flex justify-between items-center p-5 border-b border-gray-100 bg-gray-50/50">' +
            '<h4 class="text-sm font-bold text-gray-900">Breakdown | ' + (title || 'Details') + '</h4>' +
            '<button type="button" data-dds-close class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button></div>' +
            '<div class="flex-1 overflow-y-auto p-6">' + body + '</div></div></div>';
        var modal = DDS.modal.openHtml(html);
        if (rows.length) DDS.dataTable(document.getElementById(tableId)); // sortable columns
        return modal;
    };
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

    // Auto-init any declarative tab bars, and every sortable table, on load.
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-dds-tabs]').forEach(function (n) { DDS.tabs.init(n); });
        DDS.sortableAll(document);
    });

    window.DDS = DDS;
})(window, document);
