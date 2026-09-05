<?php

namespace Tests\Feature;

use App\Models\OdAppointment;
use App\Models\Office;
use App\Models\User;
use App\Services\OpenDental\QueryService;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use Tests\TestCase;

class OpenDentalExplorerTest extends TestCase
{
    public function test_od_explorer_index_requires_authentication(): void
    {
        $response = $this->get('/open-dental-explorer');
        $response->assertRedirect('/login');
    }

    public function test_od_explorer_index_page_is_accessible_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/open-dental-explorer');
        $response->assertStatus(200);
        $response->assertSee('OpenDental Realtime Data Explorer');
    }

    public function test_od_explorer_tables_endpoint_returns_json_table_list(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/open-dental-explorer/tables');
        $response->assertStatus(200);
        $response->assertJsonStructure(['opendental_tables', 'local_tables']);
    }

    public function test_od_explorer_columns_endpoint_returns_columns_for_table(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/open-dental-explorer/columns?table=patient');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'table',
            'resolved_table',
            'columns',
        ]);
    }

    public function test_od_explorer_query_endpoint_executes_via_opendental_live_api(): void
    {
        $user = User::factory()->create();

        $this->mock(QueryService::class, function (MockInterface $mock) {
            $mock->shouldReceive('forOffice')->andReturnSelf();
            $mock->shouldReceive('shortQuery')
                ->once()
                ->andReturn([
                    ['PatNum' => 1, 'LName' => 'Smith', 'FName' => 'John'],
                ]);
        });

        $response = $this->actingAs($user)->postJson('/open-dental-explorer/query', [
            'source' => 'opendental_live',
            'table' => 'patient',
            'columns' => ['PatNum', 'LName', 'FName'],
            'conditions' => [
                [
                    'logical' => 'and',
                    'column' => 'PatNum',
                    'operator' => '>',
                    'value' => '0',
                ],
            ],
            'order_by' => 'PatNum',
            'order_direction' => 'desc',
            'limit' => 10,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'source_type' => 'OpenDental Realtime API',
            'table' => 'patient',
            'count' => 1,
            'rows' => [
                ['PatNum' => 1, 'LName' => 'Smith', 'FName' => 'John'],
            ],
        ]);
    }

    public function test_od_explorer_query_endpoint_returns_local_db_when_requested(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/open-dental-explorer/query', [
            'source' => 'local_db',
            'table' => 'users',
            'columns' => ['id', 'name', 'email'],
            'conditions' => [
                [
                    'logical' => 'and',
                    'column' => 'id',
                    'operator' => '>',
                    'value' => '0',
                ],
            ],
            'order_by' => 'id',
            'order_direction' => 'desc',
            'limit' => 10,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'source_type',
            'table',
            'count',
            'execution_time_ms',
            'columns',
            'sql',
            'bindings',
            'rows',
        ]);
    }

    public function test_od_explorer_rejects_invalid_table(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/open-dental-explorer/columns?table=non_existent_table_xyz');
        $response->assertStatus(400);
        $response->assertJson(['error' => 'Invalid or unauthorized table selected.']);
    }

    public function test_od_explorer_sync_to_local_upserts_records_into_local_database(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/open-dental-explorer/sync-to-local', [
            'table' => 'patient',
            'rows' => [
                [
                    'PatNum' => 99999,
                    'LName' => 'Doe',
                    'FName' => 'Jane',
                    'PatStatus' => 0,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'table' => 'od_patients',
            'synced_count' => 1,
        ]);

        $this->assertDatabaseHas('od_patients', [
            'PatNum' => 99999,
            'LName' => 'Doe',
            'FName' => 'Jane',
        ]);
    }

    public function test_od_explorer_can_get_and_reset_sync_checkpoints(): void
    {
        $user = User::factory()->create();

        $getRes = $this->actingAs($user)->getJson('/open-dental-explorer/sync-checkpoints');
        $getRes->assertStatus(200);
        $getRes->assertJsonStructure(['logs']);

        $resetRes = $this->actingAs($user)->postJson('/open-dental-explorer/reset-sync-checkpoint', [
            'module' => 'od_patients',
            'last_synced_at' => '2026-01-01 00:00:00',
            'last_primary_key' => 0,
        ]);

        $resetRes->assertStatus(200);
        $resetRes->assertJson([
            'success' => true,
            'module' => 'od_patients',
        ]);

        $this->assertDatabaseHas('sync_logs', [
            'module' => 'od_patients',
            'last_synced_at' => '2026-01-01 00:00:00',
            'last_primary_key' => 0,
        ]);
    }

    public function test_od_explorer_supports_od_claim_proc_alias_for_columns_and_query(): void
    {
        $user = User::factory()->create();

        $colsRes = $this->actingAs($user)->getJson('/open-dental-explorer/columns?table=od_claim_proc');
        $colsRes->assertStatus(200);
        $colsRes->assertJson([
            'table' => 'od_claim_proc',
            'resolved_table' => 'od_claim_procs',
        ]);

        $this->mock(QueryService::class, function (MockInterface $mock) {
            $mock->shouldReceive('forOffice')->andReturnSelf();
            $mock->shouldReceive('shortQuery')
                ->once()
                ->andReturn([
                    ['ClaimProcNum' => 1001, 'ClaimNum' => 50, 'Status' => 1, 'DateCP' => '0001-01-01'],
                ]);
        });

        $queryRes = $this->actingAs($user)->postJson('/open-dental-explorer/query', [
            'source' => 'opendental_live',
            'table' => 'od_claim_proc',
            'limit' => 10,
        ]);

        $queryRes->assertStatus(200);
        $queryRes->assertJson([
            'source_type' => 'OpenDental Realtime API',
            'table' => 'claimproc',
            'count' => 1,
        ]);

        $syncRes = $this->actingAs($user)->postJson('/open-dental-explorer/sync-to-local', [
            'table' => 'od_claim_proc',
            'rows' => [
                [
                    'ClaimProcNum' => 88888,
                    'ClaimPaymentNum' => 0,
                    'Status' => 0,
                    'DateCP' => '0001-01-01',
                    'ProcDate' => '2026-08-01',
                ],
            ],
        ]);

        $syncRes->assertStatus(200);
        $syncRes->assertJson([
            'success' => true,
            'table' => 'od_claim_procs',
            'synced_count' => 1,
        ]);

        $this->assertDatabaseHas('od_claim_procs', [
            'ClaimProcNum' => 88888,
            'ProcDate' => '2026-08-01',
            'DateCP' => null,
        ]);
    }

    public function test_reconcile_diff_identifies_orphans_and_prunes_them(): void
    {
        $user = User::factory()->create();
        $office = Office::create([
            'id' => 1,
            'name' => 'Main Office',
            'developer_key' => 'dev_test',
            'customer_key' => 'cust_test',
        ]);

        // Create 2 appointments locally
        OdAppointment::create([
            'AptNum' => 5001,
            'PatNum' => 101,
            'AptDateTime' => '2026-08-10 10:00:00',
            'AptStatus' => 1,
            'office_id' => 1,
        ]);

        OdAppointment::create([
            'AptNum' => 5002,
            'PatNum' => 102,
            'AptDateTime' => '2026-08-10 11:00:00',
            'AptStatus' => 1,
            'office_id' => 1,
        ]);

        // Mock QueryService to return only AptNum 5001 from Live OD (so 5002 is an orphan)
        $mockQueryService = $this->mock(QueryService::class);
        $mockQueryService->shouldReceive('forOffice')->andReturnSelf();
        $mockQueryService->shouldReceive('shortQuery')->andReturn([
            [
                'AptNum' => 5001,
                'PatNum' => 101,
                'AptDateTime' => '2026-08-10 10:00:00',
                'AptStatus' => 1,
            ],
        ]);

        $res = $this->actingAs($user)->postJson('/open-dental-explorer/reconcile-diff', [
            'table' => 'appointment',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-19',
        ]);

        $res->assertStatus(200);
        $res->assertJson([
            'success' => true,
            'summary' => [
                'live_count' => 1,
                'local_count' => 2,
                'matched_count' => 1,
                'orphan_count' => 1,
                'missing_count' => 0,
            ],
            'orphan_keys' => ['5002'],
        ]);

        // Now prune orphan 5002
        $pruneRes = $this->actingAs($user)->postJson('/open-dental-explorer/prune-orphans', [
            'table' => 'appointment',
            'keys' => [5002],
        ]);

        $pruneRes->assertStatus(200);
        $pruneRes->assertJson([
            'success' => true,
            'deleted_count' => 1,
        ]);

        $this->assertDatabaseMissing('od_appointments', [
            'AptNum' => 5002,
        ]);
        $this->assertDatabaseHas('od_appointments', [
            'AptNum' => 5001,
        ]);
    }

    public function test_od_explorer_supports_histappointment_diff_and_prune(): void
    {
        $user = User::factory()->create();
        $office = Office::updateOrCreate(['id' => 1], [
            'name' => 'Main Test Office',
            'is_default' => true,
            'api_base_url' => 'https://api.opendental.com',
            'developer_key' => 'dev_test',
            'customer_key' => 'cust_test',
        ]);

        DB::table('od_histappointments')->insert([
            [
                'office_id' => $office->id,
                'HistApptNum' => 9001,
                'AptNum' => 5001,
                'PatNum' => 101,
                'AptDateTime' => '2026-08-10 10:00:00',
                'HistDateTStamp' => '2026-08-09 10:00:00',
                'AptStatus' => 1,
            ],
            [
                'office_id' => $office->id,
                'HistApptNum' => 9002,
                'AptNum' => 5002,
                'PatNum' => 102,
                'AptDateTime' => '2026-08-10 11:00:00',
                'HistDateTStamp' => '2026-08-09 11:00:00',
                'AptStatus' => 1,
            ],
        ]);

        $mockQueryService = $this->mock(QueryService::class);
        $mockQueryService->shouldReceive('forOffice')->andReturnSelf();
        $mockQueryService->shouldReceive('shortQuery')->andReturn([
            [
                'HistApptNum' => 9001,
                'AptNum' => 5001,
                'PatNum' => 101,
                'AptDateTime' => '2026-08-10 10:00:00',
                'HistDateTStamp' => '2026-08-09 10:00:00',
                'AptStatus' => 1,
            ],
        ]);

        $res = $this->actingAs($user)->postJson('/open-dental-explorer/reconcile-diff', [
            'table' => 'histappointment',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-19',
        ]);

        $res->assertStatus(200);
        $res->assertJson([
            'success' => true,
            'summary' => [
                'live_count' => 1,
                'local_count' => 2,
                'matched_count' => 1,
                'orphan_count' => 1,
                'missing_count' => 0,
            ],
            'orphan_keys' => ['9002'],
        ]);

        // Prune orphan 9002
        $pruneRes = $this->actingAs($user)->postJson('/open-dental-explorer/prune-orphans', [
            'table' => 'histappointment',
            'keys' => [9002],
        ]);

        $pruneRes->assertStatus(200);
        $pruneRes->assertJson([
            'success' => true,
            'deleted_count' => 1,
        ]);

        $this->assertDatabaseMissing('od_histappointments', [
            'HistApptNum' => 9002,
        ]);
        $this->assertDatabaseHas('od_histappointments', [
            'HistApptNum' => 9001,
        ]);
    }

    public function test_od_explorer_local_query_strictly_scoped_to_active_office(): void
    {
        $user = User::factory()->create();
        $office1 = Office::create(['id' => 1, 'name' => 'Office 1', 'is_active' => true]);
        $office2 = Office::create(['id' => 2, 'name' => 'Office 2', 'is_active' => true]);

        DB::table('od_patients')->insert([
            ['office_id' => 1, 'PatNum' => 1001, 'LName' => 'Smith', 'FName' => 'Office1Patient'],
            ['office_id' => 2, 'PatNum' => 2001, 'LName' => 'Johnson', 'FName' => 'Office2Patient'],
        ]);

        // Query with Office 1 active
        $res1 = $this->actingAs($user)->withSession(['active_office_id' => 1])->postJson('/open-dental-explorer/query', [
            'source' => 'local_db',
            'table' => 'patient',
            'columns' => ['PatNum', 'LName', 'FName', 'office_id'],
        ]);

        $res1->assertStatus(200);
        $res1->assertJson(['count' => 1]);
        $res1->assertJsonFragment(['PatNum' => 1001, 'FName' => 'Office1Patient']);
        $res1->assertJsonMissing(['FName' => 'Office2Patient']);

        // Query with Office 2 active
        $res2 = $this->actingAs($user)->withSession(['active_office_id' => 2])->postJson('/open-dental-explorer/query', [
            'source' => 'local_db',
            'table' => 'patient',
            'columns' => ['PatNum', 'LName', 'FName', 'office_id'],
        ]);

        $res2->assertStatus(200);
        $res2->assertJson(['count' => 1]);
        $res2->assertJsonFragment(['PatNum' => 2001, 'FName' => 'Office2Patient']);
        $res2->assertJsonMissing(['FName' => 'Office1Patient']);
    }

    public function test_od_explorer_local_query_nested_conditions_do_not_leak_cross_tenant_data(): void
    {
        $user = User::factory()->create();
        $office1 = Office::create(['id' => 1, 'name' => 'Office 1', 'is_active' => true]);
        $office2 = Office::create(['id' => 2, 'name' => 'Office 2', 'is_active' => true]);

        DB::table('od_appointments')->insert([
            ['office_id' => 1, 'AptNum' => 3001, 'PatNum' => 101, 'AptStatus' => 1, 'AptDateTime' => '2026-08-10 10:00:00'],
            ['office_id' => 2, 'AptNum' => 3002, 'PatNum' => 102, 'AptStatus' => 2, 'AptDateTime' => '2026-08-10 11:00:00'],
        ]);

        // Condition with OR: AptStatus = 1 OR AptStatus = 2
        $res = $this->actingAs($user)->withSession(['active_office_id' => 1])->postJson('/open-dental-explorer/query', [
            'source' => 'local_db',
            'table' => 'appointment',
            'conditions' => [
                ['column' => 'AptStatus', 'operator' => '=', 'value' => '1', 'logical' => 'and'],
                ['column' => 'AptStatus', 'operator' => '=', 'value' => '2', 'logical' => 'or'],
            ],
        ]);

        $res->assertStatus(200);
        $res->assertJson(['count' => 1]);
        $res->assertJsonFragment(['AptNum' => '3001']);
        $res->assertJsonMissing(['AptNum' => '3002']);
    }

    public function test_od_explorer_sync_to_local_enforces_active_office_id(): void
    {
        $user = User::factory()->create();
        $office2 = Office::create(['id' => 2, 'name' => 'Office 2', 'is_active' => true]);

        $res = $this->actingAs($user)->withSession(['active_office_id' => 2])->postJson('/open-dental-explorer/sync-to-local', [
            'table' => 'patient',
            'rows' => [
                ['PatNum' => 5555, 'LName' => 'Taylor', 'FName' => 'Alex'],
            ],
        ]);

        $res->assertStatus(200);
        $this->assertDatabaseHas('od_patients', [
            'office_id' => 2,
            'PatNum' => 5555,
            'LName' => 'Taylor',
        ]);
    }

    public function test_od_explorer_reconcile_diff_is_isolated_to_active_office(): void
    {
        $user = User::factory()->create();
        $office1 = Office::create(['id' => 1, 'name' => 'Office 1', 'is_active' => true]);
        $office2 = Office::create(['id' => 2, 'name' => 'Office 2', 'is_active' => true]);

        DB::table('od_appointments')->insert([
            ['office_id' => 1, 'AptNum' => 4001, 'PatNum' => 101, 'AptDateTime' => '2026-08-10 10:00:00', 'AptStatus' => 1],
            ['office_id' => 2, 'AptNum' => 4002, 'PatNum' => 102, 'AptDateTime' => '2026-08-10 11:00:00', 'AptStatus' => 1],
        ]);

        $mockQueryService = $this->mock(QueryService::class);
        $mockQueryService->shouldReceive('forOffice')->andReturnSelf();
        $mockQueryService->shouldReceive('shortQuery')->andReturn([
            ['AptNum' => 4001, 'PatNum' => 101, 'AptDateTime' => '2026-08-10 10:00:00', 'AptStatus' => 1],
        ]);

        $res = $this->actingAs($user)->withSession(['active_office_id' => 1])->postJson('/open-dental-explorer/reconcile-diff', [
            'table' => 'appointment',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-19',
        ]);

        $res->assertStatus(200);
        $res->assertJson([
            'success' => true,
            'summary' => [
                'live_count' => 1,
                'local_count' => 1, // Only office 1's record is counted in local snapshot
                'matched_count' => 1,
                'orphan_count' => 0,
                'missing_count' => 0,
            ],
        ]);
    }

    public function test_od_explorer_prune_orphans_never_deletes_records_from_other_offices(): void
    {
        $user = User::factory()->create();
        $office1 = Office::create(['id' => 1, 'name' => 'Office 1', 'is_active' => true]);
        $office2 = Office::create(['id' => 2, 'name' => 'Office 2', 'is_active' => true]);

        // Same AptNum in two different offices (composite key)
        DB::table('od_appointments')->insert([
            ['office_id' => 1, 'AptNum' => 7777, 'PatNum' => 101, 'AptDateTime' => '2026-08-10 10:00:00', 'AptStatus' => 1],
            ['office_id' => 2, 'AptNum' => 7777, 'PatNum' => 202, 'AptDateTime' => '2026-08-10 11:00:00', 'AptStatus' => 1],
        ]);

        // Prune from Office 1
        $res = $this->actingAs($user)->withSession(['active_office_id' => 1])->postJson('/open-dental-explorer/prune-orphans', [
            'table' => 'appointment',
            'keys' => [7777],
        ]);

        $res->assertStatus(200);
        $res->assertJson(['success' => true, 'deleted_count' => 1]);

        // Office 1 record deleted
        $this->assertDatabaseMissing('od_appointments', [
            'office_id' => 1,
            'AptNum' => 7777,
        ]);

        // Office 2 record MUST remain intact!
        $this->assertDatabaseHas('od_appointments', [
            'office_id' => 2,
            'AptNum' => 7777,
            'PatNum' => 202,
        ]);
    }

    public function test_od_explorer_sync_checkpoints_and_requests_scoped_to_active_office(): void
    {
        $user = User::factory()->create();
        $office1 = Office::create(['id' => 1, 'name' => 'Office 1', 'is_active' => true]);
        $office2 = Office::create(['id' => 2, 'name' => 'Office 2', 'is_active' => true]);

        DB::table('sync_logs')->insert([
            ['office_id' => 1, 'module' => 'office1_module', 'status' => 'idle', 'last_primary_key' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['office_id' => 2, 'module' => 'office2_module', 'status' => 'idle', 'last_primary_key' => 20, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('sync_requests')->insert([
            ['office_id' => 1, 'module' => 'appointments', 'status' => 'pending', 'created_by' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['office_id' => 2, 'module' => 'patients', 'status' => 'pending', 'created_by' => $user->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Checkpoints in Office 1
        $cpRes = $this->actingAs($user)->withSession(['active_office_id' => 1])->getJson('/open-dental-explorer/sync-checkpoints');
        $cpRes->assertStatus(200);
        $cpRes->assertJsonFragment(['module' => 'office1_module']);
        $cpRes->assertJsonMissing(['module' => 'office2_module']);

        // Sync Requests in Office 1
        $reqRes = $this->actingAs($user)->withSession(['active_office_id' => 1])->getJson('/open-dental-explorer/sync-requests');
        $reqRes->assertStatus(200);
        $reqRes->assertJsonFragment(['module' => 'appointments']);
        $reqRes->assertJsonMissing(['module' => 'patients']);
    }

    public function test_od_explorer_cannot_cancel_sync_request_of_another_office(): void
    {
        $user = User::factory()->create();
        $office1 = Office::create(['id' => 1, 'name' => 'Office 1', 'is_active' => true]);
        $office2 = Office::create(['id' => 2, 'name' => 'Office 2', 'is_active' => true]);

        $syncReqId = DB::table('sync_requests')->insertGetId([
            'office_id' => 2,
            'module' => 'patients',
            'status' => 'pending',
            'created_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Attempt cancel from Office 1
        $res = $this->actingAs($user)->withSession(['active_office_id' => 1])->postJson('/open-dental-explorer/cancel-sync-request', [
            'id' => $syncReqId,
        ]);

        $res->assertStatus(404);
        $res->assertJson(['error' => 'Sync request not found.']);

        $this->assertDatabaseHas('sync_requests', [
            'id' => $syncReqId,
            'status' => 'pending',
        ]);
    }
}
