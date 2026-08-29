<?php

namespace Tests\Feature;

use App\Domain\Production\ProductionService;
use App\Domain\Support\MetricFilter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CollectionParityTest extends TestCase
{
    use RefreshDatabase;

    public function test_collections_include_both_patient_and_insurance_payments(): void
    {
        $user = User::factory()->create();

        // 0. Insert mock provider
        DB::table('od_providers')->insert([
            'ProvNum' => 2,
            'LName' => 'Doctor',
            'PName' => 'Test',
            'Abbr' => 'DOC',
            'Specialty' => 0,
            'IsHidden' => 0,
        ]);

        // 1. Insert patient payments in od_payments and od_pay_splits
        DB::table('od_payments')->insert([
            [
                'PayNum' => 1,
                'ClinicNum' => 1,
                'PayAmt' => 250.00,
                'PayDate' => '2025-06-10',
                'PayType' => 0,
            ],
            [
                'PayNum' => 2,
                'ClinicNum' => 1,
                'PayAmt' => 150.00,
                'PayDate' => '2025-06-15',
                'PayType' => 0,
            ],
        ]);

        DB::table('od_pay_splits')->insert([
            [
                'SplitNum' => 1,
                'PayNum' => 1,
                'PatNum' => 10,
                'ProvNum' => 2,
                'ClinicNum' => 1,
                'DatePay' => '2025-06-10',
                'SplitAmt' => 250.00,
            ],
            [
                'SplitNum' => 2,
                'PayNum' => 2,
                'PatNum' => 20,
                'ProvNum' => 2,
                'ClinicNum' => 1,
                'DatePay' => '2025-06-15',
                'SplitAmt' => 150.00,
            ],
        ]);

        // 2. Insert insurance claim payments in od_claim_payments and od_claim_procs
        DB::table('od_claim_payments')->insert([
            [
                'ClaimPaymentNum' => 1,
                'DepositNum' => 0,
                'ClinicNum' => 1,
                'CheckAmt' => 500.00,
                'CheckDate' => '2025-06-20',
                'DateIssued' => '2025-06-20',
                'CheckNum' => 'CHK-100',
                'BankBranch' => 'Main',
                'CarrierName' => 'Delta Dental',
                'PayType' => 0,
                'IsPartial' => 0,
                'SecUserNumEntry' => 1,
                'SecDateEntry' => '2025-06-20',
                'SecDateTEdit' => '2025-06-20',
                'PayGroup' => 0,
                'Note' => '',
            ],
        ]);

        DB::table('od_claim_procs')->insert([
            [
                'ClaimProcNum' => 1,
                'ClaimNum' => 1,
                'PatNum' => 10,
                'ProvNum' => 2,
                'FeeBilled' => 600.00,
                'InsPayEst' => 500.00,
                'DedApplied' => 0,
                'Status' => 1,
                'InsPayAmt' => 500.00,
                'Remarks' => '',
                'ClaimPaymentNum' => 1,
                'PlanNum' => 5,
                'DateCP' => '2025-06-20',
                'WriteOff' => 100.00,
                'CodeSent' => '',
                'AllowedOverride' => 0,
                'Percentage' => 0,
                'PercentOverride' => 0,
                'CopayAmt' => 0,
                'NoBillIns' => 0,
                'PaidOtherIns' => 0,
                'BaseEst' => 0,
                'CopayOverride' => 0,
                'ProcDate' => '2025-06-01',
                'DateEntry' => '2025-06-20',
                'LineNumber' => 1,
                'DedEst' => 0,
                'DedEstOverride' => 0,
                'InsEstTotal' => 0,
                'InsEstTotalOverride' => 0,
                'PaidOtherInsOverride' => 0,
                'EstimateNote' => '',
                'WriteOffEst' => 0,
                'WriteOffEstOverride' => 0,
                'ClinicNum' => 1,
                'InsSubNum' => 0,
                'PaymentRow' => 0,
                'PayPlanNum' => 0,
                'ClaimPaymentTracking' => 0,
                'SecUserNumEntry' => 1,
                'SecDateEntry' => '2025-06-20',
                'SecDateTEdit' => '2025-06-20',
                'DateSuppReceived' => '0001-01-01',
                'DateInsFinalized' => '0001-01-01',
                'IsTransfer' => 0,
                'ClaimAdjReasonCodes' => '',
                'IsOverpay' => 0,
                'SecurityHash' => '',
                'Etrans835AttachNum' => 0,
            ],
        ]);

        // Expected Total = Patient Payments ($400) + Insurance Payments ($500) = $900.00
        $filter = new MetricFilter('2025-06-01', '2025-06-30');
        $productionService = app(ProductionService::class);
        $this->assertEquals(900.00, $productionService->collection($filter));

        // 3. Deposit slip endpoint verifies $900.00
        $resDeposits = $this->actingAs($user)->getJson(route('deposits.data', [
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-30',
        ]));
        $resDeposits->assertOk();
        $this->assertEquals(900.00, $resDeposits->json('summary.total_amount'));

        // 4. Financials revenue endpoint verifies $900.00
        $resFinancials = $this->actingAs($user)->getJson(route('financials.revenue', [
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-30',
        ]));
        $resFinancials->assertOk();
        $this->assertEquals(900.00, $resFinancials->json('collections'));

        // 5. Dashboard endpoint verifies $900.00
        $resDashboard = $this->actingAs($user)->getJson(route('dashboard.data', [
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-30',
        ]));
        $resDashboard->assertOk();
        $this->assertEquals(900.00, $resDashboard->json('collections'));

        // 6. Financials breakdown endpoint verifies $900.00
        $resBreakdown = $this->actingAs($user)->getJson(route('financials.breakdown', [
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-30',
            'type' => 'collection',
        ]));
        $resBreakdown->assertOk();
        $breakdownRows = $resBreakdown->json();
        $this->assertEquals(900.00, array_sum(array_column($breakdownRows, 'amount')));

        // 7. Dashboard Provider Performance endpoint verifies $900.00
        $resProviders = $this->actingAs($user)->getJson(route('dashboard.providers', [
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-30',
        ]));
        $resProviders->assertOk();
        $providerRows = $resProviders->json();
        $this->assertEquals(900.00, array_sum(array_column($providerRows, 'collections')));
    }
}
