<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class OdCarrier extends Model
{
    use BelongsToOffice;

    protected $primaryKey = 'CarrierNum';

    public $incrementing = false;

    protected $fillable = [
        'office_id',
        'CarrierNum',
        'CarrierName',
        'Address',
        'Address2',
        'City',
        'State',
        'Zip',
        'Phone',
        'ElectID',
        'NoSendElect',
        'IsCDA',
        'CDAnetVersion',
        'CanadianNetworkNum',
        'IsHidden',
        'CanadianEncryptionMethod',
        'CanadianSupportedTypes',
        'SecUserNumEntry',
        'SecDateEntry',
        'SecDateTEdit',
        'TIN',
        'CarrierGroupName',
        'ApptTextBackColor',
        'IsCoinsuranceInverted',
        'TrustedEtransFlags',
        'CobInsPaidBehaviorOverride',
        'EraAutomationOverride',
        'OrthoInsPayConsolidate',
        'PaySuiteTransSup',
        'PreAuthCodes',
    ];
}
