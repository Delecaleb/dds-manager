<?php

namespace App\Domain\Support;

use App\Models\Office;
use Illuminate\Http\Request;

/**
 * Immutable filter passed to every domain-service method.
 *
 * This object is the reason the architecture scales: adding a new filter dimension is a
 * new constructor property + builder here, and NO existing service method signature
 * changes. Methods take one MetricFilter, never a growing list of positional args.
 *
 * The `hygiene` dimension (null = all, true = hygiene only, false = doctor only) exists
 * because OpenDental reporting is pervasively segmented on od_procedures.IsHygiene.
 *
 * The `officeId` dimension scopes all reporting queries to the active office instance.
 *
 * @see refractor-blueprint/01-architecture.md  (Rule 1)
 */
final class MetricFilter
{
    public readonly int $officeId;

    /**
     * @param  string  $start  'Y-m-d'
     * @param  string  $end  'Y-m-d'
     * @param  int[]  $clinics  ClinicNum[]; empty = all clinics
     * @param  int[]  $providers  ProvNum[];  empty = all providers
     * @param  bool|null  $hygiene  null = all, true = hygiene only, false = non-hygiene
     * @param  int|null  $officeId  null = active office from session/default
     */
    public function __construct(
        public readonly string $start,
        public readonly string $end,
        public readonly array $clinics = [],
        public readonly array $providers = [],
        public readonly ?bool $hygiene = null,
        ?int $officeId = null,
    ) {
        $this->officeId = $officeId ?? Office::getActiveOfficeId();
    }

    public static function fromRequest(Request $request): self
    {
        $officeId = $request->filled('office_id')
            ? (int) $request->input('office_id')
            : Office::getActiveOfficeId();

        return new self(
            $request->input('start_date', now()->startOfMonth()->toDateString()),
            $request->input('end_date', now()->toDateString()),
            array_values(array_filter((array) $request->input('clinics', []))),
            array_values(array_filter((array) $request->input('providers', []))),
            null,
            $officeId
        );
    }

    public function forPeriod(string $start, string $end): self
    {
        return new self($start, $end, $this->clinics, $this->providers, $this->hygiene, $this->officeId);
    }

    /** @param int[] $clinics */
    public function withClinics(array $clinics): self
    {
        return new self($this->start, $this->end, $clinics, $this->providers, $this->hygiene, $this->officeId);
    }

    /** @param int[] $providers */
    public function withProviders(array $providers): self
    {
        return new self($this->start, $this->end, $this->clinics, $providers, $this->hygiene, $this->officeId);
    }

    public function withHygiene(?bool $hygiene): self
    {
        return new self($this->start, $this->end, $this->clinics, $this->providers, $hygiene, $this->officeId);
    }

    public function withOffice(int $officeId): self
    {
        return new self($this->start, $this->end, $this->clinics, $this->providers, $this->hygiene, $officeId);
    }

    /** The same period, shifted back one year (for diff-last-year comparisons). */
    public function lastYear(): self
    {
        [$start, $end] = DateRange::shiftYear($this->start, $this->end);

        return new self($start, $end, $this->clinics, $this->providers, $this->hygiene, $this->officeId);
    }

    /** Stable cache-key signature (for the future caching seam). */
    public function signature(): string
    {
        return md5(json_encode([
            $this->start,
            $this->end,
            $this->clinics,
            $this->providers,
            $this->hygiene,
            $this->officeId,
        ]));
    }
}
