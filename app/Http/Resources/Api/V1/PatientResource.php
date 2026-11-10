<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\PatientStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The public shape of one patient. OpenDental column names stop here: field names in this
 * class are the API contract and must not change within v1.
 *
 * @property object $resource a row from PatientMatchService
 */
class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $row = $this->resource;

        return [
            'id' => (int) $row->PatNum,
            'first_name' => $row->FName,
            'last_name' => $row->LName,
            'birthdate' => self::date($row->Birthdate),
            'status' => PatientStatus::fromStored($row->PatStatus)?->slug(),
            'clinic_num' => (int) $row->ClinicNum,
            'first_visit' => self::date($row->DateFirstVisit),
        ];
    }

    /** OpenDental's blank date (0001-01-01) reads as no date. */
    private static function date(mixed $value): ?string
    {
        $date = substr((string) $value, 0, 10);

        return $date === '' || $date < '1880-01-01' ? null : $date;
    }
}
