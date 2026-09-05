<?php

namespace Tests\Feature;

use App\Domain\Patient\PatientService;
use App\Domain\Patient\PatientVisitService;
use App\Domain\Production\ProductionService;
use App\Domain\Support\MetricFilter;
use App\Domain\Support\ProcCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PatientVisitCodeFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_visit_excludes_code_num_626(): void
    {
        // Insert a patient
        DB::table('od_patients')->insert([
            'PatNum' => 1,
            'LName' => 'Doe',
            'FName' => 'John',
        ]);

        // Insert procedures
        DB::table('od_procedures')->insert([
            ['CodeNum' => 626, 'ProcCode' => 'D9999', 'Descript' => 'Excluded Code'],
            ['CodeNum' => 100, 'ProcCode' => 'D0120', 'Descript' => 'Exam'],
        ]);

        // Insert procedure logs for PatNum 1 on 2026-08-01:
        // Proc 1: CodeNum 626 (should be excluded)
        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 1,
                'PatNum' => 1,
                'CodeNum' => 626,
                'ProcDate' => '2026-08-01',
                'ProcStatus' => 2, // completed
                'ProcFee' => 100.00,
            ],
        ]);

        $filter = new MetricFilter('2026-08-01', '2026-08-31');
        $productionService = app(ProductionService::class);
        $patientService = app(PatientService::class);

        // ProductionService patientVisits should be 0 because the only completed proc has CodeNum = 626
        $this->assertEquals(0, $productionService->patientVisits($filter));

        // PatientService count / newPatientCount should be 0 because CodeNum = 626 is excluded
        $this->assertEquals(0, $patientService->count($filter));
        $this->assertEquals(0, $patientService->newPatientCount($filter));

        // Now insert a valid procedure log with CodeNum 100 on 2026-08-02
        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 2,
                'PatNum' => 1,
                'CodeNum' => 100,
                'ProcDate' => '2026-08-02',
                'ProcStatus' => 2, // completed
                'ProcFee' => 150.00,
            ],
        ]);

        // Now patientVisits should be 1 (for 2026-08-02), and newPatientCount should be 1
        $this->assertEquals(1, $productionService->patientVisits($filter));
        $this->assertEquals(1, $patientService->count($filter));
        $this->assertEquals(1, $patientService->newPatientCount($filter));
    }

    public function test_financial_daily_patient_stats_excludes_code_num_626(): void
    {
        $user = User::factory()->create();

        DB::table('od_patients')->insert([
            'PatNum' => 10,
            'LName' => 'Smith',
            'FName' => 'Jane',
        ]);

        DB::table('od_procedures')->insert([
            ['CodeNum' => 626, 'ProcCode' => 'D9999', 'Descript' => 'Excluded Code'],
            ['CodeNum' => 101, 'ProcCode' => 'D1110', 'Descript' => 'Prophy'],
        ]);

        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 10,
                'PatNum' => 10,
                'CodeNum' => 626,
                'ProcDate' => '2026-08-05',
                'ProcStatus' => 2,
                'ProcFee' => 50.00,
            ],
            [
                'ProcNum' => 11,
                'PatNum' => 10,
                'CodeNum' => 101,
                'ProcDate' => '2026-08-06',
                'ProcStatus' => 2,
                'ProcFee' => 75.00,
            ],
        ]);

        $response = $this->actingAs($user)->getJson('/financials/data?section=daily-patient-chart&start_date=2026-08-01&end_date=2026-08-31');
        $response->assertOk();

        $stats = collect($response->json('daily_patient_stats'));

        // 2026-08-05 (only CodeNum 626) -> 0 patient_visits
        $aug5 = $stats->firstWhere('date', '2026-08-05');
        $this->assertEquals(0, $aug5['patient_visits']);

        // 2026-08-06 (CodeNum 101) -> 1 patient_visits
        $aug6 = $stats->firstWhere('date', '2026-08-06');
        $this->assertEquals(1, $aug6['patient_visits']);
    }

    public function test_patient_visit_excludes_d9986_and_d9987_with_arbitrary_code_nums(): void
    {
        ProcCode::clearCache();

        // Setup Plymouth office (office_id = 4)
        $plymouthOfficeId = 4;

        DB::table('od_patients')->insert([
            'PatNum' => 50,
            'office_id' => $plymouthOfficeId,
            'LName' => 'Wayne',
            'FName' => 'Bruce',
        ]);

        // In Plymouth, D9986 has CodeNum 9986 and D9987 has CodeNum 9987
        DB::table('od_procedures')->insert([
            ['office_id' => $plymouthOfficeId, 'CodeNum' => 9986, 'ProcCode' => 'D9986', 'Descript' => 'Missed appointment'],
            ['office_id' => $plymouthOfficeId, 'CodeNum' => 9987, 'ProcCode' => 'D9987', 'Descript' => 'Cancelled appointment'],
            ['office_id' => $plymouthOfficeId, 'CodeNum' => 102, 'ProcCode' => 'D0150', 'Descript' => 'Comprehensive Oral Eval'],
        ]);

        // Patient 50 has a completed missed appointment (D9986) on 2026-08-10 and cancelled (D9987) on 2026-08-11
        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 501,
                'PatNum' => 50,
                'office_id' => $plymouthOfficeId,
                'CodeNum' => 9986,
                'ProcDate' => '2026-08-10',
                'ProcStatus' => 2,
                'ProcFee' => 25.00,
            ],
            [
                'ProcNum' => 502,
                'PatNum' => 50,
                'office_id' => $plymouthOfficeId,
                'CodeNum' => 9987,
                'ProcDate' => '2026-08-11',
                'ProcStatus' => 2,
                'ProcFee' => 0.00,
            ],
        ]);

        $filter = new MetricFilter('2026-08-01', '2026-08-31', officeId: $plymouthOfficeId);
        $productionService = app(ProductionService::class);
        $patientService = app(PatientService::class);
        $patientVisitService = app(PatientVisitService::class);

        // Neither D9986 nor D9987 should count as patient visits or new patient visits
        $this->assertEquals(0, $productionService->patientVisits($filter));
        $this->assertEquals(0, $patientService->count($filter));
        $this->assertEquals(0, $patientService->newPatientCount($filter));
        $this->assertEquals(0, $patientVisitService->patientVisits($filter));
        $this->assertEquals(0, $patientVisitService->newPatientCount('2026-08-01', '2026-08-31', [], [], $plymouthOfficeId));

        // Now add a real clinical visit on 2026-08-15
        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 503,
                'PatNum' => 50,
                'office_id' => $plymouthOfficeId,
                'CodeNum' => 102,
                'ProcDate' => '2026-08-15',
                'ProcStatus' => 2,
                'ProcFee' => 120.00,
            ],
        ]);

        $this->assertEquals(1, $productionService->patientVisits($filter));
        $this->assertEquals(1, $patientService->count($filter));
        $this->assertEquals(1, $patientService->newPatientCount($filter));
        $this->assertEquals(1, $patientVisitService->patientVisits($filter));
        $this->assertEquals(1, $patientVisitService->newPatientCount('2026-08-01', '2026-08-31', [], [], $plymouthOfficeId));
    }
}
