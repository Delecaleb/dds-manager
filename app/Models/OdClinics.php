<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdClinics extends Model
{
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
