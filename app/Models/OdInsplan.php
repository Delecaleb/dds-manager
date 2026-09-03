<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;

class OdInsplan extends Model
{
    use BelongsToOffice;

    protected $primaryKey = 'PlanNum';

    public $incrementing = false;

    protected $fillable = [
        'office_id',
        'PlanNum',
        'GroupName',
        'GroupNum',
        'PlanNote',
        'FeeSched',
        'PlanType',
        'ClaimFormNum',
        'UseAltCode',
        'ClaimsUseUCR',
        'CopayFeeSched',
        'EmployerNum',
        'CarrierNum',
        'AllowedFeeSched',
        'TrojanID',
        'DivisionNo',
        'IsMedical',
        'FilingCode',
        'DentaideCardSequence',
        'ShowBaseUnits',
        'CodeSubstNone',
        'IsHidden',
        'MonthRenew',
        'FilingCodeSubtype',
        'CanadianPlanFlag',
        'CanadianDiagnosticCode',
        'CanadianInstitutionCode',
        'RxBIN',
        'CobRule',
        'SopCode',
        'SecUserNumEntry',
        'SecDateEntry',
        'SecDateTEdit',
        'HideFromVerifyList',
        'OrthoType',
        'OrthoAutoProcFreq',
        'OrthoAutoProcCodeNumOverride',
        'OrthoAutoFeeBilled',
        'OrthoAutoClaimDaysWait',
        'BillingType',
        'HasPpoSubstWriteoffs',
        'ExclusionFeeRule',
        'ManualFeeSchedNum',
        'IsBlueBookEnabled',
        'InsPlansZeroWriteOffsOnAnnualMaxOverride',
        'InsPlansZeroWriteOffsOnFreqOrAgingOverride',
        'PerVisitPatAmount',
        'PerVisitInsAmount',
    ];

    public function carrier()
    {
        return $this->belongsTo(OdCarrier::class, 'CarrierNum', 'CarrierNum');
    }
}
