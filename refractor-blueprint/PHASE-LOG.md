# Phase Log — execution record

Chronological record of what was actually built and validated. Newest first.

---

## Phase 2.2 — Single-source patient_visits + avg production (Dashboard/Financials)   (2026-07-23)

### Changed
- `PatientAnalyticsService` — injected `ProductionService`; `patient_visits` now comes from
  `ProductionService::patientVisits()` (visit-events, D7) and `patient_avg_production` from
  `grossProduction() / visits`. Both previously used `OdProcedureLog`'s `'C'`-only methods.
- Removed `OdProcedureLog::patientVisits()` and `avgProductionPerPatient()` (dead; only
  consumer was PatientAnalyticsService; no test refs).

### Why
The Dashboard/Financials `patient_visits` KPI filtered `ProcStatus = 'C'` (letter only).
Routing through the domain service uses `['C','2']`, so the metric no longer depends on the
status encoding. Grouped `MetricDefinitions::patientVisits` fragment sites (Operations,
per-clinic) are separate and untouched.

### Validation
- Parity: visits 2025=1465 / 2024=706; avg 404.10 / 240.19 — identical to the old methods.
- End-to-end via `getPatientAnalytics`: 2025 visits=1465, avg=404.10, new=549. Empty current
  month = 0 (expected).
- Lint clean; DI resolves.

---

## Phase 2.1 — Consolidate new-patient count (fixes a miscount)   (2026-07-23)

### Decision — D8 status set = ['C','2'] (confirmed by product owner)
The 2010-2012 `'2'`-status rows are REAL procedures (comprehensive exams D0150, extractions
D7140, X-rays, composites) — fees just weren't imported for that era. So those patients
genuinely visited then and are NOT new later. First-visit cohort must include them.

### Changed
- `PatientAnalyticsService::getPatientAnalytics` — now uses
  `PatientService::newPatientCount()` (injected) instead of the model's `'C'`-only method.
- Removed `OdProcedureLog::newPatientVisits()` (dead + wrong definition; no other consumers,
  no test refs).

### Validation
- new_patient_visit 2025: **550 → 549** (the returning-since-2011 patient no longer
  miscounted as new). Confirmed correct fix, product-owner approved.
- patient_visits unchanged at 1465 (visit-events: one per patient per day — a patient can
  visit multiple times; product-owner definition, blueprint D7).
- Lint clean; DI resolves.

---

## Phase 2.0 — PatientService + first-visit cohort single-sourced   (2026-07-23)

### Built (additive)
- `app/Domain/Patient/PatientService.php` — `firstVisitCohort()` (first-ever COMPLETED
  procedure per patient, the one cohort definition), plus `count()`, `newPatientCount()`,
  `existingPatientCount()`. Blueprint D8 = first completed procedure in period.

### Changed
- `OperationsAnalyticsService` — injected `PatientService`; replaced all **8** inline
  first-visit cohort subqueries (`MIN(ProcDate) … GROUP BY PatNum`) with
  `$this->patients->firstVisitCohort()`. Parity-perfect (identical query).
- `OperationsController` — injected `PatientService`; replaced **3** more identical
  practice-wide cohort subqueries. Parity-perfect. (11 copies eliminated total.)

### Validation
- Lint clean; zero inline cohort copies remain in the service.
- `PatientService::newPatientCount` == raw inline cohort: 2025 = **549**, 2024 = **318**.
- Cross-check: Payors 2025 total npt_visit (**549**) == PatientService newPatientCount (**549**).
- DI resolves (OperationsAnalyticsService now has 3 domain deps).

### Remaining Patient work
`DashboardController` has 3 cohort copies that are genuinely DIFFERENT variants
(provider-scoped, clinic-grouped, `'C'`-only, some raw SQL) — NOT the practice-wide cohort,
so they need per-case review (possibly a bug: "new to provider" vs "new patient"). Also
copies in FinancialController, KpisController (concurrency-hot), PatientAnalyticsService,
OdProcedureLog. Pending: reconcile the competing `IsNewPatient` flag path (D8).

---

## Phase 1.3 — Grouped gross: completed-status single-sourced in OperationsAnalyticsService   (2026-07-23)

