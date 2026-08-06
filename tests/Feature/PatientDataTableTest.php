<?php

namespace Tests\Feature;

use App\Models\OdPatient;
use App\Models\User;
use App\Services\OpenDental\AppointmentService;
use App\Services\OpenDental\ProcedureService;
use Tests\TestCase;

class PatientDataTableTest extends TestCase
{
    public function test_patients_data_endpoint_returns_datatables_payload(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        OdPatient::create([
            'office_id' => 1,
            'PatNum' => 1,
            'FName' => 'Ada',
            'LName' => 'Lovelace',
            'WirelessPhone' => '123',
            'Email' => 'ada@example.com',
            'Birthdate' => '2001-01-01',
            'Address' => '1 Main',
            'City' => 'Detroit',
            'Zip' => '48201',
            'State' => 'MI',
        ]);

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
                    ['patient_id', 'name', 'mobile_phone', 'email', 'birthdate', 'address', 'city', 'zip', 'state', 'first_visit', 'lifetime_value_production'],
                ],
            ]);
    }
}
