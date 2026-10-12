<?php

namespace App\Http\Resources\Api\V1;

use App\Domain\Scheduling\BookingService;
use App\Enums\AppointmentStatus;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The public shape of one booking. OpenDental column names stop here: field names in this
 * class are the API contract and must not change within v1.
 *
 * Times are the office's local wall-clock time, without an offset, exactly as OpenDental
 * holds them.
 *
 * @property object $resource a row from BookingService
 */
class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $row = $this->resource;
        $start = self::dateTime($row->AptDateTime);
        $duration = BookingService::durationMinutes($row->Pattern);
        $status = AppointmentStatus::tryFrom((int) $row->AptStatus);

        $booking = [
            'id' => (int) $row->AptNum,
            'status' => $status?->slug(),
            'start' => $start?->format('Y-m-d\TH:i:s'),
            'end' => $start !== null && $duration !== null ? $start->addMinutes($duration)->format('Y-m-d\TH:i:s') : null,
            'duration_minutes' => $duration,
            'clinic_num' => (int) $row->ClinicNum,
            'operatory_id' => self::id($row->Op),
            'provider' => self::id($row->ProvNum) === null ? null : [
                'id' => (int) $row->ProvNum,
                'abbreviation' => $row->provider_abbr,
                'first_name' => $row->provider_first_name,
                'last_name' => $row->provider_last_name,
            ],
            'hygienist_id' => self::id($row->ProvHyg),
            'is_new_patient' => (bool) (int) $row->IsNewPatient,
            'is_hygiene' => (bool) (int) $row->IsHygiene,
            'procedures' => filled($row->ProcDescript) ? $row->ProcDescript : null,
            'patient_id' => self::id($row->PatNum),
        ];

        // Only present for keys holding the patient-contact permission.
        if (property_exists($row, 'patient_first_name')) {
            $booking['patient'] = [
                'first_name' => $row->patient_first_name,
                'last_name' => $row->patient_last_name,
                'preferred_name' => filled($row->patient_preferred_name) ? $row->patient_preferred_name : null,
                'email' => filled($row->patient_email) ? $row->patient_email : null,
                'phones' => [
                    'mobile' => filled($row->patient_mobile_phone) ? $row->patient_mobile_phone : null,
                    'home' => filled($row->patient_home_phone) ? $row->patient_home_phone : null,
                    'work' => filled($row->patient_work_phone) ? $row->patient_work_phone : null,
                ],
                'address' => [
                    'line1' => filled($row->patient_address) ? $row->patient_address : null,
                    'line2' => filled($row->patient_address2) ? $row->patient_address2 : null,
                    'city' => filled($row->patient_city) ? $row->patient_city : null,
                    'state' => filled($row->patient_state) ? $row->patient_state : null,
                    'zip' => filled($row->patient_zip) ? $row->patient_zip : null,
                ],
            ];
        }

        $booking['created_at'] = self::dateTime($row->SecDateTEntry)?->format('Y-m-d\TH:i:s');
        $booking['updated_at'] = self::dateTime($row->DateTStamp)?->format('Y-m-d\TH:i:s');

        return $booking;
    }

    /** OpenDental uses 0 for "none" on foreign keys. */
    private static function id(mixed $value): ?int
    {
        return (int) $value > 0 ? (int) $value : null;
    }

    /** Null for blanks and OpenDental's 0001-01-01 sentinel. */
    public static function dateTime(mixed $value): ?CarbonImmutable
    {
        $value = str_replace('T', ' ', trim((string) $value));

        if ($value === '' || $value < '1900-01-01') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
