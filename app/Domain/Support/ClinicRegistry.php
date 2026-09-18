<?php

namespace App\Domain\Support;

use App\Models\Office;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The single source of truth for clinic (office) identity: ClinicNum -> display name.
 *
 * Multi-office readiness: the whole app filters by ClinicNum via MetricFilter already;
 * this registry provides names for the active Office location.
 */
class ClinicRegistry
{
    /** @var array<int, array<int, string>> lazily-built officeId => (ClinicNum => name) */
    private array $maps = [];

    /** @var array<string, Location>|null lazily-built reportable locations across active offices */
    private ?array $locations = null;

    public function flush(): void
    {
        $this->maps = [];
        $this->locations = null;
    }

    /** @return array<int,string> ClinicNum => display name for the given office */
    public function all(?int $officeId = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();

        if (isset($this->maps[$officeId])) {
            return $this->maps[$officeId];
        }

        $activeOffice = Office::find($officeId);
        $primaryName = $activeOffice?->name ?: config('clinics.primary_name', 'Main Office');

        $map = [];

        if (Schema::hasTable('od_clinics')) {
            $query = DB::table('od_clinics');
            if (Schema::hasColumn('od_clinics', 'office_id')) {
                $query->where('office_id', $officeId);
            }

            $rows = $query->orderBy('ItemOrder')->orderBy('ClinicNum')->get(['ClinicNum', 'Description', 'Abbr']);
            foreach ($rows as $r) {
                $name = trim((string) ($r->Description ?: $r->Abbr));
                if ($name !== '') {
                    $map[(int) $r->ClinicNum] = $name;
                }
            }
        }

        if (empty($map)) {
            $map = [0 => $primaryName];
        }

        return $this->maps[$officeId] = $map;
    }

    /** Display name for a clinic; falls back to "Location N" for unknown clinics. */
    public function name(int $clinicNum, ?int $officeId = null): string
    {
        return $this->all($officeId)[$clinicNum] ?? ('Location '.$clinicNum);
    }

    /** @return int[] all known ClinicNums */
    public function ids(?int $officeId = null): array
    {
        return array_keys($this->all($officeId));
    }

    public function exists(int $clinicNum, ?int $officeId = null): bool
    {
        return array_key_exists($clinicNum, $this->all($officeId));
    }

    /** True once real offices are configured (od_clinics synced with >1 clinic). */
    public function isMultiOffice(?int $officeId = null): bool
    {
        return count($this->all($officeId)) > 1;
    }

    public function hasClinics(?int $officeId = null): bool
    {
        return $this->isMultiOffice($officeId);
    }

    /**
     * Get the active clinic number for the specified office.
     * Persisted per office in session('active_clinic_id_{officeId}').
     */
    public function getActiveClinicNum(?int $officeId = null): ?int
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        if (! $officeId) {
            return null;
        }

        $sessionKey = "active_clinic_id_{$officeId}";
        if (session()->has($sessionKey)) {
            $saved = session($sessionKey);
            $clinics = $this->all($officeId);
            if (is_numeric($saved) && array_key_exists((int) $saved, $clinics)) {
                return (int) $saved;
            }
        }

        return null;
    }

    /**
     * Set and persist the active clinic number for an office.
     */
    public function setActiveClinicNum(int $clinicNum, ?int $officeId = null): void
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        if ($officeId) {
            session(["active_clinic_id_{$officeId}" => $clinicNum]);
        }
    }

    /**
     * Every reportable location across active offices, keyed by Location::key().
     * An office is one location; a multi-clinic office contributes one location per clinic.
     *
     * @return array<string, Location>
     */
    public function locations(): array
    {
        if ($this->locations !== null) {
            return $this->locations;
        }

        $locations = [];
        foreach (Office::where('is_active', true)->orderBy('name')->get(['id', 'name']) as $office) {
            $clinics = $this->all($office->id);

            if (count($clinics) > 1) {
                foreach ($clinics as $clinicNum => $clinicName) {
                    $displayName = $clinicName;
                    if (! empty($office->name) && ! str_contains(strtolower($clinicName), strtolower($office->name))) {
                        $displayName = "{$office->name} - {$clinicName}";
                    }
                    $location = new Location($office->id, $clinicNum, $displayName);
                    $locations[$location->key()] = $location;
                }
            } else {
                $location = new Location($office->id, null, $office->name);
                $locations[$location->key()] = $location;
            }
        }

        return $this->locations = $locations;
    }

    /**
     * The location a synced row belongs to. A single-clinic office's registered clinic is
     * the office itself; any other ClinicNum (e.g. unassigned 0 in a multi-clinic office)
     * is reported as its own location so its numbers are never merged into a real clinic.
     */
    public function locationFor(int $officeId, int $clinicNum): Location
    {
        $clinics = $this->all($officeId);

        if (count($clinics) <= 1 && array_key_exists($clinicNum, $clinics)) {
            return $this->locations()[(string) $officeId]
                ?? new Location($officeId, null, $clinics[$clinicNum]);
        }

        return $this->locations()[Location::keyFor($officeId, $clinicNum)]
            ?? new Location($officeId, $clinicNum, $this->name($clinicNum, $officeId));
    }

    /**
     * Resolve a comma-separated list of location keys (the `locations` request param).
     * Unknown keys are dropped. "all" selects every location. Missing or fully invalid
     * input falls back to the active office (and its active clinic, if one is set).
     */
    public function select(?string $param): LocationSelection
    {
        $all = $this->locations();
        $tokens = array_values(array_filter(array_map('trim', explode(',', (string) $param)), 'strlen'));

        $selected = [];
        if (in_array('all', $tokens, true)) {
            $selected = $all;
        } else {
            foreach ($tokens as $token) {
                if (isset($all[$token])) {
                    $selected[$token] = $all[$token];

                    continue;
                }
                // A bare office id also selects every clinic of a multi-clinic office.
                foreach ($all as $key => $location) {
                    if (ctype_digit($token) && $location->officeId === (int) $token) {
                        $selected[$key] = $location;
                    }
                }
            }
        }

        if ($selected === []) {
            $selected = $this->defaultLocations($all);
        }

        // Keep registry display order regardless of the order keys were passed in.
        $ordered = array_values(array_intersect_key($all, $selected));

        return new LocationSelection($ordered, $this->scopesFor($ordered));
    }

    /**
     * @param  array<string, Location>  $all
     * @return array<string, Location>
     */
    private function defaultLocations(array $all): array
    {
        $officeId = Office::getActiveOfficeId();
        if ($officeId === null) {
            return array_slice($all, 0, 1, true);
        }

        $activeClinic = $this->getActiveClinicNum($officeId);
        $key = Location::keyFor($officeId, $activeClinic);
        if ($activeClinic !== null && isset($all[$key])) {
            return [$key => $all[$key]];
        }

        return array_filter($all, fn (Location $l) => $l->officeId === $officeId);
    }

    /**
     * Collapse locations into per-office ClinicNum filters. When every clinic of an office is
     * selected the filter is dropped (all clinics), which also keeps unassigned ClinicNum rows.
     *
     * @param  Location[]  $locations
     * @return array<int, int[]>
     */
    private function scopesFor(array $locations): array
    {
        $scopes = [];
        foreach ($locations as $location) {
            $scopes[$location->officeId] ??= [];
            if ($location->clinicNum !== null) {
                $scopes[$location->officeId][] = $location->clinicNum;
            }
        }

        foreach ($scopes as $officeId => $clinics) {
            if ($clinics !== [] && count($clinics) === count($this->all($officeId))) {
                $scopes[$officeId] = [];
            }
        }

        return $scopes;
    }
}
