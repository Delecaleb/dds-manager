<?php

namespace App\Domain\Support;

use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

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

    /**
     * "The column's calendar day is within [start, end]", for ->tap() on a query builder.
     *
     * On MySQL the od_* date columns are DATE / DATETIME, so this is a plain range that can
     * use an index (`col >= start AND col < end + 1 day` — exact for both types). Wrapping the
     * column, e.g. DATE(REPLACE(col, 'T', ' ')), gives the same rows but forces a full scan.
     * SQLite stores dates as text and test fixtures may carry ISO 'T' strings, so it keeps the
     * normalising expression.
     *
     * @return Closure(QueryBuilder|EloquentBuilder): void
     */
    public static function within(string $column, string $start, string $end): Closure
    {
        return function (QueryBuilder|EloquentBuilder $q) use ($column, $start, $end): void {
            if (DB::connection()->getDriverName() === 'sqlite') {
                $q->whereRaw("DATE(REPLACE({$column}, 'T', ' ')) BETWEEN ? AND ?", [$start, $end]);

                return;
            }

            $q->where($column, '>=', Carbon::parse($start)->toDateString())
                ->where($column, '<', Carbon::parse($end)->addDay()->toDateString());
        };
    }

    /**
     * within() for hand-written SQL: the predicate and its two bindings.
     *
     * @return array{0: string, 1: array{0: string, 1: string}}
     */
    public static function withinSql(string $column, string $start, string $end): array
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return ["DATE(REPLACE({$column}, 'T', ' ')) BETWEEN ? AND ?", [$start, $end]];
        }

        return ["{$column} >= ? AND {$column} < ?", [Carbon::parse($start)->toDateString(), Carbon::parse($end)->addDay()->toDateString()]];
    }
}
