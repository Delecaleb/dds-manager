<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class OdProvider extends Model
{
    use BelongsToOffice;

    protected $primaryKey = 'ProvNum';

    public $incrementing = false;

    /**
     * ProvNum became BIGINT (2026-10-27, for indexed joins); it is still read as a string so
     * pages that compare provider ids as text keep working.
     */
    protected $keyType = 'string';

    protected $casts = [
        'ProvNum' => 'string',
    ];

    protected $fillable = [
        'office_id',
        'ProvNum',
        'Abbr',
        'ItemOrder',
        'LName',
        'FName',
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
