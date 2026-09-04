<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\User;
use App\Services\Sync\SyncReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OfficeSyncReportTest extends TestCase
{
    use RefreshDatabase;

    private SyncReportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SyncReportService::class);
    }

    public function test_sync_report_service_returns_correct_module_structure(): void
    {
        $office = Office::create([
            'name' => 'Downtown Dental',
            'is_active' => true,
        ]);

        $report = $this->service->getReportForOffice($office);

        $this->assertIsArray($report);
        $this->assertEquals($office->id, $report['office']['id']);
        $this->assertEquals('Downtown Dental', $report['office']['name']);
        $this->assertTrue($report['office']['is_active']);

        $this->assertArrayHasKey('summary', $report);
        $this->assertArrayHasKey('running', $report['summary']);
        $this->assertArrayHasKey('slow', $report['summary']);
        $this->assertArrayHasKey('stuck', $report['summary']);
        $this->assertArrayHasKey('idle', $report['summary']);
        $this->assertArrayHasKey('total_records', $report['summary']);

        $this->assertIsArray($report['items']);
        $this->assertNotEmpty($report['items']);

        // Check Patients module item
        $patientItem = collect($report['items'])->firstWhere('key', 'patients');
        $this->assertNotNull($patientItem);
        $this->assertEquals('Patients', $patientItem['sync']);
        $this->assertArrayHasKey('status', $patientItem);
        $this->assertArrayHasKey('last_heartbeat', $patientItem);
        $this->assertArrayHasKey('records', $patientItem);
    }

    public function test_sync_report_evaluates_running_slow_stuck_and_idle_statuses(): void
    {
        $office = Office::create([
            'name' => 'Uptown Dental',
            'is_active' => true,
        ]);
        $officeId = $office->id;

        $now = Carbon::parse('2026-09-04 12:00:00');
        Carbon::setTestNow($now);

        // 1. Running: heartbeat 15 seconds ago
        DB::table('sync_logs')->insert([
            'office_id' => $officeId,
            'module' => "office_{$officeId}:od_patients",
            'status' => 'running',
            'last_synced_at' => $now->copy()->subSeconds(15),
            'updated_at' => $now->copy()->subSeconds(15),
            'created_at' => $now->copy()->subSeconds(15),
        ]);

        // 2. Slow: heartbeat 5 minutes ago
        DB::table('sync_logs')->insert([
            'office_id' => $officeId,
            'module' => "office_{$officeId}:od_procedure_logs",
            'status' => 'running',
            'last_synced_at' => $now->copy()->subMinutes(5),
            'updated_at' => $now->copy()->subMinutes(5),
            'created_at' => $now->copy()->subMinutes(5),
        ]);

        // 3. Stuck: running with heartbeat 45 minutes ago
        DB::table('sync_logs')->insert([
            'office_id' => $officeId,
            'module' => "office_{$officeId}:od_payments",
            'status' => 'running',
            'last_synced_at' => $now->copy()->subMinutes(45),
            'updated_at' => $now->copy()->subMinutes(45),
            'created_at' => $now->copy()->subMinutes(45),
        ]);

        // 4. Stuck: failed status
        DB::table('sync_logs')->insert([
            'office_id' => $officeId,
            'module' => "office_{$officeId}:od_claim_procs",
            'status' => 'failed',
            'last_error' => 'Database connection timeout',
            'last_synced_at' => $now->copy()->subMinutes(2),
            'updated_at' => $now->copy()->subMinutes(2),
            'created_at' => $now->copy()->subMinutes(2),
        ]);

        // 5. Idle: completed 2 hours ago
        DB::table('sync_logs')->insert([
            'office_id' => $officeId,
            'module' => "office_{$officeId}:od_recalls",
            'status' => 'completed',
            'last_synced_at' => $now->copy()->subHours(2),
            'updated_at' => $now->copy()->subHours(2),
            'created_at' => $now->copy()->subHours(2),
        ]);

        $report = $this->service->getReportForOffice($office);
        $items = collect($report['items'])->keyBy('key');

        // Verify Patients -> 🟢 Running (15 sec ago)
        $patients = $items->get('patients');
        $this->assertEquals('🟢 Running', $patients['status']);
        $this->assertEquals('15 sec ago', $patients['last_heartbeat']);

        // Verify Procedure Logs -> 🟡 Slow (5 min ago)
        $procedures = $items->get('procedurelogs');
        $this->assertEquals('🟡 Slow', $procedures['status']);
        $this->assertEquals('5 min ago', $procedures['last_heartbeat']);

        // Verify Payments -> 🔴 Stuck (45 min ago)
        $payments = $items->get('payments');
        $this->assertEquals('🔴 Stuck', $payments['status']);
        $this->assertEquals('45 min ago', $payments['last_heartbeat']);

        // Verify Claim Procs -> 🔴 Stuck
        $claimProcs = $items->get('claimprocs');
        $this->assertEquals('🔴 Stuck', $claimProcs['status']);
        $this->assertEquals('Database connection timeout', $claimProcs['last_error']);

        // Verify Recalls -> ⚪ Idle (2 hours ago)
        $recalls = $items->get('recalls');
        $this->assertEquals('⚪ Idle', $recalls['status']);
        $this->assertEquals('2 hours ago', $recalls['last_heartbeat']);

        Carbon::setTestNow(); // reset
    }

    public function test_sync_report_counts_records_scoped_by_office_id(): void
    {
        $officeA = Office::create(['name' => 'Office Alpha', 'is_active' => true]);
        $officeB = Office::create(['name' => 'Office Beta', 'is_active' => true]);

        // Seed 3 patients for Office A and 1 for Office B
        DB::table('od_patients')->insert([
            ['PatNum' => 101, 'office_id' => $officeA->id, 'LName' => 'Smith', 'FName' => 'John'],
            ['PatNum' => 102, 'office_id' => $officeA->id, 'LName' => 'Doe', 'FName' => 'Jane'],
            ['PatNum' => 103, 'office_id' => $officeA->id, 'LName' => 'Brown', 'FName' => 'Charlie'],
            ['PatNum' => 201, 'office_id' => $officeB->id, 'LName' => 'Taylor', 'FName' => 'Alice'],
        ]);

        $reportA = $this->service->getReportForOffice($officeA);
        $reportB = $this->service->getReportForOffice($officeB);

        $patientItemA = collect($reportA['items'])->firstWhere('key', 'patients');
        $patientItemB = collect($reportB['items'])->firstWhere('key', 'patients');

        $this->assertEquals(3, $patientItemA['records']);
        $this->assertEquals('3', $patientItemA['records_formatted']);

        $this->assertEquals(1, $patientItemB['records']);
        $this->assertEquals('1', $patientItemB['records_formatted']);
    }

    public function test_sync_report_http_endpoint_returns_json_for_authenticated_user(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $office = Office::create(['name' => 'Central Dental', 'is_active' => true]);

        $response = $this->actingAs($user)
            ->getJson(route('offices.sync-report', $office->id));

        $response->assertOk();
        $response->assertJsonStructure([
            'office' => ['id', 'name', 'is_active'],
            'summary' => ['running', 'slow', 'stuck', 'idle', 'total_records'],
            'items' => [
                '*' => [
                    'key',
                    'sync',
                    'status',
                    'status_raw',
                    'last_heartbeat',
                    'records',
                    'records_formatted',
                ],
            ],
        ]);
    }

    public function test_sync_module_endpoint_validates_module(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $office = Office::create(['name' => 'East Dental', 'is_active' => true]);

        $response = $this->actingAs($user)
            ->postJson(route('offices.sync-module', $office->id), [
                'module' => 'invalid_module_xyz',
            ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_sync_report(): void
    {
        $office = Office::create(['name' => 'West Dental', 'is_active' => true]);

        $response = $this->getJson(route('offices.sync-report', $office->id));
        $response->assertUnauthorized();
    }
}
