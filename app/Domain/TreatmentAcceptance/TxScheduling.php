<?php

namespace App\Domain\TreatmentAcceptance;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Query\Builder;

/**
 * The single home of the Tx Miner "Tx Scheduled" vs "Tx Unscheduled" split.
 *
 * A treatment-planned procedure is SCHEDULED only while the appointment it is
 * attached to is still booked (AptStatus = Scheduled). OpenDental keeps
 * procedurelog.AptNum pointing at an appointment after it is broken, so AptNum
 * alone is not a signal: procedures on Broken / Unscheduled-list / Complete
 * appointments, or on an AptNum that no longer exists, are UNSCHEDULED — matching
 * Jarvis's Tx Miner Breakdown (8 Mile, Sep 2025: 6 patients, $5,247.40 on broken
 * appointments reported as Unscheduled).
 *
 * Usage: call joinScheduledAppointment() once on a query aliased `pl`, then use
 * scheduledSql()/unscheduledSql() in SUM(CASE ...) or where clauses, or read the
 * selected column via isScheduled() for per-row code.
 */
final class TxScheduling
{
    public const APPOINTMENT_ALIAS = 'tx_sched_apt';

    /**
     * LEFT JOIN the procedure's appointment, matching only while it is still Scheduled.
     * (office_id, AptNum) is unique on od_appointments, so this never duplicates rows.
     */
    public static function joinScheduledAppointment(Builder $query, string $procAlias = 'pl'): Builder
    {
        $apt = self::APPOINTMENT_ALIAS;

        return $query->leftJoin("od_appointments as {$apt}", function ($join) use ($apt, $procAlias) {
            $join->on("{$apt}.office_id", '=', "{$procAlias}.office_id")
                ->on("{$apt}.AptNum", '=', "{$procAlias}.AptNum")
                ->where("{$apt}.AptStatus", '=', (string) AppointmentStatus::Scheduled->value);
        });
    }

    /** SQL condition: the procedure sits on a still-scheduled appointment. Requires the join. */
    public static function scheduledSql(): string
    {
        return self::APPOINTMENT_ALIAS.'.AptNum IS NOT NULL';
    }

    /** SQL condition: the procedure has no still-scheduled appointment. Requires the join. */
    public static function unscheduledSql(): string
    {
        return self::APPOINTMENT_ALIAS.'.AptNum IS NULL';
    }

    /** Column to select so per-row code can call isScheduled(). */
    public static function scheduledAptColumn(string $as = 'scheduled_apt_num'): string
    {
        return self::APPOINTMENT_ALIAS.".AptNum as {$as}";
    }

    public static function isScheduled(object $row, string $column = 'scheduled_apt_num'): bool
    {
        return ($row->{$column} ?? null) !== null;
    }
}
