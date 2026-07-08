<?php

namespace App\Services\Sync;

use App\Models\OdAppointment;

class AppointmentSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'appointment';
    }

    protected function model(): string
    {
        return OdAppointment::class;
    }

    protected function syncColumn(): ?string
    {
        return 'DateTStamp';
    }

    protected function primaryKey(): string
    {
        return 'AptNum';
    }
}