<?php

namespace App\Domain\Scheduling;

use App\Domain\Support\MetricFilter;
use App\Enums\AppointmentStatus;

/**
 * What a bookings read asks for: the period and location (as a MetricFilter, like every
 * other domain read), plus the booking-specific narrowing the API offers.
 */
final class BookingQuery
{
    /**
     * @param  list<AppointmentStatus>  $statuses  empty = every status
     * @param  string|null  $updatedSince  'Y-m-d H:i:s'; only bookings changed after it
     * @param  bool  $withPatientContact  include name, email, phones and address
     */
    public function __construct(
        public readonly MetricFilter $filter,
        public readonly array $statuses = [],
        public readonly ?string $updatedSince = null,
        public readonly bool $withPatientContact = false,
    ) {}
}
