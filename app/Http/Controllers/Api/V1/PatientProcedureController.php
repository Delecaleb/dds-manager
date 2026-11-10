<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Production\ProductionService;
use App\Http\Resources\Api\V1\PatientProceduresResource;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

/**
 * POST /api/v1/patients/procedures — the completed procedures of a batch of patients in a
 * period, for the key's location. Every patient asked for is in the response, with an empty
 * list when they have none, so a caller can replace what it held for that period.
 */
class PatientProcedureController extends ApiController
{
    public function __invoke(Request $request, ProductionService $production): AnonymousResourceCollection
    {
        $maxBatch = (int) config('developer_api.max_batch');
        $maxDays = (int) config('developer_api.max_procedure_range_days');

        $data = $request->validate([
            'patient_ids' => ['required', 'array', 'min:1', "max:{$maxBatch}"],
            'patient_ids.*' => ['required', 'integer', 'min:1', 'distinct'],
            'start' => ['required', 'date_format:Y-m-d'],
            'end' => ['required', 'date_format:Y-m-d', 'after_or_equal:start'],
        ]);

        if (CarbonImmutable::parse($data['start'])->diffInDays(CarbonImmutable::parse($data['end'])) + 1 > $maxDays) {
            throw ValidationException::withMessages(['end' => "The date range may cover at most {$maxDays} days."]);
        }

        $patientIds = array_map('intval', $data['patient_ids']);
        $procedures = $production
            ->completedProceduresFor($this->filterFor($this->apiKey($request), $data['start'], $data['end']), $patientIds)
            ->groupBy(fn (object $procedure) => (int) $procedure->PatNum);

        $this->recordRows($request, $procedures->sum(fn ($rows) => $rows->count()));

        return PatientProceduresResource::collection(array_map(fn (int $id) => (object) [
            'patient_id' => $id,
            'procedures' => $procedures->get($id, collect()),
        ], $patientIds));
    }
}
