<?php

namespace Tests\Feature;

use App\Models\OdAppointment;
use App\Models\OdPatient;
use App\Models\OdProcedureLog;
use App\Models\OdProvider;
use App\Models\OdRecall;
use App\Models\Office;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HygieneRecallTest extends TestCase
{
    use RefreshDatabase;

    protected Office $office;

    protected function setUp(): void
    {
        parent::setUp();

        $this->office = Office::create([
            'name' => 'Main Office',
            'is_active' => true,
        ]);
    }

    protected function makeRecall(array $attributes = []): OdRecall
    {
        static $seq = 1;

        return OdRecall::create(array_merge([
            'RecallNum' => $seq++,
            'PatNum' => 1,
            'DateDueCalc' => '2026-08-10',
            'DateDue' => '2026-08-10',
            'DatePrevious' => '2026-02-10',
            'RecallInterval' => 6,
            'RecallStatus' => 0,
            'Note' => '',
            'IsDisabled' => 0,
            'DateTStamp' => '2026-08-01',
            'RecallTypeNum' => 1,
            'DisableUntilBalance' => '0',
            'DisableUntilDate' => '2026-01-01',
            'DateScheduled' => '2026-08-10',
            'Priority' => 0,
            'TimePatternOverride' => '',
            'office_id' => 0,
        ], $attributes));
    }

    public function test_hygiene_recall_page_can_be_rendered_by_super_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get(route('hygiene-recall.index'));

        $response->assertStatus(200);
        $response->assertSee('Hygiene Recall');
        $response->assertSee('Provider Recall Summary');
        $response->assertSee('Export CSV');
    }

    public function test_hygiene_recall_page_forbidden_for_user_without_module_access(): void
    {
        $staff = User::factory()->staff()->create();
        $staff->syncModules(['patients', 'calendar']); // No hygiene-recall module

        $response = $this->actingAs($staff)->get(route('hygiene-recall.index'));

        $response->assertStatus(403);
    }

    public function test_hygiene_recall_page_accessible_for_user_with_module_access(): void
    {
        $staff = User::factory()->staff()->create();
        $staff->syncModules(['hygiene-recall']);

        $response = $this->actingAs($staff)->get(route('hygiene-recall.index'));

        $response->assertStatus(200);
        $response->assertSee('Hygiene Recall');
    }

    public function test_hygiene_recall_data_returns_empty_when_no_records(): void
    {
        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)->getJson(route('hygiene-recall.data', [
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]));

        $response->assertStatus(200);
        $json = $response->json();
        $this->assertEquals(0, $json['recordsTotal']);
        $this->assertEmpty($json['data']);
        // Verify mock data was NOT prepended
        $this->assertStringNotContainsString('Poole, Donna', json_encode($json));
        $this->assertStringNotContainsString('Heller, Landi', json_encode($json));
    }

    public function test_hygiene_recall_data_calculates_real_metrics_correctly(): void
    {
        $user = User::factory()->superAdmin()->create();

        $provider = OdProvider::create([
            'ProvNum' => 15,
            'LName' => 'Vance',
            'PName' => 'Bob',
            'Abbr' => 'BV',
            'IsHidden' => 'false',
        ]);

        $patient1 = OdPatient::create([
            'PatNum' => 101,
            'LName' => 'Scott',
            'FName' => 'Michael',
            'PriProv' => $provider->ProvNum,
        ]);

        $patient2 = OdPatient::create([
            'PatNum' => 102,
            'LName' => 'Beesly',
            'FName' => 'Pam',
            'PriProv' => $provider->ProvNum,
        ]);

        // Patient 1 has recall due Aug 10, 2026
        $this->makeRecall([
            'RecallNum' => 101,
            'PatNum' => $patient1->PatNum,
            'DateDue' => '2026-08-10',
            'office_id' => 0,
        ]);

        // Patient 2 has recall due Aug 12, 2026
        $this->makeRecall([
            'RecallNum' => 102,
            'PatNum' => $patient2->PatNum,
            'DateDue' => '2026-08-12',
            'office_id' => 0,
        ]);

        // Patient 1 has a future scheduled appointment with $220 procedure fee
        $apt = OdAppointment::create([
            'AptNum' => 501,
            'PatNum' => $patient1->PatNum,
            'AptDateTime' => Carbon::now()->addDays(5)->toDateTimeString(),
            'AptStatus' => 1, // Scheduled
            'ClinicNum' => 0,
        ]);

        OdProcedureLog::create([
            'ProcNum' => 801,
            'PatNum' => $patient1->PatNum,
            'ProvNum' => $provider->ProvNum,
            'ClinicNum' => 0,
            'CodeNum' => 1,
            'ProcDate' => Carbon::now()->addDays(5)->toDateString(),
            'ProcFee' => '220.00',
            'ProcStatus' => 'TP',
            'AptNum' => $apt->AptNum,
        ]);

        // Patient 2 has NO future appointment (missed recall)

        $response = $this->actingAs($user)->getJson(route('hygiene-recall.data', [
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]));

        $response->assertStatus(200);
        $json = $response->json();
        $this->assertEquals(1, $json['recordsTotal']);

        $row = $json['data'][0];
        $this->assertEquals('Vance, Bob', $row['provider_name']);
        $this->assertEquals(1, $row['missed_recall']);
        $this->assertEquals(1, $row['patient_recalled']);
        $this->assertEquals(1, $row['future_appointments']);
        $this->assertEquals('$ 220.00', $row['patients_recalled_dollars']);
        $this->assertEquals('50.00%', $row['patient_recall_rate']);

        $this->assertEquals(1, $json['total']['missed_recall']);
        $this->assertEquals(1, $json['total']['patient_recalled']);
        $this->assertEquals('$ 220.00', $json['total']['patients_recalled_dollars']);
    }

    public function test_hygiene_recall_filters_by_location(): void
    {
        $user = User::factory()->superAdmin()->create();

        $provider = OdProvider::create([
            'ProvNum' => 16,
            'LName' => 'Halpert',
            'PName' => 'Jim',
            'Abbr' => 'JH',
            'IsHidden' => 'false',
        ]);

        $patientClinic0 = OdPatient::create([
            'PatNum' => 201,
            'LName' => 'Kapoor',
            'FName' => 'Kelly',
            'PriProv' => $provider->ProvNum,
        ]);

        $patientClinic1 = OdPatient::create([
            'PatNum' => 202,
            'LName' => 'Howard',
            'FName' => 'Ryan',
            'PriProv' => $provider->ProvNum,
        ]);

        $this->makeRecall([
            'RecallNum' => 201,
            'PatNum' => $patientClinic0->PatNum,
            'DateDue' => '2026-08-15',
            'office_id' => 0,
        ]);

        $this->makeRecall([
            'RecallNum' => 202,
            'PatNum' => $patientClinic1->PatNum,
            'DateDue' => '2026-08-15',
            'office_id' => 1,
        ]);

        // Filter clinic 1
        $response = $this->actingAs($user)->getJson(route('hygiene-recall.data', [
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'clinic' => '1',
        ]));

        $response->assertStatus(200);
        $json = $response->json();
        $this->assertEquals(1, $json['recordsTotal']);
        $this->assertEquals(1, $json['data'][0]['clinic_num']);
    }

    public function test_hygiene_recall_drilldown_returns_patient_breakdown(): void
    {
        $user = User::factory()->superAdmin()->create();

        $provider = OdProvider::create([
            'ProvNum' => 18,
            'LName' => 'Schrute',
            'PName' => 'Dwight',
            'Abbr' => 'DS',
            'IsHidden' => 'false',
        ]);

        $patient = OdPatient::create([
            'PatNum' => 301,
            'LName' => 'Martin',
            'FName' => 'Angela',
            'PriProv' => $provider->ProvNum,
        ]);

        $this->makeRecall([
            'RecallNum' => 301,
            'PatNum' => $patient->PatNum,
            'DateDue' => '2026-08-18',
            'office_id' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('hygiene-recall.drilldown', [
            'metric' => 'missed_recall',
            'prov_num' => 18,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Missed Recalls Breakdown');
        $response->assertSee('Martin, Angela');
        $response->assertSee('Missed (Unscheduled)');
    }

    public function test_hygiene_recall_export_csv_streams_valid_csv(): void
    {
        $user = User::factory()->superAdmin()->create();

        $provider = OdProvider::create([
            'ProvNum' => 19,
            'LName' => 'Bernard',
            'PName' => 'Andy',
            'Abbr' => 'AB',
            'IsHidden' => 'false',
        ]);

        $patient = OdPatient::create([
            'PatNum' => 401,
            'LName' => 'Flenderson',
            'FName' => 'Toby',
            'PriProv' => $provider->ProvNum,
        ]);

        $this->makeRecall([
            'RecallNum' => 401,
            'PatNum' => $patient->PatNum,
            'DateDue' => '2026-08-20',
            'office_id' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('hygiene-recall.export', [
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Bernard, Andy', $response->streamedContent());
        $this->assertStringContainsString('Missed Recall', $response->streamedContent());
    }
}
