<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One person's match outcome. `result` is matched (see `patient`), none, or multiple (see
 * `candidates`, for a person to choose from).
 *
 * @property array{reference: ?string, result: string, patient: ?object, candidates: list<object>} $resource
 */
class PatientMatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $outcome = $this->resource;

        return [
            'reference' => $outcome['reference'],
            'result' => $outcome['result'],
            'patient' => $outcome['patient'] !== null ? new PatientResource($outcome['patient']) : null,
            'candidates' => PatientResource::collection($outcome['candidates']),
        ];
    }
}
