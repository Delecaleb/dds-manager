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

    protected function syncColumn(): ?string
    {
        return 'SecDateTEdit';
    }
}
