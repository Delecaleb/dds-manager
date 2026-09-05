<?php

namespace App\Domain\Production;

use App\Domain\Support\MetricFilter;
use App\Domain\Support\ProcCode;
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
 *   collection       = SUM(od_pay_splits.SplitAmt on DatePay) + SUM(od_claim_procs.InsPayAmt on DateCP)
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

    /** Adjustments: signed sum (od_adjustments.AdjAmt minus od_claim_procs.WriteOff, matching Jarvis real app). */
    public function adjustments(MetricFilter $filter): float
    {
        $q = DB::table('od_adjustments as a')->whereBetween('a.AdjDate', [$filter->start, $filter->end]);
        $this->applyClinicProvider($q, $filter, 'a');
        $adj = (float) $q->sum('a.AdjAmt');

        $qWo = DB::table('od_claim_procs as c')->whereBetween('c.ProcDate', [$filter->start, $filter->end]);
        $this->applyClinicProvider($qWo, $filter, 'c');
        $wo = (float) $qWo->sum('c.WriteOff');

        return round($adj - $wo, 2);
    }

    /** Writeoffs: positive magnitude sum (subtracted from gross to reach net). */
    public function writeOffs(MetricFilter $filter): float
    {
        $q = DB::table('od_claim_procs as c')->whereBetween('c.ProcDate', [$filter->start, $filter->end]);
        $this->applyClinicProvider($q, $filter, 'c');

        return round((float) $q->sum('c.WriteOff'), 2);
    }

    /** Net production = gross + adjustments (where adjustments is signed net of writeoffs). */
    public function netProduction(MetricFilter $filter): float
    {
        return $this->netFrom(
            $this->grossProduction($filter),
            $this->adjustments($filter),
            0.0
        );
    }

    /**
     * The NET-production FORMULA in one place (blueprint D3): gross + adjustments - writeoffs.
     *
     * Exposed for grouped reports (per-office / per-payor tables) that aggregate the
     * components themselves but must not re-implement the formula. If `$adjustments` is
     * already net of writeoffs, `$writeOffs` can be 0.0.
     */
    public function netFrom(float $gross, float $adjustments, float $writeOffs = 0.0): float
    {
        return round($gross + $adjustments - $writeOffs, 2);
    }

    public function collection(MetricFilter $filter): float
    {
        $q = DB::table('od_pay_splits as p')->whereBetween('p.DatePay', [$filter->start, $filter->end]);
        $this->applyClinicProvider($q, $filter, 'p');
        $pat = (float) $q->sum('p.SplitAmt');

        $qIns = DB::table('od_claim_procs as cp')
            ->whereBetween('cp.DateCP', [$filter->start, $filter->end])
            ->where('cp.Status', '!=', 0);
        $this->applyClinicProvider($qIns, $filter, 'cp');
        $ins = (float) $qIns->sum('cp.InsPayAmt');

        return round($pat + $ins, 2);
    }

    public function collectionRate(MetricFilter $filter): float
    {
        $net = $this->netProduction($filter);

        return $net > 0 ? round($this->collection($filter) / $net * 100, 2) : 0.0;
    }

    public function patientVisits(MetricFilter $filter): int
    {
        $excludedCodes = ProcCode::brokenAppointmentCodeNums($filter->officeId);

        return (int) $this->completedProcedures($filter)
            ->whereNotIn(DB::raw("COALESCE(pl.CodeNum, '')"), $excludedCodes)
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
        $net = $this->netFrom($gross, $adj, 0.0);
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
            $q->join('od_procedures as pc', function ($join) use ($filter) {
                $join->on('pl.CodeNum', '=', 'pc.CodeNum')
                    ->where('pc.office_id', '=', $filter->officeId);
            })->where(function ($q) use ($filter) {
                if ($filter->hygiene) {
                    $q->whereIn('pc.IsHygiene', ['true', '1', 1, true]);
                } else {
                    $q->whereIn('pc.IsHygiene', ['false', '0', 0, false])
                        ->orWhereNull('pc.IsHygiene');
                }
            });
        }

        $this->applyClinicProvider($q, $filter, 'pl');

        return $q;
    }

    protected function applyClinicProvider(Builder $q, MetricFilter $filter, string $alias): void
    {
        $q->where("{$alias}.office_id", $filter->officeId);

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
