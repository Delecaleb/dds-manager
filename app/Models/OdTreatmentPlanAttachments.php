<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class OdTreatmentPlanAttachments extends Model
{
    use BelongsToOffice;

    protected $primaryKey = 'TreatPlanAttachNum';

    public $incrementing = false;

    protected $fillable = [
        'office_id',
        'TreatPlanAttachNum',
        'TreatPlanNum',
        'ProcNum',
        'Priority',
    ];
}
