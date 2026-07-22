# Phase Log — execution record

Chronological record of what was actually built and validated. Newest first.

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
