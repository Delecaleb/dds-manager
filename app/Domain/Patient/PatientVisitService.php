<?php

namespace App\Domain\Patient;

use App\Domain\Support\MetricFilter;
use App\Domain\Support\ProcCode;
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
        $excludedCodes = ProcCode::brokenAppointmentCodeNums($officeId);

        // Step 1: Find candidate patients with completed procedures in range
        $candidateQ = DB::table('od_procedure_logs')
            ->where('office_id', $officeId)
            ->whereIn('ProcStatus', ProcStatus::completed())
            ->whereBetween('ProcDate', [$start, $end]);

        if (! empty($excludedCodes)) {
            $candidateQ->whereNotIn('CodeNum', $excludedCodes);
        }
        if (! empty($clinics)) {
            $candidateQ->whereIn('ClinicNum', $clinics);
        }
        if (! empty($providers)) {
            $candidateQ->whereIn('ProvNum', $providers);
        }

        $candidates = $candidateQ->groupBy('PatNum')
            ->selectRaw('PatNum, MIN(ProcDate) AS first_date')
            ->get();

        if ($candidates->isEmpty()) {
            return [];
        }

        $candidatePatNums = $candidates->pluck('PatNum')->all();
        $patFirstDateMap = $candidates->pluck('first_date', 'PatNum')->all();

        // Step 2: Eliminate patients who had ANY completed procedure prior to start
        $priorPatQ = DB::table('od_procedure_logs')
            ->where('office_id', $officeId)
            ->whereIn('PatNum', $candidatePatNums)
            ->whereIn('ProcStatus', ProcStatus::completed())
            ->where('ProcDate', '<', $start);

        if (! empty($excludedCodes)) {
            $priorPatQ->whereNotIn('CodeNum', $excludedCodes);
        }

        $priorPatNums = $priorPatQ->distinct()->pluck('PatNum')->all();
        $priorSet = array_fill_keys($priorPatNums, true);

        $newPatNums = array_values(array_filter($candidatePatNums, fn ($pn) => ! isset($priorSet[$pn])));
        if (empty($newPatNums)) {
            return [];
        }

        // Step 3: Check Filter 1 & Filter 2 on appointments
        $appts = DB::table('od_appointments')
            ->where('office_id', $officeId)
            ->whereIn('PatNum', $newPatNums)
            ->get(['PatNum', 'AptStatus', 'AptDateTime', 'IsNewPatient']);

        $apptsByPat = [];
        foreach ($appts as $a) {
            $apptsByPat[$a->PatNum][] = $a;
        }

        $disqualified = [];
        foreach ($newPatNums as $pn) {
            $firstDate = $patFirstDateMap[$pn];
            $patAppts = $apptsByPat[$pn] ?? [];

            $firstDateMidnight = substr($firstDate, 0, 10).' 00:00:00';
            $firstDateEnd = substr($firstDate, 0, 10).' 23:59:59';

            $hasPriorCompleted = false;
            $hasCurrNonNew = false;
            $hasOldAppt = false;

            foreach ($patAppts as $a) {
                $status = (string) $a->AptStatus;
                $isCompleted = in_array($status, ['2', 'Complete', 'Completed'], true);
                if ($isCompleted && $a->AptDateTime < $firstDateMidnight) {
                    $hasPriorCompleted = true;
                    break;
                }

                if ($a->AptDateTime >= $firstDateMidnight && $a->AptDateTime <= $firstDateEnd && ($a->IsNewPatient == 0 || $a->IsNewPatient === '0')) {
                    $hasCurrNonNew = true;
                }

                if ($a->AptDateTime < ($start.' 00:00:00')) {
                    $hasOldAppt = true;
                }
            }

            if ($hasPriorCompleted || ($hasCurrNonNew && $hasOldAppt)) {
                $disqualified[$pn] = true;
            }
        }

        $finalPatNums = array_values(array_filter($newPatNums, fn ($pn) => ! isset($disqualified[$pn])));
        if (empty($finalPatNums)) {
            return [];
        }

        // Step 4: Fetch first-visit procedures, fees, and patient names
        $procsQ = DB::table('od_procedure_logs as pl')
            ->leftJoin('od_procedures as pc', function ($j) {
                $j->on('pl.CodeNum', '=', 'pc.CodeNum')
                    ->on('pl.office_id', '=', 'pc.office_id');
            })
            ->leftJoin('od_patients as p', function ($j) {
                $j->on('pl.PatNum', '=', 'p.PatNum')
                    ->on('pl.office_id', '=', 'p.office_id');
            })
            ->where('pl.office_id', $officeId)
            ->whereIn('pl.PatNum', $finalPatNums)
            ->whereIn('pl.ProcStatus', ProcStatus::completed());

        if (! empty($excludedCodes)) {
            $procsQ->whereNotIn('pl.CodeNum', $excludedCodes);
        }

        $procsQ->where(function ($q) {
            $q->whereNotIn('pc.ProcCode', ['D9986', 'D9987'])
                ->orWhereNull('pc.ProcCode');
        });

        $procRows = $procsQ->select([
            'pl.PatNum',
            'pl.ProcDate',
            'pl.ProcFee',
            'pl.ClinicNum',
            'pl.ProvNum',
            'pc.ProcCode',
            'p.LName',
            'p.FName',
        ])->get();

        $grouped = [];
        foreach ($procRows as $r) {
            $firstDate = $patFirstDateMap[$r->PatNum] ?? null;
            if ($firstDate === null || substr($r->ProcDate, 0, 10) !== substr($firstDate, 0, 10)) {
                continue;
            }

            $pn = $r->PatNum;
            if (! isset($grouped[$pn])) {
                $grouped[$pn] = [
                    'patient_id' => $pn,
                    'patient_name' => trim(($r->LName ?? '').', '.($r->FName ?? ''), ', '),
                    'dates' => substr($firstDate, 0, 10),
                    'service_codes_arr' => [],
                    'amount' => 0.0,
                    'clinic_num' => (int) ($r->ClinicNum ?? 0),
                    'prov_num' => $r->ProvNum ?? null,
                ];
            }

            if (! empty($r->ProcCode) && ! in_array($r->ProcCode, $grouped[$pn]['service_codes_arr'], true)) {
                $grouped[$pn]['service_codes_arr'][] = $r->ProcCode;
            }
            $grouped[$pn]['amount'] += (float) $r->ProcFee;
            if ($r->ClinicNum !== null) {
                $grouped[$pn]['clinic_num'] = (int) $r->ClinicNum;
            }
            if ($r->ProvNum !== null) {
                $grouped[$pn]['prov_num'] = $r->ProvNum;
            }
        }

        $out = [];
        foreach ($grouped as $g) {
            sort($g['service_codes_arr']);
            $out[] = [
                'patient_id' => $g['patient_id'],
                'office_id' => (int) $officeId,
                'patient_name' => $g['patient_name'],
                'dates' => $g['dates'],
                'service_codes' => implode(', ', $g['service_codes_arr']),
                'amount' => round($g['amount'], 2),
                'clinic_num' => $g['clinic_num'],
                'prov_num' => $g['prov_num'],
            ];
        }

        usort($out, function ($a, $b) {
            return ($a['dates'] <=> $b['dates']) ?: ($a['patient_name'] <=> $b['patient_name']);
        });

        return $out;
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

        $excludedCodes = ProcCode::brokenAppointmentCodeNums($officeId);

        $q = DB::table('od_procedure_logs as pl')
            ->where('pl.office_id', $officeId)
            ->whereIn('pl.ProcStatus', ProcStatus::completed())
            ->when(! empty($excludedCodes), fn ($q) => $q->whereNotIn('pl.CodeNum', $excludedCodes))
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

        $excludedCodes = ProcCode::brokenAppointmentCodeNums($officeId);
        $codeFilter = ! empty($excludedCodes) ? 'AND pl.CodeNum NOT IN ('.implode(',', array_map('intval', $excludedCodes)).')' : '';

        $rows = DB::select("
            SELECT
                p.PatNum                         AS patient_id,
                pl.office_id                     AS office_id,
                {$nameExpr}                      AS patient_name,
                {$dateConcat}                    AS dates,
                COUNT(DISTINCT DATE(pl.ProcDate)) AS count
            FROM od_procedure_logs pl
            JOIN od_patients p ON pl.PatNum = p.PatNum AND p.office_id = ?
            WHERE pl.office_id = ?
              AND pl.ProcStatus IN ({$this->completedIn})
              {$codeFilter}
              AND pl.ProcDate BETWEEN ? AND ?
              {$clinicFilter}
              {$provFilter}
            GROUP BY p.PatNum, pl.office_id, p.LName, p.FName
            ORDER BY count DESC, p.LName
        ", [$officeId, $officeId, $start, $end]);

        return array_map(fn ($r) => [
            'patient_id' => $r->patient_id,
            'office_id' => (int) ($r->office_id ?? $officeId),
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
        $excludedCodes = ProcCode::brokenAppointmentCodeNums($officeId);

        $q = DB::table('od_procedure_logs as pl')
            ->where('pl.office_id', $officeId)
            ->whereIn('pl.ProcStatus', ProcStatus::completed())
            ->when(! empty($excludedCodes), fn ($q) => $q->whereNotIn('pl.CodeNum', $excludedCodes))
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
        $excludedCodes = ProcCode::brokenAppointmentCodeNums($officeId);

        $getStats = function ($s, $e) use ($officeId, $excludedCodes) {
            $patientVisits = DB::table('od_procedure_logs')
                ->where('office_id', $officeId)
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->when(! empty($excludedCodes), fn ($q) => $q->whereNotIn('CodeNum', $excludedCodes))
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
