<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OpenDental\QueryService;
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
}
