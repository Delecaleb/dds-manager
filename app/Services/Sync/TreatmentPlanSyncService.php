<?php

namespace App\Services\Sync;

use App\Models\TreatmentPlan;
use App\Models\SyncLog;
use App\Services\OpenDental\OpenDentalClient;

class TreatmentPlanSyncService
{
    public function __construct(
        protected OpenDentalClient $client
    ) {
    }

    public function sync()
    {
        $log = SyncLog::firstOrCreate([
            'module' => 'treatment_plans'
        ]);

        $offset = 0;
        while (true) {
            $response = $this->client->get('treatplans', [
                'Offset' => $offset
            ]);

            if (empty($response)) {
                break;
            }

            foreach ($response as $tp) {
                TreatmentPlan::updateOrCreate(
                    ['TreatPlanNum' => $tp['TreatPlanNum']],
                    [
                        'PatNum' => $tp['PatNum'] ?? null,
                        'DateTP' => $tp['DateTP'] ?? null,
                        'Heading' => $tp['Heading'] ?? null,
                        'Note' => $tp['Note'] ?? null,
                        'Signature' => $tp['Signature'] ?? null,
                        'SigIsTopaz' => $tp['SigIsTopaz'] ?? null,
                        'ResponsParty' => $tp['ResponsParty'] ?? null,
                        'DocNum' => $tp['DocNum'] ?? null,
                        'TPStatus' => $tp['TPStatus'] ?? null,
                        'SecUserNumEntry' => $tp['SecUserNumEntry'] ?? null,
                        'SecDateEntry' => $tp['SecDateEntry'] ?? null,
                        'SecDateTEdit' => $tp['SecDateTEdit'] ?? null,
                        'UserNumPresenter' => $tp['UserNumPresenter'] ?? null,
                        'TPType' => $tp['TPType'] ?? null,
                        'SignaturePractice' => $tp['SignaturePractice'] ?? null,
                        'DateTSigned' => $tp['DateTSigned'] ?? null,
                        'DateTPracticeSigned' => $tp['DateTPracticeSigned'] ?? null,
                        'SignatureText' => $tp['SignatureText'] ?? null,
                        'SignaturePracticeText' => $tp['SignaturePracticeText'] ?? null,
                        'MobileAppDeviceNum' => $tp['MobileAppDeviceNum'] ?? null,
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
