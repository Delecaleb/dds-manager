<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class OdPayment extends Model
{
    use BelongsToOffice;

    protected $primaryKey = 'PayNum';

    public $incrementing = false;

    protected $fillable = [
        'office_id',
        'PayNum',
        'PayType',
        'PayDate',
        'PayAmt',
        'CheckNum',
        'BankBranch',
        'PayNote',
        'IsSplit',
        'PatNum',
        'ClinicNum',
        'DateEntry',
        'DepositNum',
        'Receipt',
        'IsRecurringCC',
        'SecUserNumEntry',
        'SecDateTEdit',
        'PaymentSource',
        'ProcessStatus',
        'RecurringChargeDate',
        'ExternalId',
        'PaymentStatus',
        'IsCcCompleted',
        'MerchantFee',
    ];
}
