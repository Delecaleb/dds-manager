<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdPatient extends Model
{
    protected $fillable = [
        'PatNum',
        'FName',
        'LName',
        'Email',
        'Birthdate',
        'MiddleI',
        'Preferred',
        'PatStatus',
        'Gender',
        'Position',
        'SSN',
        'Address',
        'Address2',
        'City',
        'State',
        'Zip',
        'HmPhone',
        'WkPhone',
        'WirelessPhone',
        'Guarantor',
        'CreditType',
        'EstBalance',
        'Salutation',
        'PriProv',
        'SecProv',
        'FeeSched',
        'BillingType',
        'ImageFolder',
        'AddrNote',
        'FamFinUrgNote',
        'MedUrgNote',
        'ApptModNote',
        'Fac',
        'StudentStatus',
        'SchoolName',
        'ChartNumber',
        'MedicaidID',
        'Bal_0_30',
        'Bal_31_60',
        'Bal_61_90',
        'BalOver90',
        'InsEst',
        'BalTotal',
        'EmployerNum',
        'EmploymentNote',
        'County',
        'GradeLevel',
    ];


    protected $primaryKey = 'PatNum';

    public $incrementing = false;


    public function appointments()
    {
        return $this->hasMany(
            OdAppointment::class,
            'PatNum',
            'PatNum'
        );
    }


    public function proceduresLogs()
    {
        return $this->hasMany(
            OdProcedureLog::class,
            'PatNum',
            'PatNum'
        );
    }
}