### Changed
- Replaced all **43** inline `['C', '2']` completed-status filters in
  `OperationsAnalyticsService` (+ **17** more in `OperationsController`) with
  `ProcStatus::completed()`. Both already used the canonical `['C','2']` set, so the change
  is **parity-perfect** (identical array value) — it centralizes the "completed" definition
  that underlies grouped gross and every completed-based metric in the Operations layer.

### Validation
- Lint clean; zero `['C','2']` literals remain in code (doc comment retained).
- Payors 2025: 123 rows, total net **672,627.85**, total gross **592,001.70** — unchanged.
- Offices tab runs.

### Note on scope
Other files (Kpis, Financial, ProviderPortal, FrontOffice, Dashboard) filter completed as
`'C'` only. Switching those to `ProcStatus::completed()` is fee-safe for gross but would
change COUNT columns over 2010–2012 (the fee-zero `'2'` rows), so each needs per-query
review — not a blanket replace. Deferred; OperationsAnalyticsService was uniquely safe
because it already used `['C','2']`.

---

## Phase 1.2 — Last net copy removed + FinancialAnalyticsService reuse   (2026-07-23)

### Changed
- `DashboardController::providerPerformance` — the net_production was computed in **SQL with
  `ABS()`** (missed by the earlier PHP-only grep gate). Removed that SQL expression; net is
  now computed per row via `ProductionService::netFrom()`. This was the **last** net copy.
- `FinancialAnalyticsService::filterAnalysis` — now sources gross/net/adjustments/writeoffs/
  collections from `ProductionService::summary()` (one call). Gross-based rates preserved.

### Validation
- Lint clean; grep gate: **zero net formulas remain** outside `ProductionService`.
- `filterAnalysis` 2025: gross 592,001.70, net 672,627.85, net == gross+adj−wo ✓, all 7
  output keys intact (parity with committed behavior).
- `providerPerformance` 2025: 6/6 providers net signed-correct.

### Status
Net production is now **fully single-sourced** across PHP and SQL. Remaining Production work
(all parity-safe reuse, no number changes): route the many grouped GROSS/collection/visits/
working-day queries through `ProductionService` fragments where cleanly separable.

---

## Phase 1.1 — Net-production call sites migrated   (2026-07-22)

### Changed
- Added `ProductionService::netFrom(gross, adjustments, writeOffs)` — the net FORMULA in one
  place; `netProduction()` and `summary()` delegate to it.
- Switched **7 inline net-formula sites** to `netFrom()` (each keeps its local component
  sums; only the formula is centralized + corrected to signed adjustments):
  - `OperationsAnalyticsService` ×4 (offices, payors, and two more grouped tables) —
    injected `ProductionService`.
  - `DashboardController` (provider detail) — added constructor.
  - `OperationsController` (a grouped table) — added constructor.
  - `FinancialAnalyticsService::filterAnalysis` — was `gross + adj + writeoff` (added
    writeoffs, a bug); now correct.

### Validation
- Lint clean; DI resolves for all 4 classes that gained constructors; no `new` instantiation
  to break; grep gate = **ZERO inline net formulas remain**.
- **Cross-consistency (the payoff):** for 2025, Operations Payors total net **= 672,627.85**,
  FinancialAnalyticsService net **= 672,627.85**, and ProductionService net **= 672,627.85** —
  three independent code paths now agree to the cent. They disagreed before.
- `blueprint:production`: gross parity PASS.
- ⚠ As intended (D3), 2025-era net is now higher than the app previously showed (+$161k at
  practice level).

### Remaining net work (flagged, NOT done)
- `FinancialController::bkNetProduction` — a raw-SQL UNION **drill-down ledger** (per-line
  procedures + adjustments, no writeoffs), not an aggregate formula. Needs a dedicated
  `ProductionService` breakdown method + a decision on whether the ledger should list
  writeoff line items. Deferred to a Production-detail sub-phase.

---

## Phase 1.0 — ProductionService built + validated (call sites not yet switched)   (2026-07-22)

### Decision locked — D3 = SIGNED (confirmed by product owner)
- **Gross production** = SUM(ProcFee) for completed procedures, before adj/writeoff (D9).
- **Net production** = `gross + SUM(AdjAmt) − SUM(WriteOff)`. AdjAmt is signed (negatives
  reduce, positives add); WriteOff is a positive magnitude (subtracted).

