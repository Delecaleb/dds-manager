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
    // Wire a picker's "apply" to a callback receiving {start,end}. Also syncs the URL.
    DDS.onDateRange = function (id, cb) {
        window['__dds_drp_' + id] = function (start, end) {
            history.replaceState(history.state, '', DDS.url.merge({ start_date: start, end_date: end }));
            cb({ start: start, end: end });
        };
        // The x-daterange-picker calls window[onApply](start,end); point onApply at us.
        return '__dds_drp_' + id;
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
                node.addEventListener('mousedown', function (e) { if (e.target === node) close(node); });
            }
            document.body.appendChild(node);
            bumpZ(node);
            document.body.style.overflow = 'hidden';
            DDS.swapHtml(node, node.innerHTML); // activate injected scripts/icons
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

    // Auto-init any declarative tab bars on load.
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-dds-tabs]').forEach(function (n) { DDS.tabs.init(n); });
    });

    window.DDS = DDS;
})(window, document);
