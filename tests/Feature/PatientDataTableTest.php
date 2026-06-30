<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OpenDental\AppointmentService;
use App\Services\OpenDental\PatientService;
use App\Services\OpenDental\ProcedureService;
use Tests\TestCase;

class PatientDataTableTest extends TestCase
{
    public function test_patients_data_endpoint_returns_datatables_payload(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $patientsMock = $this->createMock(PatientService::class);
        $patientsMock->method('all')->willReturn([
            [
                'PatNum' => 1,
                'FName' => 'Ada',
                'LName' => 'Lovelace',
                'WirelessPhone' => '123',
                'Email' => 'ada@example.com',
                'Birthdate' => '2001-01-01',
                'Address' => '1 Main',
                'Address2' => '',
                'City' => 'Detroit',
                'Zip' => '48201',
                'State' => 'MI',
            ]
        ]);
        app()->instance(PatientService::class, $patientsMock);

        $appointmentsMock = $this->createMock(AppointmentService::class);
        $appointmentsMock->method('all')->willReturn([]);
        app()->instance(AppointmentService::class, $appointmentsMock);

        $proceduresMock = $this->createMock(ProcedureService::class);
        $proceduresMock->method('all')->willReturn([]);
        app()->instance(ProcedureService::class, $proceduresMock);

        $response = $this->getJson(route('patients.data'));

        $response->assertOk()
            ->assertJsonStructure([
                'draw',
                'recordsTotal',
                'recordsFiltered',
                'data' => [
                    ['patient_id', 'name', 'phone', 'email', 'birthdate', 'address', 'city', 'zip', 'state', 'first_visit', 'last_visit', 'lifetime_production'],
                ],
            ]);
    }
}
