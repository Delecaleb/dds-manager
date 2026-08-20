<?php

namespace App\Models;

use App\Helpers\MetricDefinitions;
use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * ApptStatus Enum mappings in OpenDental:
 * 0 - None: No appointment should ever have this status.
 * 1 - Scheduled: Shows as a regularly scheduled appointment.
 * 2 - Complete: Shows greyed out.
 * 3 - UnschedList: Only shows on unscheduled list.
 * 4 - ASAP: Deprecated in 17.4.1. Use Appointment.Priority instead.
 * 5 - Broken: Shows with a big X on it.
 * 6 - Planned: Planned appointment. Only shows in Chart module.
 * 7 - PtNote: Patient "post-it" note on the schedule.
 * 8 - PtNoteCompleted: Patient "post-it" note completed.
 */
class OdAppointment extends Model
{
    use BelongsToOffice;

    protected $fillable = [
        'office_id',
        'AptNum',
        'PatNum',
        'AptStatus',
        'Pattern',
        'Confirmed',
        'TimeLocked',
        'Op',
        'Note',
        'ProvNum',
        'ProvHyg',
        'AptDateTime',
        'NextAptNum',
        'UnschedStatus',
        'IsNewPatient',
        'ProcDescript',
        'Assistant',
        'ClinicNum',
        'IsHygiene',
        'DateTStamp',
        'DateTimeArrived',
        'DateTimeSeated',
        'DateTimeDismissed',
        'InsPlan1',
        'InsPlan2',
        'DateTimeAskedToArrive',
        'ProcsColored',
        'ColorOverride',
        'AppointmentTypeNum',
        'SecUserNumEntry',
        'SecDateTEntry',
        'Priority',
        'ProvBarText',
        'PatternSecondary',
        'SecurityHash',
        'ItemOrderPlanned',
        'IsMirrored',
    ];

    protected $primaryKey = 'AptNum';

    public $incrementing = false;

    public function scopeScheduled($query)
    {
        return $query->whereIn('AptStatus', [1, 2]);
    }

    public function scopeInDateRange($query, $start, $end)
    {
        $startDate = substr($start, 0, 10);
        $endDate = substr($end, 0, 10);

        return $query->whereRaw("DATE(REPLACE(AptDateTime, 'T', ' ')) BETWEEN ? AND ?", [$startDate, $endDate]);
    }

    public function scheduledPatients($start, $end)
    {
        return (int) $this->inDateRange($start, $end)
            ->scheduled()
            ->selectRaw(MetricDefinitions::scheduledPatients('cnt'))
            ->value('cnt');
    }

    public function newPatientsScheduled($start, $end)
    {
        $startDate = substr($start, 0, 10).' 00:00:00';
        $endDate = substr($end, 0, 10).' 23:59:59';
        $startDay = substr($start, 0, 10);

        $firstApts = DB::table('od_appointments')
            ->select('PatNum', DB::raw('MIN(AptDateTime) as first_apt'))
            ->whereIn('AptStatus', [1, 2])
            ->groupBy('PatNum');

        return (int) DB::table('od_patients as p')
            ->joinSub($firstApts, 'fa', 'p.PatNum', '=', 'fa.PatNum')
            ->whereBetween('fa.first_apt', [$startDate, $endDate])
            ->whereNotIn('p.PatNum', function ($query) use ($startDay) {
                $query->select('PatNum')
                    ->from('od_procedure_logs')
                    ->where('ProcDate', '<', $startDay)
                    ->whereIn('ProcStatus', ['C', '2', 'D']);
            })
            ->whereNotIn('p.PatNum', [21216, 21231, 21254])
            ->count('p.PatNum');
    }

    public function patient()
    {
        return $this->belongsTo(OdPatient::class, 'PatNum', 'PatNum');
    }

    public function provider()
    {
        return $this->belongsTo(OdProvider::class, 'ProvNum', 'ProvNum');
    }

    public function procedureLogs()
    {
        return $this->hasMany(OdProcedureLog::class, 'AptNum', 'AptNum');
    }

    public function getDurationMinutesAttribute(): int
    {
        $pattern = $this->Pattern ?? '';

        return strlen($pattern) > 0 ? strlen($pattern) * 5 : 60;
    }
}
