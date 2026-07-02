<?php

namespace App\Services\OpenDental;

use Illuminate\Support\Facades\DB;


class FinancialAnalyticsService
{


    public function filterAnalysis($start, $end)
    {


        $gross = DB::table('od_procedure_logs')

            ->where('ProcStatus', 2)

            ->whereBetween(
                'ProcDate',
                [$start, $end]
            )

            ->sum('ProcFee');




        $adjustments = DB::table('adjustments')

            ->whereBetween(
                'AdjDate',
                [$start, $end]
            )

            ->sum('AdjAmt');




        $collections = DB::table('pay_splits')

            ->whereBetween(
                'SplitDate',
                [$start, $end]
            )

            ->sum('SplitAmt');

        $writeoffs = DB::table('claim_procs')

            ->whereBetween(
                'ProcDate',
                [$start, $end]
            )
            ->sum('WriteOff');
        return [
            'gross_production' => round($gross, 2),
            'writeoffs' => round($writeoffs, 2),
            'adjustments' => round($adjustments, 2),
            'net_production' =>
                round(
                    $gross - $writeoffs - $adjustments,
                    2
                ),
            'collections' => round($collections, 2),
            'adjustment_rate' => $gross > 0 ? round(($adjustments / $gross) * 100, 2) : 0
        ];
    }
}