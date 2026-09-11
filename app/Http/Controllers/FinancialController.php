<?php

namespace App\Http\Controllers;

use App\Domain\Patient\PatientVisitService;
use App\Domain\Production\ProductionService;
use App\Domain\Support\ClinicRegistry;
use App\Domain\Support\MetricFilter;
use App\Domain\Support\ProcStatus;
use App\Helpers\MetricDefinitions;
use App\Models\OdAppointment;
use App\Models\Office;
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
        protected PatientAnalyticsService $patientAnalytics,
        protected ProductionService $production,
        protected PatientVisitService $patientVisits,
        protected ClinicRegistry $clinics
    ) {
        $this->completedIn = ProcStatus::inList(ProcStatus::completed());
    }

    private function resolveClinicNum(Request $request, ?int $officeId = null): ?int
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $clinicInput = $request->input('clinic_id') ?? $request->input('clinic_num');
        if ($clinicInput === null && $officeId !== null) {
            $clinicInput = $this->clinics->getActiveClinicNum($officeId);
        }

        return ($clinicInput !== null && $clinicInput !== '' && $clinicInput !== 'all')
            ? (int) $clinicInput
            : null;
    }

    private function resolveClinics(Request $request, ?int $officeId = null): array
    {
        $clinicNum = $this->resolveClinicNum($request, $officeId);

        return $clinicNum !== null ? [$clinicNum] : [];
    }

    public function index()
    {
        $activeOfficeId = Office::getActiveOfficeId();
        $clinics = $this->clinics->all($activeOfficeId);
        $activeClinicNum = $this->clinics->getActiveClinicNum($activeOfficeId);

        return view('financials.index', compact('clinics', 'activeClinicNum'));
    }

    public function revenue(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
        $officeId = Office::getActiveOfficeId();
        $clinics = $this->resolveClinics($request, $officeId);

        return response()->json(
            $this->financialAnalytics->filterAnalysis($start, $end, $officeId, $clinics)
        );
    }

    public function data(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
        $section = $request->input('section', 'all');
        $officeId = Office::getActiveOfficeId();
        $clinicNum = $this->resolveClinicNum($request, $officeId);
        $clinics = $clinicNum !== null ? [$clinicNum] : [];

        $response = [];

        if (in_array($section, ['all', 'revenue-kpis', 'revenue'])) {
            $response = array_merge(
                $response,
                $this->financialAnalytics->filterAnalysis($start, $end, $officeId, $clinics)
            );
        }

        if (in_array($section, ['all', 'patient-kpis'])) {
            $filter = new MetricFilter($start, $end, $clinics, [], null, $officeId);
            $scheduled = (new OdAppointment)->scheduledPatients($start, $end, $officeId, $clinics);
            $visited = $this->patientVisits->patientVisits($start, $end, $clinics, [], $officeId);
            $netProduction = $this->production->netProduction($filter);
            $patientAvgProduction = $visited > 0 ? round($netProduction / $visited, 2) : 0;
            $newPatientVisits = $this->patientVisits->newPatientCount($start, $end, $clinics, [], $officeId);
            $newPatientsScheduled = count($this->bkNewPatientsScheduled($start, $end, $officeId, $clinicNum));

            $response = array_merge(
                $response,
                [
                    'patient_scheduled' => $scheduled,
                    'patient_visits' => $visited,
                    'patient_avg_production' => $patientAvgProduction,
                    'new_patient_visit' => $newPatientVisits,
                    'new_patients_scheduled' => $newPatientsScheduled,
                ]
            );
        }

        // Utilization Data Chart (Provider Production)
        if (in_array($section, ['all', 'utilization-chart'])) {
            $grossSub = DB::table('od_procedure_logs')
                ->where('office_id', $officeId)
                ->when($clinicNum !== null, fn ($q) => $q->where('ClinicNum', $clinicNum))
                ->select('ProvNum', DB::raw('SUM(ProcFee) AS gross'))
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$start, $end])
                ->groupBy('ProvNum');

            $adjSub = DB::table('od_adjustments')
                ->where('office_id', $officeId)
                ->when($clinicNum !== null, fn ($q) => $q->where('ClinicNum', $clinicNum))
                ->select('ProvNum', DB::raw('SUM(AdjAmt) AS adjustments'))
                ->whereBetween('AdjDate', [$start, $end])
                ->groupBy('ProvNum');

            $writeoffSub = DB::table('od_claim_procs')
                ->where('office_id', $officeId)
                ->when($clinicNum !== null, fn ($q) => $q->where('ClinicNum', $clinicNum))
                ->select('ProvNum', DB::raw('SUM(WriteOff) AS writeoffs'))
                ->whereBetween('ProcDate', [$start, $end])
                ->groupBy('ProvNum');

            $providers = DB::table('od_providers as pr')
                ->select(
                    'pr.ProvNum',
                    'pr.LName',
                    'pr.Abbr',
                    DB::raw('COALESCE(g.gross, 0) AS gross_production'),
                    DB::raw('COALESCE(a.adjustments, 0) AS adjustments'),
                    DB::raw('COALESCE(w.writeoffs, 0) AS writeoffs')
                )
                ->where('pr.office_id', $officeId)
                ->leftJoinSub($grossSub, 'g', 'pr.ProvNum', '=', 'g.ProvNum')
                ->leftJoinSub($adjSub, 'a', 'pr.ProvNum', '=', 'a.ProvNum')
                ->leftJoinSub($writeoffSub, 'w', 'pr.ProvNum', '=', 'w.ProvNum')
                ->where(function ($q) {
                    $q->whereRaw('COALESCE(g.gross, 0) != 0')
                        ->orWhereRaw('COALESCE(a.adjustments, 0) != 0')
                        ->orWhereRaw('COALESCE(w.writeoffs, 0) != 0');
                })
                ->get();

            $utilizationData = $providers->map(function ($p) {
                $net = $this->production->netFrom(
                    (float) $p->gross_production,
                    (float) $p->adjustments,
                    (float) $p->writeoffs
                );
                $providerName = ! empty($p->Abbr) ? $p->Abbr : (! empty($p->LName) ? $p->LName : (string) $p->ProvNum);

                return (object) [
                    'provider' => $providerName,
                    'production' => $net,
                    'net_production' => $net,
                    'gross_production' => round((float) $p->gross_production, 2),
                ];
            })
                ->filter(fn ($item) => $item->production > 0)
                ->sortByDesc('production')
                ->values()
                ->all();

            $response['utilization'] = $utilizationData;
        }

        // Adjustment Chart
        if (in_array($section, ['all', 'adjustment-chart'])) {
            $clinicFilter = $clinicNum !== null ? 'AND a.ClinicNum = ?' : '';
            $bindings = [$officeId, $officeId];
            if ($clinicNum !== null) {
                $bindings[] = $clinicNum;
            }
            $bindings[] = $start;
            $bindings[] = $end;

            $adjustmentData = DB::select("
                SELECT
                    d.ItemName AS label,
                    SUM(a.AdjAmt) AS value
                FROM od_adjustments a
                JOIN od_definitions d ON a.AdjType = d.DefNum AND d.office_id = ?
                WHERE a.office_id = ?
                  {$clinicFilter}
                  AND a.AdjDate BETWEEN ? AND ?
                GROUP BY d.DefNum, d.ItemName
                ORDER BY ABS(SUM(a.AdjAmt)) DESC
            ", $bindings);

            $response['adjustments_breakdown'] = $adjustmentData;
        }

        // Top Services Chart
        if (in_array($section, ['all', 'top-services-chart'])) {
            $clinicFilter = $clinicNum !== null ? 'AND pl.ClinicNum = ?' : '';
            $bindings = [$officeId, $officeId, $officeId];
            if ($clinicNum !== null) {
                $bindings[] = $clinicNum;
            }
            $bindings[] = $start;
            $bindings[] = $end;

            $topServicesData = DB::select("
                SELECT
                    d.ItemName AS label,
                    SUM(pl.ProcFee) AS value
                FROM od_procedure_logs pl
                JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
                JOIN od_definitions d ON pc.ProcCat = d.DefNum AND d.office_id = ?
                WHERE pl.office_id = ?
                  {$clinicFilter}
                  AND pl.ProcStatus IN ({$this->completedIn})
                  AND pl.ProcDate BETWEEN ? AND ?
                GROUP BY d.DefNum, d.ItemName
                ORDER BY SUM(pl.ProcFee) DESC
                LIMIT 5
            ", $bindings);

            $response['top_services'] = $topServicesData;
        }

        // Daily Revenue Data
        if (in_array($section, ['all', 'daily-revenue-chart'])) {
            $dailyGross = DB::table('od_procedure_logs')
                ->where('office_id', $officeId)
                ->when($clinicNum !== null, fn ($q) => $q->where('ClinicNum', $clinicNum))
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$start, $end])
                ->selectRaw('DATE(ProcDate) as date, '.MetricDefinitions::grossProduction('amount'))
                ->groupByRaw('DATE(ProcDate)')
                ->pluck('amount', 'date');

            $dailyAdj = DB::table('od_adjustments')
                ->where('office_id', $officeId)
                ->when($clinicNum !== null, fn ($q) => $q->where('ClinicNum', $clinicNum))
                ->whereBetween('AdjDate', [$start, $end])
                ->selectRaw('DATE(AdjDate) as date, '.MetricDefinitions::adjustments('amount'))
                ->groupByRaw('DATE(AdjDate)')
                ->pluck('amount', 'date');

            $dailyWriteOffs = DB::table('od_claim_procs')
                ->where('office_id', $officeId)
                ->when($clinicNum !== null, fn ($q) => $q->where('ClinicNum', $clinicNum))
                ->whereBetween('ProcDate', [$start, $end])
                ->selectRaw('DATE(ProcDate) as date, '.MetricDefinitions::writeOffs('amount'))
                ->groupByRaw('DATE(ProcDate)')
                ->pluck('amount', 'date');

            $dailyPatColl = DB::table('od_pay_splits')
                ->where('office_id', $officeId)
                ->when($clinicNum !== null, fn ($q) => $q->where('ClinicNum', $clinicNum))
                ->whereBetween('DatePay', [$start, $end])
                ->selectRaw('DATE(DatePay) as date, SUM(SplitAmt) as amount')
                ->groupByRaw('DATE(DatePay)')
                ->pluck('amount', 'date');

            $dailyInsColl = DB::table('od_claim_procs')
                ->where('office_id', $officeId)
                ->when($clinicNum !== null, fn ($q) => $q->where('ClinicNum', $clinicNum))
                ->whereBetween('DateCP', [$start, $end])
                ->where('Status', '!=', 0)
                ->selectRaw('DATE(DateCP) as date, SUM(InsPayAmt) as amount')
                ->groupByRaw('DATE(DateCP)')
                ->pluck('amount', 'date');

            $period = CarbonPeriod::create($start, $end);
            $allDates = collect();
            foreach ($period as $dt) {
                $allDates->push($dt->toDateString());
            }

            $response['daily_revenue'] = $allDates->map(function ($date) use ($dailyGross, $dailyAdj, $dailyWriteOffs, $dailyPatColl, $dailyInsColl) {
                $g = (float) ($dailyGross[$date] ?? 0);
                $a = (float) ($dailyAdj[$date] ?? 0) - (float) ($dailyWriteOffs[$date] ?? 0);
                $c = (float) ($dailyPatColl[$date] ?? 0) + (float) ($dailyInsColl[$date] ?? 0);
                $n = $g + $a;

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
            $visitStats = $this->patientVisits->dailyStats($start, $end, $clinics, [], $officeId);
            $dailyVisits = $visitStats['daily_visits'];
            $dailyNewVisits = $visitStats['daily_new_visits'];

            $dailyScheduled = OdAppointment::whereBetween('AptDateTime', [$start.' 00:00:00', $end.' 23:59:59'])
                ->where('office_id', $officeId)
                ->when($clinicNum !== null, fn ($q) => $q->where('ClinicNum', $clinicNum))
                ->scheduled()
                ->selectRaw('DATE(AptDateTime) as date, '.MetricDefinitions::scheduledPatients('cnt'))
                ->groupByRaw('DATE(AptDateTime)')
                ->pluck('cnt', 'date');

            $dailyNewScheduled = collect($this->bkNewPatientsScheduled($start, $end, $officeId, $clinicNum))
                ->groupBy('dates')
                ->map(fn ($group) => $group->count());

            $dailyCancelled = DB::table('od_procedure_logs as pl')
                ->join('od_procedures as pc', 'pl.CodeNum', '=', 'pc.CodeNum')
                ->where('pl.office_id', $officeId)
                ->when($clinicNum !== null, fn ($q) => $q->where('pl.ClinicNum', $clinicNum))
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
        $officeId = Office::getActiveOfficeId();
        $clinicNum = $this->resolveClinicNum($request, $officeId);

        return response()->json(
            $tab === 'collection'
            ? $this->scoreCardsCollection($start, $end, $provNum, $officeId, $clinicNum)
            : $this->scoreCardsProduction($start, $end, $provNum, $officeId, $clinicNum)
        );
    }

    private function providerExpr(string $alias): string
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return "COALESCE(NULLIF({$alias}.Abbr, ''), {$alias}.LName, 'Provider')";
        }

        return "COALESCE(NULLIF(TRIM(CONCAT(COALESCE({$alias}.LName, ''), CASE WHEN NULLIF({$alias}.PName, '') IS NOT NULL THEN CONCAT(', ', {$alias}.PName) ELSE '' END)), ''), {$alias}.Abbr, 'Provider')";
    }

    private function scoreCardsProduction(string $start, string $end, string $provNum, ?int $officeId = null, ?int $clinicNum = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $provFilter = '';
        $clinicFilter = '';
        $bindings = [$officeId, $start, $end];
        if ($clinicNum !== null) {
            $clinicFilter = 'AND pl.ClinicNum = ?';
            $bindings[] = $clinicNum;
        }
        if ($provNum !== '') {
            $provFilter = 'AND pl.ProvNum = ?';
            $bindings[] = $provNum;
        }

        $provExpr = $this->providerExpr('pr');

        $rows = DB::select("
            SELECT
                COALESCE(pr.ProvNum, 0) AS prov_num,
                {$provExpr} AS provider,
                COALESCE(pc.Descript, 'Procedure') AS service,
                COALESCE(pc.ProcCode, pl.CodeNum) AS service_code,
                COUNT(*)         AS cnt,
                CAST(pl.ProcFee AS DECIMAL(12,2)) AS service_fee,
                SUM(CAST(pl.ProcFee AS DECIMAL(12,2))) AS total_production
            FROM od_procedure_logs pl
            LEFT JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            LEFT JOIN od_providers pr ON pl.ProvNum = pr.ProvNum AND pr.office_id = ?
            WHERE pl.office_id = ?
              AND pl.ProcStatus IN ({$this->completedIn})
              AND DATE(REPLACE(pl.ProcDate, 'T', ' ')) BETWEEN ? AND ?
              {$clinicFilter}
              {$provFilter}
            GROUP BY pr.ProvNum, pr.Abbr, pr.LName, pr.PName, pc.CodeNum, pc.ProcCode, pc.Descript, CAST(pl.ProcFee AS DECIMAL(12,2)), pl.CodeNum
            ORDER BY total_production DESC, cnt DESC
        ", array_merge([$officeId, $officeId], $bindings));

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
            ->where('office_id', $officeId)
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

    private function scoreCardsCollection(string $start, string $end, string $provNum, ?int $officeId = null, ?int $clinicNum = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $provFilter = '';
        $clinicFilter = '';
        $bindings = [$officeId, $officeId, $officeId, $officeId, $start, $end];
        if ($clinicNum !== null) {
            $clinicFilter = 'AND ps.ClinicNum = ?';
            $bindings[] = $clinicNum;
        }
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
            LEFT JOIN od_providers pr ON ps.ProvNum = pr.ProvNum AND pr.office_id = ?
            LEFT JOIN od_payments p ON ps.PayNum = p.PayNum AND p.office_id = ?
            LEFT JOIN od_definitions pt ON p.PayType = pt.DefNum AND pt.office_id = ?
            WHERE ps.office_id = ?
              AND ps.DatePay BETWEEN ? AND ?
              AND ps.SplitAmt != 0
              {$clinicFilter}
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
            ->where('office_id', $officeId)
            ->whereIn('IsHidden', ['false', '0', 0, false])
            ->orderBy('LName')
            ->get(['ProvNum', 'Abbr', 'LName']);

        // Collection Scorecard - Top Counts and Top Payments Charts
        $topPayClinic = $clinicNum !== null ? 'AND p.ClinicNum = ?' : '';
        $topPayBindings = [$officeId, $officeId];
        if ($clinicNum !== null) {
            $topPayBindings[] = $clinicNum;
        }
        $topPayBindings[] = $start;
        $topPayBindings[] = $end;

        $topPayments = DB::select("
            SELECT 
                pt.ItemName AS PaymentType,
                COUNT(p.PayNum) AS CountValue,
                SUM(p.PayAmt) AS AmountValue
            FROM od_payments p
            LEFT JOIN od_definitions pt ON p.PayType = pt.DefNum AND pt.office_id = ?
            WHERE p.office_id = ?
              {$topPayClinic}
              AND p.PayDate BETWEEN ? AND ?
            GROUP BY pt.ItemName, p.PayType
            ORDER BY SUM(p.PayAmt) DESC
        ", $topPayBindings);

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
        $officeInput = $request->input('office_id');
        $officeId = ($officeInput !== null && $officeInput !== '' && $officeInput !== 'all')
            ? (int) $officeInput
            : ($officeInput === 'all' ? null : Office::getActiveOfficeId());
        $clinicNum = $this->resolveClinicNum($request, $officeId);

        $rows = match ($type) {
            'gross_production' => $this->bkGrossProduction($start, $end, $officeId, $clinicNum),
            'net_production' => $this->bkNetProduction($start, $end, $officeId, $clinicNum),
            'adjustment' => $this->bkAdjustment($start, $end, $officeId, $clinicNum),
            'collection' => $this->bkCollection($start, $end, $officeId, $clinicNum),
            'patient_visits' => $this->bkPatientVisits($start, $end, $officeId, $clinicNum),
            'new_patient_visits' => $this->bkNewPatientVisits($start, $end, $officeId, $clinicNum),
            'patients_scheduled' => $this->bkPatientsScheduled($start, $end, $officeId, $clinicNum),
            'new_patients_scheduled' => $this->bkNewPatientsScheduled($start, $end, $officeId, $clinicNum),
            'broken_cancelled' => $this->bkBrokenCancelled($start, $end, $officeId, $clinicNum),
            'avg_production_per_patient' => $this->bkAvgProductionPerPatient($start, $end, $officeId, $clinicNum),
            default => [],
        };

        return response()->json($rows);
    }

    // ── Gross Production ──────────────────────────────────────────────────────
    private function bkGrossProduction(string $start, string $end, ?int $officeId = null, ?int $clinicNum = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $patNameExpr = $isSqlite ? "(p.LName || ', ' || p.FName)" : "CONCAT(p.LName, ', ', p.FName)";
        $provIdExpr = $isSqlite ? "(pr.ProvNum || ' - ' || pr.Abbr)" : "CONCAT(pr.ProvNum, ' - ', pr.Abbr)";
        $provNameExpr = $isSqlite
            ? "CASE WHEN pr.PName IS NOT NULL AND pr.PName != '' THEN (pr.LName || ', ' || pr.PName) ELSE pr.LName END"
            : "CASE WHEN pr.PName IS NOT NULL AND pr.PName != '' THEN CONCAT(pr.LName, ', ', pr.PName) ELSE pr.LName END";

        $clinicFilter = $clinicNum !== null ? 'AND pl.ClinicNum = ?' : '';
        $bindings = [$officeId, $officeId, $officeId];
        if ($clinicNum !== null) {
            $bindings[] = $clinicNum;
        }
        $bindings[] = $start;
        $bindings[] = $end;

        $rows = DB::select("
            SELECT
                p.PatNum                                      AS patient_id,
                {$patNameExpr}                                AS patient_name,
                {$provIdExpr}                                 AS provider_ids,
                {$provNameExpr}                               AS providers,
                pl.ProcDate                                   AS dates,
                SUM(pl.ProcFee)                               AS amount
            FROM od_procedure_logs pl
            JOIN od_patients  p  ON pl.PatNum  = p.PatNum AND p.office_id = ?
            JOIN od_providers pr ON pl.ProvNum = pr.ProvNum AND pr.office_id = ?
            WHERE pl.office_id = ?
              {$clinicFilter}
              AND pl.ProcStatus IN ({$this->completedIn})
              AND pl.ProcDate BETWEEN ? AND ?
            GROUP BY p.PatNum, p.LName, p.FName,
                     pr.ProvNum, pr.Abbr, pr.LName, pr.PName,
                     pl.ProcDate
            ORDER BY pl.ProcDate, p.LName
        ", $bindings);

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
    private function bkNetProduction(string $start, string $end, ?int $officeId = null, ?int $clinicNum = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $patNameExpr = $isSqlite ? "(p.LName || ', ' || p.FName)" : "CONCAT(p.LName, ', ', p.FName)";
        $provIdExpr = $isSqlite ? "(pr.ProvNum || ' - ' || pr.Abbr)" : "CONCAT(pr.ProvNum, ' - ', pr.Abbr)";
        $provNameExpr = $isSqlite
            ? "CASE WHEN pr.PName IS NOT NULL AND pr.PName != '' THEN (pr.LName || ', ' || pr.PName) ELSE pr.LName END"
            : "CASE WHEN pr.PName IS NOT NULL AND pr.PName != '' THEN CONCAT(pr.LName, ', ', pr.PName) ELSE pr.LName END";

        $plClinic = $clinicNum !== null ? 'AND pl.ClinicNum = ?' : '';
        $aClinic = $clinicNum !== null ? 'AND a.ClinicNum = ?' : '';
        $cpClinic = $clinicNum !== null ? 'AND cp.ClinicNum = ?' : '';

        $bindings = [];
        // 1st query: pl
        $bindings = array_merge($bindings, [$officeId, $officeId, $officeId]);
        if ($clinicNum !== null) {
            $bindings[] = $clinicNum;
        }
        $bindings = array_merge($bindings, [$start, $end]);

        // 2nd query: a
        $bindings = array_merge($bindings, [$officeId, $officeId, $officeId]);
        if ($clinicNum !== null) {
            $bindings[] = $clinicNum;
        }
        $bindings = array_merge($bindings, [$start, $end]);

        // 3rd query: cp
        $bindings = array_merge($bindings, [$officeId, $officeId, $officeId]);
        if ($clinicNum !== null) {
            $bindings[] = $clinicNum;
        }
        $bindings = array_merge($bindings, [$start, $end]);

        $rows = DB::select("
            SELECT patient_id, patient_name, provider_ids, providers, dates, amount
            FROM (
                SELECT
                    p.PatNum                                          AS patient_id,
                    {$patNameExpr}                                    AS patient_name,
                    {$provIdExpr}                                     AS provider_ids,
                    {$provNameExpr}                                   AS providers,
                    pl.ProcDate                                       AS dates,
                    SUM(pl.ProcFee)                                   AS amount
                FROM od_procedure_logs pl
                JOIN od_patients  p  ON pl.PatNum  = p.PatNum AND p.office_id = ?
                JOIN od_providers pr ON pl.ProvNum = pr.ProvNum AND pr.office_id = ?
                WHERE pl.office_id = ?
                  {$plClinic}
                  AND pl.ProcStatus IN ({$this->completedIn})
                  AND pl.ProcDate BETWEEN ? AND ?
                GROUP BY p.PatNum, p.LName, p.FName,
                         pr.ProvNum, pr.Abbr, pr.LName, pr.PName, pl.ProcDate

                UNION ALL

                SELECT
                    p.PatNum                                          AS patient_id,
                    {$patNameExpr}                                    AS patient_name,
                    COALESCE({$provIdExpr}, '')                       AS provider_ids,
                    COALESCE({$provNameExpr}, '')                     AS providers,
                    a.AdjDate                                         AS dates,
                    SUM(a.AdjAmt)                                     AS amount
                FROM od_adjustments a
                JOIN od_patients  p  ON a.PatNum  = p.PatNum AND p.office_id = ?
                LEFT JOIN od_providers pr ON a.ProvNum = pr.ProvNum AND pr.office_id = ?
                WHERE a.office_id = ?
                  {$aClinic}
                  AND a.AdjDate BETWEEN ? AND ?
                GROUP BY p.PatNum, p.LName, p.FName,
                         pr.ProvNum, pr.Abbr, pr.LName, pr.PName, a.AdjDate

                UNION ALL

                SELECT
                    p.PatNum                                          AS patient_id,
                    {$patNameExpr}                                    AS patient_name,
                    COALESCE({$provIdExpr}, '')                       AS provider_ids,
                    COALESCE({$provNameExpr}, '')                     AS providers,
                    cp.ProcDate                                       AS dates,
                    -SUM(cp.WriteOff)                                 AS amount
                FROM od_claim_procs cp
                JOIN od_patients  p  ON cp.PatNum  = p.PatNum AND p.office_id = ?
                LEFT JOIN od_providers pr ON cp.ProvNum = pr.ProvNum AND pr.office_id = ?
                WHERE cp.office_id = ?
                  {$cpClinic}
                  AND cp.ProcDate BETWEEN ? AND ?
                  AND cp.WriteOff <> 0
                GROUP BY p.PatNum, p.LName, p.FName,
                         pr.ProvNum, pr.Abbr, pr.LName, pr.PName, cp.ProcDate
            ) combined
            ORDER BY dates, patient_name
        ", $bindings);

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
    private function bkAdjustment(string $start, string $end, ?int $officeId = null, ?int $clinicNum = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $patNameExpr = $isSqlite ? "(p.LName || ', ' || p.FName)" : "CONCAT(p.LName, ', ', p.FName)";
        $provIdExpr = $isSqlite ? "(pr.ProvNum || ' - ' || pr.Abbr)" : "CONCAT(pr.ProvNum, ' - ', pr.Abbr)";
        $provNameExpr = $isSqlite
            ? "CASE WHEN pr.PName IS NOT NULL AND pr.PName != '' THEN (pr.LName || ', ' || pr.PName) ELSE pr.LName END"
            : "CASE WHEN pr.PName IS NOT NULL AND pr.PName != '' THEN CONCAT(pr.LName, ', ', pr.PName) ELSE pr.LName END";

        $defMap = DB::table('od_definitions')
            ->where(function ($q) use ($officeId) {
                if ($officeId !== null) {
                    $q->where('office_id', $officeId)->orWhere('office_id', 1);
                }
            })
            ->where('Category', 1)
            ->pluck('ItemName', 'DefNum')
            ->toArray();

        $aClinic = $clinicNum !== null ? 'AND a.ClinicNum = ?' : '';
        $cpClinic = $clinicNum !== null ? 'AND cp.ClinicNum = ?' : '';

        $bindings = [];
        $bindings = array_merge($bindings, [$officeId, $officeId, $officeId]);
        if ($clinicNum !== null) {
            $bindings[] = $clinicNum;
        }
        $bindings = array_merge($bindings, [$start, $end]);

        $bindings = array_merge($bindings, [$officeId, $officeId, $officeId]);
        if ($clinicNum !== null) {
            $bindings[] = $clinicNum;
        }
        $bindings = array_merge($bindings, [$start, $end]);

        $rows = DB::select("
            SELECT
                p.PatNum                                          AS patient_id,
                {$patNameExpr}                                    AS patient_name,
                COALESCE({$provIdExpr}, '')                       AS provider_ids,
                COALESCE({$provNameExpr}, '')                     AS providers,
                a.AdjDate                                         AS dates,
                SUM(a.AdjAmt)                                     AS amount,
                a.AdjType                                         AS adj_type_id,
                'adjustment'                                      AS source_type
            FROM od_adjustments a
            JOIN od_patients  p  ON a.PatNum  = p.PatNum AND p.office_id = ?
            LEFT JOIN od_providers pr ON a.ProvNum = pr.ProvNum AND pr.office_id = ?
            WHERE a.office_id = ?
              {$aClinic}
              AND a.AdjDate BETWEEN ? AND ?
            GROUP BY p.PatNum, p.LName, p.FName,
                     pr.ProvNum, pr.Abbr, pr.LName, pr.PName,
                     a.AdjDate, a.AdjType

            UNION ALL

            SELECT
                p.PatNum                                          AS patient_id,
                COALESCE({$patNameExpr}, 'Insurance Payment')     AS patient_name,
                COALESCE({$provIdExpr}, '')                       AS provider_ids,
                COALESCE({$provNameExpr}, '')                     AS providers,
                cp.ProcDate                                       AS dates,
                -SUM(cp.WriteOff)                                 AS amount,
                0                                                 AS adj_type_id,
                'writeoff'                                        AS source_type
            FROM od_claim_procs cp
            JOIN od_patients  p  ON cp.PatNum  = p.PatNum AND p.office_id = ?
            LEFT JOIN od_providers pr ON cp.ProvNum = pr.ProvNum AND pr.office_id = ?
            WHERE cp.office_id = ?
              {$cpClinic}
              AND cp.ProcDate BETWEEN ? AND ?
              AND cp.WriteOff <> 0
            GROUP BY p.PatNum, p.LName, p.FName,
                     pr.ProvNum, pr.Abbr, pr.LName, pr.PName, cp.ProcDate

            ORDER BY dates, patient_name
        ", $bindings);

        return array_map(fn ($r) => [
            'patient_id' => $r->patient_id,
            'patient_name' => $r->patient_name,
            'provider_ids' => $r->provider_ids,
            'providers' => $r->providers,
            'dates' => $r->dates,
            'amount' => round((float) $r->amount, 2),
            'adj_type' => $r->source_type === 'writeoff'
                ? 'WriteOff'
                : ($defMap[$r->adj_type_id] ?? (((float) $r->amount >= 0 ? '+' : '-').' Adjustment (Type #'.$r->adj_type_id.')')),
        ], $rows);
    }

    // ── Collection ────────────────────────────────────────────────────────────
    private function bkCollection(string $start, string $end, ?int $officeId = null, ?int $clinicNum = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $patNameExpr = $isSqlite ? "(p.LName || ', ' || p.FName)" : "CONCAT(p.LName, ', ', p.FName)";
        $provIdExpr = $isSqlite ? "(pr.ProvNum || ' - ' || pr.Abbr)" : "CONCAT(pr.ProvNum, ' - ', pr.Abbr)";
        $provNameExpr = $isSqlite
            ? "CASE WHEN pr.PName IS NOT NULL AND pr.PName != '' THEN (pr.LName || ', ' || pr.PName) ELSE pr.LName END"
            : "CASE WHEN pr.PName IS NOT NULL AND pr.PName != '' THEN CONCAT(pr.LName, ', ', pr.PName) ELSE pr.LName END";

        $psClinic = $clinicNum !== null ? 'AND ps.ClinicNum = ?' : '';
        $cpClinic = $clinicNum !== null ? 'AND cp.ClinicNum = ?' : '';

        $bindings = [];
        $bindings = array_merge($bindings, [$officeId, $officeId, $officeId]);
        if ($clinicNum !== null) {
            $bindings[] = $clinicNum;
        }
        $bindings = array_merge($bindings, [$start, $end]);

        $bindings = array_merge($bindings, [$officeId, $officeId, $officeId, $officeId]);
        if ($clinicNum !== null) {
            $bindings[] = $clinicNum;
        }
        $bindings = array_merge($bindings, [$start, $end]);

        $rows = DB::select("
            SELECT
                COALESCE(p.PatNum, ps.PatNum, 0)                  AS patient_id,
                COALESCE({$patNameExpr}, 'Patient Payment')       AS patient_name,
                COALESCE({$provIdExpr}, '')                       AS provider_ids,
                COALESCE({$provNameExpr}, '')                     AS providers,
                ps.DatePay                                        AS dates,
                SUM(ps.SplitAmt)                                  AS amount
            FROM od_pay_splits ps
            LEFT JOIN od_patients  p  ON ps.PatNum  = p.PatNum AND p.office_id = ?
            LEFT JOIN od_providers pr ON ps.ProvNum = pr.ProvNum AND pr.office_id = ?
            WHERE ps.office_id = ?
              {$psClinic}
              AND ps.DatePay BETWEEN ? AND ?
            GROUP BY p.PatNum, ps.PatNum, p.LName, p.FName,
                     pr.ProvNum, pr.Abbr, pr.LName, pr.PName,
                     ps.DatePay

            UNION ALL

            SELECT
                COALESCE(p.PatNum, cp.PatNum, 0)                 AS patient_id,
                COALESCE({$patNameExpr}, 'Insurance Payment')     AS patient_name,
                COALESCE({$provIdExpr}, '')                       AS provider_ids,
                COALESCE({$provNameExpr}, '')                     AS providers,
                cp_pay.CheckDate                                  AS dates,
                SUM(cp.InsPayAmt)                                 AS amount
            FROM od_claim_payments cp_pay
            JOIN od_claim_procs cp ON cp.ClaimPaymentNum = cp_pay.ClaimPaymentNum AND cp.office_id = ?
            LEFT JOIN od_patients  p  ON cp.PatNum  = p.PatNum AND p.office_id = ?
            LEFT JOIN od_providers pr ON cp.ProvNum = pr.ProvNum AND pr.office_id = ?
            WHERE cp_pay.office_id = ?
              {$cpClinic}
              AND cp_pay.CheckDate BETWEEN ? AND ?
              AND cp.InsPayAmt != 0
            GROUP BY p.PatNum, p.LName, p.FName,
                     cp.PatNum,
                     pr.ProvNum, pr.Abbr, pr.LName, pr.PName,
                     cp_pay.CheckDate

            ORDER BY dates, patient_name
        ", $bindings);

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
    private function bkPatientVisits(string $start, string $end, ?int $officeId = null, ?int $clinicNum = null): array
    {
        $clinics = $clinicNum !== null ? [$clinicNum] : [];

        return $this->patientVisits->patientVisitsBreakdown($start, $end, $clinics, [], $officeId);
    }

    // ── New Patient Visits ────────────────────────────────────────────────────
    private function bkNewPatientVisits(string $start, string $end, ?int $officeId = null, ?int $clinicNum = null): array
    {
        $clinics = $clinicNum !== null ? [$clinicNum] : [];

        return $this->patientVisits->newPatientVisits($start, $end, $clinics, [], $officeId);
    }

    // ── Patients Scheduled ────────────────────────────────────────────────────
    private function bkPatientsScheduled(string $start, string $end, ?int $officeId = null, ?int $clinicNum = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $startDate = substr($start, 0, 10).' 00:00:00';
        $endDate = substr($end, 0, 10).' 23:59:59';
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $dateConcat = $isSqlite
            ? "GROUP_CONCAT(DISTINCT strftime('%Y-%m-%d', a.AptDateTime))"
            : "GROUP_CONCAT(DISTINCT DATE_FORMAT(a.AptDateTime, '%Y-%m-%d') ORDER BY a.AptDateTime SEPARATOR ', ')";
        $nameExpr = $isSqlite
            ? "COALESCE(p.LName || ', ' || p.FName, '')"
            : "COALESCE(CONCAT(p.LName, ', ', p.FName), '')";

        $clinicFilter = $clinicNum !== null ? 'AND a.ClinicNum = ?' : '';
        $bindings = [$officeId, $officeId];
        if ($clinicNum !== null) {
            $bindings[] = $clinicNum;
        }
        $bindings[] = $startDate;
        $bindings[] = $endDate;

        $rows = DB::select("
            SELECT
                a.PatNum                                                              AS patient_id,
                {$nameExpr}                                                          AS patient_name,
                {$dateConcat}                                                         AS dates,
                COUNT(DISTINCT DATE(a.AptDateTime))                                   AS count
            FROM od_appointments a
            LEFT JOIN od_patients p ON a.PatNum = p.PatNum AND p.office_id = ?
            WHERE a.office_id = ?
              {$clinicFilter}
              AND a.AptDateTime BETWEEN ? AND ?
              AND a.AptStatus IN (1, 2)
            GROUP BY a.PatNum, p.LName, p.FName
            ORDER BY count DESC, p.LName
        ", $bindings);

        return array_map(fn ($r) => [
            'patient_id' => $r->patient_id,
            'patient_name' => $r->patient_name,
            'dates' => $r->dates,
            'count' => (int) $r->count,
        ], $rows);
    }

    // ── New Patients Scheduled ────────────────────────────────────────────────
    private function bkNewPatientsScheduled(string $start, string $end, ?int $officeId = null, ?int $clinicNum = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $nameExpr = $isSqlite
            ? "COALESCE(p.LName || ', ' || p.FName, '')"
            : "COALESCE(CONCAT(p.LName, ', ', p.FName), '')";
        $dateExpr = $isSqlite
            ? "strftime('%Y-%m-%d', MIN(a.AptDateTime))"
            : "DATE_FORMAT(MIN(a.AptDateTime), '%Y-%m-%d')";

        $clinicFilter = $clinicNum !== null ? 'AND a.ClinicNum = ?' : '';
        $bindings = [$officeId, $officeId];
        if ($clinicNum !== null) {
            $bindings[] = $clinicNum;
        }
        $bindings[] = $start.' 00:00:00';
        $bindings[] = $end.' 23:59:59';
        $bindings[] = $officeId;
        $bindings[] = $start.' 00:00:00';
        $bindings[] = $officeId;
        $bindings[] = $start;

        $rows = DB::select("
            SELECT
                a.PatNum                                             AS patient_id,
                {$nameExpr}                                          AS patient_name,
                {$dateExpr}                                          AS dates,
                1                                                   AS count
            FROM od_appointments a
            LEFT JOIN od_patients p ON a.PatNum = p.PatNum AND p.office_id = ?
            WHERE a.office_id = ?
              {$clinicFilter}
              AND a.AptDateTime BETWEEN ? AND ?
              AND a.AptStatus IN (1, 2)
              AND a.IsNewPatient IN (1, '1', true, 'true')
              AND a.PatNum NOT IN (21216, 21231, 21254)
              AND NOT EXISTS (
                  SELECT 1 FROM od_appointments a_old
                  WHERE a_old.office_id = ?
                    AND a_old.PatNum = a.PatNum
                    AND a_old.AptStatus IN (1, 2)
                    AND a_old.IsNewPatient IN (1, '1', true, 'true')
                    AND a_old.AptDateTime < ?
              )
              AND NOT EXISTS (
                  SELECT 1 FROM od_procedure_logs pl_old
                  WHERE pl_old.office_id = ?
                    AND pl_old.PatNum = a.PatNum
                    AND pl_old.ProcDate < ?
                    AND pl_old.ProcStatus IN ('C', '2', 'D')
              )
            GROUP BY a.PatNum, p.LName, p.FName
            ORDER BY p.LName
        ", $bindings);

        return array_map(fn ($r) => [
            'patient_id' => $r->patient_id,
            'patient_name' => $r->patient_name,
            'dates' => $r->dates,
            'count' => (int) $r->count,
        ], $rows);
    }

    // ── Average Production Per Patient ────────────────────────────────────────
    private function bkAvgProductionPerPatient(string $start, string $end, ?int $officeId = null, ?int $clinicNum = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $clinicFilter = $clinicNum !== null ? 'AND pl.ClinicNum = ?' : '';
        $bindings = [$officeId, $officeId];
        if ($clinicNum !== null) {
            $bindings[] = $clinicNum;
        }
        $bindings[] = $start;
        $bindings[] = $end;

        $rows = DB::select("
            SELECT
                p.PatNum                         AS patient_id,
                CONCAT(p.LName, ', ', p.FName)   AS patient_name,
                COUNT(DISTINCT pl.ProcDate)       AS count,
                SUM(pl.ProcFee)                  AS amount
            FROM od_procedure_logs pl
            JOIN od_patients p ON pl.PatNum = p.PatNum AND p.office_id = ?
            WHERE pl.office_id = ?
              {$clinicFilter}
              AND pl.ProcStatus IN ({$this->completedIn})
              AND pl.ProcDate BETWEEN ? AND ?
            GROUP BY p.PatNum, p.LName, p.FName
            ORDER BY p.LName
        ", $bindings);

        return array_map(fn ($r) => [
            'patient_id' => $r->patient_id,
            'patient_name' => $r->patient_name,
            'count' => (int) $r->count,
            'amount' => round((float) $r->amount, 2),
        ], $rows);
    }

    // ── Broken & Cancelled Appointments ────────────────────────────────────────
    private function bkBrokenCancelled(string $start, string $end, ?int $officeId = null, ?int $clinicNum = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        $clinicFilter = $clinicNum !== null ? 'AND pl.ClinicNum = ?' : '';
        $bindings = [$officeId, $officeId, $officeId];
        if ($clinicNum !== null) {
            $bindings[] = $clinicNum;
        }
        $bindings[] = $start;
        $bindings[] = $end;

        $rows = DB::select("
            SELECT
                p.PatNum                         AS patient_id,
                CONCAT(p.LName, ', ', p.FName)   AS patient_name,
                pl.ProcDate                      AS dates,
                pc.ProcCode                      AS service_codes
            FROM od_procedure_logs pl
            JOIN od_patients   p  ON pl.PatNum  = p.PatNum AND p.office_id = ?
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = ?
            WHERE pl.office_id = ?
              {$clinicFilter}
              AND pl.ProcStatus IN ({$this->completedIn})
              AND pc.ProcCode IN ('D9986', 'D9987')
              AND pl.ProcDate BETWEEN ? AND ?
            ORDER BY dates, p.LName
        ", $bindings);

        return array_map(fn ($r) => [
            'patient_id' => $r->patient_id,
            'patient_name' => $r->patient_name,
            'dates' => $r->dates,
            'type' => $r->service_codes === 'D9986' ? 'No-Show' : 'Cancelled',
        ], $rows);
    }
}
