<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One OpenDental procedure code; field names here are the API contract.
 *
 * @property object $resource a row from ProcedureCatalogService
 */
class ProcedureCodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $row = $this->resource;

        return [
            'code' => $row->ProcCode,
            'description' => $row->Descript,
            'abbreviation' => filled($row->AbbrDesc) ? $row->AbbrDesc : null,
            'category' => $row->category,
            'is_hygiene' => in_array(strtolower((string) $row->IsHygiene), ['1', 'true'], true),
        ];
    }
}
