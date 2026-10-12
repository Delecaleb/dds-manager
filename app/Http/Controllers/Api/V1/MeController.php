<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Scheduling\BookingService;
use App\Domain\Support\ClinicRegistry;
use App\Http\Resources\Api\V1\BookingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** GET /api/v1/me — what the calling key is: its location, permissions and data freshness. */
class MeController extends ApiController
{
    public function __invoke(Request $request, ClinicRegistry $clinics, BookingService $bookings): JsonResponse
    {
        $key = $this->apiKey($request);
        $today = now()->toDateString();
        $officeId = (int) $key->office_id;
        $clinicNum = $key->clinic_num !== null ? (int) $key->clinic_num : null;

        $this->recordRows($request, 1);

        return response()->json(['data' => [
            'key' => [
                'name' => $key->name,
                'permissions' => array_values((array) $key->abilities),
                'rate_limit_per_minute' => (int) $key->rate_limit_per_minute,
                'expires_at' => $key->expires_at?->toIso8601String(),
            ],
            'location' => [
                'key' => $key->locationKey(),
                'name' => $clinics->labelFor($officeId, $clinicNum),
                'office_id' => $officeId,
                'clinic_num' => $clinicNum,
            ],
            // The newest OpenDental change held locally: data is a synced copy, not live.
            'latest_change_at' => BookingResource::dateTime($bookings->latestChange($this->filterFor($key, $today, $today)))?->format('Y-m-d\TH:i:s'),
        ]]);
    }
}
