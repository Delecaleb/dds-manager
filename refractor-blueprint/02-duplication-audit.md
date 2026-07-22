# 02 — Duplication Audit (Evidence)

> Snapshot of the current tree. Line numbers are indicative and will drift as the
> code changes; treat them as "look here," not exact anchors. This is the *why* behind
> the refactor — the concrete cost of the status quo.

A partial helper already exists — `app/Helpers/MetricDefinitions.php` — but it only
covers trivial single-column fragments (gross, adjustments, writeoffs, collections,
visits) and is used in just 3 files. Every **compound** formula below is copy-pasted
inline, mostly in `KpisController.php` (~1,360 lines) and
`OperationsAnalyticsService.php` (~3,500 lines).

---

## A. Backend — duplicated calculation logic

### A1. Case Acceptance — `(completed + accepted) / proposed`
`proposed` = SUM(ProcFee) for TP; `completed` = SUM(ProcFee) for C; `accepted` =
SUM(ProcFee) for TP with an appointment (`AptNum IS NOT NULL AND AptNum != '0'`).

SQL `SUM(CASE WHEN …)` block repeated at:
- `KpisController.php:76-79` (practice-wide)
- `KpisController.php:279-282` (doctor)
- `KpisController.php:821-823` (hygienist per-provider)
- `KpisController.php:979-981` (doctor per-provider)
- `OperationsAnalyticsService.php:359-362` (uses mixed literals `IN ('TP','1')` / `IN ('C','2')`)

PHP rate formula repeated at: `KpisController.php:230, 369, 920, 1043`;
`OperationsAnalyticsService.php:404+420` and `:199` (aggregated).

**Divergent 4th implementation:** `TxMinerController.php:32-61` computes the same concept
with different variable names and structure.

**⚠ Real bug from the drift:** the Kpis copies match completed as `'C'` **only**, while
Operations matches `['C','2']`. They count different sets of completed procedures, so the
same KPI can differ between pages.

Same-day / rolling-90 variants: `KpisController.php:368, 700-701, 1042, 1359-1360`.

### A2. Net production — **three conflicting definitions live at once**
- `gross − |adj| − |writeoff|` → `OperationsAnalyticsService.php:399, 654, 1291, 1569`;
  `OperationsController.php:496`; `DashboardController.php:110`.
- `gross + adj + writeoff` (additive, relies on stored sign) → `FinancialAnalyticsService.php:28`.
- `gross + adj`, **no writeoffs at all** → `FinancialController.php:473-508` (`bkNetProduction`).

### A3. Gross production — `SUM(ProcFee)`
Helper exists (`MetricDefinitions::grossProduction()`), but raw `SUM(ProcFee)` is
hand-written, bypassing it, at 25+ sites across `KpisController`,
`OperationsAnalyticsService`, `ProviderPortalController`, `FinancialController`,
`FrontOfficeController`, `OdProcedureLog.php`.

### A4. Collection / collection %
- Collection `SUM(SplitAmt)` — helper exists; also inline at `OperationsController.php:455,488`.
- Collection % `collection/net*100` — `OperationsAnalyticsService.php:1584`;
  `OperationsController.php:517,530`.

### A5. Per-day / per-visit / per-procedure ratios
`prod/work_days`, `prod/visits`, `prod/procedures` repeated across
`KpisController` (many), `ProviderPortalController.php:145-146`, `DashboardController`.
`production_per_day` block near-verbatim at `KpisController.php:710-723` and `:1111-1119`.

### A6. New-patient / first-visit detection — `MIN(ProcDate)` per patient (~19 copies)
- Join-subquery shape (`fv.first_date` in range) — `OperationsAnalyticsService.php`
  (10 copies: 470, 766, 884, 1395, 1635, 1969, 2893, 2973, 3051, 3082);
  `OperationsController.php:651, 710, 774`; `DashboardController.php:120, 166, 388`.
- CTE shape (`pt_hist.first_visit`, new vs existing) — `KpisController.php:322-324,
  1012-1013, 455`.
- Model version — `OdProcedureLog.php:104-110`.
- **Competing flag path:** `IsNewPatient = 'true'` at `OdAppointment.php:81-85`,
  `FinancialController.php:169,673`, `OperationsAnalyticsService.php:912`,
  `CalendarController.php:225`. (Two different definitions of "new patient.")

