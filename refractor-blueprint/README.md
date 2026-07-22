# Refactor Blueprint — Single Source of Truth Architecture

> **Status:** Planning / design only. **No application code has been changed.**
> This folder is the reference the team reads *before* any refactor code is written.

## Why this exists

The application computes the same business metrics (production, patient counts,
case acceptance, collections, …) in many different places. Those copies have
**drifted apart** — the same metric is calculated three different ways in three
files — which means numbers disagree between pages and a single fix has to be
applied by hand in 6+ locations.

The goal of this refactor is one sentence:

> **Every named metric in the app has exactly one definition, in exactly one place.**

Change "net production" once → every dashboard, KPI, report and drill-down updates
together, correctly.

## The target architecture in one picture

```
app/Domain/
  Support/                 ← shared kernel (no business logic; everyone depends on it)
    ProcStatus.php             the one definition of completed / treatment-planned / etc.
    MetricFilter.php           the filter object passed to every method (date, clinics, providers…)
    DateRange.php              last-year / period-shift logic
  Patient/PatientService.php
  Production/ProductionService.php
  Insurance/PayorService.php
  Scheduling/SchedulingService.php
  Financial/FinancialService.php
  TreatmentAcceptance/TreatmentAcceptanceService.php
  Recall/RecallService.php
  Provider/ProviderService.php   ← composes the services above, per provider
```

Controllers and Blade views **stop containing SQL for named metrics**. They call
domain services:

```php
$production = $productionService->grossProduction($filter);
$patients   = $patientService->count($filter);
```

## How to read this blueprint

Read in order:

| Doc | What it answers |
|-----|-----------------|
| [01-architecture.md](01-architecture.md) | The target design, the 3 rules that make it work, and the traps it avoids. |
| [02-duplication-audit.md](02-duplication-audit.md) | Exactly where the duplication is today (file:line), backend and frontend. |
| [03-canonical-definitions.md](03-canonical-definitions.md) | **Decisions you must make**: where copies disagree, the options, and the recommended pick. |
| [04-module-map.md](04-module-map.md) | Every domain service and its method signatures. The contract. |
| [05-frontend-consolidation.md](05-frontend-consolidation.md) | Collapsing the 3 drill-down modal systems + shared JS/format helpers. |
| [06-migration-plan.md](06-migration-plan.md) | Phased, validated rollout. Sequencing, snapshot testing, timeline. |
| [07-reference-example.md](07-reference-example.md) | One fully-worked service (`ProductionService`) as the pattern to copy. |

## Non-negotiables (the whole thing fails without these)

1. **One filter object, not positional args.** Methods take `MetricFilter`, never
   `($start, $end, $clinics, $providers, …)`. This is what lets the design survive
   new requirements.
2. **Two access modes.** Scalar methods for single numbers; `summary()` bundle
   methods that compute many metrics in one query for dashboards. Prevents the
   "30 aggregate queries per page" trap.
3. **A shared kernel.** `ProcStatus`, `MetricFilter`, `DateRange` live in `Support`
   and everyone depends on them. Modules never redefine primitives.
4. **The enforcement rule.** No controller, Blade template, or unrelated service
   writes raw SQL for a metric that has a name. If it has a name, it comes from its
   domain service. Enforced in code review.

## What "done" looks like

- A new engineer can open `ProductionService.php` and know how *every* production
  number in the app is calculated.
- Changing a metric definition is a one-method edit.
- Numbers are identical across every page that shows them.
- Adding a filter (say "exclude hygiene") does not break any existing call site.
