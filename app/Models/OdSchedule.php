<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdSchedule extends Model
{
    //this model handles doctors schedule
    protected $fillable=[
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
