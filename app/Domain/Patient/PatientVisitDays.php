<?php

namespace App\Domain\Patient;

use App\Domain\Support\ProcCode;
use App\Domain\Support\ProcStatus;
use Closure;
use Illuminate\Support\Facades\DB;

/**
 * Builds and maintains `patient_visit_days` (speed plan Phase 4): one row per
 * office × clinic × patient × provider × day with a completed procedure, flagged with the
 * procedure conditions the patient-history KPIs filter on.
 *
 * Rows are rebuilt per PATIENT, never per date: a procedure edit can move a row to another
 * day, and rebuilding the patient's whole history covers both the old and the new day.
 *
 * The flags reproduce the existing KPI queries exactly, including their inconsistencies
 * (see PatientActivityService). They are computed by the database with the same
 * expressions those queries use, so collation and type-juggling behave identically.
 *
 * NULL ClinicNum / ProvNum are stored as -1 (no real clinic or provider has that number),
 * so they never match a clinic filter, the same as `ClinicNum IN (...)` on the raw table.
 * Rows with NULL PatNum or ProcDate are left out: no KPI can count them.
 */
final class PatientVisitDays
{
    public const SUMMARY = 'patient_visit_days';

    public const STATUS_READY = 'ready';

    public const STATUS_STALE = 'stale';

    /**
     * Status list the legacy new-patient query uses (KpisController, retained as-is):
     * an int and two strings, so MySQL compares numerically against 2.
     */
    public const LEGACY_NEW_PATIENT_STATUSES = "2, '2', 'C'";

    public const FMX_CODES = ['D0210', '0210'];

    public const RECARE_CODES = ['D4910', 'D1110', 'D1120'];

    private const PATIENT_CHUNK = 1000;

    public function isReady(int $officeId): bool
    {
        return DB::table('summary_states')
            ->where('summary', self::SUMMARY)
            ->where('office_id', $officeId)
            ->value('status') === self::STATUS_READY;
    }

    /** Readers fall back to the raw tables until the next full rebuild. */
    public function markStale(int $officeId): void
    {
        $this->setState($officeId, self::STATUS_STALE);
    }

    /** Rebuild every row for one office in one transaction; readers keep seeing the old rows until commit. */
    public function rebuildOffice(int $officeId): int
    {
        // A full rebuild usually follows a procedure-code change, so drop the per-process
        // broken-code lookup first. Resolved before the transaction so it is not part of it.
        ProcCode::clearCache();
        $sql = $this->insertSql($officeId);

        $inserted = DB::transaction(function () use ($officeId, $sql): int {
            DB::table('patient_visit_days')->where('office_id', $officeId)->delete();

            return DB::affectingStatement($sql, [$officeId]);
        });

        $this->setState($officeId, self::STATUS_READY, built: true);

        return $inserted;
    }

    /**
     * Rebuild the rows of specific patients (after a sync batch touched their procedures).
     * Does nothing for an office that has never been fully built.
     *
     * @param  array<int|string>  $patNums
     */
    public function refreshPatients(int $officeId, array $patNums): void
    {
        $patNums = array_values(array_unique(array_map('intval', array_filter($patNums, fn ($p) => $p !== null && $p !== ''))));

        if ($patNums === [] || ! $this->hasBeenBuilt($officeId)) {
            return;
        }

        foreach (array_chunk($patNums, self::PATIENT_CHUNK) as $chunk) {
            $placeholders = implode(',', array_fill(0, count($chunk), '?'));
            $sql = $this->insertSql($officeId, " AND pl.PatNum IN ({$placeholders})");

            DB::transaction(function () use ($officeId, $chunk, $sql): void {
                DB::table('patient_visit_days')
                    ->where('office_id', $officeId)
                    ->whereIn('pat_num', $chunk)
                    ->delete();

                DB::affectingStatement($sql, [$officeId, ...$chunk]);
            });
        }
    }

