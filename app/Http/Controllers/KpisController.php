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
        $end = $request->input('end_date', now()->toDateString());
        return response()->json($this->hygieneKpis($start, $end));
    }

    public function doctor(Request $request)
    {
        $start = $request->input('start_date', now()->startOfYear()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
        return response()->json($this->doctorKpis($start, $end));
    }

    public function office(Request $request)
    {
        $start = $request->input('start_date', now()->startOfYear()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
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
                COUNT(DISTINCT CASE WHEN pc.ProcCode IN ('D1206','D1208') 
                                    THEN CONCAT(pl.PatNum,'-',pl.ProcDate) END)                        AS fluoride_count,
                COUNT(DISTINCT CASE WHEN pc.ProcCode IN ('D4341','D4342') 
                                    THEN CONCAT(pl.PatNum,'-',pl.ProcDate) END)                        AS srp_count,
                COUNT(DISTINCT CASE WHEN pc.ProcCode IN ('D0210') 
                                    THEN CONCAT(pl.PatNum,'-',pl.ProcDate) END)                        AS fmx_count,
                SUM(pc.ProcCode IN ('D1351'))                                                          AS sealants,
                SUM(pc.ProcCode IN ('D9972','D9973','D9974','D9975'))                                  AS whitening,
                SUM(pc.ProcCode IN ('D4381'))                                                          AS antimicrobial
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            WHERE pc.IsHygiene = 'true'
              AND pl.ProcStatus = 'C'
              AND pl.ProcDate BETWEEN ? AND ?
        ", [$start, $end]);

        $hygProd = (float) $s->total_prod;
        $hygCount = (int) $s->total_procs;
        $workDays = (int) $s->work_days;
        $hygVisits = (int) $s->visits;

        // ② Case Acceptance mapping over $ totals
        $caRates = DB::selectOne("
            SELECT
                SUM(CASE WHEN pl.ProcStatus = 'TP' THEN pl.ProcFee ELSE 0 END) AS proposed,
                SUM(CASE WHEN pl.ProcStatus = 'C' THEN pl.ProcFee ELSE 0 END) AS completed,
                SUM(CASE WHEN pl.ProcStatus = 'TP' AND pl.AptNum IS NOT NULL AND pl.AptNum != '0' 
                         THEN pl.ProcFee ELSE 0 END) AS accepted
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            WHERE pc.IsHygiene = 'true' AND pl.ProcDate BETWEEN ? AND ?
        ", [$start, $end]);

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
        $activeAdults = (int) $ageCounts->adults;
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
              AND DateTP IS NOT NULL
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
            $sum = array_sum(array_map(fn($p) => $p->days > 0 ? $p->prod / $p->days : 0, $hygProviders));
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
            'perio_pct' => $hygVisits > 0 ? round($s->perio_visits / $hygVisits * 100, 2) : 0,
            'fluoride_per_day' => $workDays > 0 ? round($s->fluoride_count / $workDays, 2) : 0,
            'avg_prod_per_day' => $workDays > 0 ? round($hygProd / $workDays, 2) : 0,
            'avg_prod_per_prov_day' => $avgProvProdPerDay,
            'prod_per_visit' => $hygVisits > 0 ? round($hygProd / $hygVisits, 2) : 0,
            'fmx_per_day' => $workDays > 0 ? round($s->fmx_count / $workDays, 2) : 0,
            'srp_per_day' => $workDays > 0 ? round($s->srp_count / $workDays, 2) : 0,
            'visits_per_day' => $workDays > 0 ? round($hygVisits / $workDays, 2) : 0,
            'reappt' => $reapptRate,
            'perio_reappt' => $perioReapptRate,
            'adult_retention_12m' => $activeAdults > 0 ? round($ret12->adult / $activeAdults * 100, 2) : 0,
            'adult_retention_6m' => $activeAdults > 0 ? round($ret6->adult / $activeAdults * 100, 2) : 0,
            'child_retention_12m' => $activeChildren > 0 ? round($ret12->child / $activeChildren * 100, 2) : 0,
            'child_retention_6m' => $activeChildren > 0 ? round($ret6->child / $activeChildren * 100, 2) : 0,
            'sealants' => (int) $s->sealants,
            'whitening' => (int) $s->whitening,
            'antimicrobial' => (int) $s->antimicrobial,
            'prod_per_proc' => $hygCount > 0 ? round($hygProd / $hygCount, 2) : 0,
            'visits_with_tx_pct' => $hygVisits > 0 ? round($visitsWithTx / $hygVisits * 100, 2) : 0,
            'tx_plans_per_day' => $workDays > 0 ? round($txPlanCount / $workDays, 2) : 0,
            'avg_prod_per_hour' => $totalMins > 0 ? round($hygProd / ($totalMins / 60), 2) : 0,
            'case_acceptance' => $caRates->proposed > 0 ? round((($caRates->completed + $caRates->accepted) / $caRates->proposed) * 100, 2) : 0,
        ];
    }

    // ─── Doctor ──────────────────────────────────────────────────────────────

    private function doctorKpis(string $start, string $end): array
    {
        // Total Production & Counts
        $doc = DB::selectOne("
            SELECT
                COALESCE(SUM(pl.ProcFee), 0) AS total_prod,
                COUNT(*) AS total_procs,
                COUNT(DISTINCT pl.ProcDate) AS work_days,
                COUNT(DISTINCT CONCAT(pl.PatNum, '-', pl.ProcDate)) AS visits
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            WHERE pc.IsHygiene = 'false'
              AND pl.ProcStatus = 'C'
              AND pl.ProcDate BETWEEN ? AND ?
        ", [$start, $end]);

        $docProd = (float) $doc->total_prod;
        $workDays = (int) $doc->work_days;
        $docVisits = (int) $doc->visits;

        // Appointment Time & Count
        $apts = DB::selectOne("
            SELECT 
                COUNT(DISTINCT a.AptNum) AS total_apts,
                AVG(LENGTH(a.Pattern) * 5) AS avg_mins,
                SUM(LENGTH(a.Pattern) * 5) / 60 AS total_hours
            FROM od_appointments a
            JOIN od_procedure_logs pl ON a.AptNum = pl.AptNum
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            WHERE a.AptStatus = 'Complete'
              AND pc.IsHygiene = 'false'
              AND pl.ProcStatus = 'C'
              AND a.Pattern IS NOT NULL AND a.Pattern != ''
              AND DATE(a.AptDateTime) BETWEEN ? AND ?
        ", [$start, $end]);

        $docAptCount = (int) $apts->total_apts;
        $avgAptMins = (float) $apts->avg_mins;
        $totalHours = (float) $apts->total_hours;

        // Case Acceptance mapped over $ totals
        $caRates = DB::selectOne("
            SELECT
                SUM(CASE WHEN pl.ProcStatus = 'TP' THEN pl.ProcFee ELSE 0 END) AS proposed,
                SUM(CASE WHEN pl.ProcStatus = 'C' THEN pl.ProcFee ELSE 0 END) AS completed,
                SUM(CASE WHEN pl.ProcStatus = 'TP' AND pl.AptNum IS NOT NULL AND pl.AptNum != '0' 
                         THEN pl.ProcFee ELSE 0 END) AS accepted
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            WHERE pc.IsHygiene = 'false' 
              AND pl.ProcDate BETWEEN ? AND ?
        ", [$start, $end]);

        $docReappt = DB::selectOne("
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN NextAptNum IS NOT NULL AND NextAptNum != '0' THEN 1 ELSE 0 END) AS with_next
            FROM od_appointments a
            WHERE IsHygiene = 'false' 
              AND AptStatus = 'Complete'
              AND DATE(AptDateTime) BETWEEN ? AND ?
        ", [$start, $end]);

        $examCount = $this->countByCodes(
            ['D0120', 'D0140', 'D0150', 'D0160', 'D0170', 'D0180'],
            $start,
            $end
        );

        // Advanced New/Existing & SameDay aggregation matrix
        // (Since strict schema might not sync DateFirstVisit perfectly, we use MIN(ProcDate) across entire history)
        $txMatrix = DB::selectOne("
            SELECT
                COUNT(DISTINCT CASE WHEN DATEDIFF(pl.ProcDate, tp.DateTP) = 0 THEN CONCAT(pl.PatNum,'-',pl.ProcDate) END) AS same_day_completions,
                COALESCE(AVG(CASE WHEN pt_hist.first_visit BETWEEN ? AND ? THEN pl.ProcFee END), 0) AS avg_new_pt_tx,
                COALESCE(SUM(CASE WHEN pt_hist.first_visit < ? THEN pl.ProcFee END), 0) AS total_existing_pt_tx,
                COALESCE(AVG(CASE WHEN pt_hist.first_visit < ? AND pl.ProcStatus = 'TP' THEN pl.ProcFee END), 0) AS avg_tp_existing_pt,
                COALESCE(AVG(CASE WHEN pt_hist.first_visit BETWEEN ? AND ? AND pl.ProcStatus = 'TP' THEN pl.ProcFee END), 0) AS avg_tp_new_pt,
                COUNT(DISTINCT CASE WHEN pt_hist.first_visit BETWEEN ? AND ? AND pl.ProcStatus IN ('C','TP') THEN pl.PatNum END) AS new_pts_with_tx,
                COUNT(DISTINCT CASE WHEN pt_hist.first_visit < ? AND pl.ProcStatus IN ('C','TP') THEN pl.PatNum END) AS existing_pts_with_tx,
                COUNT(DISTINCT CASE WHEN pt_hist.first_visit BETWEEN ? AND ? THEN pt_hist.PatNum END) AS new_pts_total,
                COUNT(DISTINCT CASE WHEN pt_hist.first_visit < ? AND pl.ProcStatus = 'C' THEN pt_hist.PatNum END) AS existing_pts_total,
                COALESCE(AVG(CASE WHEN pt_hist.first_visit BETWEEN ? AND ? AND DATEDIFF(pl.ProcDate, tp.DateTP) = 0 AND pl.ProcStatus = 'C' THEN pl.ProcFee END), 0) AS avg_sameday_new_pt
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            JOIN (
                SELECT PatNum, MIN(ProcDate) AS first_visit 
                FROM od_procedure_logs 
                WHERE ProcStatus = 'C' 
                GROUP BY PatNum
            ) pt_hist ON pl.PatNum = pt_hist.PatNum
            LEFT JOIN od_procedure_logs tp ON tp.PatNum = pl.PatNum 
                  AND tp.ProcStatus = 'TP' 
                  AND tp.DateTP IS NOT NULL 
                  AND tp.DateTP BETWEEN ? AND ?
            WHERE pc.IsHygiene = 'false'
              AND pl.ProcDate BETWEEN ? AND ?
        ", [
            $start,
            $end,        // avg_new_pt_tx
            $start,              // total_existing_pt_tx
            $start,              // avg_tp_existing_pt
            $start,
            $end,        // avg_tp_new_pt
            $start,
            $end,        // new_pts_with_tx
            $start,              // existing_pts_with_tx
            $start,
            $end,        // new_pts_total
            $start,              // existing_pts_total
            $start,
            $end,        // avg_sameday_new_pt
            $start,
            $end,        // (params for LEFT JOIN ON TP Date bounds)
            $start,
            $end         // MAIN WHERE bounds
        ]);

        $avgProvProdPerDay = 0;
        $docProviders = DB::select("
            SELECT pl.ProvNum, SUM(pl.ProcFee) as prod, COUNT(DISTINCT pl.ProcDate) as days
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            WHERE pc.IsHygiene = 'false' AND pl.ProcStatus = 'C' AND pl.ProcDate BETWEEN ? AND ?
            GROUP BY pl.ProvNum
        ", [$start, $end]);
        if (count($docProviders) > 0) {
            $sum = array_sum(array_map(fn($p) => $p->days > 0 ? $p->prod / $p->days : 0, $docProviders));
            $avgProvProdPerDay = round($sum / count($docProviders), 2);
        }

        return [
            'case_acceptance_same_day' => $docVisits > 0 ? round(($txMatrix->same_day_completions / $docVisits) * 100, 2) : 0,
            'case_acceptance_rate' => $caRates->proposed > 0 ? round((($caRates->completed + $caRates->accepted) / $caRates->proposed) * 100, 2) : 0,
            'new_pt_tx_dollars' => round((float) $txMatrix->avg_tp_new_pt, 2),
            'existing_pt_tx_dollars' => round((float) $txMatrix->total_existing_pt_tx, 2),
            'avg_apt_time_mins' => round($avgAptMins, 2),
            'avg_prod_per_hour' => $totalHours > 0 ? round($docProd / $totalHours, 2) : 0,
            'avg_prod_per_apt' => $docAptCount > 0 ? round($docProd / $docAptCount, 2) : 0,
            'same_day_tx_per_new_pt' => round((float) $txMatrix->avg_sameday_new_pt, 2),
            'avg_prod_per_prov_day' => $avgProvProdPerDay,
            'avg_tx_per_existing_pt' => round((float) $txMatrix->avg_tp_existing_pt, 2),
            'avg_tx_per_new_pt' => round((float) $txMatrix->avg_tp_new_pt, 2),
            'pct_new_pt_with_tx' => $txMatrix->new_pts_total > 0 ? round(($txMatrix->new_pts_with_tx / $txMatrix->new_pts_total) * 100, 2) : 0,
            'pct_existing_pt_with_tx' => $txMatrix->existing_pts_total > 0 ? round(($txMatrix->existing_pts_with_tx / $txMatrix->existing_pts_total) * 100, 2) : 0,
            'reappt' => $docReappt->total > 0 ? round(($docReappt->with_next / $docReappt->total) * 100, 2) : 0,
            'prod_per_exam' => $examCount > 0 ? round($docProd / $examCount, 2) : 0,
            'total_production' => round($docProd, 2),
        ];
    }

    // ─── Office ──────────────────────────────────────────────────────────────

    private function officeKpis(string $start, string $end): array
    {
        $cutoff36m = now()->subMonths(36)->toDateString();
        $cutoff18m = now()->subMonths(18)->toDateString();
        $cutoff12m = now()->subMonths(12)->toDateString();
        $prior12m = date('Y-m-d', strtotime("-12 months", strtotime($start)));
        $prior18m = date('Y-m-d', strtotime("-18 months", strtotime($start)));

        // 1 & 10. Patient Retention + Active Patients
        // Active = Seen in last 36mo (or 18mo for #10).
        // Exam = in last 18mo.
        $retentionData = DB::selectOne("
            SELECT
                COUNT(DISTINCT CASE WHEN ProcDate >= ? THEN PatNum END) AS active_36m,
                COUNT(DISTINCT CASE WHEN ProcDate >= ? THEN PatNum END) AS active_18m,
                COUNT(DISTINCT CASE WHEN ProcDate >= ? AND pc.ProcCode IN ('D0120','D0140','D0150','D0160','D0170','D0180') THEN pl.PatNum END) AS exam_in_18m
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            WHERE pl.ProcStatus = 'C'
        ", [$cutoff36m, $cutoff18m, $cutoff18m]);

        $activePatients = (int) $retentionData->active_18m;
        $active36m = (int) $retentionData->active_36m;
        $patientRetention = $active36m > 0 ? round(($retentionData->exam_in_18m / $active36m) * 100, 2) : 0;

        // 2. Treatment Plans per Day
        $tpDays = DB::selectOne("
            SELECT
                (SELECT COUNT(DISTINCT CONCAT(PatNum, '-', DateTP)) FROM od_procedure_logs WHERE ProcStatus = 'TP' AND DateTP BETWEEN ? AND ? AND ProcFee > 10) AS tp_count,
                (SELECT COUNT(DISTINCT pl2.ProcDate) FROM od_procedure_logs pl2 WHERE pl2.ProcStatus = 'C' AND pl2.ProcDate BETWEEN ? AND ?) AS work_days
        ", [$start, $end, $start, $end]);
        $txPlansPerDay = $tpDays->work_days > 0 ? round($tpDays->tp_count / $tpDays->work_days, 2) : 0;

        // 3. Co-Pay Collection
        $coPay = DB::selectOne("
            SELECT
                SUM(ps.SplitAmt) AS collected,
                SUM(pl.ProcFee * 0.2) AS expected
            FROM od_procedure_logs pl
            JOIN od_pay_splits ps ON pl.ProcNum = ps.ProcNum
            WHERE pl.ProcStatus = 'C'
              AND pl.ProcDate BETWEEN ? AND ?
              AND ps.SplitAmt > 0
        ", [$start, $end]);
        $coPayCollection = $coPay->expected > 0 ? round(($coPay->collected / $coPay->expected) * 100, 2) : 0;

        // 4. Unscheduled Tx
        $unscheduled = (float) DB::selectOne("
            SELECT COALESCE(SUM(ProcFee), 0) AS val FROM od_procedure_logs
            WHERE ProcStatus = 'TP'
              AND ProcDate BETWEEN ? AND ?
              AND (AptNum IS NULL OR AptNum = 0 OR AptNum = '0')
              AND ProcFee > 0
        ", [$start, $end])->val;

        // 5 & 8 & 9. New Patients Fmx % // Attrition // Growth
        // (New pts => first_visit between start/end)
        $patientStats = DB::selectOne("
            SELECT
                COUNT(DISTINCT CASE WHEN first_visit BETWEEN ? AND ? THEN x.PatNum END) AS new_pts,
                COUNT(DISTINCT CASE WHEN first_visit BETWEEN ? AND ? AND has_fmx = 1 THEN x.PatNum END) AS new_pts_fmx,
                COUNT(DISTINCT CASE WHEN last_visit_before >= ? AND last_visit_before < ? AND seen_during = 0 THEN x.PatNum END) AS attrition,
                COUNT(DISTINCT CASE WHEN last_visit_before < ? AND last_visit_before >= ? AND seen_during = 0 THEN x.PatNum END) AS reactivations
            FROM (
                SELECT 
                    pl.PatNum,
                    MIN(pl.ProcDate) AS first_visit,
                    MAX(CASE WHEN pl.ProcDate < ? THEN pl.ProcDate ELSE NULL END) AS last_visit_before,
                    MAX(CASE WHEN pl.ProcDate BETWEEN ? AND ? THEN 1 ELSE 0 END) AS seen_during,
                    MAX(CASE WHEN pl.ProcDate BETWEEN ? AND ? AND pc.ProcCode = 'D0210' THEN 1 ELSE 0 END) AS has_fmx
                FROM od_procedure_logs pl
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
                WHERE pl.ProcStatus = 'C'
                GROUP BY pl.PatNum
            ) x
        ", [
            $start,
            $end,        // new_pts
            $start,
            $end,        // new_pts_fmx
            $prior18m,
            $start,   // attrition
            $start,
            $prior12m,   // reactivations (seen before, but not seen in 12m prior to start) NOTE: adjusted logic
            $start,              // last_visit_before
            $start,
            $end,        // seen_during
            $start,
            $end         // has_fmx
        ]);

        $newPtFmxPct = $patientStats->new_pts > 0 ? round(($patientStats->new_pts_fmx / $patientStats->new_pts) * 100, 2) : 0;
        $attrition = (int) $patientStats->attrition;
        $growth = (int) $patientStats->new_pts - $attrition;

        // Reactivations override: Not seen 12mo prior to start, but seen before
        $reactivationList = (int) DB::selectOne("
            SELECT COUNT(DISTINCT pl.PatNum) AS cnt
            FROM od_procedure_logs pl
            WHERE pl.ProcStatus = 'C' AND pl.ProcDate < ?
            GROUP BY pl.PatNum
            HAVING MAX(pl.ProcDate) < ?
        ", [$start, $prior12m])->cnt;

        // 6. No Show Rate
        $aptStats = DB::selectOne("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN AptStatus IN ('Broken', 'NoShow') THEN 1 ELSE 0 END) AS broken
            FROM od_appointments
            WHERE DATE(AptDateTime) BETWEEN ? AND ?
              AND AptStatus IN ('Scheduled', 'Complete', 'Cancelled', 'Broken', 'NoShow')
        ", [$start, $end]);
        $noShowRate = $aptStats->total > 0 ? round(($aptStats->broken / $aptStats->total) * 100, 2) : 0;

        // 11. Active In Recare
        $inRecare = (int) DB::selectOne("
            SELECT COUNT(DISTINCT pl.PatNum) AS cnt
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            WHERE pl.ProcStatus = 'C'
              AND pl.ProcDate >= ?
              AND pc.ProcCode IN ('D4910','D1110','D1120')
              AND pl.PatNum IN (
                  SELECT DISTINCT p2.PatNum FROM od_procedure_logs p2 WHERE p2.ProcStatus='C' AND p2.ProcDate >= ?
              )
        ", [$cutoff12m, $cutoff18m])->cnt;

        return [
            'patient_retention' => $patientRetention,
            'tx_plans_per_day' => $txPlansPerDay,
            'co_pay_collection' => $coPayCollection,
            'unscheduled_tx' => round($unscheduled, 2),
            'new_pt_fmx_pct' => $newPtFmxPct,
            'no_show_rate' => $noShowRate,
            'reactivation_list' => $reactivationList,
            'patient_attrition' => $attrition,
            'patient_growth' => $growth,
            'active_patients' => $activePatients,
            'active_in_recare_pct' => $activePatients > 0 ? round(($inRecare / $activePatients) * 100, 2) : 0,
        ];
    }

    // ─── Specialty Tabs ──────────────────────────────────────────────────────

    public function endo(Request $request)
    {
        $start = $request->input('start_date', date('Y-m-01'));
        $end = $request->input('end_date', date('Y-m-d'));

        return response()->json($this->getSpecialtyMetrics($start, $end, 2, [
            'total_consults' => ['D9310'],
            'retreats_count' => ['D3346', 'D3347', 'D3348'],
            'rct_count' => ['D3310', 'D3320', 'D3330'],
            'obstruction_count' => ['D3331'],
            'biopure_count' => ['D3000', 'D3999'],
        ]));
    }

    public function perio(Request $request)
    {
        $start = $request->input('start_date', date('Y-m-01'));
        $end = $request->input('end_date', date('Y-m-d'));
        // Perio Specialty = 8
        $data = $this->getSpecialtyMetrics($start, $end, 8, [
            'total_consults' => ['D9310'],
            'implant_placement_count' => ['D6010'],
        ], [
            'implant_placement_dollars' => ['D6010'],
            'sedations_dollars' => ['D9222', 'D9223', 'D9239', 'D9243', 'D9248'],
        ]);

        // Perio Codes $ (D4000-D4999) - slightly different filter
        $data['perio_codes_dollars'] = round((float) DB::selectOne("
            SELECT SUM(pl.ProcFee) AS sm
            FROM od_procedure_logs pl
            JOIN od_providers pr ON pl.ProvNum = pr.ProvNum
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            WHERE pl.ProcStatus = 'C' AND pl.ProcDate BETWEEN ? AND ? AND pr.Specialty = 8
              AND pc.ProcCode BETWEEN 'D4000' AND 'D4999'
        ", [$start, $end])->sm, 2);

        // Treatment plan per exam
        $txFee = DB::selectOne("SELECT SUM(ProcFee) AS sm FROM od_procedure_logs WHERE ProcStatus = 'TP' AND DateTP BETWEEN ? AND ?", [$start, $end])->sm ?? 0;

        $exams = DB::selectOne("
            SELECT COUNT(DISTINCT pl.ProcNum) AS exam_cnt
            FROM od_procedure_logs pl
            JOIN od_providers pr ON pl.ProvNum = pr.ProvNum
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            WHERE pl.ProcDate BETWEEN ? AND ?
              AND pc.ProcCode IN ('D0120', 'D0140', 'D0145', 'D0150', 'D0160', 'D0170', 'D0180')
              AND pl.ProcStatus = 'C' AND pr.Specialty = 8
        ", [$start, $end])->exam_cnt ?? 0;

        $data['treatment_plan_per_exam'] = $exams > 0 ? round($txFee / $exams, 2) : 0;

        return response()->json($data);
    }

    public function ortho(Request $request)
    {
        $start = $request->input('start_date', date('Y-m-01'));
        $end = $request->input('end_date', date('Y-m-d'));
        // Ortho = 6
        $data = $this->getSpecialtyMetrics($start, $end, 6, [
            'total_consults' => ['D9310'],
            'appliances_count' => ['D8220', 'D8210'],
            'phase_1_count' => ['D8010', 'D8020', 'D8030', 'D8040', 'D8050', 'D8060'],
            'comprehensive_starts_count' => ['D8070', 'D8080', 'D8090'],
            'debonds_count' => ['D8999C'],
            'invisalign_starts_count' => ['D8090', 'D8080'],
        ], [], [
            'total_active_patients_seen' => ['D8670', 'D8670A']
        ]);

        $data['active_patients_seen_per_day'] = $data['work_days'] > 0 ? round($data['total_active_patients_seen'] / $data['work_days'], 2) : 0;

        $starts = DB::selectOne("
            SELECT COUNT(*) AS cnt FROM od_procedure_logs pl 
            JOIN od_providers pr ON pl.ProvNum = pr.ProvNum
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            WHERE pl.ProcStatus='C' AND pl.ProcDate BETWEEN ? AND ? AND pr.Specialty=6
            AND pc.ProcCode IN ('D8010','D8020','D8030','D8040','D8050','D8060','D8070','D8080','D8090')
        ", [$start, $end])->cnt;

        $data['conversion'] = $data['total_consults'] > 0 ? round(($starts / $data['total_consults']) * 100, 2) : 0;

        return response()->json($data);
    }

    public function os(Request $request)
    {
        $start = $request->input('start_date', date('Y-m-01'));
        $end = $request->input('end_date', date('Y-m-d'));
        // OS = 5
        $data = $this->getSpecialtyMetrics($start, $end, 5, [
            'total_consults' => ['D9310'],
            'implant_placement_count' => ['D6010'],
        ], [
            'implant_placement_dollars' => ['D6010'],
            'sedations_dollars' => ['D9222', 'D9223', 'D9239', 'D9243', 'D9248'],
            'extractions_dollars' => ['D7140', 'D7210', 'D7220', 'D7230', 'D7240', 'D7241', 'D7250']
        ]);

        $txFee = DB::selectOne("SELECT SUM(ProcFee) AS sm FROM od_procedure_logs WHERE ProcStatus = 'TP' AND DateTP BETWEEN ? AND ?", [$start, $end])->sm ?? 0;

        $exams = DB::selectOne("
            SELECT COUNT(DISTINCT pl.ProcNum) AS exam_cnt
            FROM od_procedure_logs pl
            JOIN od_providers pr ON pl.ProvNum = pr.ProvNum
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            WHERE pl.ProcDate BETWEEN ? AND ?
              AND pc.ProcCode IN ('D0120', 'D0140', 'D0145', 'D0150', 'D0160', 'D0170', 'D0180')
              AND pl.ProcStatus = 'C' AND pr.Specialty = 5
        ", [$start, $end])->exam_cnt ?? 0;

        $data['treatment_plan_per_exam'] = $exams > 0 ? round($txFee / $exams, 2) : 0;

        return response()->json($data);
    }

    public function pedo(Request $request)
    {
        $start = $request->input('start_date', date('Y-m-01'));
        $end = $request->input('end_date', date('Y-m-d'));
        // Pedo = 7
        $data = $this->getSpecialtyMetrics($start, $end, 7, [
            'stainless_steel_crowns' => ['D2929', 'D2930', 'D2931', 'D2932', 'D2933', 'D2934'],
            'pulpotomies' => ['D3220'],
            'fillings' => ['D3230', 'D3240', 'D2330', 'D2331', 'D2332', 'D2335', 'D2391', 'D2392', 'D2393', 'D2394'],
            'space_maintainer' => ['D1510', 'D1515', 'D1516', 'D1517', 'D1520', 'D1525'],
            'total_extractions' => ['D7110', 'D7111', 'D7140'],
            'sealants' => ['D1351', '01351'],
            'nitrous_sedation' => ['D9230'],
            'total_crowns' => ['D2929', 'D2930', 'D2931', 'D2932', 'D2933', 'D2934'],
            'prophylaxis' => ['D1110', 'D1120', 'D4910'],
            'fluoride_treatments' => ['D1208', 'D1206'],
            'total_consults' => ['D9310'],
        ], [
            'sedations' => ['D9220', 'D9221', 'D9230', 'D9612', 'D9243', 'D9239']
        ]);

        $data['patients_per_day'] = $data['work_days'] > 0 ? round($data['patient_visits'] / $data['work_days'], 1) : 0;
        $data['production_per_patient'] = $data['patient_visits'] > 0 ? round($data['total_production'] / $data['patient_visits'], 2) : 0;

        $workDays100 = DB::selectOne("
            SELECT COUNT(DISTINCT DATE(pl.ProcDate)) AS wd 
            FROM (
                SELECT pl.ProcDate, SUM(pl.ProcFee) AS daily_prod 
                FROM od_procedure_logs pl 
                JOIN od_providers pr ON pl.ProvNum = pr.ProvNum
                WHERE pl.ProcStatus='C' AND pl.ProcDate BETWEEN ? AND ? AND pr.Specialty=7
                GROUP BY pl.ProcDate HAVING daily_prod > 100
            ) pl
        ", [$start, $end]);
        $data['total_working_days'] = $workDays100->wd ?? 0;

        // Acceptance
        $tx = DB::selectOne("
            SELECT COUNT(DISTINCT tp.PatNum) as presented,
            COUNT(DISTINCT CASE WHEN DATEDIFF(pl.ProcDate, tp.DateTP) = 0 THEN tp.PatNum END) as same_day,
            COUNT(DISTINCT CASE WHEN DATEDIFF(pl.ProcDate, tp.DateTP) <= 90 THEN tp.PatNum END) as rolling
            FROM treatment_plans tp
            LEFT JOIN od_procedure_logs pl ON tp.PatNum = pl.PatNum 
                AND pl.ProcStatus IN ('T', 'C') 
                AND pl.ProvNum IN (SELECT ProvNum FROM od_providers WHERE Specialty=7)
                AND pl.ProcDate >= tp.DateTP
            WHERE tp.DateTP BETWEEN ? AND ?
        ", [$start, $end]);

        $data['case_acceptance_same_day'] = $tx->presented > 0 ? round(($tx->same_day / $tx->presented) * 100, 2) : 0;
        $data['case_acceptance_rolling_90_days'] = $tx->presented > 0 ? round(($tx->rolling / $tx->presented) * 100, 2) : 0;

        return response()->json($data);
    }

    private function getSpecialtyMetrics($start, $end, $specId, $customCounts = [], $customSums = [], $distinctPatNumCounts = [])
    {
        $base = DB::selectOne("
            SELECT
                SUM(pl.ProcFee) AS total_prod,
                CAST(COUNT(DISTINCT CASE WHEN pl.ProcFee > 0 THEN DATE(pl.ProcDate) END) AS SIGNED) AS work_days,
                CAST(COUNT(DISTINCT pl.PatNum) AS SIGNED) AS patient_visits
            FROM od_procedure_logs pl
            JOIN od_providers pr ON pl.ProvNum = pr.ProvNum
            WHERE pl.ProcStatus = 'C'
              AND pl.ProcDate BETWEEN ? AND ?
              AND pr.Specialty = ?
        ", [$start, $end, $specId]);

        $workDays = max(1, (int) $base->work_days);
        $res = [
            'total_production' => round($base->total_prod ?? 0, 2),
            'production_per_day' => round(($base->total_prod ?? 0) / $workDays, 2),
            'patient_visits' => (int) $base->patient_visits,
            'work_days' => $workDays,
        ];

        foreach ($customCounts as $key => $codes) {
            $inStr = "'" . implode("','", $codes) . "'";
            $res[$key] = (int) DB::selectOne("
                SELECT COUNT(DISTINCT pl.ProcNum) AS cnt
                FROM od_procedure_logs pl
                JOIN od_providers pr ON pl.ProvNum = pr.ProvNum
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
                WHERE pl.ProcStatus = 'C' AND pl.ProcDate BETWEEN ? AND ? AND pr.Specialty = ?
                  AND pc.ProcCode IN ($inStr)
            ", [$start, $end, $specId])->cnt;
        }

        foreach ($customSums as $key => $codes) {
            $inStr = "'" . implode("','", $codes) . "'";
            $res[$key] = round((float) DB::selectOne("
                SELECT SUM(pl.ProcFee) AS sm
                FROM od_procedure_logs pl
                JOIN od_providers pr ON pl.ProvNum = pr.ProvNum
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
                WHERE pl.ProcStatus = 'C' AND pl.ProcDate BETWEEN ? AND ? AND pr.Specialty = ?
                  AND pc.ProcCode IN ($inStr)
            ", [$start, $end, $specId])->sm, 2);
        }

        foreach ($distinctPatNumCounts as $key => $codes) {
            $inStr = "'" . implode("','", $codes) . "'";
            $res[$key] = (int) DB::selectOne("
                SELECT COUNT(DISTINCT pl.PatNum) AS cnt
                FROM od_procedure_logs pl
                JOIN od_providers pr ON pl.ProvNum = pr.ProvNum
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
                WHERE pl.ProcStatus = 'C' AND pl.ProcDate BETWEEN ? AND ? AND pr.Specialty = ?
                  AND pc.ProcCode IN ($inStr)
            ", [$start, $end, $specId])->cnt;
        }

        if (isset($res['total_consults'])) {
            $res['consults_per_day'] = round($res['total_consults'] / $workDays, 2);
        }

        return $res;
    }


    public function hygieneProviders(Request $request)
    {
        $start = $request->input('start_date', now()->startOfYear()->toDateString());
        $end = $request->input('end_date', now()->toDateString());

        // We fetch the overall to get the 'avg' and 'total' rows.
        $overall = $this->hygieneKpis($start, $end);

        // Fetch distinct providers who have hygiene production
        $provs = \Illuminate\Support\Facades\DB::select("
            SELECT DISTINCT pl.ProvNum, pr.Abbr, pr.LName,
                   'Unassigned' as Location
            FROM od_procedure_logs pl
            JOIN od_providers pr ON pl.ProvNum = pr.ProvNum
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            WHERE pc.IsHygiene = 'true' AND pl.ProcStatus = 'C' AND pl.ProcDate BETWEEN ? AND ?
        ", [$start, $end]);

        $providersList = [];

        foreach ($provs as $p) {
            $pId = $p->ProvNum;

            // ① Core
            $s = \Illuminate\Support\Facades\DB::selectOne("
                SELECT
                    COALESCE(SUM(pl.ProcFee), 0) AS total_prod,
                    COUNT(*) AS total_procs,
                    COUNT(DISTINCT pl.ProcDate) AS work_days,
                    COUNT(DISTINCT CONCAT(pl.PatNum,'-',pl.ProcDate)) AS visits,
                    COUNT(DISTINCT CASE WHEN pc.ProcCode IN ('D4341','D4342','D4355','D4346','D4910') THEN CONCAT(pl.PatNum,'-',pl.ProcDate) END) AS perio_visits,
                    COUNT(DISTINCT CASE WHEN pc.ProcCode IN ('D1206','D1208') THEN CONCAT(pl.PatNum,'-',pl.ProcDate) END) AS fluoride_count,
                    COUNT(DISTINCT CASE WHEN pc.ProcCode IN ('D4341','D4342') THEN CONCAT(pl.PatNum,'-',pl.ProcDate) END) AS srp_count,
                    COUNT(DISTINCT CASE WHEN pc.ProcCode IN ('D0210') THEN CONCAT(pl.PatNum,'-',pl.ProcDate) END) AS fmx_count,
                    SUM(pc.ProcCode IN ('D1351')) AS sealants,
                    SUM(pc.ProcCode IN ('D9972','D9973','D9974','D9975')) AS whitening,
                    SUM(pc.ProcCode IN ('D4381')) AS antimicrobial
                FROM od_procedure_logs pl
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
                WHERE pc.IsHygiene = 'true' AND pl.ProcStatus = 'C' AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
            ", [$start, $end, $pId]);

            $hygProd = (float) $s->total_prod;
            $hygCount = (int) $s->total_procs;
            $workDays = (int) $s->work_days;
            $hygVisits = (int) $s->visits;

            // ② Case Acceptance
            $caRates = \Illuminate\Support\Facades\DB::selectOne("
                SELECT
                    SUM(CASE WHEN pl.ProcStatus = 'TP' THEN pl.ProcFee ELSE 0 END) AS proposed,
                    SUM(CASE WHEN pl.ProcStatus = 'C' THEN pl.ProcFee ELSE 0 END) AS completed,
                    SUM(CASE WHEN pl.ProcStatus = 'TP' AND pl.AptNum IS NOT NULL AND pl.AptNum != '0' THEN pl.ProcFee ELSE 0 END) AS accepted
                FROM od_procedure_logs pl
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
                WHERE pc.IsHygiene = 'true' AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
            ", [$start, $end, $pId]);

            // ③ Reappointment
            $rapt = \Illuminate\Support\Facades\DB::selectOne("
                SELECT COUNT(DISTINCT a.AptNum) AS total, COUNT(DISTINCT CASE WHEN a.NextAptNum IS NOT NULL AND a.NextAptNum != '0' THEN a.AptNum END) AS with_next
                FROM od_procedure_logs pl JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum JOIN od_appointments a ON pl.AptNum = a.AptNum
                WHERE pc.IsHygiene = 'true' AND pl.ProcStatus = 'C' AND pl.ProcDate BETWEEN ? AND ? AND pl.AptNum IS NOT NULL AND pl.AptNum != '0' AND pl.ProvNum = ?
            ", [$start, $end, $pId]);
            $reapptRate = $rapt->total > 0 ? round($rapt->with_next / $rapt->total * 100, 2) : 0;

            // ④ Perio Reappointment
            $prapt = \Illuminate\Support\Facades\DB::selectOne("
                SELECT COUNT(DISTINCT a.AptNum) AS total, COUNT(DISTINCT CASE WHEN a.NextAptNum IS NOT NULL AND a.NextAptNum != '0' THEN a.AptNum END) AS with_next
                FROM od_procedure_logs pl JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum JOIN od_appointments a ON pl.AptNum = a.AptNum
                WHERE pc.ProcCode IN ('D4341','D4342','D4346','D4910') AND pl.ProcStatus = 'C' AND pl.ProcDate BETWEEN ? AND ? AND pl.AptNum IS NOT NULL AND pl.AptNum != '0' AND pl.ProvNum = ?
            ", [$start, $end, $pId]);
            $perioReapptRate = $prapt->total > 0 ? round($prapt->with_next / $prapt->total * 100, 2) : 0;

            // Active Adults/Children (Office wide denominator usually, or we skip per provider query unless we filter by provider... but logic says "Patients seen by provider")
            // We use the overall age count to not kill DB, or specific to provider's seen patients. For metric "retention", denominator is usually patients seen who were also seen 12m ago.
            // Wait, existing retention logic: activeAdults = all active adults. We'll use office wide denominator for consistency per the current kpi, but ideally it's provider specific. We'll query provider specific seen pt in last 12/6mo.
            $ret12 = \Illuminate\Support\Facades\DB::selectOne("
                SELECT
                    COUNT(DISTINCT CASE WHEN TIMESTAMPDIFF(YEAR, pt.Birthdate, CURDATE()) >= 18 THEN pl.PatNum END) AS adult,
                    COUNT(DISTINCT CASE WHEN TIMESTAMPDIFF(YEAR, pt.Birthdate, CURDATE()) <  18 THEN pl.PatNum END) AS child
                FROM od_procedure_logs pl JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum JOIN od_patients pt ON pl.PatNum = pt.PatNum
                WHERE pc.IsHygiene = 'true' AND pl.ProcStatus = 'C' AND pt.PatStatus = 'Patient' AND pl.ProcDate BETWEEN DATE_SUB(?, INTERVAL 12 MONTH) AND ? AND pl.ProvNum = ?
            ", [$end, $end, $pId]);
            $ret6 = \Illuminate\Support\Facades\DB::selectOne("
                SELECT
                    COUNT(DISTINCT CASE WHEN TIMESTAMPDIFF(YEAR, pt.Birthdate, CURDATE()) >= 18 THEN pl.PatNum END) AS adult,
                    COUNT(DISTINCT CASE WHEN TIMESTAMPDIFF(YEAR, pt.Birthdate, CURDATE()) <  18 THEN pl.PatNum END) AS child
                FROM od_procedure_logs pl JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum JOIN od_patients pt ON pl.PatNum = pt.PatNum
                WHERE pc.IsHygiene = 'true' AND pl.ProcStatus = 'C' AND pt.PatStatus = 'Patient' AND pl.ProcDate BETWEEN DATE_SUB(?, INTERVAL 6 MONTH) AND ? AND pl.ProvNum = ?
            ", [$end, $end, $pId]);

            // We need a proper denominator for retention "Patients seen within date range".
            $seenDens = \Illuminate\Support\Facades\DB::selectOne("
                SELECT
                    COUNT(DISTINCT CASE WHEN TIMESTAMPDIFF(YEAR, pt.Birthdate, CURDATE()) >= 18 THEN pl.PatNum END) AS adult,
                    COUNT(DISTINCT CASE WHEN TIMESTAMPDIFF(YEAR, pt.Birthdate, CURDATE()) <  18 THEN pl.PatNum END) AS child
                FROM od_procedure_logs pl JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum JOIN od_patients pt ON pl.PatNum = pt.PatNum
                WHERE pc.IsHygiene = 'true' AND pl.ProcStatus = 'C' AND pt.PatStatus = 'Patient' AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
            ", [$start, $end, $pId]);

            // Visits with Tx Plan
            $visitsWithTx = (int) \Illuminate\Support\Facades\DB::selectOne("
                SELECT COUNT(DISTINCT CONCAT(pl.PatNum,'-',pl.ProcDate)) AS cnt
                FROM od_procedure_logs pl
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
                JOIN (
                    SELECT DISTINCT PatNum FROM od_procedure_logs WHERE ProcStatus = 'TP' AND ProcDate BETWEEN ? AND ?
                ) tp ON pl.PatNum = tp.PatNum
                WHERE pc.IsHygiene = 'true' AND pl.ProcStatus = 'C' AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
            ", [$start, $end, $start, $end, $pId])->cnt;

            // Tx plans per day
            $txPlanCount = (int) \Illuminate\Support\Facades\DB::selectOne("
                SELECT COUNT(DISTINCT CONCAT(PatNum,'-',DateTP)) AS cnt
                FROM od_procedure_logs WHERE ProcStatus = 'TP' AND DateTP BETWEEN ? AND ? AND ProvNum = ?
            ", [$start, $end, $pId])->cnt;

            // Avg prod per hour
            $totalMins = (float) (\Illuminate\Support\Facades\DB::selectOne("
                SELECT COALESCE(SUM(LENGTH(a.Pattern) * 5), 0) AS mins
                FROM od_procedure_logs pl JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum JOIN od_appointments a ON pl.AptNum = a.AptNum
                WHERE pc.IsHygiene = 'true' AND pl.ProcStatus = 'C' AND pl.ProcDate BETWEEN ? AND ? AND pl.AptNum IS NOT NULL AND pl.AptNum != '0' AND a.Pattern IS NOT NULL AND a.Pattern != '' AND pl.ProvNum = ?
            ", [$start, $end, $pId])->mins ?? 0);

            $providersList[] = [
                'location' => $p->Location,
                'provider' => $p->Abbr . ' ' . $p->LName,
                'perio_pct' => $hygVisits > 0 ? round($s->perio_visits / $hygVisits * 100, 2) : 0,
                'fluoride_per_day' => $workDays > 0 ? round($s->fluoride_count / $workDays, 2) : 0,
                'avg_prod_per_day' => $workDays > 0 ? round($hygProd / $workDays, 2) : 0,
                'avg_prod_per_prov_day' => $workDays > 0 ? round($hygProd / $workDays, 2) : 0,
                'prod_per_visit' => $hygVisits > 0 ? round($hygProd / $hygVisits, 2) : 0,
                'fmx_per_day' => $workDays > 0 ? round($s->fmx_count / $workDays, 2) : 0,
                'srp_per_day' => $workDays > 0 ? round($s->srp_count / $workDays, 2) : 0,
                'visits_per_day' => $workDays > 0 ? round($hygVisits / $workDays, 2) : 0,
                'reappt' => $reapptRate,
                'perio_reappt' => $perioReapptRate,
                'adult_retention_12m' => $seenDens->adult > 0 ? round($ret12->adult / $seenDens->adult * 100, 2) : 0,
                'adult_retention_6m' => $seenDens->adult > 0 ? round($ret6->adult / $seenDens->adult * 100, 2) : 0,
                'child_retention_12m' => $seenDens->child > 0 ? round($ret12->child / $seenDens->child * 100, 2) : 0,
                'child_retention_6m' => $seenDens->child > 0 ? round($ret6->child / $seenDens->child * 100, 2) : 0,
                'sealants' => (int) $s->sealants,
                'whitening' => (int) $s->whitening,
                'antimicrobial' => (int) $s->antimicrobial,
                'prod_per_proc' => $hygCount > 0 ? round($hygProd / $hygCount, 2) : 0,
                'visits_with_tx_pct' => $hygVisits > 0 ? round($visitsWithTx / $hygVisits * 100, 2) : 0,
                'tx_plans_per_day' => $workDays > 0 ? round($txPlanCount / $workDays, 2) : 0,
                'avg_prod_per_hour' => $totalMins > 0 ? round($hygProd / ($totalMins / 60), 2) : 0,
                'case_acceptance' => $caRates->proposed > 0 ? round((($caRates->completed + $caRates->accepted) / $caRates->proposed) * 100, 2) : 0,
            ];
        }

        return response()->json([
            'providers' => $providersList,
            'avg' => $overall,
            'total' => $overall
        ]);
    }

    public function doctorProviders(Request $request)
    {
        $start = $request->input('start_date', now()->startOfYear()->toDateString());
        $end = $request->input('end_date', now()->toDateString());

        $overall = $this->doctorKpis($start, $end);

        $provs = \Illuminate\Support\Facades\DB::select("
            SELECT DISTINCT pl.ProvNum, pr.Abbr, pr.LName,
                   'Unassigned' as Location
            FROM od_procedure_logs pl
            JOIN od_providers pr ON pl.ProvNum = pr.ProvNum
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            WHERE pc.IsHygiene = 'false' AND pl.ProcStatus = 'C' AND pl.ProcDate BETWEEN ? AND ?
        ", [$start, $end]);

        $providersList = [];

        foreach ($provs as $p) {
            $pId = $p->ProvNum;

            // Total Production & Counts
            $doc = \Illuminate\Support\Facades\DB::selectOne("
                SELECT
                    COALESCE(SUM(pl.ProcFee), 0) AS total_prod,
                    COUNT(*) AS total_procs,
                    COUNT(DISTINCT pl.ProcDate) AS work_days,
                    COUNT(DISTINCT CONCAT(pl.PatNum, '-', pl.ProcDate)) AS visits
                FROM od_procedure_logs pl JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
                WHERE pc.IsHygiene = 'false' AND pl.ProcStatus = 'C' AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
            ", [$start, $end, $pId]);
            $docProd = (float) $doc->total_prod;
            $workDays = (int) $doc->work_days;
            $docVisits = (int) $doc->visits;

            // Appointment Time
            $apts = \Illuminate\Support\Facades\DB::selectOne("
                SELECT COUNT(DISTINCT a.AptNum) AS total_apts, AVG(LENGTH(a.Pattern) * 5) AS avg_mins, SUM(LENGTH(a.Pattern) * 5) / 60 AS total_hours
                FROM od_appointments a JOIN od_procedure_logs pl ON a.AptNum = pl.AptNum JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
                WHERE a.AptStatus = 'Complete' AND pc.IsHygiene = 'false' AND pl.ProcStatus = 'C' AND a.Pattern IS NOT NULL AND a.Pattern != '' AND DATE(a.AptDateTime) BETWEEN ? AND ? AND pl.ProvNum = ?
            ", [$start, $end, $pId]);
            $docAptCount = (int) $apts->total_apts;
            $avgAptMins = (float) $apts->avg_mins;
            $totalHours = (float) $apts->total_hours;

            // Case Acceptance
            $caRates = \Illuminate\Support\Facades\DB::selectOne("
                SELECT
                    SUM(CASE WHEN pl.ProcStatus = 'TP' THEN pl.ProcFee ELSE 0 END) AS proposed,
                    SUM(CASE WHEN pl.ProcStatus = 'C' THEN pl.ProcFee ELSE 0 END) AS completed,
                    SUM(CASE WHEN pl.ProcStatus = 'TP' AND pl.AptNum IS NOT NULL AND pl.AptNum != '0' THEN pl.ProcFee ELSE 0 END) AS accepted
                FROM od_procedure_logs pl JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
                WHERE pc.IsHygiene = 'false' AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
            ", [$start, $end, $pId]);

            // Reappt
            $docReappt = \Illuminate\Support\Facades\DB::selectOne("
                SELECT COUNT(*) AS total, SUM(CASE WHEN NextAptNum IS NOT NULL AND NextAptNum != '0' THEN 1 ELSE 0 END) AS with_next
                FROM od_appointments a WHERE IsHygiene = 'false' AND AptStatus = 'Complete' AND DATE(AptDateTime) BETWEEN ? AND ? AND ProvNum = ?
            ", [$start, $end, $pId]);

            // Exam count
            $examCount = \Illuminate\Support\Facades\DB::table('od_procedure_logs as pl')->join('od_procedures as pc', 'pl.CodeNum', '=', 'pc.CodeNum')
                ->whereIn('pc.ProcCode', ['D0120', 'D0140', 'D0150', 'D0160', 'D0170', 'D0180'])->where('pl.ProcStatus', 'C')->whereBetween('pl.ProcDate', [$start, $end])->where('pl.ProvNum', $pId)->count();

            // TX Matrix
            $txMatrix = \Illuminate\Support\Facades\DB::selectOne("
                SELECT
                    COUNT(DISTINCT CASE WHEN DATEDIFF(pl.ProcDate, tp.DateTP) = 0 THEN CONCAT(pl.PatNum,'-',pl.ProcDate) END) AS same_day_completions,
                    COALESCE(AVG(CASE WHEN pt_hist.first_visit BETWEEN ? AND ? THEN pl.ProcFee END), 0) AS avg_new_pt_tx,
                    COALESCE(SUM(CASE WHEN pt_hist.first_visit < ? THEN pl.ProcFee END), 0) AS total_existing_pt_tx,
                    COALESCE(AVG(CASE WHEN pt_hist.first_visit < ? AND pl.ProcStatus = 'TP' THEN pl.ProcFee END), 0) AS avg_tp_existing_pt,
                    COALESCE(AVG(CASE WHEN pt_hist.first_visit BETWEEN ? AND ? AND pl.ProcStatus = 'TP' THEN pl.ProcFee END), 0) AS avg_tp_new_pt,
                    COUNT(DISTINCT CASE WHEN pt_hist.first_visit BETWEEN ? AND ? AND pl.ProcStatus IN ('C','TP') THEN pl.PatNum END) AS new_pts_with_tx,
                    COUNT(DISTINCT CASE WHEN pt_hist.first_visit < ? AND pl.ProcStatus IN ('C','TP') THEN pl.PatNum END) AS existing_pts_with_tx,
                    COUNT(DISTINCT CASE WHEN pt_hist.first_visit BETWEEN ? AND ? THEN pt_hist.PatNum END) AS new_pts_total,
                    COUNT(DISTINCT CASE WHEN pt_hist.first_visit < ? AND pl.ProcStatus = 'C' THEN pt_hist.PatNum END) AS existing_pts_total,
                    COALESCE(AVG(CASE WHEN pt_hist.first_visit BETWEEN ? AND ? AND DATEDIFF(pl.ProcDate, tp.DateTP) = 0 AND pl.ProcStatus = 'C' THEN pl.ProcFee END), 0) AS avg_sameday_new_pt
                FROM od_procedure_logs pl
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
                JOIN (
                    SELECT PatNum, MIN(ProcDate) AS first_visit 
                    FROM od_procedure_logs WHERE ProcStatus = 'C' GROUP BY PatNum
                ) pt_hist ON pl.PatNum = pt_hist.PatNum
                LEFT JOIN od_procedure_logs tp ON tp.PatNum = pl.PatNum AND tp.ProcStatus = 'TP' AND tp.DateTP IS NOT NULL AND tp.DateTP BETWEEN ? AND ?
                WHERE pc.IsHygiene = 'false' AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
            ", [
                $start,
                $end,
                $start,
                $start,
                $start,
                $end,
                $start,
                $end,
                $start,
                $start,
                $end,
                $start,
                $start,
                $end,
                $start,
                $end,
                $start,
                $end,
                $pId
            ]);

            $providersList[] = [
                'location' => $p->Location,
                'provider' => $p->Abbr . ' ' . $p->LName,
                'case_acceptance_same_day' => $docVisits > 0 ? round(($txMatrix->same_day_completions / $docVisits) * 100, 2) : 0,
                'case_acceptance_rate' => $caRates->proposed > 0 ? round((($caRates->completed + $caRates->accepted) / $caRates->proposed) * 100, 2) : 0,
                'new_pt_tx_dollars' => round((float) $txMatrix->avg_tp_new_pt, 2),
                'existing_pt_tx_dollars' => round((float) $txMatrix->total_existing_pt_tx, 2),
                'avg_apt_time_mins' => round($avgAptMins, 2),
                'avg_prod_per_hour' => $totalHours > 0 ? round($docProd / $totalHours, 2) : 0,
                'avg_prod_per_apt' => $docAptCount > 0 ? round($docProd / $docAptCount, 2) : 0,
                'same_day_tx_per_new_pt' => round((float) $txMatrix->avg_sameday_new_pt, 2),
                'avg_prod_per_prov_day' => $workDays > 0 ? round($docProd / $workDays, 2) : 0,
                'avg_tx_per_existing_pt' => round((float) $txMatrix->avg_tp_existing_pt, 2),
                'avg_tx_per_new_pt' => round((float) $txMatrix->avg_tp_new_pt, 2),
                'pct_new_pt_with_tx' => $txMatrix->new_pts_total > 0 ? round(($txMatrix->new_pts_with_tx / $txMatrix->new_pts_total) * 100, 2) : 0,
                'pct_existing_pt_with_tx' => $txMatrix->existing_pts_total > 0 ? round(($txMatrix->existing_pts_with_tx / $txMatrix->existing_pts_total) * 100, 2) : 0,
                'reappt' => $docReappt->total > 0 ? round(($docReappt->with_next / $docReappt->total) * 100, 2) : 0,
                'prod_per_exam' => $examCount > 0 ? round($docProd / $examCount, 2) : 0,
                'total_production' => round($docProd, 2),
            ];
        }

        return response()->json([
            'providers' => $providersList,
            'avg' => $overall,
            'total' => $overall
        ]);
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


    /* ── SPECIALTY PROVIDERS ENDPOINTS ─────────────────────────────────────── */

    private function getSpecialtyProvidersBaseLoop($start, $end, $specId, $customCounts = [], $customSums = [], $distinctPatNumCounts = [], $extraCallback = null)
    {
        // Fetch distinct providers who have production in this specialty
        $provs = \Illuminate\Support\Facades\DB::select("
            SELECT DISTINCT pl.ProvNum, pr.Abbr, pr.LName,
                   'Unassigned' as Location
            FROM od_procedure_logs pl
            JOIN od_providers pr ON pl.ProvNum = pr.ProvNum
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            WHERE pl.ProcStatus = 'C' AND pl.ProcDate BETWEEN ? AND ? AND pr.Specialty = ?
        ", [$start, $end, $specId]);

        $providersList = [];
        
        // Averages tracking
        $totals = [];
        $counts = [];
        
        foreach ($provs as $p) {
            $provNum = $p->ProvNum;
            $row = [
                'Location' => $p->Location,
                'Provider' => $p->Abbr . ' ' . $p->LName,
                'ProvNum' => $provNum
            ];

            // Base queries
            $base = \Illuminate\Support\Facades\DB::selectOne("
                SELECT
                    SUM(pl.ProcFee) AS total_prod,
                    CAST(COUNT(DISTINCT CASE WHEN pl.ProcFee > 0 THEN DATE(pl.ProcDate) END) AS SIGNED) AS work_days,
                    CAST(COUNT(DISTINCT pl.PatNum) AS SIGNED) AS patient_visits
                FROM od_procedure_logs pl
                WHERE pl.ProcStatus = 'C' AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
            ", [$start, $end, $provNum]);

            $workDays = max(1, (int) $base->work_days);
            $row['total_production'] = round($base->total_prod ?? 0, 2);
            $row['production_per_day'] = round(($base->total_prod ?? 0) / $workDays, 2);
            $row['patient_visits'] = (int) $base->patient_visits;
            $row['work_days'] = $workDays;

            foreach ($customCounts as $key => $codes) {
                $inStr = "'" . implode("','", $codes) . "'";
                $row[$key] = (int) \Illuminate\Support\Facades\DB::selectOne("
                    SELECT COUNT(DISTINCT pl.ProcNum) AS cnt
                    FROM od_procedure_logs pl
                    JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
                    WHERE pl.ProcStatus = 'C' AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
                      AND pc.ProcCode IN ($inStr)
                ", [$start, $end, $provNum])->cnt;
            }

            foreach ($customSums as $key => $codes) {
                $inStr = "'" . implode("','", $codes) . "'";
                $row[$key] = round((float) \Illuminate\Support\Facades\DB::selectOne("
                    SELECT SUM(pl.ProcFee) AS sm
                    FROM od_procedure_logs pl
                    JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
                    WHERE pl.ProcStatus = 'C' AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
                      AND pc.ProcCode IN ($inStr)
                ", [$start, $end, $provNum])->sm, 2);
            }

            foreach ($distinctPatNumCounts as $key => $codes) {
                $inStr = "'" . implode("','", $codes) . "'";
                $row[$key] = (int) \Illuminate\Support\Facades\DB::selectOne("
                    SELECT COUNT(DISTINCT pl.PatNum) AS cnt
                    FROM od_procedure_logs pl
                    JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
                    WHERE pl.ProcStatus = 'C' AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
                      AND pc.ProcCode IN ($inStr)
                ", [$start, $end, $provNum])->cnt;
            }

            // Consults per day special logic
            if (isset($row['total_consults'])) {
                $row['consults_per_day'] = round($row['total_consults'] / $workDays, 2);
            }
            // Active patients seen per day
            if (isset($row['total_active_patients_seen'])) {
                $row['active_patients_seen_per_day'] = round($row['total_active_patients_seen'] / $workDays, 2);
            }

            // Allow extra callback for specific specialties
            if ($extraCallback) {
                $row = $extraCallback($row, $start, $end, $provNum);
            }

            $providersList[] = $row;

            // Compute running totals for footer
            foreach ($row as $k => $v) {
                if (!is_numeric($v) || in_array($k, ['ProvNum'])) continue;
                if (!isset($totals[$k])) { $totals[$k] = 0; $counts[$k] = 0; }
                $totals[$k] += $v;
                $counts[$k]++;
            }
        }

        $avg = [];
        foreach ($totals as $k => $v) {
            $avg[$k] = $counts[$k] > 0 ? round($v / $counts[$k], 2) : 0;
        }

        return [
            'providers' => $providersList,
            'total' => $totals,
            'avg' => $avg
        ];
    }

    public function endoProviders(Request $request)
    {
        $start = $request->input('start_date', date('Y-m-01'));
        $end = $request->input('end_date', date('Y-m-d'));
        return response()->json($this->getSpecialtyProvidersBaseLoop($start, $end, 2, [
            'total_consults' => ['D9310'],
            'retreats_count' => ['D3346', 'D3347', 'D3348'],
            'rct_count' => ['D3310', 'D3320', 'D3330'],
            'obstruction_count' => ['D3331'],
            'biopure_count' => ['D3000', 'D3999'],
        ]));
    }

    public function perioProviders(Request $request)
    {
        $start = $request->input('start_date', date('Y-m-01'));
        $end = $request->input('end_date', date('Y-m-d'));
        // Perio Specialty = 8
        return response()->json($this->getSpecialtyProvidersBaseLoop($start, $end, 8, [
            'total_consults' => ['D9310'],
            'implant_placement_count' => ['D6010'],
        ], [
            'implant_placement_dollars' => ['D6010'],
            'sedations_dollars' => ['D9222', 'D9223', 'D9239', 'D9243', 'D9248'],
        ], [], function($row, $start, $end, $provNum) {
            // Perio Codes $
            $row['perio_codes_dollars'] = round((float) \Illuminate\Support\Facades\DB::selectOne("
                SELECT SUM(pl.ProcFee) AS sm
                FROM od_procedure_logs pl
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
                WHERE pl.ProcStatus = 'C' AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
                  AND pc.ProcCode BETWEEN 'D4000' AND 'D4999'
            ", [$start, $end, $provNum])->sm, 2);

            // Treatment plan per exam
            $txFee = \Illuminate\Support\Facades\DB::selectOne("SELECT SUM(ProcFee) AS sm FROM od_procedure_logs WHERE ProcStatus = 'TP' AND DateTP BETWEEN ? AND ? AND ProvNum = ?", [$start, $end, $provNum])->sm ?? 0;
            $exams = \Illuminate\Support\Facades\DB::selectOne("
                SELECT COUNT(DISTINCT pl.ProcNum) AS exam_cnt
                FROM od_procedure_logs pl
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
                WHERE pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
                  AND pc.ProcCode IN ('D0120', 'D0140', 'D0145', 'D0150', 'D0160', 'D0170', 'D0180')
                  AND pl.ProcStatus = 'C'
            ", [$start, $end, $provNum])->exam_cnt ?? 0;
            $row['treatment_plan_per_exam'] = $exams > 0 ? round($txFee / $exams, 2) : 0;
            
            return $row;
        }));
    }

    public function orthoProviders(Request $request)
    {
        $start = $request->input('start_date', date('Y-m-01'));
        $end = $request->input('end_date', date('Y-m-d'));
        // Ortho = 6
        return response()->json($this->getSpecialtyProvidersBaseLoop($start, $end, 6, [
            'total_consults' => ['D9310'],
            'appliances_count' => ['D8220', 'D8210'],
            'phase_1_count' => ['D8010', 'D8020', 'D8030', 'D8040', 'D8050', 'D8060'],
            'comprehensive_starts_count' => ['D8070', 'D8080', 'D8090'],
            'debonds_count' => ['D8999C'],
            'invisalign_starts_count' => ['D8090', 'D8080'],
        ], [], [
            'total_active_patients_seen' => ['D8670', 'D8670A']
        ], function($row, $start, $end, $provNum) {
            
            $starts = \Illuminate\Support\Facades\DB::selectOne("
                SELECT COUNT(*) AS cnt FROM od_procedure_logs pl 
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
                WHERE pl.ProcStatus='C' AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum=?
                AND pc.ProcCode IN ('D8010','D8020','D8030','D8040','D8050','D8060','D8070','D8080','D8090')
            ", [$start, $end, $provNum])->cnt;

            $row['conversion'] = $row['total_consults'] > 0 ? round(($starts / $row['total_consults']) * 100, 2) : 0;
            return $row;
        }));
    }

    public function osProviders(Request $request)
    {
        $start = $request->input('start_date', date('Y-m-01'));
        $end = $request->input('end_date', date('Y-m-d'));
        // OS = 5
        return response()->json($this->getSpecialtyProvidersBaseLoop($start, $end, 5, [
            'total_consults' => ['D9310'],
            'implant_placement_count' => ['D6010'],
        ], [
            'implant_placement_dollars' => ['D6010'],
            'sedations_dollars' => ['D9222', 'D9223', 'D9239', 'D9243', 'D9248'],
            'extractions_dollars' => ['D7140', 'D7210', 'D7220', 'D7230', 'D7240', 'D7241', 'D7250']
        ], [], function($row, $start, $end, $provNum) {
            $txFee = \Illuminate\Support\Facades\DB::selectOne("SELECT SUM(ProcFee) AS sm FROM od_procedure_logs WHERE ProcStatus = 'TP' AND DateTP BETWEEN ? AND ? AND ProvNum = ?", [$start, $end, $provNum])->sm ?? 0;
            $exams = \Illuminate\Support\Facades\DB::selectOne("
                SELECT COUNT(DISTINCT pl.ProcNum) AS exam_cnt
                FROM od_procedure_logs pl
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
                WHERE pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
                  AND pc.ProcCode IN ('D0120', 'D0140', 'D0145', 'D0150', 'D0160', 'D0170', 'D0180')
                  AND pl.ProcStatus = 'C'
            ", [$start, $end, $provNum])->exam_cnt ?? 0;
            $row['treatment_plan_per_exam'] = $exams > 0 ? round($txFee / $exams, 2) : 0;
            return $row;
        }));
    }

    public function pedoProviders(Request $request)
    {
        $start = $request->input('start_date', date('Y-m-01'));
        $end = $request->input('end_date', date('Y-m-d'));
        // Pedo = 4
        return response()->json($this->getSpecialtyProvidersBaseLoop($start, $end, 4, [
            'stainless_steel_crowns' => ['D2929', 'D2930', 'D2931', 'D2932', 'D2933', 'D2934'],
            'pulpotomies' => ['D3220'],
            'fillings' => ['D3230', 'D3240', 'D2330', 'D2331', 'D2332', 'D2335', 'D2391', 'D2392', 'D2393', 'D2394'],
            'space_maintainer' => ['D1510', 'D1515', 'D1516', 'D1517', 'D1520', 'D1525'],
            'total_extractions' => ['D7110', 'D7111', 'D7140'],
            'sealants' => ['D1351', '01351'],
            'nitrous_sedation' => ['D9230'],
            'total_crowns' => ['D2710', 'D2712', 'D2720', 'D2721', 'D2722', 'D2740', 'D2750', 'D2751', 'D2752', 'D2753', 'D2780', 'D2781', 'D2782', 'D2783', 'D2790', 'D2791', 'D2792'],
            'prophylaxis' => ['D1110', 'D1120', 'D4910'],
            'fluoride_treatments' => ['D1208', 'D1206'],
            'total_consults' => ['D9310'],
        ], [
            'sedations' => ['D9220', 'D9221', 'D9230', 'D9612', 'D9243', 'D9239'],
        ], [], function($row, $start, $end, $provNum) {
            
            // Re-map workdays
            $row['total_working_days'] = $row['work_days'];
            $row['patients_per_day'] = $row['work_days'] > 0 ? round($row['patient_visits'] / $row['work_days'], 1) : 0;
            $row['production_per_patient'] = $row['patient_visits'] > 0 ? round($row['total_production'] / $row['patient_visits'], 2) : 0;

            // Rolling 90 days and Same Day acceptance
            $txDayZero = \Illuminate\Support\Facades\DB::selectOne("
                SELECT COUNT(DISTINCT a.PatNum) AS tx_pts
                FROM od_procedure_logs a
                WHERE a.ProcStatus = 'TP' AND a.DateTP BETWEEN ? AND ? AND a.ProvNum=?
            ", [$start, $end, $provNum])->tx_pts ?? 0;

            $accSameDay = \Illuminate\Support\Facades\DB::selectOne("
                SELECT COUNT(DISTINCT a.PatNum) AS acc_pts
                FROM od_procedure_logs a
                JOIN od_procedure_logs b ON a.PatNum = b.PatNum AND a.CodeNum = b.CodeNum
                WHERE a.ProcStatus = 'TP' AND a.DateTP BETWEEN ? AND ? AND a.ProvNum=?
                  AND b.ProcStatus IN ('C','S') AND DATE(b.ProcDate) = DATE(a.DateTP)
            ", [$start, $end, $provNum])->acc_pts ?? 0;

            $acc90Days = \Illuminate\Support\Facades\DB::selectOne("
                SELECT COUNT(DISTINCT a.PatNum) AS acc_pts
                FROM od_procedure_logs a
                JOIN od_procedure_logs b ON a.PatNum = b.PatNum AND a.CodeNum = b.CodeNum
                WHERE a.ProcStatus = 'TP' AND a.DateTP BETWEEN ? AND ? AND a.ProvNum=?
                  AND b.ProcStatus IN ('C','S') AND b.ProcDate >= a.DateTP AND DATEDIFF(b.ProcDate, a.DateTP) <= 90
            ", [$start, $end, $provNum])->acc_pts ?? 0;

            $row['case_acceptance_same_day'] = $txDayZero > 0 ? round(($accSameDay / $txDayZero) * 100, 2) : 0;
            $row['case_acceptance_rolling_90_days'] = $txDayZero > 0 ? round(($acc90Days / $txDayZero) * 100, 2) : 0;

            return $row;
        }));
    }}

