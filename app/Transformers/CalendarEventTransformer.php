<?php

namespace App\Transformers;

use App\Enums\AppointmentStatus;
use App\Models\OdAppointment;

class CalendarEventTransformer
{
    /**
     * Map a localized OdAppointment model into the FullCalendar required format.
     */
    public static function transform(OdAppointment $appointment): array
    {
        // Safe defaults and enum translations
        $status = AppointmentStatus::tryFrom($appointment->AptStatus) ?? AppointmentStatus::Scheduled;

        // Requirement 3 & 4: Provider color coding
        $color = '#94a3b8'; // default color for unassigned/others
        $textColor = '#ffffff';
        if ($appointment->ProvNum == 81) {
            $color = '#6DE5C1'; // Kathy Elias
            $textColor = '#0f172a'; // Contrast text
        } elseif ($appointment->ProvNum == 64) {
            $color = '#996BE5'; // Mason Haddow
            $textColor = '#ffffff';
        }

        $startDt = new \DateTime($appointment->AptDateTime);
        $endDt = (clone $startDt)->modify("+{$appointment->duration_minutes} minutes");

        // Match operatory tracking
        $opKey = 'op-'.($appointment->Op ?? '0');

        $staticTitles = [
            '1' => 'DR-1',
            '2' => 'DR-2',
            '3' => 'DR-3',
            '4' => 'DR-4',
            '5' => 'DR-5',
            '6' => 'Unassigned 6',
            '7' => 'Unassigned 7',
            '8' => 'Unassigned 8',
            '9' => 'Unassigned 9',
            '10' => 'Unassigned 10',
        ];
        $opTitle = $staticTitles[$appointment->Op] ?? ('Op '.$appointment->Op);

        $procedure = trim($appointment->ProcDescript ?? '');
        $note = trim($appointment->Note ?? '');

        return [
            'id' => $appointment->AptNum,
            'title' => $appointment->patient?->full_name ?? 'Unknown Patient',
            'start' => $startDt->format('Y-m-d\TH:i:s'),
            'end' => $endDt->format('Y-m-d\TH:i:s'),
            'resourceId' => $opKey,
            'backgroundColor' => $color,
            'borderColor' => $color,
            'textColor' => $textColor,
            'doctor' => $appointment->provider?->Abbr ?? '',
            'procedure' => $procedure,
            'status' => $status->text(),
            'operator' => $appointment->Op ?? '',
            'clinic' => $appointment->ClinicNum ?? '',
            'note' => $note,
            'patNum' => $appointment->PatNum,
            'isNewPatient' => (bool) $appointment->IsNewPatient,
            'phone' => $appointment->patient?->WirelessPhone ?: ($appointment->patient?->HmPhone ?: ''),

            // Phase 7: Additional Click Detail Sidebar items
            'date' => $startDt->format('M d, Y'),
            'operatoryId' => $appointment->Op ?? '',
            'operatoryTitle' => $opTitle,
            'providerId' => $appointment->ProvNum ?? '',
            'providerName' => $appointment->provider ? trim(($appointment->provider->LName ?? '').', '.($appointment->provider->PName ?? '')) : '',
            'duration' => $appointment->duration_minutes,
        ];
    }
}
