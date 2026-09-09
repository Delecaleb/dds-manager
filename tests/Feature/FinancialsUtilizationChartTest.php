<?php

namespace Tests\Feature;

use App\Domain\Support\ProcStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FinancialsUtilizationChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_utilization_chart_returns_net_production_per_provider(): void
    {
        $user = User::factory()->create();

        // Setup providers
        DB::table('od_providers')->insert([
            ['ProvNum' => 1, 'Abbr' => 'Dr Alpha', 'LName' => 'Alpha', 'PName' => 'Doc', 'IsHidden' => 0],
            ['ProvNum' => 2, 'Abbr' => 'Dr Beta', 'LName' => 'Beta', 'PName' => 'Doc', 'IsHidden' => 0],
        ]);

        // Dr Alpha: Gross 1000, Adj -100, WriteOff 200 => Net 700
        // Dr Beta: Gross 800, Adj 0, WriteOff 0 => Net 800
        // (Even though Alpha has higher gross 1000 vs 800, Beta has higher net 800 vs 700)

        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 1,
                'PatNum' => 101,
                'ProvNum' => 1,
                'ClinicNum' => 1,
                'ProcFee' => 1000.00,
                'ProcStatus' => ProcStatus::completed()[0],
                'ProcDate' => '2026-07-05',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
            [
                'ProcNum' => 2,
                'PatNum' => 102,
                'ProvNum' => 2,
                'ClinicNum' => 1,
                'ProcFee' => 800.00,
                'ProcStatus' => ProcStatus::completed()[0],
                'ProcDate' => '2026-07-06',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
        ]);

        DB::table('od_adjustments')->insert([
            [
                'ProvNum' => 1,
                'AdjAmt' => -100.00,
                'AdjDate' => '2026-07-05',
                'ClinicNum' => 1,
            ],
        ]);

        DB::table('od_claim_procs')->insert([
            [
                'ProvNum' => 1,
                'Status' => 1,
                'ClaimPaymentNum' => 0,
                'PlanNum' => 1,
                'WriteOff' => 200.00,
                'ProcDate' => '2026-07-05',
                'ClinicNum' => 1,
                'ClaimNum' => 1,
                'ClaimProcNum' => 1,
                'PatNum' => 101,
            ],
        ]);

        $response = $this->actingAs($user)->getJson('/financials/data?section=utilization-chart&start_date=2026-07-01&end_date=2026-07-31');

        $response->assertOk();
        $response->assertJsonStructure([
            'utilization' => [
                '*' => [
                    'provider',
                    'production',
                    'net_production',
                    'gross_production',
                ],
            ],
        ]);

        $utilization = $response->json('utilization');
        $this->assertCount(2, $utilization);

        // First item should be Dr Beta with Net Production 800
        $this->assertEquals('Dr Beta', $utilization[0]['provider']);
        $this->assertEquals(800.0, (float) $utilization[0]['production']);
        $this->assertEquals(800.0, (float) $utilization[0]['net_production']);
        $this->assertEquals(800.0, (float) $utilization[0]['gross_production']);

        // Second item should be Dr Alpha with Net Production 700 (Gross 1000)
        $this->assertEquals('Dr Alpha', $utilization[1]['provider']);
        $this->assertEquals(700.0, (float) $utilization[1]['production']);
        $this->assertEquals(700.0, (float) $utilization[1]['net_production']);
        $this->assertEquals(1000.0, (float) $utilization[1]['gross_production']);
    }
}
