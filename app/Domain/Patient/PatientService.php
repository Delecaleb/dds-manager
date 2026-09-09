<?php

namespace App\Domain\Patient;

use App\Domain\Support\MetricFilter;
use App\Domain\Support\ProcCode;
use App\Domain\Support\ProcStatus;
use App\Models\Office;
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
        private readonly ?PatientVisitService $patientVisitService = null
    ) {}

    /**
     * First-ever COMPLETED procedure date per patient — the one definition of the
     * "first visit" cohort that new/existing-patient logic joins against.
     *
     * Returns a query builder (columns: PatNum, first_date) for use in:
     *   ->joinSub($patients->firstVisitCohort(), 'fv', 'pl.PatNum', '=', 'fv.PatNum')
     */
    public function firstVisitCohort(?int $officeId = null): Builder
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $excludedCodes = ProcCode::brokenAppointmentCodeNums($officeId);

        return DB::table('od_procedure_logs')
            ->select('PatNum', DB::raw('MIN(ProcDate) AS first_date'))
            ->where('office_id', $officeId)
            ->whereIn('ProcStatus', ProcStatus::completed())
            ->whereNotIn(DB::raw("COALESCE(CodeNum, '')"), $excludedCodes)
            ->groupBy('PatNum');
    }

    /**
     * The first-visit cohort as a raw SQL string, for interpolation into heredoc queries
     * that can't take a query builder (e.g. KpisController's bundled single-scan KPIs).
     * Same definition as firstVisitCohort(); keeps the cohort single-sourced everywhere.
     *
     * @param  string  $dateAlias  column alias for the first-visit date (default 'first_date')
     */
    public function firstVisitCohortSql(string $dateAlias = 'first_date', ?int $officeId = null): string
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $completed = ProcStatus::inList(ProcStatus::completed());
        $notBroken = ProcCode::notBrokenAppointmentSql('', $officeId);

        return "SELECT PatNum, MIN(ProcDate) AS {$dateAlias} "
            ."FROM od_procedure_logs WHERE office_id = {$officeId} AND ProcStatus IN ({$completed}) AND {$notBroken} GROUP BY PatNum";
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
            ->whereNotIn(DB::raw("COALESCE(pl.CodeNum, '')"), $excludedCodes)
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
