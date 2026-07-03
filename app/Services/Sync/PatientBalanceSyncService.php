<?php

namespace App\Services\Sync;

use App\Models\OdPaySplit;

class PatientBalanceSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'paysplit';
    }

    protected function model(): string
    {
        return OdPaySplit::class;
    }

    protected function primaryKey(): string
    {
        return 'SplitNum';
    }

    protected function syncMode(): string
    {
        return 'timestamp';
    }

    protected function syncColumn(): ?string
    {
        return 'DateTStamp';
    }
}