<?php

namespace App\Domain\Support;

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
 * @see refractor-blueprint/01-architecture.md  (Rule 1)
 */
final class MetricFilter
{
    /**
     * @param  string  $start  'Y-m-d'
     * @param  string  $end  'Y-m-d'
     * @param  int[]  $clinics  ClinicNum[]; empty = all clinics
     * @param  int[]  $providers  ProvNum[];  empty = all providers
     * @param  bool|null  $hygiene  null = all, true = hygiene only, false = non-hygiene
     */
    public function __construct(
        public readonly string $start,
        public readonly string $end,
        public readonly array $clinics = [],
        public readonly array $providers = [],
        public readonly ?bool $hygiene = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            $request->input('start_date', now()->startOfMonth()->toDateString()),
            $request->input('end_date', now()->toDateString()),
            array_values(array_filter((array) $request->input('clinics', []))),
            array_values(array_filter((array) $request->input('providers', []))),
        );
    }

    public function forPeriod(string $start, string $end): self
    {
        return new self($start, $end, $this->clinics, $this->providers, $this->hygiene);
    }

    /** @param int[] $clinics */
    public function withClinics(array $clinics): self
    {
        return new self($this->start, $this->end, $clinics, $this->providers, $this->hygiene);
    }

    /** @param int[] $providers */
    public function withProviders(array $providers): self
    {
        return new self($this->start, $this->end, $this->clinics, $providers, $this->hygiene);
    }

    public function withHygiene(?bool $hygiene): self
    {
        return new self($this->start, $this->end, $this->clinics, $this->providers, $hygiene);
    }

    /** The same period, shifted back one year (for diff-last-year comparisons). */
    public function lastYear(): self
    {
        [$start, $end] = DateRange::shiftYear($this->start, $this->end);

        return new self($start, $end, $this->clinics, $this->providers, $this->hygiene);
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
        ]));
    }
}