### Built (additive)
- `app/Domain/Production/ProductionSummary.php` — DTO.
- `app/Domain/Production/ProductionService.php` — gross, adjustments, writeoffs, net,
  collection, collectionRate, patientVisits, procedures, workingDays, per-day/visit/proc,
  and `summary()` (4 queries for a full dashboard bundle).
- `app/Console/Commands/BlueprintProductionCommand.php` — `php artisan blueprint:production`.

### Validation
- **Gross: parity PASS** — equals independent completed-only SUM(ProcFee) in all periods.
- **Net: intended change confirmed.** ⚠ **2025 net is +$161,252 vs the old abs() formula**
  (2025 has +$80,626 of *positive* adjustments the old code wrongly subtracted). 2024/YTD
  Δ = $0 (signed sum negative there, so the two formulas coincide). This is the correction
  the signed definition was chosen to make — it WILL change 2025-era net production on
  every page once call sites are switched.

### Status
Service exists and is validated; **no call site switched** — running app unchanged. Next:
migrate net-production call sites (Operations ×4, Dashboard, Financial) one at a time. Note
these switches change displayed net numbers (unlike Phase 0), so each needs an explicit
before/after snapshot review with you.

---

## Phase 0.3 — OperationsAnalyticsService (Payors tab) — CASE ACCEPTANCE COMPLETE   (2026-07-22)

