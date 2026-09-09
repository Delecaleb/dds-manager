<?php

namespace App\Services\Sync;

use App\Models\OdProcedure;

class ProcedureSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'procedurecode';
    }

    protected function model(): string
    {
        return OdProcedure::class;
    }

    protected function primaryKey(): string
    {
        return 'CodeNum';
    }

    /**
     * procedurecode doesn't have SecDateTEdit.
     * DateTStamp is the closest incremental field.
     */
    protected function syncColumn(): ?string
    {
        return 'DateTStamp';
    }

    /**
     * Pull all columns.
     */
    protected function select(): string
    {
        return '*';
    }

    /**
     * Used during the initial sync.
     */
    protected function orderBy(): string
    {
        return 'CodeNum';
    }
}
