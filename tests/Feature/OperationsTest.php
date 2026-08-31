<?php

namespace Tests\Feature;

use App\Models\OdPatient;
use App\Models\OdProcedureLog;
use App\Models\OdProvider;
use App\Models\User;
use App\Services\OpenDental\OperationsAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_offices_tab_returns_full_totals_for_all_columns(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Seed completing procedures for gross production, unique pts, pts_visit, working days, and procedures
        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 1,
                'PatNum' => 101,
                'ClinicNum' => 1,
                'ProcFee' => 1000.00,
                'ProcStatus' => 'C',
                'ProcDate' => '2026-07-05',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
            [
                'ProcNum' => 2,
                'PatNum' => 102,
                'ClinicNum' => 1,
                'ProcFee' => 500.00,
                'ProcStatus' => 'C',
                'ProcDate' => '2026-07-06',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
        ]);

        // Seed adjustments
        DB::table('od_adjustments')->insert([
            [
                'ProvNum' => 1,
                'AdjAmt' => -100.00,
                'AdjDate' => '2026-07-05',
                'ClinicNum' => 1,
            ],
        ]);

        // Seed pay splits (collections)
        DB::table('od_pay_splits')->insert([
            [
                'ProvNum' => 1,
                'SplitAmt' => 1200.00,
                'DatePay' => '2026-07-05',
                'ClinicNum' => 1,
            ],
        ]);

        // Query the operations offices endpoint
        $response = $this->get(route('operations.data', [
            'tab' => 'offices',
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-15',
        ]));

        $response->assertOk();

        // Get the spec data passed to the view
        $spec = $response->original->getData()['spec'] ?? null;
        $this->assertNotNull($spec);

        $total = $spec['total'];

        // Assert all By Office metrics totals
        $this->assertEquals(1500.00, $total['gross']);
        $this->assertEquals(-100.00, $total['adjustment']);
        $this->assertEquals(-6.67, $total['adj_pct']); // -100 / 1500 * 100 = -6.67%
        $this->assertEquals(1400.00, $total['net']);      // 1500 - 100 = 1400
        $this->assertEquals(1200.00, $total['collection']);
        $this->assertEquals(85.71, $total['coll_pct']);  // 1200 / 1400 * 100 = 85.71%
        $this->assertEquals(2, $total['pts_visit']);
        $this->assertEquals(2, $total['unique_pts']);
        $this->assertEquals(2, $total['working_days']);

        // Assert Per Working Day metrics totals
        $this->assertEquals(700.00, $total['pwd_production']);  // 1400 / 2 working days
        $this->assertEquals(600.00, $total['pwd_collection']);  // 1200 / 2 working days
        $this->assertEquals(1.0, $total['pwd_pts_visit']);      // 2 / 2 working days

        // Assert Per Patient Visit metrics totals
        $this->assertEquals(700.00, $total['ppv_production']);  // 1400 / 2 visits
        $this->assertEquals(600.00, $total['ppv_collection']);  // 1200 / 2 visits
        $this->assertEquals(1.0, $total['ppv_procedures']);      // 2 / 2 procedures

        // Assert Per Procedure metrics totals
        $this->assertEquals(700.00, $total['pp_production']);  // 1400 / 2 procedures
        $this->assertEquals(600.00, $total['pp_collection']);  // 1200 / 2 procedures
    }

    public function test_payors_tab_returns_totals_for_all_columns(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Seed claim procs
        DB::table('od_claim_procs')->insert([
            [
                'office_id' => 1,
                'ClaimProcNum' => 1,
                'Status' => 1,
                'ClaimPaymentNum' => 0,
                'PlanNum' => 1,
                'ClinicNum' => 1,
                'FeeBilled' => '1000.00',
                'WriteOff' => '200.00',
                'InsPayAmt' => '800.00',
                'ProcDate' => '2026-07-05',
                'PatNum' => 101,
            ],
            [
                'office_id' => 1,
                'ClaimProcNum' => 2,
                'Status' => 1,
                'ClaimPaymentNum' => 0,
                'PlanNum' => 1,
                'ClinicNum' => 1,
                'FeeBilled' => '500.00',
                'WriteOff' => '100.00',
                'InsPayAmt' => '400.00',
                'ProcDate' => '2026-07-06',
                'PatNum' => 102,
            ],
        ]);

        DB::table('od_procedure_logs')->insert([
            [
                'office_id' => 1,
                'ProcNum' => 101,
                'PatNum' => 101,
                'ProcDate' => '2026-07-05',
                'DateTP' => null,
                'ProcFee' => 1000.00,
                'ProcStatus' => 'C',
                'AptNum' => 0,
                'ClinicNum' => 1,
            ],
            [
                'office_id' => 1,
                'ProcNum' => 102,
                'PatNum' => 102,
                'ProcDate' => '2026-07-06',
                'DateTP' => null,
                'ProcFee' => 500.00,
                'ProcStatus' => 'C',
                'AptNum' => 0,
                'ClinicNum' => 1,
            ],
            [
                'office_id' => 1,
                'ProcNum' => 103,
                'PatNum' => 101,
                'ProcDate' => '2026-07-07',
                'DateTP' => '2026-07-07',
                'ProcFee' => 500.00,
                'ProcStatus' => '1',
                'AptNum' => 5501,
                'ClinicNum' => 1,
            ],
        ]);
        DB::table('od_adjustments')->insert([
            [
                'office_id' => 1,
                'PatNum' => 101,
                'ProvNum' => 1,
                'AdjAmt' => 0.00,
                'AdjDate' => '2026-07-05',
                'ClinicNum' => 1,
            ],
        ]);

        DB::table('od_pay_splits')->insert([
            [
                'office_id' => 1,
                'PatNum' => 101,
                'ProvNum' => 1,
                'SplitAmt' => 1200.00,
                'DatePay' => '2026-07-05',
                'ClinicNum' => 1,
            ],
        ]);

        $response = $this->get(route('operations.data', [
            'tab' => 'payors',
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-15',
        ]));

        $response->assertOk();

        $spec = $response->original->getData()['spec'] ?? null;
        $this->assertNotNull($spec);

        $total = $spec['total'];

        // Assert By Payor totals
        $this->assertEquals(1500.00, $total['gross']);
        $this->assertEquals(-300.00, $total['adjustment']);
        $this->assertEquals(100.00, $total['pct_ttl']);
        $this->assertEquals(1200.00, $total['net']);
        $this->assertEquals(1200.00, $total['collection']);
        $this->assertEquals(2, $total['pts_visits']);
        $this->assertEquals(2, $total['npt_visit']);
        $this->assertEquals(100.00, $total['case_acceptance']);

        // Assert Per Working Day totals
        $this->assertEquals(600.00, $total['pwd_production']);
        $this->assertEquals(1, $total['pwd_pts_visit']);
        $this->assertEquals(1, $total['pwd_npt_visit']);

        // Assert Per Patient Visit totals
        $this->assertEquals(600.00, $total['ppv_production']);
        $this->assertEquals(1, $total['ppv_procedures']);

        // Assert Per Procedure totals
        $this->assertEquals(600.00, $total['pp_production']);
    }

    public function test_offices_tab_includes_completed_procedures_with_procstatus_2(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 10,
                'PatNum' => 201,
                'ClinicNum' => 1,
                'ProcFee' => 750.00,
                'ProcStatus' => '2',
                'ProcDate' => '2026-07-08',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
            [
                'ProcNum' => 11,
                'PatNum' => 202,
                'ClinicNum' => 1,
                'ProcFee' => 250.00,
                'ProcStatus' => 'C',
                'ProcDate' => '2026-07-09',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
        ]);

        $response = $this->get(route('operations.data', [
            'tab' => 'offices',
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-15',
        ]));

        $response->assertOk();
        $spec = $response->original->getData()['spec'] ?? null;
        $this->assertNotNull($spec);

        $total = $spec['total'];
        $this->assertEquals(1000.00, $total['gross']);
    }

    public function test_payors_tab_rolls_up_subplans_into_single_carrier_row_and_deduplicates_visits(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Seed carrier & plans
        DB::table('od_carriers')->insert([
            'CarrierNum' => 1029,
            'CarrierName' => 'Delta Dental of MI',
        ]);
        DB::table('od_insplans')->insert([
            ['PlanNum' => 101, 'CarrierNum' => 1029, 'GroupName' => 'Group A'],
            ['PlanNum' => 102, 'CarrierNum' => 1029, 'GroupName' => 'Group B'],
        ]);

        // Map patients to claim procs
        DB::table('od_claim_procs')->insert([
            ['ClaimProcNum' => 1, 'Status' => 1, 'ClaimPaymentNum' => 0, 'FeeBilled' => 0, 'InsPayAmt' => 0, 'PatNum' => 301, 'PlanNum' => 101, 'ClinicNum' => 1, 'ProcDate' => '2026-07-05', 'WriteOff' => 0],
            ['ClaimProcNum' => 2, 'Status' => 1, 'ClaimPaymentNum' => 0, 'FeeBilled' => 0, 'InsPayAmt' => 0, 'PatNum' => 302, 'PlanNum' => 102, 'ClinicNum' => 1, 'ProcDate' => '2026-07-05', 'WriteOff' => 0],
        ]);

        // Both procedures completed on the same date for the same clinic under different plans
        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 301,
                'PatNum' => 301,
                'ClinicNum' => 1,
                'ProcFee' => 300.00,
                'ProcStatus' => 'C',
                'ProcDate' => '2026-07-05',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
            [
                'ProcNum' => 302,
                'PatNum' => 302,
                'ClinicNum' => 1,
                'ProcFee' => 200.00,
                'ProcStatus' => 'C',
                'ProcDate' => '2026-07-05',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
        ]);

        $response = $this->get(route('operations.data', [
            'tab' => 'payors',
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-15',
        ]));

        $response->assertOk();
        $spec = $response->original->getData()['spec'] ?? null;
        $this->assertNotNull($spec);

        $rows = $spec['rows'];
        // Ensure both sub-plans are rolled up into ONE single row for Delta Dental
        $this->assertCount(1, $rows);
        $this->assertEquals('Delta Dental of MI - 1029', $rows[0]['payor']);
        $this->assertEquals(500.00, $rows[0]['gross']);
        $this->assertEquals(500.00, $rows[0]['net']);
        $this->assertEquals(2, $rows[0]['pts_visits']);
        $this->assertEquals(1, $rows[0]['working_days']);
        $this->assertEquals(2, $rows[0]['procedures']);
    }

    public function test_services_tab_returns_age_brackets_with_unknown_after_greater_than_70(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        DB::table('od_patients')->insert([
            [
                'PatNum' => 401,
                'FName' => 'Child',
                'LName' => 'Patient',
                'Birthdate' => '2020-01-01',
                'PatStatus' => '0',
            ],
            [
                'PatNum' => 402,
                'FName' => 'Senior',
                'LName' => 'Patient',
                'Birthdate' => '1950-01-01',
                'PatStatus' => '0',
            ],
            [
                'PatNum' => 403,
                'FName' => 'Unknown',
                'LName' => 'Patient',
                'Birthdate' => '0001-01-01',
                'PatStatus' => '0',
            ],
        ]);

        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 401,
                'PatNum' => 401,
                'ClinicNum' => 1,
                'ProcFee' => 100.00,
                'ProcStatus' => 'C',
                'ProcDate' => '2026-07-05',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
            [
                'ProcNum' => 402,
                'PatNum' => 402,
                'ClinicNum' => 1,
                'ProcFee' => 150.00,
                'ProcStatus' => 'C',
                'ProcDate' => '2026-07-05',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
            [
                'ProcNum' => 403,
                'PatNum' => 403,
                'ClinicNum' => 1,
                'ProcFee' => 200.00,
                'ProcStatus' => 'C',
                'ProcDate' => '2026-07-05',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
        ]);

        $response = $this->get(route('operations.data', [
            'tab' => 'services',
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-15',
        ]));

        $response->assertOk();
        $spec = $response->original->getData()['spec'] ?? null;
        $this->assertNotNull($spec);

        $ageRows = $spec['age_brackets']['rows'] ?? [];
        $labels = array_column($ageRows, 'label');

        $this->assertEquals([
            '0-9',
            '10-19',
            '20-29',
            '30-39',
            '40-49',
            '50-59',
            '60-69',
            '>70',
            'Unknown',
        ], $labels);

        $countsByLabel = array_combine($labels, array_column($ageRows, 'count'));
        $this->assertEquals(1, $countsByLabel['0-9']);
        $this->assertEquals(1, $countsByLabel['>70']);
        $this->assertEquals(1, $countsByLabel['Unknown']);
        $this->assertEquals(0, $countsByLabel['10-19']);
        $this->assertEquals(3, $spec['age_brackets']['total']);
    }

    public function test_trends_tab_renders_and_computes_metrics_properly(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Render operations page on trends tab - verify filter is present
        $pageResponse = $this->get(route('operations.tab', 'trends'));
        $pageResponse->assertOk();
        $pageResponse->assertSee('id="opsTrendsMetricWrapper"', false);
        $pageResponse->assertSee('By Office - Production');
        $pageResponse->assertSee('BYO Collection');

        // Seed sample pay splits and procedure logs
        DB::table('od_pay_splits')->insert([
            [
                'ProvNum' => 1,
                'SplitAmt' => 500.00,
                'DatePay' => '2026-06-15',
                'ClinicNum' => 1,
            ],
        ]);

        // When end date is in August 2026, trends range spans from Aug 2025 to Aug 2026
        $dataResponse = $this->get(route('operations.data', [
            'tab' => 'trends',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-25',
            'metric' => 'BYO Collection',
        ]));

        $dataResponse->assertOk();
        $spec = $dataResponse->original->getData()['spec'] ?? null;
        $this->assertNotNull($spec);
        $this->assertArrayHasKey('labels', $spec);
        $this->assertCount(13, $spec['labels']);
        $this->assertEquals('Aug 2025', $spec['labels'][0]);
        $this->assertEquals('Aug 2026', $spec['labels'][12]);

        // When end date is in March 2025, trends range spans from Mar 2024 to Mar 2025
        $marchResponse = $this->get(route('operations.data', [
            'tab' => 'trends',
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-15',
            'metric' => 'BYO Production',
        ]));

        $marchResponse->assertOk();
        $marchSpec = $marchResponse->original->getData()['spec'] ?? null;
        $this->assertNotNull($marchSpec);
        $this->assertCount(13, $marchSpec['labels']);
        $this->assertEquals('Mar 2024', $marchSpec['labels'][0]);
        $this->assertEquals('Mar 2025', $marchSpec['labels'][12]);

        // Test Doc Production, Hyg Collection, Pending Tx, Appointments, Active Pts metrics
        foreach (['BYO Doc Production', 'BYO Hyg Collection', 'BYO $ in Pen. Tx', 'BYO Pts Appointment', 'BYO Active Pts Count'] as $met) {
            $resp = $this->get(route('operations.data', [
                'tab' => 'trends',
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-25',
                'metric' => $met,
            ]));
            $resp->assertOk();
            $s = $resp->original->getData()['spec'] ?? null;
            $this->assertNotNull($s);
            $this->assertCount(13, $s['labels']);
        }
    }

    public function test_act_pts_breakdown_drilldown_renders_with_and_without_clinic_num(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        DB::table('od_providers')->insert([
            'ProvNum' => 81,
            'Abbr' => 'ELIAS',
            'LName' => 'Elias',
            'PName' => 'Kathy',
        ]);

        DB::table('od_patients')->insert([
            'PatNum' => 22057,
            'LName' => 'Akinbode',
            'FName' => 'Erioluwa',
        ]);

        // Procedure completed 20 months ago (within 24-month window, outside 18-month window)
        DB::table('od_procedure_logs')->insert([
            'ProcNum' => 991,
            'PatNum' => 22057,
            'ProvNum' => 81,
            'ClinicNum' => 0,
            'ProcFee' => 150.00,
            'ProcStatus' => '2',
            'ProcDate' => '2025-01-15 10:00:00',
            'MedicalCode' => '',
            'ToothNum' => '',
        ]);

        // Request drilldown with clinic_num=0
        $responseWithZero = $this->get(route('operations.drilldown', [
            'metric' => 'act_pts_count',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-26',
            'clinic_num' => '0',
        ]));

        $responseWithZero->assertOk();
        $responseWithZero->assertSee('Akinbode, Erioluwa');
        $responseWithZero->assertSee('81 - ELIAS');
        $responseWithZero->assertSee('Kathy Elias');

        // Request drilldown without clinic_num (All Clinics)
        $responseWithoutClinic = $this->get(route('operations.drilldown', [
            'metric' => 'act_pts_count',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-26',
        ]));

        $responseWithoutClinic->assertOk();
        $responseWithoutClinic->assertSee('Akinbode, Erioluwa');
        $responseWithoutClinic->assertSee('81 - ELIAS');
        $responseWithoutClinic->assertSee('Kathy Elias');
    }

    public function test_active_patient_count_matches_between_offices_tab_and_services_tab_age_bracket(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Patient 1: Active PatStatus '0'
        DB::table('od_patients')->insert([
            'PatNum' => 501,
            'LName' => 'Smith',
            'FName' => 'John',
            'Birthdate' => '1990-05-15',
            'PatStatus' => '0',
        ]);

        // Patient 2: Inactive PatStatus '1' but had procedure in 24 months
        DB::table('od_patients')->insert([
            'PatNum' => 502,
            'LName' => 'Doe',
            'FName' => 'Jane',
            'Birthdate' => '2005-08-20',
            'PatStatus' => '1',
        ]);

        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 8001,
                'PatNum' => 501,
                'ClinicNum' => 1,
                'ProcFee' => 200.00,
                'ProcStatus' => '2',
                'ProcDate' => '2026-06-10 10:00:00',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
            [
                'ProcNum' => 8002,
                'PatNum' => 502,
                'ClinicNum' => 1,
                'ProcFee' => 300.00,
                'ProcStatus' => '2',
                'ProcDate' => '2026-07-12 11:00:00',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
        ]);

        // Query Offices tab data
        $officesResp = $this->get(route('operations.data', [
            'tab' => 'offices',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-25',
        ]));
        $officesResp->assertOk();
        $officesSpec = $officesResp->original->getData()['spec'] ?? null;
        $officesActiveCount = $officesSpec['rows'][0]['act_pts_count'] ?? null;

        // Query Services tab data
        $servicesResp = $this->get(route('operations.data', [
            'tab' => 'services',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-25',
        ]));
        $servicesResp->assertOk();
        $servicesSpec = $servicesResp->original->getData()['spec'] ?? null;
        $servicesAgeTotal = $servicesSpec['age_brackets']['total'] ?? null;

        $this->assertEquals(2, $officesActiveCount);
        $this->assertEquals(2, $servicesAgeTotal);
        $this->assertEquals($officesActiveCount, $servicesAgeTotal);
    }

    public function test_per_working_days_rounds_to_nearest_integer_instead_of_ceiling(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 159 visits / 12 working days = 13.25 -> must round to 13 (not 14)
        // 26 NPT visits / 12 working days = 2.1666 -> must round to 2 (not 3)
        $this->assertSame(13, (int) round(159 / 12));
        $this->assertSame(2, (int) round(26 / 12));

        // Seed 1 clinic schedule with 12 working days
        DB::table('od_schedules')->insert(
            collect(range(1, 12))->map(fn ($d) => [
                'ClinicNum' => 1,
                'ProvNum' => 1,
                'SchedDate' => sprintf('2026-08-%02d', $d),
                'StartTime' => '09:00:00',
                'StopTime' => '17:00:00',
                'SchedType' => 0,
                'Status' => 2,
            ])->all()
        );

        // 159 distinct patients with completed procedures spread across the 12 days
        DB::table('od_procedure_logs')->insert(
            collect(range(1, 159))->map(fn ($i) => [
                'ProcNum' => 9000 + $i,
                'PatNum' => 1000 + $i,
                'ClinicNum' => 1,
                'ProcFee' => 100.00,
                'ProcStatus' => '2',
                'ProcDate' => sprintf('2026-08-%02d 10:00:00', ($i % 12) + 1),
                'MedicalCode' => '',
                'ToothNum' => '',
            ])->all()
        );

        $response = $this->get(route('operations.data', [
            'tab' => 'offices',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-25',
        ]));

        $response->assertOk();
        $spec = $response->original->getData()['spec'] ?? null;
        $this->assertNotNull($spec);

        $row = $spec['rows'][0] ?? [];
        $this->assertSame(159, $row['pts_visit']);
        $this->assertSame(12, $row['working_days']);
        $this->assertSame(13, $row['pwd_pts_visit']);
    }

    public function test_performance_tab_returns_exact_17_columns_and_calculated_metrics_and_aggregates(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Seed 1 completed procedure on 2026-08-04 ($1000)
        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 101,
                'PatNum' => 501,
                'ClinicNum' => 1,
                'ProcFee' => 1000.00,
                'ProcStatus' => 'C',
                'ProcDate' => '2026-08-04',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
            // 1 treatment planned procedure on 2026-08-07 ($100)
            [
                'ProcNum' => 102,
                'PatNum' => 502,
                'ClinicNum' => 1,
                'ProcFee' => 100.00,
                'ProcStatus' => '1',
                'ProcDate' => '2026-08-07',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
            // 1 unscheduled treatment planned procedure on 2026-08-06 ($500)
            [
                'ProcNum' => 103,
                'PatNum' => 503,
                'ClinicNum' => 1,
                'ProcFee' => 500.00,
                'ProcStatus' => 'TP',
                'ProcDate' => '2026-08-06',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
        ]);

        // Seed adjustment on 2026-08-04 (-$50)
        DB::table('od_adjustments')->insert([
            [
                'ProvNum' => 1,
                'AdjAmt' => -50.00,
                'AdjDate' => '2026-08-04',
                'ClinicNum' => 1,
            ],
        ]);

        // Seed collection on 2026-08-04 ($800)
        DB::table('od_pay_splits')->insert([
            [
                'ProvNum' => 1,
                'SplitAmt' => 800.00,
                'DatePay' => '2026-08-04',
                'ClinicNum' => 1,
            ],
        ]);

        // Seed scheduled appointments
        DB::table('od_appointments')->insert([
            [
                'AptNum' => 1,
                'PatNum' => 501,
                'ClinicNum' => 1,
                'AptDateTime' => '2026-08-04 10:00:00',
                'AptStatus' => 2,
                'IsNewPatient' => 1,
            ],
            [
                'AptNum' => 2,
                'PatNum' => 502,
                'ClinicNum' => 1,
                'AptDateTime' => '2026-08-07 11:00:00',
                'AptStatus' => 1,
                'IsNewPatient' => 0,
            ],
        ]);

        // Query performance data for 2 days (2026-08-04 to 2026-08-05)
        $response = $this->get(route('operations.data', [
            'tab' => 'performance',
            'start_date' => '2026-08-04',
            'end_date' => '2026-08-05',
        ]));

        $response->assertOk();
        $spec = $response->original->getData()['spec'] ?? null;
        $this->assertNotNull($spec);

        // Verify exact 17 columns
        $this->assertCount(17, $spec['columns']);
        $columnKeys = array_column($spec['columns'], 'key');
        $expectedKeys = [
            'date', 'goal', 'actual_production', 'actual_collection',
            'actual_pts_visit', 'actual_npt_visit', 'sched_production',
            'sched_pts_visit', 'sched_new_pts_visit', 'open_appt_hours',
            'unscheduled_tx', 'booked_production', 'booked_prod_pct_goal',
            'actual_prod_vs_goal', 'actual_vs_sched_prod', 'act_vs_sched_pts',
            'act_vs_sched_npts',
        ];
        $this->assertSame($expectedKeys, $columnKeys);

        // Verify grouped headers (Actual, Scheduled, Booked, Variance)
        $this->assertNotEmpty($spec['groups']);
        $this->assertSame([
            ['label' => 'Actual', 'span' => 4],
            ['label' => 'Scheduled', 'span' => 5],
            ['label' => 'Booked', 'span' => 2],
            ['label' => 'Variance', 'span' => 4],
        ], $spec['groups']);

        // Check rows
        $rows = $spec['rows'];
        $this->assertCount(2, $rows);

        // Row 1: 2026-08-04
        $r1 = $rows[0];
        $this->assertEquals('Tuesday - August 04, 2026', $r1['date']);
        $this->assertEquals(950.00, $r1['actual_production']); // 1000 - 50 = 950
        $this->assertEquals(800.00, $r1['actual_collection']);
        $this->assertEquals(1, $r1['actual_pts_visit']);
        $this->assertEquals(950.00, $r1['booked_production']);
        $this->assertEquals(950.00, $r1['actual_prod_vs_goal']);
        $this->assertEquals(950.00, $r1['actual_vs_sched_prod']);
        $this->assertEquals(0, $r1['act_vs_sched_pts']); // 1 actual - 1 sched = 0

        // Check Total row
        $total = $spec['total'];
        $this->assertEquals(950.00, $total['actual_production']);
        $this->assertEquals(800.00, $total['actual_collection']);
        $this->assertEquals(1, $total['actual_pts_visit']);
        $this->assertEquals(950.00, $total['booked_production']);

        // Check Average row (over 2 days)
        $avg = $spec['average'];
        $this->assertEquals(475.00, $avg['actual_production']); // 950 / 2 = 475
        $this->assertEquals(400.00, $avg['actual_collection']); // 800 / 2 = 400
        $this->assertEquals(1, $avg['actual_pts_visit']); // round(1 / 2) = 1
    }

    public function test_compliance_tab_returns_exact_grouped_headers_and_provider_metrics_and_aggregates(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Seed provider 1 with completed procedures and adjustments
        DB::table('od_providers')->insert([
            ['ProvNum' => 10, 'LName' => 'Zeitoun', 'PName' => 'Ali', 'Abbr' => 'ZEIT'],
            ['ProvNum' => 20, 'LName' => 'Elias', 'PName' => 'Kathy', 'Abbr' => 'ELIA'],
        ]);

        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 201,
                'PatNum' => 601,
                'ClinicNum' => 1,
                'ProvNum' => 10,
                'ProcFee' => 1000.00,
                'ProcStatus' => 'C',
                'ProcDate' => '2026-08-04',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
            [
                'ProcNum' => 202,
                'PatNum' => 602,
                'ClinicNum' => 1,
                'ProvNum' => 10,
                'ProcFee' => 500.00,
                'ProcStatus' => 'C',
                'ProcDate' => '2026-08-05',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
        ]);

        // Seed adjustment for Prov 10 (-$100) and Prov 20 (-$200, adjustment only)
        DB::table('od_adjustments')->insert([
            [
                'ProvNum' => 10,
                'AdjAmt' => -100.00,
                'AdjDate' => '2026-08-04',
                'ClinicNum' => 1,
            ],
            [
                'ProvNum' => 20,
                'AdjAmt' => -200.00,
                'AdjDate' => '2026-08-05',
                'ClinicNum' => 1,
            ],
        ]);

        $response = $this->get(route('operations.data', [
            'tab' => 'compliance',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-10',
        ]));

        $response->assertOk();
        $spec = $response->original->getData()['spec'] ?? null;
        $this->assertNotNull($spec);

        // Verify columns (14 columns)
        $this->assertCount(14, $spec['columns']);
        $columnKeys = array_column($spec['columns'], 'key');
        $expectedKeys = [
            'location', 'provider', 'total_prod', 'total_visits',
            'pwd_prod', 'pwd_proc', 'ppv_prod', 'ppv_proc',
            'ppv_fil', 'ppv_crn', 'ppv_ext', 'ppv_pulp', 'ppv_root',
            'pp_prod',
        ];
        $this->assertSame($expectedKeys, $columnKeys);

        // Verify standard groups with leadSpan = 2 (Location + Provider)
        $this->assertNotEmpty($spec['groups']);
        $this->assertSame('Provider', $spec['groups'][0]['label']);
        $this->assertSame(2, $spec['groups'][0]['span']);

        // Check rows
        $rows = $spec['rows'];
        $this->assertCount(2, $rows);

        // Prov 10: Zeitoun ($1500 - $100 = $1400 net production, 2 visits, 2 working days, 2 procedures)
        $r1 = collect($rows)->firstWhere('prov_num', 10);
        $this->assertNotNull($r1);
        $this->assertEquals('Zeitoun, Ali', $r1['provider']);
        $this->assertEquals(1400.00, $r1['total_prod']);
        $this->assertEquals(2, $r1['total_visits']);
        $this->assertEquals(700.00, $r1['pwd_prod']); // 1400 / 2 days = 700
        $this->assertEquals(1.0, $r1['pwd_proc']); // 2 procs / 2 days = 1.0
        $this->assertEquals(700.00, $r1['ppv_prod']); // 1400 / 2 visits = 700
        $this->assertEquals(1, $r1['ppv_proc']); // 2 / 2 = 1
        $this->assertEquals(700.00, $r1['pp_prod']); // 1400 / 2 procs = 700

        // Prov 20: Elias (-$200 net production from adjustment only)
        $r2 = collect($rows)->firstWhere('prov_num', 20);
        $this->assertNotNull($r2);
        $this->assertEquals('Elias, Kathy', $r2['provider']);
        $this->assertEquals(-200.00, $r2['total_prod']);
        $this->assertEquals(0, $r2['total_visits']);

        // Check Total row
        $total = $spec['total'];
        $this->assertEquals(1200.00, $total['total_prod']); // 1400 - 200 = 1200
        $this->assertEquals(2, $total['total_visits']);
        $this->assertEquals(700.00, $total['pwd_prod']);
        $this->assertEquals(700.00, $total['ppv_prod']);

        // Check Average row (2 active providers)
        $avg = $spec['average'];
        $this->assertEquals(600.00, $avg['total_prod']); // 1200 / 2 = 600
        $this->assertEquals(1, $avg['total_visits']); // 2 / 2 = 1
        $this->assertEquals(350.00, $avg['pwd_prod']); // 700 / 2 = 350
        $this->assertEquals(350.00, $avg['ppv_prod']); // 700 / 2 = 350
    }

    public function test_providers_tab_returns_exact_25_columns_and_calculated_metrics_and_aggregates(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Seed provider specialties in od_definitions
        DB::table('od_definitions')->insert([
            ['DefNum' => 268, 'Category' => 35, 'ItemName' => 'Not Set', 'ItemValue' => '', 'ItemColor' => 0, 'IsHidden' => 0],
            ['DefNum' => 269, 'Category' => 35, 'ItemName' => 'Invisalign', 'ItemValue' => '', 'ItemColor' => 0, 'IsHidden' => 0],
        ]);

        // Seed providers
        DB::table('od_providers')->insert([
            ['ProvNum' => 83, 'LName' => 'Zeitoun', 'PName' => 'Ali', 'Abbr' => 'ZEIT', 'Specialty' => 268, 'IsNotPerson' => 0],
            ['ProvNum' => 64, 'LName' => 'Haddow', 'PName' => 'Mason', 'Abbr' => 'HADD', 'Specialty' => 269, 'IsNotPerson' => 0],
            ['ProvNum' => 46, 'LName' => 'Detroit Dental Care, PC', 'PName' => '', 'Abbr' => 'DETD', 'Specialty' => 0, 'IsNotPerson' => 1],
            ['ProvNum' => 76, 'LName' => 'Heller', 'PName' => 'Landi', 'Abbr' => 'HELL', 'Specialty' => 268, 'IsNotPerson' => 0], // Inactive provider
        ]);

        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 301,
                'PatNum' => 701,
                'ClinicNum' => 1,
                'ProvNum' => 83,
                'ProcFee' => 1000.00,
                'ProcStatus' => 'C',
                'ProcDate' => '2026-08-04',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
            [
                'ProcNum' => 302,
                'PatNum' => 702,
                'ClinicNum' => 1,
                'ProvNum' => 64,
                'ProcFee' => 500.00,
                'ProcStatus' => 'C',
                'ProcDate' => '2026-08-05',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
        ]);

        // Seed payments and adjustments
        DB::table('od_pay_splits')->insert([
            ['ProvNum' => 83, 'SplitAmt' => 800.00, 'DatePay' => '2026-08-04', 'ClinicNum' => 1],
            ['ProvNum' => 64, 'SplitAmt' => 400.00, 'DatePay' => '2026-08-05', 'ClinicNum' => 1],
        ]);

        DB::table('od_adjustments')->insert([
            ['ProvNum' => 83, 'AdjAmt' => -100.00, 'AdjDate' => '2026-08-04', 'ClinicNum' => 1],
            ['ProvNum' => 46, 'AdjAmt' => 50.00, 'AdjDate' => '2026-08-05', 'ClinicNum' => 1],
        ]);

        $response = $this->get(route('operations.data', [
            'tab' => 'providers',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-10',
        ]));

        $response->assertOk();
        $spec = $response->original->getData()['spec'] ?? null;
        $this->assertNotNull($spec);

        // Verify columns (25 columns)
        $this->assertCount(25, $spec['columns']);
        $columnKeys = array_column($spec['columns'], 'key');
        $expectedKeys = [
            'location', 'line_of_business', 'provider', 'provider_id',
            'gross', 'net', 'adjustment', 'collection', 'pts_visits', 'npt_visits', 'working_days', 'procedures', 'retention',
            'pwd_production', 'pwd_collection', 'pwd_pts_visits', 'pwd_npt_visits',
            'ppv_production', 'ppv_collection', 'ppv_procedures',
            'pp_production', 'pp_collection',
            'production_goal', 'actual_production', 'variance',
        ];
        $this->assertSame($expectedKeys, $columnKeys);

        // Verify Provider column is sticky
        $provCol = collect($spec['columns'])->firstWhere('key', 'provider');
        $this->assertNotNull($provCol);
        $this->assertTrue($provCol['sticky'] ?? false);

        // Check rows (Inactive provider Heller (76) excluded, active 83, 64, 46 present)
        $rows = $spec['rows'];
        $this->assertCount(3, $rows);
        $this->assertNull(collect($rows)->firstWhere('prov_num', 76));

        // Prov 83: Zeitoun
        $r83 = collect($rows)->firstWhere('prov_num', 83);
        $this->assertNotNull($r83);
        $this->assertEquals('Not Set', $r83['line_of_business']);
        $this->assertEquals(1000.00, $r83['gross']);
        $this->assertEquals(900.00, $r83['net']);
        $this->assertEquals(-100.00, $r83['adjustment']);
        $this->assertEquals(800.00, $r83['collection']);

        // Prov 64: Haddow
        $r64 = collect($rows)->firstWhere('prov_num', 64);
        $this->assertNotNull($r64);
        $this->assertEquals('Invisalign', $r64['line_of_business']);
        $this->assertEquals(500.00, $r64['gross']);
        $this->assertEquals(500.00, $r64['net']);
        $this->assertEquals(400.00, $r64['collection']);

        // Prov 46: Detroit Dental Care (Hygiene)
        $r46 = collect($rows)->firstWhere('prov_num', 46);
        $this->assertNotNull($r46);
        $this->assertEquals('Hygiene', $r46['line_of_business']);
        $this->assertEquals(50.00, $r46['net']);

        // Check Total row
        $total = $spec['total'];
        $this->assertEquals(1500.00, $total['gross']);
        $this->assertEquals(1450.00, $total['net']); // 900 + 500 + 50
        $this->assertEquals(-50.00, $total['adjustment']);
        $this->assertEquals(1200.00, $total['collection']);

        // Check Average row
        $avg = $spec['average'];
        $this->assertEquals(500.00, $avg['gross']); // 1500 / 3
        $this->assertEquals(483.33, $avg['net']); // 1450 / 3
    }

    public function test_working_days_drilldown_only_includes_positive_production_dates(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Seed provider and procedures with positive and zero fee
        DB::table('od_providers')->insert([
            ['ProvNum' => 83, 'LName' => 'Zeitoun', 'PName' => 'Ali', 'Abbr' => 'ZEIT', 'Specialty' => 268, 'IsNotPerson' => 0],
        ]);

        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 401,
                'PatNum' => 801,
                'ClinicNum' => 1,
                'ProvNum' => 83,
                'ProcFee' => 1200.00,
                'ProcStatus' => 'C',
                'ProcDate' => '2026-08-04',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
            [
                'ProcNum' => 402,
                'PatNum' => 802,
                'ClinicNum' => 1,
                'ProvNum' => 83,
                'ProcFee' => 0.00,
                'ProcStatus' => 'C',
                'ProcDate' => '2026-08-11',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
        ]);

        $response = $this->get(route('operations.drilldown', [
            'metric' => 'working_days',
            'prov_num' => 83,
            'start' => '2026-08-01',
            'end' => '2026-08-31',
        ]));

        $response->assertOk();
        $response->assertViewHas('rows');
        $rows = $response->original->getData()['rows'] ?? [];

        // Only Aug 4 (with $1200 production) should be returned, Aug 11 ($0) should be excluded
        $this->assertCount(1, $rows);
        $this->assertEquals(1200.00, $rows[0]['production']);
    }

    public function test_payors_tab_returns_exact_columns_and_calculated_metrics_and_aggregates(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Seed carriers and plans
        DB::table('od_carriers')->insert([
            ['CarrierNum' => 1029, 'CarrierName' => 'Delta Dental of MI'],
            ['CarrierNum' => 7, 'CarrierName' => 'Medicaid'],
        ]);

        DB::table('od_insplans')->insert([
            ['PlanNum' => 130, 'CarrierNum' => 1029, 'GroupName' => 'Delta'],
            ['PlanNum' => 13161, 'CarrierNum' => 1029, 'GroupName' => 'Delta Sub'],
            ['PlanNum' => 1, 'CarrierNum' => 7, 'GroupName' => 'Medicaid'],
        ]);

        // Seed claim_procs for patient plan mapping
        DB::table('od_claim_procs')->insert([
            ['ClaimProcNum' => 1, 'PatNum' => 101, 'PlanNum' => 130, 'ProcDate' => '2026-08-05', 'WriteOff' => 894.75, 'ClinicNum' => 1, 'Status' => 0, 'ClaimPaymentNum' => 0],
            ['ClaimProcNum' => 2, 'PatNum' => 102, 'PlanNum' => 13161, 'ProcDate' => '2026-08-05', 'WriteOff' => 0.00, 'ClinicNum' => 1, 'Status' => 0, 'ClaimPaymentNum' => 0],
            ['ClaimProcNum' => 3, 'PatNum' => 103, 'PlanNum' => 1, 'ProcDate' => '2026-08-05', 'WriteOff' => 0.00, 'ClinicNum' => 1, 'Status' => 0, 'ClaimPaymentNum' => 0],
        ]);

        // Seed procedure logs
        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 501,
                'PatNum' => 101,
                'ClinicNum' => 1,
                'ProvNum' => 83,
                'ProcFee' => 4500.00,
                'ProcStatus' => 'C',
                'ProcDate' => '2026-08-05',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
            [
                'ProcNum' => 502,
                'PatNum' => 102,
                'ClinicNum' => 1,
                'ProvNum' => 83,
                'ProcFee' => 0.00,
                'ProcStatus' => 'C',
                'ProcDate' => '2026-08-05',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
            [
                'ProcNum' => 503,
                'PatNum' => 103,
                'ClinicNum' => 1,
                'ProvNum' => 83,
                'ProcFee' => 0.00,
                'ProcStatus' => 'C',
                'ProcDate' => '2026-08-06',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
            [
                'ProcNum' => 504,
                'PatNum' => 104, // No insurance patient
                'ClinicNum' => 1,
                'ProvNum' => 83,
                'ProcFee' => 5000.00,
                'ProcStatus' => 'C',
                'ProcDate' => '2026-08-07',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
        ]);

        // Seed collections
        DB::table('od_pay_splits')->insert([
            ['ProvNum' => 83, 'PatNum' => 101, 'SplitAmt' => 3855.25, 'DatePay' => '2026-08-05', 'ClinicNum' => 1],
            ['ProvNum' => 83, 'PatNum' => 104, 'SplitAmt' => 4000.00, 'DatePay' => '2026-08-07', 'ClinicNum' => 1],
        ]);

        $response = $this->get(route('operations.data', [
            'tab' => 'payors',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]));

        $response->assertOk();
        $spec = $response->original->getData()['spec'] ?? null;
        $this->assertNotNull($spec);

        // Verify columns
        $this->assertCount(16, $spec['columns']);
        $columnKeys = array_column($spec['columns'], 'key');
        $expectedKeys = [
            'location', 'payor', 'gross', 'net', 'pct_ttl', 'adjustment', 'collection',
            'pts_visits', 'npt_visit', 'case_acceptance',
            'pwd_production', 'pwd_pts_visit', 'pwd_npt_visit',
            'ppv_production', 'ppv_procedures', 'pp_production',
        ];
        $this->assertSame($expectedKeys, $columnKeys);

        // Verify rows: 3 payors (Delta plans consolidated into Delta Dental of MI - 1029)
        $rows = $spec['rows'];
        $this->assertCount(3, $rows);

        $delta = collect($rows)->firstWhere('payor', 'Delta Dental of MI - 1029');
        $this->assertNotNull($delta);
        $this->assertEquals(4500.00, $delta['gross']);
        $this->assertEquals(3605.25, $delta['net']); // 4500 - 894.75 writeoff
        $this->assertEquals(3855.25, $delta['collection']);
        $this->assertEquals(1, $delta['working_days']); // 1 day with positive fee
        $this->assertEquals(3605.25, $delta['pwd_production']);

        $noIns = collect($rows)->firstWhere('payor', 'No Insurance - 999999');
        $this->assertNotNull($noIns);
        $this->assertEquals(5000.00, $noIns['gross']);
        $this->assertEquals(5000.00, $noIns['net']);
        $this->assertEquals(1, $noIns['working_days']);

        $medicaid = collect($rows)->firstWhere('payor', 'Medicaid - 7');
        $this->assertNotNull($medicaid);
        $this->assertEquals(0.00, $medicaid['gross']);
        $this->assertEquals(0, $medicaid['working_days']);

        // Check Total row
        $total = $spec['total'];
        $this->assertEquals(9500.00, $total['gross']);
        $this->assertEquals(8605.25, $total['net']);
        $this->assertEquals(7855.25, $total['collection']);
        $this->assertEquals(8605.25, $total['pwd_production']);

        // Check Average row
        $avg = $spec['average'];
        $this->assertEquals(3166.67, $avg['gross']); // 9500 / 3
        $this->assertEquals(2868.42, $avg['net']); // 8605.25 / 3
    }

    public function test_cancellation_drilldown_renders_and_includes_export_csv(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        DB::table('od_providers')->insert([
            'ProvNum' => 99,
            'Abbr' => 'TESTPROV',
            'LName' => 'Tester',
            'PName' => 'John',
        ]);

        DB::table('od_patients')->insert([
            'PatNum' => 5001,
            'LName' => 'Doe',
            'FName' => 'Jane',
        ]);

        DB::table('od_appointments')->insert([
            'AptNum' => 7001,
            'PatNum' => 5001,
            'ProvNum' => 99,
            'ClinicNum' => 1,
            'AptStatus' => '5', // Broken / Cancelled
            'AptDateTime' => '2026-08-10 14:00:00',
            'Note' => 'Patient called to cancel appointment',
            'ProcDescript' => 'Prophy and Exam',
        ]);

        DB::table('od_procedure_logs')->insert([
            'ProcNum' => 8001,
            'PatNum' => 5001,
            'ProvNum' => 99,
            'ClinicNum' => 1,
            'AptNum' => 7001,
            'ProcFee' => 250.00,
            'ProcStatus' => '2',
            'ProcDate' => '2026-08-10 14:00:00',
        ]);

        $response = $this->get(route('operations.drilldown', [
            'metric' => 'cancellation',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'clinic_num' => 1,
        ]));

        $response->assertOk();
        $response->assertSee('Cancellation Breakdown');
        $response->assertSee('Export CSV');
        $response->assertSee('exportDrilldownModalCsv');
        $response->assertSee('Doe, Jane');
        $response->assertSee('Tester, John');
        $response->assertSee('Patient called to cancel appointment');
        $response->assertSee('$ 250.00');
    }

    public function test_total_appointments_drilldown_renders_and_includes_export_csv(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        DB::table('od_providers')->insert([
            'ProvNum' => 101,
            'Abbr' => 'SMITH',
            'LName' => 'Smith',
            'PName' => 'Sarah',
        ]);

        DB::table('od_patients')->insert([
            'PatNum' => 6001,
            'LName' => 'Johnson',
            'FName' => 'Robert',
        ]);

        DB::table('od_appointments')->insert([
            'AptNum' => 9001,
            'PatNum' => 6001,
            'ProvNum' => 101,
            'ClinicNum' => 1,
            'AptStatus' => '1', // Scheduled
            'AptDateTime' => '2026-08-15 10:00:00',
            'Note' => 'Regular checkup and cleaning',
            'ProcDescript' => 'Cleaning and exam',
        ]);

        DB::table('od_procedure_logs')->insert([
            'ProcNum' => 9501,
            'PatNum' => 6001,
            'ProvNum' => 101,
            'ClinicNum' => 1,
            'AptNum' => 9001,
            'ProcFee' => 180.00,
            'ProcStatus' => '2',
            'ProcDate' => '2026-08-15 10:00:00',
        ]);

        $response = $this->get(route('operations.drilldown', [
            'metric' => 'total_appointments',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'clinic_num' => 1,
        ]));

        $response->assertOk();
        $response->assertSee('Total Appointments Count Breakdown');
        $response->assertSee('Export CSV');
        $response->assertSee('exportDrilldownModalCsv');
        $response->assertSee('Johnson, Robert');
        $response->assertSee('Smith, Sarah');
        $response->assertSee('Regular checkup and cleaning');
        $response->assertSee('Scheduled');
        $response->assertSee('$ 180.00');
    }

    public function test_trends_tab_tx_plans_presented_and_active_pts(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Seed completing procedures for working days
        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 10001,
                'PatNum' => 7001,
                'ClinicNum' => 1,
                'ProcFee' => 200.00,
                'ProcStatus' => '2',
                'ProcDate' => '2026-08-10',
                'DateTP' => '2026-08-10',
            ],
            [
                'ProcNum' => 10002,
                'PatNum' => 7002,
                'ClinicNum' => 1,
                'ProcFee' => 300.00,
                'ProcStatus' => '2',
                'ProcDate' => '2026-08-11',
                'DateTP' => '2026-08-11',
            ],
            [
                'ProcNum' => 10003,
                'PatNum' => 7003,
                'ClinicNum' => 1,
                'ProcFee' => 150.00,
                'ProcStatus' => '1',
                'ProcDate' => '2026-08-11',
                'DateTP' => '2026-08-11',
            ],
        ]);

        $responseTx = $this->get(route('operations.data', [
            'tab' => 'trends',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'metric' => 'BYO Avg # of Tx Plans Presented',
        ]));

        $responseTx->assertOk();
        $specTx = $responseTx->original->getData()['spec'] ?? null;
        $this->assertNotNull($specTx);
        // Column type should be 'number', not 'money'
        $this->assertEquals('number', $specTx['columns'][1]['type']);

        $responseActive = $this->get(route('operations.data', [
            'tab' => 'trends',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'metric' => 'BYO Active Pts Count',
        ]));

        $responseActive->assertOk();
        $specActive = $responseActive->original->getData()['spec'] ?? null;
        $this->assertNotNull($specActive);
        $this->assertEquals('number', $specActive['columns'][1]['type']);

        $responseActivePct = $this->get(route('operations.data', [
            'tab' => 'trends',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'metric' => 'BYO Active Pts',
        ]));

        $responseActivePct->assertOk();
        $specActivePct = $responseActivePct->original->getData()['spec'] ?? null;
        $this->assertNotNull($specActivePct);
        $this->assertEquals('percent', $specActivePct['columns'][1]['type']);

        $responseRetention = $this->get(route('operations.data', [
            'tab' => 'trends',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'metric' => 'BYO Patient Retention',
        ]));

        $responseRetention->assertOk();
        $specRetention = $responseRetention->original->getData()['spec'] ?? null;
        $this->assertNotNull($specRetention);
        $this->assertEquals('percent', $specRetention['columns'][1]['type']);

        $responseCopay = $this->get(route('operations.data', [
            'tab' => 'trends',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'metric' => 'BYO Co Pay Coll',
        ]));

        $responseCopay->assertOk();
        $specCopay = $responseCopay->original->getData()['spec'] ?? null;
        $this->assertNotNull($specCopay);
        $this->assertEquals('percent', $specCopay['columns'][1]['type']);
    }

    public function test_retention_drilldown_renders_retention_cohort(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        DB::table('od_procedures')->insert([
            ['CodeNum' => 101, 'ProcCode' => 'D0120'],
            ['CodeNum' => 102, 'ProcCode' => 'D0140'],
        ]);

        DB::table('od_patients')->insert([
            ['PatNum' => 8001, 'LName' => 'Taylor', 'FName' => 'James'],
            ['PatNum' => 8002, 'LName' => 'Miller', 'FName' => 'Emily'],
        ]);

        // Patient 1: Visit in 36m and also in 18m -> Retained
        // Patient 2: Visit in 36m only (not in 18m) -> Inactive / Lost
        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 12001,
                'PatNum' => 8001,
                'ClinicNum' => 1,
                'CodeNum' => 101,
                'ProcFee' => 100.00,
                'ProcStatus' => '2',
                'ProcDate' => '2024-05-10 10:00:00',
            ],
            [
                'ProcNum' => 12002,
                'PatNum' => 8001,
                'ClinicNum' => 1,
                'CodeNum' => 101,
                'ProcFee' => 100.00,
                'ProcStatus' => '2',
                'ProcDate' => '2026-07-15 10:00:00',
            ],
            [
                'ProcNum' => 12003,
                'PatNum' => 8002,
                'ClinicNum' => 1,
                'CodeNum' => 102,
                'ProcFee' => 120.00,
                'ProcStatus' => '2',
                'ProcDate' => '2024-01-10 10:00:00',
            ],
        ]);

        $response = $this->get(route('operations.drilldown', [
            'metric' => 'retention',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'clinic_num' => 1,
        ]));

        $response->assertOk();
        $response->assertSee('Retention Breakdown');
        $response->assertSee('Taylor, James');
        $response->assertSee('Miller, Emily');
        $response->assertSee('Retained Patient');
        $response->assertSee('Inactive / Lost');
    }

    public function test_trends_tab_renders_two_tables_for_patient_retention(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        DB::table('od_procedures')->insert([
            ['CodeNum' => 101, 'ProcCode' => 'D0120'],
        ]);

        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 13001,
                'PatNum' => 8101,
                'ClinicNum' => 1,
                'CodeNum' => 101,
                'ProcFee' => 100.00,
                'ProcStatus' => '2',
                'ProcDate' => '2025-05-10 10:00:00',
            ],
            [
                'ProcNum' => 13002,
                'PatNum' => 8102,
                'ClinicNum' => 1,
                'CodeNum' => 101,
                'ProcFee' => 100.00,
                'ProcStatus' => '2',
                'ProcDate' => '2025-10-15 10:00:00',
            ],
        ]);

        $response = $this->get(route('operations.data', [
            'tab' => 'trends',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'metric' => 'BYO Patient Retention',
        ]));

        $response->assertOk();
        $response->assertSee('Patient Retention Rate (%)');
        $response->assertSee('Patient Retention Breakdown');
        $response->assertSee('Active Patient count');
        $response->assertSee('New Patient count');
        $response->assertSee('Retention count');
    }

    public function test_performance_columns_have_drilldown_types_and_rows_have_date_raw(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $service = app(OperationsAnalyticsService::class);
        $spec = $service->performance('2026-08-01', '2026-08-07');

        $this->assertArrayHasKey('columns', $spec);
        $cols = collect($spec['columns'])->keyBy('key');

        $this->assertEquals('actual_production', $cols['actual_production']['drilldown_type'] ?? null);
        $this->assertEquals('actual_collection', $cols['actual_collection']['drilldown_type'] ?? null);
        $this->assertEquals('actual_pts_visit', $cols['actual_pts_visit']['drilldown_type'] ?? null);
        $this->assertEquals('actual_npt_visit', $cols['actual_npt_visit']['drilldown_type'] ?? null);
        $this->assertEquals('sched_production', $cols['sched_production']['drilldown_type'] ?? null);
        $this->assertEquals('sched_pts_visit', $cols['sched_pts_visit']['drilldown_type'] ?? null);
        $this->assertEquals('sched_new_pts_visit', $cols['sched_new_pts_visit']['drilldown_type'] ?? null);
        $this->assertEquals('open_appt_hours', $cols['open_appt_hours']['drilldown_type'] ?? null);
        $this->assertEquals('unscheduled_tx', $cols['unscheduled_tx']['drilldown_type'] ?? null);
        $this->assertEquals('booked_production', $cols['booked_production']['drilldown_type'] ?? null);

        $this->assertNotEmpty($spec['rows']);
        $firstRow = $spec['rows'][0];
        $this->assertArrayHasKey('date_raw', $firstRow);
        $this->assertEquals('2026-08-01', $firstRow['date_raw']);
    }

    public function test_actual_collection_drilldown_returns_patient_and_provider_links_and_totals(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $patient = OdPatient::create([
            'PatNum' => 8801,
            'LName' => 'Smith',
            'FName' => 'John',
            'PatStatus' => '0',
            'ClinicNum' => 1,
        ]);

        $provider = OdProvider::create([
            'ProvNum' => 9901,
            'Abbr' => 'JSM',
            'LName' => 'Smith',
            'FName' => 'Jane',
        ]);

        DB::table('od_pay_splits')->insert([
            'SplitNum' => 7701,
            'PatNum' => $patient->PatNum,
            'ProvNum' => $provider->ProvNum,
            'DatePay' => '2026-08-04 10:00:00',
            'SplitAmt' => 1250.00,
            'ClinicNum' => 1,
        ]);

        $response = $this->get(route('operations.drilldown', [
            'metric' => 'actual_collection',
            'start_date' => '2026-08-04',
            'end_date' => '2026-08-04',
        ]));

        $response->assertOk();
        $response->assertSee('Actual Collection Breakdown');
        $response->assertSee('Patient ID');
        $response->assertSee('Provider Ids');
        $response->assertSee('Providers');
        $response->assertSee('Collection');
        $response->assertSee('Smith, John');
        $response->assertSee('9901 - JSM');
        $response->assertSee("openPatient('8801')", false);
        $response->assertSee("openProviderModal('9901')", false);
        $response->assertSee('1,250.00');
    }

    public function test_actual_production_drilldown_returns_breakdown_with_patient_link(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $patient = OdPatient::create([
            'PatNum' => 8802,
            'LName' => 'Doe',
            'FName' => 'Alice',
            'PatStatus' => '0',
            'ClinicNum' => 1,
        ]);

        $provider = OdProvider::create([
            'ProvNum' => 9902,
            'Abbr' => 'ADOE',
            'LName' => 'Doe',
            'FName' => 'Arthur',
        ]);

        OdProcedureLog::create([
            'ProcNum' => 6601,
            'PatNum' => $patient->PatNum,
            'ProvNum' => $provider->ProvNum,
            'ProcDate' => '2026-08-04',
            'ProcFee' => 750.00,
            'ProcStatus' => '2',
            'ClinicNum' => 1,
            'CodeNum' => 10,
            'MedicalCode' => '',
            'ToothNum' => '',
        ]);

        $response = $this->get(route('operations.drilldown', [
            'metric' => 'actual_production',
            'start_date' => '2026-08-04',
            'end_date' => '2026-08-04',
        ]));

        $response->assertOk();
        $response->assertSee('Actual Production Breakdown');
        $response->assertSee('Doe, Alice');
        $response->assertSee('9902 - ADOE');
        $response->assertSee("openPatient('8802')", false);
        $response->assertSee('750.00');
    }

    public function test_actual_pts_visit_drilldown_renders_correctly(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $patient = OdPatient::create([
            'PatNum' => 8809,
            'LName' => 'Miller',
            'FName' => 'Sarah',
            'PatStatus' => '0',
            'ClinicNum' => 1,
        ]);

        $provider = OdProvider::create([
            'ProvNum' => 9909,
            'Abbr' => 'SMIL',
            'LName' => 'Miller',
            'FName' => 'Steve',
        ]);

        // 2 completed procedures across 2 different dates ($300 and $200) -> Gross = $500, Visited = 2
        DB::table('od_procedure_logs')->insert([
            'ProcNum' => 7791,
            'PatNum' => $patient->PatNum,
            'ProvNum' => $provider->ProvNum,
            'ProcDate' => '2026-08-01',
            'ProcFee' => 300.00,
            'ProcStatus' => '2',
            'ClinicNum' => 1,
            'CodeNum' => 10,
            'MedicalCode' => '',
            'ToothNum' => '',
        ]);
        DB::table('od_procedure_logs')->insert([
            'ProcNum' => 7792,
            'PatNum' => $patient->PatNum,
            'ProvNum' => $provider->ProvNum,
            'ProcDate' => '2026-08-02',
            'ProcFee' => 200.00,
            'ProcStatus' => '2',
            'ClinicNum' => 1,
            'CodeNum' => 11,
            'MedicalCode' => '',
            'ToothNum' => '',
        ]);

        // Adjustment of $50
        DB::table('od_adjustments')->insert([
            'AdjNum' => 4401,
            'PatNum' => $patient->PatNum,
            'ProvNum' => $provider->ProvNum,
            'ClinicNum' => 1,
            'AdjDate' => '2026-08-01',
            'AdjAmt' => 50.00,
            'AdjType' => 1,
        ]);

        // WriteOff of $100
        DB::table('od_claim_procs')->insert([
            'ClaimProcNum' => 3301,
            'PatNum' => $patient->PatNum,
            'ProvNum' => $provider->ProvNum,
            'ClinicNum' => 1,
            'ProcDate' => '2026-08-02',
            'WriteOff' => 100.00,
            'Status' => 1,
            'ClaimPaymentNum' => 0,
            'PlanNum' => 1,
            'InsPayAmt' => 0,
            'InsPayEst' => 0,
            'FeeBilled' => 0,
        ]);

        $response = $this->get(route('operations.drilldown', [
            'metric' => 'actual_pts_visit',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-03',
        ]));

        $response->assertOk();
        $response->assertSee('Actual Pts Visits Breakdown');
        $response->assertSee('Patient ID');
        $response->assertSee('Patient');
        $response->assertSee('Gross production');
        $response->assertSee('Adjustment');
        $response->assertSee('Writeoff');
        $response->assertSee('Visited');
        $response->assertSee('Production ($)');
        $response->assertSee('Miller, Sarah');
        $response->assertSee("openPatient('8809')", false);
        $response->assertSee('500.00'); // Gross
        $response->assertSee('50.00');  // Adj
        $response->assertSee('100.00'); // WriteOff
        $response->assertSee('450.00'); // Net Production: 500 + 50 - 100
    }

    public function test_scheduled_production_and_visits_drilldown_renders_correctly(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $patient = OdPatient::create([
            'PatNum' => 8803,
            'LName' => 'Taylor',
            'FName' => 'Robert',
            'PatStatus' => '0',
            'ClinicNum' => 1,
        ]);

        $provider = OdProvider::create([
            'ProvNum' => 9903,
            'Abbr' => 'RTAY',
            'LName' => 'Taylor',
            'FName' => 'Rachel',
        ]);

        DB::table('od_procedure_logs')->insert([
            'ProcNum' => 7702,
            'PatNum' => $patient->PatNum,
            'ProvNum' => $provider->ProvNum,
            'ProcDate' => '2026-08-05',
            'ProcFee' => 450.00,
            'ProcStatus' => '1',
            'ClinicNum' => 1,
            'CodeNum' => 11,
            'MedicalCode' => '',
            'ToothNum' => '',
        ]);

        // Second procedure for same patient ($50.00) to verify grouping into $500.00
        DB::table('od_procedure_logs')->insert([
            'ProcNum' => 7703,
            'PatNum' => $patient->PatNum,
            'ProvNum' => $provider->ProvNum,
            'ProcDate' => '2026-08-05',
            'ProcFee' => 50.00,
            'ProcStatus' => '1',
            'ClinicNum' => 1,
            'CodeNum' => 12,
            'MedicalCode' => '',
            'ToothNum' => '',
        ]);

        DB::table('od_appointments')->insert([
            'AptNum' => 5501,
            'PatNum' => $patient->PatNum,
            'ProvNum' => $provider->ProvNum,
            'AptDateTime' => '2026-08-05 14:00:00',
            'AptStatus' => 1,
            'ClinicNum' => 1,
            'IsNewPatient' => 0,
            'Pattern' => '///',
        ]);

        $respProd = $this->get(route('operations.drilldown', [
            'metric' => 'sched_production',
            'start_date' => '2026-08-05',
            'end_date' => '2026-08-05',
        ]));
        $respProd->assertOk();
        $respProd->assertSee('Scheduled Production Breakdown');
        $respProd->assertSee('Taylor, Robert');
        $respProd->assertSee('9903 - RTAY');
        $respProd->assertSee("openPatient('8803')", false);
        $respProd->assertSee('500.00');

        $respVisit = $this->get(route('operations.drilldown', [
            'metric' => 'sched_pts_visit',
            'start_date' => '2026-08-05',
            'end_date' => '2026-08-05',
        ]));
        $respVisit->assertOk();
        $respVisit->assertSee('Scheduled Patient Visits Breakdown');
        $respVisit->assertSee('Taylor, Robert');
        $respVisit->assertSee('Scheduled');
    }

    public function test_open_appt_hours_and_unscheduled_tx_drilldown_render_correctly(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $patient = OdPatient::create([
            'PatNum' => 8804,
            'LName' => 'Clark',
            'FName' => 'David',
            'PatStatus' => '0',
            'ClinicNum' => 1,
        ]);

        $provider = OdProvider::create([
            'ProvNum' => 9904,
            'Abbr' => 'DCLK',
            'LName' => 'Clark',
            'FName' => 'Diana',
        ]);

        DB::table('od_schedules')->insert([
            'ScheduleNum' => 4401,
            'SchedDate' => '2026-08-06',
            'StartTime' => '08:00:00',
            'StopTime' => '17:00:00',
            'SchedType' => 1,
            'ProvNum' => $provider->ProvNum,
            'ClinicNum' => 1,
        ]);

        OdProcedureLog::create([
            'ProcNum' => 6603,
            'PatNum' => $patient->PatNum,
            'ProvNum' => $provider->ProvNum,
            'ProcDate' => '2026-08-06',
            'ProcFee' => 920.00,
            'ProcStatus' => '1',
            'AptNum' => null,
            'ClinicNum' => 1,
            'CodeNum' => 12,
            'MedicalCode' => '',
            'ToothNum' => '',
        ]);

        $respHours = $this->get(route('operations.drilldown', [
            'metric' => 'open_appt_hours',
            'start_date' => '2026-08-06',
            'end_date' => '2026-08-06',
        ]));
        $respHours->assertOk();
        $respHours->assertSee('Open Appointment Hours Breakdown');
        $respHours->assertSee('9904 - DCLK');
        $respHours->assertSee('9.00'); // 9 hours scheduled

        $respUnsched = $this->get(route('operations.drilldown', [
            'metric' => 'unscheduled_tx',
            'start_date' => '2026-08-06',
            'end_date' => '2026-08-06',
        ]));
        $respUnsched->assertOk();
        $respUnsched->assertSee('Unscheduled Treatment Breakdown');
        $respUnsched->assertSee('Clark, David');
        $respUnsched->assertSee('920.00');
    }

    public function test_claims_tab_has_drilldown_columns_and_drilldown_works(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        DB::table('od_patients')->insert([
            'PatNum' => 991,
            'LName' => 'Miller',
            'FName' => 'Sarah',
            'PatStatus' => '0',
        ]);

        DB::table('od_procedures')->insert([
            'CodeNum' => 881,
            'ProcCode' => 'D0120',
            'Descript' => 'Periodic Oral Evaluation',
        ]);

        DB::table('od_procedure_logs')->insert([
            'ProcNum' => 7701,
            'PatNum' => 991,
            'ProvNum' => 1,
            'ClinicNum' => 1,
            'CodeNum' => 881,
            'ProcFee' => 150.00,
            'ProcStatus' => '2',
            'ProcDate' => '2026-08-15 10:00:00',
            'ToothNum' => '14',
            'Surf' => 'MOD',
            'MedicalCode' => '',
        ]);

        DB::table('od_claim_procs')->insert([
            'ClaimProcNum' => 8801,
            'ProcNum' => 7701,
            'PatNum' => 991,
            'ProvNum' => 1,
            'ClinicNum' => 1,
            'ProcDate' => '2026-08-15',
            'Status' => 6,
            'ClaimPaymentNum' => 0,
            'FeeBilled' => 150.00,
            'office_id' => 1,
        ]);

        $claimsResp = $this->get(route('operations.data', [
            'tab' => 'claims',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]));
        $claimsResp->assertOk();
        $spec = $claimsResp->original->getData()['spec'] ?? null;
        $this->assertNotEmpty($spec);
        $this->assertEquals('claims_day', $spec['columns'][15]['drilldown_type'] ?? null);
        $this->assertEquals('2026-08-15', $spec['columns'][15]['date'] ?? null);
        $this->assertEquals('Y', $spec['rows'][0]['d_15'] ?? null);

        // Test drilldown endpoint
        $drillResp = $this->get(route('operations.drilldown', [
            'metric' => 'claims_day',
            'clinic_num' => 1,
            'start_date' => '2026-08-15',
            'end_date' => '2026-08-15',
        ]));
        $drillResp->assertOk();
        $drillResp->assertSee('Claims &amp; Daily Procedures (Aug 15, 2026)', false);
        $drillResp->assertSee('Miller, Sarah');
        $drillResp->assertSee('D0120');
        $drillResp->assertSee('150.00');
    }

    public function test_claims_tab_tallies_only_days_with_claim_procedures(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Day 4: completed procedure with claimproc -> should be Y
        DB::table('od_procedure_logs')->insert([
            'ProcNum' => 7801,
            'PatNum' => 991,
            'ProvNum' => 1,
            'ClinicNum' => 1,
            'CodeNum' => 881,
            'ProcFee' => 200.00,
            'ProcStatus' => '2',
            'ProcDate' => '2026-08-04 10:00:00',
            'ToothNum' => '',
            'Surf' => '',
            'MedicalCode' => '',
        ]);
        DB::table('od_claim_procs')->insert([
            'ClaimProcNum' => 8901,
            'ProcNum' => 7801,
            'PatNum' => 991,
            'ProvNum' => 1,
            'ClinicNum' => 1,
            'ProcDate' => '2026-08-04',
            'Status' => 6,
            'ClaimPaymentNum' => 0,
            'FeeBilled' => 200.00,
            'office_id' => 1,
        ]);

        // Day 7: completed procedure WITHOUT claimproc -> should be N
        DB::table('od_procedure_logs')->insert([
            'ProcNum' => 7802,
            'PatNum' => 991,
            'ProvNum' => 1,
            'ClinicNum' => 1,
            'CodeNum' => 881,
            'ProcFee' => 100.00,
            'ProcStatus' => '2',
            'ProcDate' => '2026-08-07 10:00:00',
            'ToothNum' => '',
            'Surf' => '',
            'MedicalCode' => '',
        ]);

        $service = app(OperationsAnalyticsService::class);
        $spec = $service->claims('2026-08-01', '2026-08-31', 'default', [1]);

        $this->assertNotEmpty($spec['rows']);
        $row = $spec['rows'][0];
        $this->assertEquals('Y', $row['d_4']);
        $this->assertEquals('N', $row['d_7']);
        $this->assertEquals('N', $row['d_1']);
    }
}
