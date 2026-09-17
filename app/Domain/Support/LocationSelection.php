<?php

namespace App\Domain\Support;

/**
 * Immutable set of locations a report covers, resolved into per-office query scopes.
 *
 * Every OpenDental table is partitioned by office_id, so reports run once per office and
 * filter that office by ClinicNum. scopes() is that plan:
 *   [officeId => int[] ClinicNums]   (empty array = every clinic of the office)
 *
 * Build with ClinicRegistry::select() (validated, from the UI) or forOffice() (one office).
 */
final class LocationSelection
{
    /**
     * @param  Location[]  $locations  selected locations, in display order
     * @param  array<int, int[]>  $scopes  officeId => ClinicNum[] (empty = all clinics)
     */
    public function __construct(
        private readonly array $locations,
        private readonly array $scopes,
    ) {}

    /** @param int[] $clinics */
    public static function forOffice(int $officeId, array $clinics = []): self
    {
        return new self([], [$officeId => array_values(array_map('intval', $clinics))]);
    }

    /** @return array<int, int[]> */
    public function scopes(): array
    {
        return $this->scopes;
    }

    /** @return Location[] */
    public function locations(): array
    {
        return $this->locations;
    }

    /** @return string[] */
    public function keys(): array
    {
        return array_map(fn (Location $l) => $l->key(), $this->locations);
    }

    public function toParam(): string
    {
        return implode(',', $this->keys());
    }
}
