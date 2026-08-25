<?php

namespace Tests\Feature;

use App\Models\OdPatient;
use App\Models\User;
use Tests\TestCase;

class PatientExportTest extends TestCase
{
    public function test_patients_index_renders_with_export_data_tab(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('patients.index'));

        $response->assertOk();
        $response->assertSee('Export Data');
        $response->assertSee('Filter Parameters');
        $response->assertSee('Select Export Columns');
        $response->assertSee('Date Added to Open Dental');
    }

    public function test_export_data_endpoint_filters_by_date_added_to_open_dental(): void
    {
        $user = User::factory()->create();

        // Patient 1 added in August 2026
        $p1 = OdPatient::create([
            'office_id' => 1,
            'PatNum' => 5001,
            'FName' => 'Sarah',
            'LName' => 'Connor',
            'Email' => 'sarah@example.com',
            'WirelessPhone' => '555-0101',
            'Address' => '123 Cyber Way',
            'City' => 'Los Angeles',
            'State' => 'CA',
            'Zip' => '90001',
            'SecDateEntry' => '2026-08-10',
            'PatStatus' => 0,
        ]);

        // Patient 2 added in January 2026
        $p2 = OdPatient::create([
            'office_id' => 1,
            'PatNum' => 5002,
            'FName' => 'John',
            'LName' => 'Connor',
            'Email' => 'john@example.com',
            'WirelessPhone' => '555-0102',
            'Address' => '456 Resistance Blvd',
            'City' => 'Los Angeles',
            'State' => 'CA',
            'Zip' => '90002',
            'SecDateEntry' => '2026-01-15',
            'PatStatus' => 0,
        ]);

        // Query with Date Range for August 2026
        $response = $this->actingAs($user)->getJson(route('patients.export-data', [
            'date_mode' => 'custom',
            'date_from' => '2026-08-01',
            'date_to' => '2026-08-31',
            'columns' => ['patient_id', 'first_name', 'last_name', 'email', 'mobile_phone', 'address', 'date_added'],
        ]));

        $response->assertOk();
        $data = $response->json();

        $this->assertEquals(1, $data['total']);
        $this->assertEquals('Sarah', $data['data'][0]['first_name']);
        $this->assertEquals('2026-08-10', $data['data'][0]['date_added']);
    }

    public function test_export_data_endpoint_returns_only_selected_columns(): void
    {
        $user = User::factory()->create();

        OdPatient::create([
            'office_id' => 1,
            'PatNum' => 5003,
            'FName' => 'Kyle',
            'LName' => 'Reese',
            'Email' => 'kyle@example.com',
            'WirelessPhone' => '555-0199',
            'Address' => '789 Future St',
            'City' => 'Skynet City',
            'State' => 'NV',
            'Zip' => '89001',
            'SecDateEntry' => '2026-08-12',
            'PatStatus' => 0,
        ]);

        $selectedCols = ['patient_id', 'full_name', 'mobile_phone', 'email', 'city'];
        $response = $this->actingAs($user)->getJson(route('patients.export-data', [
            'date_mode' => 'all',
            'search' => 'Kyle',
            'columns' => $selectedCols,
        ]));

        $response->assertOk();
        $data = $response->json();

        $this->assertEquals(1, $data['total']);
        $row = $data['data'][0];

        $this->assertArrayHasKey('patient_id', $row);
        $this->assertArrayHasKey('full_name', $row);
        $this->assertArrayHasKey('mobile_phone', $row);
        $this->assertArrayHasKey('email', $row);
        $this->assertArrayHasKey('city', $row);
        $this->assertArrayNotHasKey('ssn', $row);
        $this->assertArrayNotHasKey('bal_total', $row);
    }

    public function test_export_download_streams_csv_with_selected_columns_and_filters(): void
    {
        $user = User::factory()->create();

        $p4 = OdPatient::create([
            'office_id' => 1,
            'PatNum' => 5004,
            'FName' => 'Ellen',
            'LName' => 'Ripley',
            'Email' => 'ripley@weyland.com',
            'WirelessPhone' => '555-4260',
            'Address' => 'Nostromo Deck A',
            'City' => 'Space Station',
            'State' => 'OR',
            'Zip' => '97001',
            'SecDateEntry' => '2026-08-20',
            'PatStatus' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('patients.export-download', [
            'date_mode' => 'custom',
            'date_from' => '2026-08-01',
            'date_to' => '2026-08-31',
            'columns' => ['patient_id', 'first_name', 'last_name', 'email', 'mobile_phone', 'address', 'city', 'state', 'zip', 'date_added'],
            'filename' => 'august_new_patients',
        ]));

        $response->assertOk();
        $this->assertTrue($response->headers->contains('content-type', 'text/csv; charset=UTF-8'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Patient ID', $content);
        $this->assertStringContainsString('First Name', $content);
        $this->assertStringContainsString('Email Address', $content);
        $this->assertStringContainsString('Ellen', $content);
        $this->assertStringContainsString('Ripley', $content);
        $this->assertStringContainsString('ripley@weyland.com', $content);
        $this->assertStringContainsString('2026-08-20', $content);
    }

    public function test_export_data_endpoint_prioritizes_sec_date_entry_over_created_at(): void
    {
        $user = User::factory()->create();

        // Patient entered in Open Dental on 2026-08-05, but synced locally today
        $p1 = OdPatient::create([
            'office_id' => 1,
            'PatNum' => 5010,
            'FName' => 'Marcus',
            'LName' => 'Wright',
            'Email' => 'marcus@example.com',
            'SecDateEntry' => '2026-08-05',
            'DateTStamp' => '2026-08-05 14:00:00',
            'PatStatus' => 0,
        ]);

        // Patient entered in Open Dental on 2026-01-10, synced locally today
        $p2 = OdPatient::create([
            'office_id' => 1,
            'PatNum' => 5011,
            'FName' => 'John',
            'LName' => 'Connor',
            'Email' => 'john.connor@example.com',
            'SecDateEntry' => '2026-01-10',
            'DateTStamp' => '2026-01-10 09:00:00',
            'PatStatus' => 0,
        ]);

        // Filter for August 2026
        $response = $this->actingAs($user)->getJson(route('patients.export-data', [
            'date_mode' => 'custom',
            'date_from' => '2026-08-01',
            'date_to' => '2026-08-31',
            'columns' => ['patient_id', 'first_name', 'last_name', 'date_added'],
        ]));

        $response->assertOk();
        $data = $response->json();

        $this->assertEquals(1, $data['total']);
        $this->assertEquals('Marcus', $data['data'][0]['first_name']);
        $this->assertEquals('2026-08-05', $data['data'][0]['date_added']);
    }
}
