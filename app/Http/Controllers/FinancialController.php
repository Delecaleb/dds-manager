<?php

namespace App\Http\Controllers;

use App\Services\OpenDental\FinancialAnalyticsService;
use App\Services\OpenDental\PatientAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinancialController extends Controller
{
    public function __construct(
        protected FinancialAnalyticsService $financialAnalytics,
        protected PatientAnalyticsService $patientAnalytics
    ) {
    }

    public function index()
    {
        return view('financials.index');
    }

    public function data(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());

        // Utilization Data Chart (Provider Production)
        $utilizationData = DB::select("
            SELECT
                CONCAT(pr.LName, ', ', pr.PName) AS provider,
                SUM(pl.ProcFee) AS production
            FROM od_procedure_logs pl
            JOIN od_providers pr ON pl.ProvNum = pr.ProvNum
            WHERE pl.ProcStatus = 'C'
              AND pl.ProcDate BETWEEN ? AND ?
            GROUP BY pr.ProvNum, pr.LName, pr.PName
            ORDER BY production DESC
        ", [$start, $end]);

        // Adjustment Percent Chart
        $adjustmentData = DB::select("
            SELECT
                d.ItemName AS label,
                SUM(a.AdjAmt) AS value
            FROM od_adjustments a
            JOIN od_definitions d ON a.AdjType = d.DefNum
            WHERE a.AdjDate BETWEEN ? AND ?
            GROUP BY d.DefNum, d.ItemName
            ORDER BY ABS(value) DESC
        ", [$start, $end]);

        // Top Services Chart
        $topServicesData = DB::select("
            SELECT
                d.ItemName AS label,
                SUM(pl.ProcFee) AS value
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            JOIN od_definitions d ON pc.ProcCat = d.DefNum
            WHERE pl.ProcStatus = 'C'
              AND pl.ProcDate BETWEEN ? AND ?
            GROUP BY d.DefNum, d.ItemName
            ORDER BY value DESC
            LIMIT 5
        ", [$start, $end]);


        // Daily Revenue Data
        $dailyGross = DB::table('od_procedure_logs')
            ->where('ProcStatus', 'C')
            ->whereBetween('ProcDate', [$start, $end])
            ->selectRaw('DATE(ProcDate) as date, SUM(ProcFee) as amount')
            ->groupByRaw('DATE(ProcDate)')
            ->pluck('amount', 'date');

        $dailyAdj = DB::table('od_adjustments')
            ->whereBetween('AdjDate', [$start, $end])
            ->selectRaw('DATE(AdjDate) as date, SUM(AdjAmt) as amount')
            ->groupByRaw('DATE(AdjDate)')
            ->pluck('amount', 'date');

        $dailyWriteOffs = DB::table('od_claim_procs')
            ->whereBetween('ProcDate', [$start, $end])
            ->selectRaw('DATE(ProcDate) as date, SUM(WriteOff) as amount')
            ->groupByRaw('DATE(ProcDate)')
            ->pluck('amount', 'date');

        $dailyColl = DB::table('od_pay_splits')
            ->whereBetween('DatePay', [$start, $end])
            ->selectRaw('DATE(DatePay) as date, SUM(SplitAmt) as amount')
            ->groupByRaw('DATE(DatePay)')
            ->pluck('amount', 'date');

        $allDates = collect(array_keys($dailyGross->toArray()))
            ->merge(array_keys($dailyAdj->toArray()))
            ->merge(array_keys($dailyWriteOffs->toArray()))
            ->merge(array_keys($dailyColl->toArray()))
            ->unique()
            ->sort()
            ->values();

        $dailyRevenueData = $allDates->map(function ($date) use ($dailyGross, $dailyAdj, $dailyWriteOffs, $dailyColl) {
            $g = (float) ($dailyGross[$date] ?? 0);
            $a = (float) ($dailyAdj[$date] ?? 0);
            $w = (float) ($dailyWriteOffs[$date] ?? 0);
            $c = (float) ($dailyColl[$date] ?? 0);
            $n = $g + $a + $w;
            return [
                'date' => $date,
                'gross' => $g,
                'adjustments' => $a,
                'collections' => $c,
                'net' => $n
            ];
        })->values();

        // Daily Patient Statistics
        $dailyVisits = \App\Models\OdProcedureLog::where('ProcStatus', 'C')
            ->whereBetween('ProcDate', [$start, $end])
            ->selectRaw('DATE(ProcDate) as date, COUNT(DISTINCT PatNum) as cnt')
            ->groupByRaw('DATE(ProcDate)')
            ->pluck('cnt', 'date');

        $dailyScheduled = \App\Models\OdAppointment::whereBetween('AptDateTime', [$start, $end])
            ->where('AptStatus', 'Scheduled')
            ->selectRaw('DATE(AptDateTime) as date, COUNT(DISTINCT PatNum) as cnt')
            ->groupByRaw('DATE(AptDateTime)')
            ->pluck('cnt', 'date');

        $dailyNewScheduled = \App\Models\OdAppointment::whereBetween('AptDateTime', [$start, $end])
            ->where('IsNewPatient', 'true')
            ->selectRaw('DATE(AptDateTime) as date, COUNT(DISTINCT PatNum) as cnt')
            ->groupByRaw('DATE(AptDateTime)')
            ->pluck('cnt', 'date');

        $dailyNewVisits = DB::table(function ($query) {
            $query->from('od_procedure_logs')
                ->where('ProcStatus', 'C')
                ->select('PatNum')
                ->selectRaw('MIN(DATE(ProcDate)) as first_visit')
                ->groupBy('PatNum');
        }, 'first_visits')
            ->whereBetween('first_visit', [$start, $end])
            ->select('first_visit as date')
            ->selectRaw('COUNT(*) as cnt')
            ->groupBy('first_visit')
            ->pluck('cnt', 'date');

        $allStatDates = collect(array_keys($dailyVisits->toArray()))
            ->merge(array_keys($dailyScheduled->toArray()))
            ->merge(array_keys($dailyNewScheduled->toArray()))
            ->merge(array_keys($dailyNewVisits->toArray()))
            ->unique()
            ->sort()
            ->values();

        $dailyPatientStats = $allStatDates->map(function ($date) use ($dailyVisits, $dailyScheduled, $dailyNewScheduled, $dailyNewVisits) {
            return [
                'date' => $date,
                'patient_visits' => (int) ($dailyVisits[$date] ?? 0),
                'new_patient_visits' => (int) ($dailyNewVisits[$date] ?? 0),
                'patient_scheduled' => (int) ($dailyScheduled[$date] ?? 0),
                'new_patient_scheduled' => (int) ($dailyNewScheduled[$date] ?? 0),
            ];
        })->values();

        return response()->json(array_merge(
            $this->patientAnalytics->getPatientAnalytics($start, $end),
            $this->financialAnalytics->filterAnalysis($start, $end),
            [
                'utilization' => $utilizationData,
                'adjustments_breakdown' => $adjustmentData,
                'top_services' => $topServicesData,
                'daily_revenue' => $dailyRevenueData,
                'daily_patient_stats' => $dailyPatientStats
            ]
        ));
    }

    // ── Score Cards ───────────────────────────────────────────────────────────
    public function scoreCards(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
        $tab = $request->input('tab', 'production');
        $provNum = $request->input('provider_num', '');

        return response()->json(
            $tab === 'collection'
            ? $this->scoreCardsCollection($start, $end, $provNum)
            : $this->scoreCardsProduction($start, $end, $provNum)
        );
    }

    private function providerExpr(string $alias): string
    {
        return "IF({$alias}.PName IS NULL OR {$alias}.PName = '', {$alias}.LName, CONCAT({$alias}.LName, ', ', {$alias}.PName))";
    }

    private function scoreCardsProduction(string $start, string $end, string $provNum): array
    {
        $provFilter = '';
        $bindings = [$start, $end];
        if ($provNum !== '') {
            $provFilter = 'AND pl.ProvNum = ?';
            $bindings[] = $provNum;
        }

        $provExpr = $this->providerExpr('pr');

        $rows = DB::select("
            SELECT
                pr.ProvNum AS prov_num,
                {$provExpr} AS provider,
                pc.Descript AS service,
                pc.ProcCode AS service_code,
                COUNT(*)         AS cnt,
                MAX(pl.ProcFee)  AS service_fee,
                SUM(pl.ProcFee)  AS total_production
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            JOIN od_providers  pr ON pl.ProvNum  = pr.ProvNum
            WHERE pl.ProcStatus = 'C'
              AND pl.ProcDate BETWEEN ? AND ?
              {$provFilter}
            GROUP BY pr.ProvNum, pr.LName, pr.PName, pc.CodeNum, pc.ProcCode, pc.Descript
            ORDER BY total_production DESC, cnt DESC
        ", $bindings);

        // Tier assignment (sorted by total_production DESC — already sorted)
        $n = count($rows);
        $topCut = max(1, (int) ceil($n * 0.2));
        $botCut = max(1, (int) ceil($n * 0.2));
        foreach ($rows as $i => $r) {
            $r->tier = match (true) {
                $i < $topCut => 'top',
                $i >= $n - $botCut => 'bottom',
                default => 'mid',
            };
        }

        $totalCount = (int) array_sum(array_map(fn($r) => $r->cnt, $rows));
        $totalProd = (float) array_sum(array_map(fn($r) => $r->total_production, $rows));
        $uniquePriced = count(array_filter($rows, fn($r) => (float) $r->service_fee > 0));

        // Top-5 for charts
        $byCount = $rows;
        usort($byCount, fn($a, $b) => $b->cnt <=> $a->cnt);

        $providers = DB::table('od_providers')
            ->where('IsHidden', 'false')
            ->orderBy('LName')
            ->get(['ProvNum', 'LName', 'PName']);

        return [
            'kpis' => [
                'total_count' => $totalCount,
                'unique_by_pricing' => $uniquePriced,
                'total_production' => round($totalProd, 2),
            ],
            'chart_counts' => array_slice(array_map(fn($r) => [
                'label' => $r->service,
                'value' => (int) $r->cnt,
            ], $byCount), 0, 5),
            'chart_services' => array_slice(array_map(fn($r) => [
                'label' => $r->service,
                'value' => round((float) $r->total_production, 2),
            ], $rows), 0, 5),
            'rows' => array_map(fn($r) => [
                'provider' => $r->provider ?? 'Unknown',
                'service' => $r->service,
                'service_code' => $r->service_code,
                'count' => (int) $r->cnt,
                'service_fee' => round((float) $r->service_fee, 2),
                'total_production' => round((float) $r->total_production, 2),
                'tier' => $r->tier,
            ], $rows),
            'providers' => $providers->map(fn($p) => [
                'id' => $p->ProvNum,
                'name' => $p->PName ? "{$p->LName}, {$p->PName}" : $p->LName,
            ])->values(),
        ];
    }

    private function scoreCardsCollection(string $start, string $end, string $provNum): array
    {
        $provFilter = '';
        $bindings = [$start, $end];
        if ($provNum !== '') {
            $provFilter = 'AND ps.ProvNum = ?';
            $bindings[] = $provNum;
        }

        $provExpr = $this->providerExpr('pr');

        $rows = DB::select("
            SELECT
                {$provExpr}                     AS provider,
                'Payment'                        AS description,
                'Payment'                        AS type,
                COUNT(*)                         AS cnt,
                SUM(ps.SplitAmt)                 AS service_fee,
                SUM(ps.SplitAmt)                 AS total_payments
            FROM od_pay_splits ps
            LEFT JOIN od_providers pr ON ps.ProvNum = pr.ProvNum
            WHERE ps.DatePay BETWEEN ? AND ?
              AND ps.SplitAmt != 0
              {$provFilter}
            GROUP BY ps.ProvNum, pr.LName, pr.PName
            ORDER BY total_payments DESC
        ", $bindings);

        $n = count($rows);
        $topCut = max(1, (int) ceil($n * 0.2));
        $botCut = max(1, (int) ceil($n * 0.2));
        foreach ($rows as $i => $r) {
            $r->tier = match (true) {
                $i < $topCut => 'top',
                $i >= $n - $botCut => 'bottom',
                default => 'mid',
            };
        }

        $totalCount = (int) array_sum(array_map(fn($r) => $r->cnt, $rows));
        $totalPay = (float) array_sum(array_map(fn($r) => $r->total_payments, $rows));

        $byCount = $rows;
        usort($byCount, fn($a, $b) => $b->cnt <=> $a->cnt);

        $providers = DB::table('od_providers')
            ->where('IsHidden', 'false')
            ->orderBy('LName')
            ->get(['ProvNum', 'LName', 'PName']);

        return [
            'kpis' => [
                'total_count' => $totalCount,
                'total_payments' => round($totalPay, 2),
            ],
            'chart_counts' => array_slice(array_map(fn($r) => [
                'label' => $r->description,
                'value' => (int) $r->cnt,
            ], $byCount), 0, 5),
            'chart_payments' => array_slice(array_map(fn($r) => [
                'label' => $r->description,
                'value' => round((float) $r->total_payments, 2),
            ], $rows), 0, 5),
            'rows' => array_map(fn($r) => [
                'provider' => $r->provider ?? 'Unknown',
                'description' => $r->description,
                'type' => $r->type,
                'count' => (int) $r->cnt,
                'service_fee' => round((float) $r->service_fee, 2),
                'total_payments' => round((float) $r->total_payments, 2),
                'tier' => $r->tier,
            ], $rows),
            'providers' => $providers->map(fn($p) => [
                'id' => $p->ProvNum,
                'name' => $p->PName ? "{$p->LName}, {$p->PName}" : $p->LName,
            ])->values(),
        ];
    }

    public function breakdown(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
        $type = $request->input('type', '');

        $rows = match ($type) {
            'gross_production' => $this->bkGrossProduction($start, $end),
            'net_production' => $this->bkNetProduction($start, $end),
            'adjustment' => $this->bkAdjustment($start, $end),
            'collection' => $this->bkCollection($start, $end),
            'patient_visits' => $this->bkPatientVisits($start, $end),
            'new_patient_visits' => $this->bkNewPatientVisits($start, $end),
            'patients_scheduled' => $this->bkPatientsScheduled($start, $end),
            'new_patients_scheduled' => $this->bkNewPatientsScheduled($start, $end),
            'avg_production_per_patient' => $this->bkAvgProductionPerPatient($start, $end),
            default => [],
        };

        return response()->json($rows);
    }

    // ── Gross Production ──────────────────────────────────────────────────────
    private function bkGrossProduction(string $start, string $end): array
    {
        $rows = DB::select("
            SELECT
                p.PatNum        AS patient_id,
                CONCAT(p.LName, ', ', p.FName)               AS patient_name,
                CONCAT(pr.ProvNum, ' - ', pr.Abbr)           AS provider_ids,
                CONCAT(pr.LName, ', ', pr.PName)             AS providers,
                pl.ProcDate                                   AS dates,
                SUM(pl.ProcFee)                               AS amount
            FROM od_procedure_logs pl
            JOIN od_patients  p  ON pl.PatNum  = p.PatNum
            JOIN od_providers pr ON pl.ProvNum = pr.ProvNum
            WHERE pl.ProcStatus = 'C'
              AND pl.ProcDate BETWEEN ? AND ?
            GROUP BY p.PatNum, p.LName, p.FName,
                     pr.ProvNum, pr.Abbr, pr.LName, pr.PName,
                     pl.ProcDate
            ORDER BY pl.ProcDate, p.LName
        ", [$start, $end]);

        return array_map(fn($r) => [
            'patient_id' => $r->patient_id,
            'patient_name' => $r->patient_name,
            'provider_ids' => $r->provider_ids,
            'providers' => $r->providers,
            'dates' => $r->dates,
            'amount' => round((float) $r->amount, 2),
        ], $rows);
    }

    // ── Net Production (procedures + adjustments combined) ───────────────────
    private function bkNetProduction(string $start, string $end): array
    {
        $rows = DB::select("
            SELECT patient_id, patient_name, provider_ids, providers, dates, amount
            FROM (
                SELECT
                    p.PatNum                                          AS patient_id,
                    CONCAT(p.LName, ', ', p.FName)                   AS patient_name,
                    CONCAT(pr.ProvNum, ' - ', pr.Abbr)               AS provider_ids,
                    CONCAT(pr.LName, ', ', pr.PName)                 AS providers,
                    pl.ProcDate                                       AS dates,
                    SUM(pl.ProcFee)                                   AS amount
                FROM od_procedure_logs pl
                JOIN od_patients  p  ON pl.PatNum  = p.PatNum
                JOIN od_providers pr ON pl.ProvNum = pr.ProvNum
                WHERE pl.ProcStatus = 'C'
                  AND pl.ProcDate BETWEEN ? AND ?
                GROUP BY p.PatNum, p.LName, p.FName,
                         pr.ProvNum, pr.Abbr, pr.LName, pr.PName, pl.ProcDate

                UNION ALL

                SELECT
                    p.PatNum                                          AS patient_id,
                    CONCAT(p.LName, ', ', p.FName)                   AS patient_name,
                    COALESCE(CONCAT(pr.ProvNum, ' - ', pr.Abbr), '') AS provider_ids,
                    COALESCE(CONCAT(pr.LName, ', ', pr.PName), '')   AS providers,
                    a.AdjDate                                         AS dates,
                    a.AdjAmt                                          AS amount
                FROM od_adjustments a
                JOIN od_patients  p  ON a.PatNum  = p.PatNum
                LEFT JOIN od_providers pr ON a.ProvNum = pr.ProvNum
                WHERE a.AdjDate BETWEEN ? AND ?
            ) combined
            ORDER BY dates, patient_name
        ", [$start, $end, $start, $end]);

        return array_map(fn($r) => [
            'patient_id' => $r->patient_id,
            'patient_name' => $r->patient_name,
            'provider_ids' => $r->provider_ids,
            'providers' => $r->providers,
            'dates' => $r->dates,
            'amount' => round((float) $r->amount, 2),
        ], $rows);
    }

    // ── Adjustment ────────────────────────────────────────────────────────────
    private function bkAdjustment(string $start, string $end): array
    {
        $rows = DB::select("
            SELECT
                p.PatNum                                          AS patient_id,
                CONCAT(p.LName, ', ', p.FName)                   AS patient_name,
                COALESCE(CONCAT(pr.ProvNum, ' - ', pr.Abbr), '') AS provider_ids,
                COALESCE(CONCAT(pr.LName, ', ', pr.PName), '')   AS providers,
                a.AdjDate                                         AS dates,
                a.AdjAmt                                          AS amount,
                a.AdjType                                         AS adj_type_id,
                COALESCE(NULLIF(a.AdjNote, ''), NULL)             AS adj_note
            FROM od_adjustments a
            JOIN od_patients  p  ON a.PatNum  = p.PatNum
            LEFT JOIN od_providers pr ON a.ProvNum = pr.ProvNum
            WHERE a.AdjDate BETWEEN ? AND ?
            ORDER BY a.AdjDate, p.LName
        ", [$start, $end]);

        return array_map(fn($r) => [
            'patient_id' => $r->patient_id,
            'patient_name' => $r->patient_name,
            'provider_ids' => $r->provider_ids,
            'providers' => $r->providers,
            'dates' => $r->dates,
            'amount' => round((float) $r->amount, 2),
            'adj_type' => ((float) $r->amount >= 0 ? '+' : '-') . ' Adjustment (Type #' . $r->adj_type_id . ')',
        ], $rows);
    }

    // ── Collection ────────────────────────────────────────────────────────────
    private function bkCollection(string $start, string $end): array
    {
        $rows = DB::select("
            SELECT
                p.PatNum                                          AS patient_id,
                CONCAT(p.LName, ', ', p.FName)                   AS patient_name,
                COALESCE(CONCAT(pr.ProvNum, ' - ', pr.Abbr), '') AS provider_ids,
                COALESCE(CONCAT(pr.LName, ', ', pr.PName), '')   AS providers,
                ps.DatePay                                        AS dates,
                SUM(ps.SplitAmt)                                  AS amount
            FROM od_pay_splits ps
            JOIN od_patients  p  ON ps.PatNum  = p.PatNum
            LEFT JOIN od_providers pr ON ps.ProvNum = pr.ProvNum
            WHERE ps.DatePay BETWEEN ? AND ?
            GROUP BY p.PatNum, p.LName, p.FName,
                     pr.ProvNum, pr.Abbr, pr.LName, pr.PName,
                     ps.DatePay
            ORDER BY ps.DatePay, p.LName
        ", [$start, $end]);

        return array_map(fn($r) => [
            'patient_id' => $r->patient_id,
            'patient_name' => $r->patient_name,
            'provider_ids' => $r->provider_ids,
            'providers' => $r->providers,
            'dates' => $r->dates,
            'amount' => round((float) $r->amount, 2),
        ], $rows);
    }

    // ── Patient Visits ────────────────────────────────────────────────────────
    private function bkPatientVisits(string $start, string $end): array
    {
        $rows = DB::select("
            SELECT
                p.PatNum                         AS patient_id,
                CONCAT(p.LName, ', ', p.FName)   AS patient_name,
                pl.ProcDate                      AS dates,
                COUNT(DISTINCT pl.ProcDate)      AS count
            FROM od_procedure_logs pl
            JOIN od_patients p ON pl.PatNum = p.PatNum
            WHERE pl.ProcStatus = 'C'
              AND pl.ProcDate BETWEEN ? AND ?
            GROUP BY p.PatNum, p.LName, p.FName, pl.ProcDate
            ORDER BY pl.ProcDate, p.LName
        ", [$start, $end]);

        return array_map(fn($r) => [
            'patient_id' => $r->patient_id,
            'patient_name' => $r->patient_name,
            'dates' => $r->dates,
            'count' => (int) $r->count,
        ], $rows);
    }

    // ── New Patient Visits ────────────────────────────────────────────────────
    private function bkNewPatientVisits(string $start, string $end): array
    {
        $rows = DB::select("
            SELECT
                p.PatNum                                                        AS patient_id,
                CONCAT(p.LName, ', ', p.FName)                                 AS patient_name,
                MIN(pl.ProcDate)                                                AS dates,
                GROUP_CONCAT(DISTINCT pc.ProcCode ORDER BY pc.ProcCode SEPARATOR ', ') AS service_codes,
                SUM(pl.ProcFee)                                                 AS amount
            FROM od_procedure_logs pl
            JOIN od_patients   p  ON pl.PatNum  = p.PatNum
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            WHERE pl.ProcStatus = 'C'
            GROUP BY p.PatNum, p.LName, p.FName
            HAVING MIN(pl.ProcDate) BETWEEN ? AND ?
            ORDER BY dates, p.LName
        ", [$start, $end]);

        return array_map(fn($r) => [
            'patient_id' => $r->patient_id,
            'patient_name' => $r->patient_name,
            'dates' => $r->dates,
            'service_codes' => $r->service_codes,
            'amount' => round((float) $r->amount, 2),
        ], $rows);
    }

    // ── Patients Scheduled ────────────────────────────────────────────────────
    private function bkPatientsScheduled(string $start, string $end): array
    {
        $rows = DB::select("
            SELECT
                p.PatNum                         AS patient_id,
                CONCAT(p.LName, ', ', p.FName)   AS patient_name,
                DATE(a.AptDateTime)              AS dates,
                COUNT(*)                         AS count
            FROM od_appointments a
            JOIN od_patients p ON a.PatNum = p.PatNum
            WHERE DATE(a.AptDateTime) BETWEEN ? AND ?
              AND a.AptStatus = 'Scheduled'
            GROUP BY p.PatNum, p.LName, p.FName, DATE(a.AptDateTime)
            ORDER BY dates, p.LName
        ", [$start, $end]);

        return array_map(fn($r) => [
            'patient_id' => $r->patient_id,
            'patient_name' => $r->patient_name,
            'dates' => $r->dates,
            'count' => (int) $r->count,
        ], $rows);
    }

    // ── New Patients Scheduled ────────────────────────────────────────────────
    private function bkNewPatientsScheduled(string $start, string $end): array
    {
        $rows = DB::select("
            SELECT
                p.PatNum                         AS patient_id,
                CONCAT(p.LName, ', ', p.FName)   AS patient_name,
                DATE(a.AptDateTime)              AS dates,
                COUNT(*)                         AS count
            FROM od_appointments a
            JOIN od_patients p ON a.PatNum = p.PatNum
            WHERE DATE(a.AptDateTime) BETWEEN ? AND ?
              AND a.AptStatus = 'Scheduled'
              AND a.IsNewPatient = 'true'
            GROUP BY p.PatNum, p.LName, p.FName, DATE(a.AptDateTime)
            ORDER BY dates, p.LName
        ", [$start, $end]);

        return array_map(fn($r) => [
            'patient_id' => $r->patient_id,
            'patient_name' => $r->patient_name,
            'dates' => $r->dates,
            'count' => (int) $r->count,
        ], $rows);
    }

    // ── Average Production Per Patient ────────────────────────────────────────
    private function bkAvgProductionPerPatient(string $start, string $end): array
    {
        $rows = DB::select("
            SELECT
                p.PatNum                         AS patient_id,
                CONCAT(p.LName, ', ', p.FName)   AS patient_name,
                COUNT(DISTINCT pl.ProcDate)       AS count,
                SUM(pl.ProcFee)                  AS amount
            FROM od_procedure_logs pl
            JOIN od_patients p ON pl.PatNum = p.PatNum
            WHERE pl.ProcStatus = 'C'
              AND pl.ProcDate BETWEEN ? AND ?
            GROUP BY p.PatNum, p.LName, p.FName
            ORDER BY p.LName
        ", [$start, $end]);

        return array_map(fn($r) => [
            'patient_id' => $r->patient_id,
            'patient_name' => $r->patient_name,
            'count' => (int) $r->count,
            'amount' => round((float) $r->amount, 2),
        ], $rows);
    }
}
