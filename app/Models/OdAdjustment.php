<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class OdAdjustment extends Model
{
    use BelongsToOffice;

    protected $primaryKey = 'AdjNum';

    public $incrementing = false;

    protected $fillable = [
        'office_id',
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
