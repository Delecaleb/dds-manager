<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpisController extends Controller
{
    public function index()
    {
        return view('kpis.index');
    }

    public function hygiene(Request $request)
    {
        $start = $request->input('start_date', now()->startOfYear()->toDateString());
        $end   = $request->input('end_date',   now()->toDateString());
        return response()->json($this->hygieneKpis($start, $end));
    }

    public function doctor(Request $request)
    {
        $start = $request->input('start_date', now()->startOfYear()->toDateString());
        $end   = $request->input('end_date',   now()->toDateString());
        return response()->json($this->doctorKpis($start, $end));
    }

    public function office(Request $request)
    {
        $start = $request->input('start_date', now()->startOfYear()->toDateString());
        $end   = $request->input('end_date',   now()->toDateString());
        return response()->json($this->officeKpis($start, $end));
    }

    // ─── Hygiene ─────────────────────────────────────────────────────────────

    private function hygieneKpis(string $start, string $end): array
    {
        // ① One scan for all per-procedure aggregates (replaces ~10 separate queries)
        $s = DB::selectOne("
            SELECT
                COALESCE(SUM(pl.ProcFee), 0)                                                           AS total_prod,
                COUNT(*)                                                                                AS total_procs,
                COUNT(DISTINCT pl.ProcDate)                                                             AS work_days,
                COUNT(DISTINCT CONCAT(pl.PatNum,'-',pl.ProcDate))                                      AS visits,
                COUNT(DISTINCT CASE WHEN pc.ProcCode IN ('D4341','D4342','D4355','D4346','D4910')
                                    THEN CONCAT(pl.PatNum,'-',pl.ProcDate) END)                        AS perio_visits,
                SUM(pc.ProcCode IN ('D1203','D1204','D1206','D1208','D1209'))                           AS fluoride_count,
                SUM(pc.ProcCode IN ('D4341','D4342'))                                                  AS srp_count,
                SUM(pc.ProcCode IN ('D0210','D0330'))                                                  AS fmx_count,
                SUM(pc.ProcCode IN ('D1351','D1352'))                                                  AS sealants,
                SUM(pc.ProcCode IN ('D9975','D9976','D9972','D9973','D9974'))                          AS whitening,
                SUM(pc.ProcCode IN ('D4381'))                                                          AS antimicrobial
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            WHERE pc.IsHygiene = 'true'
              AND pl.ProcStatus = 'C'
              AND pl.ProcDate BETWEEN ? AND ?
        ", [$start, $end]);

        $hygProd  = (float) $s->total_prod;
        $hygCount = (int)   $s->total_procs;
        $workDays = (int)   $s->work_days;
        $hygVisits = (int)  $s->visits;

        // ② All-status count for case-acceptance denominator (separate — different ProcStatus filter)
        $hygTotalAll = (int) DB::selectOne("
            SELECT COUNT(*) AS cnt
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            WHERE pc.IsHygiene = 'true' AND pl.ProcDate BETWEEN ? AND ?
        ", [$start, $end])->cnt;

        // ③ Reappointment rate — single JOIN, no PHP-side array
        $rapt = DB::selectOne("
            SELECT
                COUNT(DISTINCT a.AptNum) AS total,
                COUNT(DISTINCT CASE WHEN a.NextAptNum IS NOT NULL AND a.NextAptNum != '0'
                                    THEN a.AptNum END)              AS with_next
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            JOIN od_appointments a ON pl.AptNum = a.AptNum
            WHERE pc.IsHygiene = 'true'
              AND pl.ProcStatus = 'C'
              AND pl.ProcDate BETWEEN ? AND ?
              AND pl.AptNum IS NOT NULL AND pl.AptNum != '0'
        ", [$start, $end]);
        $reapptRate = $rapt->total > 0 ? round($rapt->with_next / $rapt->total * 100, 2) : 0;

        // ④ Perio reappointment — same pattern
        $prapt = DB::selectOne("
            SELECT
                COUNT(DISTINCT a.AptNum) AS total,
                COUNT(DISTINCT CASE WHEN a.NextAptNum IS NOT NULL AND a.NextAptNum != '0'
                                    THEN a.AptNum END)              AS with_next
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            JOIN od_appointments a ON pl.AptNum = a.AptNum
            WHERE pc.ProcCode IN ('D4341','D4342','D4346','D4910')
              AND pl.ProcStatus = 'C'
              AND pl.ProcDate BETWEEN ? AND ?
              AND pl.AptNum IS NOT NULL AND pl.AptNum != '0'
        ", [$start, $end]);
        $perioReapptRate = $prapt->total > 0 ? round($prapt->with_next / $prapt->total * 100, 2) : 0;

        // ⑤ Active adults + children — single query
        $ageCounts = DB::selectOne("
            SELECT
                COUNT(CASE WHEN TIMESTAMPDIFF(YEAR, Birthdate, CURDATE()) >= 18 THEN 1 END) AS adults,
                COUNT(CASE WHEN TIMESTAMPDIFF(YEAR, Birthdate, CURDATE()) <  18 THEN 1 END) AS children
            FROM od_patients WHERE PatStatus = 'Patient'
        ");
        $activeAdults   = (int) $ageCounts->adults;
        $activeChildren = (int) $ageCounts->children;

        // ⑥ Retention — 2 queries instead of 4 (each returns adult + child in one pass)
        $ret12 = DB::selectOne("
            SELECT
                COUNT(DISTINCT CASE WHEN TIMESTAMPDIFF(YEAR, pt.Birthdate, CURDATE()) >= 18
                                    THEN pl.PatNum END) AS adult,
                COUNT(DISTINCT CASE WHEN TIMESTAMPDIFF(YEAR, pt.Birthdate, CURDATE()) <  18
                                    THEN pl.PatNum END) AS child
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            JOIN od_patients pt   ON pl.PatNum  = pt.PatNum
            WHERE pc.IsHygiene = 'true' AND pl.ProcStatus = 'C'
              AND pt.PatStatus = 'Patient'
              AND pl.ProcDate >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        ");
        $ret6 = DB::selectOne("
            SELECT
                COUNT(DISTINCT CASE WHEN TIMESTAMPDIFF(YEAR, pt.Birthdate, CURDATE()) >= 18
                                    THEN pl.PatNum END) AS adult,
                COUNT(DISTINCT CASE WHEN TIMESTAMPDIFF(YEAR, pt.Birthdate, CURDATE()) <  18
                                    THEN pl.PatNum END) AS child
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            JOIN od_patients pt   ON pl.PatNum  = pt.PatNum
            WHERE pc.IsHygiene = 'true' AND pl.ProcStatus = 'C'
              AND pt.PatStatus = 'Patient'
              AND pl.ProcDate >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        ");

        // ⑦ Visits with TX plan — JOIN instead of correlated EXISTS
        $visitsWithTx = (int) DB::selectOne("
            SELECT COUNT(DISTINCT CONCAT(pl.PatNum,'-',pl.ProcDate)) AS cnt
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            JOIN (
                SELECT DISTINCT PatNum
                FROM od_procedure_logs
                WHERE ProcStatus = 'TP' AND ProcDate BETWEEN ? AND ?
            ) tp ON pl.PatNum = tp.PatNum
            WHERE pc.IsHygiene = 'true' AND pl.ProcStatus = 'C'
              AND pl.ProcDate BETWEEN ? AND ?
        ", [$start, $end, $start, $end])->cnt;

        // ⑧ TX plans per day
        $txPlanCount = (int) DB::selectOne("
            SELECT COUNT(DISTINCT CONCAT(PatNum,'-',DateTP)) AS cnt
            FROM od_procedure_logs
            WHERE ProcStatus = 'TP'
              AND DateTP IS NOT NULL AND DateTP != '0000-00-00'
              AND DateTP BETWEEN ? AND ?
        ", [$start, $end])->cnt;

        // ⑨ Avg prod per provider per day (must stay as grouped query)
        $hygProviders = DB::select("
            SELECT pl.ProvNum,
                   SUM(pl.ProcFee)          AS prod,
                   COUNT(DISTINCT pl.ProcDate) AS days
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            WHERE pc.IsHygiene = 'true' AND pl.ProcStatus = 'C'
              AND pl.ProcDate BETWEEN ? AND ?
            GROUP BY pl.ProvNum
        ", [$start, $end]);

        $avgProvProdPerDay = 0;
        if (count($hygProviders) > 0) {
            $sum = array_sum(array_map(fn ($p) => $p->days > 0 ? $p->prod / $p->days : 0, $hygProviders));
            $avgProvProdPerDay = round($sum / count($hygProviders), 2);
        }

        // ⑩ Avg prod per hour via appointment pattern
        $totalMins = (float) (DB::selectOne("
            SELECT COALESCE(SUM(LENGTH(a.Pattern) * 5), 0) AS mins
            FROM od_procedure_logs pl
            JOIN od_procedures pc    ON pl.CodeNum = pc.CodeNum
            JOIN od_appointments a   ON pl.AptNum  = a.AptNum
            WHERE pc.IsHygiene = 'true' AND pl.ProcStatus = 'C'
              AND pl.ProcDate BETWEEN ? AND ?
              AND pl.AptNum IS NOT NULL AND pl.AptNum != '0'
              AND a.Pattern IS NOT NULL AND a.Pattern != ''
        ", [$start, $end])->mins ?? 0);

        return [
            'perio_pct'             => $hygVisits > 0 ? round($s->perio_visits / $hygVisits * 100, 2) : 0,
            'fluoride_per_day'      => $workDays > 0 ? round($s->fluoride_count / $workDays, 2) : 0,
            'avg_prod_per_day'      => $workDays > 0 ? round($hygProd / $workDays, 2) : 0,
            'avg_prod_per_prov_day' => $avgProvProdPerDay,
            'prod_per_visit'        => $hygVisits > 0 ? round($hygProd / $hygVisits, 2) : 0,
            'fmx_per_day'           => $workDays > 0 ? round($s->fmx_count / $workDays, 2) : 0,
            'srp_per_day'           => $workDays > 0 ? round($s->srp_count / $workDays, 2) : 0,
            'visits_per_day'        => $workDays > 0 ? round($hygVisits / $workDays, 2) : 0,
            'reappt'                => $reapptRate,
            'perio_reappt'          => $perioReapptRate,
            'adult_retention_12m'   => $activeAdults > 0 ? round($ret12->adult / $activeAdults * 100, 2) : 0,
            'adult_retention_6m'    => $activeAdults > 0 ? round($ret6->adult  / $activeAdults * 100, 2) : 0,
            'child_retention_12m'   => $activeChildren > 0 ? round($ret12->child / $activeChildren * 100, 2) : 0,
            'child_retention_6m'    => $activeChildren > 0 ? round($ret6->child  / $activeChildren * 100, 2) : 0,
            'sealants'              => (int) $s->sealants,
            'whitening'             => (int) $s->whitening,
            'antimicrobial'         => (int) $s->antimicrobial,
            'prod_per_proc'         => $hygCount > 0 ? round($hygProd / $hygCount, 2) : 0,
            'visits_with_tx_pct'    => $hygVisits > 0 ? round($visitsWithTx / $hygVisits * 100, 2) : 0,
            'tx_plans_per_day'      => $workDays > 0 ? round($txPlanCount / $workDays, 2) : 0,
            'avg_prod_per_hour'     => $totalMins > 0 ? round($hygProd / ($totalMins / 60), 2) : 0,
            'case_acceptance'       => $hygTotalAll > 0 ? round($hygCount / $hygTotalAll * 100, 2) : 0,
        ];
    }

    // ─── Doctor ──────────────────────────────────────────────────────────────

    private function doctorKpis(string $start, string $end): array
    {
        $docBase = fn () => DB::table('od_procedure_logs as pl')
            ->join('od_procedures as pc', 'pl.CodeNum', '=', 'pc.CodeNum')
            ->where('pc.IsHygiene', 'false')
            ->where('pl.ProcStatus', 'C')
            ->whereBetween('pl.ProcDate', [$start, $end]);

        $docProd  = $docBase()->sum('pl.ProcFee');
        $docCount = $docBase()->count();
        $workDays = $docBase()->distinct('pl.ProcDate')->count('pl.ProcDate');

        // Completed doctor appointments
        $docApts = DB::table('od_appointments')
            ->where('IsHygiene', 'false')
            ->where('AptStatus', 'Complete')
            ->whereBetween(DB::raw('DATE(AptDateTime)'), [$start, $end]);

        $docAptCount = (clone $docApts)->count();
        $avgAptMins  = (float) ((clone $docApts)
            ->whereNotNull('Pattern')->where('Pattern', '!=', '')
            ->selectRaw('AVG(LENGTH(Pattern) * 5) as v')->value('v') ?? 0);
        $totalHours  = (float) ((clone $docApts)
            ->whereNotNull('Pattern')->where('Pattern', '!=', '')
            ->selectRaw('SUM(LENGTH(Pattern) * 5) / 60 as v')->value('v') ?? 0);

        // Avg prod per provider per day
        $docProviders = $docBase()
            ->select('pl.ProvNum',
                DB::raw('SUM(pl.ProcFee) as prod'),
                DB::raw('COUNT(DISTINCT pl.ProcDate) as days'))
            ->groupBy('pl.ProvNum')->get();

        $avgProvProdPerDay = 0;
        if ($docProviders->count() > 0) {
            $sum = $docProviders->sum(fn ($p) => $p->days > 0 ? $p->prod / $p->days : 0);
            $avgProvProdPerDay = round($sum / $docProviders->count(), 2);
        }

        // Reappointment — single JOIN, no PHP-side array
        $drapt = DB::selectOne("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN NextAptNum IS NOT NULL AND NextAptNum != '0' THEN 1 ELSE 0 END) AS with_next
            FROM od_appointments
            WHERE IsHygiene = 'false' AND AptStatus = 'Complete'
              AND DATE(AptDateTime) BETWEEN ? AND ?
        ", [$start, $end]);
        $docReapptRate = $drapt->total > 0 ? round($drapt->with_next / $drapt->total * 100, 2) : 0;

        // Exam production (evaluation codes)
        $examCount = $this->countByCodes(
            ['D0120','D0140','D0150','D0160','D0170','D0180'], $start, $end
        );

        // Case acceptance
        $docTotalAll = DB::table('od_procedure_logs as pl')
            ->join('od_procedures as pc', 'pl.CodeNum', '=', 'pc.CodeNum')
            ->where('pc.IsHygiene', 'false')
            ->whereBetween('pl.ProcDate', [$start, $end])->count();

        return [
            'case_acceptance_same_day'  => 0,
            'case_acceptance_rate'      => $docTotalAll > 0 ? round($docCount / $docTotalAll * 100, 2) : 0,
            'new_pt_tx_dollars'         => 0,
            'existing_pt_tx_dollars'    => 0,
            'avg_apt_time_mins'         => round($avgAptMins, 2),
            'avg_prod_per_hour'         => $totalHours > 0 ? round($docProd / $totalHours, 2) : 0,
            'avg_prod_per_apt'          => $docAptCount > 0 ? round($docProd / $docAptCount, 2) : 0,
            'same_day_tx_per_new_pt'    => 0,
            'avg_prod_per_prov_day'     => $avgProvProdPerDay,
            'avg_tx_per_existing_pt'    => 0,
            'avg_tx_per_new_pt'         => 0,
            'pct_new_pt_with_tx'        => 0,
            'pct_existing_pt_with_tx'   => 0,
            'reappt'                    => $docReapptRate,
            'prod_per_exam'             => $examCount > 0 ? round($docProd / $examCount, 2) : 0,
            'total_production'          => round($docProd, 2),
        ];
    }

    // ─── Office ──────────────────────────────────────────────────────────────

    private function officeKpis(string $start, string $end): array
    {
        $cutoff18m = now()->subMonths(18)->toDateString();
        $today     = now()->toDateString();

        // ① Active patient count + appointment totals in one pass each
        $activePatients = (int) DB::selectOne(
            "SELECT COUNT(*) AS cnt FROM od_patients WHERE PatStatus = 'Patient'"
        )->cnt;

        $aptStats = DB::selectOne("
            SELECT
                COUNT(*) AS total,
                SUM(AptStatus = 'Broken') AS broken
            FROM od_appointments
            WHERE DATE(AptDateTime) BETWEEN ? AND ?
        ", [$start, $end]);

        // ② Reactivation — LEFT JOIN replaces correlated NOT EXISTS (O(n) vs O(n*m))
        $reactivation = (int) DB::selectOne("
            SELECT COUNT(*) AS cnt
            FROM od_patients p
            LEFT JOIN (
                SELECT DISTINCT PatNum
                FROM od_procedure_logs
                WHERE ProcStatus = 'C' AND ProcDate >= ?
            ) recent ON p.PatNum = recent.PatNum
            WHERE p.PatStatus = 'Patient' AND recent.PatNum IS NULL
        ", [$cutoff18m])->cnt;

        // ③ Attrition — two LEFT JOINs replace two correlated NOT EXISTS
        $attrition = (int) DB::selectOne("
            SELECT COUNT(*) AS cnt
            FROM od_patients p
            LEFT JOIN (
                SELECT DISTINCT PatNum
                FROM od_procedure_logs
                WHERE ProcStatus = 'C' AND ProcDate BETWEEN ? AND ?
            ) visited ON p.PatNum = visited.PatNum
            LEFT JOIN (
                SELECT DISTINCT PatNum
                FROM od_appointments
                WHERE AptStatus = 'Scheduled' AND DATE(AptDateTime) >= ?
            ) future ON p.PatNum = future.PatNum
            WHERE p.PatStatus = 'Patient'
              AND visited.PatNum IS NULL
              AND future.PatNum IS NULL
        ", [$start, $end, $today])->cnt;

        // ④ New patients + growth in one query
        $newAndLapsed = DB::selectOne("
            SELECT
                (SELECT COUNT(*) FROM (
                    SELECT PatNum, MIN(ProcDate) AS first_date
                    FROM od_procedure_logs WHERE ProcStatus = 'C'
                    GROUP BY PatNum
                    HAVING first_date BETWEEN ? AND ?
                ) fp) AS new_patients,
                (SELECT COUNT(*) FROM od_patients
                 WHERE PatStatus = 'Inactive'
                   AND updated_at BETWEEN ? AND ?) AS lapsed
        ", [$start, $end, $start . ' 00:00:00', $end . ' 23:59:59']);
        $newPatients   = (int) $newAndLapsed->new_patients;
        $patientGrowth = $newPatients - (int) $newAndLapsed->lapsed;

        // ⑤ Work-days + TX plan count in one query
        $workStats = DB::selectOne("
            SELECT
                (SELECT COUNT(DISTINCT ProcDate)
                 FROM od_procedure_logs
                 WHERE ProcStatus = 'C' AND ProcDate BETWEEN ? AND ?) AS work_days,
                (SELECT COUNT(DISTINCT CONCAT(PatNum,'-',DateTP))
                 FROM od_procedure_logs
                 WHERE ProcStatus = 'TP'
                   AND DateTP IS NOT NULL AND DateTP != '0000-00-00'
                   AND DateTP BETWEEN ? AND ?)  AS tx_plan_count
        ", [$start, $end, $start, $end]);
        $workDays    = (int) $workStats->work_days;
        $txPlanCount = (int) $workStats->tx_plan_count;

        // ⑥ Co-pay: two simple SUM queries (different tables — can't combine)
        $totalCharged  = (float) DB::selectOne(
            "SELECT COALESCE(SUM(ProcFee),0) AS v FROM od_procedure_logs WHERE ProcStatus='C' AND ProcDate BETWEEN ? AND ?",
            [$start, $end]
        )->v;
        $totalPaid = (float) DB::selectOne(
            "SELECT COALESCE(SUM(SplitAmt),0) AS v FROM od_pay_splits WHERE DatePay BETWEEN ? AND ?",
            [$start, $end]
        )->v;

        // ⑦ Unscheduled TX snapshot + FMX new-patient % in one query
        $snapshotStats = DB::selectOne("
            SELECT
                (SELECT COALESCE(SUM(ProcFee),0) FROM od_procedure_logs WHERE ProcStatus='TP') AS unscheduled_tx,
                (SELECT COUNT(DISTINCT pl2.PatNum)
                 FROM od_procedure_logs pl2
                 JOIN od_procedures pc2 ON pl2.CodeNum = pc2.CodeNum
                 JOIN (
                     SELECT PatNum, MIN(ProcDate) AS first_date
                     FROM od_procedure_logs WHERE ProcStatus='C' GROUP BY PatNum
                 ) fv ON pl2.PatNum = fv.PatNum AND pl2.ProcDate = fv.first_date
                 WHERE pc2.ProcCode IN ('D0210','D0330')
                   AND pl2.ProcStatus='C'
                   AND pl2.ProcDate BETWEEN ? AND ?) AS fmx_new_pts
        ", [$start, $end]);

        // ⑧ Retention (seen in last 18 months)
        $seenRecently = (int) DB::selectOne(
            "SELECT COUNT(DISTINCT PatNum) AS cnt FROM od_procedure_logs WHERE ProcStatus='C' AND ProcDate >= ?",
            [$cutoff18m]
        )->cnt;

        // ⑨ In recare — JOIN replaces correlated WHERE EXISTS
        $inRecare = (int) DB::selectOne("
            SELECT COUNT(DISTINCT p.PatNum) AS cnt
            FROM od_patients p
            JOIN od_recalls r ON r.PatNum = p.PatNum
            WHERE p.PatStatus = 'Patient'
              AND r.IsDisabled = 0
              AND r.DateDue >= ?
        ", [$today])->cnt;

        $unscheduledTx = (float) $snapshotStats->unscheduled_tx;
        $fmxNewPts     = (int)   $snapshotStats->fmx_new_pts;
        $coPayPct      = $totalCharged > 0 ? round(min($totalPaid / $totalCharged * 100, 100), 2) : 0;

        return [
            'patient_retention'    => $activePatients > 0 ? round($seenRecently / $activePatients * 100, 2) : 0,
            'tx_plans_per_day'     => $workDays > 0 ? round($txPlanCount / $workDays, 2) : 0,
            'co_pay_collection'    => $coPayPct,
            'unscheduled_tx'       => round($unscheduledTx, 2),
            'new_pt_fmx_pct'       => $newPatients > 0 ? round($fmxNewPts / $newPatients * 100, 2) : 0,
            'no_show_rate'         => $aptStats->total > 0 ? round($aptStats->broken / $aptStats->total * 100, 2) : 0,
            'reactivation_list'    => $reactivation,
            'patient_attrition'    => $attrition,
            'patient_growth'       => $patientGrowth,
            'active_patients'      => $activePatients,
            'active_in_recare_pct' => $activePatients > 0 ? round($inRecare / $activePatients * 100, 2) : 0,
        ];
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function countByCodes(array $codes, string $start, string $end): int
    {
        return DB::table('od_procedure_logs as pl')
            ->join('od_procedures as pc', 'pl.CodeNum', '=', 'pc.CodeNum')
            ->whereIn('pc.ProcCode', $codes)
            ->where('pl.ProcStatus', 'C')
            ->whereBetween('pl.ProcDate', [$start, $end])
            ->count();
    }

}
