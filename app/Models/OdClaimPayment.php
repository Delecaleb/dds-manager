<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class OdClaimPayment extends Model
{
    use BelongsToOffice;

    protected $primaryKey = 'ClaimPaymentNum';

    protected $attributes = [
        'PayGroup' => 0,
        'PayType' => 0,
        'IsPartial' => 0,
        'DepositNum' => 0,
        'ClinicNum' => 0,
        'SecUserNumEntry' => 0,
    ];

    public $fillable = [
        'office_id',
        'ClaimPaymentNum',
        'CheckDate',
        'CheckAmt',
        'CheckNum',
        'BankBranch',
        'Note',
        'ClinicNum',
        'DepositNum',
        'CarrierName',
        'DateIssued',
        'IsPartial',
        'PayType',
        'SecUserNumEntry',
        'SecDateEntry',
        'SecDateTEdit',
        'PayGroup',
    ];
}
