<?php

namespace App\Services\Sync;

use App\Models\ClaimProcs;

class ClaimProcSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'claimproc';
    }

    protected function model(): string
    {
        return ClaimProcs::class;
    }

    protected function primaryKey(): string
    {
        return 'ClaimProcNum';
    }

    protected function syncColumn(): ?string
    {
        return 'SecDateTEdit';
    }

    protected function module(): string
    {
        return 'claimprocs';
    }
}
