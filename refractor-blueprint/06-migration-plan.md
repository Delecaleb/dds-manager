# 06 — Migration Plan (Phased & Validated)

The refactor is done **one metric/domain at a time**, each step independently shippable
and validated. No big-bang branch. The rule: the app is fully working and better after
every step.

## The golden rule: snapshot before, snapshot after

Because the current copies already disagree, centralizing **will move some numbers**.
Every migration step is bracketed by a snapshot so each change is *intentional and
explained*, never silent.

```
1. SNAPSHOT  — capture current outputs for a fixed set of (date range × clinic) inputs.
2. MIGRATE   — point call sites at the new domain service.
3. SNAPSHOT  — capture again.
4. DIFF      — every changed number is either:
                 (a) expected (a known divergence being fixed — cite the D# decision), or
                 (b) a regression — stop and fix before shipping.
```

Snapshots use the same technique already used to verify the Payors Case Acceptance fix
(driving the service via `tinker` / a throwaway command over known inputs and dumping
JSON). A small `php artisan blueprint:snapshot` helper can standardize this, writing
JSON files under `refractor-blueprint/snapshots/` (git-ignored) for diffing.

## Fixed validation inputs (define once, reuse every step)
- **Date ranges:** current month, current YTD, a full prior year, an empty range.
- **Clinics:** all, one busy clinic, one quiet clinic.
- **Providers:** all, one high-volume provider.

The cartesian product of these is the "golden set." Every migrated method is checked
against it.

---

## Phases

### Phase 0 — Kernel + Pilot   (~1 day)
- Build `Support/ProcStatus`, `Support/MetricFilter`, `Support/DateRange`.
- Build `TreatmentAcceptanceService` with the confirmed **D4** formula.
- Migrate **all** case-acceptance call sites (Kpis ×4, Operations, TxMiner) to it.
- Snapshot-diff. Expected change: Kpis numbers shift because `['C','2']` now included (D1).
- **Ship.** This alone proves the pattern end-to-end and fixes a real bug.

### Phase 1 — ProductionService   (~2 days)
- gross, adjustments, writeoffs, net (**D3**), collection, ratios, working-days (**D6**),
  patient-visits (**D7**), + `summary()`.
- Migrate Operations, Dashboard, Financial, Kpis, ProviderPortal, FrontOffice call sites.
- Snapshot-diff — this is the biggest number-movement step (net production had 3 defs).

### Phase 2 — PatientService   (~1–2 days)
- Counts, new/existing (**D8**), the reusable `firstVisitCohort()` (kills ~19 copies).
- Re-point Production's new-patient logic to consume this cohort.

### Phase 3 — PayorService   (~1–2 days)
- Payor mapping (**D10** — includes the flagged `MAX(PlanNum)` review).
- Move the Operations Payors tab (incl. the Case Acceptance column) here; it now calls
  `TreatmentAcceptanceService` instead of holding its own copy.

### Phase 4 — Scheduling / Financial / Recall   (~1–2 days each)
- One at a time, same snapshot loop. Wrap existing `AgingCalculationService` rather than
  rewriting it.

### Phase 5 — Provider composer   (~1 day)
- `ProviderService` orchestrates the above per provider. Verify per-provider totals now
  reconcile exactly with practice-wide totals (they currently can't, because of drift).

### Phase 6 — Frontend consolidation   (~1–2 days)
- Follow [05-frontend-consolidation.md](05-frontend-consolidation.md) priority order:
  F1 (drill-downs) → F2 (`#providerModal`) → F4 (`fmtMoney`) → rest.
- Manual UI regression pass per page touched.

### Buffer   (~2–3 days)
- Edge cases surfaced in the real UI, review feedback, doc updates.

**Total: ~3–4 weeks calendar** at a careful pace; compresses if D-decisions in
[03-canonical-definitions.md](03-canonical-definitions.md) are answered up front.

---

## Definition of Done per phase
- [ ] New service + DTOs exist, typed, documented with `// D#` citations.
- [ ] **All** call sites for that metric migrated (grep proves no raw copies remain).
- [ ] Snapshot diff reviewed; every change classified expected/regression.
- [ ] Old private helpers / inline SQL for that metric deleted (no dead duplicates left).
- [ ] Shipped and merged on its own.

## Guardrails
- **Never** leave a metric half-migrated (some callers on the service, some on inline SQL)
  across a merge boundary — that is *two* sources of truth, worse than one.
- **Grep gate:** before closing a phase, grep for the old pattern (e.g. the
  `SUM(CASE WHEN...ProcStatus = 'TP'` block) and confirm zero remain outside the service.
- **One data check owed up front:** the sign convention of `AdjAmt` / `WriteOff` (D3).
  Do this before Phase 1.

## Rollback
Each phase is one mergeable unit → `git revert` of that merge cleanly restores prior
behavior. This is only possible *because* the work isn't a monolithic branch.

## Progress tracker
| Phase | Domain | Status | Snapshot diff notes |
|-------|--------|--------|---------------------|
| 0 | Kernel + Case Acceptance | ☐ not started | |
| 1 | Production | ☐ | |
| 2 | Patient | ☐ | |
| 3 | Payor | ☐ | |
| 4a | Scheduling | ☐ | |
| 4b | Financial | ☐ | |
| 4c | Recall | ☐ | |
| 5 | Provider composer | ☐ | |
| 6 | Frontend | ☐ | |
