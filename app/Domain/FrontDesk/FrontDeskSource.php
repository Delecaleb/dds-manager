<?php

namespace App\Domain\FrontDesk;

use App\Domain\Support\Location;

/**
 * Where AI Front Desk data comes from. The pages depend on this contract only, so the
 * call platform (not chosen yet) plugs in as one more implementation.
 *
 * Shapes are plain arrays so Blade and JSON charts consume them directly. A series is
 * {labels: list<string>, series: list<{name, data: list<int|float|null>, color}>}.
 * Money is in dollars; rates are percentages (0–100); a null metric renders as "—".
 *
 * Production and appointment numbers are NOT owned here: once calls are linked to
 * OpenDental appointments, they come from the Production / Scheduling domain services.
 */
interface FrontDeskSource
{
    /** True once real call data flows; false for the empty and sample sources. */
    public function isLive(): bool;

    /** Short label for the header badge ("Sample data", "No call platform connected"). */
    public function label(): string;

    /**
     * Everything the Analytics and Overview pages draw, for the filter's locations.
     *
     * @return array{
     *   summary: array<string, int|float|null>,
     *   booked: array, callVolume: array, callsByHour: array,
     *   successRate: array, transferRate: array, intakeActions: array, closeRate: array,
     *   dailyOutcomes: array, transferIntents: array,
     *   failureReasons: list<array{label: string, count: int}>,
     *   bookingGaps: list<array{label: string, count: int}>,
     *   byOffice: list<array>
     * }
     */
    public function analytics(FrontDeskFilter $filter): array;

    /** @return list<array{key: string, name: string, status: string, calls: ?int, appts: ?int, production: ?float, address: ?string, lat: ?float, lng: ?float}> */
    public function offices(FrontDeskFilter $filter): array;

    /** @return list<array> newest first */
    public function calls(Location $location): array;

    public function call(Location $location, string $id): ?array;

    /** SMS threads, newest first. @return list<array> */
    public function conversations(Location $location): array;

    public function conversation(Location $location, string $id): ?array;

    /** Follow-ups the SMS agent handed to staff. @return list<array> */
    public function messageActions(Location $location): array;

    /** @return list<array> newest first */
    public function bookings(Location $location): array;

    public function booking(Location $location, string $id): ?array;

    /** @return list<array> */
    public function workflows(Location $location): array;

    /** Day schedule: {columns: list<string>, appointments: list<array>, appointmentTypes: list<string>}. */
    public function schedule(Location $location, string $date): array;

    /** Agent configuration for the Settings pages, keyed by section slug. */
    public function settings(Location $location): array;
}
