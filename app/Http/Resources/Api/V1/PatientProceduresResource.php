<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * One patient's completed procedures in the requested period. `fee` is OpenDental's ProcFee
 * on the procedure; field names here are the API contract and must not change within v1.
 *
 * @property object{patient_id: int, procedures: Collection<int, object>} $resource
 */
class PatientProceduresResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $procedures = $this->resource->procedures;

        return [
            'patient_id' => $this->resource->patient_id,
            'procedure_count' => $procedures->count(),
            'total_fee' => round((float) $procedures->sum(fn (object $p) => (float) $p->ProcFee), 2),
            'procedures' => $procedures->map(fn (object $p) => [
                'id' => (int) $p->ProcNum,
                'date' => substr((string) $p->ProcDate, 0, 10),
                'code' => $p->ProcCode,
                'description' => $p->Descript,
                'category' => $p->category,
                'fee' => round((float) $p->ProcFee, 2),
                'clinic_num' => (int) $p->ClinicNum,
                'provider_id' => (int) $p->ProvNum ?: null,
            ])->values()->all(),
        ];
    }
}
