# 07 — Reference Example: `ProductionService`

> **This is illustrative design, not shipped code.** It shows the pattern every service
> follows so a third party can read one file and understand the rest. Copy this shape.
> `// D#` comments cite decisions in [03-canonical-definitions.md](03-canonical-definitions.md).

## The shared kernel it depends on

### `app/Domain/Support/ProcStatus.php`
```php
<?php

namespace App\Domain\Support;

/**
 * The single source of truth for OpenDental procedure status codes.
 * The synced data encodes status inconsistently (letters AND numerics), so every
 * status comparison in the app goes through here — never a raw literal.
 */
final class ProcStatus
{
    public const COMPLETED         = ['C', '2'];   // blueprint D1
    public const TREATMENT_PLANNED = ['TP', '1'];  // blueprint D2

    /** @return string[] */
    public static function completed(): array
    {
        return self::COMPLETED;
    }

    /** @return string[] */
    public static function treatmentPlanned(): array
    {
        return self::TREATMENT_PLANNED;
    }

    /** SUM(CASE WHEN {alias}.ProcStatus IN (...) THEN {col} ELSE 0 END) */
    public static function sumWhereCompleted(string $col, string $alias = 'pl'): string
    {
        return self::sumWhere(self::COMPLETED, $col, $alias);
    }

    public static function sumWhereTreatmentPlanned(string $col, string $alias = 'pl'): string
    {
        return self::sumWhere(self::TREATMENT_PLANNED, $col, $alias);
    }

    private static function sumWhere(array $statuses, string $col, string $alias): string
    {
        $list = collect($statuses)->map(fn ($s) => "'".$s."'")->implode(', ');

        return "SUM(CASE WHEN {$alias}.ProcStatus IN ({$list}) THEN {$col} ELSE 0 END)";
    }
}
```

### `app/Domain/Support/MetricFilter.php`
```php
<?php

namespace App\Domain\Support;

use Illuminate\Http\Request;

/** Immutable filter passed to every domain-service method. Grows without breaking signatures. */
final class MetricFilter
{
    public function __construct(
        public readonly string $start,
        public readonly string $end,
        public readonly array  $clinics = [],
        public readonly array  $providers = [],
    ) {}

    public static function fromRequest(Request $r): self
    {
        return new self(
            $r->input('start_date', now()->startOfMonth()->toDateString()),
            $r->input('end_date', now()->toDateString()),
            array_filter((array) $r->input('clinics', [])),
            array_filter((array) $r->input('providers', [])),
        );
    }

    public function withClinics(array $c): self
    {
        return new self($this->start, $this->end, $c, $this->providers);
    }

    public function withProviders(array $p): self
    {
        return new self($this->start, $this->end, $this->clinics, $p);
    }

    public function lastYear(): self
    {
        [$s, $e] = DateRange::shiftYear($this->start, $this->end);

        return new self($s, $e, $this->clinics, $this->providers);
    }

    public function signature(): string
    {
        return md5(json_encode([$this->start, $this->end, $this->clinics, $this->providers]));
    }
}
```

## The DTO the bundle returns

### `app/Domain/Production/ProductionSummary.php`
```php
<?php

namespace App\Domain\Production;

/** Typed result of ProductionService::summary(). A reader/IDE sees exactly what exists. */
final class ProductionSummary
{
    public function __construct(
        public readonly float $gross,
        public readonly float $adjustments,
        public readonly float $writeOffs,
        public readonly float $net,
        public readonly float $collection,
        public readonly float $collectionRate,
        public readonly int   $patientVisits,
        public readonly int   $procedures,
        public readonly int   $workingDays,
        public readonly float $productionPerDay,
        public readonly float $productionPerVisit,
        public readonly float $productionPerProcedure,
    ) {}
}
```

## The service

