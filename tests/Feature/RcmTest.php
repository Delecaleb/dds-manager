<?php

namespace Tests\Feature;

use App\Models\ClaimProcs;
use App\Models\OdPatient;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RcmTest extends TestCase
{
    use RefreshDatabase;

    protected Office $office;

    protected function setUp(): void
    {
        parent::setUp();

        $this->office = Office::create([
            'name' => '8 Mile',
            'is_active' => true,
        ]);
    }

    public function test_rcm_page_can_be_rendered_by_authenticated_super_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get(route('rcm.index'));

        $response->assertStatus(200);
        $response->assertSee('RCM');
        $response->assertSee('Claim Submissions');
        $response->assertSee('Payment Arrangement');
        $response->assertSee('Patients Statements');
        $response->assertSee('Point Of Service Collection');
        $response->assertSee('Adjustment');
        $response->assertSee('Dashboard');
        $response->assertSee('Collection Refund');
        $response->assertSee('Payor Overview');
    }

    public function test_rcm_page_forbidden_for_user_without_rcm_module_access(): void
    {
        $staff = User::factory()->staff()->create();
        $staff->syncModules(['patients', 'calendar']); // No RCM access

        $response = $this->actingAs($staff)->get(route('rcm.index'));

        $response->assertStatus(403);
    }

    public function test_rcm_page_accessible_for_user_with_rcm_module_access(): void
    {
        $staff = User::factory()->staff()->create();
        $staff->syncModules(['rcm']);

        $response = $this->actingAs($staff)->get(route('rcm.index'));

        $response->assertStatus(200);
    }

    public function test_rcm_data_endpoint_returns_claim_submissions_json(): void
    {
        $user = User::factory()->superAdmin()->create();

        $patient = OdPatient::create([
            'PatNum' => 101,
            'FName' => 'Stacey',
            'LName' => 'Al Harazi',
            'office_id' => $this->office->id,
        ]);

        ClaimProcs::create([
            'ClaimProcNum' => 5001,
            'ClaimNum' => 57853,
            'PatNum' => 101,
            'FeeBilled' => '345.00',
            'InsPayEst' => '220.00',
            'InsPayAmt' => '220.00',
            'WriteOff' => '50.00',
            'Status' => 0,
            'ProcDate' => '2025-01-10',
            'DateEntry' => '2025-01-09',
            'DateCP' => '2025-01-30',
            'PlanNum' => 2,
            'ClaimPaymentNum' => 0,
            'office_id' => $this->office->id,
        ]);

        $response = $this->actingAs($user)->getJson(route('rcm.data', [
            'tab' => 'claim_submissions',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'office_id' => $this->office->id,
        ]));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('tab', 'claim_submissions');
        $response->assertJsonPath('data.total', 1);
        $response->assertJsonPath('data.items.0.claim_id', 57853);
        $response->assertJsonPath('data.items.0.patient_id', 101);
        $response->assertJsonPath('data.items.0.patient_name', 'Al Harazi, Stacey');
        $response->assertJsonPath('data.items.0.claim_fee_formatted', '$ 345.00');
    }

    public function test_rcm_data_endpoint_supports_all_tabs(): void
    {
        $user = User::factory()->superAdmin()->create();

        $tabs = [
            'payment_arrangement',
            'patients_statements',
            'point_of_service',
            'adjustment',
            'dashboard',
            'collection_refund',
            'payor_overview',
        ];

        foreach ($tabs as $tab) {
            $response = $this->actingAs($user)->getJson(route('rcm.data', [
                'tab' => $tab,
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
            ]));

            $response->assertStatus(200);
            $response->assertJsonPath('success', true);
            $response->assertJsonPath('tab', $tab);
        }
    }

    public function test_point_of_service_collection_endpoint_returns_data_and_summary(): void
    {
        $user = User::factory()->superAdmin()->create();

        OdPatient::create([
            'PatNum' => 102,
            'FName' => 'Dorothy',
            'LName' => 'White',
            'BalTotal' => '150.00',
            'office_id' => $this->office->id,
        ]);

        \DB::table('od_procedure_logs')->insert([
            'ProcNum' => 9001,
            'PatNum' => 102,
            'ProcDate' => '2025-02-15',
            'ProcFee' => '250.00',
            'CodeNum' => 1,
            'ProvNum' => 10,
            'office_id' => $this->office->id,
        ]);

        $response = $this->actingAs($user)->getJson(route('rcm.data', [
            'tab' => 'point_of_service',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'office_id' => $this->office->id,
        ]));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('tab', 'point_of_service');
        $response->assertJsonPath('data.total', 1);
        $response->assertJsonPath('data.items.0.patient_id', 102);
        $response->assertJsonPath('data.items.0.patient_name', 'White, Dorothy');
        $response->assertJsonPath('data.items.0.total_amount_service_formatted', '$ 250.00');
        $response->assertJsonStructure([
            'data' => [
                'summary' => [
                    'average_past_due_formatted',
                    'total_past_due_formatted',
                    'average_total_amount_formatted',
                    'total_total_amount_formatted',
                ],
            ],
        ]);
    }

    public function test_adjustment_endpoint_returns_data_and_summary(): void
    {
        $user = User::factory()->superAdmin()->create();

        OdPatient::create([
            'PatNum' => 103,
            'FName' => 'James',
            'LName' => 'Miller',
            'office_id' => $this->office->id,
        ]);

        \DB::table('od_adjustments')->insert([
            'AdjNum' => 8001,
            'PatNum' => 103,
            'AdjDate' => '2025-03-10',
            'AdjAmt' => '75.50',
            'AdjType' => 349,
            'ProvNum' => 10,
            'AdjNote' => 'Debit adjustment for missed appointment',
            'office_id' => $this->office->id,
        ]);

        $response = $this->actingAs($user)->getJson(route('rcm.data', [
            'tab' => 'adjustment',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'office_id' => $this->office->id,
        ]));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('tab', 'adjustment');
        $response->assertJsonPath('data.total', 1);
        $response->assertJsonPath('data.items.0.patient_id', 103);
        $response->assertJsonPath('data.items.0.patient_name', 'Miller, James');
        $response->assertJsonPath('data.items.0.adj_amount_formatted', '$ 75.50');
        $response->assertJsonStructure([
            'data' => [
                'summary' => [
                    'average_formatted',
                    'total_formatted',
                ],
            ],
        ]);
    }

    public function test_rcm_export_endpoint_streams_csv(): void
    {
        $user = User::factory()->superAdmin()->create();

        foreach (['claim_submissions', 'point_of_service', 'adjustment', 'payor_overview', 'payment_arrangement', 'patients_statements'] as $tab) {
            $response = $this->actingAs($user)->get(route('rcm.export', [
                'tab' => $tab,
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
            ]));

            $response->assertStatus(200);
            $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        }
    }
}
