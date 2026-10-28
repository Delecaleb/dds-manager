<?php

namespace App\Domain\Patient;

use App\Domain\Support\MetricFilter;
use App\Domain\Support\ProcCode;
use App\Domain\Support\ProcStatus;
use Illuminate\Support\Facades\DB;

/**
 * Single source for the patient-history counts on the Office KPI page.
 *
 * Two implementations that must agree to the patient (`php artisan summaries:parity`):
 *  - fromSummary(): reads `patient_visit_days` — used whenever the office's summary is ready;
 *  - fromRaw(): the original full-history queries over od_procedure_logs, moved here
 *    unchanged from KpisController. The fallback, and the reference for parity.
 *
 * The definitions are retained exactly as they ran before (product decision 2026-10-27),
 * including these known inconsistencies:
 *  - active/retention exclude broken appointments by ProcCode (D9986/D9987), new patients
 *    by the office's broken-appointment CodeNums;
 *  - new patients match ProcStatus (2, '2', 'C') instead of ProcStatus::completed();
 *  - (fixed 2026-10-27) the recare sub-query used ProcStatus = 'C' only, which synced data
 *    never contains (statuses are numeric), so inRecare was always 0; it now uses completed();
 *  - (fixed 2026-10-27) the reactivation query read the first patient group's count, so it
 *    was 1 or 0; it now counts every patient last seen before 12 months prior to the start.
 */
class PatientActivityService
{
    public function __construct(
        private readonly PatientVisitDays $visitDays,
    ) {}

    public function officeActivity(MetricFilter $filter): OfficePatientActivity
    {
        return $this->visitDays->isReady($filter->officeId)
            ? $this->fromSummary($filter)
            : $this->fromRaw($filter);
    }

