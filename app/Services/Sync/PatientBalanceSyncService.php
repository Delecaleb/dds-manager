<?php

namespace App\Services\Sync;

use App\Models\Office;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Guarantor-level balance is not an OpenDental table — it is a local rollup
 * of each family member's individual balance buckets (already synced onto
 * od_patients) onto their guarantor. Mirrors the guarantor-resolution rule
 * used by AgingController: fall back to the patient themselves when
 * `Guarantor` doesn't resolve to another patient.
 */
class PatientBalanceSyncService
{
    protected ?Office $office = null;

    public function forOffice(?Office $office): static
    {
        $this->office = $office;

        return $this;
    }

    public function getOffice(): Office
    {
        return $this->office ?? Office::getActiveOffice() ?? Office::first() ?? new Office(['id' => 1]);
    }

    protected function module(): string
    {
        $officeId = $this->getOffice()->id ?? 1;

        return "office_{$officeId}:patient-balance";
    }

    public function sync(): void
    {
        $office = $this->getOffice();
        $officeId = $office->id ?? 1;

        $lease = SyncLease::acquire($this->module(), (int) $officeId);

        if ($lease === null) {
            return;
        }

        try {
            $rows = $this->guarantorRollups($officeId);

            foreach (array_chunk($rows, 500) as $batch) {
                DB::table('od_patient_balances')->upsert(
                    $batch,
                    ['office_id', 'PatNum'],
                    ['Bal_0_30', 'Bal_31_60', 'Bal_61_90', 'BalOver90', 'Total', 'InsEst', 'updated_at']
                );
            }

            $lease->complete([
                'total_processed' => count($rows),
                'last_synced_at' => now(),
            ]);
        } catch (SyncLeaseLostException $e) {
            throw $e;
        } catch (Throwable $e) {
            $lease->fail($e);

            throw $e;
        }
    }

    protected function guarantorRollups(?int $officeId = null): array
    {
        $now = now();

        $query = DB::table('od_patients as p')
            ->leftJoin('od_patients as g', function ($join) {
                $join->on('p.Guarantor', '=', 'g.PatNum')
                    ->on('p.office_id', '=', 'g.office_id');
            });

        if ($officeId !== null) {
            $query->where('p.office_id', $officeId);
        }

        return $query
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
