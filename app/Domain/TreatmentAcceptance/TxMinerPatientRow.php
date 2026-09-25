<?php

namespace App\Domain\TreatmentAcceptance;

/**
 * One patient in the Tx Miner month breakdown.
 *
 * @see TxMinerPatientBreakdownService
 */
final class TxMinerPatientRow
{
    /**
     * @param  array<int, array{num: int, name: string, abbr: string}>  $providers
     * @param  string[]  $datesPlanned  'Y-m-d', ascending
     * @param  string[]  $datesCreated  'Y-m-d', ascending
     */
    public function __construct(
        public readonly int $officeId,
        public readonly int $patNum,
        public readonly string $name,
        public readonly string $chartNumber,
        public readonly string $homePhone,
        public readonly string $wirelessPhone,
        public readonly string $email,
        public readonly bool $isNew,
        public readonly float $txScheduled,
        public readonly float $txUnscheduled,
        public readonly float $completedTx,
        public readonly ?string $nextVisit,
        public readonly ?string $nextHygieneVisit,
        public readonly string $status,
        public readonly array $providers,
        public readonly ?string $insurance,
        public readonly array $datesPlanned,
        public readonly array $datesCreated,
    ) {}
}
