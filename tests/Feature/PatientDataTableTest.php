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

    public function test_patients_data_endpoint_supports_search_and_pagination(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        for ($i = 1; $i <= 25; $i++) {
            OdPatient::create([
                'office_id' => 1,
                'PatNum' => $i,
                'FName' => "Patient{$i}",
                'LName' => ($i === 10) ? 'SpecialSearchTarget' : "LastName{$i}",
                'WirelessPhone' => "555-000-{$i}",
                'Email' => "patient{$i}@example.com",
                'Birthdate' => '1990-05-15',
                'Address' => "{$i} Main St",
                'City' => 'Detroit',
                'Zip' => '48201',
                'State' => 'MI',
            ]);
        }

        // Test pagination limit 10
        $response = $this->getJson(route('patients.data', ['start' => 0, 'length' => 10, 'draw' => 1]));
        $response->assertOk()
            ->assertJson([
                'draw' => 1,
                'recordsTotal' => 25,
                'recordsFiltered' => 25,
            ]);
        $this->assertCount(10, $response->json('data'));

        // Test search filter
        $searchResponse = $this->getJson(route('patients.data', ['search' => ['value' => 'SpecialSearchTarget'], 'start' => 0, 'length' => 10]));
        $searchResponse->assertOk()
            ->assertJson([
                'recordsFiltered' => 1,
            ]);
        $this->assertEquals('SpecialSearchTarget Patient10', $searchResponse->json('data.0.name'));
    }

    public function test_patients_data_handles_full_datatables_payload(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        OdPatient::create([
            'office_id' => 1,
            'PatNum' => 101,
            'FName' => 'John',
            'LName' => 'Doe',
            'WirelessPhone' => '555-1234',
            'Email' => 'john.doe@example.com',
            'Birthdate' => '1985-04-12',
            'Address' => '123 Main St',
            'City' => 'Detroit',
            'Zip' => '48201',
            'State' => 'MI',
        ]);

        $response = $this->getJson(route('patients.data', [
            'draw' => 2,
            'start' => 0,
            'length' => 20,
            'search' => ['value' => '', 'regex' => 'false'],
            'order' => [
                ['column' => 0, 'dir' => 'asc'],
            ],
            'columns' => [
                ['data' => 'name', 'name' => 'name', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'patient_id', 'name' => 'PatNum', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ],
        ]));

        $response->assertOk()
            ->assertJson([
                'draw' => 2,
                'recordsTotal' => 1,
                'recordsFiltered' => 1,
            ]);
        $this->assertCount(1, $response->json('data'));
    }
}
