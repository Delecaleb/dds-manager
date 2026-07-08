<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdPayment extends Model
{
    protected $fillable = [
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
