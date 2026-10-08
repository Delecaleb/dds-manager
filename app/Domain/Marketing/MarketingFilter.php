<?php

namespace App\Domain\Marketing;

use App\Domain\Support\ClinicRegistry;
use App\Domain\Support\Location;
use App\Domain\Support\LocationSelection;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * The one filter every Growth Engine (Marketing) service method takes: a period and the
 * locations the user picked, resolved the same way the rest of the app resolves them
 * (ClinicRegistry::select, so the choice is shared with the analytics shell and persists
 * in the session).
 *
 * A location is an office, or one clinic of a multi-clinic office. Marketing rows (sites,
 * ad accounts, campaigns) carry office_id + clinic_num; see LocationScope for how a
 * selection is turned into a query constraint.
 */
final class MarketingFilter
{
    public const DEFAULT_DAYS = 30;

    /**
     * @param  string  $start  'Y-m-d'
     * @param  string  $end  'Y-m-d'
     * @param  bool  $allLocations  true when the selection covers every reportable location
     * @param  int|null  $siteId  narrow to one tracked site (Websites page)
     */
    public function __construct(
        public readonly string $start,
        public readonly string $end,
        public readonly LocationSelection $selection,
        public readonly bool $allLocations,
        public readonly ?int $siteId = null,
    ) {}

    /**
     * Read `locations`, `start_date`, `end_date` and `site` off the request. With $persist
     * the location choice is remembered in the session, as the analytics shell does.
     */
    public static function fromRequest(Request $request, ClinicRegistry $clinics, bool $persist = true): self
    {
        $selection = $clinics->select($request->input('locations'), $persist);
        [$start, $end] = self::period($request);

        return new self(
            $start,
            $end,
            $selection,
            count($selection->locations()) >= count($clinics->locations()),
            $request->filled('site') ? (int) $request->input('site') : null,
        );
    }

    /** Build directly (jobs, tests): a period and an explicit selection. */
    public static function for(string $start, string $end, LocationSelection $selection, ClinicRegistry $clinics): self
    {
        return new self($start, $end, $selection, count($selection->locations()) >= count($clinics->locations()));
    }

    /** @return array<int, int[]> officeId => ClinicNum[] (empty = every clinic of the office) */
    public function scopes(): array
    {
        return $this->selection->scopes();
    }

    /** @return Location[] */
    public function locations(): array
    {
        return $this->selection->locations();
    }

    /** @return string[] location keys, for the picker and for URLs */
    public function keys(): array
    {
        return $this->selection->keys();
    }

    public function withSite(?int $siteId): self
    {
        return new self($this->start, $this->end, $this->selection, $this->allLocations, $siteId);
    }

    /** "All locations", one location's name, or "3 locations". */
    public function locationLabel(): string
    {
        $locations = $this->locations();

        return match (true) {
            $this->allLocations => 'All locations',
            count($locations) === 1 => $locations[0]->name,
            default => count($locations).' locations',
        };
    }

    /**
     * The query string that reproduces this filter, so links between pages keep it.
     *
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    public function query(array $extra = []): array
    {
        return array_filter(array_merge([
            'locations' => $this->allLocations ? 'all' : implode(',', $this->keys()),
            'start_date' => $this->start,
            'end_date' => $this->end,
            'site' => $this->siteId,
        ], $extra), fn ($v) => $v !== null && $v !== '');
    }

    /** @return array{0: string, 1: string} */
    private static function period(Request $request): array
    {
        $today = CarbonImmutable::today();
        $parse = fn (mixed $value, CarbonImmutable $default) => is_string($value) && $value !== ''
            ? rescue(fn () => CarbonImmutable::parse($value), $default, false)
            : $default;

        $start = $parse($request->input('start_date'), $today->subDays(self::DEFAULT_DAYS - 1));
        $end = $parse($request->input('end_date'), $today);

        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        return [$start->toDateString(), $end->toDateString()];
    }
}
