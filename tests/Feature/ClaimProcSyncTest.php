<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Services\OpenDental\QueryService;
use App\Services\Sync\ClaimProcSyncService;
use Mockery\MockInterface;
use Tests\TestCase;

class ClaimProcSyncTest extends TestCase
{
    public function test_claim_proc_sync_normalizes_dates_and_persists_records(): void
    {
        $office = Office::create(['name' => 'Main Clinic', 'is_active' => true]);

        $this->mock(QueryService::class, function (MockInterface $mock) {
            $mock->shouldReceive('forOffice')->andReturnSelf();
            $mock->shouldReceive('shortQuery')
                ->once()
                ->andReturn([
                    [
                        'ClaimProcNum' => 7001,
                        'ClaimNum' => 12,
                        'ProcNum' => 45,
                        'PatNum' => 88,
                        'ProvNum' => 2,
                        'Status' => 1,
                        'ClaimPaymentNum' => 0,
                        'DateCP' => '0001-01-01',
                        'ProcDate' => '2026-08-01 00:00:00',
                        'DateEntry' => '2026-08-01',
                        'SecDateEntry' => '0000-00-00',
                        'SecDateTEdit' => '2026-08-01T15:30:00',
                        'DateSuppReceived' => '0001-01-01',
                        'DateInsFinalized' => '0001-01-01',
                    ],
                ])
                ->shouldReceive('shortQuery')
                ->andReturn([]);
        });

        $service = app(ClaimProcSyncService::class)->forOffice($office);
        $service->sync();

        $this->assertDatabaseHas('od_claim_procs', [
            'ClaimProcNum' => 7001,
            'ProcDate' => '2026-08-01',
            'DateEntry' => '2026-08-01',
            'DateCP' => null,
            'SecDateEntry' => null,
            'DateSuppReceived' => null,
            'DateInsFinalized' => null,
            'SecDateTEdit' => '2026-08-01 15:30:00',
        ]);
    }
}
