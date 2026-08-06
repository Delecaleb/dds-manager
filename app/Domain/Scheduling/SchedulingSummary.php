<?php

namespace App\Domain\Scheduling;

/** Typed result of SchedulingService::summary(). */
final class SchedulingSummary
{
    public function __construct(
        public readonly int $appointments,
        public readonly int $completed,
        public readonly int $broken,
        public readonly float $brokenRate,
        public readonly float $reappointmentRate,
        public readonly float $scheduledProduction,
    ) {}
}
