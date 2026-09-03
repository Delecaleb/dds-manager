<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class OdClaimPayment extends Model
{
    use BelongsToOffice;

    protected $primaryKey = 'ClaimPaymentNum';

    public $incrementing = false;

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
