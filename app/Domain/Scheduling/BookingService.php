<?php

namespace App\Domain\Scheduling;

use App\Domain\Support\MetricFilter;
use App\Enums\AppointmentStatus;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Single source for reading individual appointments ("bookings") from the local sync, as the
 * Developer API serves them. Metrics about appointments stay in SchedulingService.
 *
 * Every read is scoped to the filter's office (and clinics) explicitly, never through the
 * session's active office, because API requests have no session.
 */
class BookingService
{
    /** OpenDental stores an appointment's Pattern in 5-minute slots, whatever the display setting. */
    public const MINUTES_PER_PATTERN_SLOT = 5;

    /** Bookings in the period, in appointment order, one cursor page at a time. */
    public function paginate(BookingQuery $query, int $perPage): CursorPaginator
    {
        $filter = $query->filter;

        $builder = $this->base($query)
            ->whereBetween('a.AptDateTime', [$filter->start.' 00:00:00', $filter->end.' 23:59:59'])
            ->orderBy('a.AptDateTime')
            ->orderBy('a.AptNum');

        if ($query->statuses !== []) {
            // AptStatus is a VARCHAR locally; compare as strings so the index stays usable.
            $builder->whereIn('a.AptStatus', array_map(fn (AppointmentStatus $s) => (string) $s->value, $query->statuses));
        }

        if ($query->updatedSince !== null) {
            $builder->where('a.DateTStamp', '>', $query->updatedSince);
        }

        return $builder->cursorPaginate($perPage);
    }

    /** One booking by its OpenDental AptNum, or null when it is not in this location. */
    public function find(BookingQuery $query, int $aptNum): ?object
    {
        return $this->base($query)->where('a.AptNum', $aptNum)->first();
    }

    /** The most recent OpenDental change the local copy holds for the location. */
    public function latestChange(MetricFilter $filter): ?string
    {
        $builder = DB::table('od_appointments as a');
        $this->scope($builder, $filter);

        $latest = $builder->max('a.DateTStamp');

        return $latest !== null ? (string) $latest : null;
    }

    public static function durationMinutes(?string $pattern): ?int
    {
        return filled($pattern) ? strlen((string) $pattern) * self::MINUTES_PER_PATTERN_SLOT : null;
    }

    private function base(BookingQuery $query): Builder
    {
        $builder = DB::table('od_appointments as a')
            ->leftJoin('od_providers as pr', function ($join) {
                $join->on('pr.ProvNum', '=', 'a.ProvNum')->on('pr.office_id', '=', 'a.office_id');
            })
            ->select([
                'a.AptNum', 'a.AptDateTime', 'a.Pattern', 'a.AptStatus', 'a.Op', 'a.ProvNum', 'a.ProvHyg',
                'a.ClinicNum', 'a.IsNewPatient', 'a.IsHygiene', 'a.ProcDescript', 'a.PatNum',
                'a.DateTStamp', 'a.SecDateTEntry',
                'pr.Abbr as provider_abbr', 'pr.FName as provider_first_name', 'pr.LName as provider_last_name',
            ]);

        if ($query->withPatientContact) {
            $builder->leftJoin('od_patients as p', function ($join) {
                $join->on('p.PatNum', '=', 'a.PatNum')->on('p.office_id', '=', 'a.office_id');
            })->addSelect([
                'p.FName as patient_first_name', 'p.LName as patient_last_name', 'p.Preferred as patient_preferred_name',
                'p.Email as patient_email', 'p.WirelessPhone as patient_mobile_phone', 'p.HmPhone as patient_home_phone',
                'p.WkPhone as patient_work_phone', 'p.Address as patient_address', 'p.Address2 as patient_address2',
                'p.City as patient_city', 'p.State as patient_state', 'p.Zip as patient_zip',
            ]);
        }

        $this->scope($builder, $query->filter);

        return $builder;
    }

    private function scope(Builder $builder, MetricFilter $filter): void
    {
        $builder->where('a.office_id', $filter->officeId);

        if ($filter->clinics !== []) {
            $builder->whereIn('a.ClinicNum', $filter->clinics);
        }

        if ($filter->providers !== []) {
            $builder->whereIn('a.ProvNum', $filter->providers);
        }
    }
}
