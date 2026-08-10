<?php

namespace App\Domain\Production;

use App\Domain\Support\MetricFilter;
use App\Domain\Support\ProcStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for production figures.
 *
 * Definitions (confirmed with the product owner, blueprint D3/D9):
 *   gross production = SUM(ProcFee) for COMPLETED procedures (before adjustments/writeoffs)
 *   adjustments      = SUM(AdjAmt), SIGNED — negatives reduce, positives add
 *   writeoffs        = SUM(WriteOff), stored as a positive magnitude
 *   NET PRODUCTION   = gross + adjustments - writeoffs
 *                      (adjustments are already signed, so adding them applies reductions;
 *                       writeoffs are positive, so they are subtracted)
 *   collection       = SUM(SplitAmt)
 *
 * Filters: date range + clinics + providers apply to every metric. The `hygiene` dimension
 * applies only to procedure-derived figures (gross/visits/procedures/days), because
 * adjustments/writeoffs/collections are not hygiene-attributable in OpenDental.
 *
 * @see refractor-blueprint/04-module-map.md
 * @see refractor-blueprint/03-canonical-definitions.md  (D3, D9)
 */
class ProductionService
{
    // ─────────────── Scalars ───────────────

    /** Gross production: completed ProcFee, before adjustments/writeoffs (D9). */
    public function grossProduction(MetricFilter $filter): float
    {
        return round((float) $this->completedProcedures($filter)->sum('pl.ProcFee'), 2);
    }

    /** Adjustments: signed sum (negatives reduce, positives add). */
    public function adjustments(MetricFilter $filter): float
    {
        $q = DB::table('od_adjustments as a')->whereBetween('a.AdjDate', [$filter->start, $filter->end]);
        $this->applyClinicProvider($q, $filter, 'a');

        return round((float) $q->sum('a.AdjAmt'), 2);
    }

    /** Writeoffs: positive magnitude sum (subtracted from gross to reach net). */
    public function writeOffs(MetricFilter $filter): float
    {
        $q = DB::table('od_claim_procs as c')->whereBetween('c.ProcDate', [$filter->start, $filter->end]);
        $this->applyClinicProvider($q, $filter, 'c');

        return round((float) $q->sum('c.WriteOff'), 2);
    }

    /** Net production = gross + adjustments(signed) - writeoffs (blueprint D3). */
    public function netProduction(MetricFilter $filter): float
    {
        return $this->netFrom(
            $this->grossProduction($filter),
            $this->adjustments($filter),
            $this->writeOffs($filter)
        );
    }

    /**
     * The NET-production FORMULA in one place (blueprint D3): gross + adjustments - writeoffs.
     *
     * Exposed for grouped reports (per-office / per-payor tables) that aggregate the
     * components themselves but must not re-implement the formula. `$adjustments` must be
     * the SIGNED SUM(AdjAmt); `$writeOffs` the positive SUM(WriteOff).
     */
    public function netFrom(float $gross, float $adjustments, float $writeOffs): float
    {
        return round($gross + $adjustments - $writeOffs, 2);
    }

    public function collection(MetricFilter $filter): float
    {
        $q = DB::table('od_pay_splits as p')->whereBetween('p.DatePay', [$filter->start, $filter->end]);
        $this->applyClinicProvider($q, $filter, 'p');

        return round((float) $q->sum('p.SplitAmt'), 2);
    }

    public function collectionRate(MetricFilter $filter): float
    {
        $net = $this->netProduction($filter);

        return $net > 0 ? round($this->collection($filter) / $net * 100, 2) : 0.0;
    }

    public function patientVisits(MetricFilter $filter): int
    {
        return (int) $this->completedProcedures($filter)
            ->where('pl.CodeNum', '!=', 626)
            ->distinct()
            ->count(DB::raw($this->visitKeyExpr()));
    }

    public function procedures(MetricFilter $filter): int
    {
        return (int) $this->completedProcedures($filter)->count();
    }

