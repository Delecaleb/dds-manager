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

    /**
     * SecDateTEdit, not DateEntry: DateEntry is the creation date, so edits to an
     * existing payment (amount, PayDate, splits re-balanced) would never re-sync.
     */
    protected function syncColumn(): ?string
    {
        return 'SecDateTEdit';
    }

    protected function dateColumn(): ?string
    {
        return 'PayDate';
    }
}
