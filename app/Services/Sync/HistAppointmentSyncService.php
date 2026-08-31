<?php

namespace App\Services\Sync;

use App\Models\OdHistappointment;

class HistAppointmentSyncService extends BaseQuerySyncService
{
    protected function table(): string
    {
        return 'histappointment';
    }

    protected function model(): string
    {
        return OdHistappointment::class;
    }

    protected function syncColumn(): ?string
    {
        return 'HistDateTStamp';
    }

    protected function primaryKey(): string
    {
        return 'HistApptNum';
    }

    /**
     * Business date a windowed sync filters on.
     * AptDateTime is what appointment/calendar metrics bucket by.
     */
    protected function dateColumn(): ?string
    {
        return 'AptDateTime';
    }

    /**
     * Normalize datetime strings from OpenDental.
     */
    protected function transformRow(array $row): array
    {
        foreach ([
            'AptDateTime',
            'HistDateTStamp',
            'DateTStamp',
            'DateTimeArrived',
            'DateTimeSeated',
            'DateTimeDismissed',
            'DateTimeAskedToArrive',
            'SecDateTEntry',
        ] as $col) {
            if (array_key_exists($col, $row)) {
                $row[$col] = $this->normalizeDateTime($row[$col]);
            }
        }

        return $row;
    }
}