### `app/Domain/Production/ProductionService.php`
```php
<?php

namespace App\Domain\Production;

use App\Domain\Support\MetricFilter;
use App\Domain\Support\ProcStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ProductionService
{
    // ─────────── Scalars (one number each) ───────────

    public function grossProduction(MetricFilter $f): float
    {
        // D9: completed procedures only
        return (float) $this->completedProcedures($f)->sum('pl.ProcFee');
    }

    public function adjustments(MetricFilter $f): float
    {
        $q = DB::table('od_adjustments as a')->whereBetween('a.AdjDate', [$f->start, $f->end]);
        $this->applyClinics($q, $f, 'a');

        return (float) $q->sum('a.AdjAmt');
    }

    public function writeOffs(MetricFilter $f): float
    {
        $q = DB::table('od_claim_procs as c')->whereBetween('c.ProcDate', [$f->start, $f->end]);
        $this->applyClinics($q, $f, 'c');

        return (float) $q->sum('c.WriteOff');
    }

    public function netProduction(MetricFilter $f): float
    {
        // D3: gross − |adjustments| − |writeoffs|
        return round(
            $this->grossProduction($f)
            - abs($this->adjustments($f))
            - abs($this->writeOffs($f)),
            2
        );
    }

    public function patientVisits(MetricFilter $f): int
    {
        // D7: distinct patient × day
        return (int) $this->completedProcedures($f)
            ->distinct()
            ->count(DB::raw('CONCAT(pl.PatNum, "|", DATE(pl.ProcDate))'));
    }

    public function workingDays(MetricFilter $f): int
    {
        // D6 default
        return (int) $this->completedProcedures($f)
            ->distinct()->count(DB::raw('DATE(pl.ProcDate)'));
    }

    public function productionPerDay(MetricFilter $f): float
    {
        $days = $this->workingDays($f);

        return $days > 0 ? round($this->netProduction($f) / $days, 2) : 0.0;
    }

    // ─────────── Bundle (many metrics, ONE query) ───────────

    public function summary(MetricFilter $f): ProductionSummary
    {
        $row = $this->completedProcedures($f)
            ->selectRaw('
                SUM(pl.ProcFee)                                      AS gross,
                COUNT(*)                                             AS procedures,
                COUNT(DISTINCT CONCAT(pl.PatNum,"|",DATE(pl.ProcDate))) AS patient_visits,
                COUNT(DISTINCT DATE(pl.ProcDate))                   AS working_days
            ')
            ->first();

        $gross   = (float) ($row->gross ?? 0);
        $adj     = $this->adjustments($f);
        $wo      = $this->writeOffs($f);
        $net     = round($gross - abs($adj) - abs($wo), 2);
        $coll    = $this->collection($f);
        $visits  = (int) ($row->patient_visits ?? 0);
        $procs   = (int) ($row->procedures ?? 0);
        $days    = (int) ($row->working_days ?? 0);

        return new ProductionSummary(
            gross:                 round($gross, 2),
            adjustments:           round($adj, 2),
            writeOffs:             round($wo, 2),
            net:                   $net,
            collection:            round($coll, 2),
            collectionRate:        $net > 0 ? round($coll / $net * 100, 2) : 0.0,
            patientVisits:         $visits,
            procedures:            $procs,
            workingDays:           $days,
            productionPerDay:      $days   > 0 ? round($net / $days, 2)   : 0.0,
            productionPerVisit:    $visits > 0 ? round($net / $visits, 2) : 0.0,
            productionPerProcedure:$procs  > 0 ? round($net / $procs, 2)  : 0.0,
        );
    }

    public function collection(MetricFilter $f): float
    {
        $q = DB::table('od_pay_splits as p')->whereBetween('p.DatePay', [$f->start, $f->end]);
        $this->applyClinics($q, $f, 'p');

        return (float) $q->sum('p.SplitAmt');
    }

    // ─────────── Shared private query builders (the only place SQL lives) ───────────

    /** Completed procedure-log rows for the filter — the base every production metric builds on. */
    protected function completedProcedures(MetricFilter $f): Builder
    {
        $q = DB::table('od_procedure_logs as pl')
            ->whereIn('pl.ProcStatus', ProcStatus::completed())   // D1 — single source
            ->whereBetween('pl.ProcDate', [$f->start, $f->end]);

        $this->applyClinics($q, $f, 'pl');
        $this->applyProviders($q, $f, 'pl');

        return $q;
    }

    protected function applyClinics(Builder $q, MetricFilter $f, string $alias): void
    {
        if ($f->clinics) {
            $q->whereIn("{$alias}.ClinicNum", $f->clinics);
        }
    }

    protected function applyProviders(Builder $q, MetricFilter $f, string $alias): void
    {
        if ($f->providers) {
            $q->whereIn("{$alias}.ProvNum", $f->providers);
        }
    }
}
```

## What a caller looks like after migration
```php
// DashboardController
public function index(Request $r, ProductionService $production)
{
    $filter = MetricFilter::fromRequest($r);

    return view('dashboard', [
        'production'         => $production->summary($filter),           // this year
        'productionLastYear' => $production->summary($filter->lastYear()), // comparison
    ]);
}
```
```blade
{{-- dashboard.blade.php --}}
<div>Net production: {{ number_format($production->net, 2) }}</div>
<div>Per day: {{ number_format($production->productionPerDay, 2) }}</div>
```

## Why this satisfies every goal
- **Single source of truth:** net production is defined once, in `netProduction()`/`summary()`.
- **Scalable:** dashboards use `summary()` (one query); caching later drops into `summary()`
  keyed by `$filter->signature()` with zero caller changes.
- **Understandable by a third person:** open `ProductionService`, read the method names —
  that *is* the list of every production number in the app, each with its definition.
- **Extensible:** a new filter is one `MetricFilter` property; no method signature changes.
