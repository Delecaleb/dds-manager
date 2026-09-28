<?php

namespace App\Domain\FrontDesk;

use App\Domain\Support\MetricFilter;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * The AI Front Desk filter: a period and a location set (carried by MetricFilter, so the
 * Production / Scheduling numbers on these pages use the same filter as the rest of the
 * app), plus the call-only dimensions the Arini-style pages filter by.
 */
final class FrontDeskFilter
{
    public const RANGES = ['7d' => '7D', '30d' => '30D', '3m' => '3M'];

    public const PATIENTS = ['all' => 'All patients', 'new' => 'New', 'existing' => 'Existing'];

    public const DIRECTIONS = ['all' => 'All calls', 'inbound' => 'Inbound', 'outbound' => 'Outbound'];

    public const GRANULARITIES = ['daily' => 'Daily', 'weekly' => 'Weekly'];

    /**
     * @param  list<string>  $locations  ClinicRegistry location keys; empty = every location
     */
    public function __construct(
        public readonly MetricFilter $metric,
        public readonly string $range = '30d',
        public readonly array $locations = [],
        public readonly string $patients = 'all',
        public readonly string $direction = 'all',
        public readonly string $granularity = 'weekly',
    ) {}

    public static function fromRequest(Request $request, array $locations = []): self
    {
        $range = self::pick($request->query('range'), self::RANGES, '30d');
        $end = CarbonImmutable::today();
        $start = match ($range) {
            '7d' => $end->subDays(6),
            '3m' => $end->subMonthsNoOverflow(3)->addDay(),
            default => $end->subDays(29),
        };

        return new self(
            metric: new MetricFilter($start->toDateString(), $end->toDateString()),
            range: $range,
            locations: $locations,
            patients: self::pick($request->query('patients'), self::PATIENTS, 'all'),
            direction: self::pick($request->query('direction'), self::DIRECTIONS, 'all'),
            granularity: self::pick($request->query('granularity'), self::GRANULARITIES, 'weekly'),
        );
    }

    /** Human label for the period, e.g. "last 30 days". */
    public function periodLabel(): string
    {
        return match ($this->range) {
            '7d' => 'last 7 days',
            '3m' => 'last 3 months',
            default => 'last 30 days',
        };
    }

    /** @return list<CarbonImmutable> every day in the period */
    public function days(): array
    {
        $days = [];
        $day = CarbonImmutable::parse($this->metric->start);
        $end = CarbonImmutable::parse($this->metric->end);
        for (; $day->lte($end); $day = $day->addDay()) {
            $days[] = $day;
        }

        return $days;
    }

    /** @return list<string> x-axis labels, one per day ("09/14") */
    public function dayLabels(): array
    {
        return array_map(fn (CarbonImmutable $d) => $d->format('m/d'), $this->days());
    }

    /** @return list<string> x-axis labels at the trend granularity ("09-14" per week start, or per day) */
    public function trendLabels(): array
    {
        $days = $this->days();
        if ($this->granularity === 'weekly') {
            $days = array_values(array_filter($days, fn (CarbonImmutable $d) => $d->isMonday() || $d->eq($days[0])));
        }

        return array_map(fn (CarbonImmutable $d) => $d->format('m-d'), $days);
    }

    /** @return list<string> 24 hour-of-day labels ("12a" … "11p") */
    public static function hourLabels(): array
    {
        return array_map(fn (int $h) => ($h % 12 === 0 ? 12 : $h % 12).($h < 12 ? 'a' : 'p'), range(0, 23));
    }

    /** @param array<string, string> $allowed */
    private static function pick(mixed $value, array $allowed, string $default): string
    {
        return is_string($value) && array_key_exists($value, $allowed) ? $value : $default;
    }
}
