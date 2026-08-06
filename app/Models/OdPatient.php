<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;


/**
 * Enum:PatientStatus
 * Patient: 0
 * NonPatient: 1
 * Inactive: 2
 * Archived: 3 - This status is also used for a merged patient that you're not keeping.
 * Deleted: 4
 * Deceased: 5
 * Prospective: 6- Not an actual patient yet.
 * 6    Gender    tinyint    Enum:PatientGender
 * Male: 0
 * Female: 1
 * Unknown: 2- Required by HIPAA for privacy. Required by ehr to track missing entries. EHR/HL7 known as undifferentiated (UN).
 * Other: 3

 * 7    Position    tinyint    Enum:PatientPosition Marital status would probably be a better name for this column.
 * Single: 0
 * Married: 1
 * Child: 2
 * Widowed: 3
 * Divorced: 4
 */

class OdPatient extends Model
{
    use BelongsToOffice;

    protected $fillable = [
        'office_id',
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

    public function getFullNameAttribute(): string
    {
        return trim(($this->FName ?? '').' '.($this->LName ?? ''));
    }
    public function getFullNameAttribute(): string
    {
        return trim(($this->FName ?? '') . ' ' . ($this->LName ?? ''));
    }

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
