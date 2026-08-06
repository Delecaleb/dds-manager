# 05 — Frontend Consolidation

The backend gets a single source of truth for *numbers*; the frontend needs the same for
*components* — especially the pop-ups. Evidence is in
[02-duplication-audit.md](02-duplication-audit.md) section B.

## F1. One drill-down modal system (retire the other two)

**Today: three systems doing the identical job.**

| System | Where | Verdict |
|--------|-------|---------|
| `openLimitlessModal()` + `drilldown/table-modal` + `table-content` | `operations/tabs/table.blade.php:357` + components | **KEEP** — server-rendered, stackable, already a component |
| `openOpsDrilldown()` + `#ops_drilldown_modal` | `operations/tabs/table.blade.php:376, 331` | **RETIRE** — legacy, "backward compatibility" |
| `openMarketingDrilldown()` + `#marketing_drilldown_modal` | `operations/tabs/marketing.blade.php:549, 404` | **RETIRE** — copy of the above |

**Target:** a single Blade component + one JS entry point.
```
components/app-components/drilldown/
  table-modal.blade.php      (shell — already exists)
  table-content.blade.php    (server-rendered rows — already exists)
```
```js
// one function, used by every tab and every page:
openDrilldown(url)   // fetch server HTML, insert, manage stacking z-index
```
Callers pass a URL to a controller drill-down endpoint that renders `table-content`.
Delete the inline `#ops_drilldown_modal` / `#marketing_drilldown_modal` markup and their
`open*Drilldown()` / `close*Drilldown()` JS.

**Migration note:** the two legacy systems take a pre-built `details` array, the kept one
takes a URL. Each retired caller needs a small server endpoint (or reuse an existing one)
returning `table-content`. Low risk, but it is per-caller work — not a global find/replace.

## F2. Resolve the duplicate `#providerModal` (actual bug)

Same DOM id defined twice:
- `dashboard.blade.php:1206` (tabbed, with charts)
- `components/app-components/patient-modal.blade.php:810` (simpler, jQuery)

**Target:** one `<x-app-components.provider-modal>` component, included where needed,
with a single `openProviderModal(provNum)`. Pick the richer dashboard version as the base,
parameterize what the simpler one needs. Removes the id collision that breaks any page
loading both.

## F3. One breakdown modal in financials
`financials/index.blade.php` has both a hand-rolled `#bkOverlay` (`:706`) and the shared
`<x-app-components.datatable-modal>` (`:1902`). **Target:** delete `#bkOverlay` and its JS
(`bkNetProduction` frontend glue), route those breakdowns through the shared datatable
modal.

## F4. One JS formatting util (kill the 4 `fmtMoney` copies)
Create `resources/js/analytics-format.js` (or a shared Blade partial if not using a JS
build step):
```js
export const fmtMoney = (v) => { /* one implementation: null → '—', spaced $, (neg) */ };
export const fmtPct   = (v) => ...;
export const fmtNum   = (v) => ...;
export const fmtDec1  = (v) => ...;
```
Replace: `dashboard:499`, `financials:773`, `provider-portal:187`, `deposit:142`,
`kpis:472 fmtKpi`, `provider-portal:194 fmtDec1`. **This fixes visible inconsistency**
(the copies currently format the same value differently).

## F5. Align server-side formatting
`drilldown/table-content.blade.php:25-29, 78-82` re-implements money/percent formatting
that `ops_fmt()` (`operations/tabs/table.blade.php:14`) already does. **Target:** extract
`ops_fmt` / `ops_heat_class` into a shared helper (`app/Support/ViewFormat.php` or a
Blade `@php` include) and call it from both places, so terminal and drill-down cells match.

## F6. One donut config
`createDonutConfig` (`financials/index.blade.php:1153`) is the good version; 8+ hand-written
donut configs exist elsewhere. **Target:** move it into the shared JS util (F4) and have
`schedule`, `collections`, `services`, `marketing` partials call it.

## F7. De-duplicate the tasks widget
`front-office/tasks.blade.php` duplicates `front-office/partials/tasks.blade.php`.
**Target:** the standalone page `@include`s the partial; delete the copy.

---

## Frontend priority order
1. **F1** (drill-down consolidation) — highest value, directly the "reusable pop-up" goal.
2. **F2** (`#providerModal`) — it's a live bug, cheap to fix.
3. **F4** (`fmtMoney`) — quick, removes visible inconsistency.
4. F3, F5, F6, F7 — cleanup, lower risk, do as capacity allows.

> Frontend changes carry UI-regression risk, so they follow the backend pilot in the
> sequencing (see [06-migration-plan.md](06-migration-plan.md)) — after the pattern and
> the validation habit are proven.