### A7. Working days — **three different definitions**
- `COUNT(DISTINCT ProcDate)` → `KpisController.php:48,243,418,799,957`.
- `COUNT(DISTINCT CASE WHEN ProcFee>0 THEN DATE(ProcDate) END)` (fee-gated) →
  `KpisController.php:711,1111`.
- `COUNT(DISTINCT DATE(ProcDate))` over a `daily_prod >= 100` subquery ("$100 day") →
  `KpisController.php:676`.

### A8. Patient visits — **three different definitions**
- `COUNT(DISTINCT PatNum, DATE(ProcDate))` (helper) → used in 3 files.
- `COUNT(DISTINCT CONCAT(PatNum,'-',ProcDate))` → `KpisController.php:49,157,244,800,874,958`.
- `COUNT(DISTINCT PatNum)` (no date) → `KpisController.php:486,506,712,755`.

### A9. Date-range shifting (last-year)
Good helper exists but is **private** to one service: `OperationsAnalyticsService::shiftYear()`
(`:1759-1764`). Re-implemented inline at `DashboardController.php:297-298, 376-377`;
`FrontOfficeController.php:48-49`.

### A10. Payor mapping subquery
`SELECT PatNum, MAX(PlanNum) FROM od_claim_procs GROUP BY PatNum` duplicated across the
two methods that use it: `OperationsAnalyticsService.php:292-294` and `:467`.

### A11. ProcStatus encoding — the root cause
No enum, despite `app/Enums/` existing. 200+ occurrences, **6 encodings**: `'C'`, `'2'`,
`['C','2']`, integer `2`, `"Complete"`, and status-set arrays. Other sets in use:
`IN ('C','TP')`, `IN ('C','S')`, `['Scheduled','Active','Accepted']`.

---

## B. Frontend — duplicated UI

### B1. Three parallel drill-down modal systems (same job)
1. **Shared (keep this one):** `openLimitlessModal()` (`operations/tabs/table.blade.php:357`)
   + `components/app-components/drilldown/table-modal.blade.php` + `table-content.blade.php`.
2. **Legacy embedded:** `openOpsDrilldown()` + `#ops_drilldown_modal`
   (`operations/tabs/table.blade.php:376, 331`) — commented "backward compatibility."
3. **Copy of #2:** `openMarketingDrilldown()` + `#marketing_drilldown_modal`
   (`operations/tabs/marketing.blade.php:549, 404`).

### B2. Duplicate `#providerModal` DOM id (collision bug)
Defined twice with the same id: `dashboard.blade.php:1206` and
`components/app-components/patient-modal.blade.php:810`. Any page including both breaks.

### B3. Two breakdown modals in one file
`financials/index.blade.php` ships a hand-rolled `#bkOverlay` (`:706`) **and** the shared
`<x-app-components.datatable-modal id="breakdown-modal">` (`:1902`).

### B4. `fmtMoney` — 4 divergent copies
`dashboard.blade.php:499`, `financials/index.blade.php:773`,
`provider-portal/index.blade.php:187`, `deposit/index.blade.php:142` — each handles
null / `$` spacing / negatives differently. Plus `kpis:472 fmtKpi()`,
`provider-portal:194 fmtDec1()`. Guaranteed inconsistent display.

### B5. Server-side formatting re-implemented
`ops_fmt()` / `ops_heat_class()` (`operations/tabs/table.blade.php:14, 45`) are reused via
`@include` by 6 ops tabs (good), but the same money/percent formatting is re-done in
`drilldown/table-content.blade.php:25-29, 78-82`.

### B6. Donut chart config — 8+ hand-written copies
`createDonutConfig` exists (`financials/index.blade.php:1153`) but is not reused; donut
configs re-written in `front-office/partials/schedule.blade.php`, `collections.blade.php`,
`operations/tabs/services.blade.php`, `marketing.blade.php` (×4).

### B7. Full-widget duplicate
`front-office/tasks.blade.php` is a near byte-for-byte copy of
`front-office/partials/tasks.blade.php` (chart + init block).

---

## C. Worst offenders (priority targets)
1. `KpisController.php` — case acceptance ×4, per-day/visit ratios, new/existing blocks ×2.
2. `OperationsAnalyticsService.php` — net production ×4, first-visit subquery ×10, payor
   map ×2, the one good `shiftYear` helper (but private).
3. **ProcStatus encoding** — most pervasive; fix first (foundation).
4. **Three drill-down modal systems** — highest-value frontend consolidation.