    public function fromSummary(MetricFilter $filter): OfficePatientActivity
    {
        $officeId = $filter->officeId;
        $start = $filter->start;
        $end = $filter->end;
        $clinic = $this->clinicScope($filter, 'v.clinic_num');
        [$cutoff36m, $cutoff18m, $cutoff12m, $prior12m, $prior18m] = $this->cutoffs($filter);

        $retention = DB::selectOne("
            SELECT
                COUNT(DISTINCT CASE WHEN v.visit_date >= ? THEN v.pat_num END) AS active_18m,
                COUNT(DISTINCT CASE WHEN v.visit_date BETWEEN ? AND ? THEN v.pat_num END) AS active_36m_prior
            FROM patient_visit_days v
            WHERE v.office_id = ? AND v.coded_not_broken = 1{$clinic}
        ", [$cutoff18m, $cutoff36m, $cutoff18m, $officeId]);

        // Same choice the raw query makes: no broken CodeNums for the office = no exclusion.
        $npFlag = ProcCode::brokenAppointmentCodeNums($officeId) === [] ? 'np_any' : 'np_not_broken';
        $newPatients18m = (int) (DB::selectOne("
            SELECT COUNT(*) AS cnt FROM (
                SELECT v.pat_num
                FROM patient_visit_days v
                WHERE v.office_id = ? AND v.{$npFlag} = 1{$clinic}
                GROUP BY v.pat_num
                HAVING MIN(v.visit_date) >= ?
            ) x
        ", [$officeId, $cutoff18m])->cnt ?? 0);

        $stats = DB::selectOne("
            SELECT
                COUNT(DISTINCT CASE WHEN first_visit BETWEEN ? AND ? THEN x.pat_num END) AS new_pts,
                COUNT(DISTINCT CASE WHEN first_visit BETWEEN ? AND ? AND has_fmx = 1 THEN x.pat_num END) AS new_pts_fmx,
                COUNT(DISTINCT CASE WHEN last_visit_before >= ? AND last_visit_before < ? AND seen_during = 0 THEN x.pat_num END) AS attrition
            FROM (
                SELECT
                    v.pat_num,
                    MIN(v.visit_date) AS first_visit,
                    MAX(CASE WHEN v.visit_date < ? THEN v.visit_date ELSE NULL END) AS last_visit_before,
                    MAX(CASE WHEN v.visit_date BETWEEN ? AND ? THEN 1 ELSE 0 END) AS seen_during,
                    MAX(CASE WHEN v.visit_date BETWEEN ? AND ? AND v.fmx = 1 THEN 1 ELSE 0 END) AS has_fmx
                FROM patient_visit_days v
                WHERE v.office_id = ? AND v.coded = 1{$clinic}
                GROUP BY v.pat_num
            ) x
        ", [$start, $end, $start, $end, $prior18m, $start, $start, $start, $end, $start, $end, $officeId]);

        $reactivationList = (int) (DB::selectOne("
            SELECT COUNT(*) AS cnt FROM (
                SELECT v.pat_num
                FROM patient_visit_days v
                WHERE v.office_id = ? AND v.completed = 1 AND v.visit_date < ?{$clinic}
                GROUP BY v.pat_num
                HAVING MAX(v.visit_date) < ?
            ) x
        ", [$officeId, $start, $prior12m])->cnt ?? 0);

        $inRecare = (int) (DB::selectOne("
            SELECT COUNT(DISTINCT v.pat_num) AS cnt
            FROM patient_visit_days v
            WHERE v.office_id = ? AND v.recare = 1 AND v.visit_date >= ?{$clinic}
              AND v.pat_num IN (
                  SELECT s.pat_num FROM patient_visit_days s
                  WHERE s.office_id = ? AND s.completed = 1 AND s.visit_date >= ?{$this->clinicScope($filter, 's.clinic_num')}
              )
        ", [$officeId, $cutoff12m, $officeId, $cutoff18m])->cnt ?? 0);

        return new OfficePatientActivity(
            active18m: (int) ($retention->active_18m ?? 0),
            active36mPrior: (int) ($retention->active_36m_prior ?? 0),
            newPatients18m: $newPatients18m,
            newPatients: (int) ($stats->new_pts ?? 0),
            newPatientsWithFmx: (int) ($stats->new_pts_fmx ?? 0),
            attrition: (int) ($stats->attrition ?? 0),
            reactivationList: $reactivationList,
            inRecare: $inRecare,
        );
    }

    /** The original queries from KpisController::officeKpiSet() (recare status fixed 2026-10-27). */
    public function fromRaw(MetricFilter $filter): OfficePatientActivity
    {
        $officeId = $filter->officeId;
        $start = $filter->start;
        $end = $filter->end;
        $clinic = $this->clinicScope($filter, 'pl.ClinicNum');
        $completedIn = ProcStatus::inList(ProcStatus::completed());
        [$cutoff36m, $cutoff18m, $cutoff12m, $prior12m, $prior18m] = $this->cutoffs($filter);

        $retentionData = DB::selectOne("
            SELECT
                COUNT(DISTINCT CASE WHEN pl.ProcDate >= ? AND pc.ProcCode NOT IN ('D9986', 'D9987') THEN pl.PatNum END) AS active_18m,
                COUNT(DISTINCT CASE WHEN pl.ProcDate BETWEEN ? AND ? AND pc.ProcCode NOT IN ('D9986', 'D9987') THEN pl.PatNum END) AS active_36m_prior
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            WHERE pl.office_id = ?
              AND pl.ProcStatus IN ({$completedIn}){$clinic}
        ", [$cutoff18m, $cutoff36m, $cutoff18m, $officeId, $officeId]);

        $excludedCodes = ProcCode::brokenAppointmentCodeNums($officeId);
        $newPatientsCount = DB::table('od_procedure_logs as pl')
            ->where('pl.office_id', $officeId)
            ->whereIn('pl.ProcStatus', [2, '2', 'C'])
            ->when($filter->clinics !== [], fn ($q) => $q->whereIn('pl.ClinicNum', $filter->clinics))
            ->when(! empty($excludedCodes), fn ($q) => $q->whereNotIn('pl.CodeNum', $excludedCodes))
            ->selectRaw('pl.PatNum, MIN(pl.ProcDate) as first_date')
            ->groupBy('pl.PatNum')
            ->havingRaw('MIN(pl.ProcDate) >= ?', [$cutoff18m.' 00:00:00'])
            ->pluck('PatNum')
            ->count();

        $patientStats = DB::selectOne("
            SELECT
                COUNT(DISTINCT CASE WHEN first_visit BETWEEN ? AND ? THEN x.PatNum END) AS new_pts,
                COUNT(DISTINCT CASE WHEN first_visit BETWEEN ? AND ? AND has_fmx = 1 THEN x.PatNum END) AS new_pts_fmx,
                COUNT(DISTINCT CASE WHEN last_visit_before >= ? AND last_visit_before < ? AND seen_during = 0 THEN x.PatNum END) AS attrition
            FROM (
                SELECT
                    pl.PatNum,
                    MIN(pl.ProcDate) AS first_visit,
                    MAX(CASE WHEN pl.ProcDate < ? THEN pl.ProcDate ELSE NULL END) AS last_visit_before,
                    MAX(CASE WHEN pl.ProcDate BETWEEN ? AND ? THEN 1 ELSE 0 END) AS seen_during,
                    MAX(CASE WHEN pl.ProcDate BETWEEN ? AND ? AND pc.ProcCode IN ('D0210','0210') THEN 1 ELSE 0 END) AS has_fmx
                FROM od_procedure_logs pl
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
                WHERE pl.office_id = ?
                  AND pl.ProcStatus IN ({$completedIn}){$clinic}
                GROUP BY pl.PatNum
            ) x
        ", [
            $start, $end,        // new_pts
            $start, $end,        // new_pts_fmx
            $prior18m, $start,   // attrition
            $start,              // last_visit_before
            $start, $end,        // seen_during
            $start, $end,        // has_fmx
            $officeId,
            $officeId,
        ]);

        $reactivationList = (int) (DB::selectOne("
            SELECT COUNT(*) AS cnt FROM (
                SELECT pl.PatNum
                FROM od_procedure_logs pl
                WHERE pl.office_id = ? AND pl.ProcStatus IN ({$completedIn}) AND pl.ProcDate < ?{$clinic}
                GROUP BY pl.PatNum
                HAVING MAX(pl.ProcDate) < ?
            ) x
        ", [$officeId, $start, $prior12m])->cnt ?? 0);

        $inRecare = (int) (DB::selectOne("
            SELECT COUNT(DISTINCT pl.PatNum) AS cnt
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            WHERE pl.office_id = ?
              AND pl.ProcStatus IN ({$completedIn})
              AND pl.ProcDate >= ?
              AND pc.ProcCode IN ('D4910','D1110','D1120'){$clinic}
              AND pl.PatNum IN (
                  SELECT DISTINCT p2.PatNum FROM od_procedure_logs p2 WHERE p2.office_id = ? AND p2.ProcStatus IN ({$completedIn}) AND p2.ProcDate >= ?{$this->clinicScope($filter, 'p2.ClinicNum')}
              )
        ", [$officeId, $officeId, $cutoff12m, $officeId, $cutoff18m])->cnt ?? 0);

        return new OfficePatientActivity(
            active18m: (int) ($retentionData->active_18m ?? 0),
            active36mPrior: (int) ($retentionData->active_36m_prior ?? 0),
            newPatients18m: $newPatientsCount,
            newPatients: (int) ($patientStats->new_pts ?? 0),
            newPatientsWithFmx: (int) ($patientStats->new_pts_fmx ?? 0),
            attrition: (int) ($patientStats->attrition ?? 0),
            reactivationList: $reactivationList,
            inRecare: $inRecare,
        );
    }

    /**
     * Rolling cutoffs (from today) and period-relative ones (from the start date).
     *
     * @return array{0: string, 1: string, 2: string, 3: string, 4: string}
     */
    private function cutoffs(MetricFilter $filter): array
    {
        return [
            now()->subMonths(36)->toDateString(),
            now()->subMonths(18)->toDateString(),
            now()->subMonths(12)->toDateString(),
            date('Y-m-d', strtotime('-12 months', strtotime($filter->start))),
            date('Y-m-d', strtotime('-18 months', strtotime($filter->start))),
        ];
    }

    /** ClinicNum predicate, or '' when the filter covers the whole office. */
    private function clinicScope(MetricFilter $filter, string $column): string
    {
        if ($filter->clinics === []) {
            return '';
        }

        return " AND {$column} IN (".implode(',', array_map('intval', $filter->clinics)).')';
    }
}
