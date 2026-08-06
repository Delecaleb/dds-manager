<?php

namespace Tests\Feature;

use App\Models\OdPatient;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_offices_page(): void
    {
        $user = User::factory()->create();
        $office = Office::create([
            'name' => 'Main Office',
            'developer_key' => 'dev_key_123',
            'customer_key' => 'cust_key_456',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('offices.index'));

        $response->assertOk();
        $response->assertSee('Main Office');
        $response->assertSee('dev_key_123');
        $response->assertSee('cust_key_456');
    }

    public function test_can_create_office_location(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('offices.store'), [
            'name' => 'Downtown Clinic',
            'developer_key' => 'dev_dt_789',
            'customer_key' => 'cust_dt_012',
            'api_url' => 'https://api.opendental.com/api/v1',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('offices.index'));
        $this->assertDatabaseHas('offices', [
            'name' => 'Downtown Clinic',
            'developer_key' => 'dev_dt_789',
            'customer_key' => 'cust_dt_012',
        ]);
    }

    public function test_can_switch_active_office(): void
    {
        $user = User::factory()->create();

        $office1 = Office::create([
            'name' => 'Office One',
            'is_active' => true,
        ]);

        $office2 = Office::create([
            'name' => 'Office Two',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('offices.switch'), [
            'office_id' => $office2->id,
        ]);

        $response->assertSessionHas('active_office_id', $office2->id);
        $this->assertEquals($office2->id, Office::getActiveOfficeId());
    }

    public function test_queries_are_scoped_to_active_office(): void
    {
        $user = User::factory()->create();

        $office1 = Office::create(['name' => 'Office One', 'is_active' => true]);
        $office2 = Office::create(['name' => 'Office Two', 'is_active' => true]);

        // Create patients belonging to office 1 and office 2
        OdPatient::create([
            'office_id' => $office1->id,
            'PatNum' => 101,
            'FName' => 'John',
            'LName' => 'Doe',
        ]);

        OdPatient::create([
            'office_id' => $office2->id,
            'PatNum' => 102,
            'FName' => 'Jane',
            'LName' => 'Smith',
        ]);

        // Activate Office 1
        session(['active_office_id' => $office1->id]);

        $patientsOffice1 = OdPatient::all();
        $this->assertCount(1, $patientsOffice1);
        $this->assertEquals('John', $patientsOffice1->first()->FName);

        // Activate Office 2
        session(['active_office_id' => $office2->id]);

        $patientsOffice2 = OdPatient::all();
        $this->assertCount(1, $patientsOffice2);
        $this->assertEquals('Jane', $patientsOffice2->first()->FName);
    }

    public function test_can_create_office_via_ajax(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('offices.store'), [
            'name' => 'Westside Dental',
            'developer_key' => 'dev_west_111',
            'customer_key' => 'cust_west_222',
            'is_active' => 1,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'office' => [
                'name' => 'Westside Dental',
                'developer_key' => 'dev_west_111',
                'customer_key' => 'cust_west_222',
            ],
        ]);
        $this->assertDatabaseHas('offices', ['name' => 'Westside Dental']);
    }

    public function test_can_update_office_via_ajax(): void
    {
        $user = User::factory()->create();
        $office = Office::create([
            'name' => 'Old Name',
            'developer_key' => 'old_dev',
            'customer_key' => 'old_cust',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->putJson(route('offices.update', $office->id), [
            'name' => 'New Name',
            'developer_key' => 'new_dev',
            'customer_key' => 'new_cust',
            'is_active' => 1,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'office' => [
                'id' => $office->id,
                'name' => 'New Name',
                'developer_key' => 'new_dev',
                'customer_key' => 'new_cust',
            ],
        ]);
        $this->assertDatabaseHas('offices', [
            'id' => $office->id,
            'name' => 'New Name',
            'developer_key' => 'new_dev',
        ]);
    }
}
