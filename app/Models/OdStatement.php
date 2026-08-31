<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class OdStatement extends Model
{
    use BelongsToOffice;

    protected $table = 'od_statements';

    protected $primaryKey = 'StatementNum';

    public $incrementing = false;

    protected $fillable = [
        'office_id',
        'StatementNum',
        'PatNum',
        'DateSent',
        'DateRangeFrom',
        'DateRangeTo',
        'Note',
        'NoteBold',
        'Mode_',
        'HidePayment',
        'SinglePatient',
        'Intermingled',
        'IsSent',
        'DocNum',
        'DateTStamp',
        'IsReceipt',
        'IsInvoice',
        'IsInvoiceCopy',
        'EmailSubject',
        'EmailBody',
        'SuperFamily',
        'IsBalValid',
        'InsEst',
        'BalTotal',
        'StatementType',
        'ShortGUID',
        'StatementShortURL',
        'StatementURL',
        'SmsSendStatus',
        'LimitedCustomFamily',
    ];
}
