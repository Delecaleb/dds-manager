<?php

namespace App\Services\Sync;

use App\Models\OdInsplan;

class SyncInsplanService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'insplan';
    }

    protected function model(): string
    {
        return OdInsplan::class;
    }

    protected function primaryKey(): string
    {
        return 'PlanNum';
    }

    protected function syncColumn(): ?string
    {
        return 'SecDateTEdit';
    }

    protected function module(): string
    {
        return 'insplans';
    }

}
