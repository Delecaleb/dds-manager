<?php

namespace Tests\Feature;

use App\Models\OdProcedureLog;
use App\Models\Office;
use App\Services\OpenDental\QueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PruneDeletedSyncedDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_prune_deleted_command_dry_run_identifies_hard_deleted_records_without_deleting(): void
    {
        Office::create(['id' => 1, 'name' => '8 Mile', 'is_active' => true]);

        // Insert 3 local procedure log records
        OdProcedureLog::create(['office_id' => 1, 'ProcNum' => 1001, 'ProcDate' => '2026-08-05', 'ProcFee' => 150]);
        OdProcedureLog::create(['office_id' => 1, 'ProcNum' => 1002, 'ProcDate' => '2026-08-05', 'ProcFee' => 200]);
        OdProcedureLog::create(['office_id' => 1, 'ProcNum' => 1003, 'ProcDate' => '2026-08-05', 'ProcFee' => 250]); // Hard-deleted in OD

        // Mock QueryService to return only ProcNum 1001 & 1002 from OpenDental
        $mockQueryService = Mockery::mock(QueryService::class);
        $mockQueryService->shouldReceive('forOffice')->andReturnSelf();
        $mockQueryService->shouldReceive('shortQuery')
            ->once()
            ->andReturn([
                ['ProcNum' => 1001],
                ['ProcNum' => 1002],
            ]);

        $this->app->instance(QueryService::class, $mockQueryService);

        $this->artisan('sync:prune-deleted', [
            'table' => 'od_procedure_logs',
            '--start-date' => '2026-08-01',
            '--end-date' => '2026-08-07',
            '--dry-run' => true,
        ])->assertExitCode(0);

        // Dry run should keep all 3 records in DB
        $this->assertDatabaseHas('od_procedure_logs', ['ProcNum' => 1001]);
        $this->assertDatabaseHas('od_procedure_logs', ['ProcNum' => 1002]);
        $this->assertDatabaseHas('od_procedure_logs', ['ProcNum' => 1003]);
    }

    public function test_prune_deleted_command_removes_hard_deleted_records_and_updates_sync_log(): void
    {
        Office::create(['id' => 1, 'name' => '8 Mile', 'is_active' => true]);

        // Insert 3 local procedure log records
        OdProcedureLog::create(['office_id' => 1, 'ProcNum' => 2001, 'ProcDate' => '2026-08-05', 'ProcFee' => 150]);
        OdProcedureLog::create(['office_id' => 1, 'ProcNum' => 2002, 'ProcDate' => '2026-08-05', 'ProcFee' => 200]);
        OdProcedureLog::create(['office_id' => 1, 'ProcNum' => 2003, 'ProcDate' => '2026-08-05', 'ProcFee' => 250]); // Hard-deleted in OD

        // Mock QueryService to return only ProcNum 2001 & 2002 from OpenDental
        $mockQueryService = Mockery::mock(QueryService::class);
        $mockQueryService->shouldReceive('forOffice')->andReturnSelf();
        $mockQueryService->shouldReceive('shortQuery')
            ->once()
            ->andReturn([
                ['ProcNum' => 2001],
                ['ProcNum' => 2002],
            ]);

        $this->app->instance(QueryService::class, $mockQueryService);

        $this->artisan('sync:prune-deleted', [
            'table' => 'od_procedure_logs',
            '--start-date' => '2026-08-01',
            '--end-date' => '2026-08-07',
        ])->assertExitCode(0);

        // Record 2003 should be purged from database
        $this->assertDatabaseHas('od_procedure_logs', ['ProcNum' => 2001]);
        $this->assertDatabaseHas('od_procedure_logs', ['ProcNum' => 2002]);
        $this->assertDatabaseMissing('od_procedure_logs', ['ProcNum' => 2003]);

        // Verify sync_log entry was created with office_id and office_1:... module name
        $this->assertDatabaseHas('sync_logs', [
            'office_id' => 1,
            'module' => 'office_1:prune-deleted:od_procedure_logs',
            'status' => 'completed',
            'total_processed' => 1,
        ]);
    }

    public function test_prune_deleted_full_scan_loops_all_existing_records_and_updates_sync_log(): void
    {
        Office::create(['id' => 1, 'name' => '8 Mile', 'is_active' => true]);

        OdProcedureLog::create(['office_id' => 1, 'ProcNum' => 3001, 'ProcDate' => '2024-01-10', 'ProcFee' => 100]);
        OdProcedureLog::create(['office_id' => 1, 'ProcNum' => 3002, 'ProcDate' => '2025-05-15', 'ProcFee' => 200]); // Deleted in OD

        $mockQueryService = Mockery::mock(QueryService::class);
        $mockQueryService->shouldReceive('forOffice')->andReturnSelf();
        $mockQueryService->shouldReceive('shortQuery')
            ->once()
            ->andReturn([
                ['ProcNum' => 3001],
            ]);

        $this->app->instance(QueryService::class, $mockQueryService);

        $this->artisan('sync:prune-deleted', [
            'table' => 'od_procedure_logs',
            '--full' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseHas('od_procedure_logs', ['ProcNum' => 3001]);
        $this->assertDatabaseMissing('od_procedure_logs', ['ProcNum' => 3002]);

        $this->assertDatabaseHas('sync_logs', [
            'office_id' => 1,
            'module' => 'office_1:prune-deleted:od_procedure_logs:full',
            'status' => 'completed',
            'total_processed' => 1,
        ]);
    }

    public function test_prune_deleted_current_year_purges_orphans_in_current_year(): void
    {
        Office::create(['id' => 1, 'name' => '8 Mile', 'is_active' => true]);

        $currentYearDate = now()->startOfYear()->addDays(10)->toDateString();

        OdProcedureLog::create(['office_id' => 1, 'ProcNum' => 4001, 'ProcDate' => $currentYearDate, 'ProcFee' => 100]);
        OdProcedureLog::create(['office_id' => 1, 'ProcNum' => 4002, 'ProcDate' => $currentYearDate, 'ProcFee' => 200]); // Deleted in OD

        $mockQueryService = Mockery::mock(QueryService::class);
        $mockQueryService->shouldReceive('forOffice')->andReturnSelf();
        $mockQueryService->shouldReceive('shortQuery')
            ->once()
            ->andReturn([
                ['ProcNum' => 4001],
            ]);

        $this->app->instance(QueryService::class, $mockQueryService);

        $this->artisan('sync:prune-deleted', [
            'table' => 'od_procedure_logs',
            '--current-year' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseHas('od_procedure_logs', ['ProcNum' => 4001]);
        $this->assertDatabaseMissing('od_procedure_logs', ['ProcNum' => 4002]);

        $this->assertDatabaseHas('sync_logs', [
            'office_id' => 1,
            'module' => 'office_1:prune-deleted:od_procedure_logs',
            'status' => 'completed',
            'total_processed' => 1,
        ]);
    }
}
