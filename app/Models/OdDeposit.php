<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class OdDeposit extends Model
{
    use BelongsToOffice;

    protected $table = 'od_deposits';

    protected $primaryKey = 'DepositNum';

    public $incrementing = false;

    protected $fillable = [
        'office_id',
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
