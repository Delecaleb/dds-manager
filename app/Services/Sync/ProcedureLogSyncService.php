<?php

namespace App\Services\Sync;

use App\Models\OdProcedureLog;

class ProcedureLogSyncService extends BaseQuerySyncService
{
    protected int $batchSize = 1000;

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
     * Business date a windowed sync (sync:procedurelogs-range) filters on.
     * ProcDate is what every production metric buckets by, so it is the only
     * correct bound for a "from 2025 till date" backfill.
     */
    protected function dateColumn(): ?string
    {
        return 'ProcDate';
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