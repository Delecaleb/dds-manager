<?php

namespace App\Domain\Scheduling;

use App\Domain\Support\MetricFilter;
use App\Enums\AppointmentStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for scheduling / appointment metrics.
 *
 * Multi-office & per-provider aware: every method filters by MetricFilter's clinics[] and
 * providers[]. Appointment status uses the App\Enums\AppointmentStatus enum (one definition).
 */
class SchedulingService
{
    /** Appointments occurring in the period (by AptDateTime). */
    public function appointmentCount(MetricFilter $filter): int
    {
        return (int) $this->appointments($filter)->count();
    }

    /** Completed appointments in the period. */
    public function completedCount(MetricFilter $filter): int
    {
        return (int) $this->appointments($filter)
            ->where('AptStatus', AppointmentStatus::Complete->value)
            ->count();
    }

    /** Broken/missed appointments in the period. */
    public function brokenCount(MetricFilter $filter): int
    {
        return (int) $this->appointments($filter)
            ->where('AptStatus', AppointmentStatus::Broken->value)
            ->count();
    }

    /** Broken appointments as a % of all appointments in the period. */
    public function brokenRate(MetricFilter $filter): float
    {
        $total = $this->appointmentCount($filter);

        return $total > 0 ? round($this->brokenCount($filter) / $total * 100, 2) : 0.0;
    }

    /**
     * Reappointment rate: of completed appointments in the period, the share that left with a
     * next appointment booked (NextAptNum set). Mirrors the existing KPI definition.
     */
    public function reappointmentRate(MetricFilter $filter): float
    {
        $row = $this->appointments($filter)
            ->where('AptStatus', AppointmentStatus::Complete->value)
            ->selectRaw("
                COUNT(*) AS total,
                COUNT(CASE WHEN NextAptNum IS NOT NULL AND NextAptNum != '0' THEN 1 END) AS with_next
            ")
            ->first();

        return ($row->total ?? 0) > 0 ? round($row->with_next / $row->total * 100, 2) : 0.0;
    }

    /**
     * Value of production sitting on SCHEDULED appointments in the period — the fee total of
     * procedures attached to not-yet-completed scheduled appointments (forward-looking book).
     */
    public function scheduledProduction(MetricFilter $filter): float
    {
        $q = DB::table('od_appointments as a')
            ->join('od_procedure_logs as pl', 'pl.AptNum', '=', 'a.AptNum')
            ->where('a.AptStatus', AppointmentStatus::Scheduled->value)
            ->whereBetween('a.AptDateTime', [$filter->start, $filter->end]);
        $this->applyScopes($q, $filter, 'a');

        return (float) $q->sum('pl.ProcFee');
    }

    public function summary(MetricFilter $filter): SchedulingSummary
    {
        $appts = $this->appointmentCount($filter);
        $broken = $this->brokenCount($filter);

        return new SchedulingSummary(
            appointments: $appts,
            completed: $this->completedCount($filter),
            broken: $broken,
            brokenRate: $appts > 0 ? round($broken / $appts * 100, 2) : 0.0,
            reappointmentRate: $this->reappointmentRate($filter),
            scheduledProduction: round($this->scheduledProduction($filter), 2),
        );
    }

    /** Base appointment query for the period, scoped to the filter's clinics & providers. */
    protected function appointments(MetricFilter $filter): Builder
    {
        $q = DB::table('od_appointments as a')
            ->whereBetween('a.AptDateTime', [$filter->start, $filter->end]);
        $this->applyScopes($q, $filter, 'a');

        return $q;
    }

    protected function applyScopes(Builder $q, MetricFilter $filter, string $alias): void
    {
        if ($filter->clinics) {
            $q->whereIn("{$alias}.ClinicNum", $filter->clinics);
        }
        if ($filter->providers) {
            $q->whereIn("{$alias}.ProvNum", $filter->providers);
        }
    }
}
