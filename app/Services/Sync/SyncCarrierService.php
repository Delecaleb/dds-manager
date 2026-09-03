<?php

namespace App\Services\Sync;

use App\Models\OdCarrier;

class SyncCarrierService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'carrier';
    }

    protected function model(): string
    {
        return OdCarrier::class;
    }

    protected function primaryKey(): string
    {
        return 'CarrierNum';
    }

    protected function syncColumn(): ?string
    {
        return 'SecDateTEdit';
    }
}
