<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Patient\PatientMatchService;
use App\Http\Resources\Api\V1\PatientMatchResource;
use App\Http\Resources\Api\V1\PatientResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * POST /api/v1/patients/match and GET /api/v1/patients/{id} — read-only, for the key's location.
 * Matching is a POST so names and birth dates never reach URLs or the access log.
 */
class PatientController extends ApiController
{
    public function __construct(private readonly PatientMatchService $patients) {}

    public function match(Request $request): AnonymousResourceCollection
    {
        $max = (int) config('developer_api.max_batch');

        $data = $request->validate([
            'people' => ['required', 'array', 'min:1', "max:{$max}"],
            'people.*.reference' => ['nullable', 'string', 'max:64'],
            'people.*.first_name' => ['required', 'string', 'max:100'],
            'people.*.last_name' => ['required', 'string', 'max:100'],
            'people.*.birthdate' => ['required', 'date_format:Y-m-d'],
            'people.*.phone' => ['nullable', 'string', 'max:30'],
        ]);

        $today = now()->toDateString();
        $outcomes = $this->patients->match($this->filterFor($this->apiKey($request), $today, $today), $data['people']);

        $this->recordRows($request, count($outcomes));

        return PatientMatchResource::collection($outcomes);
    }

    public function show(Request $request, string $patient): PatientResource|JsonResponse
    {
        $today = now()->toDateString();
        $row = ctype_digit($patient)
            ? $this->patients->find($this->filterFor($this->apiKey($request), $today, $today), (int) $patient)
            : null;

        $this->recordRows($request, $row === null ? 0 : 1);

        return $row === null
            ? response()->json(['message' => 'Patient not found.'], 404)
            : new PatientResource($row);
    }
}
