<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdDeposit extends Model
{
    protected $table = 'od_deposits';

    protected $fillable = [
        'DepositNum',
        'DateDeposit',
        'BankAccountInfo',
        'Amount',
        'Memo',
        'Batch',
        'DepositAccountNum',
        'IsSentToQuickBooksOnline',
    ];
}
