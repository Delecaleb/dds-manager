<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class TreatmentPlan extends Model
{
    use BelongsToOffice;

    protected $primaryKey = 'TreatPlanNum';

    public $incrementing = false;

    protected $fillable = [
        'office_id',
        'TreatPlanNum',
        'PatNum',
        'DateTP',
        'Heading',
        'Note',
        'Signature',
        'SigIsTopaz',
        'ResponsParty',
        'DocNum',
        'TPStatus',
        'SecUserNumEntry',
        'SecDateEntry',
        'SecDateTEdit',
        'UserNumPresenter',
        'TPType',
        'SignaturePractice',
        'DateTSigned',
        'DateTPracticeSigned',
        'SignatureText',
        'SignaturePracticeText',
        'MobileAppDeviceNum',
    ];
}
