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
1. ✅ **Foundation** — `ui.css`, `ui.js` (in `<head>`), layout wiring.
2. ✅ **`<x-analytics-table>`** canonical component (+ real sticky columns); Operations table
   is now a thin wrapper around it; migrated stray `tb:sm:stick-*` usages.
3. ✅ **`DDS.modal`** — canonical stacking opener (handles `.ds-limitless-modal` + `.dds-modal`);
   removed the duplicate `openLimitlessModal`. *(Still TODO: retire `openMarketingDrilldown`
   and the embedded `openOpsDrilldown`; fix the duplicate `#providerModal` id.)*
4. ✅ **`DDS.tabs`** — URL-driven + deep-linkable. Operations (AJAX); `DDS.tabs.deeplink`
   added for pre-rendered/show-hide pages → applied to **Aging, KPIs, Tx Miner** (`?tab=`
   sync + deep-link + back/forward). Dashboard/patient-modal tabs are modal-internal (left
   page-URL-agnostic by design). Calendar's detail/capacity are panels, not tabs.
5. ✅ **Date picker** — URL-persisted range + `daterange:changed` event; `DDS.onDateRange`
   retires the glue. Consumers migrate opportunistically (back-compat kept).
6. ✅ **Formatters** — the 4 `fmtMoney` copies now delegate to `DDS.fmt.money`.
7. ✅ **Modals** — `DDS.modal.details` (one stackable embedded-details modal) retired both
   `openOpsDrilldown` and `openMarketingDrilldown`.

### Remaining (minor)
- Duplicate `#providerModal` id (dashboard + patient-modal component) — latent id collision.
- Horizontal-scroll fix shipped (`.dds-table-scroll` pinned to parent width).

**Runtime verification owed** for the per-page tab conversions and modal stacking (browser).
