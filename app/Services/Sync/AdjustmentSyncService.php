<?php

namespace App\Services\Sync;

use App\Models\OdAdjustment;

class AdjustmentSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'adjustment';
    }

    protected function model(): string
    {
        return OdAdjustment::class;
    }

    protected function primaryKey(): string
    {
        return 'AdjNum';
    }

    protected function syncMode(): string
    {
        return 'timestamp';
    }

    protected function syncColumn(): ?string
    {
        return 'SecDateTEdit';
    }
}