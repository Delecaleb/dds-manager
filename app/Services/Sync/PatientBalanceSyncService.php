<?php

namespace App\Services\Sync;

use App\Models\Office;
use App\Services\Sync\Concerns\RetriesLockContention;
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
    use RetriesLockContention;

    /** The balance columns a rollup writes; a row is rewritten only when one of them changed. */
    private const BALANCE_COLUMNS = ['Bal_0_30', 'Bal_31_60', 'Bal_61_90', 'BalOver90', 'Total', 'InsEst'];

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
            $rows = $this->changedRows($this->guarantorRollups($officeId), (int) $officeId);

            // Every office's job runs at the same moment against this shared table. Writing in
            // PatNum order makes concurrent jobs take index locks in the same order, and the
            // retry absorbs the occasional deadlock MySQL still reports.
            usort($rows, fn (array $a, array $b) => (int) $a['PatNum'] <=> (int) $b['PatNum']);

            foreach (array_chunk($rows, 500) as $batch) {
                $this->retryOnLockContention(fn () => DB::table('od_patient_balances')->upsert(
                    $batch,
                    ['office_id', 'PatNum'],
                    [...self::BALANCE_COLUMNS, 'updated_at']
                ));
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

    /**
     * Drop rollups whose stored balance is already identical, so a run only writes (and locks)
     * the guarantors whose balance actually moved since the last run.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    protected function changedRows(array $rows, int $officeId): array
    {
        $stored = DB::table('od_patient_balances')
            ->where('office_id', $officeId)
            ->get(['PatNum', ...self::BALANCE_COLUMNS])
            ->mapWithKeys(fn ($row) => [(string) $row->PatNum => $this->fingerprint((array) $row)])
            ->all();

        return array_values(array_filter(
            $rows,
            fn (array $row) => ($stored[(string) $row['PatNum']] ?? null) !== $this->fingerprint($row)
        ));
    }

    /** The balance values as fixed 2-decimal text, so "12.5", 12.50 and "12.50" compare equal. */
    private function fingerprint(array $row): string
    {
        return implode('|', array_map(
            fn (string $column) => number_format((float) ($row[$column] ?? 0), 2, '.', ''),
            self::BALANCE_COLUMNS
        ));
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
