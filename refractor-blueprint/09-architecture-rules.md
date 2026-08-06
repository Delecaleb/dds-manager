# 09 — Architecture Rules (MUST FOLLOW)

**Audience:** every engineer or AI agent changing this codebase.
**Purpose:** the refactor gave this app a *single source of truth* for every metric, every
table, every modal, every tab, and every format. If new code bypasses these, the duplication
comes straight back and the restructure is defeated. These rules keep it intact.

> **The one rule that implies all the others:** if a thing already has a home, use its home.
> Don't re-implement a metric, a table, a modal, a tab, a formatter, a status code, or an
> office name inline. Add to / call the single source instead.

---

## 1. Backend — metrics & data

### 1.1 Never write a metric formula or business SQL in a controller/view
Every named number (production, collections, visits, new patients, case acceptance, AR, …)
has ONE definition in a **domain service** under `app/Domain/`. Call it; don't recompute it.

| Domain | Service | Owns |
|---|---|---|
| Production | `App\Domain\Production\ProductionService` | gross, adjustments, writeoffs, **net (D3)**, collection, ratios, visits, days, `summary()` |
| Patients | `App\Domain\Patient\PatientService` | counts, **new/existing (D8)**, `firstVisitCohort()` (+ `firstVisitCohortSql()` for heredocs) |
| Case acceptance | `App\Domain\TreatmentAcceptance\TreatmentAcceptanceService` | proposed/completed/accepted, **rate (D4)**, `rateFrom()` |
| Insurance | `App\Domain\Insurance\PayorService` | patient→plan map (D10), `payorLabel()`, per-payor production |
| Scheduling | `App\Domain\Scheduling\SchedulingService` | appts, broken/reappointment rates, scheduled production |
| Financial | `App\Domain\Financial\FinancialService` | collections, adjustments breakdown, AR aging (wraps `AgingCalculationService`) |
| Recall | `App\Domain\Recall\RecallService` | due / overdue / scheduled / by type |
| Provider | `App\Domain\Provider\ProviderService` | per-provider `scorecard()` — **composes the above; owns no formulas** |

**Do**
```php
public function index(Request $r, ProductionService $production)
{
    $filter = MetricFilter::fromRequest($r);   // period + clinics[] + providers[]
    return view('dashboard', ['production' => $production->summary($filter)]);
}
```
**Don't**
```php
$net = DB::table('od_procedure_logs')->whereIn('ProcStatus', ['C','2'])->sum('ProcFee') - ...; // ❌ inline formula
```

### 1.2 Filters go through `MetricFilter` — never positional args
`App\Domain\Support\MetricFilter` carries `start, end, clinics[], providers[], hygiene`.
Build it with `MetricFilter::fromRequest($r)` or `new MetricFilter(...)`; derive with
`->withClinics()`, `->withProviders()`, `->lastYear()`. **Adding a new filter = a new property
on MetricFilter**, not a new argument on every method. Never add `($start, $end, $clinics, $x)`
positional signatures.

### 1.3 Status codes come from `ProcStatus` — never literal `'C' / 'TP' / 1 / 2`
`App\Domain\Support\ProcStatus` is the ONE definition (`COMPLETED = ['C','2']`,
`TREATMENT_PLANNED = ['TP','1']`).
- Query builder: `->whereIn('pl.ProcStatus', ProcStatus::completed())`
- Raw SQL heredoc: interpolate a pre-rendered list — `$c = ProcStatus::inList(ProcStatus::completed()); "... ProcStatus IN ({$c}) ..."` (or a `$this->completedIn` property for a class full of heredocs).
- **Never** `ProcStatus = 'C'`, `= 'TP'`, `= 1`, `= 2` (the integer forms are a known bug — the data is letter-encoded).

### 1.4 Office/clinic identity comes from `ClinicRegistry` — never `'8 Mile'`
`App\Domain\Support\ClinicRegistry` maps `ClinicNum → name` (DB-backed once `od_clinics`
syncs; multi-office ready). Use `$clinics->name($clinicNum)` / `->all()` / `->ids()`.
**Never hardcode an office name.** Every metric already filters by `MetricFilter->clinics`,
so a second office needs no query changes.

### 1.5 SQL fragments (grouped/raw queries) reuse the shared definitions
Grouped queries that must stay one-scan (e.g. KpisController bundles) may keep raw SQL for
performance, but the *definitions* inside them are still single-sourced: `ProcStatus::*`,
`PatientService::firstVisitCohortSql()`, `MetricDefinitions::grossProduction()`.

