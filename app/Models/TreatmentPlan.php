<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreatmentPlan extends Model
{
    protected $fillable = [
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
