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

    public function test_deposit_slip_multi_clinic_scoping_and_session_persistence(): void
    {
        $user = User::factory()->create();

        $office = Office::create([
            'name' => 'Metro Dental Group',
            'is_active' => true,
        ]);

        DB::table('od_clinics')->insert([
            ['office_id' => $office->id, 'ClinicNum' => 10, 'Description' => 'North Clinic', 'Abbr' => 'North', 'ItemOrder' => 1],
            ['office_id' => $office->id, 'ClinicNum' => 20, 'Description' => 'South Clinic', 'Abbr' => 'South', 'ItemOrder' => 2],
        ]);

        DB::table('od_definitions')->insert([
            ['office_id' => $office->id, 'DefNum' => 201, 'Category' => 10, 'ItemName' => 'Cash'],
            ['office_id' => $office->id, 'DefNum' => 202, 'Category' => 10, 'ItemName' => 'Check'],
        ]);

        // Payment for Clinic 10 ($400 Cash)
        DB::table('od_payments')->insert([
            'office_id' => $office->id,
            'PayNum' => 8001,
            'ClinicNum' => 10,
            'PayAmt' => 400.00,
            'PayDate' => '2026-08-15',
            'PayType' => 201,
        ]);

        // Claim Payment for Clinic 10 ($600)
        DB::table('od_claim_payments')->insert([
            'office_id' => $office->id,
            'ClaimPaymentNum' => 8002,
            'ClinicNum' => 10,
            'CheckAmt' => 600.00,
            'CheckDate' => '2026-08-15',
            'DateIssued' => '2026-08-15',
            'CheckNum' => 'CHK10',
            'CarrierName' => 'MetLife',
            'PayType' => 0,
        ]);

        // Payment for Clinic 20 ($750 Check)
        DB::table('od_payments')->insert([
            'office_id' => $office->id,
            'PayNum' => 8003,
            'ClinicNum' => 20,
            'PayAmt' => 750.00,
            'PayDate' => '2026-08-15',
            'PayType' => 202,
        ]);

        // Claim Payment for Clinic 20 ($1250)
        DB::table('od_claim_payments')->insert([
            'office_id' => $office->id,
            'ClaimPaymentNum' => 8004,
            'ClinicNum' => 20,
            'CheckAmt' => 1250.00,
            'CheckDate' => '2026-08-15',
            'DateIssued' => '2026-08-15',
            'CheckNum' => 'CHK20',
            'CarrierName' => 'Guardian',
            'PayType' => 0,
        ]);

        // 1. When session active clinic is 10
        $res10 = $this->actingAs($user)
            ->withSession([
                'active_office_id' => $office->id,
                "active_clinic_id_{$office->id}" => 10,
            ])
            ->getJson(route('deposits.data', [
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-31',
            ]));

        $res10->assertOk();
        $this->assertEquals(1000.00, $res10->json('summary.total_amount')); // 400 + 600
        $this->assertCount(2, $res10->json('deposits'));
        $this->assertEquals('North Clinic', $res10->json('deposits.0.location'));

        // 2. When session active clinic is 20
        $res20 = $this->actingAs($user)
            ->withSession([
                'active_office_id' => $office->id,
                "active_clinic_id_{$office->id}" => 20,
            ])
            ->getJson(route('deposits.data', [
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-31',
            ]));

        $res20->assertOk();
        $this->assertEquals(2000.00, $res20->json('summary.total_amount')); // 750 + 1250
        $this->assertCount(2, $res20->json('deposits'));
        $this->assertEquals('South Clinic', $res20->json('deposits.0.location'));

        // 3. Explicit clinic_num=all
        $resAll = $this->actingAs($user)
            ->withSession([
                'active_office_id' => $office->id,
                "active_clinic_id_{$office->id}" => 10,
            ])
            ->getJson(route('deposits.data', [
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-31',
                'clinic_num' => 'all',
            ]));

        $resAll->assertOk();
        $this->assertEquals(3000.00, $resAll->json('summary.total_amount')); // 1000 + 2000
    }
}
