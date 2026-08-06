<?php

namespace Tests\Feature;

use App\Models\OdAppointment;
use App\Models\OdPatient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NewPatientScheduledTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_patients_scheduled_includes_appointments_with_is_new_patient_as_string_one(): void
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
                'AptStatus' => 1,
                'AptDateTime' => '2026-07-15 10:00:00',
                'IsNewPatient' => '1',
            ],
            [
                'AptNum' => 202,
                'PatNum' => 20,
                'AptStatus' => 1,
                'AptDateTime' => '2026-07-20 14:00:00',
                'IsNewPatient' => '0',
            ],
        ]);

        $query = OdAppointment::whereRaw("DATE(REPLACE(AptDateTime, 'T', ' ')) BETWEEN ? AND ?", ['2026-07-01', '2026-07-31'])
            ->scheduled()
            ->whereIn('IsNewPatient', ['1', 1, 'true', true]);

        $this->assertEquals(1, $query->count());
        $this->assertEquals(201, $query->first()->AptNum);
    }
}
