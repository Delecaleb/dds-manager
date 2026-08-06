<?php

namespace Tests\Unit;

use App\Models\OdAppointment;
use App\Transformers\CalendarEventTransformer;
use Tests\TestCase;

class CalendarEventTransformerTest extends TestCase
{
    public function test_transform_normalizes_procedure_and_note_whitespace(): void
    {
        $appointment = new OdAppointment([
            'AptNum' => 123,
            'PatNum' => 456,
            'AptStatus' => 1,
            'Pattern' => '//',
            'Op' => 2,
            'AptDateTime' => '2026-07-28 10:00:00',
            'ProcDescript' => ' PeriodicX, ProphyCh  ',
            'Note' => ' PeriodicX, ProphyCh 07/28/2026 ins active ',
        ]);

        $transformed = CalendarEventTransformer::transform($appointment);

        $this->assertEquals('PeriodicX, ProphyCh', $transformed['procedure']);
        $this->assertEquals('PeriodicX, ProphyCh 07/28/2026 ins active', $transformed['note']);
    }
}
