<?php

namespace App\Domain\Patient;

use App\Domain\Support\MetricFilter;
use App\Domain\Support\ProcStatus;
use App\Helpers\MetricDefinitions;
use App\Models\Office;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PatientVisitService
{
    private readonly string $completedIn;

    public function __construct()
    {
        $this->completedIn = ProcStatus::inList(ProcStatus::completed());
    }

    /**
     * Get detailed list of New Patient Visits in the date range.
     *
     * Rules (matching verified JarvisAnalytics logic):
     * 1. Cohort: Identifies patients whose first-ever completed clinical procedure date falls within [$start, $end].
     * 2. Exclude Prior Completed Visits: Excludes patients who already completed an appointment prior to this visit date.
     * 3. Exclude Returning Patients: Excludes patients whose appointment on the visit date was flagged as an existing patient
     *    (IsNewPatient = 0) AND who already had prior appointments before the current date range.
     * 4. First-Visit Scoping: Aggregates service codes and production completed ON that exact first visit date.
     *
     * @return array<int, array{patient_id: string|int, patient_name: string, dates: string, service_codes: string, amount: float, clinic_num: string|int|null, prov_num: string|int|null}>
     */
    public function newPatientVisits(string $start, string $end, array $clinics = [], array $providers = [], ?int $officeId = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $groupConcat = $isSqlite
            ? 'GROUP_CONCAT(DISTINCT pc.ProcCode)'
            : "GROUP_CONCAT(DISTINCT pc.ProcCode ORDER BY pc.ProcCode SEPARATOR ', ')";
        $nameExpr = $isSqlite
            ? "COALESCE(p.LName || ', ' || p.FName, '')"
            : "COALESCE(CONCAT(p.LName, ', ', p.FName), '')";

        $clinicFilter = ! empty($clinics) ? 'AND pl_inner.ClinicNum IN ('.implode(',', array_map('intval', $clinics)).')' : '';
        $provFilter = ! empty($providers) ? 'AND pl_inner.ProvNum IN ('.implode(',', array_map('intval', $providers)).')' : '';

        $rows = DB::select("
            SELECT
                fv.PatNum                                                              AS patient_id,
                {$nameExpr}                                                            AS patient_name,
                fv.first_date                                                           AS dates,
                {$groupConcat}                                                         AS service_codes,
                COALESCE(SUM(pl.ProcFee), 0)                                           AS amount,
                MAX(pl.ClinicNum)                                                      AS clinic_num,
                MAX(pl.ProvNum)                                                        AS prov_num
            FROM (
                -- Identify the patient's first-ever completed visit date across history
                SELECT
                    pl_inner.PatNum,
                    MIN(pl_inner.ProcDate) AS first_date
                FROM od_procedure_logs pl_inner
                WHERE pl_inner.office_id = ?
                  AND pl_inner.ProcStatus IN ({$this->completedIn})
                  AND COALESCE(pl_inner.CodeNum, '') != '626'
                  AND pl_inner.ProcDate BETWEEN ? AND ?
                  {$clinicFilter}
                  {$provFilter}
                  AND NOT EXISTS (
                      SELECT 1 FROM od_procedure_logs pl_prior
                      WHERE pl_prior.office_id = ?
                        AND pl_prior.PatNum = pl_inner.PatNum
                        AND pl_prior.ProcStatus IN ({$this->completedIn})
                        AND COALESCE(pl_prior.CodeNum, '') != '626'
                        AND pl_prior.ProcDate < ?
                  )
                GROUP BY pl_inner.PatNum
            ) fv
            LEFT JOIN od_patients p ON fv.PatNum = p.PatNum AND p.office_id = ?
            -- Join only procedures completed on that specific first visit date
            JOIN od_procedure_logs pl ON fv.PatNum = pl.PatNum
                AND pl.office_id = ?
                AND pl.ProcDate = fv.first_date
                AND pl.ProcStatus IN ({$this->completedIn})
                AND COALESCE(pl.CodeNum, '') != '626'
            LEFT JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            -- Filter 1: Exclude patients who already had a completed appointment before this visit date
            WHERE NOT EXISTS (
                SELECT 1 FROM od_appointments a_prev
                WHERE a_prev.office_id = ?
                  AND a_prev.PatNum = fv.PatNum
                  AND a_prev.AptStatus IN (2, 'Complete', 'Completed')
                  AND a_prev.AptDateTime < ".($isSqlite ? 'fv.first_date' : "CONCAT(fv.first_date, ' 00:00:00')").'
            )
            -- Filter 2: Exclude returning patients whose visit was IsNewPatient = 0 AND who had appointments prior to this date range
            AND NOT (
                EXISTS (
                    SELECT 1 FROM od_appointments a_curr
                    WHERE a_curr.office_id = ?
                      AND a_curr.PatNum = fv.PatNum
                      AND a_curr.AptDateTime BETWEEN '.($isSqlite ? "fv.first_date AND fv.first_date || ' 23:59:59'" : "CONCAT(fv.first_date, ' 00:00:00') AND CONCAT(fv.first_date, ' 23:59:59')")."
                      AND (a_curr.IsNewPatient = 0 OR a_curr.IsNewPatient = '0')
                )
                AND EXISTS (
                    SELECT 1 FROM od_appointments a_old
                    WHERE a_old.office_id = ?
                      AND a_old.PatNum = fv.PatNum
                      AND a_old.AptDateTime < ?
                )
            )
            GROUP BY fv.PatNum, p.LName, p.FName, fv.first_date
            ORDER BY fv.first_date, p.LName
        ", [$officeId, $start, $end, $officeId, $start, $officeId, $officeId, $officeId, $officeId, $officeId, $start.' 00:00:00']);

        return array_map(fn ($r) => [
            'patient_id' => $r->patient_id,
            'patient_name' => $r->patient_name,
            'dates' => $r->dates,
            'service_codes' => $r->service_codes,
            'amount' => round((float) $r->amount, 2),
            'clinic_num' => (int) ($r->clinic_num ?? 0),
            'prov_num' => $r->prov_num ?? null,
        ], $rows);
    }

    /**
     * Get scalar count of New Patient Visits in the date range.
     */
    public function newPatientCount(string $start, string $end, array $clinics = [], array $providers = [], ?int $officeId = null): int
    {
        return count($this->newPatientVisits($start, $end, $clinics, $providers, $officeId));
    }

    /**
     * Get scalar count of distinct Patient Visits (patient x day) in the date range.
     */
    public function patientVisits(string|MetricFilter $start, ?string $end = null, array $clinics = [], array $providers = [], ?int $officeId = null): int
    {
        if ($start instanceof MetricFilter) {
            $filter = $start;
            $startDate = $filter->start;
            $endDate = $filter->end;
            $clinics = $filter->clinics;
            $providers = $filter->providers;
            $officeId = $filter->officeId;
        } else {
            $startDate = $start;
            $endDate = $end ?? $start;
            $officeId = $officeId ?? Office::getActiveOfficeId();
        }

        $q = DB::table('od_procedure_logs as pl')
            ->where('pl.office_id', $officeId)
            ->whereIn('pl.ProcStatus', ProcStatus::completed())
            ->whereRaw("COALESCE(pl.CodeNum, '') != '626'")
            ->whereBetween('pl.ProcDate', [$startDate, $endDate]);

        if (! empty($clinics)) {
            $q->whereIn('pl.ClinicNum', $clinics);
        }
        if (! empty($providers)) {
            $q->whereIn('pl.ProvNum', $providers);
        }

        $visitKey = DB::connection()->getDriverName() === 'sqlite'
            ? "pl.PatNum || '|' || DATE(pl.ProcDate)"
            : "CONCAT(pl.PatNum, '|', DATE(pl.ProcDate))";

        return (int) $q->distinct()->count(DB::raw($visitKey));
    }

    /**
     * Get breakdown list of Patient Visits (patient, dates, visit count).
     */
    public function patientVisitsBreakdown(string $start, string $end, array $clinics = [], array $providers = [], ?int $officeId = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $dateConcat = $isSqlite
            ? "GROUP_CONCAT(DISTINCT strftime('%Y-%m-%d', pl.ProcDate))"
            : "GROUP_CONCAT(DISTINCT DATE_FORMAT(pl.ProcDate, '%Y-%m-%d') ORDER BY pl.ProcDate SEPARATOR ', ')";
        $nameExpr = $isSqlite
            ? "COALESCE(p.LName || ', ' || p.FName, '')"
            : "COALESCE(CONCAT(p.LName, ', ', p.FName), '')";

        $clinicFilter = ! empty($clinics) ? 'AND pl.ClinicNum IN ('.implode(',', array_map('intval', $clinics)).')' : '';
        $provFilter = ! empty($providers) ? 'AND pl.ProvNum IN ('.implode(',', array_map('intval', $providers)).')' : '';

        $rows = DB::select("
            SELECT
                p.PatNum                         AS patient_id,
                {$nameExpr}                      AS patient_name,
                {$dateConcat}                    AS dates,
                COUNT(DISTINCT DATE(pl.ProcDate)) AS count
            FROM od_procedure_logs pl
            JOIN od_patients p ON pl.PatNum = p.PatNum AND p.office_id = ?
            WHERE pl.office_id = ?
              AND pl.ProcStatus IN ({$this->completedIn})
              AND COALESCE(pl.CodeNum, '') != '626'
              AND pl.ProcDate BETWEEN ? AND ?
              {$clinicFilter}
              {$provFilter}
            GROUP BY p.PatNum, p.LName, p.FName
            ORDER BY count DESC, p.LName
        ", [$officeId, $officeId, $start, $end]);

        return array_map(fn ($r) => [
            'patient_id' => $r->patient_id,
            'patient_name' => $r->patient_name,
            'dates' => $r->dates,
            'count' => (int) $r->count,
        ], $rows);
    }

    /**
     * Get daily patient statistics mapped by date.
     *
     * @return array{daily_visits: Collection<string, int>, daily_new_visits: Collection<string, int>}
     */
    public function dailyStats(string $start, string $end, array $clinics = [], array $providers = [], ?int $officeId = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();

        $q = DB::table('od_procedure_logs as pl')
            ->where('pl.office_id', $officeId)
            ->whereIn('pl.ProcStatus', ProcStatus::completed())
            ->whereRaw("COALESCE(pl.CodeNum, '') != '626'")
            ->whereBetween('pl.ProcDate', [$start, $end]);

        if (! empty($clinics)) {
            $q->whereIn('pl.ClinicNum', $clinics);
        }
        if (! empty($providers)) {
            $q->whereIn('pl.ProvNum', $providers);
        }

        $dailyVisits = $q->selectRaw('DATE(pl.ProcDate) as date, COUNT(DISTINCT pl.PatNum) as cnt')
            ->groupByRaw('DATE(pl.ProcDate)')
            ->pluck('cnt', 'date');

        $dailyNewVisits = collect($this->newPatientVisits($start, $end, $clinics, $providers, $officeId))
            ->groupBy('dates')
            ->map(fn ($group) => $group->count());

        return [
            'daily_visits' => $dailyVisits,
            'daily_new_visits' => $dailyNewVisits,
        ];
    }

    /**
     * Get patient visits and new patient visits grouped by ClinicNum for location cards.
     */
    public function visitsPerLocation(string $start, string $end, array $clinicNames = [], ?int $officeId = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $startLastYear = Carbon::parse($start)->subYear()->toDateString();
        $endLastYear = Carbon::parse($end)->subYear()->toDateString();

        $getStats = function ($s, $e) use ($officeId) {
            $patientVisits = DB::table('od_procedure_logs')
                ->where('office_id', $officeId)
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereRaw("COALESCE(CodeNum, '') != '626'")
                ->whereBetween('ProcDate', [$s, $e])
                ->selectRaw('COALESCE(ClinicNum + 0, 0) as ClinicNum, '.MetricDefinitions::patientVisits('val'))
                ->groupBy(DB::raw('COALESCE(ClinicNum + 0, 0)'))
                ->pluck('val', 'ClinicNum')
                ->mapWithKeys(fn ($val, $k) => [(int) $k => (int) $val]);

            $newVisits = collect($this->newPatientVisits($s, $e, [], [], $officeId))
                ->groupBy(fn ($item) => (int) ($item['clinic_num'] ?? 0))
                ->map(fn ($g) => $g->count());

            return compact('patientVisits', 'newVisits');
        };

        $currentStats = $getStats($start, $end);
        $lastYearStats = $getStats($startLastYear, $endLastYear);

        $allClinicNums = collect(array_keys($clinicNames))
            ->merge($currentStats['patientVisits']->keys())
            ->merge($currentStats['newVisits']->keys())
            ->merge($lastYearStats['patientVisits']->keys())
            ->merge($lastYearStats['newVisits']->keys())
            ->map(fn ($k) => (int) $k)
            ->unique()
            ->sort()
            ->values();

        $result = [];
        foreach ($allClinicNums as $cNum) {
            $result[] = [
                'clinic_num' => (int) $cNum,
                'location' => $clinicNames[(int) $cNum] ?? 'Location '.$cNum,
                'patient_visits' => (int) $currentStats['patientVisits']->get((int) $cNum, 0),
                'patient_visits_last' => (int) $lastYearStats['patientVisits']->get((int) $cNum, 0),
                'new_patient_visits' => (int) $currentStats['newVisits']->get((int) $cNum, 0),
                'new_patient_visits_last' => (int) $lastYearStats['newVisits']->get((int) $cNum, 0),
            ];
        }

        return $result;
    }
}
