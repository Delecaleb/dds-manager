<?php

namespace Tests\Feature;

use App\Models\OdAdjustment;
use App\Models\OdPatient;
use App\Models\OdProcedureLog;
use App\Models\PaySplit;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class FrontOfficeCollectionsTest extends TestCase
{
    public function test_collections_stats_calculates_monthly_patient_ins_collections_and_adjustments(): void
    {
        $user = User::factory()->create();

        $monthYear = Carbon::now()->format('Y-m');
        $today = Carbon::today()->format('Y-m-d');

        // Create Patient with Balances
        OdPatient::create([
            'office_id' => 1,
            'PatNum' => 101,
            'Guarantor' => 101,
            'FName' => 'John',
            'LName' => 'Doe',
            'Bal_0_30' => 150.00,
            'Bal_31_60' => 75.00,
            'Bal_61_90' => 50.00,
            'BalOver90' => 25.00,
            'BalTotal' => 300.00,
        ]);

        // Create Completed Procedure ($1000)
        OdProcedureLog::create([
            'office_id' => 1,
            'ProcNum' => 1001,
            'PatNum' => 101,
            'ProvNum' => 1,
            'ProcDate' => $today,
            'ProcFee' => 1000.00,
            'ProcStatus' => 2, // Completed
        ]);

        // Create Patient Payment ($400)
        PaySplit::create([
            'office_id' => 1,
            'SplitNum' => 1001,
            'PatNum' => 101,
            'ProcNum' => 1001,
            'DatePay' => $today,
            'SplitAmt' => 400.00,
            'ClaimPaymentNum' => 0,
        ]);

        // Create Adjustment (-$100)
        OdAdjustment::create([
            'office_id' => 1,
            'AdjNum' => 1001,
            'PatNum' => 101,
            'ProvNum' => 1,
            'AdjDate' => $today,
            'AdjAmt' => -100.00,
            'AdjType' => 1,
        ]);

        $response = $this->actingAs($user)->getJson(route('front-office.collections-stats', [
            'month_year' => $monthYear,
        ]));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals(150.00, $data['balances']['current']);
        $this->assertEquals(300.00, $data['balances']['total']);
        $this->assertEquals(400.00, $data['collections']['pts']);
        $this->assertEquals(1000.00, $data['adjustments']['gross_production']);
        $this->assertEquals(-100.00, $data['adjustments']['total']);
        $this->assertEquals(10.00, $data['adjustments']['percent']); // 100 / 1000 * 100
    }

    public function test_collections_subtab_endpoints_respond_with_valid_data(): void
    {
        $user = User::factory()->create();
        $monthYear = Carbon::now()->format('Y-m');

        // 1. Patient Balances subtab
        $resBal = $this->actingAs($user)->getJson(route('front-office.collections-data', [
            'month_year' => $monthYear,
            'subtab' => 'patient-balances',
        ]));
        $resBal->assertStatus(200);

        // 2. CoPay Collections subtab
        $resCopay = $this->actingAs($user)->getJson(route('front-office.collections-data', [
            'month_year' => $monthYear,
            'subtab' => 'copay-collections',
        ]));
        $resCopay->assertStatus(200);

        // 3. Adjustments subtab
        $resAdj = $this->actingAs($user)->getJson(route('front-office.collections-data', [
            'month_year' => $monthYear,
            'subtab' => 'adjustments',
        ]));
        $resAdj->assertStatus(200);

        // 4. Collections daily breakdown subtab
        $resColl = $this->actingAs($user)->getJson(route('front-office.collections-data', [
            'month_year' => $monthYear,
            'subtab' => 'collections',
        ]));
        $resColl->assertStatus(200);
    }
}
