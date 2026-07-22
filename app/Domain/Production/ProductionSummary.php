<?php

namespace App\Domain\Production;

/**
 * Typed result of ProductionService::summary().
 * Dollar figures except the counts and $collectionRate (a percentage).
 */
final class ProductionSummary
{
    public function __construct(
        public readonly float $gross,           // completed ProcFee (before adj/writeoff)
        public readonly float $adjustments,     // SUM(AdjAmt), signed
        public readonly float $writeOffs,       // SUM(WriteOff), positive magnitude
        public readonly float $net,             // gross + adjustments - writeOffs  (blueprint D3)
        public readonly float $collection,      // SUM(SplitAmt)
        public readonly float $collectionRate,  // collection / net * 100
        public readonly int $patientVisits,   // distinct patient × day
        public readonly int $procedures,      // completed procedure count
        public readonly int $workingDays,     // distinct production days
        public readonly float $productionPerDay,
        public readonly float $productionPerVisit,
        public readonly float $productionPerProcedure,
    ) {}
}
