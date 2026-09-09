<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class OdHistappointment extends Model
{
    use BelongsToOffice;

    protected $table = 'od_histappointments';

    protected $fillable = [
        'office_id',
        'HistApptNum',
        'HistUserNum',
        'HistDateTStamp',
        'HistApptAction',
        'ApptSource',
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

    public function patient()
    {
        return $this->belongsTo(OdPatient::class, 'PatNum', 'PatNum');
    }

    public function provider()
    {
        return $this->belongsTo(OdProvider::class, 'ProvNum', 'ProvNum');
    }
}
