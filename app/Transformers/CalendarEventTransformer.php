<?php

namespace App\Transformers;

use App\Models\OdAppointment;
use App\Enums\AppointmentStatus;

class CalendarEventTransformer
{
    /**
     * Map a localized OdAppointment model into the FullCalendar required format.
     */
    public static function transform(OdAppointment $appointment): array
    {
        // Safe defaults and enum translations
        $status = AppointmentStatus::tryFrom($appointment->AptStatus) ?? AppointmentStatus::Scheduled;
        $color = $status->color();

        $startDt = new \DateTime($appointment->AptDateTime);
        $endDt = (clone $startDt)->modify("+{$appointment->duration_minutes} minutes");

        // Match operatory tracking
        $opKey = 'op-' . ($appointment->Op ?? '0');

        return [
            'id' => $appointment->AptNum,
            'title' => $appointment->patient?->full_name ?? 'Unknown Patient',
            'start' => $startDt->format('Y-m-d\TH:i:s'),
            'end' => $endDt->format('Y-m-d\TH:i:s'),
            'resourceId' => $opKey,
            'backgroundColor' => $color,
            'borderColor' => $color,
            'textColor' => '#ffffff',
            'doctor' => $appointment->provider?->Abbr ?? '',
            'procedure' => $appointment->ProcDescript ?? '',
            'status' => $status->text(),
            'operator' => $appointment->Op ?? '',
            'clinic' => $appointment->ClinicNum ?? '',
            'note' => $appointment->Note ?? '',
            'patNum' => $appointment->PatNum,
            'isNewPatient' => (bool) $appointment->IsNewPatient,
        ];
    }
}
