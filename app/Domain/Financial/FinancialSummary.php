<?php

namespace App\Domain\Financial;

/** Typed result of FinancialService::summary(). */
final class FinancialSummary
{
    public function __construct(
        public readonly float $collections,
        public readonly float $netProduction,
        public readonly float $collectionRate,
        public readonly float $accountsReceivable,
    ) {}
}
