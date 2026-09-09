<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class OdSchedule extends Model
{
    use BelongsToOffice;

    protected $primaryKey = 'ScheduleNum';

    public $incrementing = false;

    // this model handles doctors schedule
    protected $fillable = [
        'office_id',
        'ScheduleNum',
        'SchedDate',
        'StartTime',
        'StopTime',
        'SchedType',
        'ProvNum',
        'BlockoutType',
        'Note',
        'Status',
        'EmployeeNum',
        'DateTStamp',
        'ClinicNum',
    ];
}
