<?php

namespace App\Models;

use App\Domain\Support\ProcStatus;
use Illuminate\Database\Eloquent\Model;

class OdProcedureLog extends Model
{
    protected $fillable = [
        'ProcNum',
        'PatNum',
        'AptNum',
        'OldCode',
        'ProcDate',
        'ProcFee',
        'Surf',
        'ToothNum',
        'ToothRange',
        'Priority',
        'ProcStatus',
        'ProvNum',
        'Dx',
        'PlannedAptNum',
        'PlaceService',
        'Prosthesis',
        'DateOriginalProsth',
        'ClaimNote',
        'DateEntryC',
        'ClinicNum',
        'MedicalCode',
        'DiagnosticCode',
        'IsPrincDiag',
        'ProcNumLab',
        'BillingTypeOne',
        'BillingTypeTwo',
        'CodeNum',
        'CodeMod1',
        'CodeMod2',
        'CodeMod3',
        'CodeMod4',
        'RevCode',
        'UnitQty',
        'BaseUnits',
        'StartTime',
        'StopTime',
        'DateTP',
        'SiteNum',
        'HideGraphics',
        'CanadianTypeCodes',
        'ProcTime',
        'ProcTimeEnd',
        'DateTStamp',
        'Prognosis',
        'DrugUnit',
        'DrugQty',
        'UnitQtyType',
        'StatementNum',
        'IsLocked',
        'BillingNote',
        'RepeatChargeNum',
        'SnomedBodySite',
        'DiagnosticCode2',
        'DiagnosticCode3',
        'DiagnosticCode4',
        'ProvOrderOverride',
        'Discount',
        'IsDateProsthEst',
        'IcdVersion',
        'IsCpoe',
        'SecUserNumEntry',
        'SecDateEntry',
        'DateComplete',
        'OrderingReferralNum',
        'TaxAmt',
        'Urgency',
        'DiscountPlanAmt',
        'NoBillIns',
    ];

    protected $primaryKey = 'ProcNum';

    public $incrementing = false;

    public function scopeCompleted($query)
    {
        return $query->whereIn('ProcStatus', ProcStatus::completed());
    }

    public function scopeInDateRange($query, $start, $end)
    {
        return $query->whereBetween('ProcDate', [$start, $end]);
    }

}
