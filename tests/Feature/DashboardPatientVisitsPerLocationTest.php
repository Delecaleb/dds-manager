<?php

namespace Tests\Feature;

use App\Domain\Support\ProcStatus;
use App\Models\OdPatient;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DashboardPatientVisitsPerLocationTest extends TestCase
{
    use RefreshDatabase;

    private Office $office;

    protected function setUp(): void
    {
        parent::setUp();

        $this->office = Office::create(['name' => 'Main Office', 'is_active' => true]);

        if (! Schema::hasTable('od_clinics')) {
            Schema::create('od_clinics', function ($table) {
                $table->integer('ClinicNum');
                $table->string('Description')->nullable();
                $table->string('Abbr')->nullable();
                $table->integer('office_id')->nullable();
            });
        }
    }

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
                'office_id' => $this->office->id,
            ],
            [
                'ProcNum' => 2,
                'PatNum' => 101,
                'ClinicNum' => 1,
                'CodeNum' => 100,
                'ProcFee' => 120.00,
                'ProcStatus' => ProcStatus::completed()[0],
                'ProcDate' => '2026-08-05',
                'office_id' => $this->office->id,
            ],
            [
                'ProcNum' => 3,
                'PatNum' => 102,
                'ClinicNum' => 2,
                'CodeNum' => 100,
                'ProcFee' => 200.00,
                'ProcStatus' => ProcStatus::completed()[0],
                'ProcDate' => '2026-08-02',
                'office_id' => $this->office->id,
            ],
            [
                'ProcNum' => 4,
                'PatNum' => 103,
                'ClinicNum' => 1,
                'CodeNum' => 626,
                'ProcFee' => 50.00,
                'ProcStatus' => ProcStatus::completed()[0],
                'ProcDate' => '2026-08-03',
                'office_id' => $this->office->id,
            ],
            [
                'ProcNum' => 5,
                'PatNum' => 104,
                'ClinicNum' => 2,
                'CodeNum' => 100,
                'ProcFee' => 150.00,
                'ProcStatus' => 0, // Not completed
                'ProcDate' => '2026-08-04',
                'office_id' => $this->office->id,
            ],
        ]);

        // Check Dashboard Data endpoint (Total Patient Visits card)
        $dataResponse = $this->actingAs($user)->withSession(['active_office_id' => $this->office->id])->getJson('/dashboard/data?start_date=2026-08-01&end_date=2026-08-31');
        $dataResponse->assertOk();
        $this->assertEquals(3, $dataResponse->json('patient_visits'));

        // Check Patient Visits Per Location endpoint (Chart data)
        $chartResponse = $this->actingAs($user)->withSession(['active_office_id' => $this->office->id])->getJson('/dashboard/patient-visits-per-location?start_date=2026-08-01&end_date=2026-08-31');
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

    public function test_new_patient_visits_per_location_matches_total_new_patient_visits_kpi(): void
    {
        $user = User::factory()->create();

        // Seed registered clinics
        DB::table('od_clinics')->insert([
            ['ClinicNum' => 1, 'Description' => 'Downtown Clinic', 'Abbr' => 'DT', 'office_id' => $this->office->id],
            ['ClinicNum' => 2, 'Description' => 'Uptown Clinic', 'Abbr' => 'UT', 'office_id' => $this->office->id],
        ]);

        // Seed patients
        OdPatient::create(['office_id' => $this->office->id, 'PatNum' => 201, 'FName' => 'Alice', 'LName' => 'Smith', 'PatStatus' => 'Patient']);
        OdPatient::create(['office_id' => $this->office->id, 'PatNum' => 202, 'FName' => 'Bob', 'LName' => 'Jones', 'PatStatus' => 'Patient']);
        OdPatient::create(['office_id' => $this->office->id, 'PatNum' => 203, 'FName' => 'Charlie', 'LName' => 'Brown', 'PatStatus' => 'Patient']);

        // Patient 201: First ever visit at Clinic 1 on 2026-08-02 -> New patient at Clinic 1
        // Patient 202: First ever visit at Clinic 2 on 2026-08-03 -> New patient at Clinic 2
        // Patient 203: Had prior procedure on 2026-07-15, then visited on 2026-08-04 at Clinic 1 -> Returning patient, not new
        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 11,
                'PatNum' => 201,
                'ClinicNum' => 1,
                'CodeNum' => 100,
                'ProcFee' => 150.00,
                'ProcStatus' => 2,
                'ProcDate' => '2026-08-02',
                'office_id' => $this->office->id,
            ],
            [
                'ProcNum' => 12,
                'PatNum' => 202,
                'ClinicNum' => 2,
                'CodeNum' => 100,
                'ProcFee' => 200.00,
                'ProcStatus' => 2,
                'ProcDate' => '2026-08-03',
                'office_id' => $this->office->id,
            ],
            [
                'ProcNum' => 13,
                'PatNum' => 203,
                'ClinicNum' => 1,
                'CodeNum' => 100,
                'ProcFee' => 100.00,
                'ProcStatus' => 2,
                'ProcDate' => '2026-07-15', // Prior procedure
                'office_id' => $this->office->id,
            ],
            [
                'ProcNum' => 14,
                'PatNum' => 203,
                'ClinicNum' => 1,
                'CodeNum' => 100,
                'ProcFee' => 120.00,
                'ProcStatus' => 2,
                'ProcDate' => '2026-08-04',
                'office_id' => $this->office->id,
            ],
        ]);

        // Check Dashboard Data endpoint (Total New Patient Visits card)
        $dataResponse = $this->actingAs($user)->withSession(['active_office_id' => $this->office->id])->getJson('/dashboard/data?start_date=2026-08-01&end_date=2026-08-31');
        $dataResponse->assertOk();
        $this->assertEquals(2, $dataResponse->json('new_patient_visit'));
        $this->assertEquals(3, $dataResponse->json('patient_visits'));

        // Check Patient Visits Per Location endpoint (Chart data)
        $chartResponse = $this->actingAs($user)->withSession(['active_office_id' => $this->office->id])->getJson('/dashboard/patient-visits-per-location?start_date=2026-08-01&end_date=2026-08-31');
        $chartResponse->assertOk();

        $chartData = collect($chartResponse->json());

        $clinic1 = $chartData->firstWhere('clinic_num', 1);
        $clinic2 = $chartData->firstWhere('clinic_num', 2);

        $this->assertNotNull($clinic1);
        $this->assertNotNull($clinic2);

        $this->assertEquals('Downtown Clinic', $clinic1['location']);
        $this->assertEquals(1, $clinic1['new_patient_visits']);
        $this->assertEquals(2, $clinic1['patient_visits']); // Pat 201 + Pat 203

        $this->assertEquals('Uptown Clinic', $clinic2['location']);
        $this->assertEquals(1, $clinic2['new_patient_visits']);
        $this->assertEquals(1, $clinic2['patient_visits']); // Pat 202

        // Sum across locations must correlate perfectly with the top KPI cards
        $this->assertEquals($dataResponse->json('new_patient_visit'), $chartData->sum('new_patient_visits'));
        $this->assertEquals($dataResponse->json('patient_visits'), $chartData->sum('patient_visits'));
    }

    public function test_coalesces_null_clinic_num_and_zero_without_duplicate_locations(): void
    {
        $user = User::factory()->create();

        // Patient with ClinicNum = NULL and another with ClinicNum = '0'
        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 21,
                'PatNum' => 301,
                'ClinicNum' => null,
                'CodeNum' => 100,
                'ProcFee' => 100.00,
                'ProcStatus' => 2,
                'ProcDate' => '2026-08-01',
                'office_id' => $this->office->id,
            ],
            [
                'ProcNum' => 22,
                'PatNum' => 302,
                'ClinicNum' => '0',
                'CodeNum' => 100,
                'ProcFee' => 150.00,
                'ProcStatus' => 2,
                'ProcDate' => '2026-08-02',
                'office_id' => $this->office->id,
            ],
        ]);

        $chartResponse = $this->actingAs($user)->withSession(['active_office_id' => $this->office->id])->getJson('/dashboard/patient-visits-per-location?start_date=2026-08-01&end_date=2026-08-31');
        $chartResponse->assertOk();

        $chartData = collect($chartResponse->json());

        // Should have exactly 1 location entry for Clinic 0 (no duplicate rows)
        $clinic0Entries = $chartData->where('clinic_num', 0);
        $this->assertCount(1, $clinic0Entries);

        $clinic0 = $clinic0Entries->first();
        $this->assertEquals(2, $clinic0['patient_visits']);
        $this->assertEquals(2, $chartData->sum('patient_visits'));
    }

    public function test_location_stats_and_visits_per_location_are_scoped_by_active_office(): void
    {
        $office1 = Office::create(['name' => 'Downtown Office', 'is_active' => true]);
        $office2 = Office::create(['name' => 'Suburban Office', 'is_active' => true]);

        $user = User::factory()->create();

        // Office 1 procedures
        DB::table('od_procedure_logs')->insert([
            'ProcNum' => 31,
            'PatNum' => 401,
            'ClinicNum' => 0,
            'CodeNum' => 100,
            'ProcFee' => 300.00,
            'ProcStatus' => 2,
            'ProcDate' => '2026-08-01',
            'office_id' => $office1->id,
        ]);

        // Office 2 procedures
        DB::table('od_procedure_logs')->insert([
            'ProcNum' => 32,
            'PatNum' => 501,
            'ClinicNum' => 0,
            'CodeNum' => 100,
            'ProcFee' => 700.00,
            'ProcStatus' => 2,
            'ProcDate' => '2026-08-01',
            'office_id' => $office2->id,
        ]);

        // Request as Office 1
        $resOffice1 = $this->actingAs($user)
            ->withSession(['active_office_id' => $office1->id])
            ->getJson('/dashboard/patient-visits-per-location?start_date=2026-08-01&end_date=2026-08-31');
        $resOffice1->assertOk();
        $data1 = collect($resOffice1->json());
        $this->assertEquals(1, $data1->firstWhere('clinic_num', 0)['patient_visits']);
        $this->assertEquals('Downtown Office', $data1->firstWhere('clinic_num', 0)['location']);

        // Request as Office 2
        $resOffice2 = $this->actingAs($user)
            ->withSession(['active_office_id' => $office2->id])
            ->getJson('/dashboard/patient-visits-per-location?start_date=2026-08-01&end_date=2026-08-31');
        $resOffice2->assertOk();
        $data2 = collect($resOffice2->json());
        $this->assertEquals(1, $data2->firstWhere('clinic_num', 0)['patient_visits']);
        $this->assertEquals('Suburban Office', $data2->firstWhere('clinic_num', 0)['location']);
    }
}
