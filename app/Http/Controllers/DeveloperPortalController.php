<?php

namespace App\Http\Controllers;

use App\Domain\DeveloperApi\ApiUsageService;
use App\Domain\Support\ClinicRegistry;
use App\Models\ApiKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Developer Portal: a developer creates and manages their own read-only API keys. Showing a
 * key again and regenerating it both require the developer's password, every time.
 *
 * A developer only ever sees their own keys; anyone else's is a 404. Super Admins oversee
 * every key from Administration → Developer API.
 */
class DeveloperPortalController extends Controller
{
    /** Requests shown on a key's activity page. */
    private const ACTIVITY_LIMIT = 200;

    public function __construct(
        private readonly ClinicRegistry $clinics,
        private readonly ApiUsageService $usage,
    ) {}

    public function index(Request $request): View
    {
        $locations = $this->clinics->locations();
        $keys = ApiKey::where('created_by', $request->user()->id)->withCount('requests')->latest()->get();
        $usageDays = ApiUsageService::period($request->query('days'));

        return view('developer.index', [
            'keys' => $keys,
            'usage' => $this->usage->daily($keys->modelKeys(), $usageDays),
            'usageDays' => $usageDays,
            'locations' => $locations,
            'locationNames' => $keys->mapWithKeys(fn (ApiKey $key) => [$key->id => $this->locationName($key)])->all(),
            'abilities' => ApiKey::ABILITIES,
            'maxRangeDays' => (int) config('developer_api.max_range_days'),
            'maxPerPage' => (int) config('developer_api.max_per_page'),
            'apiBase' => url('/api/v1'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $locations = $this->clinics->locations();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'location' => ['required', 'string', Rule::in(array_keys($locations))],
            'patient_contact' => ['nullable', 'boolean'],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ]);

        $abilities = [ApiKey::ABILITY_BOOKINGS];
        if ($request->boolean('patient_contact')) {
            $abilities[] = ApiKey::ABILITY_PATIENT_CONTACT;
        }

        [$key] = ApiKey::issue((new ApiKey([
            'name' => $data['name'],
            'abilities' => $abilities,
            // Not chosen by the developer: every key gets the configured allowance.
            'rate_limit_per_minute' => (int) config('developer_api.default_rate_limit'),
            'created_by' => $request->user()->id,
            'expires_at' => $data['expires_at'] ?? null,
        ]))->assignLocation($locations[$data['location']]));

        // The key is never put on the page here: the developer views it with "Copy key",
        // which asks for their password, like every other time it is shown.
        return redirect()->route('developer.index')
            ->with('success', "API key \"{$key->name}\" created for {$locations[$data['location']]->name}. Use Copy key to view it.");
    }

    public function show(Request $request, ApiKey $apiKey): View
    {
        $this->authorizeOwner($request, $apiKey);
        $usageDays = ApiUsageService::period($request->query('days'));

        return view('admin.developers.show', [
            'key' => $apiKey->load('creator:id,name'),
            'usage' => $this->usage->daily([$apiKey->id], $usageDays),
            'usageDays' => $usageDays,
            'locationName' => $this->locationName($apiKey),
            'requests' => $apiKey->requests()->latest('id')->limit(self::ACTIVITY_LIMIT)->get(),
            'activityLimit' => self::ACTIVITY_LIMIT,
            'backUrl' => route('developer.index'),
        ]);
    }

    /** Show the key again. Password required. */
    public function reveal(Request $request, ApiKey $apiKey): JsonResponse
    {
        $this->authorizeOwner($request, $apiKey);
        $request->validate(['password' => ['required', 'current_password']]);

        if (! $apiKey->isActive()) {
            return response()->json(['message' => 'This key is revoked or expired and cannot be shown.'], 422);
        }

        if ($apiKey->plainKey() === null) {
            return response()->json(['message' => 'This key was created before keys could be shown again. Regenerate it to get a new one.'], 422);
        }

        Log::info('Developer API key shown again.', ['api_key_id' => $apiKey->id, 'user_id' => $request->user()->id, 'ip' => $request->ip()]);

        return response()->json(['key' => $apiKey->plainKey()]);
    }

    /** Replace the key's value; the old value stops working immediately. Password required. */
    public function regenerate(Request $request, ApiKey $apiKey): JsonResponse
    {
        $this->authorizeOwner($request, $apiKey);
        $request->validate(['password' => ['required', 'current_password']]);

        if ($apiKey->revoked_at !== null) {
            return response()->json(['message' => 'A revoked key cannot be regenerated. Create a new key instead.'], 422);
        }

        $plain = $apiKey->regenerate();

        Log::info('Developer API key regenerated.', ['api_key_id' => $apiKey->id, 'user_id' => $request->user()->id, 'ip' => $request->ip()]);

        return response()->json(['key' => $plain, 'masked' => $apiKey->maskedKey()]);
    }

    public function destroy(Request $request, ApiKey $apiKey): RedirectResponse
    {
        $this->authorizeOwner($request, $apiKey);

        if ($apiKey->revoked_at === null) {
            $apiKey->revoke();
        }

        return redirect()->route('developer.index')->with('success', "API key \"{$apiKey->name}\" revoked.");
    }

    /** Someone else's key is reported as not found, so key ids cannot be probed. */
    private function authorizeOwner(Request $request, ApiKey $apiKey): void
    {
        abort_unless((int) $apiKey->created_by === (int) $request->user()->id, 404);
    }

    private function locationName(ApiKey $key): string
    {
        return $this->clinics->labelFor((int) $key->office_id, $key->clinic_num !== null ? (int) $key->clinic_num : null);
    }
}
