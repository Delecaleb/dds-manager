<?php

namespace App\Services\Sync;

use App\Models\OdRecallType;

class RecallTypeSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'recalltype';
    }

    protected function model(): string
    {
        return OdRecallType::class;
    }

    protected function primaryKey(): string
    {
        return 'RecallTypeNum';
    }

    protected function syncColumn(): ?string
    {
        return null;
    }
}
