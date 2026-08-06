<?php

namespace Tests\Feature;

use App\Domain\Support\ProcStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardLocationStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_stats_endpoint_calculates_avg_production_from_net_production_and_patient_visits(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Clinic 1: 2 procedures on different days for different patients -> gross = 1500, patient_visits = 2
        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 1,
                'PatNum' => 101,
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
                'ClinicNum' => 1,
                'ProcFee' => 500.00,
                'ProcStatus' => ProcStatus::completed()[0],
                'ProcDate' => '2026-07-06',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
        ]);

        // Adjustment for Clinic 1: -100
        DB::table('od_adjustments')->insert([
            [
                'ProvNum' => 1,
                'AdjAmt' => -100.00,
                'AdjDate' => '2026-07-05',
                'ClinicNum' => 1,
            ],
        ]);

        // Writeoff for Clinic 1: 200
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

        // Net production = 1500 + (-100) - 200 = 1200.00
        // Patient count = 2
        // avg_production = 1200 / 2 = 600.00 (previously gross 1500 / 2 = 750.00)

        $response = $this->getJson('/dashboard/location-stats?start_date=2026-07-01&end_date=2026-07-31');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'clinic_num' => 1,
            'total_production' => 1500.0,
            'net_production' => 1200.0,
            'patient_count' => 2,
            'avg_production' => 600.0,
        ]);
    }
}
