<?php

namespace App\Domain\Support;

use Carbon\Carbon;

/**
 * Period math shared by every domain service (last-year comparisons, etc.).
 *
 * Replaces the private OperationsAnalyticsService::shiftYear() and the inline
 * Carbon->subYear() copies in Dashboard/FrontOffice controllers.
 *
 * @see refractor-blueprint/03-canonical-definitions.md
 */
final class DateRange
{
    /**
     * Shift a [start, end] range back exactly one year.
     *
     * Uses the NoOverflow variant so Feb 29 clamps to Feb 28 of the prior year
     * rather than rolling forward to Mar 1. Plain subYear() on 2024-02-29 gives
     * 2023-03-01, which would pull a March day into a February comparison and
     * make every leap-year "vs last year" figure wrong.
     *
     * @return array{0:string,1:string} [start, end] as 'Y-m-d'
     */
    public static function shiftYear(string $start, string $end): array
    {
        return [
            Carbon::parse($start)->subYearNoOverflow()->toDateString(),
            Carbon::parse($end)->subYearNoOverflow()->toDateString(),
        ];
    }
}
