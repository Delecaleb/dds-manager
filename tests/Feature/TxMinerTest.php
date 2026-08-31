<?php

namespace Tests\Feature;

use App\Models\OdPatient;
use App\Models\OdProcedure;
use App\Models\OdProcedureLog;
use App\Models\OdProvider;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TxMinerTest extends TestCase
{
    use RefreshDatabase;

    protected Office $office;

    protected function setUp(): void
    {
        parent::setUp();

        $this->office = Office::create([
            'name' => 'Main Clinic',
            'is_active' => true,
        ]);
    }

    public function test_tx_miner_page_can_be_rendered_by_authenticated_super_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get(route('tx-miner.index'));

        $response->assertStatus(200);
        $response->assertSee('Treatment Miner');
        $response->assertSee('By month');
        $response->assertSee('By Provider');
        $response->assertSee('By Location');
    }

    public function test_tx_miner_page_forbidden_for_user_without_tx_miner_module_access(): void
    {
        $staff = User::factory()->staff()->create();
        $staff->syncModules(['patients', 'calendar']); // No tx-miner module

        $response = $this->actingAs($staff)->get(route('tx-miner.index'));

        $response->assertStatus(403);
    }

    public function test_tx_miner_page_accessible_for_user_with_tx_miner_module_access(): void
    {
        $staff = User::factory()->staff()->create();
        $staff->syncModules(['tx-miner']);

        $response = $this->actingAs($staff)->get(route('tx-miner.index'));

        $response->assertStatus(200);
        $response->assertSee('Treatment Miner');
    }

    public function test_tx_miner_month_data_endpoint_returns_aggregated_metrics(): void
    {
        $user = User::factory()->superAdmin()->create();

        $provider = OdProvider::create([
            'ProvNum' => 10,
            'LName' => 'Smith',
            'FName' => 'John',
            'Abbr' => 'JS',
            'IsHidden' => 'false',
        ]);

        $patient = OdPatient::create([
            'PatNum' => 100,
            'LName' => 'Doe',
            'FName' => 'Jane',
        ]);

        $proc = OdProcedure::create([
            'CodeNum' => 50,
            'ProcCode' => 'D2740',
            'Descript' => 'Crown - Porcelain/Ceramic',
            'IsHygiene' => 'false',
        ]);

        // 1. Treatment planned & scheduled ($1000)
        OdProcedureLog::create([
            'ProcNum' => 1,
            'PatNum' => $patient->PatNum,
            'ProvNum' => $provider->ProvNum,
            'ClinicNum' => 1,
            'CodeNum' => $proc->CodeNum,
            'ProcDate' => '2026-07-15',
            'ProcFee' => '1000.00',
            'ProcStatus' => 'TP',
            'AptNum' => '101',
        ]);

        // 2. Treatment planned & unscheduled ($500)
        OdProcedureLog::create([
            'ProcNum' => 2,
            'PatNum' => $patient->PatNum,
            'ProvNum' => $provider->ProvNum,
            'ClinicNum' => 1,
            'CodeNum' => $proc->CodeNum,
            'ProcDate' => '2026-07-20',
            'ProcFee' => '500.00',
            'ProcStatus' => 'TP',
            'AptNum' => '0',
        ]);

        // 3. Completed ($300)
        OdProcedureLog::create([
            'ProcNum' => 3,
            'PatNum' => $patient->PatNum,
            'ProvNum' => $provider->ProvNum,
            'ClinicNum' => 1,
            'CodeNum' => $proc->CodeNum,
            'ProcDate' => '2026-07-25',
            'ProcFee' => '300.00',
            'ProcStatus' => 'C',
            'AptNum' => '102',
        ]);

        $response = $this->actingAs($user)->getJson(route('tx-miner.data', [
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
            'average',
            'total',
        ]);

        $json = $response->json();
        $this->assertNotEmpty($json['data']);
        $firstRow = $json['data'][0];

        $this->assertEquals('$ 1,500.00', $firstRow['total_tx_plan']);
        $this->assertEquals('$ 1,000.00', $firstRow['tx_scheduled']);
        $this->assertEquals('$ 500.00', $firstRow['tx_unscheduled']);
        $this->assertEquals('$ 300.00', $firstRow['completed_tx']);
        $this->assertEquals(2, $firstRow['tx_presented']);
    }

    public function test_tx_miner_provider_data_endpoint_returns_provider_breakdown(): void
    {
        $user = User::factory()->superAdmin()->create();

        $provider1 = OdProvider::create([
            'ProvNum' => 21,
            'LName' => 'Adams',
            'FName' => 'Abigail',
            'Abbr' => 'AA',
            'IsHidden' => 'false',
        ]);

        $provider2 = OdProvider::create([
            'ProvNum' => 22,
            'LName' => 'Baker',
            'FName' => 'Bob',
            'Abbr' => 'BB',
            'IsHidden' => 'false',
        ]);

        OdProcedureLog::create([
            'ProcNum' => 11,
            'PatNum' => 100,
            'ProvNum' => $provider1->ProvNum,
            'ClinicNum' => 1,
            'CodeNum' => 1,
            'ProcDate' => '2026-08-10',
            'ProcFee' => '800.00',
            'ProcStatus' => 'TP',
            'AptNum' => '201',
        ]);

        OdProcedureLog::create([
            'ProcNum' => 12,
            'PatNum' => 100,
            'ProvNum' => $provider2->ProvNum,
            'ClinicNum' => 1,
            'CodeNum' => 1,
            'ProcDate' => '2026-08-12',
            'ProcFee' => '600.00',
            'ProcStatus' => 'TP',
            'AptNum' => '0',
        ]);

        $response = $this->actingAs($user)->getJson(route('tx-miner.data-provider', [
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]));

        $response->assertStatus(200);
        $json = $response->json();
        $this->assertCount(2, $json['data']);
        $this->assertEquals(21, $json['data'][0]['prov_num']);
        $this->assertEquals('$ 800.00', $json['data'][0]['total_tx_plan']);
    }

    public function test_tx_miner_location_data_endpoint_returns_location_breakdown(): void
    {
        $user = User::factory()->superAdmin()->create();

        OdProcedureLog::create([
            'ProcNum' => 31,
            'PatNum' => 100,
            'ProvNum' => 1,
            'ClinicNum' => 1,
            'CodeNum' => 1,
            'ProcDate' => '2026-08-15',
            'ProcFee' => '1200.00',
            'ProcStatus' => 'TP',
            'AptNum' => '301',
        ]);

        $response = $this->actingAs($user)->getJson(route('tx-miner.data-location', [
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]));

        $response->assertStatus(200);
        $json = $response->json();
        $this->assertNotEmpty($json['data']);
        $this->assertEquals('$ 1,200.00', $json['data'][0]['total_tx_plan']);
    }

    public function test_tx_miner_export_csv_downloads_valid_csv(): void
    {
        $user = User::factory()->superAdmin()->create();

        OdProcedureLog::create([
            'ProcNum' => 41,
            'PatNum' => 100,
            'ProvNum' => 1,
            'ClinicNum' => 1,
            'CodeNum' => 1,
            'ProcDate' => '2026-08-15',
            'ProcFee' => '500.00',
            'ProcStatus' => 'TP',
            'AptNum' => '401',
        ]);

        $response = $this->actingAs($user)->get(route('tx-miner.export', [
            'tab' => 'provider',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Provider', $response->streamedContent());
        $this->assertStringContainsString('Total TX Plan', $response->streamedContent());
    }

    public function test_tx_miner_drilldown_endpoint_returns_treatment_plan_breakdown(): void
    {
        $user = User::factory()->superAdmin()->create();

        $patient = OdPatient::create([
            'PatNum' => 201,
            'LName' => 'Williams',
            'FName' => 'Roger',
        ]);

        $provider = OdProvider::create([
            'ProvNum' => 30,
            'LName' => 'Clark',
            'FName' => 'Kent',
            'Abbr' => 'CK',
            'IsHidden' => 'false',
        ]);

        $proc = OdProcedure::create([
            'CodeNum' => 77,
            'ProcCode' => 'D3330',
            'Descript' => 'Endodontic Therapy - Molar',
            'IsHygiene' => 'false',
        ]);

        OdProcedureLog::create([
            'ProcNum' => 51,
            'PatNum' => $patient->PatNum,
            'ProvNum' => $provider->ProvNum,
            'ClinicNum' => 1,
            'CodeNum' => $proc->CodeNum,
            'ProcDate' => '2026-08-18',
            'ProcFee' => '1350.00',
            'ProcStatus' => 'TP',
            'ToothNum' => '19',
            'Surf' => 'MO',
            'AptNum' => '0',
        ]);

        // Drill down on unscheduled treatment
        $response = $this->actingAs($user)->get(route('tx-miner.drilldown', [
            'metric' => 'tx_unscheduled',
            'prov_num' => 30,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Tx Unscheduled Breakdown');
        $response->assertSee('Williams, Roger');
        $response->assertSee('D3330');
        $response->assertSee('Endodontic Therapy - Molar');
        $response->assertSee('1,350.00');
        $response->assertSee('Unscheduled');
    }

    public function test_tx_miner_filters_by_line_of_business(): void
    {
        $user = User::factory()->superAdmin()->create();

        $procEndo = OdProcedure::create([
            'CodeNum' => 81,
            'ProcCode' => 'D3310',
            'Descript' => 'Anterior Root Canal',
            'IsHygiene' => 'false',
        ]);

        $procPerio = OdProcedure::create([
            'CodeNum' => 82,
            'ProcCode' => 'D4341',
            'Descript' => 'Periodontal Scaling',
            'IsHygiene' => 'true',
        ]);

        OdProcedureLog::create([
            'ProcNum' => 61,
            'PatNum' => 100,
            'ProvNum' => 1,
            'ClinicNum' => 1,
            'CodeNum' => $procEndo->CodeNum,
            'ProcDate' => '2026-08-05',
            'ProcFee' => '800.00',
            'ProcStatus' => 'TP',
            'AptNum' => '0',
        ]);

        OdProcedureLog::create([
            'ProcNum' => 62,
            'PatNum' => 100,
            'ProvNum' => 1,
            'ClinicNum' => 1,
            'CodeNum' => $procPerio->CodeNum,
            'ProcDate' => '2026-08-06',
            'ProcFee' => '250.00',
            'ProcStatus' => 'TP',
            'AptNum' => '0',
        ]);

        // Filter only Endo
        $response = $this->actingAs($user)->getJson(route('tx-miner.data', [
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'lobs' => ['Endo'],
        ]));

        $response->assertStatus(200);
        $json = $response->json();
        $this->assertEquals('$ 800.00', $json['data'][0]['total_tx_plan']);
    }

    public function test_tx_miner_index_contains_month_selector_and_date_range_picker(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get(route('tx-miner.index'));

        $response->assertStatus(200);
        $response->assertSee('id="txMinerMonth"', false);
        $response->assertSee('id="txMinerDateRange"', false);
        $response->assertSee('id="txMinerMonthPickerWrap"', false);
        $response->assertSee('id="txMinerDateRangeWrap"', false);
    }

    public function test_tx_miner_month_data_returns_13_months_from_selected_month_to_prior_12_months(): void
    {
        $user = User::factory()->superAdmin()->create();

        $provider = OdProvider::create([
            'ProvNum' => 30,
            'LName' => 'Doctor',
            'FName' => 'Jane',
            'Abbr' => 'JD',
            'IsHidden' => 'false',
        ]);

        $patient = OdPatient::create([
            'PatNum' => 200,
            'LName' => 'Smith',
            'FName' => 'John',
        ]);

        $proc = OdProcedure::create([
            'CodeNum' => 90,
            'ProcCode' => 'D0120',
            'Descript' => 'Periodic Oral Eval',
            'IsHygiene' => 'false',
        ]);

        // Procedure in target month: July 2025
        OdProcedureLog::create([
            'ProcNum' => 71,
            'PatNum' => $patient->PatNum,
            'ProvNum' => $provider->ProvNum,
            'ClinicNum' => 1,
            'CodeNum' => $proc->CodeNum,
            'ProcDate' => '2025-07-10',
            'ProcFee' => '500.00',
            'ProcStatus' => 'TP',
            'AptNum' => '100',
        ]);

        // Procedure in 12 months prior: July 2024
        OdProcedureLog::create([
            'ProcNum' => 72,
            'PatNum' => $patient->PatNum,
            'ProvNum' => $provider->ProvNum,
            'ClinicNum' => 1,
            'CodeNum' => $proc->CodeNum,
            'ProcDate' => '2024-07-05',
            'ProcFee' => '750.00',
            'ProcStatus' => 'TP',
            'AptNum' => '101',
        ]);

        // Procedure before window: June 2024 (should be excluded)
        OdProcedureLog::create([
            'ProcNum' => 73,
            'PatNum' => $patient->PatNum,
            'ProvNum' => $provider->ProvNum,
            'ClinicNum' => 1,
            'CodeNum' => $proc->CodeNum,
            'ProcDate' => '2024-06-30',
            'ProcFee' => '999.00',
            'ProcStatus' => 'TP',
            'AptNum' => '102',
        ]);

        // Procedure after window: August 2025 (should be excluded)
        OdProcedureLog::create([
            'ProcNum' => 74,
            'PatNum' => $patient->PatNum,
            'ProvNum' => $provider->ProvNum,
            'ClinicNum' => 1,
            'CodeNum' => $proc->CodeNum,
            'ProcDate' => '2025-08-01',
            'ProcFee' => '999.00',
            'ProcStatus' => 'TP',
            'AptNum' => '103',
        ]);

        $response = $this->actingAs($user)->getJson(route('tx-miner.data', [
            'month' => '2025-07',
        ]));

        $response->assertStatus(200);
        $json = $response->json();

        // Must return exactly 13 months (July 2025 down to July 2024)
        $this->assertCount(13, $json['data']);
        $this->assertEquals('2025-07', $json['data'][0]['month_group']);
        $this->assertEquals('Jul 25', $json['data'][0]['month']);
        $this->assertEquals('$ 500.00', $json['data'][0]['total_tx_plan']);

        $this->assertEquals('2024-07', $json['data'][12]['month_group']);
        $this->assertEquals('Jul 24', $json['data'][12]['month']);
        $this->assertEquals('$ 750.00', $json['data'][12]['total_tx_plan']);

        // Sum across the 13 months
        $this->assertEquals('$ 1,250.00', $json['total']['total_tx_plan']);
    }

    public function test_tx_miner_export_csv_with_month_selector(): void
    {
        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)->get(route('tx-miner.export', [
            'tab' => 'month',
            'month' => '2025-07',
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }
}
