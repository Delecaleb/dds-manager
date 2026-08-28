<?php

namespace Tests\Feature;

use App\Domain\Support\ProcStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardPatientVisitsPerLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_visits_per_location_matches_total_patient_visits_calculation(): void
    {
        $user = User::factory()->create();

        // Clinic 1: Patient 101 visits on 2 separate dates (2026-08-01, 2026-08-05) -> 2 patient visits
        // Clinic 2: Patient 102 visits on 1 date (2026-08-02) -> 1 patient visit
        // Clinic 1: Patient 103 has a procedure with CodeNum 626 (excluded) -> 0 visits
        // Clinic 2: Patient 104 has an uncompleted procedure (ProcStatus != completed) -> 0 visits
        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 1,
                'PatNum' => 101,
                'ClinicNum' => 1,
                'CodeNum' => 100,
                'ProcFee' => 100.00,
                'ProcStatus' => ProcStatus::completed()[0],
                'ProcDate' => '2026-08-01',
            ],
            [
                'ProcNum' => 2,
                'PatNum' => 101,
                'ClinicNum' => 1,
                'CodeNum' => 100,
                'ProcFee' => 120.00,
                'ProcStatus' => ProcStatus::completed()[0],
                'ProcDate' => '2026-08-05',
            ],
            [
                'ProcNum' => 3,
                'PatNum' => 102,
                'ClinicNum' => 2,
                'CodeNum' => 100,
                'ProcFee' => 200.00,
                'ProcStatus' => ProcStatus::completed()[0],
                'ProcDate' => '2026-08-02',
            ],
            [
                'ProcNum' => 4,
                'PatNum' => 103,
                'ClinicNum' => 1,
                'CodeNum' => 626,
                'ProcFee' => 50.00,
                'ProcStatus' => ProcStatus::completed()[0],
                'ProcDate' => '2026-08-03',
            ],
            [
                'ProcNum' => 5,
                'PatNum' => 104,
                'ClinicNum' => 2,
                'CodeNum' => 100,
                'ProcFee' => 150.00,
                'ProcStatus' => 0, // Not completed
                'ProcDate' => '2026-08-04',
            ],
        ]);

        // Check Dashboard Data endpoint (Total Patient Visits card)
        $dataResponse = $this->actingAs($user)->getJson('/dashboard/data?start_date=2026-08-01&end_date=2026-08-31');
        $dataResponse->assertOk();
        $this->assertEquals(3, $dataResponse->json('patient_visits'));

        // Check Patient Visits Per Location endpoint (Chart data)
        $chartResponse = $this->actingAs($user)->getJson('/dashboard/patient-visits-per-location?start_date=2026-08-01&end_date=2026-08-31');
        $chartResponse->assertOk();

        $chartData = collect($chartResponse->json());

        $clinic1 = $chartData->firstWhere('clinic_num', 1);
        $clinic2 = $chartData->firstWhere('clinic_num', 2);

        $this->assertNotNull($clinic1);
        $this->assertNotNull($clinic2);

        // Clinic 1 should have 2 patient visits (Patient 101 across 2 days)
        $this->assertEquals(2, $clinic1['patient_visits']);

        // Clinic 2 should have 1 patient visit
        $this->assertEquals(1, $clinic2['patient_visits']);

        // Total across locations matches total card count
        $totalChartVisits = $chartData->sum('patient_visits');
        $this->assertEquals($dataResponse->json('patient_visits'), $totalChartVisits);
    }
}
