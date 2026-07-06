<?php

namespace App\Services\Sync;

use App\Models\PaySplit;

class PaySplitSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'paysplit';
    }

    protected function model(): string
    {
        return PaySplit::class;
    }

    protected function primaryKey(): string
    {
        return 'SplitNum';
    }

    protected function syncColumn(): ?string
    {
        return 'SecDateTEdit';
    }
}
