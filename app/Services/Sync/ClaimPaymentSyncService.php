<?php

namespace App\Services\Sync;

use App\Models\OdClaimPayment;

class ClaimPaymentSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'claimpayment';
    }

    protected function model(): string
    {
        return OdClaimPayment::class;
    }

    protected function primaryKey(): string
    {
        return 'ClaimPaymentNum';
    }

    protected function syncColumn(): ?string
    {
        return 'SecDateTEdit';
    }

    protected function module(): string
    {
        return 'claimpayments';
    }
}