### Changed
- `OperationsAnalyticsService` — added constructor injecting `TreatmentAcceptanceService`.
- Payors tab (`payorRows` + the `calculateAbsoluteTotal` closure): the per-row rate and the
  weighted total rate now both go through `TreatmentAcceptanceService::rateFrom()`. The
  local grouped/mapped components query stays (it groups by payor, which the service can't),
  but its status sums now use `ProcStatus::sumWhere*` so status codes are defined in one place.

### Validation
- **1,061 payor rows, 0 mismatches** (service `rateFrom` vs legacy inline formula per row).
- **Independent cross-check:** Payors TOTAL case-acceptance equals
  `TreatmentAcceptanceService::rate()` for the all-segment — 135.50% (2025), 55.92% (2024).
  The service computes this without the payor mapping, so an exact match validates both the
  grouped query and the formula.
- No `new OperationsAnalyticsService` anywhere (all via DI); container resolves it.
- `php artisan blueprint:parity`: PASS.

### 🏁 Grep gate — Phase 0 CLOSED
`grep` for inline `(completed + accepted) / proposed` formulas and the `AS proposed`
SUM(CASE) blocks returns **ZERO** hits across `app/`. All **6** original case-acceptance
copies are gone. Case-acceptance now has exactly one definition:
`TreatmentAcceptanceService`.

---

## Phase 0.2 — KpisController migrated (4 case-acceptance sites)   (2026-07-22)

### Changed
- `KpisController` — added constructor injecting `TreatmentAcceptanceService`.
- Replaced all **four** inline `$caRates` SQL blocks with
  `$this->txAcceptance->summary(new MetricFilter(...))`, and all four use sites with
  `$caRates->rate`:
  - `hygieneKpis` (hygiene=true, practice-wide)
  - `doctorKpis` (hygiene=false, practice-wide)
  - `hygieneProviders` (hygiene=true, per-provider `$pId`)
  - `doctorProviders` (hygiene=false, per-provider `$pId`)
- This clears the worst offender: 4 of the 6 duplicated case-acceptance copies gone.

### Validation
- Practice-wide (sites 1–2): covered by `php artisan blueprint:parity` — PASS.
- Per-provider (sites 3–4): **156/156 checks** (2 segments × 3 periods × 26 providers),
  0 mismatches (legacy SQL vs `summary()->rate`).
- DI: `KpisController` constructs and `TreatmentAcceptanceService` is injected.
- (Full-endpoint HTTP smoke test is slow in tinker — these KPI methods fire ~20 aggregate
  queries each, pre-existing; parity proven at unit level regardless.)

### Status
Case-acceptance duplication remaining: **OperationsAnalyticsService** only (the Payors tab).
5 of 6 original copies now removed.

---

## Phase 0.1 — First call site switched: TxMinerController   (2026-07-22)

### Changed
- `TreatmentAcceptanceService` — extracted the formula into a public
  `rateFrom(proposed, completed, accepted)`; `rate()` and `summary()` now delegate to it
  (so the formula can't drift between scalar and grouped-report callers).
- `TxMinerController::data()` — now takes `TreatmentAcceptanceService` via method injection
  and calls `rateFrom()` instead of the inline `(completed + scheduled) / total * 100`.
  This retires the "divergent 4th implementation" flagged in the audit (§A1).
  **The grouped SQL was left untouched** — deliberately, because TxMiner's count-based
  columns (patients_with_tx over 2010–2012) are NOT D1-safe; only the fee-weighted formula
  was centralized.

### Validation
- Per-month call-site parity: **160/160 months, 0 mismatches** (old inline formula vs new
  `rateFrom()` over the exact report query).
- DI smoke test: controller resolves, endpoint returns HTTP 200 with rendered rows.
- `php artisan blueprint:parity`: still **PASS** after the `rateFrom()` refactor.

### Status
TxMiner is live on the service. Running app behavior unchanged (proven identical).
Remaining case-acceptance call sites to migrate: KpisController ×4, OperationsAnalyticsService.

---

## Phase 0 — Kernel + Case Acceptance   (2026-07-22)

### Built (all additive — no existing code path touched)
- `app/Domain/Support/ProcStatus.php` — single source for status codes (D1, D2).
- `app/Domain/Support/DateRange.php` — `shiftYear()` promoted to shared kernel.
- `app/Domain/Support/MetricFilter.php` — the filter object (start, end, clinics,
  providers, hygiene). Hygiene dimension added because every legacy case-acceptance
  query segments on `od_procedures.IsHygiene`.
- `app/Domain/TreatmentAcceptance/CaseAcceptanceSummary.php` — result DTO.
- `app/Domain/TreatmentAcceptance/TreatmentAcceptanceService.php` — the single home of the
  case-acceptance formula (D4-A).
- `app/Console/Commands/BlueprintParityCommand.php` — read-only parity harness
  (`php artisan blueprint:parity`).

### Decisions locked
- **D4 = A** (app-consistent formula `(completed + accepted) / proposed`). Parity-preserving.
- **D1 status set = `['C','2']`**, **D2 = `['TP','1']`** encoded in `ProcStatus`.

### Validation — `php artisan blueprint:parity`
15/15 golden inputs (3 hygiene segments × 5 periods) — Service reproduces the legacy
inline SQL **exactly** under the canonical status set. Result: **PASS**.

### Key finding — D1 is a proven no-op for case acceptance
All `ProcStatus='2'` rows in this DB are from **2010–2012 and have `ProcFee = 0`**
(7,978 rows, $0 total). Therefore including `'2'` in the completed set cannot change any
fee-weighted case-acceptance figure in any date range. Switching call sites to the
service is genuinely behavior-preserving for this metric — not merely "matches in the
tested ranges."
> ⚠ This does **not** generalize to count-based or production metrics over the 2010–2012
> era — revisit the D1 effect per-metric in later phases (esp. Production).

### D3 data check (owed from Phase 1, run early)
- `od_adjustments.AdjAmt` is **signed** (4,706 neg / 3,799 pos rows).
- `od_claim_procs.WriteOff` is **unsigned** (all ≥ 0).
- ⇒ None of the three legacy net-production formulas is fully correct. New **option D**
  proposed: `net = gross + AdjAmt(signed) − WriteOff`. Recorded in
  [03-canonical-definitions.md](03-canonical-definitions.md#d3-net-production). Needs your
  confirmation before Phase 1 build.

### Status
Service exists and is proven correct, but **no live call site has been switched yet** —
the running app is byte-for-byte unchanged. Next step (on your go): switch the lowest-risk
consumer (`TxMinerController`) to `TreatmentAcceptanceService::rate()`, verify the page,
ship; then migrate the remaining 5 case-acceptance call sites one at a time.

### How to re-verify at any time
```
php artisan blueprint:parity
```
Read-only; safe to run anytime. Must print PASS before any call site is switched.
