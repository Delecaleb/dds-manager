<?php

namespace App\Services\Sync;

use App\Models\OdTreatmentPlanAttachments;
use App\Models\SyncLog;
use App\Services\OpenDental\OpenDentalClient;

class TreatmentPlanAttachmentSyncService
{
    public function __construct(
        protected OpenDentalClient $client
    ) {
    }

    public function sync()
    {
        $log = SyncLog::firstOrCreate([
            'module' => 'treatment_plan_attachments'
        ]);

        $offset = 0;
        while (true) {
            $response = $this->client->get('treatplanattaches', [
                'Offset' => $offset
            ]);

            if (empty($response)) {
                break;
            }

            foreach ($response as $attach) {
                OdTreatmentPlanAttachments::updateOrCreate(
                    ['TreatPlanAttachNum' => $attach['TreatPlanAttachNum']],
                    [
                        'TreatPlanNum' => $attach['TreatPlanNum'] ?? null,
                        'ProcNum' => $attach['ProcNum'] ?? null,
                        'Priority' => $attach['Priority'] ?? null,
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
