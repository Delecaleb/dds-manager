<?php

namespace App\Services\OpenDental;

use Illuminate\Support\Facades\DB;

class FinancialAnalyticsService
{
    public function filterAnalysis($start, $end)
    {
        $gross = DB::table('od_procedure_logs')
            ->where('ProcStatus', 'C')
            ->whereBetween('ProcDate', [$start, $end])
            ->sum('ProcFee');

        $adjustments = DB::table('od_adjustments')
            ->whereBetween('AdjDate', [$start, $end])
            ->sum('AdjAmt');

        $collections = DB::table('od_pay_splits')
            ->whereBetween('DatePay', [$start, $end])
            ->sum('SplitAmt');

        $writeoffs = DB::table('od_claim_procs')
            ->whereBetween('ProcDate', [$start, $end])
            ->sum('WriteOff');

        $net = $gross - abs($adjustments) - abs($writeoffs);

        return [
            'gross_production'  => round($gross, 2),
            'net_production'    => round($net, 2),
            'adjustments'       => round($adjustments, 2),
            'writeoffs'         => round($writeoffs, 2),
            'collections'       => round($collections, 2),
            'adjustment_rate'   => $gross > 0 ? round((abs($adjustments) / $gross) * 100, 2) : 0,
            'collection_rate'   => $gross > 0 ? round(($collections / $gross) * 100, 2) : 0,
        ];
    }
}
