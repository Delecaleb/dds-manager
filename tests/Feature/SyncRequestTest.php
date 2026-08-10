<?php

namespace Tests\Feature;

use App\Models\SyncRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_access_sync_manager_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/sync-manager');

        $response->assertStatus(200);
        $response->assertSee('Data Synchronization');
    }

    public function test_can_create_date_range_sync_request_from_api(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/sync-manager/trigger', [
            'module' => 'appointments',
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
            'prune_deleted' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('sync_requests', [
            'module' => 'appointments',
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
            'prune_deleted' => 1,
            'status' => 'pending',
        ]);
    }

    public function test_can_fetch_sync_requests_list(): void
    {
        $user = User::factory()->create();

        SyncRequest::create([
            'office_id' => 1,
            'module' => 'procedurelogs',
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
            'status' => 'completed',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->getJson('/sync-manager/requests');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'requests');
        $response->assertJsonPath('requests.0.module', 'procedurelogs');
    }

    public function test_can_cancel_pending_sync_request(): void
    {
        $user = User::factory()->create();

        $req = SyncRequest::create([
            'office_id' => 1,
            'module' => 'appointments',
            'status' => 'pending',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->postJson('/sync-manager/cancel', [
            'id' => $req->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('sync_requests', [
            'id' => $req->id,
            'status' => 'cancelled',
        ]);
    }
}
