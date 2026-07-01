<?php

namespace App\Services\Sync;

use App\Models\OdProcedure;
use App\Models\SyncLog;
use App\Services\OpenDental\OpenDentalClient;

class ProcedureSyncService
{
    public function __construct(
        protected OpenDentalClient $client
    ) {
    }

    public function sync()
    {
        $log = SyncLog::firstOrCreate([
            'module' => 'procedures'
        ]);

        $offset = 0;
        while (true) {
            $response = $this->client->get('procedurecodes', [
                'Offset' => $offset
            ]);

            if (empty($response)) {
                break;
            }

            foreach ($response as $proc) {
                OdProcedure::updateOrCreate(
                    ['CodeNum' => $proc['CodeNum']],
                    [
                        'ProcCode' => $proc['ProcCode'] ?? null,
                        'Descript' => $proc['Descript'] ?? null,
                        'AbbrDesc' => $proc['AbbrDesc'] ?? null,
                        'ProcTime' => $proc['ProcTime'] ?? null,
                        'ProcCat' => $proc['ProcCat'] ?? null,
                        'TreatArea' => $proc['TreatArea'] ?? null,
                        'NoBillIns' => $proc['NoBillIns'] ?? null,
                        'IsProsth' => $proc['IsProsth'] ?? null,
                        'DefaultNote' => $proc['DefaultNote'] ?? null,
                        'IsHygiene' => $proc['IsHygiene'] ?? null,
                        'GTypeNum' => $proc['GTypeNum'] ?? null,
                        'AlternateCode1' => $proc['AlternateCode1'] ?? null,
                        'MedicalCode' => $proc['MedicalCode'] ?? null,
                        'IsTaxed' => $proc['IsTaxed'] ?? null,
                        'PaintType' => $proc['PaintType'] ?? null,
                        'GraphicColor' => $proc['GraphicColor'] ?? null,
                        'LaymanTerm' => $proc['LaymanTerm'] ?? null,
                        'IsCanadianLab' => $proc['IsCanadianLab'] ?? null,
                        'PreExisting' => $proc['PreExisting'] ?? null,
                        'BaseUnits' => $proc['BaseUnits'] ?? null,
                        'SubstitutionCode' => $proc['SubstitutionCode'] ?? null,
                        'SubstOnlyIf' => $proc['SubstOnlyIf'] ?? null,
                        'DateTStamp' => $proc['DateTStamp'] ?? null,
                        'IsMultiVisit' => $proc['IsMultiVisit'] ?? null,
                        'DrugNDC' => $proc['DrugNDC'] ?? null,
                        'RevenueCodeDefault' => $proc['RevenueCodeDefault'] ?? null,
                        'ProvNumDefault' => $proc['ProvNumDefault'] ?? null,
                        'CanadaTimeUnits' => $proc['CanadaTimeUnits'] ?? null,
                        'IsRadiology' => $proc['IsRadiology'] ?? null,
                        'DefaultClaimNote' => $proc['DefaultClaimNote'] ?? null,
                        'DefaultTPNote' => $proc['DefaultTPNote'] ?? null,
                        'BypassGlobalLock' => $proc['BypassGlobalLock'] ?? null,
                        'TaxCode' => $proc['TaxCode'] ?? null,
                        'PaintText' => $proc['PaintText'] ?? null,
                        'AreaAlsoToothRange' => $proc['AreaAlsoToothRange'] ?? null,
                        'DiagnosticCodes' => $proc['DiagnosticCodes'] ?? null,
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
