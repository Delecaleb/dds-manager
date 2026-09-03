<?php

namespace App\Services\OpenDental;

use App\Domain\Production\ProductionService;
use App\Domain\Support\MetricFilter;

class FinancialAnalyticsService
{
    public function __construct(
        private readonly ProductionService $production,
    ) {}

    public function filterAnalysis($start, $end, ?int $officeId = null)
    {
        $s = $this->production->summary(new MetricFilter($start, $end, [], [], false, $officeId));

        return [
            'gross_production' => $s->gross,
            'net_production' => $s->net,
            'adjustments' => $s->adjustments,
            'adjustment' => $s->adjustments,
            'writeoffs' => $s->writeOffs,
            'collections' => $s->collection,
            'collection' => $s->collection,
            // Rates here are expressed over GROSS (not net) — preserved as-is.
            'adjustment_rate' => $s->gross > 0 ? round((abs($s->adjustments) / $s->gross) * 100, 2) : 0,
            'collection_rate' => $s->gross > 0 ? round(($s->collection / $s->gross) * 100, 2) : 0,
        ];
    }
}
