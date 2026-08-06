<?php

namespace Tests\Feature;

use App\Models\OdPatient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_show_redirects_direct_visit_to_index(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $patient = OdPatient::create([
            'PatNum' => 22037,
            'FName' => 'Ada',
            'LName' => 'Lovelace',
            'WirelessPhone' => '5551234',
            'Email' => 'ada@example.com',
            'Birthdate' => '2001-01-01',
            'Address' => '1 Main',
            'City' => 'Detroit',
            'Zip' => '48201',
            'State' => 'MI',
            'Gender' => 1,
            'PatStatus' => 0,
        ]);

        $response = $this->get(route('patients.show', ['id' => 22037]));

        $response->assertRedirect(route('patients.index', ['open_patient_id' => 22037]));
    }

    public function test_patient_show_returns_json_for_ajax_request(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $patient = OdPatient::create([
            'PatNum' => 22037,
            'FName' => 'Ada',
            'LName' => 'Lovelace',
            'WirelessPhone' => '5551234',
            'Email' => 'ada@example.com',
            'Birthdate' => '2001-01-01',
            'Address' => '1 Main',
            'City' => 'Detroit',
            'Zip' => '48201',
            'State' => 'MI',
            'Gender' => 1,
            'PatStatus' => 0,
        ]);

        $response = $this->getJson(route('patients.show', ['id' => 22037]));

        $response->assertOk()
            ->assertJsonStructure([
                'id',
                'name',
                'age',
                'gender',
                'birthdate',
                'status',
                'mobile_phone',
                'work_phone',
                'home_phone',
                'email',
                'address',
                'city',
                'state',
                'zip',
                'overview',
                'ledger',
                'txplans',
                'notes',
            ]);

        $response->assertJson([
            'id' => 22037,
            'name' => 'Lovelace, Ada',
            'email' => 'ada@example.com',
        ]);
    }
}
