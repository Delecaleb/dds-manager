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

        app()->instance(PatientService::class, new class {
            public function all(): array
            {
                return [[
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
                ]];
            }
        });

        app()->instance(AppointmentService::class, new class {
            public function all(): array
            {
                return [];
            }
        });

        app()->instance(ProcedureService::class, new class {
            public function all(): array
            {
                return [];
            }
        });

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
