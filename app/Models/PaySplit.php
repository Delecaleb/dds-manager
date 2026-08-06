<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaySplit extends Model
{
    protected $table = 'od_pay_splits';

    protected $fillable = [
        'SplitNum', 
        'SplitAmt', 
        'PatNum', 
        'ProcDate', 
        'PayNum', 
        'IsDiscount', 
        'DiscountType', 
        'ProvNum', 
        'PayPlanNum', 
        'DatePay', 
        'ProcNum', 
        'DateEntry', 
        'UnearnedType', 
        'ClinicNum', 
        'SecUserNumEntry', 
        'SecDateTEdit', 
        'FSplitNum', 'AdjNum', 
        'PayPlanChargeNum', 
        'PayPlanDebitType', 
        'SecurityHash',
    ];
}
