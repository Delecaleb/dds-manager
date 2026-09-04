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

        // Provider color coding: use OpenDental ProvColor, or deterministic palette hash
        $provColor = $appointment->provider?->ProvColor;
        $palette = ['#6DE5C1', '#996BE5', '#38bdf8', '#fb923c', '#f472b6', '#a78bfa', '#34d399', '#facc15', '#818cf8', '#2dd4bf'];
        if ($provColor && preg_match('/^#?[0-9a-fA-F]{6}$/', (string) $provColor)) {
            $color = str_starts_with((string) $provColor, '#') ? (string) $provColor : '#'.$provColor;
        } elseif ($appointment->ProvNum > 0) {
            $color = $palette[((int) $appointment->ProvNum) % count($palette)];
        } else {
            $color = '#94a3b8';
        }
        $textColor = '#ffffff';

        $startDt = new \DateTime($appointment->AptDateTime);
        $endDt = (clone $startDt)->modify("+{$appointment->duration_minutes} minutes");

        // Match operatory tracking
        $opKey = 'op-'.($appointment->Op ?? '0');
        $opTitle = $appointment->operatory?->OpName ?: ($appointment->Op ? 'Op '.$appointment->Op : 'Unassigned');

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
