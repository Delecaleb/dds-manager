<?php

namespace App\Domain\Financial;

use App\Domain\Production\ProductionService;
use App\Domain\Support\MetricFilter;
use App\Services\OpenDental\AgingCalculationService;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for financial roll-ups: collections, adjustments breakdown, and
 * accounts-receivable aging. Composes existing sources rather than duplicating them —
 * collections/net come from ProductionService (D3), AR aging from AgingCalculationService.
 */
class FinancialService
{
    public function __construct(
        private readonly ProductionService $production,
        private readonly AgingCalculationService $aging,
    ) {}

    /** Payments collected in the period (SUM SplitAmt). */
    public function collections(MetricFilter $filter): float
    {
        return $this->production->collection($filter);
    }

    /** Adjustments in the period grouped by adjustment type. @return array<int,array{label:string,value:float}> */
    public function adjustmentsBreakdown(MetricFilter $filter): array
    {
        $q = DB::table('od_adjustments as a')
            ->leftJoin('od_definitions as d', function ($join) use ($filter) {
                $join->on('a.AdjType', '=', 'd.DefNum')
                    ->where('d.office_id', '=', $filter->officeId);
            })
            ->where('a.office_id', $filter->officeId)
            ->whereBetween('a.AdjDate', [$filter->start, $filter->end]);
        if ($filter->clinics) {
            $q->whereIn('a.ClinicNum', $filter->clinics);
        }

        return $q->selectRaw("COALESCE(d.DefNum, 0) as DefNum, COALESCE(d.ItemName, 'Adjustment') AS label, SUM(a.AdjAmt) AS value")
            ->groupBy('d.DefNum', 'd.ItemName')
            ->orderByRaw('ABS(SUM(a.AdjAmt)) DESC')
            ->get()
            ->map(fn ($r) => ['label' => $r->label, 'value' => round((float) $r->value, 2)])
            ->all();
    }

    /**
     * Accounts-receivable aging buckets as of the period end (delegates to the existing
     * transaction-level aging service — wrap, don't duplicate).
     */
    public function accountsReceivable(MetricFilter $filter): array
    {
        return $this->aging->totals($filter->end, null, true, 'guarantor');
    }

    /** Total outstanding AR (grand total across aging buckets) as of period end. */
    public function accountsReceivableTotal(MetricFilter $filter): float
    {
        $ar = $this->accountsReceivable($filter);

        return round((float) ($ar['grand_total'] ?? 0), 2);
    }

    public function summary(MetricFilter $filter): FinancialSummary
    {
        $collections = $this->collections($filter);
        $net = $this->production->netProduction($filter);

        return new FinancialSummary(
            collections: round($collections, 2),
            netProduction: $net,
            collectionRate: $net > 0 ? round($collections / $net * 100, 2) : 0.0,
            accountsReceivable: $this->accountsReceivableTotal($filter),
        );
    }
}
