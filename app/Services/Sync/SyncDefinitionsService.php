<?php

namespace App\Services\Sync;

use App\Models\OdDefinition;

class SyncDefinitionsService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'definition';
    }

    protected function model(): string
    {
        return OdDefinition::class;
    }

    protected function primaryKey(): string
    {
        return 'DefNum';
    }

    protected function syncColumn(): ?string
    {
        return null;
    }

    protected function module(): string
    {
        return 'definitions';
    }
}
