<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Database\Factories\ClaimProcsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimProcs extends Model
{
    /** @use HasFactory<ClaimProcsFactory> */
    use BelongsToOffice, HasFactory;

    protected $table = 'od_claim_procs';

    protected $fillable = [
        'office_id',
        'ClaimProcNum',
        'ProcNum',
        'ClaimNum', 'PatNum', 'ProvNum', 'FeeBilled', 'InsPayEst', 'DedApplied', 'Status', 'InsPayAmt', 'Remarks', 'ClaimPaymentNum', 'PlanNum', 'DateCP', 'WriteOff', 'CodeSent', 'AllowedOverride', 'Percentage', 'PercentOverride', 'CopayAmt', 'NoBillIns', 'PaidOtherIns', 'BaseEst', 'CopayOverride', 'ProcDate', 'DateEntry', 'LineNumber', 'DedEst', 'DedEstOverride', 'InsEstTotal', 'InsEstTotalOverride', 'PaidOtherInsOverride', 'EstimateNote', 'WriteOffEst', 'WriteOffEstOverride', 'ClinicNum', 'InsSubNum', 'PaymentRow', 'PayPlanNum', 'ClaimPaymentTracking', 'SecUserNumEntry', 'SecDateEntry', 'SecDateTEdit', 'DateSuppReceived', 'DateInsFinalized', 'IsTransfer', 'ClaimAdjReasonCodes', 'IsOverpay', 'SecurityHash', 'Etrans835AttachNum',
    ];
}
