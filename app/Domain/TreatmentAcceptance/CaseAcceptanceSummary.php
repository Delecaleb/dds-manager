<?php

namespace App\Domain\TreatmentAcceptance;

/**
 * Typed result of TreatmentAcceptanceService::summary().
 *
 * All figures are dollar totals except $rate, which is a percentage.
 */
final class CaseAcceptanceSummary
{
    public function __construct(
        public readonly float $proposed,   // SUM ProcFee of treatment-planned procedures (D2)
        public readonly float $completed,  // SUM ProcFee of completed procedures (D1)
        public readonly float $accepted,   // SUM ProcFee of TP procedures with an appointment (D5)
        public readonly float $rate,       // (completed + accepted) / proposed * 100 (D4-A)
    ) {}
}
