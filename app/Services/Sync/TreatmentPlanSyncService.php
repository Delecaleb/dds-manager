<?php

namespace App\Services\Sync;

use App\Models\TreatmentPlan;

class TreatmentPlanSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'treatplan';
    }

    protected function model(): string
    {
        return TreatmentPlan::class;
    }

    protected function primaryKey(): string
    {
        return 'TreatPlanNum';
    }

    /**
     * Supports incremental sync.
     */
    protected function syncColumn(): ?string
    {
        return 'SecDateTEdit';
    }

    /**
     * Pull every column.
     */
    protected function select(): string
    {
        return '*';
    }

    /**
     * Used during the initial full sync.
     */
    protected function orderBy(): string
    {
        return 'TreatPlanNum';
    }

    /**
     * Module name stored in sync_logs.
     */
    protected function module(): string
    {
        return 'treatment_plans';
    }
}