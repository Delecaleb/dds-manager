<?php

namespace App\Services\Sync;

use App\Models\OdPatient;

class PatientSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'patient';
    }

    protected function model(): string
    {
        return OdPatient::class;
    }

    protected function primaryKey(): string
    {
        return 'PatNum';
    }

    /**
     * patient table supports incremental sync
     */
    protected function syncColumn(): ?string
    {
        return 'DateTStamp';
    }

    /**
     * Optional.
     * Helps performance when selecting *
     */
    protected function orderBy(): string
    {
        return 'PatNum';
    }

    /**
     * Optional.
     * Pull every column.
     */
    protected function select(): string
    {
        return '*';
    }
}