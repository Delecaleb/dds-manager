# 08 — Frontend Architecture (consolidation)

Single-source front-end layer, mirroring the backend domain services. Built on the existing
stack (no new build step) per the decisions below.

## Decisions (locked with product owner)
- **Canonical look** = the Operations analytics table (teal `#00bfa5`, heatmap, sticky header/cols).
- **Tabs** = AJAX-swap on demand + `history.pushState` (URL changes, no reload) + server-rendered
  deep-link fallback. Generalizes the pattern Operations already uses.
- **Build pipeline** = stay on Play-CDN Tailwind + jQuery (no `npm run build` on deploy). Shared
  CSS/JS are static files in `public/`, loaded once in the layout.

## Single sources of truth
| Concern | One place |
|---|---|
| Component CSS (tokens, sticky header/cols, heatmap, modal, tabs, skeleton) | `public/css/ui.css` |
| Shared JS behavior | `public/js/ui.js` → `window.DDS` |
| Server-rendered analytics table | `<x-data-table :spec>` (from `operations/tabs/table.blade.php`) |
| Interactive tables | DataTables + the same `ui.css` sticky classes |
| Stacking drill-down modals | `DDS.modal` (`open`/`openHtml`/`closeTop`) |
| URL-driven tabs | `DDS.tabs` + `[data-dds-tabs]` markup |
| Date range picker | `<x-daterange-picker>` + `DDS.getRange`/`DDS.onDateRange` |

## `window.DDS` API (ui.js)
- `DDS.fmt.money|percent|number(v)` — mirror the PHP `ops_fmt`; retires the ~4 copied `fmtMoney`.
- `DDS.swapHtml(sel, html)` — inject + re-execute scripts + refresh Lucide icons.
- `DDS.url.get(key)` / `DDS.url.merge({k:v})` — URL-as-state helpers.
- `DDS.getRange(id)` → `{start,end}` (YYYY-MM-DD) from a daterangepicker.
- `DDS.onDateRange(id, cb)` — returns the global name to pass as the picker's `on-apply`; also
  syncs the range into the URL.
- `DDS.modal.open(url)` / `.openHtml(html)` / `.closeTop()` — **stacking** (z-index counter above
  `--dds-modal-base-z`), ESC/backdrop closes the topmost only. `window.openLimitlessModal` aliased.
- `DDS.tabs.init(nav)` — auto-inits `[data-dds-tabs]`; click→fetch `data-url`→swap→pushState `href`;
  `popstate` re-swaps; server renders the deep-linked tab and JS marks it active.

## The three table types (one component)
`<x-data-table :spec="$spec">` where `$spec`:
- **type 1 (basic)** — `columns[] + rows[]`
- **type 2 (footer)** — `+ average[]` / `total[]` (and optional `header_groups[]`)
- **type 3 (grouped head)** — `+ groups[] ([{label,span}])`; plain head = `groups: []`
Column: `{key,label,type: text|money|percent|number|html|yn_badge, sticky?, agg?, heat?: false|'invert', drilldown?, drilldown_type?, class?}`.
Sticky columns use `.dds-stick` / `.dds-stick-2` (**real** CSS — the old `tb:sm:stick-*` classes were
undefined no-ops and are being replaced).

## Migration order
1. ✅ Foundation — `ui.css`, `ui.js`, layout wiring.
2. `<x-data-table>` canonical component (+ fix sticky columns); repoint `operations/tabs/table` and
   the drilldown table-content at it.
3. `DDS.modal` — retire `openOpsDrilldown` / `openMarketingDrilldown`; fix duplicate `#providerModal`.
4. `DDS.tabs` — convert Aging/Financials/etc. show-hide tabs to URL-driven; keep Operations behavior.
5. Date picker — repoint consumers at `DDS.onDateRange`/`getRange`; drop the ~8 glue copies.
6. Per-page cleanup: replace inline formatters with `DDS.fmt`.

Each step ships independently and is validated (page renders, no console errors).
