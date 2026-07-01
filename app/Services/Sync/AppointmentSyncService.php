<?php

namespace App\Services\Sync;

use App\Models\OdAppointment;
use App\Models\SyncLog;
use App\Services\OpenDental\OpenDentalClient;

class AppointmentSyncService
{
    public function __construct(
        protected OpenDentalClient $client
    ) {
    }

    public function sync()
    {
        $log = SyncLog::firstOrCreate([
            'module' => 'appointments'
        ]);

        $offset = 0;
        while (true) {
            $response = $this->client->get('appointments', [
                'Offset' => $offset
            ]);

            if (empty($response)) {
                break;
            }

            foreach ($response as $appt) {
                OdAppointment::updateOrCreate(
                    ['AptNum' => $appt['AptNum']],
                    [
                        'PatNum' => $appt['PatNum'] ?? null,
                        'AptStatus' => $appt['AptStatus'] ?? null,
                        'Pattern' => $appt['Pattern'] ?? null,
                        'Confirmed' => $appt['Confirmed'] ?? null,
                        'TimeLocked' => $appt['TimeLocked'] ?? null,
                        'Op' => $appt['Op'] ?? null,
                        'Note' => $appt['Note'] ?? null,
                        'ProvNum' => $appt['ProvNum'] ?? null,
                        'ProvHyg' => $appt['ProvHyg'] ?? null,
                        'AptDateTime' => $appt['AptDateTime'] ?? null,
                        'NextAptNum' => $appt['NextAptNum'] ?? null,
                        'UnschedStatus' => $appt['UnschedStatus'] ?? null,
                        'IsNewPatient' => $appt['IsNewPatient'] ?? null,
                        'ProcDescript' => $appt['ProcDescript'] ?? null,
                        'Assistant' => $appt['Assistant'] ?? null,
                        'ClinicNum' => $appt['ClinicNum'] ?? null,
                        'IsHygiene' => $appt['IsHygiene'] ?? null,
                        'DateTStamp' => $appt['DateTStamp'] ?? null,
                        'DateTimeArrived' => $appt['DateTimeArrived'] ?? null,
                        'DateTimeSeated' => $appt['DateTimeSeated'] ?? null,
                        'DateTimeDismissed' => $appt['DateTimeDismissed'] ?? null,
                        'InsPlan1' => $appt['InsPlan1'] ?? null,
                        'InsPlan2' => $appt['InsPlan2'] ?? null,
                        'DateTimeAskedToArrive' => $appt['DateTimeAskedToArrive'] ?? null,
                        'ProcsColored' => $appt['ProcsColored'] ?? null,
                        'ColorOverride' => $appt['ColorOverride'] ?? null,
                        'AppointmentTypeNum' => $appt['AppointmentTypeNum'] ?? null,
                        'SecUserNumEntry' => $appt['SecUserNumEntry'] ?? null,
                        'SecDateTEntry' => $appt['SecDateTEntry'] ?? null,
                        'Priority' => $appt['Priority'] ?? null,
                        'ProvBarText' => $appt['ProvBarText'] ?? null,
                        'PatternSecondary' => $appt['PatternSecondary'] ?? null,
                        'SecurityHash' => $appt['SecurityHash'] ?? null,
                        'ItemOrderPlanned' => $appt['ItemOrderPlanned'] ?? null,
                        'IsMirrored' => $appt['IsMirrored'] ?? null,
                    ]
                );
            }

            if (count($response) < 1000) {
                break;
            }

            $offset += 1000;
        }

        $log->update([
            'last_synced_at' => now()
        ]);
    }
}
