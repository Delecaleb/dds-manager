<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class OdPayPlanCharge extends Model
{
    use BelongsToOffice;

    protected $primaryKey = 'PayPlanChargeNum';

    public $incrementing = false;

    protected $fillable = [
        'office_id', 'PayPlanChargeNum', 'PayPlanNum', 'Guarantor', 'PatNum', 'ChargeDate', 'Principal', 'Interest', 'Note', 'ProvNum', 'ClinicNum', 'ChargeType', 'ProcNum', 'SecDateTEntry', 'SecDateTEdit', 'StatementNum', 'FKey', 'LinkType', 'IsOffset', 'IsDownPayment',
    ];
}
