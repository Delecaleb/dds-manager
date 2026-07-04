<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdPayPlanCharge extends Model
{
    protected $fillable = [
        'PayPlanChargeNum', 'PayPlanNum', 'Guarantor', 'PatNum', 'ChargeDate', 'Principal', 'Interest', 'Note', 'ProvNum', 'ClinicNum', 'ChargeType', 'ProcNum', 'SecDateTEntry', 'SecDateTEdit', 'StatementNum', 'FKey', 'LinkType', 'IsOffset', 'IsDownPayment'
    ];
}
