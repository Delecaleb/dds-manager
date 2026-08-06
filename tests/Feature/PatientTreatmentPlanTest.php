<?php

namespace Tests\Feature;

use App\Models\OdAppointment;
use App\Models\OdPatient;
use App\Models\OdProcedureLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientTreatmentPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_appointment_scope_scheduled_only_includes_active_scheduled_status(): void
    {
        OdAppointment::create([
            'AptNum' => 101,
            'PatNum' => 1,
            'AptStatus' => 1, // Scheduled
            'AptDateTime' => '2026-08-10 10:00:00',
        ]);

        OdAppointment::create([
            'AptNum' => 102,
            'PatNum' => 2,
            'AptStatus' => 2, // Complete
            'AptDateTime' => '2026-08-10 11:00:00',
        ]);

        OdAppointment::create([
            'AptNum' => 103,
            'PatNum' => 3,
            'AptStatus' => 4, // ASAP (Scheduled)
            'AptDateTime' => '2026-08-10 12:00:00',
        ]);

        $scheduled = OdAppointment::scheduled()->get();

        $this->assertCount(2, $scheduled);
        $this->assertTrue($scheduled->pluck('AptNum')->contains(101));
        $this->assertTrue($scheduled->pluck('AptNum')->contains(103));
        $this->assertFalse($scheduled->pluck('AptNum')->contains(102));
    }

    public function test_show_treatment_returns_treatment_planned_procedures(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        OdPatient::create([
            'PatNum' => 50,
            'LName' => 'Smith',
            'FName' => 'Jane',
        ]);

        // Procedure 1: Treatment Planned (TP)
        OdProcedureLog::create([
            'ProcNum' => 201,
            'PatNum' => 50,
            'ProcStatus' => 'TP',
            'ProcFee' => 150.00,
            'AptNum' => 0,
        ]);

        // Procedure 2: Completed (C)
        OdProcedureLog::create([
            'ProcNum' => 202,
            'PatNum' => 50,
            'ProcStatus' => 'C',
            'ProcFee' => 200.00,
            'AptNum' => 0,
        ]);

        $response = $this->getJson('/patients/50/treatment-plans');

        $response->assertOk();
        $data = $response->json();

        $this->assertCount(1, $data);
        $this->assertEquals('$ 150.00', $data[0]['amount']);
        $this->assertEquals('Unscheduled', $data[0]['status']);
    }
}
