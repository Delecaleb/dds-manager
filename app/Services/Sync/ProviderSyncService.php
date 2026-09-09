<?php

namespace App\Services\Sync;

use App\Models\OdProvider;

class ProviderSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'provider';
    }

    protected function model(): string
    {
        return OdProvider::class;
    }

    protected function primaryKey(): string
    {
        return 'ProvNum';
    }

    /**
     * provider does not have SecDateTEdit.
     * Use DateTStamp if available.
     */
    protected function syncColumn(): ?string
    {
        return 'DateTStamp';
    }

    /**
     * Pull every column.
     */
    protected function select(): string
    {
        return '*';
    }

    /**
     * Used for the initial full sync.
     */
    protected function orderBy(): string
    {
        return 'ProvNum';
    }
}
