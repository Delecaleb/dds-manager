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
        $sql = MetricDefinitions::scheduledPatients('cnt');
        $this->assertStringContainsString('COUNT(DISTINCT PatNum', $sql);
        $this->assertStringContainsString('DATE(AptDateTime)', $sql);
        $this->assertStringContainsString('AS cnt', $sql);
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

    public function test_scheduled_patients_includes_status_1_and_2(): void
    {
        OdPatient::create([
            'PatNum' => 20,
            'LName' => 'Smith',
            'FName' => 'Jane',
        ]);

        DB::table('od_appointments')->insert([
            [
                'AptNum' => 201,
                'PatNum' => 20,
                'AptStatus' => 1, // Scheduled
                'AptDateTime' => '2026-07-05 09:00:00',
                'IsNewPatient' => 'false',
            ],
            [
                'AptNum' => 202,
                'PatNum' => 20,
                'AptStatus' => 2, // Complete
                'AptDateTime' => '2026-07-06 10:00:00',
                'IsNewPatient' => 'false',
            ],
            [
                'AptNum' => 203,
                'PatNum' => 20,
                'AptStatus' => 5, // Broken
                'AptDateTime' => '2026-07-07 11:00:00',
                'IsNewPatient' => 'false',
            ],
        ]);

        $count = (new OdAppointment)->scheduledPatients('2026-07-01', '2026-07-31');

        // Status 1 (2026-07-05) and Status 2 (2026-07-06) should be counted -> total 2
        $this->assertEquals(2, $count);
    }
}
