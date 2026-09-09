<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\SyncLog;
use App\Services\OpenDental\QueryService;
use App\Services\Sync\PatientSyncService;
use Mockery\MockInterface;
use Tests\TestCase;

class PatientSyncTest extends TestCase
{
    public function test_patient_sync_normalizes_dates_and_persists_records(): void
    {
        $office = Office::create(['name' => 'Main Clinic', 'is_active' => true]);

        $this->mock(QueryService::class, function (MockInterface $mock) {
            $mock->shouldReceive('forOffice')->andReturnSelf();
            $mock->shouldReceive('shortQuery')
                ->once()
                ->andReturn([
                    [
                        'PatNum' => 8001,
                        'LName' => 'Smith',
                        'FName' => 'Jane',
                        'Email' => 'jane@example.com',
                        'Birthdate' => '1990-05-15',
                        'PatStatus' => 0,
                        'DateFirstVisit' => '0001-01-01',
                        'SecDateEntry' => '2026-08-15',
                        'DateTStamp' => '2026-08-15T10:30:00',
                        'SecUserNumEntry' => 3,
                        'ClinicNum' => 1,
                        'HasIns' => 'I',
                        'Urgency' => 0,
                    ],
                ])
                ->shouldReceive('shortQuery')
                ->andReturn([]);
        });

        $service = app(PatientSyncService::class)->forOffice($office);
        $service->sync();

        $this->assertDatabaseHas('od_patients', [
            'PatNum' => 8001,
            'LName' => 'Smith',
            'FName' => 'Jane',
            'Birthdate' => '1990-05-15',
            'DateFirstVisit' => null,
            'SecDateEntry' => '2026-08-15',
            'DateTStamp' => '2026-08-15 10:30:00',
            'SecUserNumEntry' => 3,
            'ClinicNum' => 1,
            'HasIns' => 'I',
        ]);
    }

    public function test_patients_range_command_syncs_with_date_window(): void
    {
        $office = Office::create(['name' => 'Main Clinic', 'is_active' => true]);

        $this->mock(QueryService::class, function (MockInterface $mock) {
            $mock->shouldReceive('forOffice')->andReturnSelf();
            $mock->shouldReceive('shortQuery')
                ->withArgs(function ($sql) {
                    return str_contains($sql, "SecDateEntry >= '2025-01-01'")
                        && str_contains($sql, "SecDateEntry <= '2026-12-31'");
                })
                ->once()
                ->andReturn([
                    [
                        'PatNum' => 8002,
                        'LName' => 'Doe',
                        'FName' => 'John',
                        'Email' => 'john.doe@example.com',
                        'Birthdate' => '1985-04-20',
                        'PatStatus' => 0,
                        'DateFirstVisit' => '2025-03-01',
                        'SecDateEntry' => '2025-02-15',
                        'DateTStamp' => '2025-02-15T09:00:00',
                        'SecUserNumEntry' => 1,
                        'ClinicNum' => 1,
                        'HasIns' => 'I',
                        'Urgency' => 0,
                    ],
                ])
                ->shouldReceive('shortQuery')
                ->andReturn([]);
        });

        $this->artisan('sync:patients-range', [
            '--since' => '2025-01-01',
            '--until' => '2026-12-31',
        ])->assertSuccessful();

        $this->assertDatabaseHas('od_patients', [
            'PatNum' => 8002,
            'LName' => 'Doe',
            'FName' => 'John',
            'SecDateEntry' => '2025-02-15',
            'DateTStamp' => '2025-02-15 09:00:00',
        ]);
    }

    public function test_sync_patients_with_fresh_flag_resets_cursor_and_runs_full_sync(): void
    {
        $office = Office::create(['name' => 'Main Clinic', 'is_active' => true]);

        // Pre-create a SyncLog with last_synced_at to simulate an existing incremental cursor
        SyncLog::create([
            'office_id' => $office->id,
            'module' => 'office_'.$office->id.':patient',
            'last_synced_at' => '2026-08-01 00:00:00',
            'status' => 'completed',
        ]);

        $this->mock(QueryService::class, function (MockInterface $mock) {
            $mock->shouldReceive('forOffice')->andReturnSelf();
            // Fresh flag causes an initial sync from ID 0 without DateTStamp > filter
            $mock->shouldReceive('shortQuery')
                ->withArgs(function ($sql) {
                    return str_contains($sql, 'PatNum > 0') && ! str_contains($sql, 'DateTStamp >');
                })
                ->once()
                ->andReturn([
                    [
                        'PatNum' => 8003,
                        'LName' => 'Vance',
                        'FName' => 'Bob',
                        'Email' => 'bob@vancerefrigeration.com',
                        'Birthdate' => '1970-01-01',
                        'PatStatus' => 0,
                        'SecDateEntry' => '2020-05-10',
                        'DateTStamp' => '2020-05-10 10:00:00',
                    ],
                ])
                ->shouldReceive('shortQuery')
                ->andReturn([]);
        });

        $this->artisan('sync:patients', ['--fresh' => true])->assertSuccessful();

        $this->assertDatabaseHas('od_patients', [
            'PatNum' => 8003,
            'LName' => 'Vance',
            'SecDateEntry' => '2020-05-10',
        ]);
    }
}
