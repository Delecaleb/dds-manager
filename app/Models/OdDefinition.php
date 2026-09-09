<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class OdDefinition extends Model
{
    use BelongsToOffice;

    protected $primaryKey = 'DefNum';

    public $incrementing = false;

    protected $fillable = [
        'office_id',
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
