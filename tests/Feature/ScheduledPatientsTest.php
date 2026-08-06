<?php

namespace Tests\Feature;

use App\Helpers\MetricDefinitions;
use App\Models\OdAppointment;
use App\Models\OdPatient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ScheduledPatientsTest extends TestCase
{
    use RefreshDatabase;

    public function test_metric_definitions_scheduled_patients_query_expression(): void
    {
        $driver = DB::connection()->getDriverName();
        $expected = $driver === 'sqlite'
            ? "COUNT(DISTINCT PatNum || '|' || DATE(AptDateTime)) AS cnt"
            : 'COUNT(DISTINCT PatNum, DATE(AptDateTime)) AS cnt';

        $sql = MetricDefinitions::scheduledPatients('cnt');
        $this->assertEquals($expected, $sql);
    }

    public function test_od_appointment_scheduled_patients_counts_distinct_patient_days(): void
    {
        // Seed 1 patient with 3 appointments on 2 dates (2 on same day, 1 on another)
        OdPatient::create([
            'PatNum' => 10,
            'LName' => 'Doe',
            'FName' => 'John',
        ]);

        DB::table('od_appointments')->insert([
            [
                'AptNum' => 101,
                'PatNum' => 10,
                'AptStatus' => 1,
                'AptDateTime' => '2026-07-02 09:00:00',
                'IsNewPatient' => 'false',
            ],
            [
                'AptNum' => 102,
                'PatNum' => 10,
                'AptStatus' => 1,
                'AptDateTime' => '2026-07-02 11:00:00',
                'IsNewPatient' => 'false',
            ],
            [
                'AptNum' => 103,
                'PatNum' => 10,
                'AptStatus' => 1,
                'AptDateTime' => '2026-07-10 14:00:00',
                'IsNewPatient' => 'false',
            ],
        ]);

        $count = (new OdAppointment)->scheduledPatients('2026-07-01', '2026-07-31');

        // Total raw appointment rows = 3, but distinct (PatNum, DATE) = 2
        $this->assertEquals(2, $count);
    }
}
