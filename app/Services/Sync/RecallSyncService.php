<?php

namespace App\Services\Sync;

use App\Models\OdRecall;

class RecallSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'recall';
    }

    protected function model(): string
    {
        return OdRecall::class;
    }

    protected function primaryKey(): string
    {
        return 'RecallNum';
    }

    protected function syncColumn(): ?string
    {
        return 'DateTStamp';
    }

    protected function module(): string
    {
        return 'recalls';
    }

}
