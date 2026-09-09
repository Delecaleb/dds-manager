<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class OdClinics extends Model
{
    use BelongsToOffice;

    protected $primaryKey = 'ClinicNum';

    public $incrementing = false;

    protected $fillable = [
        'office_id',
        'ClinicNum',
        'Abbr',
        'ItemOrder',
        'ItemName',
        'ItemValue',
        'ItemColor',
        'IsHidden',
        'Supp',
    ];
}
