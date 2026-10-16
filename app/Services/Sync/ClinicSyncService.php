<?php

namespace App\Services\Sync;

use App\Models\OdClinics;

class ClinicSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'clinic';
    }

    protected function model(): string
    {
        return OdClinics::class;
    }

    protected function primaryKey(): string
    {
        return 'ClinicNum';
    }

    /**
     * None: OpenDental's `clinic` table has no change timestamp (no SecDateTEdit, no
     * DateTStamp), so filtering on one is rejected ("Unknown column 'SecDateTEdit'").
     * The table holds a handful of rows, so every run simply re-reads all of it, like
     * recall types.
     */
    protected function syncColumn(): ?string
    {
        return null;
    }
}
