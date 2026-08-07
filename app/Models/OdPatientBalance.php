<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdPatientBalance extends Model
{
    protected $primaryKey = 'PatNum';

    public $incrementing = false;

    protected $fillable = [

        'PatNum',

        'Bal_0_30',
        'Bal_31_60',
        'Bal_61_90',
        'BalOver90',

        'Total',

        'InsEst',

        'EstBal',

        'PatEstBal',

        'Unearned',

    ];
}
