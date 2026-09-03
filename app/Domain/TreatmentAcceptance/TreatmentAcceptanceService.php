<?php

namespace App\Domain\TreatmentAcceptance;

use App\Domain\Support\MetricFilter;
use App\Domain\Support\ProcStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * The single home of the Case Acceptance calculation.
 *
 * Before this service, the formula (completed + accepted) / proposed was copy-pasted in
 * 6+ places (KpisController ×4, OperationsAnalyticsService, TxMinerController) with drift
 * between the copies. Every caller now routes here.
 *
 * @see refractor-blueprint/04-module-map.md
 * @see refractor-blueprint/03-canonical-definitions.md  (D1, D2, D4, D5)
 */
class TreatmentAcceptanceService
{
    /** Proposed = SUM ProcFee of treatment-planned procedures (D2). */
    public function proposed(MetricFilter $filter): float
    {
        return round($this->components($filter)->proposed, 2);
    }

    /** Completed = SUM ProcFee of completed procedures (D1). */
    public function completed(MetricFilter $filter): float
    {
        return round($this->components($filter)->completed, 2);
    }

    /** Accepted = SUM ProcFee of TP procedures that have an appointment (D5). */
    public function accepted(MetricFilter $filter): float
    {
        return round($this->components($filter)->accepted, 2);
    }

    /**
     * Case acceptance rate (D4-A): (completed + accepted) / proposed * 100.
     * Returns 0.0 when nothing was proposed.
     */
    public function rate(MetricFilter $filter): float
    {
        $c = $this->components($filter);

        return $this->rateFrom($c->proposed, $c->completed, $c->accepted);
    }

    /**
     * The case-acceptance FORMULA in one place (D4-A): (completed + accepted) / proposed * 100.
     *
     * Exposed for grouped reports (e.g. per-month DataTables) that must compute the
     * proposed/completed/accepted components with their own aggregation but should not
     * re-implement the formula. Returns 0.0 when nothing was proposed.
     */
    public function rateFrom(float $proposed, float $completed, float $accepted): float
    {
        return $proposed > 0
            ? min(100.0, round(($completed + $accepted) / $proposed * 100, 2))
            : 0.0;
    }

    /** All components + rate in a single query. */
    public function summary(MetricFilter $filter): CaseAcceptanceSummary
    {
        $c = $this->components($filter);

        return new CaseAcceptanceSummary(
            proposed: round($c->proposed, 2),
            completed: round($c->completed, 2),
            accepted: round($c->accepted, 2),
            rate: $this->rateFrom($c->proposed, $c->completed, $c->accepted),
        );
    }

    /**
     * Compute proposed / completed / accepted dollar totals in one scan.
     *
     * @return object{proposed: float, completed: float, accepted: float}
     */
    protected function components(MetricFilter $filter): object
    {
        $proposedSql = ProcStatus::sumWhereTreatmentPlanned('pl.ProcFee', 'pl');
        $completedSql = ProcStatus::sumWhereCompleted('pl.ProcFee', 'pl');
        $tpList = ProcStatus::inList(ProcStatus::TREATMENT_PLANNED);
        $acceptedSql = "SUM(CASE WHEN pl.ProcStatus IN ({$tpList}) "
            ."AND pl.AptNum IS NOT NULL AND pl.AptNum <> '0' THEN pl.ProcFee ELSE 0 END)";

        $row = $this->baseQuery($filter)
            ->selectRaw("
                COALESCE({$proposedSql}, 0)  AS proposed,
                COALESCE({$completedSql}, 0) AS completed,
                COALESCE({$acceptedSql}, 0)  AS accepted
            ")
            ->first();

        return (object) [
            'proposed' => (float) ($row->proposed ?? 0),
            'completed' => (float) ($row->completed ?? 0),
            'accepted' => (float) ($row->accepted ?? 0),
        ];
    }

    /** Procedure-log rows for the filter. Joins od_procedures only when hygiene is scoped. */
    protected function baseQuery(MetricFilter $filter): Builder
    {
        $q = DB::table('od_procedure_logs as pl')
            ->where('pl.office_id', $filter->officeId)
            ->whereBetween('pl.ProcDate', [$filter->start, $filter->end]);

        if ($filter->hygiene !== null) {
            $q->join('od_procedures as pc', 'pl.CodeNum', '=', 'pc.CodeNum')
                ->where('pc.IsHygiene', $filter->hygiene ? 'true' : 'false');
        }

        if ($filter->clinics) {
            $q->whereIn('pl.ClinicNum', $filter->clinics);
        }

        if ($filter->providers) {
            $q->whereIn('pl.ProvNum', $filter->providers);
        }

        return $q;
    }
}
