<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProviderPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_performance_returns_correct_kpis_and_breakdowns_when_no_production(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 1. Insert mock providers
        DB::table('od_providers')->insert([
            [
                'ProvNum' => 64,
                'LName' => 'Haddow',
                'PName' => 'Mason',
                'Abbr' => 'HADD',
                'Specialty' => 268,
                'IsHidden' => 'false',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ProvNum' => 81,
                'LName' => 'Elias',
                'PName' => 'Kathy',
                'Abbr' => 'ELIAS',
                'Specialty' => 268,
                'IsHidden' => 'false',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 2. Insert mock adjustments in range
        DB::table('od_adjustments')->insert([
            [
                'ProvNum' => 64,
                'AdjAmt' => -1530.00,
                'AdjDate' => '2026-07-05',
                'ClinicNum' => 0,
            ],
            [
                'ProvNum' => 81,
                'AdjAmt' => -900.00,
                'AdjDate' => '2026-07-06',
                'ClinicNum' => 0,
            ],
        ]);

        // 3. Insert mock collections in range
        DB::table('od_pay_splits')->insert([
            [
                'ProvNum' => 64,
                'SplitAmt' => 15570.00,
                'DatePay' => '2026-07-05',
                'ClinicNum' => 0,
            ],
        ]);

        // 4. Insert mock appointments in range
        DB::table('od_appointments')->insert([
            [
                'AptNum' => 1,
                'ProvNum' => 64,
                'AptStatus' => '1', // Scheduled
                'AptDateTime' => '2026-07-05 10:00:00',
                'ClinicNum' => 0,
            ],
            [
                'AptNum' => 2,
                'ProvNum' => 81,
                'AptStatus' => '2', // Completed
                'AptDateTime' => '2026-07-06 14:00:00',
                'ClinicNum' => 0,
            ],
        ]);

        // 5. Query the provider performance endpoint
        $response = $this->getJson(route('dashboard.providers', [
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-15',
        ]));

        // 6. Assert response
        $response->assertOk();
        $data = $response->json();

        $this->assertCount(2, $data);

        // Haddow is first because they had positive collection activity, net, or gross ranking
        $haddow = collect($data)->firstWhere('ProvNum', 64);
        $this->assertNotNull($haddow);
        $this->assertEquals(0, $haddow['gross_production']);
        $this->assertEquals(-1530.00, $haddow['adjustments']);
        $this->assertEquals(15570.00, $haddow['collections']);
        $this->assertEquals(-1530.00, $haddow['net_production']);
        $this->assertEquals(1, $haddow['appointment_count']);
        $this->assertEquals('Invisalign', $haddow['specialty']);
        $this->assertEquals('8 Mile', $haddow['location']);

        $elias = collect($data)->firstWhere('ProvNum', 81);
        $this->assertNotNull($elias);
        $this->assertEquals(0, $elias['gross_production']);
        $this->assertEquals(-900.00, $elias['adjustments']);
        $this->assertEquals(0, $elias['collections']);
        $this->assertEquals(-900.00, $elias['net_production']);
        $this->assertEquals(1, $elias['appointment_count']);
        $this->assertEquals('Invisalign', $elias['specialty']);
        $this->assertEquals('8 Mile', $elias['location']);
    }
}
