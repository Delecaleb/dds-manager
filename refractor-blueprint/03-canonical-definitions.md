# 03 — Canonical Definitions (DECISIONS REQUIRED)

> This is the document that unblocks the whole refactor. Where the current code
> **disagrees with itself**, someone with business authority must pick the single
> correct definition. Each decision below has a recommendation, but the recommendation
> is not authority — **you confirm each one.**
>
> ⚠ Centralizing on any single definition **will change some displayed numbers**,
> because the copies already differ. That is a *correctness fix*, but it is visible.
> Every such change is validated with a before/after snapshot (see
> [06-migration-plan.md](06-migration-plan.md)) so nothing moves silently.

Legend: **[REC]** = recommended default. Mark your choice in the "Decision" line.

---

## D1. Completed procedure status
The most fundamental one — everything downstream depends on it.

| Option | Meaning |
|--------|---------|
| **A [REC]** — `['C','2']` | Match both letter and numeric encodings of "Complete". The data contains both. |
| B — `'C'` only | Misses numerically-encoded rows (~8k rows in the current DB). Current Kpis behavior. |

**Recommendation:** A. Option B is almost certainly an unintentional undercount.
**Decision:** ______

## D2. Treatment-planned status
| Option | Meaning |
|--------|---------|
| **A [REC]** — `['TP','1']` | Both encodings. |
| B — `'TP'` only | Current DB shows only `'TP'` present for TP, but include `'1'` defensively. |

**Decision:** ______

## D3. Net production
Three definitions exist today. Pick one.

| Option | Formula | Currently in |
|--------|---------|--------------|
| **A [REC]** | `gross − |adjustments| − |writeoffs|` | Operations, Dashboard |
| B | `gross + adjustments + writeoffs` (relies on stored sign) | FinancialAnalyticsService |
| C | `gross + adjustments` (**no writeoffs**) | FinancialController `bkNetProduction` |

**Recommendation:** A, *if* adjustments/writeoffs are stored as signed the way Operations
assumes. **This needs a data check** — confirm the sign convention of `AdjAmt` and
`WriteOff` in `od_adjustments` / `od_claim_procs` before locking. B and A are equivalent
only if signs are consistent; C is missing writeoffs entirely and is likely wrong.
**Decision:** ______  (and: confirm sign convention — signed or absolute?)

## D4. Case Acceptance formula
| Option | Formula | Note |
|--------|---------|------|
| **A [REC — app-consistent]** | `(completed$ + accepted$) / proposed$ × 100` | Matches existing KPIs. **Can exceed 100%** because completed procedures aren't a subset of the period's proposed TP. |
| B — stricter | `accepted$ / proposed$ × 100` | Of what was proposed, how much got scheduled. Always ≤100%. Closer to intuitive "acceptance." |

This is a genuine business fork, not a bug. A keeps every existing page consistent;
B is more intuitive but changes every case-acceptance number in the app.
**Recommendation:** A for a like-for-like refactor; revisit B separately as a product
decision. **Decision:** ______

## D5. "Accepted" definition (inside case acceptance)
Currently "accepted" = TP procedure that has an appointment (`AptNum` set).
Confirm this is the intended proxy for "patient accepted the treatment," vs. e.g. a
`Priority`/`treatment plan status` field if one exists in the synced data.
**Decision:** ______

## D6. Working days — keep as **separate named metrics**, do not merge
These are genuinely different metrics, not copies. Recommendation: expose all three
under distinct names rather than collapsing.

| Name | Formula |
|------|---------|
| `workingDays` **[REC default]** | `COUNT(DISTINCT ProcDate)` with production present |
| `producingDays` | `COUNT(DISTINCT DATE(ProcDate) WHERE ProcFee>0)` |
| `hundredDollarDays` | days with `daily production ≥ $100` |

**Decision:** confirm names / which is the "default" work-day used in per-day ratios: ______

## D7. Patient visit
| Option | Formula |
|--------|---------|
| **A [REC]** | `COUNT(DISTINCT PatNum, DATE(ProcDate))` — one patient, one day = one visit |
| B | `COUNT(DISTINCT CONCAT(PatNum,'-',ProcDate))` — same intent, string form |
| C | `COUNT(DISTINCT PatNum)` — unique patients, **not** visits |

A and B are equivalent; C is a *different metric* ("unique patients"). Recommendation:
`patientVisits` = A; expose C separately as `uniquePatients` where actually needed.
**Decision:** ______

## D8. New patient definition
| Option | Meaning |
|--------|---------|
| **A [REC]** | First-ever procedure: `MIN(ProcDate)` per patient falls in the period |
| B | The `IsNewPatient='true'` flag on appointments |

These can disagree. Recommendation: A as the analytics definition (deterministic from
procedure history); document where B is intentionally used (e.g. front-desk appointment
tagging). **Decision:** ______

## D9. Gross production status filter
Confirm gross production counts **completed procedures only** (`D1` set), not all
procedure-log rows regardless of status. Some current call sites sum `ProcFee` unfiltered.
**Recommendation:** completed-only. **Decision:** ______

## D10. Payor / plan mapping
Mapping a patient to a plan via `MAX(PlanNum)` from `od_claim_procs` = "the patient's
highest plan number." Confirm the intended rule is **most-recent/primary plan**, and
whether `MAX(PlanNum)` is actually a correct proxy for that (it may not be — highest id
≠ primary/active plan). **This is flagged as a likely latent bug.**
**Decision / investigate:** ______

---

## How these feed the build
Each confirmed decision becomes a constant/method in the shared kernel or a domain
service, cited by name in code comments (e.g. `// canonical: blueprint D3-A`). When a
definition later changes, it changes in that one place, and this document is updated to
record the new decision and date.
