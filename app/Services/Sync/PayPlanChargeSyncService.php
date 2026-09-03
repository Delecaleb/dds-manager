<?php

namespace App\Services\Sync;

use App\Models\OdPayPlanCharge;

class PayPlanChargeSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'payplancharge';
    }

    protected function model(): string
    {
        return OdPayPlanCharge::class;
    }

    protected function primaryKey(): string
    {
        return 'PayPlanChargeNum';
    }

    protected function syncColumn(): ?string
    {
        return 'SecDateTEdit';
    }
}
