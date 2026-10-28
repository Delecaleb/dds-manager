<?php

namespace App\Services\Sync;

use App\Domain\Patient\PatientVisitDays;
use App\Models\OdProcedureLog;
use Illuminate\Support\Facades\DB;

class ProcedureLogSyncService extends BaseQuerySyncService
{
    protected int $batchSize = 1000;

    protected function table(): string
    {
        return 'procedurelog';
    }

    protected function model(): string
    {
        return OdProcedureLog::class;
    }

    protected function primaryKey(): string
    {
        return 'ProcNum';
    }

    /**
     * procedurelog supports incremental sync.
     */
    protected function syncColumn(): ?string
    {
        return 'DateTStamp';
    }

    /**
     * Business date a windowed sync (sync:procedurelogs-range) filters on.
     * ProcDate is what every production metric buckets by, so it is the only
     * correct bound for a "from 2025 till date" backfill.
     */
    protected function dateColumn(): ?string
    {
        return 'ProcDate';
    }

    /**
     * Order records by the primary key during the initial sync.
     */
    protected function orderBy(): string
    {
        return 'ProcNum';
    }

    /**
     * Pull every column.
     */
    protected function select(): string
    {
        return '*';
    }

    /**
     * Patients whose procedures this batch rewrites, read before the write so a procedure
     * moved to another patient also refreshes the patient it left.
     *
     * @return list<int>
     */
    protected function beforePersist(int $officeId, array $rows): array
    {
        $procNums = array_column($rows, 'ProcNum');

        $previous = DB::table('od_procedure_logs')
            ->where('office_id', $officeId)
            ->whereIn('ProcNum', $procNums)
            ->distinct()
            ->pluck('PatNum')
            ->all();

        return array_merge($previous, array_column($rows, 'PatNum'));
    }

    /** Keep the patient_visit_days summary in step with the rows just written. */
    protected function afterPersist(int $officeId, array $rows, mixed $context): void
    {
        app(PatientVisitDays::class)->refreshPatients($officeId, (array) $context);
    }
}
