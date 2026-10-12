<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Scheduling\BookingQuery;
use App\Domain\Scheduling\BookingService;
use App\Enums\AppointmentStatus;
use App\Http\Resources\Api\V1\BookingResource;
use App\Models\ApiKey;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

/** GET /api/v1/bookings and /api/v1/bookings/{id} — read-only, for the key's location. */
class BookingController extends ApiController
{
    private const DATE_TIME_FORMATS = 'Y-m-d H:i:s,Y-m-d\TH:i:s';

    public function __construct(private readonly BookingService $bookings) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $maxDays = (int) config('developer_api.max_range_days');
        $maxPerPage = (int) config('developer_api.max_per_page');

        $data = $request->validate([
            'start' => ['required', 'date_format:Y-m-d'],
            'end' => ['required', 'date_format:Y-m-d', 'after_or_equal:start'],
            'status' => ['nullable', 'string', 'max:255'],
            'provider' => ['nullable', 'string', 'max:255', 'regex:/^\d+(,\d+)*$/'],
            'updated_since' => ['nullable', 'date_format:'.self::DATE_TIME_FORMATS],
            'per_page' => ['nullable', 'integer', 'min:1', "max:{$maxPerPage}"],
            'cursor' => ['nullable', 'string'],
        ]);

        $start = CarbonImmutable::parse($data['start']);
        if ($start->diffInDays(CarbonImmutable::parse($data['end'])) + 1 > $maxDays) {
            throw ValidationException::withMessages(['end' => "The date range may cover at most {$maxDays} days."]);
        }

        $key = $this->apiKey($request);
        $filter = $this->filterFor($key, $data['start'], $data['end']);

        if (filled($data['provider'] ?? null)) {
            $filter = $filter->withProviders(array_map('intval', explode(',', $data['provider'])));
        }

        $page = $this->bookings->paginate(new BookingQuery(
            $filter,
            $this->statuses($data['status'] ?? null),
            filled($data['updated_since'] ?? null) ? str_replace('T', ' ', $data['updated_since']) : null,
            $key->allows(ApiKey::ABILITY_PATIENT_CONTACT),
        ), (int) ($data['per_page'] ?? config('developer_api.per_page')))->withQueryString();

        $this->recordRows($request, $page->count());

        return BookingResource::collection($page);
    }

    public function show(Request $request, string $booking): BookingResource|JsonResponse
    {
        $key = $this->apiKey($request);
        $today = now()->toDateString();

        $row = ctype_digit($booking)
            ? $this->bookings->find(new BookingQuery(
                $this->filterFor($key, $today, $today),
                withPatientContact: $key->allows(ApiKey::ABILITY_PATIENT_CONTACT),
            ), (int) $booking)
            : null;

        if ($row === null) {
            $this->recordRows($request, 0);

            return response()->json(['message' => 'Booking not found.'], 404);
        }

        $this->recordRows($request, 1);

        return new BookingResource($row);
    }

    /**
     * "scheduled,broken" → statuses; an unknown name is a 422 listing the valid ones.
     *
     * @return list<AppointmentStatus>
     */
    private function statuses(?string $raw): array
    {
        if (blank($raw)) {
            return [];
        }

        $statuses = [];
        foreach (array_filter(array_map('trim', explode(',', $raw))) as $slug) {
            $status = AppointmentStatus::fromSlug($slug);

            if ($status === null) {
                $valid = implode(', ', array_map(fn (AppointmentStatus $s) => $s->slug(), AppointmentStatus::cases()));

                throw ValidationException::withMessages(['status' => "Unknown status \"{$slug}\". Valid statuses: {$valid}."]);
            }

            $statuses[] = $status;
        }

        return $statuses;
    }
}
