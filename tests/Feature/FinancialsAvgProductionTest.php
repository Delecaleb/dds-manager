<?php

namespace Tests\Feature;

use App\Domain\Support\ProcStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FinancialsAvgProductionTest extends TestCase
{
    use RefreshDatabase;

    public function test_financials_data_calculates_avg_production_per_patient_from_net_production(): void
    {
        $user = User::factory()->create();

        // 2 procedures on different dates for different patients -> gross = 1500, patient_visits = 2
        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 1,
                'PatNum' => 101,
                'ClinicNum' => 1,
                'CodeNum' => 1,
                'ProcFee' => 1000.00,
                'ProcStatus' => ProcStatus::completed()[0],
                'ProcDate' => '2026-08-05',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
            [
                'ProcNum' => 2,
                'PatNum' => 102,
                'ClinicNum' => 1,
                'CodeNum' => 1,
                'ProcFee' => 500.00,
                'ProcStatus' => ProcStatus::completed()[0],
                'ProcDate' => '2026-08-06',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
        ]);

        // Adjustment: -100
        DB::table('od_adjustments')->insert([
            [
                'ProvNum' => 1,
                'AdjAmt' => -100.00,
                'AdjDate' => '2026-08-05',
                'ClinicNum' => 1,
            ],
        ]);

        // Writeoff: 200
        DB::table('od_claim_procs')->insert([
            [
                'ClaimProcNum' => 1,
                'ProvNum' => 1,
                'Status' => 1,
                'ClaimPaymentNum' => 0,
                'PlanNum' => 1,
                'WriteOff' => 200.00,
                'ProcDate' => '2026-08-05',
                'ClinicNum' => 1,
            ],
        ]);

        // Net production = 1500 + (-100) - 200 = 1200.00
        // Patient visits = 2
        // Expected patient_avg_production = 1200 / 2 = 600.00

        $response = $this->actingAs($user)->getJson('/financials/data?section=patient-kpis&start_date=2026-08-01&end_date=2026-08-31');

        $response->assertStatus(200);
        $response->assertJson([
            'patient_visits' => 2,
            'patient_avg_production' => 600.00,
        ]);
    }
}
