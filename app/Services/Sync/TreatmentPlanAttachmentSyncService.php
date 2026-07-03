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

    /**
     * Used during full sync.
     */
    protected function orderBy(): string
    {
        return 'TreatPlanAttachNum';
    }

    /**
     * Name used in sync_logs.
     */
    protected function module(): string
    {
        return 'treatment_plan_attachments';
    }
}