<?php

namespace App\Services\Sync;

use App\Models\OdSchedule;

class ScheduleSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'schedule';
    }

    protected function model(): string
    {
        return OdSchedule::class;
    }

    protected function primaryKey(): string
    {
        return 'ScheduleNum';
    }

    protected function syncColumn(): ?string
    {
        return 'DateTStamp';
    }

    protected function module(): string
    {
        return 'schedules';
    }

}
