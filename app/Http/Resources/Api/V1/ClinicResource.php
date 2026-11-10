<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One clinic's name and contact details; field names here are the API contract.
 *
 * @property object $resource a row from ClinicRegistry::contacts
 */
class ClinicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $row = $this->resource;

        return [
            'clinic_num' => (int) $row->ClinicNum,
            'name' => $row->Description,
            'abbreviation' => filled($row->Abbr) ? $row->Abbr : null,
            'phone' => filled($row->Phone) ? $row->Phone : null,
            'address' => [
                'line1' => filled($row->Address) ? $row->Address : null,
                'line2' => filled($row->Address2) ? $row->Address2 : null,
                'city' => filled($row->City) ? $row->City : null,
                'state' => filled($row->State) ? $row->State : null,
                'zip' => filled($row->Zip) ? $row->Zip : null,
            ],
        ];
    }
}
