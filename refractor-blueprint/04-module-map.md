# 04 — Module Map & Service Contracts

The full set of domain services and their public methods. This is the **contract** —
what callers can rely on. Signatures are the target; bodies are built during migration.

Conventions:
- Every method takes a `MetricFilter` (except pure period helpers).
- Scalar methods return `int` / `float`. Bundles return a `readonly` DTO.
- `// D#` tags reference the decision in [03-canonical-definitions.md](03-canonical-definitions.md).

---

## Shared kernel — `App\Domain\Support`

### `ProcStatus`
```php
final class ProcStatus
{
    // canonical status sets — the ONE definition (blueprint D1, D2)
    public const COMPLETED          = ['C', '2'];      // D1
    public const TREATMENT_PLANNED  = ['TP', '1'];     // D2
    public const EXISTING_OTHER     = ['EC', 'EO', 'R', 'D']; // reference; extend as needed

    /** For ->whereIn('ProcStatus', ProcStatus::COMPLETED) */
    public static function completed(): array;
    public static function treatmentPlanned(): array;

    /** Raw SQL fragment: SUM(CASE WHEN status IN (...) THEN {$col} ELSE 0 END) */
    public static function sumWhereCompleted(string $col, string $alias = 'pl'): string;
    public static function sumWhereTreatmentPlanned(string $col, string $alias = 'pl'): string;
}
```

### `MetricFilter`
The object that replaces positional args everywhere.
```php
final class MetricFilter
{
    public function __construct(
        public readonly string $start,           // 'Y-m-d'
        public readonly string $end,             // 'Y-m-d'
        public readonly array  $clinics = [],    // ClinicNum[]; [] = all
        public readonly array  $providers = [],  // ProvNum[]; [] = all
    ) {}

    // Fluent, immutable derivations:
    public function withClinics(array $c): self;
    public function withProviders(array $p): self;
    public function forPeriod(string $start, string $end): self;
    public function lastYear(): self;            // delegates to DateRange

    // Cache-key signature for the future caching seam:
    public function signature(): string;
}
```
> Adding a filter later (e.g. `excludeHygiene`) is a new constructor property + builder.
> **No existing method signature changes.**

### `DateRange`
```php
final class DateRange
{
    public static function shiftYear(string $start, string $end): array; // [start,end] − 1yr
    public static function diffPeriods(MetricFilter $f): array;          // current vs last-year
}
```

---

## `App\Domain\Production\ProductionService`
The heart of the app. Absorbs A2–A5, A7, A8 from the audit.

```php
// Scalars
public function grossProduction(MetricFilter $f): float;        // SUM(ProcFee), completed  (D9)
public function adjustments(MetricFilter $f): float;
public function writeOffs(MetricFilter $f): float;
public function netProduction(MetricFilter $f): float;          // D3
public function collection(MetricFilter $f): float;             // SUM(SplitAmt)
public function collectionRate(MetricFilter $f): float;         // collection / net * 100

public function patientVisits(MetricFilter $f): int;            // D7
public function procedures(MetricFilter $f): int;
public function workingDays(MetricFilter $f): int;              // D6 default
public function producingDays(MetricFilter $f): int;            // D6
public function hundredDollarDays(MetricFilter $f): int;        // D6

// Ratios
public function productionPerDay(MetricFilter $f): float;
public function productionPerVisit(MetricFilter $f): float;
public function productionPerProcedure(MetricFilter $f): float;

// Bundle — one query, many metrics (dashboards use this)
public function summary(MetricFilter $f): ProductionSummary;
```
`ProductionSummary` (readonly DTO): gross, adjustments, writeOffs, net, collection,
collectionRate, patientVisits, procedures, workingDays, per-day/visit/proc ratios.

---

## `App\Domain\Patient\PatientService`
Absorbs A6 (first-visit cohort) and patient counts/details.

