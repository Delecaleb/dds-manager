<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdTreatmentPlanAttachments extends Model
{
    protected $primaryKey = 'TreatPlanAttachNum';

    public $incrementing = false;

    protected $fillable = [
        'TreatPlanAttachNum',
        'TreatPlanNum',
        'ProcNum',
        'Priority',
    ];
}
