<?php

namespace App\Domain\Patient;

use App\Domain\Support\MetricFilter;
use App\Domain\Support\ProcCode;
use App\Domain\Support\ProcStatus;
use App\Models\Office;
use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for patient cohorts and counts.
 *
 * The "first visit" cohort (first-ever COMPLETED procedure per patient) was duplicated
 * ~19 times across the app — 10 in OperationsAnalyticsService alone. It now lives here.
 *
 * @see refractor-blueprint/04-module-map.md
 * @see refractor-blueprint/03-canonical-definitions.md  (D8 — new patient = first completed
 *      procedure in the period)
 */
class PatientService
{
    public function __construct(
        private readonly ?PatientVisitService $patientVisitService = null,
        private readonly ?PatientVisitDays $visitDays = null,
    ) {}

    /**
     * First-ever COMPLETED procedure date per patient — the one definition of the
     * "first visit" cohort that new/existing-patient logic joins against.
     *
     * Returns a query builder (columns: PatNum, first_date) for use in:
     *   ->joinSub($patients->firstVisitCohort(), 'fv', 'pl.PatNum', '=', 'fv.PatNum')
     *
     * Reads patient_visit_days (flag completed_not_broken: completed, CodeNum not a
     * broken-appointment code) once the office's summary is built — one row per patient-day
     * instead of every procedure in the office's history. Same rows (summaries:parity).
     */
    public function firstVisitCohort(?int $officeId = null, int|string|array|null $clinicId = null): Builder
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $clinics = $this->cohortClinics($clinicId);

        if ($this->summaryReady($officeId)) {
            return DB::table('patient_visit_days')
                ->select('pat_num AS PatNum', DB::raw('MIN(visit_date) AS first_date'))
                ->where('office_id', $officeId)
                ->where('completed_not_broken', 1)
                ->when($clinics !== [], fn ($q) => $q->whereIn('clinic_num', $clinics))
                ->groupBy('pat_num');
        }

        $excludedCodes = ProcCode::brokenAppointmentCodeNums($officeId);

        $q = DB::table('od_procedure_logs')
            ->select('PatNum', DB::raw('MIN(ProcDate) AS first_date'))
            ->where('office_id', $officeId)
            ->whereIn('ProcStatus', ProcStatus::completed())
            ->when(! empty($excludedCodes), fn ($q) => $q->whereNotIn('CodeNum', $excludedCodes));

        if ($clinicId !== null && $clinicId !== 'all' && $clinicId !== '') {
            if (is_array($clinicId)) {
                $q->whereIn('ClinicNum', $clinicId);
            } else {
                $q->where('ClinicNum', (int) $clinicId);
            }
        }

        return $q->groupBy('PatNum');
    }

    /**
     * The first-visit cohort as a raw SQL string, for interpolation into heredoc queries
     * that can't take a query builder (e.g. KpisController's bundled single-scan KPIs).
     * Same definition as firstVisitCohort(); keeps the cohort single-sourced everywhere.
     *
     * The raw form keeps a procedure with a NULL CodeNum (COALESCE), the summary form drops it,
     * like firstVisitCohort(). Synced data has no NULL CodeNum (checked 2026-10-27), so both
     * give the same cohort; summaries:parity would show it if that ever changed.
     *
     * @param  string  $dateAlias  column alias for the first-visit date (default 'first_date')
     */
    public function firstVisitCohortSql(string $dateAlias = 'first_date', ?int $officeId = null, int|string|array|null $clinicId = null): string
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();

        if ($this->summaryReady($officeId)) {
            $clinics = $this->cohortClinics($clinicId);
            $clinicClause = $clinics === [] ? '' : ' AND clinic_num IN ('.implode(',', $clinics).')';

            return "SELECT pat_num AS PatNum, MIN(visit_date) AS {$dateAlias} "
                ."FROM patient_visit_days WHERE office_id = {$officeId} AND completed_not_broken = 1{$clinicClause} GROUP BY pat_num";
        }

        $completed = ProcStatus::inList(ProcStatus::completed());
        $notBroken = ProcCode::notBrokenAppointmentSql('', $officeId);

        $clinicClause = '';
        if ($clinicId !== null && $clinicId !== 'all' && $clinicId !== '') {
            if (is_array($clinicId)) {
                $escaped = implode(',', array_map('intval', $clinicId));
                $clinicClause = " AND ClinicNum IN ({$escaped})";
            } else {
                $clinicNumInt = (int) $clinicId;
                $clinicClause = " AND ClinicNum = {$clinicNumInt}";
            }
        }

        return "SELECT PatNum, MIN(ProcDate) AS {$dateAlias} "
            ."FROM od_procedure_logs WHERE office_id = {$officeId} AND ProcStatus IN ({$completed}) AND {$notBroken}{$clinicClause} GROUP BY PatNum";
    }

    private bool $rawOnly = false;

    /**
     * Run $compute with the cohort read from the raw tables only (the parity reference).
     *
     * @template T
     *
     * @param  Closure(self): T  $compute
     * @return T
     */
    public function onRawTables(Closure $compute): mixed
    {
        $this->rawOnly = true;

        try {
            return $compute($this);
        } finally {
            $this->rawOnly = false;
        }
    }

    private function summaryReady(int $officeId): bool
    {
        return ! $this->rawOnly && ($this->visitDays ?? app(PatientVisitDays::class))->isReady($officeId);
    }

    /** @return list<int> clinic filter of the cohort helpers ([] = whole office) */
    private function cohortClinics(int|string|array|null $clinicId): array
    {
        if ($clinicId === null || $clinicId === 'all' || $clinicId === '') {
            return [];
        }

        return array_values(array_map('intval', (array) $clinicId));
    }

    /** Patients seen (any completed procedure) in the period. */
    public function count(MetricFilter $filter): int
    {
        return (int) $this->completedInPeriod($filter)->distinct()->count('pl.PatNum');
    }

    /** New patients: those whose first-ever completed procedure falls within the period. */
    public function newPatientCount(MetricFilter $filter): int
    {
        if ($this->patientVisitService) {
            return $this->patientVisitService->newPatientCount($filter->start, $filter->end, $filter->clinics, $filter->providers, $filter->officeId);
        }

        return app(PatientVisitService::class)->newPatientCount($filter->start, $filter->end, $filter->clinics, $filter->providers, $filter->officeId);
    }

    /** Existing patients: seen in the period but whose first visit predates it. */
    public function existingPatientCount(MetricFilter $filter): int
    {
        return (int) $this->completedInPeriod($filter)
            ->joinSub($this->firstVisitCohort($filter->officeId), 'fv', 'pl.PatNum', '=', 'fv.PatNum')
            ->where('fv.first_date', '<', $filter->start)
            ->distinct()
            ->count('pl.PatNum');
    }

    /** Completed procedure-log rows for the filter (the base for patient counts). */
    private function completedInPeriod(MetricFilter $filter): Builder
    {
        $excludedCodes = ProcCode::brokenAppointmentCodeNums($filter->officeId);

        $q = DB::table('od_procedure_logs as pl')
            ->where('pl.office_id', $filter->officeId)
            ->whereIn('pl.ProcStatus', ProcStatus::completed())
            ->when(! empty($excludedCodes), fn ($q) => $q->whereNotIn('pl.CodeNum', $excludedCodes))
            ->whereBetween('pl.ProcDate', [$filter->start, $filter->end]);

        if ($filter->clinics) {
            $q->whereIn('pl.ClinicNum', $filter->clinics);
        }
        if ($filter->providers) {
            $q->whereIn('pl.ProvNum', $filter->providers);
        }

        return $q;
    }
}
