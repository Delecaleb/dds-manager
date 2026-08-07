<?php

namespace App\Services\Sync;

use App\Models\SyncLog;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Guarantor-level balance is not an OpenDental table — it is a local rollup
 * of each family member's individual balance buckets (already synced onto
 * od_patients) onto their guarantor. Mirrors the guarantor-resolution rule
 * used by AgingController: fall back to the patient themselves when
 * `Guarantor` doesn't resolve to another patient.
 */
class PatientBalanceSyncService
{
    protected function module(): string
    {
        return 'patient-balance';
    }

    public function sync(): void
    {
        $log = SyncLog::firstOrCreate(
            ['module' => $this->module()],
            ['status' => 'idle', 'total_processed' => 0]
        );

        $log->update([
            'status' => 'running',
            'started_at' => now(),
            'last_error' => null,
        ]);

        try {
            $rows = $this->guarantorRollups();

            foreach (array_chunk($rows, 500) as $batch) {
                DB::table('od_patient_balances')->upsert(
                    $batch,
                    ['office_id', 'PatNum'],
                    ['Bal_0_30', 'Bal_31_60', 'Bal_61_90', 'BalOver90', 'Total', 'InsEst', 'updated_at']
                );
            }

            $log->update([
                'status' => 'completed',
                'finished_at' => now(),
                'total_processed' => count($rows),
                'last_synced_at' => now(),
                'retry_count' => 0,
            ]);
        } catch (Exception $e) {
            $log->increment('retry_count');

            $log->update([
                'status' => 'failed',
                'last_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function guarantorRollups(): array
    {
        $now = now();

        return DB::table('od_patients as p')
            ->leftJoin('od_patients as g', function ($join) {
                $join->on('p.Guarantor', '=', 'g.PatNum')
                    ->on('p.office_id', '=', 'g.office_id');
            })
            ->groupBy('p.office_id', DB::raw('COALESCE(g.PatNum, p.PatNum)'))
            ->selectRaw("
                p.office_id as office_id,
                COALESCE(g.PatNum, p.PatNum) as PatNum,
                COALESCE(SUM(CAST(NULLIF(p.Bal_0_30, '') AS DECIMAL(10,2))), 0)  as Bal_0_30,
                COALESCE(SUM(CAST(NULLIF(p.Bal_31_60, '') AS DECIMAL(10,2))), 0) as Bal_31_60,
                COALESCE(SUM(CAST(NULLIF(p.Bal_61_90, '') AS DECIMAL(10,2))), 0) as Bal_61_90,
                COALESCE(SUM(CAST(NULLIF(p.BalOver90, '') AS DECIMAL(10,2))), 0) as BalOver90,
                COALESCE(SUM(CAST(NULLIF(p.BalTotal, '') AS DECIMAL(10,2))), 0)  as Total,
                COALESCE(SUM(CAST(NULLIF(p.InsEst, '') AS DECIMAL(10,2))), 0)    as InsEst
            ")
            ->get()
            ->map(fn ($row) => (array) $row + ['created_at' => $now, 'updated_at' => $now])
            ->all();
    }
}
