<?php

namespace App\Services\Sync;

use App\Models\OdStatement;

class StatementSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'statement';
    }

    protected function model(): string
    {
        return OdStatement::class;
    }

    protected function primaryKey(): string
    {
        return 'StatementNum';
    }

    protected function syncColumn(): ?string
    {
        return 'DateTStamp';
    }
}
