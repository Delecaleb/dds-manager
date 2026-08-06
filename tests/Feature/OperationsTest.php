<?php

namespace Tests\Feature;

use App\Models\User;
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
                'ProcFee' => 1000.00,
                'ProcStatus' => 'C',
                'ClinicNum' => 1,
            ],
            [
                'office_id' => 1,
                'ProcNum' => 102,
                'PatNum' => 102,
                'ProcDate' => '2026-07-06',
                'ProcFee' => 500.00,
                'ProcStatus' => 'C',
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
        $this->assertEquals(0.00, $total['adjustment']);
        $this->assertEquals(100.00, $total['pct_ttl']);
        $this->assertEquals(1200.00, $total['net']);
        $this->assertEquals(1200.00, $total['collection']);
        $this->assertEquals(2, $total['pts_visits']);
        $this->assertEquals(2, $total['npt_visit']);
        $this->assertTrue(is_null($total['case_acceptance']) || $total['case_acceptance'] == 0);

        // Assert Per Working Day totals
        $this->assertEquals(600.00, $total['pwd_production']);
        $this->assertEquals(1.0, $total['pwd_pts_visit']);
        $this->assertEquals(1.0, $total['pwd_npt_visit']);

        // Assert Per Patient Visit totals
        $this->assertEquals(600.00, $total['ppv_production']);
        $this->assertEquals(1.0, $total['ppv_procedures']);

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
}
