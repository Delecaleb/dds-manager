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

<<<<<<< Updated upstream
=======
    public function scopeScheduled($query)
    {
        return $query->whereIn('AptStatus', [1, 4]);
    }

    public function scopeInDateRange($query, $start, $end)
    {
        $startDate = substr($start, 0, 10);
        $endDate = substr($end, 0, 10);

        return $query->whereRaw("DATE(REPLACE(AptDateTime, 'T', ' ')) BETWEEN ? AND ?", [$startDate, $endDate]);
    }

>>>>>>> Stashed changes
    public function scheduledPatients($start, $end)
    {
        return OdAppointment::whereBetween('AptDateTime', [$start, $end])
            ->where("AptStatus", "1")
            ->distinct('PatNum')
            ->count('PatNum');
    }

    public function newPatientsScheduled($start, $end)
    {
        return OdAppointment::whereBetween('AptDateTime', [$start, $end])
            ->where('IsNewPatient', true)
            ->distinct('PatNum')
            ->count('PatNum');
    }
}
