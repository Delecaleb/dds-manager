<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdRecallType extends Model
{
    protected $fillable = [
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
