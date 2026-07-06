<?php

namespace App\Services\OpenDental;

use App\Repositories\AppointmentRepository;
use App\Transformers\CalendarEventTransformer;
use App\Transformers\CalendarResourceTransformer;

class CalendarService
{
    public function __construct(
        protected AppointmentRepository $appointments
    ) {
    }

    public function events($start, $end)
    {
        $collection = $this->appointments->getAppointmentsByDateRange($start, $end);

        return $collection->map(fn($apt) => CalendarEventTransformer::transform($apt))->toArray();
    }

    public function resources($start, $end)
    {
        $collection = $this->appointments->getAppointmentsByDateRange($start, $end);

        return CalendarResourceTransformer::transform($collection);
    }
}
