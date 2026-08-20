<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdRecall extends Model
{
    protected $fillable = [
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