### 1.6 Changing a definition = one edit + a parity check
Change the method in the service. Then snapshot before/after on a fixed date range
(`php artisan blueprint:parity` / `blueprint:production`, or a throwaway tinker over the
"golden set" in [06-migration-plan.md](06-migration-plan.md)) and classify every changed
number as *expected* (cite the D# decision) or *regression* (fix before shipping).

---

## 2. Frontend — components & behavior

**Stack reality:** Play-CDN Tailwind + jQuery + DataTables 2 (no Vite build, no Alpine).
Shared CSS = `public/css/ui.css`; shared JS = `public/js/ui.js` (`window.DDS`). Both are loaded
in the layout `<head>`. Assets are referenced with the **`public/` prefix**:
`{{ asset('public/css/ui.css') }}` (this deployment serves the app with `public/` in the URL).

### 2.1 There are exactly TWO table types — don't invent a third
1. **`<x-analytics-table :spec="…">`** — static, server-pre-aggregated (dashboards/Operations).
   The spec (`groups`, `header_groups`, `columns`, `rows`, `average`, `total`, `is_compare`)
   covers all head/footer variants: no-footer, footer, and grouped/sectioned head.
2. **`<x-data-table>` markup + `DDS.dataTable(el, opts)` init** — interactive
   (sortable/searchable/paginated). Used for lists **and drill-downs** (a drill-down is this
   type inside a modal — *not* a new type).

**Never** write a bare `<table>` for data, and **never** call `$('#x').DataTable({…})`
directly. Route every DataTable through `DDS.dataTable`:
```js
DDS.dataTable(document.getElementById('myTable'), { ajax: {...}, columns: [...] }); // ✅
$('#myTable').DataTable({ pageLength: 10, ordering: true, ... });                    // ❌ re-fragments config
```
Shared defaults (page size, search, sortable, language) live in `DDS.dataTable`; pass only
your `ajax`/`columns`/`order` and deliberate overrides. Numeric columns must carry
`data-order="<raw>"` so they sort by value, not formatted text.

### 2.2 Table styling lives ONLY in `ui.css`
Every data table carries the **`.dds-table`** class and draws its look (sticky header
`.dds-head-sticky`, sticky columns `.dds-stick` / `.dt-col-sticky`, heatmap `.dds-heat-*`,
tokens in `:root`) from `ui.css`. To change how tables look, edit `ui.css` — **once**.
**Never** add per-page table CSS or invent new sticky classes (the old `tb:sm:stick-*` were
undefined no-ops — don't resurrect that pattern).

### 2.3 Drill-downs & modals — one stackable system (`DDS.modal`)
- Embedded rows → `DDS.modal.details(title, rows)` (renders the shared sortable DataTable).
- Server-rendered fragment → `DDS.modal.open(url)` (fetch + stack); render the fragment with
  `drilldown/table-content.blade.php` and mark its table `.dds-datatable`.
- Modals **stack** (each sits above the last; Esc/✕ closes the top).
**Never** create a new modal element + open/close function, and never reuse a fixed DOM id for
a modal that can appear on a page alongside another (that caused the `#providerModal` clash).

### 2.4 Tabs — URL-driven & deep-linkable (`DDS.tabs`)
Page-level tabs must reflect in the URL, survive reload/share, and support back/forward — no
full page reload.
- AJAX-panel pages: the Operations `loadTab` pattern (`?tab`/route + `pushState`).
- Pre-rendered show/hide pages: `DDS.tabs.deeplink('tab', activateFn)` — see Aging / KPIs /
  Tx-Miner / Financials.
Modal-internal tabs (patient/provider modal) stay page-URL-agnostic by design.
**Never** ship a tab that only toggles visibility with no URL state.

### 2.5 Date range — one component (`<x-daterange-picker>`)
Use the component; read changes via the event, not by re-reading the widget:
```js
DDS.onDateRange('opsDateRange', function (r) { reload(r.start, r.end); }); // ✅ syncs URL too
```
The range persists in the URL (`?start_date&end_date`). **Never** add another date library or
hand-roll `$('#x').data('daterangepicker')` glue.

### 2.6 Formatting — `DDS.fmt` (JS) / `ops_fmt` (PHP)
Money/percent/number formatting has one home each: `DDS.fmt.money/percent/number` in JS,
`ops_fmt($value,$type)` in the analytics table partial for server-rendered cells.
**Never** write another `fmtMoney` / `toLocaleString(...)` inline.

---

## 3. "Where does X live?" — quick reference

| Want to change / add… | Edit exactly this |
|---|---|
| A metric formula | its method in the `app/Domain/…Service` |
| A new filter dimension | a property on `App\Domain\Support\MetricFilter` |
| Status-code meaning | `App\Domain\Support\ProcStatus` |
| Office name / add an office | `ClinicRegistry` (or sync `od_clinics`) |
| Table look (colors, sticky, rows) | `public/css/ui.css` |
| Table behavior (paging, sort, search) | `DDS.dataTable` in `public/js/ui.js` |
| Money/number format | `DDS.fmt` (JS) / `ops_fmt` (PHP) |
| Drill-down modal behavior | `DDS.modal` in `public/js/ui.js` |
| Tab behavior | `DDS.tabs` in `public/js/ui.js` |
| Date picker | `resources/views/components/daterange-picker.blade.php` |

---

## 4. Definition of Done / review checklist
Before merging any change that touches metrics or UI, confirm:

- [ ] No inline metric SQL/formula — it comes from a domain service.
- [ ] No positional `($start,$end,…)` metric signatures — a `MetricFilter` is passed.
- [ ] No literal `'C'/'TP'/1/2` status — `ProcStatus` is used.
- [ ] No hardcoded office string — `ClinicRegistry` is used.
- [ ] No bare `<table>` for data and no direct `$('#x').DataTable(…)` — components + `DDS.dataTable`.
- [ ] Every table has `.dds-table`; no per-page table CSS added.
- [ ] Drill-downs use `DDS.modal`; new tabs are URL-driven; dates use `<x-daterange-picker>`.
- [ ] No new `fmtMoney`/date-picker/modal system.
- [ ] Definition changes are parity-checked (before/after on the golden set) and classified.
- [ ] `php -l` / `node --check` clean; touched Blade compiles.

---

## 5. Anti-patterns that defeat the restructure (never do these)
- Copy-pasting a KPI query into a new controller "just for this page".
- `$('#x').DataTable({ …full config… })` — re-scatters behavior across pages.
- A new `#somethingModal` + `openSomething()`/`closeSomething()` pair.
- A tab that only `classList.toggle('hidden')` with no URL state.
- `'$ ' + v.toLocaleString(...)` inline instead of `DDS.fmt.money(v)`.
- `WHERE ProcStatus = 'C'` / `= 2` in new SQL.
- `'8 Mile'` hardcoded anywhere.

If a genuine need doesn't fit the single source, **extend the single source** (add a method /
option / token) — do not fork it.
