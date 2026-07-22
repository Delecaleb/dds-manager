# 08 — System Coverage Matrix (Nothing Left Behind)

> This document exists to answer one question: **"Does the refactor cover the whole
> system, or just a few pages?"** Answer: the whole system. Every controller, every
> analytics service, and every reporting page is accounted for below. If a metric is
> shown anywhere in the app, this table says which domain service will own it.

## Scope boundary (read this first)

- **IN scope** — every place that *computes a named metric for display*: all reporting
  controllers, all analytics services, all dashboard/report/KPI pages.
- **Intentionally OUT of scope** — two categories that do **not** contain metric
  duplication:
  - **Sync services** (`*SyncService.php`, `OpenDentalClient`, `BaseQuerySyncService`) —
    these *ingest* OpenDental data into local tables. They are already single-purpose and
    write-only; they don't calculate reporting metrics. (They will still adopt the
    `ProcStatus` enum wherever they touch status codes, but their logic isn't refactored.)
  - **Pure infrastructure/UI** — `auth`, `profile`, `layouts`, `components`, `modals`,
    `ProfileController`. No metrics.

Everything else is covered. No reporting page is excluded.

---

## Controllers → domain services

| Controller | Pages it drives | Domain services it will call |
|------------|-----------------|------------------------------|
| `DashboardController` | dashboard | Production, Patient, Financial, Provider, Scheduling |
| `KpisController` | kpi, kpis | Production, TreatmentAcceptance, Patient, Scheduling |
| `OperationsController` (+ `OperationsAnalyticsService`) | operations (offices, production-details, **payors**, performance, providers, services, marketing, trends, claims, compliance, scorecards) | **All** domains |
| `FinancialController` (+ `FinancialAnalyticsService`, `FinancialService`) | financials, rcm | Financial, Production, Insurance |
| `ProviderPortalController` | provider-portal | Provider (composes Production + TreatmentAcceptance) |
| `TxMinerController` | tx-miner | TreatmentAcceptance |
| `HygieneRecallController` | hygiene-recall | Recall, TreatmentAcceptance (hygiene) |
| `AgingController` (+ `AgingCalculationService`) | aging | Financial (AR / aging) |
| `CalendarController` (+ `CalendarService`, `AppointmentService`) | calendar | Scheduling |
| `FrontOfficeController` | front-office, huddle, eod | Production, Scheduling, Patient |
| `PatientController` (+ `PatientAnalyticsService`) | patients | Patient |
| `DepositSlipController` (+ `DepositSyncService` read side) | deposit | Financial (deposits) |
| `ClaimProcsController` | claims views | Insurance |
| `OdClaimPaymentController` | claim payments | Insurance, Financial |

Every controller in `app/Http/Controllers` except `ProfileController` (infra) and the base
`Controller` appears above.

---

## View areas (pages) → coverage

| View area | Covered by | |
|-----------|-----------|---|
| `operations` | All domains (via OperationsController) | ✅ |
| `dashboard` | Production, Patient, Financial, Provider | ✅ |
| `kpi`, `kpis` | Production, TreatmentAcceptance, Patient | ✅ |
| `financials` | Financial, Production | ✅ |
| `rcm` | Financial, Insurance | ✅ |
| `aging` | Financial (AR) | ✅ |
| `deposit` | Financial | ✅ |
| `eod` | Production, Financial | ✅ |
| `front-office` | Production, Scheduling, Patient | ✅ |
| `huddle` | Scheduling, Production, Patient | ✅ |
| `calendar` | Scheduling | ✅ |
| `patients` | Patient | ✅ |
| `provider-portal` | Provider | ✅ |
| `tx-miner` | TreatmentAcceptance | ✅ |
| `hygiene-recall` | Recall, TreatmentAcceptance | ✅ |
| `snapshot` | Reporting output of the above | ✅ |
| `provisioner` | Setup/admin (no metrics) | infra |
| `auth`, `profile`, `layouts`, `components`, `modals` | UI/infra (no metrics) | infra |

---

## Existing analytics services → what happens to each

These are **absorbed into** the domain layer (not left running in parallel). Where two of
them define the same metric differently today (see [02-duplication-audit.md](02-duplication-audit.md)),
the domain service becomes the single definition and the old ones are deleted or reduced to
thin wrappers during their phase.

| Existing service | Fate |
|------------------|------|
| `OperationsAnalyticsService` (3,500 lines) | Split across Production / Patient / Insurance / TreatmentAcceptance / Scheduling. Retired. |
| `DashboardAnalyticsService` | Calls domain services; metric logic removed. |
| `FinancialAnalyticsService`, `FinancialService` | Merged into `Financial\FinancialService`. |
| `PatientAnalyticsService`, `PatientService` | Merged into `Patient\PatientService`. |
| `ProviderService` | Becomes `Provider\ProviderService` (composer). |
| `TreatmentAnalyticsService`, `TreatmentPlanService` | Merged into `TreatmentAcceptance\TreatmentAcceptanceService`. |
| `AgingCalculationService` | Kept, **wrapped** by `Financial\FinancialService` (do not rewrite AR math). |
| `AppointmentService`, `CalendarService` | Merged into `Scheduling\SchedulingService`. |
| `AdjustmentService`, `PaymentService`, `LedgerService`, `AccountModuleService`, `ProcedureService`, `ProcedureCodeService`, `QueryService` | Reviewed per phase; metric logic moves to the owning domain, data-access helpers kept. |
| `*SyncService`, `OpenDentalClient`, `BaseQuerySyncService` | **Untouched** (ingestion, out of scope). Delete the stale `BaseQuerySyncService_Old.php` separately. |

---

## The guarantee

By the end of the phased plan ([06-migration-plan.md](refractor-blueprint/06-migration-plan.md)):

- **Every metric on every reporting page** resolves to a method in one domain service.
- A grep for the old inline patterns (e.g. the case-acceptance `SUM(CASE WHEN ProcStatus
  = 'TP'` block, the `gross - abs(...)` net formula) returns **zero hits outside the
  domain layer** — this is the closing gate of each phase.
- Per-provider numbers reconcile exactly with practice-wide numbers, because both come
  from the same services (they can't today).

This is not a pilot on a few pages. It is a full sweep, executed page-group by page-group
so the app stays working throughout.
