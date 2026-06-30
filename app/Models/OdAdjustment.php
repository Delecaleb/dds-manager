<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdAdjustment extends Model
{
    protected $fillable = [
        'AdjNum',
        'AdjDate',
        'AdjAmt',
        'PatNum',
        'AdjType',
        'ProvNum',
        'AdjNote',
        'ProcDate',
        'ProcNum',
        'DateEntry',
        'ClinicNum',
        'StatementNum',
        'SecUserNumEntry',
        'SecDateTEdit',
        'TaxTransID',
    ];
}
