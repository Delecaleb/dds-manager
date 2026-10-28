<?php

namespace App\Domain\Scheduling;

use App\Domain\Support\MetricCache;
use Illuminate\Support\Facades\DB;

/**
 * Single source of "new patients scheduled": patients with a new-patient appointment
 * (scheduled or complete) in the period and, at that office, no earlier new-patient
 * appointment and no earlier completed procedure. Used by the Dashboard card
 * (OdAppointment::newPatientsScheduled) and the Financials breakdown.
 *
 * Two steps rather than one query with NOT EXISTS: MariaDB materialised both NOT EXISTS
 * sub-queries over the whole appointment and procedure tables (≈1 s per office on the
 * Dashboard, 4.8 s for all offices on Financials). Here the period's candidates come first,
 * then only their history is checked, through indexed lookups. Same rules as before.
 */
class NewPatientsScheduledService
{
    public function __construct(
        private readonly MetricCache $metricCache,
    ) {}

    /** Excluded test/duplicate patients, as in the original query. */
    private const EXCLUDED_PATNUMS = '21216, 21231, 21254';

    private const IS_NEW_PATIENT = "IsNewPatient IN (1, '1', true, 'true')";

    /**
     * One row per patient, sorted by last name like MySQL's ORDER BY LName (NULLs first,
     * case-insensitive).
     *
     * @param  array<int, int[]>  $scopes  officeId => ClinicNum[] ([] = whole office)
     * @return list<array{patient_id: mixed, office_id: int, patient_name: string, dates: string, count: int}>
     */
    public function patients(string $start, string $end, array $scopes): array
    {
        $startDay = substr($start, 0, 10);
        $rows = [];

        foreach ($scopes as $officeId => $clinics) {
            // Cached per office until its next sync: the Dashboard and Financials ask per office.
            $officeRows = $this->metricCache->remember(
                'patients.new-patients-scheduled', [(int) $officeId], [$startDay, substr($end, 0, 10), $clinics],
                fn () => $this->officeRows((int) $officeId, $clinics, $startDay, substr($end, 0, 10))
            );
            array_push($rows, ...$officeRows);
        }

        $sortKey = fn (array $r) => [$r['sort'] !== null, mb_strtolower((string) $r['sort'])];
        usort($rows, fn ($a, $b) => $sortKey($a) <=> $sortKey($b));

        return array_map(function (array $r) {
            unset($r['sort']);

            return $r;
        }, $rows);
    }

    /**
     * One office's rows (with the last name kept for sorting).
     *
     * @param  int[]  $clinics
     * @return list<array<string, mixed>>
     */
    private function officeRows(int $officeId, array $clinics, string $startDay, string $endDay): array
    {
        $rows = [];
        $candidates = DB::table('od_appointments as a')
            ->where('a.office_id', $officeId)
            ->when(! empty($clinics), fn ($q) => $q->whereIn('a.ClinicNum', $clinics))
            ->whereBetween('a.AptDateTime', [$startDay.' 00:00:00', $endDay.' 23:59:59'])
            ->whereRaw('a.AptStatus IN (1, 2)')
            ->whereRaw('a.'.self::IS_NEW_PATIENT)
            ->whereRaw('a.PatNum NOT IN ('.self::EXCLUDED_PATNUMS.')')
            ->groupBy('a.PatNum')
            ->selectRaw('a.PatNum, MIN(a.AptDateTime) AS first_apt')
            ->get();

        $excluded = $this->withEarlierHistory((int) $officeId, $candidates->pluck('PatNum')->all(), $startDay);
        $kept = $candidates->reject(fn ($r) => isset($excluded[$r->PatNum]));

        $names = DB::table('od_patients')
            ->where('office_id', $officeId)
            ->whereIn('PatNum', $kept->pluck('PatNum')->all())
            ->get(['PatNum', 'LName', 'FName'])
            ->keyBy('PatNum');

        foreach ($kept as $r) {
            $p = $names[$r->PatNum] ?? null;
            $rows[] = [
                'patient_id' => $r->PatNum,
                'office_id' => (int) $officeId,
                // CONCAT() is NULL when either part is NULL, so the name is then ''.
                'patient_name' => $p !== null && $p->LName !== null && $p->FName !== null ? $p->LName.', '.$p->FName : '',
                'dates' => substr((string) $r->first_apt, 0, 10),
                'count' => 1,
                'sort' => $p?->LName,
            ];
        }

        return $rows;
    }

    /** @param  int[]  $clinics */
    public function count(string $start, string $end, int $officeId, array $clinics = []): int
    {
        return count($this->patients($start, $end, [$officeId => $clinics]));
    }

    /**
     * Candidates with an earlier new-patient appointment or an earlier completed procedure.
     *
     * @param  list<int|string>  $patNums
     * @return array<int|string, true>
     */
    private function withEarlierHistory(int $officeId, array $patNums, string $startDay): array
    {
        $excluded = [];

        foreach (array_chunk($patNums, 1000) as $chunk) {
            $earlierAppointment = DB::table('od_appointments')
                ->where('office_id', $officeId)
                ->whereIn('PatNum', $chunk)
                ->whereRaw('AptStatus IN (1, 2)')
                ->whereRaw(self::IS_NEW_PATIENT)
                ->where('AptDateTime', '<', $startDay.' 00:00:00')
                ->distinct()
                ->pluck('PatNum');

            $earlierProcedure = DB::table('od_procedure_logs')
                ->where('office_id', $officeId)
                ->whereIn('PatNum', $chunk)
                ->where('ProcDate', '<', $startDay)
                ->whereRaw("ProcStatus IN ('C', '2', 'D')")
                ->distinct()
                ->pluck('PatNum');

            foreach ($earlierAppointment->concat($earlierProcedure) as $patNum) {
                $excluded[$patNum] = true;
            }
        }

        return $excluded;
    }
}