    /**
     * Run a delete of local rows and, when they are procedure logs, refresh the patients
     * those rows belonged to. The one wrapper every pruning path uses.
     *
     * @param  array<int|string>  $keys  primary keys about to be deleted
     * @param  Closure(): int  $delete
     */
    public function deletingRows(string $table, int $officeId, string $primaryKey, array $keys, Closure $delete): int
    {
        if ($table !== 'od_procedure_logs') {
            return $delete();
        }

        $patNums = DB::table('od_procedure_logs')
            ->where('office_id', $officeId)
            ->whereIn($primaryKey, $keys)
            ->distinct()
            ->pluck('PatNum')
            ->all();

        $deleted = $delete();
        $this->refreshPatients($officeId, $patNums);

        return $deleted;
    }

    private function insertSql(int $officeId, string $extraWhere = ''): string
    {
        $completed = ProcStatus::inList(ProcStatus::completed());
        $legacyNp = self::LEGACY_NEW_PATIENT_STATUSES;
        $broken = ProcStatus::inList(ProcCode::BROKEN_APPOINTMENT_CODES);
        $brokenCodeNums = ProcCode::brokenAppointmentCodeNumsInList($officeId);
        $fmx = ProcStatus::inList(self::FMX_CODES);
        $recare = ProcStatus::inList(self::RECARE_CODES);
        $isCompleted = "pl.ProcStatus IN ({$completed})";
        $isCoded = "{$isCompleted} AND pc.office_id IS NOT NULL";
        // Same rule as PatientVisitService's raw queries: no broken CodeNums = no exclusion.
        $isCompletedNotBroken = ProcCode::brokenAppointmentCodeNums($officeId) === []
            ? $isCompleted
            : "{$isCompleted} AND pl.CodeNum NOT IN ({$brokenCodeNums})";

        return "
            INSERT INTO patient_visit_days
                (office_id, clinic_num, pat_num, prov_num, visit_date,
                 completed, completed_not_broken, coded, coded_not_broken, fmx, recare, np_any, np_not_broken)
            SELECT
                pl.office_id,
                COALESCE(pl.ClinicNum, -1),
                pl.PatNum,
                COALESCE(pl.ProvNum, -1),
                pl.ProcDate,
                MAX(CASE WHEN {$isCompleted} THEN 1 ELSE 0 END),
                MAX(CASE WHEN {$isCompletedNotBroken} THEN 1 ELSE 0 END),
                MAX(CASE WHEN {$isCoded} THEN 1 ELSE 0 END),
                MAX(CASE WHEN {$isCoded} AND pc.ProcCode NOT IN ({$broken}) THEN 1 ELSE 0 END),
                MAX(CASE WHEN {$isCoded} AND pc.ProcCode IN ({$fmx}) THEN 1 ELSE 0 END),
                MAX(CASE WHEN {$isCoded} AND pc.ProcCode IN ({$recare}) THEN 1 ELSE 0 END),
                MAX(CASE WHEN pl.ProcStatus IN ({$legacyNp}) THEN 1 ELSE 0 END),
                MAX(CASE WHEN pl.ProcStatus IN ({$legacyNp}) AND pl.CodeNum NOT IN ({$brokenCodeNums}) THEN 1 ELSE 0 END)
            FROM od_procedure_logs pl
            LEFT JOIN od_procedures pc ON pc.CodeNum = pl.CodeNum AND pc.office_id = pl.office_id
            WHERE pl.office_id = ?
              AND (pl.ProcStatus IN ({$completed}) OR pl.ProcStatus IN ({$legacyNp}))
              AND pl.PatNum IS NOT NULL
              AND pl.ProcDate IS NOT NULL{$extraWhere}
            GROUP BY pl.office_id, COALESCE(pl.ClinicNum, -1), pl.PatNum, COALESCE(pl.ProvNum, -1), pl.ProcDate
        ";
    }

    private function hasBeenBuilt(int $officeId): bool
    {
        return DB::table('summary_states')
            ->where('summary', self::SUMMARY)
            ->where('office_id', $officeId)
            ->whereNotNull('built_at')
            ->exists();
    }

    private function setState(int $officeId, string $status, bool $built = false): void
    {
        $values = ['status' => $status, 'updated_at' => now()];

        if ($built) {
            $values['built_at'] = now();
        }

        DB::table('summary_states')->updateOrInsert(
            ['summary' => self::SUMMARY, 'office_id' => $officeId],
            $values,
        );
    }
}
