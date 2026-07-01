<?php

namespace App\Services\Sync;

use App\Models\OdProcedureLog;
use App\Models\SyncLog;
use App\Services\OpenDental\OpenDentalClient;

class ProcedureLogSyncService
{
    public function __construct(
        protected OpenDentalClient $client
    ) {
    }

    public function sync()
    {
        $log = SyncLog::firstOrCreate([
            'module' => 'procedurelogs'
        ]);

        $offset = 0;
        while (true) {
            $response = $this->client->get('procedurelogs', [
                'Offset' => $offset
            ]);

            if (empty($response)) {
                break;
            }

            foreach ($response as $logEntry) {
                OdProcedureLog::updateOrCreate(
                    ['ProcNum' => $logEntry['ProcNum']],
                    [
                        'PatNum' => $logEntry['PatNum'] ?? null,
                        'AptNum' => $logEntry['AptNum'] ?? null,
                        'OldCode' => $logEntry['OldCode'] ?? null,
                        'ProcDate' => $logEntry['ProcDate'] ?? null,
                        'ProcFee' => $logEntry['ProcFee'] ?? null,
                        'Surf' => $logEntry['Surf'] ?? null,
                        'ToothNum' => $logEntry['ToothNum'] ?? null,
                        'ToothRange' => $logEntry['ToothRange'] ?? null,
                        'Priority' => $logEntry['Priority'] ?? null,
                        'ProcStatus' => $logEntry['ProcStatus'] ?? null,
                        'ProvNum' => $logEntry['ProvNum'] ?? null,
                        'Dx' => $logEntry['Dx'] ?? null,
                        'PlannedAptNum' => $logEntry['PlannedAptNum'] ?? null,
                        'PlaceService' => $logEntry['PlaceService'] ?? null,
                        'Prosthesis' => $logEntry['Prosthesis'] ?? null,
                        'DateOriginalProsth' => $logEntry['DateOriginalProsth'] ?? null,
                        'ClaimNote' => $logEntry['ClaimNote'] ?? null,
                        'DateEntryC' => $logEntry['DateEntryC'] ?? null,
                        'ClinicNum' => $logEntry['ClinicNum'] ?? null,
                        'MedicalCode' => $logEntry['MedicalCode'] ?? null,
                        'DiagnosticCode' => $logEntry['DiagnosticCode'] ?? null,
                        'IsPrincDiag' => $logEntry['IsPrincDiag'] ?? null,
                        'ProcNumLab' => $logEntry['ProcNumLab'] ?? null,
                        'BillingTypeOne' => $logEntry['BillingTypeOne'] ?? null,
                        'BillingTypeTwo' => $logEntry['BillingTypeTwo'] ?? null,
                        'CodeNum' => $logEntry['CodeNum'] ?? null,
                        'CodeMod1' => $logEntry['CodeMod1'] ?? null,
                        'CodeMod2' => $logEntry['CodeMod2'] ?? null,
                        'CodeMod3' => $logEntry['CodeMod3'] ?? null,
                        'CodeMod4' => $logEntry['CodeMod4'] ?? null,
                        'RevCode' => $logEntry['RevCode'] ?? null,
                        'UnitQty' => $logEntry['UnitQty'] ?? null,
                        'BaseUnits' => $logEntry['BaseUnits'] ?? null,
                        'StartTime' => $logEntry['StartTime'] ?? null,
                        'StopTime' => $logEntry['StopTime'] ?? null,
                        'DateTP' => $logEntry['DateTP'] ?? null,
                        'SiteNum' => $logEntry['SiteNum'] ?? null,
                        'HideGraphics' => $logEntry['HideGraphics'] ?? null,
                        'CanadianTypeCodes' => $logEntry['CanadianTypeCodes'] ?? null,
                        'ProcTime' => $logEntry['ProcTime'] ?? null,
                        'ProcTimeEnd' => $logEntry['ProcTimeEnd'] ?? null,
                        'DateTStamp' => $logEntry['DateTStamp'] ?? null,
                        'Prognosis' => $logEntry['Prognosis'] ?? null,
                        'DrugUnit' => $logEntry['DrugUnit'] ?? null,
                        'DrugQty' => $logEntry['DrugQty'] ?? null,
                        'UnitQtyType' => $logEntry['UnitQtyType'] ?? null,
                        'StatementNum' => $logEntry['StatementNum'] ?? null,
                        'IsLocked' => $logEntry['IsLocked'] ?? null,
                        'BillingNote' => $logEntry['BillingNote'] ?? null,
                        'RepeatChargeNum' => $logEntry['RepeatChargeNum'] ?? null,
                        'SnomedBodySite' => $logEntry['SnomedBodySite'] ?? null,
                        'DiagnosticCode2' => $logEntry['DiagnosticCode2'] ?? null,
                        'DiagnosticCode3' => $logEntry['DiagnosticCode3'] ?? null,
                        'DiagnosticCode4' => $logEntry['DiagnosticCode4'] ?? null,
                        'ProvOrderOverride' => $logEntry['ProvOrderOverride'] ?? null,
                        'Discount' => $logEntry['Discount'] ?? null,
                        'IsDateProsthEst' => $logEntry['IsDateProsthEst'] ?? null,
                        'IcdVersion' => $logEntry['IcdVersion'] ?? null,
                        'IsCpoe' => $logEntry['IsCpoe'] ?? null,
                        'SecUserNumEntry' => $logEntry['SecUserNumEntry'] ?? null,
                        'SecDateEntry' => $logEntry['SecDateEntry'] ?? null,
                        'DateComplete' => $logEntry['DateComplete'] ?? null,
                        'OrderingReferralNum' => $logEntry['OrderingReferralNum'] ?? null,
                        'TaxAmt' => $logEntry['TaxAmt'] ?? null,
                        'Urgency' => $logEntry['Urgency'] ?? null,
                        'DiscountPlanAmt' => $logEntry['DiscountPlanAmt'] ?? null,
                        'NoBillIns' => $logEntry['NoBillIns'] ?? null,
                    ]
                );
            }

            if (count($response) < 1000) {
                break;
            }

            $offset += 1000;
        }

        $log->update([
            'last_synced_at' => now()
        ]);
    }
}
