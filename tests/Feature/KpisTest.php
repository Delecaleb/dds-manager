<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KpisTest extends TestCase
{
    use RefreshDatabase;

    public function test_kpis_index_page_requires_auth(): void
    {
        $response = $this->get('/kpis');
        $response->assertRedirect('/login');
    }

    public function test_kpis_index_page_is_accessible_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/kpis');
        $response->assertStatus(200);
        $response->assertSee('Hygiene');
    }

    public function test_hygiene_kpi_endpoint_returns_all_22_metrics(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/kpis/hygiene?start_date=2026-01-01&end_date=2026-07-01');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'perio_pct',
            'fluoride_per_day',
            'avg_prod_per_day',
            'avg_prod_per_prov_day',
            'prod_per_visit',
            'fmx_per_day',
            'srp_per_day',
            'visits_per_day',
            'reappt',
            'perio_reappt',
            'adult_retention_12m',
            'adult_retention_6m',
            'child_retention_12m',
            'child_retention_6m',
            'sealants',
            'whitening',
            'antimicrobial',
            'prod_per_proc',
            'visits_with_tx_pct',
            'tx_plans_per_day',
            'avg_prod_per_hour',
            'case_acceptance',
        ]);
    }

    public function test_hygiene_providers_endpoint_returns_data_structure(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/kpis/hygiene-providers?start_date=2026-01-01&end_date=2026-07-01');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'providers',
            'avg',
            'total',
        ]);
    }

    public function test_doctor_kpi_endpoint_returns_all_16_metrics(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/kpis/doctor?start_date=2026-01-01&end_date=2026-07-01');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'case_acceptance_same_day',
            'case_acceptance_rate',
            'new_pt_tx_dollars',
            'existing_pt_tx_dollars',
            'avg_apt_time_mins',
            'avg_prod_per_hour',
            'avg_prod_per_apt',
            'same_day_tx_per_new_pt',
            'avg_prod_per_prov_day',
            'avg_tx_per_existing_pt',
            'avg_tx_per_new_pt',
            'pct_new_pt_with_tx',
            'pct_existing_pt_with_tx',
            'reappt',
            'prod_per_exam',
            'total_production',
        ]);
    }

    public function test_office_kpi_endpoint_returns_all_11_metrics(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/kpis/office?start_date=2026-01-01&end_date=2026-07-01');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'patient_retention',
            'tx_plans_per_day',
            'co_pay_collection',
            'unscheduled_tx',
            'new_pt_fmx_pct',
            'no_show_rate',
            'reactivation_list',
            'patient_attrition',
            'patient_growth',
            'active_patients',
            'active_in_recare_pct',
        ]);
    }

    public function test_hygiene_perio_percentage_calculation(): void
    {
        $office = Office::create([
            'name' => 'Main Clinic',
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        // 1. Hygiene provider
        DB::table('od_providers')->insert([
            'ProvNum' => 10,
            'LName' => 'Jones',
            'PName' => 'Sarah',
            'Abbr' => 'SJ',
            'Specialty' => 0,
            'IsHidden' => 'false',
            'office_id' => $office->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Hygiene & Perio procedures
        DB::table('od_procedures')->insert([
            ['CodeNum' => 101, 'ProcCode' => 'D1110', 'Descript' => 'Prophy Adult', 'IsHygiene' => '1', 'office_id' => $office->id],
            ['CodeNum' => 102, 'ProcCode' => 'D1120', 'Descript' => 'Prophy Child', 'IsHygiene' => '1', 'office_id' => $office->id],
            ['CodeNum' => 103, 'ProcCode' => 'D4341', 'Descript' => 'SRP 4+ teeth', 'IsHygiene' => '1', 'office_id' => $office->id],
            ['CodeNum' => 104, 'ProcCode' => 'D4910', 'Descript' => 'Perio Maint', 'IsHygiene' => '1', 'office_id' => $office->id],
            ['CodeNum' => 105, 'ProcCode' => 'D4342', 'Descript' => 'SRP 1-3 teeth', 'IsHygiene' => '1', 'office_id' => $office->id],
        ]);

        // 3. Completed procedure logs
        // Patient 1: D1110 on 2026-03-01 (Hygiene visit 1)
        // Patient 2: D1120 on 2026-03-02 (Hygiene visit 2)
        // Patient 3: D4341 on 2026-03-03 (Perio appointment 1 & Hygiene visit 3)
        // Patient 4: D4910 and D4342 on 2026-03-04 (Perio appointment 2 & Hygiene visit 4 - distinct visit)
        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 1,
                'PatNum' => 201,
                'CodeNum' => 101,
                'ProcDate' => '2026-03-01',
                'ProcFee' => 100.00,
                'ProcStatus' => 'C',
                'ProvNum' => 10,
                'ClinicNum' => 0,
                'office_id' => $office->id,
            ],
            [
                'ProcNum' => 2,
                'PatNum' => 202,
                'CodeNum' => 102,
                'ProcDate' => '2026-03-02',
                'ProcFee' => 75.00,
                'ProcStatus' => 'C',
                'ProvNum' => 10,
                'ClinicNum' => 0,
                'office_id' => $office->id,
            ],
            [
                'ProcNum' => 3,
                'PatNum' => 203,
                'CodeNum' => 103,
                'ProcDate' => '2026-03-03',
                'ProcFee' => 250.00,
                'ProcStatus' => 'C',
                'ProvNum' => 10,
                'ClinicNum' => 0,
                'office_id' => $office->id,
            ],
            [
                'ProcNum' => 4,
                'PatNum' => 204,
                'CodeNum' => 104,
                'ProcDate' => '2026-03-04',
                'ProcFee' => 150.00,
                'ProcStatus' => 'C',
                'ProvNum' => 10,
                'ClinicNum' => 0,
                'office_id' => $office->id,
            ],
            [
                'ProcNum' => 5,
                'PatNum' => 204,
                'CodeNum' => 105,
                'ProcDate' => '2026-03-04',
                'ProcFee' => 120.00,
                'ProcStatus' => 'C',
                'ProvNum' => 10,
                'ClinicNum' => 0,
                'office_id' => $office->id,
            ],
        ]);

        // 4. Test /kpis/hygiene: 2 perio appts / 4 hygiene appts * 100 = 50.0%
        $response = $this->actingAs($user)
            ->withSession(['active_office_id' => $office->id])
            ->getJson('/kpis/hygiene?start_date=2026-03-01&end_date=2026-03-31');

        $response->assertStatus(200);
        $this->assertEquals(50.0, $response->json('perio_pct'));

        // 5. Test /kpis/hygiene-providers
        $provResponse = $this->actingAs($user)
            ->withSession(['active_office_id' => $office->id])
            ->getJson('/kpis/hygiene-providers?start_date=2026-03-01&end_date=2026-03-31');

        $provResponse->assertStatus(200);
        $providers = $provResponse->json('providers');
        $this->assertCount(1, $providers);
        $this->assertEquals(50.0, $providers[0]['perio_pct']);
    }

    public function test_hygiene_perio_percentage_handles_all_specified_codes(): void
    {
        $office = Office::create([
            'name' => 'Perio Specialty Clinic',
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        DB::table('od_providers')->insert([
            'ProvNum' => 20,
            'LName' => 'Miller',
            'PName' => 'Amy',
            'Abbr' => 'AM',
            'Specialty' => 0,
            'IsHidden' => 'false',
            'office_id' => $office->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert all 5 perio codes and 2 standard hygiene codes
        DB::table('od_procedures')->insert([
            ['CodeNum' => 201, 'ProcCode' => 'D1110', 'Descript' => 'Adult Prophy', 'IsHygiene' => '1', 'office_id' => $office->id],
            ['CodeNum' => 202, 'ProcCode' => 'D1120', 'Descript' => 'Child Prophy', 'IsHygiene' => '1', 'office_id' => $office->id],
            ['CodeNum' => 203, 'ProcCode' => 'D4341', 'Descript' => 'Perio SRP 4+ Teeth', 'IsHygiene' => '1', 'office_id' => $office->id],
            ['CodeNum' => 204, 'ProcCode' => 'D4342', 'Descript' => 'Perio SRP 1-3 Teeth', 'IsHygiene' => '1', 'office_id' => $office->id],
            ['CodeNum' => 205, 'ProcCode' => 'D4910', 'Descript' => 'Perio Maint', 'IsHygiene' => '1', 'office_id' => $office->id],
            ['CodeNum' => 206, 'ProcCode' => 'D4346', 'Descript' => 'Scaling Gingival Inflammation', 'IsHygiene' => '1', 'office_id' => $office->id],
            ['CodeNum' => 207, 'ProcCode' => 'D4355', 'Descript' => 'Full Mouth Debridement', 'IsHygiene' => '1', 'office_id' => $office->id],
        ]);

        // Create 7 separate patient visits (5 perio, 2 standard prophy => 5 / 7 * 100 = 71.43%)
        $logs = [];
        $perioCodes = [203, 204, 205, 206, 207];
        foreach ($perioCodes as $idx => $codeNum) {
            $logs[] = [
                'ProcNum' => 100 + $idx,
                'PatNum' => 300 + $idx,
                'CodeNum' => $codeNum,
                'ProcDate' => '2026-04-0'.($idx + 1),
                'ProcFee' => 150.00,
                'ProcStatus' => 'C',
                'ProvNum' => 20,
                'ClinicNum' => 0,
                'office_id' => $office->id,
            ];
        }

        // Add 2 non-perio hygiene visits
        $logs[] = [
            'ProcNum' => 105,
            'PatNum' => 305,
            'CodeNum' => 201,
            'ProcDate' => '2026-04-06',
            'ProcFee' => 100.00,
            'ProcStatus' => 'C',
            'ProvNum' => 20,
            'ClinicNum' => 0,
            'office_id' => $office->id,
        ];
        $logs[] = [
            'ProcNum' => 106,
            'PatNum' => 306,
            'CodeNum' => 202,
            'ProcDate' => '2026-04-07',
            'ProcFee' => 80.00,
            'ProcStatus' => 'C',
            'ProvNum' => 20,
            'ClinicNum' => 0,
            'office_id' => $office->id,
        ];

        DB::table('od_procedure_logs')->insert($logs);

        $response = $this->actingAs($user)
            ->withSession(['active_office_id' => $office->id])
            ->getJson('/kpis/hygiene?start_date=2026-04-01&end_date=2026-04-30');

        $response->assertStatus(200);
        // 5 perio appointments / 7 hygiene appointments * 100 = 71.43%
        $this->assertEquals(71.43, $response->json('perio_pct'));
    }

    public function test_hygiene_fluoride_per_day_calculation(): void
    {
        $office = Office::create([
            'name' => 'Fluoride Test Clinic',
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        DB::table('od_providers')->insert([
            'ProvNum' => 30,
            'LName' => 'Williams',
            'PName' => 'Rachel',
            'Abbr' => 'RW',
            'Specialty' => 0,
            'IsHidden' => 'false',
            'office_id' => $office->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('od_procedures')->insert([
            ['CodeNum' => 301, 'ProcCode' => 'D1110', 'Descript' => 'Adult Prophy', 'IsHygiene' => '1', 'office_id' => $office->id],
            ['CodeNum' => 302, 'ProcCode' => 'D1206', 'Descript' => 'Fluoride Varnish', 'IsHygiene' => '1', 'office_id' => $office->id],
            ['CodeNum' => 303, 'ProcCode' => 'D1208', 'Descript' => 'Fluoride Gel/Foam', 'IsHygiene' => '1', 'office_id' => $office->id],
        ]);

        // Day 1 (2026-05-01):
        // - Patient 401: D1110 & D1206 (Fluoride 1)
        // - Patient 402: D1206 (Fluoride 2)
        // Day 2 (2026-05-02):
        // - Patient 402: D1208 (Already counted once as unique patient)
        // - Patient 403: D1208 (Fluoride 3)
        // - Patient 404: D1110 (Hygiene visit only)
        // Total unique patients with fluoride = 3 (Patients 401, 402, 403)
        // Total hygiene working days = 2 (2026-05-01, 2026-05-02)
        // Expected fluoride per day = 3 / 2 = 1.5
        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 201,
                'PatNum' => 401,
                'CodeNum' => 301,
                'ProcDate' => '2026-05-01',
                'ProcFee' => 100.00,
                'ProcStatus' => 'C',
                'ProvNum' => 30,
                'ClinicNum' => 0,
                'office_id' => $office->id,
            ],
            [
                'ProcNum' => 202,
                'PatNum' => 401,
                'CodeNum' => 302,
                'ProcDate' => '2026-05-01',
                'ProcFee' => 35.00,
                'ProcStatus' => 'C',
                'ProvNum' => 30,
                'ClinicNum' => 0,
                'office_id' => $office->id,
            ],
            [
                'ProcNum' => 203,
                'PatNum' => 402,
                'CodeNum' => 302,
                'ProcDate' => '2026-05-01',
                'ProcFee' => 35.00,
                'ProcStatus' => 'C',
                'ProvNum' => 30,
                'ClinicNum' => 0,
                'office_id' => $office->id,
            ],
            [
                'ProcNum' => 204,
                'PatNum' => 402,
                'CodeNum' => 303,
                'ProcDate' => '2026-05-02',
                'ProcFee' => 30.00,
                'ProcStatus' => 'C',
                'ProvNum' => 30,
                'ClinicNum' => 0,
                'office_id' => $office->id,
            ],
            [
                'ProcNum' => 205,
                'PatNum' => 403,
                'CodeNum' => 303,
                'ProcDate' => '2026-05-02',
                'ProcFee' => 30.00,
                'ProcStatus' => 'C',
                'ProvNum' => 30,
                'ClinicNum' => 0,
                'office_id' => $office->id,
            ],
            [
                'ProcNum' => 206,
                'PatNum' => 404,
                'CodeNum' => 301,
                'ProcDate' => '2026-05-02',
                'ProcFee' => 100.00,
                'ProcStatus' => 'C',
                'ProvNum' => 30,
                'ClinicNum' => 0,
                'office_id' => $office->id,
            ],
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_office_id' => $office->id])
            ->getJson('/kpis/hygiene?start_date=2026-05-01&end_date=2026-05-31');

        $response->assertStatus(200);
        $this->assertEquals(1.5, $response->json('fluoride_per_day'));

        $provResponse = $this->actingAs($user)
            ->withSession(['active_office_id' => $office->id])
            ->getJson('/kpis/hygiene-providers?start_date=2026-05-01&end_date=2026-05-31');

        $provResponse->assertStatus(200);
        $providers = $provResponse->json('providers');
        $this->assertCount(1, $providers);
        $this->assertEquals(1.5, $providers[0]['fluoride_per_day']);
    }
}
