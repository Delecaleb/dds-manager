<?php

namespace App\Http\Controllers;

use App\Domain\Patient\PatientService;
use App\Domain\Support\ClinicRegistry;
use App\Domain\Support\MetricFilter;
use App\Domain\Support\ProcCode;
use App\Domain\Support\ProcStatus;
use App\Domain\TreatmentAcceptance\TreatmentAcceptanceService;
use App\Models\Office;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpisController extends Controller
{
    /** Pre-rendered status IN-lists for interpolation into raw-SQL heredocs (DRY). */
    private readonly string $completedIn;

    private readonly string $tpIn;

    public function __construct(
        private readonly TreatmentAcceptanceService $txAcceptance,
        private readonly PatientService $patients,
    ) {
        $this->completedIn = ProcStatus::inList(ProcStatus::completed());
        $this->tpIn = ProcStatus::inList(ProcStatus::treatmentPlanned());
    }

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

    private function concatPatDate(string $patCol = 'PatNum', string $dateCol = 'ProcDate'): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "({$patCol} || '-' || {$dateCol})"
            : "CONCAT({$patCol}, '-', {$dateCol})";
    }

    private function dateSubMonths(int $months): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "date('now', '-{$months} month')"
            : "DATE_SUB(CURDATE(), INTERVAL {$months} MONTH)";
    }

    private function ageSql(string $birthdateCol): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "CAST((strftime('%Y', 'now') - strftime('%Y', {$birthdateCol})) AS INT)"
            : "TIMESTAMPDIFF(YEAR, {$birthdateCol}, CURDATE())";
    }

    public function hygieneKpis(string $start, string $end, ?int $officeId = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $patDate = $this->concatPatDate('pl.PatNum', 'pl.ProcDate');
        $patTpDate = $this->concatPatDate('PatNum', 'DateTP');
        $agePt = $this->ageSql('pt.Birthdate');
        $ageRaw = $this->ageSql('Birthdate');
        $sub12 = $this->dateSubMonths(12);
        $sub6 = $this->dateSubMonths(6);

        // ① One scan for all per-procedure aggregates (replaces ~10 separate queries)
        $s = DB::selectOne("
            SELECT
                COALESCE(SUM(pl.ProcFee), 0)                                                           AS total_prod,
                COUNT(*)                                                                                AS total_procs,
                COUNT(DISTINCT pl.ProcDate)                                                             AS work_days,
                COUNT(DISTINCT {$patDate})                                                              AS visits,
                COUNT(DISTINCT CASE WHEN pc.ProcCode IN ('D4341','D4342','D4910','D4346','D4355')
                                    THEN {$patDate} END)                                                AS perio_visits,
                COUNT(DISTINCT CASE WHEN pc.ProcCode IN ('D1206','D1208','1206','1208') 
                                    THEN pl.PatNum END)                                                AS fluoride_count,
                COUNT(DISTINCT CASE WHEN pc.ProcCode IN ('D4341','D4342','4341','4342') 
                                    THEN {$patDate} END)                                                AS srp_count,
                COUNT(DISTINCT CASE WHEN pc.ProcCode IN ('D0210','0210') 
                                    THEN pl.PatNum END)                                                AS fmx_count,
                SUM(CASE WHEN pc.ProcCode IN ('D1351') THEN 1 ELSE 0 END)                               AS sealants,
                SUM(CASE WHEN pc.ProcCode IN ('D9972','D9973','D9974','D9975') THEN 1 ELSE 0 END)       AS whitening,
                SUM(CASE WHEN pc.ProcCode IN ('D4381') THEN 1 ELSE 0 END)                               AS antimicrobial
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            WHERE pl.office_id = ?
              AND pc.IsHygiene = 'true'
              AND pl.ProcStatus IN ({$this->completedIn})
              AND pl.ProcDate BETWEEN ? AND ?
        ", [$officeId, $officeId, $start, $end]);

        $hygProd = (float) $s->total_prod;
        $hygCount = (int) $s->total_procs;
        $workDays = (int) $s->work_days;
        $hygVisits = (int) $s->visits;

        // ② Case Acceptance — single source of truth (blueprint D4-A)
        $caRates = $this->txAcceptance->summary(new MetricFilter($start, $end, [], [], true, $officeId));

        // ③ Reappointment rate — single JOIN, no PHP-side array
        $rapt = DB::selectOne("
            SELECT
                COUNT(DISTINCT a.AptNum) AS total,
                COUNT(DISTINCT CASE WHEN a.NextAptNum IS NOT NULL AND a.NextAptNum != '0'
                                    THEN a.AptNum END)              AS with_next
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            JOIN od_appointments a ON pl.AptNum = a.AptNum AND a.office_id = ?
            WHERE pl.office_id = ?
              AND pc.IsHygiene = 'true'
              AND pl.ProcStatus IN ({$this->completedIn})
              AND pl.ProcDate BETWEEN ? AND ?
              AND pl.AptNum IS NOT NULL AND pl.AptNum != '0'
        ", [$officeId, $officeId, $officeId, $start, $end]);
        $reapptRate = $rapt->total > 0 ? round($rapt->with_next / $rapt->total * 100, 2) : 0;

        // ④ Perio reappointment — same pattern
        $prapt = DB::selectOne("
            SELECT
                COUNT(DISTINCT a.AptNum) AS total,
                COUNT(DISTINCT CASE WHEN a.NextAptNum IS NOT NULL AND a.NextAptNum != '0'
                                    THEN a.AptNum END)              AS with_next
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            JOIN od_appointments a ON pl.AptNum = a.AptNum AND a.office_id = ?
            WHERE pl.office_id = ?
              AND pc.ProcCode IN ('D4341','D4342','D4910','4341','4342','4910')
              AND pl.ProcStatus IN ({$this->completedIn})
              AND pl.ProcDate BETWEEN ? AND ?
              AND pl.AptNum IS NOT NULL AND pl.AptNum != '0'
        ", [$officeId, $officeId, $officeId, $start, $end]);
        $perioReapptRate = $prapt->total > 0 ? round($prapt->with_next / $prapt->total * 100, 2) : 0;

        // ⑤ Active adults + children — single query
        $ageCounts = DB::selectOne("
            SELECT
                COUNT(CASE WHEN {$ageRaw} >= 18 THEN 1 END) AS adults,
                COUNT(CASE WHEN {$ageRaw} <  18 THEN 1 END) AS children
            FROM od_patients WHERE office_id = ? AND PatStatus = 'Patient'
        ", [$officeId]);
        $activeAdults = (int) ($ageCounts->adults ?? 0);
        $activeChildren = (int) ($ageCounts->children ?? 0);

        // ⑥ Retention — 2 queries instead of 4 (each returns adult + child in one pass)
        $ret12 = DB::selectOne("
            SELECT
                COUNT(DISTINCT CASE WHEN {$agePt} >= 18
                                    THEN pl.PatNum END) AS adult,
                COUNT(DISTINCT CASE WHEN {$agePt} <  18
                                    THEN pl.PatNum END) AS child
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            JOIN od_patients pt   ON pl.PatNum  = pt.PatNum AND pt.office_id = ?
            WHERE pl.office_id = ?
              AND pc.IsHygiene = 'true' AND pl.ProcStatus IN ({$this->completedIn})
              AND pt.PatStatus = 'Patient'
              AND pl.ProcDate >= {$sub12}
        ", [$officeId, $officeId, $officeId]);
        $ret6 = DB::selectOne("
            SELECT
                COUNT(DISTINCT CASE WHEN {$agePt} >= 18
                                    THEN pl.PatNum END) AS adult,
                COUNT(DISTINCT CASE WHEN {$agePt} <  18
                                    THEN pl.PatNum END) AS child
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            JOIN od_patients pt   ON pl.PatNum  = pt.PatNum AND pt.office_id = ?
            WHERE pl.office_id = ?
              AND pc.IsHygiene = 'true' AND pl.ProcStatus IN ({$this->completedIn})
              AND pt.PatStatus = 'Patient'
              AND pl.ProcDate >= {$sub6}
        ", [$officeId, $officeId, $officeId]);

        // ⑦ Visits with TX plan — JOIN instead of correlated EXISTS
        $visitsWithTx = (int) (DB::selectOne("
            SELECT COUNT(DISTINCT {$patDate}) AS cnt
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            JOIN (
                SELECT DISTINCT PatNum
                FROM od_procedure_logs
                WHERE office_id = ? AND ProcStatus IN ({$this->tpIn}) AND ProcDate BETWEEN ? AND ?
            ) tp ON pl.PatNum = tp.PatNum
            WHERE pl.office_id = ?
              AND pc.IsHygiene = 'true' AND pl.ProcStatus IN ({$this->completedIn})
              AND pl.ProcDate BETWEEN ? AND ?
        ", [$officeId, $officeId, $start, $end, $officeId, $start, $end])->cnt ?? 0);

        // ⑧ TX plans per day
        $txPlanCount = (int) (DB::selectOne("
            SELECT COUNT(DISTINCT {$patTpDate}) AS cnt
            FROM od_procedure_logs
            WHERE office_id = ?
              AND ProcStatus IN ({$this->tpIn})
              AND DateTP IS NOT NULL
              AND DateTP BETWEEN ? AND ?
        ", [$officeId, $start, $end])->cnt ?? 0);

        // ⑨ Avg prod per provider per day (must stay as grouped query)
        $hygProviders = DB::select("
            SELECT pl.ProvNum,
                   SUM(pl.ProcFee)          AS prod,
                   COUNT(DISTINCT pl.ProcDate) AS days
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            WHERE pl.office_id = ?
              AND pc.IsHygiene = 'true' AND pl.ProcStatus IN ({$this->completedIn})
              AND pl.ProcDate BETWEEN ? AND ?
            GROUP BY pl.ProvNum
        ", [$officeId, $officeId, $start, $end]);

        $avgProvProdPerDay = 0;
        if (count($hygProviders) > 0) {
            $sum = array_sum(array_map(fn ($p) => $p->days > 0 ? $p->prod / $p->days : 0, $hygProviders));
            $avgProvProdPerDay = round($sum / count($hygProviders), 2);
        }

        // ⑩ Avg prod per hour via appointment pattern
        $totalMins = (float) (DB::selectOne("
            SELECT COALESCE(SUM(LENGTH(a.Pattern) * 5), 0) AS mins
            FROM od_procedure_logs pl
            JOIN od_procedures pc    ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            JOIN od_appointments a   ON pl.AptNum  = a.AptNum AND a.office_id = ?
            WHERE pl.office_id = ?
              AND pc.IsHygiene = 'true' AND pl.ProcStatus IN ({$this->completedIn})
              AND pl.ProcDate BETWEEN ? AND ?
              AND pl.AptNum IS NOT NULL AND pl.AptNum != '0'
              AND a.Pattern IS NOT NULL AND a.Pattern != ''
        ", [$officeId, $officeId, $officeId, $start, $end])->mins ?? 0);

        return [
            'perio_pct' => $hygVisits > 0 ? round(($s->perio_visits ?? 0) / $hygVisits * 100, 2) : 0,
            'fluoride_per_day' => $workDays > 0 ? round(($s->fluoride_count ?? 0) / $workDays, 2) : 0,
            'avg_prod_per_day' => $workDays > 0 ? round($hygProd / $workDays, 2) : 0,
            'avg_prod_per_prov_day' => $avgProvProdPerDay,
            'prod_per_visit' => $hygVisits > 0 ? round($hygProd / $hygVisits, 2) : 0,
            'fmx_per_day' => $workDays > 0 ? round(($s->fmx_count ?? 0) / $workDays, 2) : 0,
            'srp_per_day' => $workDays > 0 ? round(($s->srp_count ?? 0) / $workDays, 2) : 0,
            'visits_per_day' => $workDays > 0 ? round($hygVisits / $workDays, 2) : 0,
            'reappt' => $reapptRate,
            'perio_reappt' => $perioReapptRate,
            'adult_retention_12m' => $activeAdults > 0 ? round(($ret12->adult ?? 0) / $activeAdults * 100, 2) : 0,
            'adult_retention_6m' => $activeAdults > 0 ? round(($ret6->adult ?? 0) / $activeAdults * 100, 2) : 0,
            'child_retention_12m' => $activeChildren > 0 ? round(($ret12->child ?? 0) / $activeChildren * 100, 2) : 0,
            'child_retention_6m' => $activeChildren > 0 ? round(($ret6->child ?? 0) / $activeChildren * 100, 2) : 0,
            'sealants' => (int) ($s->sealants ?? 0),
            'whitening' => (int) ($s->whitening ?? 0),
            'antimicrobial' => (int) ($s->antimicrobial ?? 0),
            'prod_per_proc' => $hygCount > 0 ? round($hygProd / $hygCount, 2) : 0,
            'visits_with_tx_pct' => $hygVisits > 0 ? round($visitsWithTx / $hygVisits * 100, 2) : 0,
            'tx_plans_per_day' => $workDays > 0 ? round($txPlanCount / $workDays, 2) : 0,
            'avg_prod_per_hour' => $totalMins > 0 ? round($hygProd / ($totalMins / 60), 2) : 0,
            'case_acceptance' => $caRates->rate,
        ];
    }

    // ─── Doctor ──────────────────────────────────────────────────────────────

    public function doctorKpis(string $start, string $end, ?int $officeId = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $patDate = $this->concatPatDate('pl.PatNum', 'pl.ProcDate');

        // Total Production & Counts
        $doc = DB::selectOne("
            SELECT
                COALESCE(SUM(pl.ProcFee), 0) AS total_prod,
                COUNT(*) AS total_procs,
                COUNT(DISTINCT pl.ProcDate) AS work_days,
                COUNT(DISTINCT {$patDate}) AS visits
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            WHERE pl.office_id = ?
              AND pc.IsHygiene = 'false'
              AND pl.ProcStatus IN ({$this->completedIn})
              AND pl.ProcDate BETWEEN ? AND ?
        ", [$officeId, $officeId, $start, $end]);

        $docProd = (float) ($doc->total_prod ?? 0);
        $workDays = (int) ($doc->work_days ?? 0);
        $docVisits = (int) ($doc->visits ?? 0);

        // Appointment Time & Count
        $apts = DB::selectOne("
            SELECT 
                COUNT(DISTINCT a.AptNum) AS total_apts,
                AVG(LENGTH(a.Pattern) * 5) AS avg_mins,
                SUM(LENGTH(a.Pattern) * 5) / 60 AS total_hours
            FROM od_appointments a
            JOIN od_procedure_logs pl ON a.AptNum = pl.AptNum AND pl.office_id = ?
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            WHERE a.office_id = ?
              AND a.AptStatus = 2
              AND pc.IsHygiene = 'false'
              AND pl.ProcStatus IN ({$this->completedIn})
              AND a.Pattern IS NOT NULL AND a.Pattern != ''
              AND DATE(a.AptDateTime) BETWEEN ? AND ?
        ", [$officeId, $officeId, $officeId, $start, $end]);

        $docAptCount = (int) ($apts->total_apts ?? 0);
        $avgAptMins = (float) ($apts->avg_mins ?? 0);
        $totalHours = (float) ($apts->total_hours ?? 0);

        // Case Acceptance — single source of truth (blueprint D4-A)
        $caRates = $this->txAcceptance->summary(new MetricFilter($start, $end, [], [], false, $officeId));

        $docReappt = DB::selectOne("
            SELECT 
                COUNT(DISTINCT a.PatNum) AS total,
                COUNT(DISTINCT CASE WHEN a.NextAptNum IS NOT NULL AND a.NextAptNum != '0' THEN a.PatNum END) AS with_next
            FROM od_appointments a
            WHERE a.office_id = ?
              AND a.IsHygiene = 'false' 
              AND a.AptStatus = 2
              AND DATE(a.AptDateTime) BETWEEN ? AND ?
        ", [$officeId, $start, $end]);

        $examCount = (int) (DB::selectOne("
            SELECT COUNT(DISTINCT pl.ProcNum) AS exam_cnt
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            WHERE pl.office_id = ?
              AND pl.ProcStatus IN ({$this->completedIn}) 
              AND pc.IsHygiene = 'false'
              AND pl.ProcDate BETWEEN ? AND ?
              AND pc.ProcCode IN ('D0120', 'D0140', 'D0150', 'D0160', 'D0170', 'D0180')
        ", [$officeId, $officeId, $start, $end])->exam_cnt ?? 0);

        // Advanced New/Existing & SameDay aggregation matrix
        $cohortSql = $this->patients->firstVisitCohortSql('first_visit', $officeId);
        $txMatrix = DB::selectOne("
            SELECT
                COUNT(DISTINCT CASE WHEN pl.ProcStatus IN ({$this->tpIn}) AND tp_same.same_day_completed = 1 THEN {$patDate} END) AS same_day_tp_accepted_cnt,
                COUNT(DISTINCT CASE WHEN pl.ProcStatus IN ({$this->tpIn}) THEN {$patDate} END) AS total_tp_presented_cnt,

                COALESCE(SUM(CASE WHEN pt_hist.first_visit BETWEEN ? AND ? AND pl.ProcStatus IN ({$this->tpIn}) THEN pl.ProcFee ELSE 0 END), 0) AS total_new_pt_tp_dollars,
                COUNT(DISTINCT CASE WHEN pt_hist.first_visit BETWEEN ? AND ? AND pl.ProcStatus IN ({$this->tpIn}) THEN pl.PatNum END) AS new_pts_with_tp_cnt,

                COALESCE(SUM(CASE WHEN pt_hist.first_visit < ? AND pl.ProcStatus IN ({$this->tpIn}) THEN pl.ProcFee ELSE 0 END), 0) AS total_existing_pt_tp_dollars,

                COALESCE(SUM(CASE WHEN pt_hist.first_visit BETWEEN ? AND ? AND pl.ProcStatus IN ({$this->completedIn}) AND tp_same.same_day_completed = 1 THEN pl.ProcFee ELSE 0 END), 0) AS sameday_new_pt_tx_dollars,
                COUNT(DISTINCT CASE WHEN pt_hist.first_visit BETWEEN ? AND ? AND pl.ProcStatus IN ({$this->completedIn}) AND tp_same.same_day_completed = 1 THEN pl.PatNum END) AS sameday_new_pt_cnt,

                COUNT(DISTINCT CASE WHEN pt_hist.first_visit < ? AND pl.ProcStatus IN ({$this->tpIn}) THEN pl.PatNum END) AS existing_pts_with_tp_cnt,

                COUNT(DISTINCT CASE WHEN pt_hist.first_visit BETWEEN ? AND ? AND pl.ProcStatus IN ({$this->completedIn}) THEN pt_hist.PatNum END) AS new_pts_seen_cnt,
                COUNT(DISTINCT CASE WHEN pt_hist.first_visit < ? AND pl.ProcStatus IN ({$this->completedIn}) THEN pt_hist.PatNum END) AS existing_pts_seen_cnt
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            JOIN (
                {$cohortSql}
            ) pt_hist ON pl.PatNum = pt_hist.PatNum
            LEFT JOIN (
                SELECT DISTINCT c.PatNum, c.ProcDate, 1 AS same_day_completed
                FROM od_procedure_logs c
                JOIN od_procedure_logs tp ON c.PatNum = tp.PatNum AND c.ProcDate = tp.DateTP AND tp.office_id = ?
                WHERE c.office_id = ? AND c.ProcStatus IN ({$this->completedIn}) AND tp.ProcStatus IN ({$this->tpIn})
            ) tp_same ON pl.PatNum = tp_same.PatNum AND pl.ProcDate = tp_same.ProcDate
            WHERE pl.office_id = ?
              AND pc.IsHygiene = 'false'
              AND pl.ProcDate BETWEEN ? AND ?
        ", [
            $start, $end,
            $start, $end,
            $start,
            $start, $end,
            $start, $end,
            $start,
            $start, $end,
            $start,
            $officeId,
            $officeId,
            $officeId,
            $officeId,
            $start, $end,
        ]);

        $avgProvProdPerDay = 0;
        $docProviders = DB::select("
            SELECT pl.ProvNum, SUM(pl.ProcFee) as prod, COUNT(DISTINCT pl.ProcDate) as days
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            WHERE pl.office_id = ?
              AND pc.IsHygiene = 'false' AND pl.ProcStatus IN ({$this->completedIn}) AND pl.ProcDate BETWEEN ? AND ?
            GROUP BY pl.ProvNum
        ", [$officeId, $officeId, $start, $end]);
        if (count($docProviders) > 0) {
            $sum = array_sum(array_map(fn ($p) => $p->days > 0 ? $p->prod / $p->days : 0, $docProviders));
            $avgProvProdPerDay = round($sum / count($docProviders), 2);
        }

        $sameDayCaPct = ($txMatrix->total_tp_presented_cnt ?? 0) > 0
            ? round(($txMatrix->same_day_tp_accepted_cnt / $txMatrix->total_tp_presented_cnt) * 100, 2)
            : ($docVisits > 0 ? round((($txMatrix->same_day_tp_accepted_cnt ?? 0) / $docVisits) * 100, 2) : 0);

        return [
            'case_acceptance_same_day' => $sameDayCaPct,
            'case_acceptance_rate' => $caRates->rate,
            'new_pt_tx_dollars' => ($txMatrix->new_pts_with_tp_cnt ?? 0) > 0 ? round($txMatrix->total_new_pt_tp_dollars / $txMatrix->new_pts_with_tp_cnt, 2) : 0,
            'existing_pt_tx_dollars' => round((float) ($txMatrix->total_existing_pt_tp_dollars ?? 0), 2),
            'avg_apt_time_mins' => round($avgAptMins, 2),
            'avg_prod_per_hour' => $totalHours > 0 ? round($docProd / $totalHours, 2) : 0,
            'avg_prod_per_apt' => $docAptCount > 0 ? round($docProd / $docAptCount, 2) : 0,
            'same_day_tx_per_new_pt' => ($txMatrix->sameday_new_pt_cnt ?? 0) > 0 ? round($txMatrix->sameday_new_pt_tx_dollars / $txMatrix->sameday_new_pt_cnt, 2) : 0,
            'avg_prod_per_prov_day' => $avgProvProdPerDay,
            'avg_tx_per_existing_pt' => ($txMatrix->existing_pts_with_tp_cnt ?? 0) > 0 ? round($txMatrix->total_existing_pt_tp_dollars / $txMatrix->existing_pts_with_tp_cnt, 2) : 0,
            'avg_tx_per_new_pt' => ($txMatrix->new_pts_with_tp_cnt ?? 0) > 0 ? round($txMatrix->total_new_pt_tp_dollars / $txMatrix->new_pts_with_tp_cnt, 2) : 0,
            'pct_new_pt_with_tx' => ($txMatrix->new_pts_seen_cnt ?? 0) > 0 ? round(($txMatrix->new_pts_with_tp_cnt / $txMatrix->new_pts_seen_cnt) * 100, 2) : 0,
            'pct_existing_pt_with_tx' => ($txMatrix->existing_pts_seen_cnt ?? 0) > 0 ? round(($txMatrix->existing_pts_with_tp_cnt / $txMatrix->existing_pts_seen_cnt) * 100, 2) : 0,
            'reappt' => ($docReappt->total ?? 0) > 0 ? round(($docReappt->with_next / $docReappt->total) * 100, 2) : 0,
            'prod_per_exam' => $examCount > 0 ? round($docProd / $examCount, 2) : 0,
            'total_production' => round($docProd, 2),
        ];
    }

    // ─── Office ──────────────────────────────────────────────────────────────

    public function officeKpis(string $start, string $end, ?int $officeId = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $cutoff36m = now()->subMonths(36)->toDateString();
        $cutoff18m = now()->subMonths(18)->toDateString();
        $cutoff12m = now()->subMonths(12)->toDateString();
        $prior12m = date('Y-m-d', strtotime('-12 months', strtotime($start)));
        $prior18m = date('Y-m-d', strtotime('-18 months', strtotime($start)));

        $patDate = $this->concatPatDate('pl.PatNum', 'pl.ProcDate');
        $patTpDate = $this->concatPatDate('PatNum', 'DateTP');

        // 1 & 10. Patient Retention + Active Patients
        $retentionData = DB::selectOne("
            SELECT
                COUNT(DISTINCT CASE WHEN pl.ProcDate >= ? AND pc.ProcCode NOT IN ('D9986', 'D9987') THEN pl.PatNum END) AS active_18m,
                COUNT(DISTINCT CASE WHEN pl.ProcDate BETWEEN ? AND ? AND pc.ProcCode NOT IN ('D9986', 'D9987') THEN pl.PatNum END) AS active_36m_prior
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            WHERE pl.office_id = ?
              AND pl.ProcStatus IN ({$this->completedIn})
        ", [$cutoff18m, $cutoff36m, $cutoff18m, $officeId, $officeId]);

        $activePatients = (int) ($retentionData->active_18m ?? 0);
        $priorActivePatients = (int) ($retentionData->active_36m_prior ?? 0);

        // New patients in last 18 months
        $excludedCodes = ProcCode::brokenAppointmentCodeNums($officeId);
        $newPatientsCount = DB::table('od_procedure_logs as pl')
            ->where('pl.office_id', $officeId)
            ->whereIn('pl.ProcStatus', [2, '2', 'C'])
            ->whereNotIn(DB::raw("COALESCE(pl.CodeNum, '')"), $excludedCodes)
            ->selectRaw('pl.PatNum, MIN(pl.ProcDate) as first_date')
            ->groupBy('pl.PatNum')
            ->havingRaw('MIN(pl.ProcDate) >= ?', [$cutoff18m.' 00:00:00'])
            ->pluck('PatNum')
            ->count();

        $retainedPatients = max(0, $activePatients - $newPatientsCount);
        $patientRetention = $priorActivePatients > 0 ? round(($retainedPatients / $priorActivePatients) * 100, 2) : 0;

        // 2. Treatment Plans per Day
        $tpDays = DB::selectOne("
            SELECT
                (SELECT COUNT(DISTINCT {$patTpDate}) FROM od_procedure_logs WHERE office_id = ? AND ProcStatus IN ({$this->tpIn}) AND DateTP BETWEEN ? AND ? AND ProcFee > 10) AS tp_count,
                (SELECT COUNT(DISTINCT pl2.ProcDate) FROM od_procedure_logs pl2 WHERE pl2.office_id = ? AND pl2.ProcStatus IN ({$this->completedIn}) AND pl2.ProcDate BETWEEN ? AND ?) AS work_days
        ", [$officeId, $start, $end, $officeId, $start, $end]);
        $txPlansPerDay = ($tpDays->work_days ?? 0) > 0 ? round(($tpDays->tp_count ?? 0) / $tpDays->work_days, 2) : 0;

        // 3. Co-Pay Collection
        $procFee = (float) DB::table('od_procedure_logs')
            ->where('office_id', $officeId)
            ->whereIn('ProcStatus', ProcStatus::completed())
            ->whereBetween('ProcDate', [$start, $end])
            ->sum('ProcFee');

        $insEstimates = (float) DB::table('od_claim_procs')
            ->where('office_id', $officeId)
            ->whereBetween('ProcDate', [$start, $end])
            ->whereIn('Status', [0, 1, 4, 6])
            ->selectRaw('SUM(COALESCE(InsPayEst, InsEstTotal, 0) + COALESCE(CASE WHEN WriteOff > 0 THEN WriteOff ELSE WriteOffEst END, 0)) as ins_portion')
            ->value('ins_portion');

        $expectedPatPortion = max(0, $procFee - $insEstimates);

        $collectedPat = (float) DB::table('od_pay_splits')
            ->where('office_id', $officeId)
            ->whereBetween('DatePay', [$start, $end])
            ->sum('SplitAmt');

        $coPayCollection = $expectedPatPortion > 0 ? round(($collectedPat / $expectedPatPortion) * 100, 2) : 0;

        // 4. Unscheduled Tx
        $unscheduled = (float) (DB::selectOne("
            SELECT COALESCE(SUM(ProcFee), 0) AS val FROM od_procedure_logs
            WHERE office_id = ?
              AND ProcStatus IN ({$this->tpIn})
              AND ProcDate BETWEEN ? AND ?
              AND (AptNum IS NULL OR AptNum = 0 OR AptNum = '0')
              AND ProcFee > 0
        ", [$officeId, $start, $end])->val ?? 0);

        // 5 & 8 & 9. New Patients Fmx % // Attrition // Growth
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
                  AND pl.ProcStatus IN ({$this->completedIn})
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

        $newPtFmxPct = ($patientStats->new_pts ?? 0) > 0 ? round(($patientStats->new_pts_fmx / $patientStats->new_pts) * 100, 2) : 0;
        $attrition = (int) ($patientStats->attrition ?? 0);
        $growth = (int) ($patientStats->new_pts ?? 0) - $attrition;

        // 7. Reactivations
        $reactivationList = (int) (DB::selectOne("
            SELECT COUNT(DISTINCT pl.PatNum) AS cnt
            FROM od_procedure_logs pl
            WHERE pl.office_id = ? AND pl.ProcStatus IN ({$this->completedIn}) AND pl.ProcDate < ?
            GROUP BY pl.PatNum
            HAVING MAX(pl.ProcDate) < ?
        ", [$officeId, $start, $prior12m])->cnt ?? 0);

        // 6. No Show Rate
        $aptStats = DB::selectOne('
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN AptStatus = 5 THEN 1 ELSE 0 END) AS broken
            FROM od_appointments
            WHERE office_id = ?
              AND DATE(AptDateTime) BETWEEN ? AND ?
              AND AptStatus IN (1, 2, 5)
        ', [$officeId, $start, $end]);
        $noShowRate = ($aptStats->total ?? 0) > 0 ? round(($aptStats->broken / $aptStats->total) * 100, 2) : 0;

        // 11. Active In Recare
        $inRecare = (int) (DB::selectOne("
            SELECT COUNT(DISTINCT pl.PatNum) AS cnt
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            WHERE pl.office_id = ?
              AND pl.ProcStatus IN ({$this->completedIn})
              AND pl.ProcDate >= ?
              AND pc.ProcCode IN ('D4910','D1110','D1120')
              AND pl.PatNum IN (
                  SELECT DISTINCT p2.PatNum FROM od_procedure_logs p2 WHERE p2.office_id = ? AND p2.ProcStatus='C' AND p2.ProcDate >= ?
              )
        ", [$officeId, $officeId, $cutoff12m, $officeId, $cutoff18m])->cnt ?? 0);

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
        $officeId = Office::getActiveOfficeId();

        // Perio Specialty = 8
        $data = $this->getSpecialtyMetrics($start, $end, 8, [
            'total_consults' => ['D9310'],
            'implant_placement_count' => ['D6010'],
        ], [
            'implant_placement_dollars' => ['D6010'],
            'sedations_dollars' => ['D9222', 'D9223', 'D9239', 'D9243', 'D9248'],
        ]);

        // Perio Codes $ (D4000-D4999) - slightly different filter
        $data['perio_codes_dollars'] = round((float) (DB::selectOne("
            SELECT SUM(pl.ProcFee) AS sm
            FROM od_procedure_logs pl
            JOIN od_providers pr ON pl.ProvNum = pr.ProvNum AND pr.office_id = ?
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            WHERE pl.office_id = ? AND pl.ProcStatus IN ({$this->completedIn}) AND pl.ProcDate BETWEEN ? AND ? AND pr.Specialty = 8
              AND pc.ProcCode BETWEEN 'D4000' AND 'D4999'
        ", [$officeId, $officeId, $officeId, $start, $end])->sm ?? 0), 2);

        // Treatment plan per exam
        $txFee = DB::selectOne("SELECT SUM(ProcFee) AS sm FROM od_procedure_logs WHERE office_id = ? AND ProcStatus IN ({$this->tpIn}) AND DateTP BETWEEN ? AND ?", [$officeId, $start, $end])->sm ?? 0;

        $exams = DB::selectOne("
            SELECT COUNT(DISTINCT pl.ProcNum) AS exam_cnt
            FROM od_procedure_logs pl
            JOIN od_providers pr ON pl.ProvNum = pr.ProvNum AND pr.office_id = ?
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            WHERE pl.office_id = ?
              AND pl.ProcDate BETWEEN ? AND ?
              AND pc.ProcCode IN ('D0120', 'D0140', 'D0145', 'D0150', 'D0160', 'D0170', 'D0180')
              AND pl.ProcStatus IN ({$this->completedIn}) AND pr.Specialty = 8
        ", [$officeId, $officeId, $officeId, $start, $end])->exam_cnt ?? 0;

        $data['treatment_plan_per_exam'] = $exams > 0 ? round($txFee / $exams, 2) : 0;

        return response()->json($data);
    }

    public function ortho(Request $request)
    {
        $start = $request->input('start_date', date('Y-m-01'));
        $end = $request->input('end_date', date('Y-m-d'));
        $officeId = Office::getActiveOfficeId();

        // Ortho = 6
        $data = $this->getSpecialtyMetrics($start, $end, 6, [
            'total_consults' => ['D9310'],
            'appliances_count' => ['D8220', 'D8210'],
            'phase_1_count' => ['D8010', 'D8020', 'D8030', 'D8040', 'D8050', 'D8060'],
            'comprehensive_starts_count' => ['D8070', 'D8080', 'D8090'],
            'debonds_count' => ['D8999C'],
            'invisalign_starts_count' => ['D8090', 'D8080'],
        ], [], [
            'total_active_patients_seen' => ['D8670', 'D8670A'],
        ]);

        $data['active_patients_seen_per_day'] = $data['work_days'] > 0 ? round($data['total_active_patients_seen'] / $data['work_days'], 2) : 0;

        $starts = DB::selectOne("
            SELECT COUNT(*) AS cnt FROM od_procedure_logs pl 
            JOIN od_providers pr ON pl.ProvNum = pr.ProvNum AND pr.office_id = ?
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            WHERE pl.office_id = ? AND pl.ProcStatus='C' AND pl.ProcDate BETWEEN ? AND ? AND pr.Specialty=6
            AND pc.ProcCode IN ('D8010','D8020','D8030','D8040','D8050','D8060','D8070','D8080','D8090')
        ", [$officeId, $officeId, $officeId, $start, $end])->cnt ?? 0;

        $data['conversion'] = $data['total_consults'] > 0 ? round(($starts / $data['total_consults']) * 100, 2) : 0;

        return response()->json($data);
    }

    public function os(Request $request)
    {
        $start = $request->input('start_date', date('Y-m-01'));
        $end = $request->input('end_date', date('Y-m-d'));
        $officeId = Office::getActiveOfficeId();

        // OS = 5
        $data = $this->getSpecialtyMetrics($start, $end, 5, [
            'total_consults' => ['D9310'],
            'implant_placement_count' => ['D6010'],
        ], [
            'implant_placement_dollars' => ['D6010'],
            'sedations_dollars' => ['D9222', 'D9223', 'D9239', 'D9243', 'D9248'],
            'extractions_dollars' => ['D7140', 'D7210', 'D7220', 'D7230', 'D7240', 'D7241', 'D7250'],
        ]);

        $txFee = DB::selectOne("SELECT SUM(ProcFee) AS sm FROM od_procedure_logs WHERE office_id = ? AND ProcStatus IN ({$this->tpIn}) AND DateTP BETWEEN ? AND ?", [$officeId, $start, $end])->sm ?? 0;

        $exams = DB::selectOne("
            SELECT COUNT(DISTINCT pl.ProcNum) AS exam_cnt
            FROM od_procedure_logs pl
            JOIN od_providers pr ON pl.ProvNum = pr.ProvNum AND pr.office_id = ?
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            WHERE pl.office_id = ?
              AND pl.ProcDate BETWEEN ? AND ?
              AND pc.ProcCode IN ('D0120', 'D0140', 'D0145', 'D0150', 'D0160', 'D0170', 'D0180')
              AND pl.ProcStatus IN ({$this->completedIn}) AND pr.Specialty = 5
        ", [$officeId, $officeId, $officeId, $start, $end])->exam_cnt ?? 0;

        $data['treatment_plan_per_exam'] = $exams > 0 ? round($txFee / $exams, 2) : 0;

        return response()->json($data);
    }

    public function pedo(Request $request)
    {
        $start = $request->input('start_date', date('Y-m-01'));
        $end = $request->input('end_date', date('Y-m-d'));
        $officeId = Office::getActiveOfficeId();

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
            'sedations' => ['D9220', 'D9221', 'D9230', 'D9612', 'D9243', 'D9239'],
        ]);

        $data['patients_per_day'] = $data['work_days'] > 0 ? round($data['patient_visits'] / $data['work_days'], 1) : 0;
        $data['production_per_patient'] = $data['patient_visits'] > 0 ? round($data['total_production'] / $data['patient_visits'], 2) : 0;

        $workDays100 = DB::selectOne("
            SELECT COUNT(DISTINCT DATE(pl.ProcDate)) AS wd 
            FROM (
                SELECT pl.ProcDate, SUM(pl.ProcFee) AS daily_prod 
                FROM od_procedure_logs pl 
                JOIN od_providers pr ON pl.ProvNum = pr.ProvNum AND pr.office_id = ?
                WHERE pl.office_id = ? AND pl.ProcStatus='C' AND pl.ProcDate BETWEEN ? AND ? AND pr.Specialty=7
                GROUP BY pl.ProcDate HAVING daily_prod > 100
            ) pl
        ", [$officeId, $officeId, $start, $end]);
        $data['total_working_days'] = $workDays100->wd ?? 0;

        // Acceptance
        $tx = DB::selectOne("
            SELECT COUNT(DISTINCT tp.PatNum) as presented,
            COUNT(DISTINCT CASE WHEN DATEDIFF(pl.ProcDate, tp.DateTP) = 0 THEN tp.PatNum END) as same_day,
            COUNT(DISTINCT CASE WHEN DATEDIFF(pl.ProcDate, tp.DateTP) <= 90 THEN tp.PatNum END) as rolling
            FROM treatment_plans tp
            LEFT JOIN od_procedure_logs pl ON tp.PatNum = pl.PatNum 
                AND pl.office_id = ?
                AND pl.ProcStatus IN ('T', 'C') 
                AND pl.ProvNum IN (SELECT ProvNum FROM od_providers WHERE office_id = ? AND Specialty=7)
                AND pl.ProcDate >= tp.DateTP
            WHERE tp.office_id = ? AND tp.DateTP BETWEEN ? AND ?
        ", [$officeId, $officeId, $officeId, $start, $end]);

        $data['case_acceptance_same_day'] = ($tx->presented ?? 0) > 0 ? round(($tx->same_day / $tx->presented) * 100, 2) : 0;
        $data['case_acceptance_rolling_90_days'] = ($tx->presented ?? 0) > 0 ? round(($tx->rolling / $tx->presented) * 100, 2) : 0;

        return response()->json($data);
    }

    private function getSpecialtyMetrics($start, $end, $specId, $customCounts = [], $customSums = [], $distinctPatNumCounts = [])
    {
        $officeId = Office::getActiveOfficeId();
        $base = DB::selectOne("
            SELECT
                SUM(pl.ProcFee) AS total_prod,
                CAST(COUNT(DISTINCT CASE WHEN pl.ProcFee > 0 THEN DATE(pl.ProcDate) END) AS SIGNED) AS work_days,
                CAST(COUNT(DISTINCT pl.PatNum) AS SIGNED) AS patient_visits
            FROM od_procedure_logs pl
            JOIN od_providers pr ON pl.ProvNum = pr.ProvNum AND pr.office_id = ?
            WHERE pl.office_id = ?
              AND pl.ProcStatus IN ({$this->completedIn})
              AND pl.ProcDate BETWEEN ? AND ?
              AND pr.Specialty = ?
        ", [$officeId, $officeId, $start, $end, $specId]);

        $workDays = max(1, (int) ($base->work_days ?? 1));
        $res = [
            'total_production' => round($base->total_prod ?? 0, 2),
            'production_per_day' => round(($base->total_prod ?? 0) / $workDays, 2),
            'patient_visits' => (int) ($base->patient_visits ?? 0),
            'work_days' => $workDays,
        ];

        foreach ($customCounts as $key => $codes) {
            $inStr = "'".implode("','", $codes)."'";
            $res[$key] = (int) (DB::selectOne("
                SELECT COUNT(DISTINCT pl.ProcNum) AS cnt
                FROM od_procedure_logs pl
                JOIN od_providers pr ON pl.ProvNum = pr.ProvNum AND pr.office_id = ?
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
                WHERE pl.office_id = ? AND pl.ProcStatus IN ({$this->completedIn}) AND pl.ProcDate BETWEEN ? AND ? AND pr.Specialty = ?
                  AND pc.ProcCode IN ($inStr)
            ", [$officeId, $officeId, $officeId, $start, $end, $specId])->cnt ?? 0);
        }

        foreach ($customSums as $key => $codes) {
            $inStr = "'".implode("','", $codes)."'";
            $res[$key] = round((float) (DB::selectOne("
                SELECT SUM(pl.ProcFee) AS sm
                FROM od_procedure_logs pl
                JOIN od_providers pr ON pl.ProvNum = pr.ProvNum AND pr.office_id = ?
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
                WHERE pl.office_id = ? AND pl.ProcStatus IN ({$this->completedIn}) AND pl.ProcDate BETWEEN ? AND ? AND pr.Specialty = ?
                  AND pc.ProcCode IN ($inStr)
            ", [$officeId, $officeId, $officeId, $start, $end, $specId])->sm ?? 0), 2);
        }

        foreach ($distinctPatNumCounts as $key => $codes) {
            $inStr = "'".implode("','", $codes)."'";
            $res[$key] = (int) (DB::selectOne("
                SELECT COUNT(DISTINCT pl.PatNum) AS cnt
                FROM od_procedure_logs pl
                JOIN od_providers pr ON pl.ProvNum = pr.ProvNum AND pr.office_id = ?
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
                WHERE pl.office_id = ? AND pl.ProcStatus IN ({$this->completedIn}) AND pl.ProcDate BETWEEN ? AND ? AND pr.Specialty = ?
                  AND pc.ProcCode IN ($inStr)
            ", [$officeId, $officeId, $officeId, $start, $end, $specId])->cnt ?? 0);
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
        $officeId = Office::getActiveOfficeId();

        // We fetch the overall to get the 'avg' and 'total' rows.
        $overall = $this->hygieneKpis($start, $end, $officeId);

        // Fetch distinct providers who have hygiene production
        $provs = DB::select("
            SELECT DISTINCT pl.ProvNum, pr.Abbr, pr.LName,
                   'Unassigned' as Location
            FROM od_procedure_logs pl
            JOIN od_providers pr ON pl.ProvNum = pr.ProvNum AND pr.office_id = ?
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            WHERE pl.office_id = ?
              AND pc.IsHygiene = 'true' AND pl.ProcStatus IN ({$this->completedIn}) AND pl.ProcDate BETWEEN ? AND ?
        ", [$officeId, $officeId, $officeId, $start, $end]);

        $providersList = [];

        foreach ($provs as $p) {
            $pId = $p->ProvNum;

            $patDate = $this->concatPatDate('pl.PatNum', 'pl.ProcDate');
            $patTpDate = $this->concatPatDate('PatNum', 'DateTP');
            $agePt = $this->ageSql('pt.Birthdate');

            // ① Core
            $s = DB::selectOne("
                SELECT
                    COALESCE(SUM(pl.ProcFee), 0) AS total_prod,
                    COUNT(*) AS total_procs,
                    COUNT(DISTINCT pl.ProcDate) AS work_days,
                    COUNT(DISTINCT {$patDate}) AS visits,
                    COUNT(DISTINCT CASE WHEN pc.ProcCode IN ('D4341','D4342','D4910','D4346','D4355') THEN {$patDate} END) AS perio_visits,
                    COUNT(DISTINCT CASE WHEN pc.ProcCode IN ('D1206','D1208','1206','1208') THEN pl.PatNum END) AS fluoride_count,
                    COUNT(DISTINCT CASE WHEN pc.ProcCode IN ('D4341','D4342','4341','4342') THEN {$patDate} END) AS srp_count,
                    COUNT(DISTINCT CASE WHEN pc.ProcCode IN ('D0210','0210') THEN pl.PatNum END) AS fmx_count,
                    SUM(CASE WHEN pc.ProcCode IN ('D1351') THEN 1 ELSE 0 END) AS sealants,
                    SUM(CASE WHEN pc.ProcCode IN ('D9972','D9973','D9974','D9975') THEN 1 ELSE 0 END) AS whitening,
                    SUM(CASE WHEN pc.ProcCode IN ('D4381') THEN 1 ELSE 0 END) AS antimicrobial
                FROM od_procedure_logs pl
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
                WHERE pl.office_id = ?
                  AND pc.IsHygiene = 'true' AND pl.ProcStatus IN ({$this->completedIn}) AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
            ", [$officeId, $officeId, $start, $end, $pId]);

            $hygProd = (float) ($s->total_prod ?? 0);
            $hygCount = (int) ($s->total_procs ?? 0);
            $workDays = (int) ($s->work_days ?? 0);
            $hygVisits = (int) ($s->visits ?? 0);

            // ② Case Acceptance — single source of truth (blueprint D4-A)
            $caRates = $this->txAcceptance->summary(new MetricFilter($start, $end, [], [$pId], true, $officeId));

            // ③ Reappointment
            $rapt = DB::selectOne("
                SELECT COUNT(DISTINCT a.AptNum) AS total, COUNT(DISTINCT CASE WHEN a.NextAptNum IS NOT NULL AND a.NextAptNum != '0' THEN a.AptNum END) AS with_next
                FROM od_procedure_logs pl
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
                JOIN od_appointments a ON pl.AptNum = a.AptNum AND a.office_id = ?
                WHERE pl.office_id = ?
                  AND pc.IsHygiene = 'true' AND pl.ProcStatus IN ({$this->completedIn}) AND pl.ProcDate BETWEEN ? AND ? AND pl.AptNum IS NOT NULL AND pl.AptNum != '0' AND pl.ProvNum = ?
            ", [$officeId, $officeId, $officeId, $start, $end, $pId]);
            $reapptRate = ($rapt->total ?? 0) > 0 ? round(($rapt->with_next ?? 0) / $rapt->total * 100, 2) : 0;

            // ④ Perio Reappointment
            $prapt = DB::selectOne("
                SELECT COUNT(DISTINCT a.AptNum) AS total, COUNT(DISTINCT CASE WHEN a.NextAptNum IS NOT NULL AND a.NextAptNum != '0' THEN a.AptNum END) AS with_next
                FROM od_procedure_logs pl
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
                JOIN od_appointments a ON pl.AptNum = a.AptNum AND a.office_id = ?
                WHERE pl.office_id = ?
                  AND pc.ProcCode IN ('D4341','D4342','D4910','4341','4342','4910') AND pl.ProcStatus IN ({$this->completedIn}) AND pl.ProcDate BETWEEN ? AND ? AND pl.AptNum IS NOT NULL AND pl.AptNum != '0' AND pl.ProvNum = ?
            ", [$officeId, $officeId, $officeId, $start, $end, $pId]);
            $perioReapptRate = ($prapt->total ?? 0) > 0 ? round(($prapt->with_next ?? 0) / $prapt->total * 100, 2) : 0;

            // Active Adults/Children
            $ret12 = DB::selectOne("
                SELECT
                    COUNT(DISTINCT CASE WHEN {$agePt} >= 18 THEN pl.PatNum END) AS adult,
                    COUNT(DISTINCT CASE WHEN {$agePt} <  18 THEN pl.PatNum END) AS child
                FROM od_procedure_logs pl
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
                JOIN od_patients pt ON pl.PatNum = pt.PatNum AND pt.office_id = ?
                WHERE pl.office_id = ?
                  AND pc.IsHygiene = 'true' AND pl.ProcStatus IN ({$this->completedIn}) AND pt.PatStatus = 'Patient' AND pl.ProcDate BETWEEN DATE_SUB(?, INTERVAL 12 MONTH) AND ? AND pl.ProvNum = ?
            ", [$officeId, $officeId, $officeId, $end, $end, $pId]);

            $ret6 = DB::selectOne("
                SELECT
                    COUNT(DISTINCT CASE WHEN {$agePt} >= 18 THEN pl.PatNum END) AS adult,
                    COUNT(DISTINCT CASE WHEN {$agePt} <  18 THEN pl.PatNum END) AS child
                FROM od_procedure_logs pl
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
                JOIN od_patients pt ON pl.PatNum = pt.PatNum AND pt.office_id = ?
                WHERE pl.office_id = ?
                  AND pc.IsHygiene = 'true' AND pl.ProcStatus IN ({$this->completedIn}) AND pt.PatStatus = 'Patient' AND pl.ProcDate BETWEEN DATE_SUB(?, INTERVAL 6 MONTH) AND ? AND pl.ProvNum = ?
            ", [$officeId, $officeId, $officeId, $end, $end, $pId]);

            $providersList[] = [
                'Location' => 'Main Office',
                'location' => 'Main Office',
                'Provider' => $p->Abbr.' '.$p->LName,
                'provider' => $p->Abbr.' '.$p->LName,
                'perio_pct' => $hygVisits > 0 ? round(($s->perio_visits ?? 0) / $hygVisits * 100, 2) : 0,
                'fluoride_per_day' => $workDays > 0 ? round(($s->fluoride_count ?? 0) / $workDays, 2) : 0,
                'avg_prod_per_day' => $workDays > 0 ? round($hygProd / $workDays, 2) : 0,
                'avg_prod_per_prov_day' => $workDays > 0 ? round($hygProd / $workDays, 2) : 0,
                'prod_per_visit' => $hygVisits > 0 ? round($hygProd / $hygVisits, 2) : 0,
                'fmx_per_day' => $workDays > 0 ? round(($s->fmx_count ?? 0) / $workDays, 2) : 0,
                'srp_per_day' => $workDays > 0 ? round(($s->srp_count ?? 0) / $workDays, 2) : 0,
                'visits_per_day' => $workDays > 0 ? round($hygVisits / $workDays, 2) : 0,
                'reappt' => $reapptRate,
                'perio_reappt' => $perioReapptRate,
                'adult_retention_12m' => 0,
                'adult_retention_6m' => 0,
                'child_retention_12m' => 0,
                'child_retention_6m' => 0,
                'sealants' => (int) ($s->sealants ?? 0),
                'whitening' => (int) ($s->whitening ?? 0),
                'antimicrobial' => (int) ($s->antimicrobial ?? 0),
                'prod_per_proc' => $hygCount > 0 ? round($hygProd / $hygCount, 2) : 0,
                'visits_with_tx_pct' => 0,
                'tx_plans_per_day' => 0,
                'avg_prod_per_hour' => 0,
                'case_acceptance' => $caRates->rate,
            ];
        }

        return response()->json([
            'providers' => $providersList,
            'avg' => $overall,
            'total' => $overall,
        ]);
    }

    public function doctorProviders(Request $request): JsonResponse
    {
        $start = $request->input('start_date', now()->startOfYear()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
        $officeId = Office::getActiveOfficeId();

        $overall = $this->doctorKpis($start, $end, $officeId);
        $clinicRegistry = app(ClinicRegistry::class);

        $provs = DB::select("
            SELECT DISTINCT pl.ProvNum, pr.Abbr, pr.LName,
                   COALESCE(pl.ClinicNum, 0) AS ClinicNum
            FROM od_procedure_logs pl
            JOIN od_providers pr ON pl.ProvNum = pr.ProvNum AND pr.office_id = ?
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            WHERE pl.office_id = ?
              AND pc.IsHygiene = 'false' AND pl.ProcStatus IN ({$this->completedIn}) AND pl.ProcDate BETWEEN ? AND ?
        ", [$officeId, $officeId, $officeId, $start, $end]);

        $providersList = [];

        foreach ($provs as $p) {
            $pId = $p->ProvNum;
            $locName = $clinicRegistry->name((int) $p->ClinicNum, $officeId);

            // Total Production & Counts
            $doc = DB::selectOne("
                SELECT
                    COALESCE(SUM(pl.ProcFee), 0) AS total_prod,
                    COUNT(*) AS total_procs,
                    COUNT(DISTINCT pl.ProcDate) AS work_days,
                    COUNT(DISTINCT CONCAT(pl.PatNum, '-', pl.ProcDate)) AS visits
                FROM od_procedure_logs pl
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
                WHERE pl.office_id = ?
                  AND pc.IsHygiene = 'false' AND pl.ProcStatus IN ({$this->completedIn}) AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
            ", [$officeId, $officeId, $start, $end, $pId]);
            $docProd = (float) ($doc->total_prod ?? 0);
            $workDays = (int) ($doc->work_days ?? 0);
            $docVisits = (int) ($doc->visits ?? 0);

            // Appointment Time
            $apts = DB::selectOne("
                SELECT COUNT(DISTINCT a.AptNum) AS total_apts, AVG(LENGTH(a.Pattern) * 5) AS avg_mins, SUM(LENGTH(a.Pattern) * 5) / 60 AS total_hours
                FROM od_appointments a
                JOIN od_procedure_logs pl ON a.AptNum = pl.AptNum AND pl.office_id = ?
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
                WHERE a.office_id = ?
                  AND a.AptStatus = 2 AND pc.IsHygiene = 'false' AND pl.ProcStatus IN ({$this->completedIn}) AND a.Pattern IS NOT NULL AND a.Pattern != '' AND DATE(a.AptDateTime) BETWEEN ? AND ? AND pl.ProvNum = ?
            ", [$officeId, $officeId, $officeId, $start, $end, $pId]);
            $docAptCount = (int) ($apts->total_apts ?? 0);
            $avgAptMins = (float) ($apts->avg_mins ?? 0);
            $totalHours = (float) ($apts->total_hours ?? 0);

            // Case Acceptance — single source of truth (blueprint D4-A)
            $caRates = $this->txAcceptance->summary(new MetricFilter($start, $end, [], [$pId], false, $officeId));

            // Reappt
            $docReappt = DB::selectOne("
                SELECT COUNT(*) AS total, SUM(CASE WHEN NextAptNum IS NOT NULL AND NextAptNum != '0' THEN 1 ELSE 0 END) AS with_next
                FROM od_appointments a
                WHERE a.office_id = ?
                  AND IsHygiene = 'false' AND AptStatus = 2 AND DATE(AptDateTime) BETWEEN ? AND ? AND ProvNum = ?
            ", [$officeId, $start, $end, $pId]);

            // Exam count
            $examCount = DB::table('od_procedure_logs as pl')
                ->join('od_procedures as pc', 'pl.CodeNum', '=', 'pc.CodeNum')
                ->where('pl.office_id', $officeId)
                ->where('pc.office_id', $officeId)
                ->whereIn('pc.ProcCode', ['D0120', 'D0140', 'D0150', 'D0160', 'D0170', 'D0180'])
                ->whereIn('pl.ProcStatus', ProcStatus::completed())
                ->whereBetween('pl.ProcDate', [$start, $end])
                ->where('pl.ProvNum', $pId)
                ->count();

            // TX Matrix
            $cohortSql = $this->patients->firstVisitCohortSql('first_visit', $officeId);
            $txMatrix = DB::selectOne("
                SELECT
                    COUNT(DISTINCT CASE WHEN DATEDIFF(pl.ProcDate, tp.DateTP) = 0 THEN CONCAT(pl.PatNum,'-',pl.ProcDate) END) AS same_day_completions,
                    COALESCE(AVG(CASE WHEN pt_hist.first_visit BETWEEN ? AND ? THEN pl.ProcFee END), 0) AS avg_new_pt_tx,
                    COALESCE(SUM(CASE WHEN pt_hist.first_visit < ? THEN pl.ProcFee END), 0) AS total_existing_pt_tx,
                    COALESCE(AVG(CASE WHEN pt_hist.first_visit < ? AND pl.ProcStatus IN ({$this->tpIn}) THEN pl.ProcFee END), 0) AS avg_tp_existing_pt,
                    COALESCE(AVG(CASE WHEN pt_hist.first_visit BETWEEN ? AND ? AND pl.ProcStatus IN ({$this->tpIn}) THEN pl.ProcFee END), 0) AS avg_tp_new_pt,
                    COUNT(DISTINCT CASE WHEN pt_hist.first_visit BETWEEN ? AND ? AND pl.ProcStatus IN ('C','TP') THEN pl.PatNum END) AS new_pts_with_tx,
                    COUNT(DISTINCT CASE WHEN pt_hist.first_visit < ? AND pl.ProcStatus IN ('C','TP') THEN pl.PatNum END) AS existing_pts_with_tx,
                    COUNT(DISTINCT CASE WHEN pt_hist.first_visit BETWEEN ? AND ? THEN pt_hist.PatNum END) AS new_pts_total,
                    COUNT(DISTINCT CASE WHEN pt_hist.first_visit < ? AND pl.ProcStatus IN ({$this->completedIn}) THEN pt_hist.PatNum END) AS existing_pts_total,
                    COALESCE(AVG(CASE WHEN pt_hist.first_visit BETWEEN ? AND ? AND DATEDIFF(pl.ProcDate, tp.DateTP) = 0 AND pl.ProcStatus IN ({$this->completedIn}) THEN pl.ProcFee END), 0) AS avg_sameday_new_pt
                FROM od_procedure_logs pl
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
                JOIN (
                    {$cohortSql}
                ) pt_hist ON pl.PatNum = pt_hist.PatNum
                LEFT JOIN od_procedure_logs tp ON tp.PatNum = pl.PatNum AND tp.office_id = ? AND tp.ProcStatus IN ({$this->tpIn}) AND tp.DateTP IS NOT NULL AND tp.DateTP BETWEEN ? AND ?
                WHERE pl.office_id = ?
                  AND pc.IsHygiene = 'false' AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
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
                $officeId,
                $officeId,
                $start,
                $end,
                $officeId,
                $start,
                $end,
                $pId,
            ]);

            $providersList[] = [
                'Location' => $locName,
                'location' => $locName,
                'Provider' => $p->Abbr.' '.$p->LName,
                'provider' => $p->Abbr.' '.$p->LName,
                'case_acceptance_same_day' => $docVisits > 0 ? round((($txMatrix->same_day_completions ?? 0) / $docVisits) * 100, 2) : 0,
                'case_acceptance_rate' => $caRates->rate,
                'new_pt_tx_dollars' => round((float) ($txMatrix->avg_tp_new_pt ?? 0), 2),
                'existing_pt_tx_dollars' => round((float) ($txMatrix->total_existing_pt_tx ?? 0), 2),
                'avg_apt_time_mins' => round($avgAptMins, 2),
                'avg_prod_per_hour' => $totalHours > 0 ? round($docProd / $totalHours, 2) : 0,
                'avg_prod_per_apt' => $docAptCount > 0 ? round($docProd / $docAptCount, 2) : 0,
                'same_day_tx_per_new_pt' => round((float) ($txMatrix->avg_sameday_new_pt ?? 0), 2),
                'avg_prod_per_prov_day' => $workDays > 0 ? round($docProd / $workDays, 2) : 0,
                'avg_tx_per_existing_pt' => round((float) ($txMatrix->avg_tp_existing_pt ?? 0), 2),
                'avg_tx_per_new_pt' => round((float) ($txMatrix->avg_tp_new_pt ?? 0), 2),
                'pct_new_pt_with_tx' => ($txMatrix->new_pts_total ?? 0) > 0 ? round(($txMatrix->new_pts_with_tx / $txMatrix->new_pts_total) * 100, 2) : 0,
                'pct_existing_pt_with_tx' => ($txMatrix->existing_pts_total ?? 0) > 0 ? round(($txMatrix->existing_pts_with_tx / $txMatrix->existing_pts_total) * 100, 2) : 0,
                'reappt' => ($docReappt->total ?? 0) > 0 ? round(($docReappt->with_next / $docReappt->total) * 100, 2) : 0,
                'prod_per_exam' => $examCount > 0 ? round($docProd / $examCount, 2) : 0,
                'total_production' => round($docProd, 2),
            ];
        }

        return response()->json([
            'providers' => $providersList,
            'avg' => $overall,
            'total' => $overall,
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function countByCodes(array $codes, string $start, string $end, ?int $officeId = null): int
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();

        return DB::table('od_procedure_logs as pl')
            ->join('od_procedures as pc', 'pl.CodeNum', '=', 'pc.CodeNum')
            ->where('pl.office_id', $officeId)
            ->where('pc.office_id', $officeId)
            ->whereIn('pc.ProcCode', $codes)
            ->whereIn('pl.ProcStatus', ProcStatus::completed())
            ->whereBetween('pl.ProcDate', [$start, $end])
            ->count();
    }

    /* ── SPECIALTY PROVIDERS ENDPOINTS ─────────────────────────────────────── */

    private function getSpecialtyProvidersBaseLoop($start, $end, $specId, $customCounts = [], $customSums = [], $distinctPatNumCounts = [], $extraCallback = null)
    {
        $officeId = Office::getActiveOfficeId();
        // Fetch distinct providers who have production in this specialty
        $provs = DB::select("
            SELECT DISTINCT pl.ProvNum, pr.Abbr, pr.LName,
                   'Unassigned' as Location
            FROM od_procedure_logs pl
            JOIN od_providers pr ON pl.ProvNum = pr.ProvNum AND pr.office_id = ?
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            WHERE pl.office_id = ? AND pl.ProcStatus IN ({$this->completedIn}) AND pl.ProcDate BETWEEN ? AND ? AND pr.Specialty = ?
        ", [$officeId, $officeId, $officeId, $start, $end, $specId]);

        $providersList = [];

        // Averages tracking
        $totals = [];
        $counts = [];

        foreach ($provs as $p) {
            $provNum = $p->ProvNum;
            $row = [
                'Location' => $p->Location,
                'Provider' => $p->Abbr.' '.$p->LName,
                'ProvNum' => $provNum,
            ];

            // Base queries
            $base = DB::selectOne("
                SELECT
                    SUM(pl.ProcFee) AS total_prod,
                    CAST(COUNT(DISTINCT CASE WHEN pl.ProcFee > 0 THEN DATE(pl.ProcDate) END) AS SIGNED) AS work_days,
                    CAST(COUNT(DISTINCT pl.PatNum) AS SIGNED) AS patient_visits
                FROM od_procedure_logs pl
                WHERE pl.office_id = ? AND pl.ProcStatus IN ({$this->completedIn}) AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
            ", [$officeId, $start, $end, $provNum]);

            $workDays = max(1, (int) ($base->work_days ?? 1));
            $row['total_production'] = round($base->total_prod ?? 0, 2);
            $row['production_per_day'] = round(($base->total_prod ?? 0) / $workDays, 2);
            $row['patient_visits'] = (int) ($base->patient_visits ?? 0);
            $row['work_days'] = $workDays;

            foreach ($customCounts as $key => $codes) {
                $inStr = "'".implode("','", $codes)."'";
                $row[$key] = (int) (DB::selectOne("
                    SELECT COUNT(DISTINCT pl.ProcNum) AS cnt
                    FROM od_procedure_logs pl
                    JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
                    WHERE pl.office_id = ? AND pl.ProcStatus IN ({$this->completedIn}) AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
                      AND pc.ProcCode IN ($inStr)
                ", [$officeId, $officeId, $start, $end, $provNum])->cnt ?? 0);
            }

            foreach ($customSums as $key => $codes) {
                $inStr = "'".implode("','", $codes)."'";
                $row[$key] = round((float) (DB::selectOne("
                    SELECT SUM(pl.ProcFee) AS sm
                    FROM od_procedure_logs pl
                    JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
                    WHERE pl.office_id = ? AND pl.ProcStatus IN ({$this->completedIn}) AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
                      AND pc.ProcCode IN ($inStr)
                ", [$officeId, $officeId, $start, $end, $provNum])->sm ?? 0), 2);
            }

            foreach ($distinctPatNumCounts as $key => $codes) {
                $inStr = "'".implode("','", $codes)."'";
                $row[$key] = (int) (DB::selectOne("
                    SELECT COUNT(DISTINCT pl.PatNum) AS cnt
                    FROM od_procedure_logs pl
                    JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
                    WHERE pl.office_id = ? AND pl.ProcStatus IN ({$this->completedIn}) AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
                      AND pc.ProcCode IN ($inStr)
                ", [$officeId, $officeId, $start, $end, $provNum])->cnt ?? 0);
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
                $row = $extraCallback($row, $start, $end, $provNum, $officeId);
            }

            $providersList[] = $row;

            // Compute running totals for footer
            foreach ($row as $k => $v) {
                if (! is_numeric($v) || in_array($k, ['ProvNum'])) {
                    continue;
                }
                if (! isset($totals[$k])) {
                    $totals[$k] = 0;
                    $counts[$k] = 0;
                }
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
            'avg' => $avg,
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
        ], [], function ($row, $start, $end, $provNum, $officeId = null) {
            $officeId = $officeId ?? Office::getActiveOfficeId();
            // Perio Codes $
            $row['perio_codes_dollars'] = round((float) (DB::selectOne("
                SELECT SUM(pl.ProcFee) AS sm
                FROM od_procedure_logs pl
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
                WHERE pl.office_id = ? AND pl.ProcStatus IN ({$this->completedIn}) AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
                  AND pc.ProcCode BETWEEN 'D4000' AND 'D4999'
            ", [$officeId, $officeId, $start, $end, $provNum])->sm ?? 0), 2);

            // Treatment plan per exam
            $txFee = DB::selectOne("SELECT SUM(ProcFee) AS sm FROM od_procedure_logs WHERE office_id = ? AND ProcStatus IN ({$this->tpIn}) AND DateTP BETWEEN ? AND ? AND ProvNum = ?", [$officeId, $start, $end, $provNum])->sm ?? 0;
            $exams = DB::selectOne("
                SELECT COUNT(DISTINCT pl.ProcNum) AS exam_cnt
                FROM od_procedure_logs pl
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
                WHERE pl.office_id = ? AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
                  AND pc.ProcCode IN ('D0120', 'D0140', 'D0145', 'D0150', 'D0160', 'D0170', 'D0180')
                  AND pl.ProcStatus IN ({$this->completedIn})
            ", [$officeId, $officeId, $start, $end, $provNum])->exam_cnt ?? 0;
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
            'total_active_patients_seen' => ['D8670', 'D8670A'],
        ], function ($row, $start, $end, $provNum, $officeId = null) {
            $officeId = $officeId ?? Office::getActiveOfficeId();
            $starts = DB::selectOne("
                SELECT COUNT(*) AS cnt FROM od_procedure_logs pl 
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
                WHERE pl.office_id = ? AND pl.ProcStatus='C' AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum=?
                AND pc.ProcCode IN ('D8010','D8020','D8030','D8040','D8050','D8060','D8070','D8080','D8090')
            ", [$officeId, $officeId, $start, $end, $provNum])->cnt ?? 0;

            $row['conversion'] = ($row['total_consults'] ?? 0) > 0 ? round(($starts / $row['total_consults']) * 100, 2) : 0;

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
            'extractions_dollars' => ['D7140', 'D7210', 'D7220', 'D7230', 'D7240', 'D7241', 'D7250'],
        ], [], function ($row, $start, $end, $provNum, $officeId = null) {
            $officeId = $officeId ?? Office::getActiveOfficeId();
            $txFee = DB::selectOne("SELECT SUM(ProcFee) AS sm FROM od_procedure_logs WHERE office_id = ? AND ProcStatus IN ({$this->tpIn}) AND DateTP BETWEEN ? AND ? AND ProvNum = ?", [$officeId, $start, $end, $provNum])->sm ?? 0;
            $exams = DB::selectOne("
                SELECT COUNT(DISTINCT pl.ProcNum) AS exam_cnt
                FROM od_procedure_logs pl
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
                WHERE pl.office_id = ? AND pl.ProcDate BETWEEN ? AND ? AND pl.ProvNum = ?
                  AND pc.ProcCode IN ('D0120', 'D0140', 'D0145', 'D0150', 'D0160', 'D0170', 'D0180')
                  AND pl.ProcStatus IN ({$this->completedIn})
            ", [$officeId, $officeId, $start, $end, $provNum])->exam_cnt ?? 0;
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
        ], [], function ($row, $start, $end, $provNum, $officeId = null) {
            $officeId = $officeId ?? Office::getActiveOfficeId();
            // Re-map workdays
            $row['total_working_days'] = $row['work_days'];
            $row['patients_per_day'] = $row['work_days'] > 0 ? round($row['patient_visits'] / $row['work_days'], 1) : 0;
            $row['production_per_patient'] = $row['patient_visits'] > 0 ? round($row['total_production'] / $row['patient_visits'], 2) : 0;

            // Rolling 90 days and Same Day acceptance
            $txDayZero = DB::selectOne("
                SELECT COUNT(DISTINCT a.PatNum) AS tx_pts
                FROM od_procedure_logs a
                WHERE a.office_id = ? AND a.ProcStatus IN ({$this->tpIn}) AND a.DateTP BETWEEN ? AND ? AND a.ProvNum=?
            ", [$officeId, $start, $end, $provNum])->tx_pts ?? 0;

            $accSameDay = DB::selectOne("
                SELECT COUNT(DISTINCT a.PatNum) AS acc_pts
                FROM od_procedure_logs a
                JOIN od_procedure_logs b ON a.PatNum = b.PatNum AND a.CodeNum = b.CodeNum AND b.office_id = ?
                WHERE a.office_id = ? AND a.ProcStatus IN ({$this->tpIn}) AND a.DateTP BETWEEN ? AND ? AND a.ProvNum=?
                  AND b.ProcStatus IN ('C','S') AND DATE(b.ProcDate) = DATE(a.DateTP)
            ", [$officeId, $officeId, $start, $end, $provNum])->acc_pts ?? 0;

            $acc90Days = DB::selectOne("
                SELECT COUNT(DISTINCT a.PatNum) AS acc_pts
                FROM od_procedure_logs a
                JOIN od_procedure_logs b ON a.PatNum = b.PatNum AND a.CodeNum = b.CodeNum AND b.office_id = ?
                WHERE a.office_id = ? AND a.ProcStatus IN ({$this->tpIn}) AND a.DateTP BETWEEN ? AND ? AND a.ProvNum=?
                  AND b.ProcStatus IN ('C','S') AND b.ProcDate >= a.DateTP AND DATEDIFF(b.ProcDate, a.DateTP) <= 90
            ", [$officeId, $officeId, $start, $end, $provNum])->acc_pts ?? 0;

            $row['case_acceptance_same_day'] = $txDayZero > 0 ? round(($accSameDay / $txDayZero) * 100, 2) : 0;
            $row['case_acceptance_rolling_90_days'] = $txDayZero > 0 ? round(($acc90Days / $txDayZero) * 100, 2) : 0;

            return $row;
        }));
    }
}
