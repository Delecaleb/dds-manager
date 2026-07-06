<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdAppointment extends Model
{
    protected $fillable = [
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

    public function scheduledPatients($start, $end)
    {
        return OdAppointment::whereBetween('AptDateTime', [$start, $end])
            ->where('AptStatus', 'Scheduled')
            ->distinct('PatNum')
            ->count('PatNum');
    }

    public function newPatientsScheduled($start, $end)
    {
        return OdAppointment::whereBetween('AptDateTime', [$start, $end])
            ->where('IsNewPatient', 'true')
            ->distinct('PatNum')
            ->count('PatNum');
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
        return strlen($pattern) > 0 ? strlen($pattern) * 10 : 60;
    }
}
