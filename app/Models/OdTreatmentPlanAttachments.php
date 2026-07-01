<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdTreatmentPlanAttachments extends Model
{
    protected $fillable = [
        'TreatPlanAttachNum',
        'TreatPlanNum',
        'ProcNum',
        'Priority',
    ];
}
