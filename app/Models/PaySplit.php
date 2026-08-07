<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class PaySplit extends Model
{
    use BelongsToOffice;

    protected $table = 'od_pay_splits';

    protected $primaryKey = 'SplitNum';

    public $incrementing = false;

    protected $fillable = [
        'office_id',
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
