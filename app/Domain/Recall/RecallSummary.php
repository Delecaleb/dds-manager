<?php

namespace App\Domain\Recall;

/** Typed result of RecallService::summary(). */
final class RecallSummary
{
    public function __construct(
        public readonly int $due,
        public readonly int $overdue,
        public readonly int $scheduled,
        public readonly float $scheduledRate,
    ) {}
}
