# 01 — Target Architecture

## 1. The layering

```
┌─────────────────────────────────────────────────────────────┐
│  Presentation:  Controllers, Blade, AJAX endpoints           │
│  - coordinate only. NO metric SQL. Build a MetricFilter,      │
│    call a domain service, hand the result to the view.       │
├─────────────────────────────────────────────────────────────┤
│  Domain services:  app/Domain/{Domain}/{Domain}Service.php   │
│  - the ONLY place a named metric is defined.                 │
│  - scalar methods + summary() bundle methods.                │
├─────────────────────────────────────────────────────────────┤
│  Shared kernel:  app/Domain/Support/                         │
│  - ProcStatus, MetricFilter, DateRange, base query helpers.  │
├─────────────────────────────────────────────────────────────┤
│  Data:  Eloquent models / query builder over od_* tables     │
└─────────────────────────────────────────────────────────────┘
```

**Dependency direction (never violate):**
`Presentation → Domain services → Support → Data`.
Higher domains may use lower ones (Production may call Patient for the new-patient
cohort). Lower never call higher. `Support` depends on nothing but the framework.

## 2. Why plain `App\Domain\…`, not a "modules" package

The requested feel — `Modules/Patient/PatientService.php` — is achieved with plain
PSR-4 namespaces under `app/Domain/`. A modules *package* (e.g. nwidart/laravel-modules)
is designed for independently-shippable features with their own routes/views/migrations.
This app is one analytics product, not a set of separable plugins, so the package adds
ceremony with no payoff. If hard module boundaries are ever needed, the package can be
adopted later without moving any of this code.

> Folder on disk: `app/Domain/Patient/PatientService.php`
> Class: `App\Domain\Patient\PatientService`

## 3. The three rules that make it work

### Rule 1 — One filter object, never positional args

**Do not do this:**
```php
public function patientCount($start, $end) { ... }
// ...six months later...
public function patientCount($start, $end, $clinics, $providers, $newOnly, $status) { ... }
```
Every added filter breaks every call site and nobody remembers the argument order.

**Do this:**
```php
public function count(MetricFilter $filter): int { ... }
```
`MetricFilter` carries `start`, `end`, `clinics[]`, `providers[]`, and grows over time
**without changing a single method signature.** See [04-module-map.md](04-module-map.md)
for its shape.

### Rule 2 — Two access modes: scalar + bundle

A KPI page needs 20–30 numbers. If each is a separate method, that's 20–30 full-table
aggregates per request — a performance cliff on large clinics (the app targets millions
of rows).

Every service therefore exposes **both**:

```php
// Scalar — when you need one number:
$gross = $production->grossProduction($filter);

// Bundle — when a page needs many at once, computed in ONE query:
$summary = $production->summary($filter);   // returns a ProductionSummary DTO
$summary->gross; $summary->net; $summary->collection; ...
```

Dashboards use `summary()`. Never loop scalar calls to fill a dashboard.

### Rule 3 — The enforcement rule

> No controller, Blade template, or unrelated service writes raw SQL for a metric
> that has a name. If the number has a name, it comes from its domain service.

This is what actually creates the single source of truth. The folder structure only
makes it *possible*; this rule, enforced in review, makes it *true*.

## 4. The shared kernel (`app/Domain/Support`)

| Class | Responsibility | Kills this duplication |
|-------|----------------|------------------------|
| `ProcStatus` | The one definition of completed / treatment-planned / etc. status codes, plus SQL helpers. | 200+ scattered `'C'` / `['C','2']` / `2` / `"Complete"` literals with 6 encodings. |
| `MetricFilter` | Immutable filter passed to every method: date range, clinics, providers. Fluent builders. | Positional-arg explosion; inconsistent clinic filtering. |
| `DateRange` | Period math: last-year shift, diff/percent-diff periods. | `shiftYear()` re-implemented inline in Dashboard/FrontOffice. |

`ProcStatus` is built **first** — every metric depends on it, and centralizing it is
what silently fixes the "one file counts `'C'` only, another counts `['C','2']`" bug.

## 5. Return types

- **Scalars** return `int` / `float`.
- **Bundles and detail rows** return small `readonly` DTOs (value objects), not loose
  arrays, so a reader/IDE knows exactly what fields exist. Example:
  `ProductionSummary`, `PatientDetail`.
- Arrays are acceptable for tabular report payloads handed straight to a DataTable,
  but the *scalar contract* stays typed.

## 6. Where does the SQL live?

Inside the service, in **protected query-builder methods** — not inline in the public
methods. This keeps public methods readable (business intent) and the SQL isolated and
testable. A separate Repository layer is **optional** and not required for v1; it can be
extracted later if a service's data access grows complex. Do not over-engineer up front.

## 7. Caching seam (future-proofing, not v1 work)

Because every metric now has a single entry point, caching is a later, localized change:
`summary()` can memoize by `MetricFilter` signature, or read from a materialized summary
table, with **zero changes to callers**. This is a major reason the design is worth the
effort — it is the correct place to solve the millions-of-rows performance goal when it
becomes necessary. Do not build caching in v1; just don't design it out.

## 8. Explicitly rejected alternatives

- **A class per metric** (`GrossProductionCalculator`, …) — too granular, hundreds of
  tiny classes. Group by domain instead.
- **One god "AnalyticsService"** — just relocates the 3,500-line problem. Split by domain.
- **Traits mixed into controllers** — hides the source of truth in the consumer; keeps
  logic reachable/duplicable. Services with explicit dependencies instead.
- **Big-bang rewrite** — replaced by the phased, per-metric, validated migration in
  [06-migration-plan.md](06-migration-plan.md).
