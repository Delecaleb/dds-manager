<?php

namespace App\Services\Sync;

use App\Models\OdProcedureLog;

class ProcedureLogSyncService extends BaseQuerySyncService
{
    protected int $batchSize = 500;

    protected function table(): string
    {
        return 'procedurelog';
    }

    protected function model(): string
    {
        return OdProcedureLog::class;
    }

    protected function primaryKey(): string
    {
        return 'ProcNum';
    }

    /**
     * procedurelog supports incremental sync.
     */
    protected function syncColumn(): ?string
    {
        return 'DateTStamp';
    }

    /**
     * Order records by the primary key during the initial sync.
     */
    protected function orderBy(): string
    {
        return 'ProcNum';
    }

    /**
     * Pull every column.
     */
    protected function select(): string
    {
        return '*';
    }
}