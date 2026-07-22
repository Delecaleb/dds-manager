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
     * @return array{0:string,1:string} [start, end] as 'Y-m-d'
     */
    public static function shiftYear(string $start, string $end): array
    {
        return [
            Carbon::parse($start)->subYear()->toDateString(),
            Carbon::parse($end)->subYear()->toDateString(),
        ];
    }
}
