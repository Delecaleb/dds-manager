<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdDefinition extends Model
{
    protected $primaryKey = 'DefNum';

    public $incrementing = false;

    protected $fillable = [
        'DefNum',
        'Category',
        'ItemOrder',
        'ItemName',
        'ItemValue',
        'ItemColor',
        'IsHidden',
        'Supp',
    ];
}
