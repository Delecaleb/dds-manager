<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Support\ClinicRegistry;
use App\Http\Resources\Api\V1\ClinicResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/** GET /api/v1/clinics — name and contact details of the clinics in the key's location. */
class ClinicController extends ApiController
{
    public function index(Request $request, ClinicRegistry $clinics): AnonymousResourceCollection
    {
        $key = $this->apiKey($request);
        $rows = $clinics->contacts((int) $key->office_id, $key->clinic_num !== null ? [(int) $key->clinic_num] : []);

        $this->recordRows($request, $rows->count());

        return ClinicResource::collection($rows);
    }
}
