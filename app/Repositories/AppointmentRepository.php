<?php

namespace App\Repositories;

use App\Models\OdAppointment;
use App\Models\Office;
use Illuminate\Database\Eloquent\Collection;

class AppointmentRepository
{
    /**
     * Retrieve appointments with relationships eager-loaded, within a date range.
     * Filter dynamically active apt status logic.
     */
    public function getAppointmentsByDateRange(string $start, string $end, int|string|null $clinicId = null, ?int $officeId = null): Collection
    {
        // AptDateTime may be stored either as OpenDental's raw ISO string with
        // a 'T' separator (varchar) or as a normalized MySQL DATETIME, depending
        // on whether the datetime conversion migration has run yet. Filtering on
        // the normalized DATE keeps the range correct in BOTH cases — REPLACE is
        // a harmless no-op once the column is a real DATETIME (no 'T' present).
        $startDate = substr($start, 0, 10);
        $endDate = substr($end, 0, 10);

        $officeId ??= Office::getActiveOfficeId() ?? 1;

        // PatNum / ProvNum / Op are only unique within one office's OpenDental database, so the
        // related rows are read from the appointments' office — not from the session's active
        // office (the models' global scope), which may be a different office entirely.
        $sameOffice = fn ($related) => $related->withoutGlobalScopes()->where('office_id', $officeId);

        $query = OdAppointment::withoutGlobalScopes()
            ->with(['patient' => $sameOffice, 'provider' => $sameOffice, 'operatory' => $sameOffice])
            ->whereRaw("DATE(REPLACE(AptDateTime, 'T', ' ')) BETWEEN ? AND ?", [$startDate, $endDate])
            ->where('office_id', $officeId);

        // Filter out unused status codes to match legacy Calendar logic
        // E.g., skip planned or unscheduled if needed in calendar view, though OpenDental API filtered these dynamically
        // 1=Scheduled, 2=Complete, 4=ASAP, 5=Broken
        $query->whereIn('AptStatus', [1, 2, 4, 5]);

        if ($clinicId !== null && $clinicId !== '' && $clinicId !== 'all') {
            $query->where('ClinicNum', (int) $clinicId);
        }

        return $query->get();
    }
}
