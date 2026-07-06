<?php

namespace App\Console\Commands;

use App\Models\ClaimProcs;
use App\Services\OpenDental\QueryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\SyncLog;

class SyncOpenDentalClaimProcs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:claimprocs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync claim procedures from OpenDental to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queryService = app(QueryService::class);

        $log = SyncLog::firstOrCreate(
            ['module' => 'claimprocs'],
            ['status' => 'idle', 'total_processed' => 0]
        );

        $log->update([
            'status' => 'running',
            'started_at' => now(),
            'last_error' => null,
        ]);

        try {
            $lastId = $log->last_primary_key ?? 0;

            while (true) {
                $sql = "
                    SELECT *
                    FROM claimproc
                    WHERE ClaimProcNum > {$lastId}
                    ORDER BY ClaimProcNum
                    LIMIT 1000
                ";

                $rows = $queryService->shortQuery($sql);

                if (empty($rows)) {
                    break;
                }

                DB::transaction(function () use ($rows, &$lastId, $log) {
                    foreach ($rows as $row) {
                        ClaimProcs::updateOrCreate(
                            ['ClaimProcNum' => $row['ClaimProcNum']],
                            $row
                        );
                        $lastId = $row['ClaimProcNum'];
                        $log->increment('total_processed');
                    }
                });

                $log->update(['last_primary_key' => $lastId]);

                echo "Synced through ClaimProcNum {$lastId}\n";
                sleep(1);
            }

            $log->update([
                'status' => 'completed',
                'finished_at' => now(),
                'retry_count' => 0,
            ]);
        } catch (\Exception $e) {
            $log->increment('retry_count');
            $log->update([
                'status' => 'failed',
                'last_error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
