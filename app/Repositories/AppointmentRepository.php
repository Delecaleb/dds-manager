<?php

namespace App\Repositories;

use App\Models\OdAppointment;
use Illuminate\Database\Eloquent\Collection;

class AppointmentRepository
{
    /**
     * Retrieve appointments with relationships eager-loaded, within a date range.
     * Filter dynamically active apt status logic.
     */
    public function getAppointmentsByDateRange(string $start, string $end, ?string $clinicId = null): Collection
    {
        // AptDateTime may be stored either as OpenDental's raw ISO string with
        // a 'T' separator (varchar) or as a normalized MySQL DATETIME, depending
        // on whether the datetime conversion migration has run yet. Filtering on
        // the normalized DATE keeps the range correct in BOTH cases — REPLACE is
        // a harmless no-op once the column is a real DATETIME (no 'T' present).
        $startDate = substr($start, 0, 10);
        $endDate   = substr($end, 0, 10);

        $query = OdAppointment::query()
            ->with(['patient', 'provider'])
            ->withSum('procedureLogs as production_total', 'ProcFee')
            ->whereRaw("DATE(REPLACE(AptDateTime, 'T', ' ')) BETWEEN ? AND ?", [$startDate, $endDate]);

        // Filter out unused status codes to match legacy Calendar logic
        // E.g., skip planned or unscheduled if needed in calendar view, though OpenDental API filtered these dynamically
        // 1=Scheduled, 2=Complete, 4=ASAP, 5=Broken
        $query->whereIn('AptStatus', [1, 2, 4, 5]);

        if ($clinicId && $clinicId !== 'all') {
            $query->where('ClinicNum', $clinicId);
        }

        return $query->get();
    }
}
