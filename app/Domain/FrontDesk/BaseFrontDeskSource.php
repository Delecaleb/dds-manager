<?php

namespace App\Domain\FrontDesk;

use App\Domain\Support\ClinicRegistry;
use App\Domain\Support\Location;

/**
 * The analytics shape every source returns, defined once. A source only decides the
 * values: `series()` asks it for one data list per chart series, `summary()` for the
 * headline numbers. The empty source answers with nothing; the sample source with
 * generated demo numbers; the call platform source will answer with real ones.
 */
abstract class BaseFrontDeskSource implements FrontDeskSource
{
    public function __construct(protected readonly ClinicRegistry $clinics) {}

    /**
     * One data list for a chart series.
     *
     * @param  string  $key  stable series id, e.g. "callVolume.total"
     * @param  list<string>  $labels  the x-axis it must line up with
     * @return list<int|float|null> empty when there is no data
     */
    abstract protected function values(string $key, array $labels, FrontDeskFilter $filter): array;

    /** @return array<string, int|float|null> the headline numbers (see analytics()) */
    abstract protected function summary(FrontDeskFilter $filter): array;

    /** @return list<array{label: string, count: int}> */
    abstract protected function ranked(string $key, array $labels, FrontDeskFilter $filter): array;

    /** @return array{calls: ?int, appts: ?int, production: ?float, status: string} */
    abstract protected function officeTotals(Location $location, FrontDeskFilter $filter): array;

    public function analytics(FrontDeskFilter $filter): array
    {
        $days = $filter->dayLabels();
        $trend = $filter->trendLabels();
        $patients = [
            'existing' => ['Existing patient', FrontDeskTaxonomy::EXISTING_COLOR],
            'new' => ['New patient', FrontDeskTaxonomy::NEW_COLOR],
        ];

        $byOffice = $this->offices($filter);
        $total = array_sum(array_map(fn ($o) => (float) ($o['production'] ?? 0), $byOffice));
        foreach ($byOffice as &$office) {
            $office['share'] = $total > 0 && $office['production'] !== null
                ? round($office['production'] / $total * 100)
                : null;
        }
        unset($office);
        usort($byOffice, fn ($a, $b) => ($b['production'] ?? 0) <=> ($a['production'] ?? 0));

        return [
            'summary' => $this->summary($filter),
            'booked' => $this->chart('booked', $days, $filter, [
                'existing' => ['Existing patients', FrontDeskTaxonomy::EXISTING_COLOR],
                'new' => ['New patients', FrontDeskTaxonomy::NEW_COLOR],
            ]),
            'callVolume' => $this->chart('callVolume', $days, $filter, [
                'total' => ['Total calls', FrontDeskTaxonomy::NEW_COLOR],
                'resolved' => ['Resolved by AI', FrontDeskTaxonomy::EXISTING_COLOR],
            ]),
            'callsByHour' => $this->chart('callsByHour', FrontDeskFilter::hourLabels(), $filter, [
                'existing' => ['Existing patients', FrontDeskTaxonomy::EXISTING_COLOR],
                'new' => ['New patients', FrontDeskTaxonomy::NEW_COLOR],
            ]),
            'successRate' => $this->chart('successRate', $trend, $filter, $patients),
            'transferRate' => $this->chart('transferRate', $trend, $filter, $patients),
            'closeRate' => $this->chart('closeRate', $trend, $filter, $patients),
            'intakeActions' => $this->chart('intakeActions', $trend, $filter, [
                'booked' => ['Booked', '#6b9bd1'],
                'cancelled' => ['Cancelled', '#b39ddb'],
                'confirmed' => ['Confirmed', '#7fb88a'],
                'rescheduled' => ['Rescheduled', '#e0a863'],
            ]),
            'dailyOutcomes' => $this->chart('dailyOutcomes', $days, $filter, array_map(
                fn ($o) => [$o['label'], $o['color']], FrontDeskTaxonomy::OUTCOMES,
            )),
            'transferIntents' => $this->chart('transferIntents', $days, $filter, array_map(
                fn ($i) => [$i['label'], $i['color']], FrontDeskTaxonomy::INTENTS,
            )),
            'failureReasons' => $this->ranked('failureReasons', array_values(FrontDeskTaxonomy::FAILURE_REASONS), $filter),
            'bookingGaps' => $this->ranked('bookingGaps', array_values(FrontDeskTaxonomy::FAILURE_REASONS), $filter),
            'byOffice' => $byOffice,
        ];
    }

    public function offices(FrontDeskFilter $filter): array
    {
        $rows = [];
        foreach ($this->clinics->locations() as $key => $location) {
            if ($filter->locations !== [] && ! in_array($key, $filter->locations, true)) {
                continue;
            }
            $rows[] = [
                'key' => $key,
                'name' => $location->name,
                'address' => null,
                'lat' => null,
                'lng' => null,
            ] + $this->officeTotals($location, $filter);
        }

        return $rows;
    }

    /**
     * @param  array<string, array{0: string, 1: string}>  $series  key => [name, colour]
     * @return array{labels: list<string>, series: list<array{name: string, data: list<int|float|null>, color: string}>}
     */
    private function chart(string $key, array $labels, FrontDeskFilter $filter, array $series): array
    {
        $out = [];
        foreach ($series as $seriesKey => [$name, $color]) {
            $out[] = ['name' => $name, 'color' => $color, 'data' => $this->values("{$key}.{$seriesKey}", $labels, $filter)];
        }

        return ['labels' => $labels, 'series' => $out];
    }
}
