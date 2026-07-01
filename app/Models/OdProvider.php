<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdProvider extends Model
{
    protected $fillable = [
        'ProvNum',
        'Abbr',
        'ItemOrder',
        'LName',
        'PName',
        'MI',
        'Suffix',
        'FeeSched',
        'Specialty',
        'SSN',
        'StateLicense',
        'DEANum',
        'IsSecondary',
        'ProvColor',
        'IsHidden',
        'UsingTIN',
        'BlueCrossID',
        'SigOnFile',
        'MedicaidID',
        'OutlineColor',
        'SchoolClassNum',
        'NationalProvID',
        'CanadianOfficeNum',
        'DateTStamp',
        'AnesthProvType',
        'TaxonomyCodeOverride',
        'IsCDAnet',
        'EcwID',
        'StateRxID',
        'IsNotPerson',
        'StateWhereLicensed',
        'EmailAddressNum',
        'IsInstructor',
        'EhrMuStage',
        'ProvNumBillingOverride',
        'CustomID',
        'ProvStatus',
        'IsHiddenReport',
        'IsErxEnabled',
        'Birthdate',
        'SchedNote',
        'WebSchedDescript',
        'WebSchedFaceT',
        'WebSchedImageLocation',
        'HourlyProdGoalAmt',
        'DateTerm',
        'PreferredName',
    ];
}
