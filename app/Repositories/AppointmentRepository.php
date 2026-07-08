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
        // AptDateTime is a real DATETIME column; build full-day bounds so the
        // range is inclusive of the entire end day.
        $startBound = substr($start, 0, 10) . ' 00:00:00';
        $endBound   = substr($end, 0, 10) . ' 23:59:59';

        $query = OdAppointment::query()
            ->with(['patient', 'provider'])
            ->whereBetween('AptDateTime', [$startBound, $endBound]);

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
