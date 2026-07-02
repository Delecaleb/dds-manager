<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimProcs extends Model
{
    /** @use HasFactory<\Database\Factories\ClaimProcsFactory> */
    use HasFactory;
    protected $fillable = [
        'ClaimProcNum', 
        'ProcNum', 
        'ClaimNum', 'PatNum', 'ProvNum', 'FeeBilled', 'InsPayEst', 'DedApplied', 'Status', 'InsPayAmt', 'Remarks', 'ClaimPaymentNum', 'PlanNum', 'DateCP', 'WriteOff', 'CodeSent', 'AllowedOverride', 'Percentage', 'PercentOverride', 'CopayAmt', 'NoBillIns', 'PaidOtherIns', 'BaseEst', 'CopayOverride', 'ProcDate', 'DateEntry', 'LineNumber', 'DedEst', 'DedEstOverride', 'InsEstTotal', 'InsEstTotalOverride', 'PaidOtherInsOverride', 'EstimateNote', 'WriteOffEst', 'WriteOffEstOverride', 'ClinicNum', 'InsSubNum', 'PaymentRow', 'PayPlanNum', 'ClaimPaymentTracking', 'SecUserNumEntry', 'SecDateEntry', 'SecDateTEdit', 'DateSuppReceived', 'DateInsFinalized', 'IsTransfer', 'ClaimAdjReasonCodes', 'IsOverpay', 'SecurityHash', 'Etrans835AttachNum'
    ];
}