    public function workingDays(MetricFilter $filter): int
    {
        return (int) $this->completedProcedures($filter)
            ->distinct()
            ->count(DB::raw('DATE(pl.ProcDate)'));
    }

    public function productionPerDay(MetricFilter $filter): float
    {
        $days = $this->workingDays($filter);

        return $days > 0 ? round($this->netProduction($filter) / $days, 2) : 0.0;
    }

    public function productionPerVisit(MetricFilter $filter): float
    {
        $visits = $this->patientVisits($filter);

        return $visits > 0 ? round($this->netProduction($filter) / $visits, 2) : 0.0;
    }

    public function productionPerProcedure(MetricFilter $filter): float
    {
        $procs = $this->procedures($filter);

        return $procs > 0 ? round($this->netProduction($filter) / $procs, 2) : 0.0;
    }

    // ─────────────── Bundle (dashboards) ───────────────

    /**
     * All core production metrics. Procedure-derived figures come from a single scan;
     * adjustments/writeoffs/collections are three further aggregates. 4 queries total,
     * versus one-per-metric scalar calls.
     */
    public function summary(MetricFilter $filter): ProductionSummary
    {
        $row = $this->completedProcedures($filter)
            ->selectRaw('
                COALESCE(SUM(pl.ProcFee), 0)          AS gross,
                COUNT(*)                              AS procedures,
                COUNT(DISTINCT '.$this->visitKeyExpr().') AS patient_visits,
                COUNT(DISTINCT DATE(pl.ProcDate))     AS working_days
            ')
            ->first();

        $gross = round((float) ($row->gross ?? 0), 2);
        $adj = $this->adjustments($filter);
        $wo = $this->writeOffs($filter);
        $net = $this->netFrom($gross, $adj, $wo);
        $coll = $this->collection($filter);
        $visits = (int) ($row->patient_visits ?? 0);
        $procs = (int) ($row->procedures ?? 0);
        $days = (int) ($row->working_days ?? 0);

        return new ProductionSummary(
            gross: $gross,
            adjustments: $adj,
            writeOffs: $wo,
            net: $net,
            collection: $coll,
            collectionRate: $net > 0 ? round($coll / $net * 100, 2) : 0.0,
            patientVisits: $visits,
            procedures: $procs,
            workingDays: $days,
            productionPerDay: $days > 0 ? round($net / $days, 2) : 0.0,
            productionPerVisit: $visits > 0 ? round($net / $visits, 2) : 0.0,
            productionPerProcedure: $procs > 0 ? round($net / $procs, 2) : 0.0,
        );
    }

    // ─────────────── Shared query builders (the only place production SQL lives) ───────────────

    /** Completed procedure-log rows for the filter — the base every procedure metric builds on. */
    protected function completedProcedures(MetricFilter $filter): Builder
    {
        $q = DB::table('od_procedure_logs as pl')
            ->whereIn('pl.ProcStatus', ProcStatus::completed())
            ->whereBetween('pl.ProcDate', [$filter->start, $filter->end]);

        if ($filter->hygiene !== null) {
            $q->join('od_procedures as pc', 'pl.CodeNum', '=', 'pc.CodeNum')
                ->where('pc.IsHygiene', $filter->hygiene ? 'true' : 'false');
        }

        $this->applyClinicProvider($q, $filter, 'pl');

        return $q;
    }

    protected function applyClinicProvider(Builder $q, MetricFilter $filter, string $alias): void
    {
        if ($filter->clinics) {
            $q->whereIn("{$alias}.ClinicNum", $filter->clinics);
        }
        if ($filter->providers) {
            $q->whereIn("{$alias}.ProvNum", $filter->providers);
        }
    }

    /** Driver-aware "patient|day" key for distinct visit counting. */
    protected function visitKeyExpr(): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "pl.PatNum || '|' || DATE(pl.ProcDate)"
            : "CONCAT(pl.PatNum, '|', DATE(pl.ProcDate))";
    }
}
