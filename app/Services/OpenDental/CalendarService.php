<?php

namespace App\Services\OpenDental;

use Illuminate\Support\Facades\Cache;

class CalendarService
{
    public function __construct(
        protected PatientService $patients,
        protected AppointmentService $appointments
    ) {}


    public function events($start, $end)
    {
        $appointments = collect($this->appointments->byDate($start, $end));

        if ($appointments->isEmpty()) return [];

        // Build a per-PatNum patient map.
        // PatientService::all() only returns the first API page (~1000 rows),
        // so patients with high PatNums are silently missing via findMany().
        // Fetching individually by PatNum is reliable regardless of list size.
        $patientIds = $appointments->pluck('PatNum')->filter()->unique()->values();
        $patientMap = [];
        foreach ($patientIds as $pid) {
            $patientMap[(string) $pid] = Cache::remember(
                "od_patient_{$pid}",
                now()->addHours(12),
                fn () => $this->patients->find((int) $pid)
            );
        }

        $statusColors = [
            1 => '#10b981', 'Scheduled' => '#10b981',
            2 => '#64748b', 'Complete'  => '#64748b',
            5 => '#ef4444', 'Broken'    => '#ef4444',
            4 => '#f59e0b', 'ASAP'      => '#f59e0b',
        ];

        return $appointments->map(function ($appointment) use ($patientMap, $statusColors) {

            // Some OpenDental builds embed LName/FName in the appointment row.
            // Fall back to the individually fetched patient when they are absent.
            $lname = $appointment['LName'] ?? null;
            $fname = $appointment['FName'] ?? null;

            if ($lname === null || $lname === '') {
                $patient = $patientMap[(string) ($appointment['PatNum'] ?? '')] ?? null;
                $lname   = $patient['LName'] ?? '';
                $fname   = $patient['FName'] ?? '';
            }

            $title = trim(($lname ?? '') . ', ' . ($fname ?? ''), ' ,');
            if ($title === '') {
                $title = 'Patient #' . ($appointment['PatNum'] ?? '?');
            }

            $pattern         = $appointment['Pattern'] ?? '';
            $durationMinutes = strlen($pattern) > 0 ? strlen($pattern) * 10 : 60;

            $startDt = new \DateTime($appointment['AptDateTime']);
            $endDt   = (clone $startDt)->modify("+{$durationMinutes} minutes");

            $status = $appointment['AptStatus'] ?? 1;
            $color  = $statusColors[$status] ?? '#3b82f6';

            // Op (operatory number) is always-present and numeric — reliable resource key.
            $opKey = 'op-' . ($appointment['Op'] ?? '0');

            return [
                'id'              => $appointment['AptNum'],
                'title'           => $title,
                'start'           => $startDt->format('Y-m-d\TH:i:s'),
                'end'             => $endDt->format('Y-m-d\TH:i:s'),
                'resourceId'      => $opKey,
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'textColor'       => '#ffffff',
                'doctor'          => $appointment['provAbbr'] ?? '',
                'procedure'       => $appointment['ProcDescript'] ?? '',
                'status'          => $status,
                'operator'        => $appointment['Op'] ?? '',
                'clinic'          => $appointment['ClinicNum'] ?? '',
                'note'            => $appointment['Note'] ?? '',
                'patNum'          => $appointment['PatNum'] ?? '',
                'isNewPatient'    => (bool) ($appointment['IsNewPatient'] ?? false),
            ];
        });
    }


    public function resources($start, $end)
    {
        $appointments = collect($this->appointments->byDate($start, $end));

        if ($appointments->isEmpty()) return [];

        return $appointments
            ->groupBy('Op')
            ->map(function ($apts, $op) {
                $first    = $apts->first();
                $provAbbr = trim($first['provAbbr'] ?? '');
                return [
                    'id'    => 'op-' . $op,
                    'title' => $provAbbr ?: ('Op ' . $op),
                ];
            })
            ->sortKeys()
            ->values();
    }
}
