<?php

namespace App\Services\Sync;

use App\Models\OdPayment;

class PaymentSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'payment';
    }

    protected function model(): string
    {
        return OdPayment::class;
    }

    protected function primaryKey(): string
    {
        return 'PayNum';
    }

    protected function syncColumn(): ?string
    {
        return 'DateEntry';
    }
}
