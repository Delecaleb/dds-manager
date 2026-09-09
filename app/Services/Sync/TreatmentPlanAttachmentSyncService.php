<?php

namespace App\Services\Sync;

use App\Models\OdTreatmentPlanAttachments;

class TreatmentPlanAttachmentSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'treatplanattach';
    }

    protected function model(): string
    {
        return OdTreatmentPlanAttachments::class;
    }

    protected function primaryKey(): string
    {
        return 'TreatPlanAttachNum';
    }

    /**
     * No incremental sync column exists.
     */
    protected function syncColumn(): ?string
    {
        return null;
    }

    /**
     * Pull every column.
     */
    protected function select(): string
    {
        return '*';
    }

    protected function orderBy(): string
    {
        return 'TreatPlanAttachNum';
    }
}
