<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class OdRecall extends Model
{
    use BelongsToOffice;

    protected $fillable = [
        'office_id',
        'RecallNum',
        'PatNum',
        'DateDueCalc',
        'DateDue',
        'DatePrevious',
        'RecallInterval',
        'RecallStatus',
        'Note',
        'IsDisabled',
        'DateTStamp',
        'RecallTypeNum',
        'DisableUntilBalance',
        'DisableUntilDate',
        'DateScheduled',
        'Priority',
        'TimePatternOverride',
    ];

    protected $primaryKey = 'RecallNum';

    public $incrementing = false;
}
