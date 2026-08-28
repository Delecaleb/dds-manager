<?php

namespace App\Services\Sync;

use App\Models\OdDeposit;

class DepositSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'deposit';
    }

    protected function model(): string
    {
        return OdDeposit::class;
    }

    protected function primaryKey(): string
    {
        return 'DepositNum';
    }

    protected function syncColumn(): ?string
    {
        return 'DateDeposit';
    }

    protected function module(): string
    {
        return 'deposits';
    }
}
