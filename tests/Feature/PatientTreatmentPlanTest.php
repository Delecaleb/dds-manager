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

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

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
        $this->assertTrue($scheduled->pluck('AptNum')->contains(102));
        $this->assertFalse($scheduled->pluck('AptNum')->contains(103));
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

    public function test_show_treatment_includes_completed_procedure_with_datetp(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        OdPatient::create([
            'PatNum' => 51,
            'LName' => 'Doe',
            'FName' => 'John',
        ]);

        // Procedure: Completed (C), but has DateTP originally planned
        OdProcedureLog::create([
            'ProcNum' => 301,
            'PatNum' => 51,
            'ProcStatus' => 'C',
            'ProcFee' => 350.00,
            'DateTP' => '2026-01-15',
            'ProcDate' => '2026-02-15',
            'AptNum' => 0,
        ]);

        $response = $this->getJson('/patients/51/treatment-plans');

        $response->assertOk();
        $data = $response->json();

        $this->assertCount(1, $data);
        $this->assertEquals('$ 350.00', $data[0]['amount']);
        $this->assertEquals('Completed', $data[0]['status']);
        $this->assertEquals('Jan 15, 2026', $data[0]['date_planned']);
        $this->assertEquals('Feb 15, 2026', $data[0]['date_completed']);
    }

    public function test_show_treatment_identifies_broken_appointment_status(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        OdPatient::create([
            'PatNum' => 52,
            'LName' => 'Taylor',
            'FName' => 'Alex',
        ]);

        OdAppointment::create([
            'AptNum' => 401,
            'PatNum' => 52,
            'AptStatus' => 5, // Broken
            'AptDateTime' => '2026-03-24 10:00:00',
        ]);

        OdProcedureLog::create([
            'ProcNum' => 401,
            'PatNum' => 52,
            'ProcStatus' => 'TP',
            'ProcFee' => 120.00,
            'DateTP' => '2026-02-17',
            'AptNum' => 401,
        ]);

        $response = $this->getJson('/patients/52/treatment-plans');

        $response->assertOk();
        $data = $response->json();

        $this->assertCount(1, $data);
        $this->assertEquals('Broken', $data[0]['status']);
        $this->assertEquals('Mar 24, 2026', $data[0]['date_scheduled']);
        $this->assertEquals('Feb 17, 2026', $data[0]['date_planned']);
    }
}
