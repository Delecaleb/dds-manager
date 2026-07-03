<?php

namespace App\Models;

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

    public function patientVisits($start, $end)
    {
        return OdProcedureLog::whereBetween('ProcDate', [$start, $end])
            ->where('ProcStatus', 'C')
            ->distinct('PatNum')
            ->count('PatNum');
    }

    public function newPatientVisits($start, $end)
    {
        return OdProcedureLog::select('PatNum')
            ->selectRaw('MIN(ProcDate) first_visit')
            ->where('ProcStatus', 'C')
            ->groupBy('PatNum')
            ->havingBetween('first_visit', [$start, $end])
            ->count();
    }

    public function avgProductionPerPatient($start, $end)
    {
        $production = OdProcedureLog::where('ProcStatus', 'C')
            ->whereBetween('ProcDate', [$start, $end])
            ->sum('ProcFee');

        $visits = $this->patientVisits($start, $end);

        return $visits
            ? round($production / $visits, 2)
            : 0;
    }
}
