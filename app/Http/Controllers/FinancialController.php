<?php

namespace App\Http\Controllers;

use App\Domain\Support\ProcStatus;
use App\Helpers\MetricDefinitions;
use App\Models\OdAppointment;
use App\Models\OdProcedureLog;
use App\Services\OpenDental\FinancialAnalyticsService;
use App\Services\OpenDental\PatientAnalyticsService;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialController extends Controller
{
    /** Pre-rendered completed-status IN-list for raw-SQL heredoc interpolation (DRY). */
    private readonly string $completedIn;

    public function __construct(
        protected FinancialAnalyticsService $financialAnalytics,
        protected PatientAnalyticsService $patientAnalytics
    ) {
        $this->completedIn = ProcStatus::inList(ProcStatus::completed());
    }

    public function index()
    {
        return view('financials.index');
    }

    public function revenue(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());

        return response()->json(
            $this->financialAnalytics->filterAnalysis($start, $end)
        );
    }

    public function data(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
        $section = $request->input('section', 'all');

        $response = [];

        if (in_array($section, ['all', 'revenue-kpis', 'revenue'])) {
            $response = array_merge(
                $response,
                $this->financialAnalytics->filterAnalysis($start, $end)
            );
        }

        if (in_array($section, ['all', 'patient-kpis'])) {
            $response = array_merge(
                $response,
                $this->patientAnalytics->getPatientAnalytics($start, $end)
            );
        }

        // Utilization Data Chart (Provider Production)
        if (in_array($section, ['all', 'utilization-chart'])) {
            $utilizationData = DB::select("
                SELECT
                    COALESCE(NULLIF(pr.Abbr, ''), pr.LName, CAST(pr.ProvNum AS CHAR)) AS provider,
                    SUM(pl.ProcFee) AS production
                FROM od_procedure_logs pl
                JOIN od_providers pr ON pl.ProvNum = pr.ProvNum
                WHERE pl.ProcStatus IN ({$this->completedIn})
                  AND pr.IsHidden IN ('false', '0', 0)
                  AND pl.ProcDate BETWEEN ? AND ?
                GROUP BY pr.ProvNum, pr.Abbr, pr.LName
                HAVING SUM(pl.ProcFee) > 0
                ORDER BY production DESC
            ", [$start, $end]);

            $response['utilization'] = $utilizationData;
        }

        // Adjustment Chart
        if (in_array($section, ['all', 'adjustment-chart'])) {
            $adjustmentData = DB::select('
                SELECT
                    d.ItemName AS label,
                    SUM(a.AdjAmt) AS value
                FROM od_adjustments a
                JOIN od_definitions d ON a.AdjType = d.DefNum
                WHERE a.AdjDate BETWEEN ? AND ?
                GROUP BY d.DefNum, d.ItemName
                ORDER BY ABS(SUM(a.AdjAmt)) DESC
            ', [$start, $end]);

            $response['adjustments_breakdown'] = $adjustmentData;
        }

        // Top Services Chart
        if (in_array($section, ['all', 'top-services-chart'])) {
            $topServicesData = DB::select("
                SELECT
                    d.ItemName AS label,
                    SUM(pl.ProcFee) AS value
                FROM od_procedure_logs pl
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
                JOIN od_definitions d ON pc.ProcCat = d.DefNum
                WHERE pl.ProcStatus IN ({$this->completedIn})
                  AND pl.ProcDate BETWEEN ? AND ?
                GROUP BY d.DefNum, d.ItemName
                ORDER BY SUM(pl.ProcFee) DESC
                LIMIT 5
            ", [$start, $end]);

            $response['top_services'] = $topServicesData;
        }

        // Daily Revenue Data
        if (in_array($section, ['all', 'daily-revenue-chart'])) {
            $dailyGross = DB::table('od_procedure_logs')
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$start, $end])
                ->selectRaw('DATE(ProcDate) as date, '.MetricDefinitions::grossProduction('amount'))
                ->groupByRaw('DATE(ProcDate)')
                ->pluck('amount', 'date');

            $dailyAdj = DB::table('od_adjustments')
                ->whereBetween('AdjDate', [$start, $end])
                ->selectRaw('DATE(AdjDate) as date, '.MetricDefinitions::adjustments('amount'))
                ->groupByRaw('DATE(AdjDate)')
                ->pluck('amount', 'date');

            $dailyWriteOffs = DB::table('od_claim_procs')
                ->whereBetween('ProcDate', [$start, $end])
                ->selectRaw('DATE(ProcDate) as date, '.MetricDefinitions::writeOffs('amount'))
                ->groupByRaw('DATE(ProcDate)')
                ->pluck('amount', 'date');

            $dailyColl = DB::table('od_pay_splits')
                ->whereBetween('DatePay', [$start, $end])
                ->selectRaw('DATE(DatePay) as date, '.MetricDefinitions::collections('amount'))
                ->groupByRaw('DATE(DatePay)')
                ->pluck('amount', 'date');

            $period = CarbonPeriod::create($start, $end);
            $allDates = collect();
            foreach ($period as $dt) {
                $allDates->push($dt->toDateString());
            }

            $response['daily_revenue'] = $allDates->map(function ($date) use ($dailyGross, $dailyAdj, $dailyWriteOffs, $dailyColl) {
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
                    'net' => $n,
                ];
            })->values();
        }

        // Daily Patient Statistics
        if (in_array($section, ['all', 'daily-patient-chart'])) {
            $dailyVisits = OdProcedureLog::whereIn('ProcStatus', ProcStatus::completed())
                ->where('CodeNum', '!=', 626)
                ->whereBetween('ProcDate', [$start, $end])
                ->selectRaw('DATE(ProcDate) as date, '.MetricDefinitions::patientVisits('cnt'))
                ->groupByRaw('DATE(ProcDate)')
                ->pluck('cnt', 'date');

            $dailyScheduled = OdAppointment::whereRaw("DATE(REPLACE(AptDateTime, 'T', ' ')) BETWEEN ? AND ?", [$start, $end])
                ->scheduled()
                ->selectRaw("DATE(REPLACE(AptDateTime, 'T', ' ')) as date, ".MetricDefinitions::scheduledPatients('cnt'))
                ->groupByRaw("DATE(REPLACE(AptDateTime, 'T', ' '))")
                ->pluck('cnt', 'date');

            $dailyNewScheduled = DB::table(function ($query) {
                $query->from('od_appointments')
                    ->whereIn('AptStatus', [1, 2])
                    ->select('PatNum')
                    ->selectRaw("MIN(DATE(REPLACE(AptDateTime, 'T', ' '))) as first_apt")
                    ->groupBy('PatNum');
            }, 'fa')
                ->join('od_patients as p', 'fa.PatNum', '=', 'p.PatNum')
                ->whereBetween('fa.first_apt', [$start, $end])
                ->whereNotIn('p.PatNum', function ($query) use ($start) {
                    $query->select('PatNum')
                        ->from('od_procedure_logs')
                        ->where('ProcDate', '<', $start)
                        ->whereIn('ProcStatus', ['C', '2', 'D']);
                })
                ->whereNotIn('p.PatNum', [21216, 21231, 21254])
                ->select('fa.first_apt as date')
                ->selectRaw('COUNT(*) as cnt')
                ->groupBy('fa.first_apt')
                ->pluck('cnt', 'date');

            $dailyNewVisits = DB::table(function ($query) {
                $query->from('od_procedure_logs')
                    ->whereIn('ProcStatus', ProcStatus::completed())
                    ->where('CodeNum', '!=', 626)
                    ->select('PatNum')
                    ->selectRaw('MIN(DATE(ProcDate)) as first_visit')
                    ->groupBy('PatNum');
            }, 'first_visits')
                ->whereBetween('first_visit', [$start, $end])
                ->select('first_visit as date')
                ->selectRaw('COUNT(*) as cnt')
                ->groupBy('first_visit')
                ->pluck('cnt', 'date');

            $dailyCancelled = DB::table('od_procedure_logs as pl')
                ->join('od_procedures as pc', 'pl.CodeNum', '=', 'pc.CodeNum')
                ->whereIn('pl.ProcStatus', ProcStatus::completed())
                ->whereIn('pc.ProcCode', ['D9986', 'D9987'])
                ->whereBetween('pl.ProcDate', [$start, $end])
                ->selectRaw('DATE(pl.ProcDate) as date, COUNT(*) as cnt')
                ->groupByRaw('DATE(pl.ProcDate)')
                ->pluck('cnt', 'date');

            $period = CarbonPeriod::create($start, $end);
            $allStatDates = collect();
            foreach ($period as $dt) {
                $allStatDates->push($dt->toDateString());
            }

            $response['daily_patient_stats'] = $allStatDates->map(function ($date) use ($dailyVisits, $dailyScheduled, $dailyNewScheduled, $dailyNewVisits, $dailyCancelled) {
                return [
                    'date' => $date,
                    'patient_visits' => (int) ($dailyVisits[$date] ?? 0),
                    'new_patient_visits' => (int) ($dailyNewVisits[$date] ?? 0),
                    'patient_scheduled' => (int) ($dailyScheduled[$date] ?? 0),
                    'new_patient_scheduled' => (int) ($dailyNewScheduled[$date] ?? 0),
                    'broken_cancelled' => (int) ($dailyCancelled[$date] ?? 0),
                ];
            })->values();
        }

        return response()->json($response);
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
        if (DB::connection()->getDriverName() === 'sqlite') {
            return "COALESCE(NULLIF({$alias}.Abbr, ''), {$alias}.LName, 'Detroit Dental Care, PC')";
        }

        return "COALESCE(NULLIF(TRIM(CONCAT(COALESCE({$alias}.LName, ''), CASE WHEN NULLIF({$alias}.PName, '') IS NOT NULL THEN CONCAT(', ', {$alias}.PName) ELSE '' END)), ''), {$alias}.Abbr, 'Detroit Dental Care, PC')";
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
                COALESCE(pr.ProvNum, 0) AS prov_num,
                {$provExpr} AS provider,
                pc.Descript AS service,
                pc.ProcCode AS service_code,
                COUNT(*)         AS cnt,
                CAST(pl.ProcFee AS DECIMAL(12,2)) AS service_fee,
                SUM(CAST(pl.ProcFee AS DECIMAL(12,2))) AS total_production
            FROM od_procedure_logs pl
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            LEFT JOIN od_providers pr ON pl.ProvNum  = pr.ProvNum
            WHERE pl.ProcStatus IN ({$this->completedIn})
              AND DATE(REPLACE(pl.ProcDate, 'T', ' ')) BETWEEN ? AND ?
              {$provFilter}
            GROUP BY pr.ProvNum, pr.Abbr, pr.LName, pr.PName, pc.CodeNum, pc.ProcCode, pc.Descript, CAST(pl.ProcFee AS DECIMAL(12,2))
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

        $totalCount = (int) array_sum(array_map(fn ($r) => $r->cnt, $rows));
        $totalProd = (float) array_sum(array_map(fn ($r) => $r->total_production, $rows));

        // Unique Services By Pricing = total count of unique service-by-pricing rows in the table
        $uniquePriced = count($rows);

        // Top-5 for charts
        $byCount = $rows;
        usort($byCount, fn ($a, $b) => $b->cnt <=> $a->cnt);

        $providers = DB::table('od_providers')
            ->whereIn('IsHidden', ['false', '0', 0, false])
            ->orderBy('LName')
            ->get(['ProvNum', 'Abbr', 'LName']);

        return [
            'kpis' => [
                'total_count' => $totalCount,
                'unique_by_pricing' => $uniquePriced,
                'total_production' => round($totalProd, 2),
            ],
            'chart_counts' => array_slice(array_map(fn ($r) => [
                'label' => $r->service,
                'value' => (int) $r->cnt,
            ], $byCount), 0, 5),
            'chart_services' => array_slice(array_map(fn ($r) => [
                'label' => $r->service,
                'value' => round((float) $r->total_production, 2),
            ], $rows), 0, 5),
            'rows' => array_map(fn ($r) => [
                'provider' => $r->provider ?? 'Unknown',
                'service' => $r->service,
                'service_code' => $r->service_code,
                'count' => (int) $r->cnt,
                'service_fee' => round((float) $r->service_fee, 2),
                'total_production' => round((float) $r->total_production, 2),
                'tier' => $r->tier,
            ], $rows),
            'providers' => $providers->map(fn ($p) => [
                'id' => $p->ProvNum,
                'name' => $p->Abbr ?: $p->LName,
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
                COALESCE(pt.ItemName, 'Payment') AS description,
                'Payment'                        AS type,
                1                                AS cnt,
                ps.SplitAmt                      AS service_fee,
                ps.DatePay                       AS payment_date,
                ps.SplitAmt                      AS total_payments
            FROM od_pay_splits ps
            LEFT JOIN od_providers pr ON ps.ProvNum = pr.ProvNum
            LEFT JOIN od_payments p ON ps.PayNum = p.PayNum
            LEFT JOIN od_definitions pt ON p.PayType = pt.DefNum
            WHERE ps.DatePay BETWEEN ? AND ?
              AND ps.SplitAmt != 0
              {$provFilter}
            ORDER BY ps.DatePay DESC
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

        $totalCount = count($rows);
        $totalPay = (float) array_sum(array_map(fn ($r) => $r->total_payments, $rows));

        $providers = DB::table('od_providers')
            ->whereIn('IsHidden', ['false', '0', 0, false])
            ->orderBy('LName')
            ->get(['ProvNum', 'Abbr', 'LName']);

        // Collection Scorecard - Top Counts and Top Payments Charts
        $topPayments = DB::select('
            SELECT 
                pt.ItemName AS PaymentType,
                COUNT(p.PayNum) AS CountValue,
                SUM(p.PayAmt) AS AmountValue
            FROM od_payments p
            LEFT JOIN od_definitions pt ON p.PayType = pt.DefNum
            WHERE p.PayDate BETWEEN ? AND ?
            GROUP BY pt.ItemName, p.PayType
            ORDER BY SUM(p.PayAmt) DESC
        ', [$start, $end]);

        $byCount = $topPayments;
        usort($byCount, fn ($a, $b) => $b->CountValue <=> $a->CountValue);

        return [
            'kpis' => [
                'total_count' => $totalCount,
                'total_payments' => round($totalPay, 2),
            ],
            'chart_counts' => array_map(fn ($r) => [
                'label' => $r->PaymentType ?? 'Unknown',
                'value' => (int) $r->CountValue,
            ], array_slice($byCount, 0, 6)),
            'chart_payments' => array_map(fn ($r) => [
                'label' => $r->PaymentType ?? 'Unknown',
                'value' => round((float) $r->AmountValue, 2),
            ], array_slice($topPayments, 0, 6)),
            'rows' => array_map(fn ($r) => [
                'provider' => $r->provider ?? 'Unknown',
                'payment_date' => $r->payment_date,
                'description' => $r->description,
                'type' => $r->type,
                'count' => (int) $r->cnt,
                'service_fee' => round((float) $r->service_fee, 2),
                'total_payments' => round((float) $r->total_payments, 2),
                'tier' => $r->tier,
            ], $rows),
            'providers' => $providers->map(fn ($p) => [
                'id' => $p->ProvNum,
                'name' => $p->Abbr ?: $p->LName,
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
            'broken_cancelled' => $this->bkBrokenCancelled($start, $end),
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
            WHERE pl.ProcStatus IN ({$this->completedIn})
              AND pl.ProcDate BETWEEN ? AND ?
            GROUP BY p.PatNum, p.LName, p.FName,
                     pr.ProvNum, pr.Abbr, pr.LName, pr.PName,
                     pl.ProcDate
            ORDER BY pl.ProcDate, p.LName
        ", [$start, $end]);

        return array_map(fn ($r) => [
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
                WHERE pl.ProcStatus IN ({$this->completedIn})
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

                UNION ALL

                -- Writeoffs reduce net (stored positive → shown negative) so the ledger
                -- total reconciles with Net = gross + adjustments − writeoffs (D3).
                SELECT
                    p.PatNum                                          AS patient_id,
                    CONCAT(p.LName, ', ', p.FName)                   AS patient_name,
                    COALESCE(CONCAT(pr.ProvNum, ' - ', pr.Abbr), '') AS provider_ids,
                    COALESCE(CONCAT(pr.LName, ', ', pr.PName), '')   AS providers,
                    cp.ProcDate                                       AS dates,
                    -SUM(cp.WriteOff)                                 AS amount
                FROM od_claim_procs cp
                JOIN od_patients  p  ON cp.PatNum  = p.PatNum
                LEFT JOIN od_providers pr ON cp.ProvNum = pr.ProvNum
                WHERE cp.ProcDate BETWEEN ? AND ?
                  AND cp.WriteOff <> 0
                GROUP BY p.PatNum, p.LName, p.FName,
                         pr.ProvNum, pr.Abbr, pr.LName, pr.PName, cp.ProcDate
            ) combined
            ORDER BY dates, patient_name
        ", [$start, $end, $start, $end, $start, $end]);

        return array_map(fn ($r) => [
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

        return array_map(fn ($r) => [
            'patient_id' => $r->patient_id,
            'patient_name' => $r->patient_name,
            'provider_ids' => $r->provider_ids,
            'providers' => $r->providers,
            'dates' => $r->dates,
            'amount' => round((float) $r->amount, 2),
            'adj_type' => ((float) $r->amount >= 0 ? '+' : '-').' Adjustment (Type #'.$r->adj_type_id.')',
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

        return array_map(fn ($r) => [
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
                GROUP_CONCAT(DISTINCT DATE_FORMAT(pl.ProcDate, '%Y-%m-%d') ORDER BY pl.ProcDate SEPARATOR ', ') AS dates,
                COUNT(DISTINCT DATE(pl.ProcDate)) AS count
            FROM od_procedure_logs pl
            JOIN od_patients p ON pl.PatNum = p.PatNum
            WHERE pl.ProcStatus IN ({$this->completedIn})
              AND pl.CodeNum != 626
              AND pl.ProcDate BETWEEN ? AND ?
            GROUP BY p.PatNum, p.LName, p.FName
            ORDER BY count DESC, p.LName
        ", [$start, $end]);

        return array_map(fn ($r) => [
            'patient_id' => $r->patient_id,
            'patient_name' => $r->patient_name,
            'dates' => $r->dates,
            'count' => (int) $r->count,
        ], $rows);
    }

    // ── New Patient Visits ────────────────────────────────────────────────────
    /**
     * New Patient Visits breakdown report (matching JarvisAnalytics logic).
     *
     * Rules:
     * 1. Cohort: Identifies patients whose first-ever completed procedure date falls within [$start, $end].
     * 2. First-Visit Scoping: Strictly aggregates service codes and production completed ON that exact
     *    first visit date (ProcDate = first_date). Procedures from subsequent appointments later in the month
     *    are excluded so they do not inflate the initial new-patient visit production.
     */
    private function bkNewPatientVisits(string $start, string $end): array
    {
        $rows = DB::select("
            SELECT
                p.PatNum                                                                AS patient_id,
                CONCAT(p.LName, ', ', p.FName)                                         AS patient_name,
                fv.first_date                                                           AS dates,
                GROUP_CONCAT(DISTINCT pc.ProcCode ORDER BY pc.ProcCode SEPARATOR ', ') AS service_codes,
                COALESCE(SUM(pl.ProcFee), 0)                                           AS amount
            FROM (
                -- Identify the patient's first-ever completed visit date across history
                SELECT
                    PatNum,
                    MIN(ProcDate) AS first_date
                FROM od_procedure_logs
                WHERE ProcStatus IN ({$this->completedIn})
                  AND CodeNum != 626
                GROUP BY PatNum
                HAVING MIN(ProcDate) BETWEEN ? AND ?
            ) fv
            JOIN od_patients p ON fv.PatNum = p.PatNum
            -- Join only procedures completed on that specific first visit date
            JOIN od_procedure_logs pl ON fv.PatNum = pl.PatNum
                AND pl.ProcDate = fv.first_date
                AND pl.ProcStatus IN ({$this->completedIn})
                AND pl.CodeNum != 626
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            GROUP BY p.PatNum, p.LName, p.FName, fv.first_date
            ORDER BY fv.first_date, p.LName
        ", [$start, $end]);

        return array_map(fn ($r) => [
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
                GROUP_CONCAT(DISTINCT DATE_FORMAT(a.AptDateTime, '%Y-%m-%d') ORDER BY a.AptDateTime SEPARATOR ', ') AS dates,
                COUNT(DISTINCT DATE(a.AptDateTime)) AS count
            FROM od_appointments a
            JOIN od_patients p ON a.PatNum = p.PatNum
            WHERE DATE(a.AptDateTime) BETWEEN ? AND ?
              AND a.AptStatus IN (1, 2)
            GROUP BY p.PatNum, p.LName, p.FName
            ORDER BY count DESC, p.LName
        ", [$start, $end]);

        return array_map(fn ($r) => [
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
                DATE_FORMAT(fa.first_apt, '%Y-%m-%d') AS dates,
                1                                AS count
            FROM od_patients p
            JOIN (
                SELECT PatNum, MIN(AptDateTime) AS first_apt
                FROM od_appointments
                WHERE AptStatus IN (1, 2)
                GROUP BY PatNum
            ) fa ON p.PatNum = fa.PatNum
            WHERE fa.first_apt BETWEEN ? AND ?
              AND p.PatNum NOT IN (
                  SELECT DISTINCT PatNum
                  FROM od_procedure_logs
                  WHERE ProcDate < ?
                    AND ProcStatus IN ('C', '2', 'D')
              )
              AND p.PatNum NOT IN (21216, 21231, 21254)
            ORDER BY p.LName
        ", [$start.' 00:00:00', $end.' 23:59:59', $start]);

        return array_map(fn ($r) => [
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
            WHERE pl.ProcStatus IN ({$this->completedIn})
              AND pl.ProcDate BETWEEN ? AND ?
            GROUP BY p.PatNum, p.LName, p.FName
            ORDER BY p.LName
        ", [$start, $end]);

        return array_map(fn ($r) => [
            'patient_id' => $r->patient_id,
            'patient_name' => $r->patient_name,
            'count' => (int) $r->count,
            'amount' => round((float) $r->amount, 2),
        ], $rows);
    }

    // ── Broken & Cancelled Appointments ────────────────────────────────────────
    private function bkBrokenCancelled(string $start, string $end): array
    {
        $rows = DB::select("
            SELECT
                p.PatNum                         AS patient_id,
                CONCAT(p.LName, ', ', p.FName)   AS patient_name,
                pl.ProcDate                      AS dates,
                pc.ProcCode                      AS service_codes
            FROM od_procedure_logs pl
            JOIN od_patients   p  ON pl.PatNum  = p.PatNum
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum
            WHERE pl.ProcStatus IN ({$this->completedIn})
              AND pc.ProcCode IN ('D9986', 'D9987')
              AND pl.ProcDate BETWEEN ? AND ?
            ORDER BY dates, p.LName
        ", [$start, $end]);

        return array_map(fn ($r) => [
            'patient_id' => $r->patient_id,
            'patient_name' => $r->patient_name,
            'dates' => $r->dates,
            'type' => $r->service_codes === 'D9986' ? 'No-Show' : 'Cancelled',
        ], $rows);
    }
}
