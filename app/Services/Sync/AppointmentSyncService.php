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

    /**
     * Business date a windowed sync filters on.
     * AptDateTime is what appointment/calendar metrics bucket by.
     */
    protected function dateColumn(): ?string
    {
        return 'AptDateTime';
    }

    /**
     * AptDateTime is a real DATETIME column locally, but OpenDental sends it
     * as an ISO-8601 string with a 'T' separator. Normalize it on the way in
     * so the value stores cleanly and stays range-queryable (calendar, KPIs,
     * financials all filter appointments by AptDateTime).
     */
    protected function transformRow(array $row): array
    {
        if (array_key_exists('AptDateTime', $row)) {
            $row['AptDateTime'] = $this->normalizeDateTime($row['AptDateTime']);
        }

        return $row;
    }
}
