<?php

namespace App\Http\Controllers\Admin;

use App\Domain\DeveloperApi\ApiUsageService;
use App\Domain\Support\ClinicRegistry;
use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Administration → Developer API: oversight of every key, whoever owns it. Developers create
 * and manage their own keys in the Developer Portal; admins give a developer access by
 * creating their login (role Developer, module Developer Portal) under User & Access.
 */
class DeveloperApiController extends Controller
{
    /** Requests shown on a key's activity page. */
    private const ACTIVITY_LIMIT = 200;

    public function __construct(
        private readonly ClinicRegistry $clinics,
        private readonly ApiUsageService $usage,
    ) {}

    public function index(Request $request): View
    {
        $usageDays = ApiUsageService::period($request->query('days'));
        $locations = $this->clinics->locations();
        $keys = ApiKey::with('creator:id,name,email')->withCount('requests')->latest()->get();

        // Keys under their location, in the registry's order; keys whose location is gone last.
        $byLocation = [];
        foreach ($locations as $locationKey => $location) {
            $byLocation[$locationKey] = ['location' => $location, 'keys' => []];
        }
        $orphaned = [];
        foreach ($keys as $key) {
            isset($byLocation[$key->locationKey()])
                ? $byLocation[$key->locationKey()]['keys'][] = $key
                : $orphaned[] = $key;
        }

        return view('admin.developers.index', [
            'byLocation' => $byLocation,
            'orphaned' => $orphaned,
            'usage' => $this->usage->daily(null, $usageDays),
            'usageDays' => $usageDays,
            'apiBase' => url('/api/v1'),
        ]);
    }

    public function show(Request $request, ApiKey $apiKey): View
    {
        $usageDays = ApiUsageService::period($request->query('days'));

        return view('admin.developers.show', [
            'key' => $apiKey->load('creator:id,name'),
            'usage' => $this->usage->daily([$apiKey->id], $usageDays),
            'usageDays' => $usageDays,
            'locationName' => $this->clinics->labelFor((int) $apiKey->office_id, $apiKey->clinic_num !== null ? (int) $apiKey->clinic_num : null),
            'requests' => $apiKey->requests()->latest('id')->limit(self::ACTIVITY_LIMIT)->get(),
            'activityLimit' => self::ACTIVITY_LIMIT,
            'backUrl' => route('admin.developers.index'),
        ]);
    }

    /** Revoked keys stop working at once; they and their request history are kept for audit. */
    public function destroy(ApiKey $apiKey): RedirectResponse
    {
        if ($apiKey->revoked_at === null) {
            $apiKey->revoke();
        }

        return redirect()->route('admin.developers.index')->with('success', "API key \"{$apiKey->name}\" revoked.");
    }
}
