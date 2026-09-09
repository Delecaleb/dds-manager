<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class OdPayPlanCharge extends Model
{
    use BelongsToOffice;

    protected $primaryKey = 'PayPlanChargeNum';

    public $incrementing = false;

    protected $attributes = [
        'IsDownPayment' => 0,
        'IsOffset' => 0,
        'LinkType' => 0,
        'FKey' => 0,
        'StatementNum' => 0,
        'Interest' => '0',
        'Principal' => '0',
        'ChargeType' => 0,
        'ProcNum' => 0,
        'ClinicNum' => 0,
        'ProvNum' => 0,
    ];

    protected $fillable = [
        'office_id', 'PayPlanChargeNum', 'PayPlanNum', 'Guarantor', 'PatNum', 'ChargeDate', 'Principal', 'Interest', 'Note', 'ProvNum', 'ClinicNum', 'ChargeType', 'ProcNum', 'SecDateTEntry', 'SecDateTEdit', 'StatementNum', 'FKey', 'LinkType', 'IsOffset', 'IsDownPayment',
    ];
}