```php
public function count(MetricFilter $f): int;                 // patients seen in period
public function uniquePatients(MetricFilter $f): int;        // D7-C
public function newPatientCount(MetricFilter $f): int;       // D8 — first visit in period
public function existingPatientCount(MetricFilter $f): int;  // first visit before period

/** Reusable cohort other services join against (Production new-pt production, etc.) */
public function firstVisitCohort(MetricFilter $f): Builder;  // PatNum + first_visit
public function isNewPatient(int $patNum, MetricFilter $f): bool;

public function detail(int $patNum): PatientDetail;          // the "patient details anywhere" method
public function summary(MetricFilter $f): PatientSummary;
```

---

## `App\Domain\Insurance\PayorService`
Absorbs A10 and the Operations "Payors tab" logic (including the Case Acceptance column
just fixed — it moves here and calls `TreatmentAcceptanceService`).

```php
/** patient -> plan map, the ONE definition (D10, flagged for review) */
public function planForPatientSubquery(): Builder;   // PatNum + PlanNum
public function payorLabel(int $planNum): string;

public function productionByPayor(MetricFilter $f): array;   // per-plan rows
public function newPatientsByPayor(MetricFilter $f): array;
public function summary(MetricFilter $f): array;             // the Payors tab payload
```

---

## `App\Domain\TreatmentAcceptance\TreatmentAcceptanceService`
Absorbs A1 (case acceptance) in all its forms — the single home of that formula.

```php
public function proposed(MetricFilter $f): float;    // SUM TP ProcFee (D2)
public function completed(MetricFilter $f): float;   // SUM completed ProcFee (D1)
public function accepted(MetricFilter $f): float;    // TP with appointment (D5)

public function rate(MetricFilter $f): float;                 // D4 — the ONE formula
public function sameDayRate(MetricFilter $f): float;
public function rolling90Rate(MetricFilter $f): float;
public function txPlansPerDay(MetricFilter $f): float;

public function summary(MetricFilter $f): CaseAcceptanceSummary;
```
> When `TxMinerController`, `KpisController`, and the Operations Payors tab all call
> `rate($f)`, the "6-place edit" problem is gone.

---

## `App\Domain\Scheduling\SchedulingService`
```php
public function appointmentCount(MetricFilter $f): int;
public function scheduledProduction(MetricFilter $f): float;
public function reappointmentRate(MetricFilter $f): float;   // AptNum with NextAptNum set
public function capacity(MetricFilter $f): array;            // calendar capacity view
public function summary(MetricFilter $f): SchedulingSummary;
```

## `App\Domain\Financial\FinancialService`
```php
public function collections(MetricFilter $f): float;
public function adjustmentsBreakdown(MetricFilter $f): array;
public function accountsReceivable(MetricFilter $f): array;  // aging buckets
public function deposits(MetricFilter $f): array;
public function payPlanCharges(MetricFilter $f): array;
public function summary(MetricFilter $f): FinancialSummary;
```
> AR/aging may reuse the existing `AgingCalculationService`; wrap, don't duplicate.

## `App\Domain\Recall\RecallService`
```php
public function due(MetricFilter $f): int;
public function overdue(MetricFilter $f): int;
public function scheduled(MetricFilter $f): int;
public function byType(MetricFilter $f): array;
public function summary(MetricFilter $f): RecallSummary;
```

## `App\Domain\Provider\ProviderService` (composer, not a new data source)
Per-provider views compose the services above by iterating providers and calling their
`summary()` with `$filter->withProviders([$provNum])`. It **owns no metric formulas** —
it only orchestrates, which is why per-provider and practice-wide numbers can never drift.
```php
public function scorecard(MetricFilter $f): array;   // one row per provider
```

---

## Consumer rewrite pattern (what controllers become)
```php
public function index(Request $r, ProductionService $production)
{
    $filter = MetricFilter::fromRequest($r);   // builds date + clinics + providers
    return view('dashboard', [
        'production' => $production->summary($filter),
    ]);
}
```
No SQL. No formulas. Just: build filter → call service → pass to view.
