<?php

namespace App\Domain\Marketing;

use Illuminate\Contracts\Database\Query\Builder;

/**
 * Turns a location selection into a WHERE clause on a table that carries office_id and
 * clinic_num. One definition, used by every marketing query, so "which rows belong to the
 * selected offices" is answered in exactly one place.
 *
 * Rules:
 *  - a row matches a selected office when its office_id is that office;
 *  - when only some clinics of an office are selected, the row must name one of them,
 *    or name no clinic at all (NULL = the office as a whole, shared by all its clinics);
 *  - when the selection covers every location, rows with no office at all are included
 *    too, so nothing silently disappears from the organization-wide view.
 */
final class LocationScope
{
    /**
     * @param  string  $officeExpr  SQL expression for the office id, e.g. "office_id" or "COALESCE(c.office_id, a.office_id)"
     * @param  string  $clinicExpr  SQL expression for the clinic number
     */
    public static function apply(Builder $query, MarketingFilter $filter, string $officeExpr, string $clinicExpr): void
    {
        $scopes = $filter->scopes();

        if ($scopes === [] && ! $filter->allLocations) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function (Builder $q) use ($scopes, $filter, $officeExpr, $clinicExpr) {
            foreach ($scopes as $officeId => $clinics) {
                $q->orWhere(function (Builder $w) use ($officeId, $clinics, $officeExpr, $clinicExpr) {
                    $w->whereRaw("{$officeExpr} = ?", [$officeId]);

                    if ($clinics !== []) {
                        $marks = implode(', ', array_fill(0, count($clinics), '?'));
                        $w->whereRaw("({$clinicExpr} IS NULL OR {$clinicExpr} IN ({$marks}))", array_values($clinics));
                    }
                });
            }

            if ($filter->allLocations) {
                $q->orWhereRaw("{$officeExpr} IS NULL");
            }
        });
    }
}
