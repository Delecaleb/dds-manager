<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class OdRecallType extends Model
{
    use BelongsToOffice;

    protected $fillable = [
        'office_id',
        'RecallTypeNum',
        'Description',
        'DefaultInterval',
        'TimePattern',
        'Procedures',
        'AppendToSpecial',
    ];

    protected $primaryKey = 'RecallTypeNum';

    public $incrementing = false;
}
