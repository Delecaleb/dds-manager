<?php

namespace Tests\Feature;

use App\Models\Office;
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

    public function test_deposit_slip_multi_office_isolation(): void
    {
        $user = User::factory()->create();

        $office1 = Office::create([
            'name' => '8 Mile Office',
            'is_active' => true,
        ]);
        $office2 = Office::create([
            'name' => 'Adrian Office',
            'is_active' => true,
        ]);

        // Definition for Office 1
        DB::table('od_definitions')->insert([
            'office_id' => $office1->id,
            'DefNum' => 101,
            'Category' => 1,
            'ItemName' => 'Check (8 Mile)',
        ]);

        // Definition for Office 2 with same DefNum
        DB::table('od_definitions')->insert([
            'office_id' => $office2->id,
            'DefNum' => 101,
            'Category' => 1,
            'ItemName' => 'Credit Card (Adrian)',
        ]);

        // Office 1 Payment ($300)
        DB::table('od_payments')->insert([
            'office_id' => $office1->id,
            'PayNum' => 1001,
            'ClinicNum' => 0,
            'PayAmt' => 300.00,
            'PayDate' => '2026-08-10',
            'PayType' => 101,
        ]);

        // Office 2 Payment ($700)
        DB::table('od_payments')->insert([
            'office_id' => $office2->id,
            'PayNum' => 1001, // same PayNum across offices
            'ClinicNum' => 0,
            'PayAmt' => 700.00,
            'PayDate' => '2026-08-10',
            'PayType' => 101,
        ]);

        // Test querying Office 1
        $res1 = $this->actingAs($user)->getJson(route('deposits.data', [
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'office_id' => $office1->id,
        ]));
        $res1->assertOk();
        $this->assertEquals(300.00, $res1->json('summary.total_amount'));
        $this->assertEquals('Check (8 Mile)', $res1->json('deposits.0.type'));

        // Test querying Office 2
        $res2 = $this->actingAs($user)->getJson(route('deposits.data', [
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'office_id' => $office2->id,
        ]));
        $res2->assertOk();
        $this->assertEquals(700.00, $res2->json('summary.total_amount'));
        $this->assertEquals('Credit Card (Adrian)', $res2->json('deposits.0.type'));

        // Test All Locations
        $resAll = $this->actingAs($user)->getJson(route('deposits.data', [
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'office_id' => 'all',
        ]));
        $resAll->assertOk();
        $this->assertEquals(1000.00, $resAll->json('summary.total_amount'));
    }
}
