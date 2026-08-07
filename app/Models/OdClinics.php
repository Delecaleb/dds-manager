<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdClinics extends Model
{
    protected $primaryKey = 'ClinicNum';

    public $incrementing = false;

    protected $fillable = [
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
