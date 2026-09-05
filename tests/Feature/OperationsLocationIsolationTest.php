<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\User;
use App\Services\OpenDental\OperationsAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OperationsLocationIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Office $office1;

    private Office $office2;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->office1 = Office::create([
            'name' => 'Downtown Dental',
            'is_active' => true,
        ]);

        $this->office2 = Office::create([
            'name' => 'Uptown Dental',
            'is_active' => true,
        ]);
    }

    public function test_operations_offices_tab_is_isolated_between_tenants(): void
    {
        // Seed Office 1: $1,000 gross
        DB::table('od_procedure_logs')->insert([
            'office_id' => $this->office1->id,
            'ProcNum' => 101,
            'PatNum' => 1,
            'ClinicNum' => 0,
            'ProvNum' => 1,
            'ProcFee' => 1000.00,
            'ProcStatus' => 'C',
            'ProcDate' => '2026-07-05',
            'MedicalCode' => '',
            'ToothNum' => '',
        ]);

        // Seed Office 2: $2,500 gross (same ProcNum and PatNum to test collision safety)
        DB::table('od_procedure_logs')->insert([
            'office_id' => $this->office2->id,
            'ProcNum' => 101,
            'PatNum' => 1,
            'ClinicNum' => 0,
            'ProvNum' => 1,
            'ProcFee' => 2500.00,
            'ProcStatus' => 'C',
            'ProcDate' => '2026-07-05',
            'MedicalCode' => '',
            'ToothNum' => '',
        ]);

        // Request Offices tab as Office 1
        $response1 = $this->withSession(['active_office_id' => $this->office1->id])
            ->get(route('operations.data', [
                'tab' => 'offices',
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-31',
            ]));

        $response1->assertOk();
        $spec1 = $response1->original->getData()['spec'];
        $this->assertEquals(1000.00, $spec1['total']['gross']);

        // Request Offices tab as Office 2
        $response2 = $this->withSession(['active_office_id' => $this->office2->id])
            ->get(route('operations.data', [
                'tab' => 'offices',
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-31',
            ]));

        $response2->assertOk();
        $spec2 = $response2->original->getData()['spec'];
        $this->assertEquals(2500.00, $spec2['total']['gross']);
    }

    public function test_operations_drilldown_is_strictly_scoped_to_active_office(): void
    {
        // Seed Provider and Patient in Office 1
        DB::table('od_providers')->insert([
            'office_id' => $this->office1->id,
            'ProvNum' => 1,
            'LName' => 'Smith',
            'PName' => 'Alice',
            'Abbr' => 'AS',
            'Specialty' => 0,
        ]);
        DB::table('od_patients')->insert([
            'office_id' => $this->office1->id,
            'PatNum' => 100,
            'LName' => 'Doe',
            'FName' => 'John',
        ]);
        DB::table('od_procedure_logs')->insert([
            'office_id' => $this->office1->id,
            'ProcNum' => 1,
            'PatNum' => 100,
            'ProvNum' => 1,
            'ClinicNum' => 0,
            'ProcFee' => 750.00,
            'ProcStatus' => 'C',
            'ProcDate' => '2026-07-10',
            'MedicalCode' => '',
            'ToothNum' => '',
        ]);

        // Seed Provider and Patient in Office 2 with identical IDs but different names
        DB::table('od_providers')->insert([
            'office_id' => $this->office2->id,
            'ProvNum' => 1,
            'LName' => 'Johnson',
            'PName' => 'Bob',
            'Abbr' => 'BJ',
            'Specialty' => 0,
        ]);
        DB::table('od_patients')->insert([
            'office_id' => $this->office2->id,
            'PatNum' => 100,
            'LName' => 'Williams',
            'FName' => 'Carol',
        ]);
        DB::table('od_procedure_logs')->insert([
            'office_id' => $this->office2->id,
            'ProcNum' => 1,
            'PatNum' => 100,
            'ProvNum' => 1,
            'ClinicNum' => 0,
            'ProcFee' => 1500.00,
            'ProcStatus' => 'C',
            'ProcDate' => '2026-07-10',
            'MedicalCode' => '',
            'ToothNum' => '',
        ]);

        // Drilldown as Office 1
        $response1 = $this->withSession(['active_office_id' => $this->office1->id])
            ->get(route('operations.drilldown', [
                'metric' => 'gross',
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-31',
            ]));

        $response1->assertOk();
        $rows1 = $response1->original->getData()['rows'];
        $totals1 = $response1->original->getData()['totals'];
        $this->assertEquals(750.00, $totals1['gross']);
        $this->assertEquals('Doe, John', $rows1[0]['patient']['label']);
        $this->assertEquals('Alice Smith', $rows1[0]['provider']['label']);

        // Drilldown as Office 2
        $response2 = $this->withSession(['active_office_id' => $this->office2->id])
            ->get(route('operations.drilldown', [
                'metric' => 'gross',
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-31',
            ]));

        $response2->assertOk();
        $rows2 = $response2->original->getData()['rows'];
        $totals2 = $response2->original->getData()['totals'];
        $this->assertEquals(1500.00, $totals2['gross']);
        $this->assertEquals('Williams, Carol', $rows2[0]['patient']['label']);
        $this->assertEquals('Bob Johnson', $rows2[0]['provider']['label']);
    }

    public function test_operations_marketing_tab_zip_codes_are_isolated(): void
    {
        // Seed patients with ZIP codes in Office 1
        DB::table('od_patients')->insert([
            ['office_id' => $this->office1->id, 'PatNum' => 1, 'LName' => 'A', 'FName' => 'A', 'Zip' => '48101'],
            ['office_id' => $this->office1->id, 'PatNum' => 2, 'LName' => 'B', 'FName' => 'B', 'Zip' => '48102'],
        ]);

        // Seed patients with ZIP codes in Office 2
        DB::table('od_patients')->insert([
            ['office_id' => $this->office2->id, 'PatNum' => 3, 'LName' => 'C', 'FName' => 'C', 'Zip' => '90210'],
        ]);

        // Marketing tab as Office 1
        $response1 = $this->withSession(['active_office_id' => $this->office1->id])
            ->get(route('operations.data', [
                'tab' => 'marketing',
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-31',
            ]));

        $response1->assertOk();
        $spec1 = $response1->original->getData()['spec'];
        $this->assertContains('48101', $spec1['available_zips']);
        $this->assertContains('48102', $spec1['available_zips']);
        $this->assertNotContains('90210', $spec1['available_zips']);

        // Marketing tab as Office 2
        $response2 = $this->withSession(['active_office_id' => $this->office2->id])
            ->get(route('operations.data', [
                'tab' => 'marketing',
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-31',
            ]));

        $response2->assertOk();
        $spec2 = $response2->original->getData()['spec'];
        $this->assertContains('90210', $spec2['available_zips']);
        $this->assertNotContains('48101', $spec2['available_zips']);
        $this->assertNotContains('48102', $spec2['available_zips']);
    }

    public function test_operations_cancellations_tab_isolation(): void
    {
        // Seed Office 1 broken appointment
        DB::table('od_appointments')->insert([
            'office_id' => $this->office1->id,
            'AptNum' => 1,
            'PatNum' => 10,
            'ClinicNum' => 0,
            'AptStatus' => '5',
            'AptDateTime' => '2026-07-15 10:00:00',
        ]);
        DB::table('od_procedure_logs')->insert([
            'office_id' => $this->office1->id,
            'ProcNum' => 1,
            'AptNum' => 1,
            'PatNum' => 10,
            'ClinicNum' => 0,
            'ProcFee' => 300.00,
            'ProcStatus' => '1',
            'ProcDate' => '2026-07-15',
            'MedicalCode' => '',
            'ToothNum' => '',
        ]);

        // Seed Office 2 broken appointment
        DB::table('od_appointments')->insert([
            'office_id' => $this->office2->id,
            'AptNum' => 2,
            'PatNum' => 20,
            'ClinicNum' => 0,
            'AptStatus' => '5',
            'AptDateTime' => '2026-07-15 11:00:00',
        ]);
        DB::table('od_procedure_logs')->insert([
            'office_id' => $this->office2->id,
            'ProcNum' => 2,
            'AptNum' => 2,
            'PatNum' => 20,
            'ClinicNum' => 0,
            'ProcFee' => 850.00,
            'ProcStatus' => '1',
            'ProcDate' => '2026-07-15',
            'MedicalCode' => '',
            'ToothNum' => '',
        ]);

        // Cancellations as Office 1
        $response1 = $this->withSession(['active_office_id' => $this->office1->id])
            ->get(route('operations.data', [
                'tab' => 'cancellations',
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-31',
            ]));

        $response1->assertOk();
        $spec1 = $response1->original->getData()['spec'];
        $this->assertEquals(1, $spec1['total']['cancellation']);
        $this->assertEquals(300.00, $spec1['total']['cancellation_dollars']);

        // Cancellations as Office 2
        $response2 = $this->withSession(['active_office_id' => $this->office2->id])
            ->get(route('operations.data', [
                'tab' => 'cancellations',
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-31',
            ]));

        $response2->assertOk();
        $spec2 = $response2->original->getData()['spec'];
        $this->assertEquals(1, $spec2['total']['cancellation']);
        $this->assertEquals(850.00, $spec2['total']['cancellation_dollars']);
    }

    public function test_operations_payors_and_services_and_compliance_tabs_isolation(): void
    {
        // Seed Procedure Code
        DB::table('od_procedures')->insert([
            'office_id' => $this->office1->id,
            'CodeNum' => 50,
            'ProcCode' => 'D0120',
            'Descript' => 'Periodic Oral Exam',
            'ProcCat' => 1,
        ]);
        DB::table('od_procedures')->insert([
            'office_id' => $this->office2->id,
            'CodeNum' => 50,
            'ProcCode' => 'D0120',
            'Descript' => 'Periodic Oral Exam',
            'ProcCat' => 1,
        ]);

        // Seed Office 1 procedure log
        DB::table('od_procedure_logs')->insert([
            'office_id' => $this->office1->id,
            'ProcNum' => 1,
            'PatNum' => 10,
            'ProvNum' => 1,
            'CodeNum' => 50,
            'ClinicNum' => 0,
            'ProcFee' => 120.00,
            'ProcStatus' => 'C',
            'ProcDate' => '2026-07-15',
            'MedicalCode' => '',
            'ToothNum' => '',
        ]);

        // Seed Office 2 procedure log
        DB::table('od_procedure_logs')->insert([
            'office_id' => $this->office2->id,
            'ProcNum' => 2,
            'PatNum' => 20,
            'ProvNum' => 1,
            'CodeNum' => 50,
            'ClinicNum' => 0,
            'ProcFee' => 340.00,
            'ProcStatus' => 'C',
            'ProcDate' => '2026-07-15',
            'MedicalCode' => '',
            'ToothNum' => '',
        ]);

        // Services tab for Office 1
        $res1 = $this->withSession(['active_office_id' => $this->office1->id])
            ->get(route('operations.data', [
                'tab' => 'services',
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-31',
            ]));
        $res1->assertOk();
        $spec1 = $res1->original->getData()['spec'];
        $this->assertEquals(120.00, $spec1['total']['fee']);

        // Services tab for Office 2
        $res2 = $this->withSession(['active_office_id' => $this->office2->id])
            ->get(route('operations.data', [
                'tab' => 'services',
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-31',
            ]));
        $res2->assertOk();
        $spec2 = $res2->original->getData()['spec'];
        $this->assertEquals(340.00, $spec2['total']['fee']);

        // Compliance tab for Office 1
        $comp1 = $this->withSession(['active_office_id' => $this->office1->id])
            ->get(route('operations.data', [
                'tab' => 'compliance',
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-31',
            ]));
        $comp1->assertOk();
        $compSpec1 = $comp1->original->getData()['spec'];
        $this->assertEquals(120.00, $compSpec1['total']['total_prod']);

        // Compliance tab for Office 2
        $comp2 = $this->withSession(['active_office_id' => $this->office2->id])
            ->get(route('operations.data', [
                'tab' => 'compliance',
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-31',
            ]));
        $comp2->assertOk();
        $compSpec2 = $comp2->original->getData()['spec'];
        $this->assertEquals(340.00, $compSpec2['total']['total_prod']);
    }

    public function test_service_methods_support_explicit_office_id(): void
    {
        $service = app(OperationsAnalyticsService::class);

        // Seed Office 2 only
        DB::table('od_procedure_logs')->insert([
            'office_id' => $this->office2->id,
            'ProcNum' => 999,
            'PatNum' => 99,
            'ClinicNum' => 0,
            'ProvNum' => 1,
            'ProcFee' => 450.00,
            'ProcStatus' => 'C',
            'ProcDate' => '2026-07-20',
            'MedicalCode' => '',
            'ToothNum' => '',
        ]);

        $resOffice1 = $service->offices('2026-07-01', '2026-07-31', 'default', [], $this->office1->id);
        $resOffice2 = $service->offices('2026-07-01', '2026-07-31', 'default', [], $this->office2->id);

        $this->assertEquals(0.0, $resOffice1['total']['gross']);
        $this->assertEquals(450.00, $resOffice2['total']['gross']);
    }
}
