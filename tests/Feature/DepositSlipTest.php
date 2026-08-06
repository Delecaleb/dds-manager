<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DepositSlipTest extends TestCase
{
    public function test_deposit_slip_page_loads_successfully(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('deposits.index'));

        $response->assertStatus(200);
        $response->assertSee('Deposit Slip');
        $response->assertSee('detailContainer');
        $response->assertSee('data-sort="office"', false);
        $response->assertSee('data-sort="patient_name"', false);
        $response->assertSee('data-sort="amount"', false);
    }

    public function test_deposit_slip_data_endpoint_returns_json(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('deposits.data', [
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'deposits',
            'details',
            'summary' => [
                'total_amount',
            ],
        ]);
    }

    public function test_deposit_slip_details_total_equals_summary_total(): void
    {
        $user = User::factory()->create();

        DB::table('od_payments')->insert([
            [
                'PayNum' => 1,
                'ClinicNum' => 1,
                'PayAmt' => 250.00,
                'PayDate' => '2026-07-10',
                'PayType' => 0,
            ],
            [
                'PayNum' => 2,
                'ClinicNum' => 1,
                'PayAmt' => 150.00,
                'PayDate' => '2026-07-15',
                'PayType' => 0,
            ],
        ]);

        DB::table('od_claim_payments')->insert([
            [
                'ClaimPaymentNum' => 1,
                'DepositNum' => 0,
                'ClinicNum' => 1,
                'CheckAmt' => 500.00,
                'CheckDate' => '2026-07-20',
                'DateIssued' => '2026-07-20',
                'CheckNum' => '12345',
                'BankBranch' => 'Main',
                'CarrierName' => 'Delta Dental',
                'PayType' => 0,
                'IsPartial' => 0,
                'SecUserNumEntry' => 1,
                'SecDateEntry' => '2026-07-20',
                'SecDateTEdit' => '2026-07-20',
                'PayGroup' => 0,
                'Note' => '',
            ],
        ]);

        $response = $this->actingAs($user)->getJson(route('deposits.data', [
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
        ]));

        $response->assertStatus(200);
        $data = $response->json();

        $summaryTotal = $data['summary']['total_amount'];
        $detailsTotal = array_sum(array_column($data['details'], 'amount'));

        $this->assertEquals(900.00, $summaryTotal);
        $this->assertEquals($summaryTotal, $detailsTotal);
    }
}
