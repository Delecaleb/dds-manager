<?php

namespace App\Http\Controllers;

use App\Domain\Patient\PatientVisitService;
use App\Domain\Production\ProductionService;
use App\Domain\Support\ClinicRegistry;
use App\Domain\Support\LocationSelection;
use App\Domain\Support\MetricFilter;
use App\Domain\Support\ProcStatus;
use App\Helpers\MetricDefinitions;
use App\Models\BasicSetting;
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

    private function resolveLocations(Request $request): LocationSelection
    {
        if ($request->filled('locations')) {
            return $this->clinics->select($request->input('locations'));
        }

        if ($request->filled('office_id') || $request->filled('clinic_num') || $request->filled('clinic_id')) {
            $officeInput = $request->input('office_id');
            if ($officeInput === 'all') {
                return $this->clinics->select('all');
            }
            $officeId = ($officeInput !== null && $officeInput !== '') ? (int) $officeInput : Office::getActiveOfficeId();
            $clinicInput = $request->input('clinic_id') ?? $request->input('clinic_num');
            if ($clinicInput === null && $officeId !== null) {
                $clinicInput = $this->clinics->getActiveClinicNum($officeId);
            }
            if ($clinicInput !== null && $clinicInput !== '' && $clinicInput !== 'all') {
                return $this->clinics->select("{$officeId}:{$clinicInput}");
            }
            if ($officeId !== null) {
                return $this->clinics->select((string) $officeId);
            }
        }

        return $this->clinics->select(null);
    }

    /**
     * Build SQL where clause and parameter bindings for multi-office/multi-clinic scoping.
     *
     * @param  array<int, int[]>  $scopes
     * @return array{0: string, 1: array}
     */
    private function buildScopeSql(array $scopes, string $alias = ''): array
    {
        $prefix = $alias !== '' ? "{$alias}." : '';
        $clauses = [];
        $bindings = [];
        foreach ($scopes as $officeId => $scopedClinics) {
            if (! empty($scopedClinics)) {
                $inPlaceholders = implode(',', array_fill(0, count($scopedClinics), '?'));
                $clauses[] = "({$prefix}office_id = ? AND {$prefix}ClinicNum IN ({$inPlaceholders}))";
                $bindings[] = $officeId;
                foreach ($scopedClinics as $cNum) {
                    $bindings[] = $cNum;
                }
            } else {
                $clauses[] = "({$prefix}office_id = ?)";
                $bindings[] = $officeId;
            }
        }

        if (empty($clauses)) {
            return ['1 = 0', []];
        }

        return ['('.implode(' OR ', $clauses).')', $bindings];
    }

    public function index()
    {
        $locations = $this->clinics->locations();
        $selectedLocations = $this->clinics->select(request('locations'))->keys();

        return view('financials.index', compact('locations', 'selectedLocations'));
    }

    public function revenue(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
        $selection = $this->resolveLocations($request);
        $scopes = $selection->scopes();

        $gross = 0.0;
        $net = 0.0;
        $adj = 0.0;
        $wo = 0.0;
        $coll = 0.0;

        foreach ($scopes as $officeId => $scopedClinics) {
            $analysis = $this->financialAnalytics->filterAnalysis($start, $end, $officeId, $scopedClinics);
            $gross += (float) ($analysis['gross_production'] ?? 0);
            $net += (float) ($analysis['net_production'] ?? 0);
            $adj += (float) ($analysis['adjustments'] ?? 0);
            $wo += (float) ($analysis['writeoffs'] ?? 0);
            $coll += (float) ($analysis['collections'] ?? 0);
        }

        $adjRate = $gross > 0 ? round((abs($adj) / $gross) * 100, 2) : 0;
        $primaryOfficeId = array_key_first($scopes) ?? (Office::getActiveOfficeId() ?? 1);
        $basicSettings = BasicSetting::forOffice($primaryOfficeId);
        $metricBasis = $basicSettings->collection_rate_metric ?? 'net';
        $prodForRate = $metricBasis === 'gross' ? $gross : $net;
        $collRate = $prodForRate > 0 ? round(($coll / $prodForRate) * 100, 2) : 0;

        return response()->json([
            'gross_production' => round($gross, 2),
            'net_production' => round($net, 2),
            'adjustments' => round($adj, 2),
            'adjustment' => round($adj, 2),
            'writeoffs' => round($wo, 2),
            'collections' => round($coll, 2),
            'collection' => round($coll, 2),
            'adjustment_rate' => $adjRate,
            'collection_rate' => $collRate,
        ]);
    }

    public function data(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
        $section = $request->input('section', 'all');
        $selection = $this->resolveLocations($request);
        $scopes = $selection->scopes();

        $response = [];

        if (in_array($section, ['all', 'revenue-kpis', 'revenue'])) {
            $gross = 0.0;
            $net = 0.0;
            $adj = 0.0;
            $wo = 0.0;
            $coll = 0.0;
            foreach ($scopes as $officeId => $scopedClinics) {
                $analysis = $this->financialAnalytics->filterAnalysis($start, $end, $officeId, $scopedClinics);
                $gross += (float) ($analysis['gross_production'] ?? 0);
                $net += (float) ($analysis['net_production'] ?? 0);
                $adj += (float) ($analysis['adjustments'] ?? 0);
                $wo += (float) ($analysis['writeoffs'] ?? 0);
                $coll += (float) ($analysis['collections'] ?? 0);
            }
            $adjRate = $gross > 0 ? round((abs($adj) / $gross) * 100, 2) : 0;
            $primaryOfficeId = array_key_first($scopes) ?? (Office::getActiveOfficeId() ?? 1);
            $basicSettings = BasicSetting::forOffice($primaryOfficeId);
            $metricBasis = $basicSettings->collection_rate_metric ?? 'net';
            $prodForRate = $metricBasis === 'gross' ? $gross : $net;
            $collRate = $prodForRate > 0 ? round(($coll / $prodForRate) * 100, 2) : 0;

            $response = array_merge($response, [
                'gross_production' => round($gross, 2),
                'net_production' => round($net, 2),
                'adjustments' => round($adj, 2),
                'adjustment' => round($adj, 2),
                'writeoffs' => round($wo, 2),
                'collections' => round($coll, 2),
                'collection' => round($coll, 2),
                'adjustment_rate' => $adjRate,
                'collection_rate' => $collRate,
            ]);
        }

        if (in_array($section, ['all', 'patient-kpis'])) {
            $scheduled = 0;
            $visited = 0;
            $netProduction = 0.0;
            $newPatientVisits = 0;
            $newPatientsScheduled = 0;

            foreach ($scopes as $officeId => $scopedClinics) {
                $filter = new MetricFilter($start, $end, $scopedClinics, [], null, $officeId);
                $scheduled += (new OdAppointment)->scheduledPatients($start, $end, $officeId, $scopedClinics);
                $visited += $this->patientVisits->patientVisits($start, $end, $scopedClinics, [], $officeId);
                $netProduction += $this->production->netProduction($filter);
                $newPatientVisits += $this->patientVisits->newPatientCount($start, $end, $scopedClinics, [], $officeId);
                $newPatientsScheduled += count($this->bkNewPatientsScheduledScopes($start, $end, [$officeId => $scopedClinics]));
            }

            $patientAvgProduction = $visited > 0 ? round($netProduction / $visited, 2) : 0;

            $response = array_merge($response, [
                'patient_scheduled' => $scheduled,
                'patient_visits' => $visited,
                'patient_avg_production' => $patientAvgProduction,
                'new_patient_visit' => $newPatientVisits,
                'new_patients_scheduled' => $newPatientsScheduled,
            ]);
        }

        // Utilization Data Chart (Provider Production)
        if (in_array($section, ['all', 'utilization-chart'])) {
            $allProviders = collect();
            foreach ($scopes as $officeId => $scopedClinics) {
                $grossSub = DB::table('od_procedure_logs')
                    ->where('office_id', $officeId)
                    ->when(! empty($scopedClinics), fn ($q) => $q->whereIn('ClinicNum', $scopedClinics))
                    ->select('ProvNum', DB::raw('SUM(ProcFee) AS gross'))
                    ->whereIn('ProcStatus', ProcStatus::completed())
                    ->whereBetween('ProcDate', [$start, $end])
                    ->groupBy('ProvNum');

                $adjSub = DB::table('od_adjustments')
                    ->where('office_id', $officeId)
                    ->when(! empty($scopedClinics), fn ($q) => $q->whereIn('ClinicNum', $scopedClinics))
                    ->select('ProvNum', DB::raw('SUM(AdjAmt) AS adjustments'))
                    ->whereBetween('AdjDate', [$start, $end])
                    ->groupBy('ProvNum');

                $writeoffSub = DB::table('od_claim_procs')
                    ->where('office_id', $officeId)
                    ->when(! empty($scopedClinics), fn ($q) => $q->whereIn('ClinicNum', $scopedClinics))
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

                $mapped = $providers->map(function ($p) {
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
                });

                $allProviders = $allProviders->merge($mapped);
            }

            $response['utilization'] = $allProviders
                ->filter(fn ($item) => $item->production > 0)
                ->sortByDesc('production')
                ->values()
                ->all();
        }

        // Adjustment Chart
        if (in_array($section, ['all', 'adjustment-chart'])) {
            [$scopeSql, $scopeBindings] = $this->buildScopeSql($scopes, 'a');
            $adjQuery = DB::table('od_adjustments as a')
                ->join('od_definitions as d', function ($join) {
                    $join->on('a.AdjType', '=', 'd.DefNum')
                        ->on('a.office_id', '=', 'd.office_id');
                })
                ->whereBetween('a.AdjDate', [$start, $end])
                ->whereRaw($scopeSql, $scopeBindings)
                ->select('d.ItemName as label', DB::raw('SUM(a.AdjAmt) as value'))
                ->groupBy('d.DefNum', 'd.ItemName')
                ->orderByRaw('ABS(SUM(a.AdjAmt)) DESC');

            $response['adjustments_breakdown'] = $adjQuery->get();
        }

        // Top Services Chart
        if (in_array($section, ['all', 'top-services-chart'])) {
            [$scopeSql, $scopeBindings] = $this->buildScopeSql($scopes, 'pl');
            $topServicesQuery = DB::table('od_procedure_logs as pl')
                ->join('od_procedures as pc', function ($join) {
                    $join->on('pl.CodeNum', '=', 'pc.CodeNum')
                        ->on('pl.office_id', '=', 'pc.office_id');
                })
                ->join('od_definitions as d', function ($join) {
                    $join->on('pc.ProcCat', '=', 'd.DefNum')
                        ->on('pc.office_id', '=', 'd.office_id');
                })
                ->whereIn('pl.ProcStatus', ProcStatus::completed())
                ->whereBetween('pl.ProcDate', [$start, $end])
                ->whereRaw($scopeSql, $scopeBindings)
                ->select('d.ItemName as label', DB::raw('SUM(pl.ProcFee) as value'))
                ->groupBy('d.DefNum', 'd.ItemName')
                ->orderByRaw('SUM(pl.ProcFee) DESC')
                ->limit(5);

            $response['top_services'] = $topServicesQuery->get();
        }

        // Daily Revenue Data
        if (in_array($section, ['all', 'daily-revenue-chart'])) {
            [$plSql, $plBindings] = $this->buildScopeSql($scopes, 'od_procedure_logs');
            $dailyGross = DB::table('od_procedure_logs')
                ->whereRaw($plSql, $plBindings)
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$start, $end])
                ->selectRaw('DATE(ProcDate) as date, '.MetricDefinitions::grossProduction('amount'))
                ->groupByRaw('DATE(ProcDate)')
                ->pluck('amount', 'date');

            [$adjSql, $adjBindings] = $this->buildScopeSql($scopes, 'od_adjustments');
            $dailyAdj = DB::table('od_adjustments')
                ->whereRaw($adjSql, $adjBindings)
                ->whereBetween('AdjDate', [$start, $end])
                ->selectRaw('DATE(AdjDate) as date, '.MetricDefinitions::adjustments('amount'))
                ->groupByRaw('DATE(AdjDate)')
                ->pluck('amount', 'date');

            [$cpSql, $cpBindings] = $this->buildScopeSql($scopes, 'od_claim_procs');
            $dailyWriteOffs = DB::table('od_claim_procs')
                ->whereRaw($cpSql, $cpBindings)
                ->whereBetween('ProcDate', [$start, $end])
                ->selectRaw('DATE(ProcDate) as date, '.MetricDefinitions::writeOffs('amount'))
                ->groupByRaw('DATE(ProcDate)')
                ->pluck('amount', 'date');

            [$psSql, $psBindings] = $this->buildScopeSql($scopes, 'od_pay_splits');
            $dailyPatColl = DB::table('od_pay_splits')
                ->whereRaw($psSql, $psBindings)
                ->whereBetween('DatePay', [$start, $end])
                ->selectRaw('DATE(DatePay) as date, SUM(SplitAmt) as amount')
                ->groupByRaw('DATE(DatePay)')
                ->pluck('amount', 'date');

            $dailyInsColl = DB::table('od_claim_procs')
                ->whereRaw($cpSql, $cpBindings)
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
            $dailyVisits = [];
            $dailyNewVisits = [];
            foreach ($scopes as $officeId => $scopedClinics) {
                $visitStats = $this->patientVisits->dailyStats($start, $end, $scopedClinics, [], $officeId);
                foreach ($visitStats['daily_visits'] as $d => $v) {
                    $dailyVisits[$d] = ($dailyVisits[$d] ?? 0) + $v;
                }
                foreach ($visitStats['daily_new_visits'] as $d => $v) {
                    $dailyNewVisits[$d] = ($dailyNewVisits[$d] ?? 0) + $v;
                }
            }

            [$aptSql, $aptBindings] = $this->buildScopeSql($scopes, 'od_appointments');
            $dailyScheduled = OdAppointment::whereBetween('AptDateTime', [$start.' 00:00:00', $end.' 23:59:59'])
                ->whereRaw($aptSql, $aptBindings)
                ->scheduled()
                ->selectRaw('DATE(AptDateTime) as date, '.MetricDefinitions::scheduledPatients('cnt'))
                ->groupByRaw('DATE(AptDateTime)')
                ->pluck('cnt', 'date');

            $dailyNewScheduled = collect($this->bkNewPatientsScheduledScopes($start, $end, $scopes))
                ->groupBy('dates')
                ->map(fn ($group) => $group->count());

            [$plSql, $plBindings] = $this->buildScopeSql($scopes, 'pl');
            $dailyCancelled = DB::table('od_procedure_logs as pl')
                ->join('od_procedures as pc', function ($join) {
                    $join->on('pl.CodeNum', '=', 'pc.CodeNum')
                        ->on('pl.office_id', '=', 'pc.office_id');
                })
                ->whereRaw($plSql, $plBindings)
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
        $selection = $this->resolveLocations($request);
        $scopes = $selection->scopes();

        return response()->json(
            $tab === 'collection'
            ? $this->scoreCardsCollection($start, $end, $provNum, $scopes)
            : $this->scoreCardsProduction($start, $end, $provNum, $scopes)
        );
    }

    private function providerExpr(string $alias): string
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return "COALESCE(NULLIF({$alias}.Abbr, ''), {$alias}.LName, 'Provider')";
        }

        return "COALESCE(NULLIF(TRIM(CONCAT(COALESCE({$alias}.LName, ''), CASE WHEN NULLIF({$alias}.PName, '') IS NOT NULL THEN CONCAT(', ', {$alias}.PName) ELSE '' END)), ''), {$alias}.Abbr, 'Provider')";
    }

    private function scoreCardsProduction(string $start, string $end, string $provNum, array $scopes): array
    {
        [$scopeSql, $scopeBindings] = $this->buildScopeSql($scopes, 'pl');
        $provFilter = '';
        $provBindings = [];
        if ($provNum !== '') {
            $provFilter = 'AND pl.ProvNum = ?';
            $provBindings[] = $provNum;
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
            LEFT JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = pl.office_id
            LEFT JOIN od_providers pr ON pl.ProvNum = pr.ProvNum AND pr.office_id = pl.office_id
            WHERE {$scopeSql}
              AND pl.ProcStatus IN ({$this->completedIn})
              AND DATE(REPLACE(pl.ProcDate, 'T', ' ')) BETWEEN ? AND ?
              {$provFilter}
            GROUP BY pr.ProvNum, pr.Abbr, pr.LName, pr.PName, pc.CodeNum, pc.ProcCode, pc.Descript, CAST(pl.ProcFee AS DECIMAL(12,2)), pl.CodeNum
            ORDER BY total_production DESC, cnt DESC
        ", array_merge($scopeBindings, [$start, $end], $provBindings));

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
        $uniquePriced = count($rows);

        $byCount = $rows;
        usort($byCount, fn ($a, $b) => $b->cnt <=> $a->cnt);

        $providers = DB::table('od_providers')
            ->whereIn('office_id', array_keys($scopes))
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

    private function scoreCardsCollection(string $start, string $end, string $provNum, array $scopes): array
    {
        [$scopeSql, $scopeBindings] = $this->buildScopeSql($scopes, 'ps');
        $provFilter = '';
        $provBindings = [];
        if ($provNum !== '') {
            $provFilter = 'AND ps.ProvNum = ?';
            $provBindings[] = $provNum;
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
            LEFT JOIN od_providers pr ON ps.ProvNum = pr.ProvNum AND pr.office_id = ps.office_id
            LEFT JOIN od_payments p ON ps.PayNum = p.PayNum AND p.office_id = ps.office_id
            LEFT JOIN od_definitions pt ON p.PayType = pt.DefNum AND pt.office_id = p.office_id
            WHERE {$scopeSql}
              AND ps.DatePay BETWEEN ? AND ?
              AND ps.SplitAmt != 0
              {$provFilter}
            ORDER BY ps.DatePay DESC
        ", array_merge($scopeBindings, [$start, $end], $provBindings));

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
            ->whereIn('office_id', array_keys($scopes))
            ->whereIn('IsHidden', ['false', '0', 0, false])
            ->orderBy('LName')
            ->get(['ProvNum', 'Abbr', 'LName']);

        // Collection Scorecard - Top Payments Chart
        [$pScopeSql, $pScopeBindings] = $this->buildScopeSql($scopes, 'p');
        $topPayments = DB::select("
            SELECT 
                pt.ItemName AS PaymentType,
                COUNT(p.PayNum) AS CountValue,
                SUM(p.PayAmt) AS AmountValue
            FROM od_payments p
            LEFT JOIN od_definitions pt ON p.PayType = pt.DefNum AND pt.office_id = p.office_id
            WHERE {$pScopeSql}
              AND p.PayDate BETWEEN ? AND ?
            GROUP BY pt.ItemName, p.PayType
            ORDER BY SUM(p.PayAmt) DESC
        ", array_merge($pScopeBindings, [$start, $end]));

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
        $selection = $this->resolveLocations($request);
        $scopes = $selection->scopes();

        $rows = match ($type) {
            'gross_production' => $this->bkGrossProduction($start, $end, $scopes),
            'net_production' => $this->bkNetProduction($start, $end, $scopes),
            'adjustment' => $this->bkAdjustment($start, $end, $scopes),
            'collection' => $this->bkCollection($start, $end, $scopes),
            'patient_visits' => $this->bkPatientVisits($start, $end, $scopes),
            'new_patient_visits' => $this->bkNewPatientVisits($start, $end, $scopes),
            'patients_scheduled' => $this->bkPatientsScheduled($start, $end, $scopes),
            'new_patients_scheduled' => $this->bkNewPatientsScheduledScopes($start, $end, $scopes),
            'broken_cancelled' => $this->bkBrokenCancelled($start, $end, $scopes),
            'avg_production_per_patient' => $this->bkAvgProductionPerPatient($start, $end, $scopes),
            default => [],
        };

        return response()->json($rows);
    }

    // ── Gross Production ──────────────────────────────────────────────────────
    private function bkGrossProduction(string $start, string $end, array $scopes): array
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $patNameExpr = $isSqlite ? "(p.LName || ', ' || p.FName)" : "CONCAT(p.LName, ', ', p.FName)";
        $provIdExpr = $isSqlite ? "(pr.ProvNum || ' - ' || pr.Abbr)" : "CONCAT(pr.ProvNum, ' - ', pr.Abbr)";
        $provNameExpr = $isSqlite
            ? "CASE WHEN pr.PName IS NOT NULL AND pr.PName != '' THEN (pr.LName || ', ' || pr.PName) ELSE pr.LName END"
            : "CASE WHEN pr.PName IS NOT NULL AND pr.PName != '' THEN CONCAT(pr.LName, ', ', pr.PName) ELSE pr.LName END";

        [$scopeSql, $scopeBindings] = $this->buildScopeSql($scopes, 'pl');

        $rows = DB::select("
            SELECT
                p.PatNum                                      AS patient_id,
                pl.office_id                                  AS office_id,
                {$patNameExpr}                                AS patient_name,
                {$provIdExpr}                                 AS provider_ids,
                {$provNameExpr}                               AS providers,
                pl.ProcDate                                   AS dates,
                SUM(pl.ProcFee)                               AS amount
            FROM od_procedure_logs pl
            JOIN od_patients  p  ON pl.PatNum  = p.PatNum AND p.office_id = pl.office_id
            JOIN od_providers pr ON pl.ProvNum = pr.ProvNum AND pr.office_id = pl.office_id
            WHERE {$scopeSql}
              AND pl.ProcStatus IN ({$this->completedIn})
              AND pl.ProcDate BETWEEN ? AND ?
            GROUP BY p.PatNum, pl.office_id, p.LName, p.FName,
                     pr.ProvNum, pr.Abbr, pr.LName, pr.PName,
                     pl.ProcDate
            HAVING SUM(pl.ProcFee) != 0
            ORDER BY pl.ProcDate, p.LName
        ", array_merge($scopeBindings, [$start, $end]));

        $filteredRows = array_filter($rows, fn ($r) => round((float) $r->amount, 2) != 0.0);

        return array_values(array_map(fn ($r) => [
            'patient_id' => $r->patient_id,
            'office_id' => (int) $r->office_id,
            'patient_name' => $r->patient_name,
            'provider_ids' => $r->provider_ids,
            'providers' => $r->providers,
            'dates' => $r->dates,
            'amount' => round((float) $r->amount, 2),
        ], $filteredRows));
    }

    // ── Net Production (procedures + adjustments combined) ───────────────────
    private function bkNetProduction(string $start, string $end, array $scopes): array
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $patNameExpr = $isSqlite ? "(p.LName || ', ' || p.FName)" : "CONCAT(p.LName, ', ', p.FName)";
        $provIdExpr = $isSqlite ? "(pr.ProvNum || ' - ' || pr.Abbr)" : "CONCAT(pr.ProvNum, ' - ', pr.Abbr)";
        $provNameExpr = $isSqlite
            ? "CASE WHEN pr.PName IS NOT NULL AND pr.PName != '' THEN (pr.LName || ', ' || pr.PName) ELSE pr.LName END"
            : "CASE WHEN pr.PName IS NOT NULL AND pr.PName != '' THEN CONCAT(pr.LName, ', ', pr.PName) ELSE pr.LName END";

        [$plSql, $plBindings] = $this->buildScopeSql($scopes, 'pl');
        [$aSql, $aBindings] = $this->buildScopeSql($scopes, 'a');
        [$cpSql, $cpBindings] = $this->buildScopeSql($scopes, 'cp');

        $rows = DB::select("
            SELECT patient_id, office_id, patient_name, provider_ids, providers, dates, amount
            FROM (
                SELECT
                    p.PatNum                                          AS patient_id,
                    pl.office_id                                      AS office_id,
                    {$patNameExpr}                                    AS patient_name,
                    {$provIdExpr}                                     AS provider_ids,
                    {$provNameExpr}                                   AS providers,
                    pl.ProcDate                                       AS dates,
                    SUM(pl.ProcFee)                                   AS amount
                FROM od_procedure_logs pl
                JOIN od_patients  p  ON pl.PatNum  = p.PatNum AND p.office_id = pl.office_id
                JOIN od_providers pr ON pl.ProvNum = pr.ProvNum AND pr.office_id = pl.office_id
                WHERE {$plSql}
                  AND pl.ProcStatus IN ({$this->completedIn})
                  AND pl.ProcDate BETWEEN ? AND ?
                GROUP BY p.PatNum, pl.office_id, p.LName, p.FName,
                         pr.ProvNum, pr.Abbr, pr.LName, pr.PName, pl.ProcDate

                UNION ALL

                SELECT
                    p.PatNum                                          AS patient_id,
                    a.office_id                                       AS office_id,
                    {$patNameExpr}                                    AS patient_name,
                    COALESCE({$provIdExpr}, '')                       AS provider_ids,
                    COALESCE({$provNameExpr}, '')                     AS providers,
                    a.AdjDate                                         AS dates,
                    SUM(a.AdjAmt)                                     AS amount
                FROM od_adjustments a
                JOIN od_patients  p  ON a.PatNum  = p.PatNum AND p.office_id = a.office_id
                LEFT JOIN od_providers pr ON a.ProvNum = pr.ProvNum AND pr.office_id = a.office_id
                WHERE {$aSql}
                  AND a.AdjDate BETWEEN ? AND ?
                GROUP BY p.PatNum, a.office_id, p.LName, p.FName,
                         pr.ProvNum, pr.Abbr, pr.LName, pr.PName, a.AdjDate

                UNION ALL

                SELECT
                    p.PatNum                                          AS patient_id,
                    cp.office_id                                      AS office_id,
                    {$patNameExpr}                                    AS patient_name,
                    COALESCE({$provIdExpr}, '')                       AS provider_ids,
                    COALESCE({$provNameExpr}, '')                     AS providers,
                    cp.ProcDate                                       AS dates,
                    -SUM(cp.WriteOff)                                 AS amount
                FROM od_claim_procs cp
                JOIN od_patients  p  ON cp.PatNum  = p.PatNum AND p.office_id = cp.office_id
                LEFT JOIN od_providers pr ON cp.ProvNum = pr.ProvNum AND pr.office_id = cp.office_id
                WHERE {$cpSql}
                  AND cp.ProcDate BETWEEN ? AND ?
                  AND cp.WriteOff <> 0
                GROUP BY p.PatNum, cp.office_id, p.LName, p.FName,
                         pr.ProvNum, pr.Abbr, pr.LName, pr.PName, cp.ProcDate
            ) combined
            ORDER BY dates, patient_name
        ", array_merge($plBindings, [$start, $end], $aBindings, [$start, $end], $cpBindings, [$start, $end]));

        return array_map(fn ($r) => [
            'patient_id' => $r->patient_id,
            'office_id' => (int) $r->office_id,
            'patient_name' => $r->patient_name,
            'provider_ids' => $r->provider_ids,
            'providers' => $r->providers,
            'dates' => $r->dates,
            'amount' => round((float) $r->amount, 2),
        ], $rows);
    }

    // ── Adjustment ────────────────────────────────────────────────────────────
    private function bkAdjustment(string $start, string $end, array $scopes): array
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $patNameExpr = $isSqlite ? "(p.LName || ', ' || p.FName)" : "CONCAT(p.LName, ', ', p.FName)";
        $provIdExpr = $isSqlite ? "(pr.ProvNum || ' - ' || pr.Abbr)" : "CONCAT(pr.ProvNum, ' - ', pr.Abbr)";
        $provNameExpr = $isSqlite
            ? "CASE WHEN pr.PName IS NOT NULL AND pr.PName != '' THEN (pr.LName || ', ' || pr.PName) ELSE pr.LName END"
            : "CASE WHEN pr.PName IS NOT NULL AND pr.PName != '' THEN CONCAT(pr.LName, ', ', pr.PName) ELSE pr.LName END";

        $defMap = DB::table('od_definitions')
            ->whereIn('office_id', array_keys($scopes))
            ->where('Category', 1)
            ->pluck('ItemName', 'DefNum')
            ->toArray();

        [$aSql, $aBindings] = $this->buildScopeSql($scopes, 'a');
        [$cpSql, $cpBindings] = $this->buildScopeSql($scopes, 'cp');

        $rows = DB::select("
            SELECT
                p.PatNum                                          AS patient_id,
                a.office_id                                       AS office_id,
                {$patNameExpr}                                    AS patient_name,
                COALESCE({$provIdExpr}, '')                       AS provider_ids,
                COALESCE({$provNameExpr}, '')                     AS providers,
                a.AdjDate                                         AS dates,
                SUM(a.AdjAmt)                                     AS amount,
                a.AdjType                                         AS adj_type_id,
                'adjustment'                                      AS source_type
            FROM od_adjustments a
            JOIN od_patients  p  ON a.PatNum  = p.PatNum AND p.office_id = a.office_id
            LEFT JOIN od_providers pr ON a.ProvNum = pr.ProvNum AND pr.office_id = a.office_id
            WHERE {$aSql}
              AND a.AdjDate BETWEEN ? AND ?
            GROUP BY p.PatNum, a.office_id, p.LName, p.FName,
                     pr.ProvNum, pr.Abbr, pr.LName, pr.PName,
                     a.AdjDate, a.AdjType

            UNION ALL

            SELECT
                p.PatNum                                          AS patient_id,
                cp.office_id                                      AS office_id,
                COALESCE({$patNameExpr}, 'Insurance Payment')     AS patient_name,
                COALESCE({$provIdExpr}, '')                       AS provider_ids,
                COALESCE({$provNameExpr}, '')                     AS providers,
                cp.ProcDate                                       AS dates,
                -SUM(cp.WriteOff)                                 AS amount,
                0                                                 AS adj_type_id,
                'writeoff'                                        AS source_type
            FROM od_claim_procs cp
            JOIN od_patients  p  ON cp.PatNum  = p.PatNum AND p.office_id = cp.office_id
            LEFT JOIN od_providers pr ON cp.ProvNum = pr.ProvNum AND pr.office_id = cp.office_id
            WHERE {$cpSql}
              AND cp.ProcDate BETWEEN ? AND ?
              AND cp.WriteOff <> 0
            GROUP BY p.PatNum, cp.office_id, p.LName, p.FName,
                     pr.ProvNum, pr.Abbr, pr.LName, pr.PName, cp.ProcDate

            ORDER BY dates, patient_name
        ", array_merge($aBindings, [$start, $end], $cpBindings, [$start, $end]));

        return array_map(fn ($r) => [
            'patient_id' => $r->patient_id,
            'office_id' => (int) $r->office_id,
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
    private function bkCollection(string $start, string $end, array $scopes): array
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $patNameExpr = $isSqlite ? "(p.LName || ', ' || p.FName)" : "CONCAT(p.LName, ', ', p.FName)";
        $provIdExpr = $isSqlite ? "(pr.ProvNum || ' - ' || pr.Abbr)" : "CONCAT(pr.ProvNum, ' - ', pr.Abbr)";
        $provNameExpr = $isSqlite
            ? "CASE WHEN pr.PName IS NOT NULL AND pr.PName != '' THEN (pr.LName || ', ' || pr.PName) ELSE pr.LName END"
            : "CASE WHEN pr.PName IS NOT NULL AND pr.PName != '' THEN CONCAT(pr.LName, ', ', pr.PName) ELSE pr.LName END";

        [$psSql, $psBindings] = $this->buildScopeSql($scopes, 'ps');
        [$cpPaySql, $cpPayBindings] = $this->buildScopeSql($scopes, 'cp_pay');

        $rows = DB::select("
            SELECT
                COALESCE(p.PatNum, ps.PatNum, 0)                  AS patient_id,
                ps.office_id                                      AS office_id,
                COALESCE({$patNameExpr}, 'Patient Payment')       AS patient_name,
                COALESCE({$provIdExpr}, '')                       AS provider_ids,
                COALESCE({$provNameExpr}, '')                     AS providers,
                ps.DatePay                                        AS dates,
                SUM(ps.SplitAmt)                                  AS amount
            FROM od_pay_splits ps
            LEFT JOIN od_patients  p  ON ps.PatNum  = p.PatNum AND p.office_id = ps.office_id
            LEFT JOIN od_providers pr ON ps.ProvNum = pr.ProvNum AND pr.office_id = ps.office_id
            WHERE {$psSql}
              AND ps.DatePay BETWEEN ? AND ?
            GROUP BY p.PatNum, ps.PatNum, ps.office_id, p.LName, p.FName,
                     pr.ProvNum, pr.Abbr, pr.LName, pr.PName,
                     ps.DatePay

            UNION ALL

            SELECT
                COALESCE(p.PatNum, cp.PatNum, 0)                 AS patient_id,
                cp_pay.office_id                                  AS office_id,
                COALESCE({$patNameExpr}, 'Insurance Payment')     AS patient_name,
                COALESCE({$provIdExpr}, '')                       AS provider_ids,
                COALESCE({$provNameExpr}, '')                     AS providers,
                cp_pay.CheckDate                                  AS dates,
                SUM(cp.InsPayAmt)                                 AS amount
            FROM od_claim_payments cp_pay
            JOIN od_claim_procs cp ON cp.ClaimPaymentNum = cp_pay.ClaimPaymentNum AND cp.office_id = cp_pay.office_id
            LEFT JOIN od_patients  p  ON cp.PatNum  = p.PatNum AND p.office_id = cp.office_id
            LEFT JOIN od_providers pr ON cp.ProvNum = pr.ProvNum AND pr.office_id = cp.office_id
            WHERE {$cpPaySql}
              AND cp_pay.CheckDate BETWEEN ? AND ?
              AND cp.InsPayAmt != 0
            GROUP BY p.PatNum, cp_pay.office_id, p.LName, p.FName,
                     cp.PatNum,
                     pr.ProvNum, pr.Abbr, pr.LName, pr.PName,
                     cp_pay.CheckDate

            ORDER BY dates, patient_name
        ", array_merge($psBindings, [$start, $end], $cpPayBindings, [$start, $end]));

        return array_map(fn ($r) => [
            'patient_id' => $r->patient_id,
            'office_id' => (int) $r->office_id,
            'patient_name' => $r->patient_name,
            'provider_ids' => $r->provider_ids,
            'providers' => $r->providers,
            'dates' => $r->dates,
            'amount' => round((float) $r->amount, 2),
        ], $rows);
    }

    // ── Patient Visits ────────────────────────────────────────────────────────
    private function bkPatientVisits(string $start, string $end, array $scopes): array
    {
        $all = [];
        foreach ($scopes as $officeId => $scopedClinics) {
            $rows = $this->patientVisits->patientVisitsBreakdown($start, $end, $scopedClinics, [], $officeId);
            $all = array_merge($all, $rows);
        }

        return $all;
    }

    // ── New Patient Visits ────────────────────────────────────────────────────
    private function bkNewPatientVisits(string $start, string $end, array $scopes): array
    {
        $all = [];
        foreach ($scopes as $officeId => $scopedClinics) {
            $rows = $this->patientVisits->newPatientVisits($start, $end, $scopedClinics, [], $officeId);
            $all = array_merge($all, $rows);
        }

        return $all;
    }

    // ── Patients Scheduled ────────────────────────────────────────────────────
    private function bkPatientsScheduled(string $start, string $end, array $scopes): array
    {
        $startDate = substr($start, 0, 10).' 00:00:00';
        $endDate = substr($end, 0, 10).' 23:59:59';
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $dateConcat = $isSqlite
            ? "GROUP_CONCAT(DISTINCT strftime('%Y-%m-%d', a.AptDateTime))"
            : "GROUP_CONCAT(DISTINCT DATE_FORMAT(a.AptDateTime, '%Y-%m-%d') ORDER BY a.AptDateTime SEPARATOR ', ')";
        $nameExpr = $isSqlite
            ? "COALESCE(p.LName || ', ' || p.FName, '')"
            : "COALESCE(CONCAT(p.LName, ', ', p.FName), '')";

        [$scopeSql, $scopeBindings] = $this->buildScopeSql($scopes, 'a');

        $rows = DB::select("
            SELECT
                a.PatNum                                                              AS patient_id,
                a.office_id                                                           AS office_id,
                {$nameExpr}                                                          AS patient_name,
                {$dateConcat}                                                         AS dates,
                COUNT(DISTINCT DATE(a.AptDateTime))                                   AS count
            FROM od_appointments a
            LEFT JOIN od_patients p ON a.PatNum = p.PatNum AND p.office_id = a.office_id
            WHERE {$scopeSql}
              AND a.AptDateTime BETWEEN ? AND ?
              AND a.AptStatus IN (1, 2)
            GROUP BY a.PatNum, a.office_id, p.LName, p.FName
            ORDER BY count DESC, p.LName
        ", array_merge($scopeBindings, [$startDate, $endDate]));

        return array_map(fn ($r) => [
            'patient_id' => $r->patient_id,
            'office_id' => (int) $r->office_id,
            'patient_name' => $r->patient_name,
            'dates' => $r->dates,
            'count' => (int) $r->count,
        ], $rows);
    }

    // ── New Patients Scheduled ────────────────────────────────────────────────
    private function bkNewPatientsScheduledScopes(string $start, string $end, array $scopes): array
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $nameExpr = $isSqlite
            ? "COALESCE(p.LName || ', ' || p.FName, '')"
            : "COALESCE(CONCAT(p.LName, ', ', p.FName), '')";
        $dateExpr = $isSqlite
            ? "strftime('%Y-%m-%d', MIN(a.AptDateTime))"
            : "DATE_FORMAT(MIN(a.AptDateTime), '%Y-%m-%d')";

        [$scopeSql, $scopeBindings] = $this->buildScopeSql($scopes, 'a');

        $rows = DB::select("
            SELECT
                a.PatNum                                             AS patient_id,
                a.office_id                                          AS office_id,
                {$nameExpr}                                          AS patient_name,
                {$dateExpr}                                          AS dates,
                1                                                   AS count
            FROM od_appointments a
            LEFT JOIN od_patients p ON a.PatNum = p.PatNum AND p.office_id = a.office_id
            WHERE {$scopeSql}
              AND a.AptDateTime BETWEEN ? AND ?
              AND a.AptStatus IN (1, 2)
              AND a.IsNewPatient IN (1, '1', true, 'true')
              AND a.PatNum NOT IN (21216, 21231, 21254)
              AND NOT EXISTS (
                  SELECT 1 FROM od_appointments a_old
                  WHERE a_old.office_id = a.office_id
                    AND a_old.PatNum = a.PatNum
                    AND a_old.AptStatus IN (1, 2)
                    AND a_old.IsNewPatient IN (1, '1', true, 'true')
                    AND a_old.AptDateTime < ?
              )
              AND NOT EXISTS (
                  SELECT 1 FROM od_procedure_logs pl_old
                  WHERE pl_old.office_id = a.office_id
                    AND pl_old.PatNum = a.PatNum
                    AND pl_old.ProcDate < ?
                    AND pl_old.ProcStatus IN ('C', '2', 'D')
              )
            GROUP BY a.PatNum, a.office_id, p.LName, p.FName
            ORDER BY p.LName
        ", array_merge($scopeBindings, [$start.' 00:00:00', $end.' 23:59:59', $start.' 00:00:00', $start]));

        return array_map(fn ($r) => [
            'patient_id' => $r->patient_id,
            'office_id' => (int) $r->office_id,
            'patient_name' => $r->patient_name,
            'dates' => $r->dates,
            'count' => (int) $r->count,
        ], $rows);
    }

    // ── Average Production Per Patient ────────────────────────────────────────
    private function bkAvgProductionPerPatient(string $start, string $end, array $scopes): array
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $nameExpr = $isSqlite
            ? "COALESCE(p.LName || ', ' || p.FName, '')"
            : "CONCAT(p.LName, ', ', p.FName)";

        [$scopeSql, $scopeBindings] = $this->buildScopeSql($scopes, 'pl');

        $rows = DB::select("
            SELECT
                p.PatNum                         AS patient_id,
                pl.office_id                     AS office_id,
                {$nameExpr}                      AS patient_name,
                COUNT(DISTINCT pl.ProcDate)       AS count,
                SUM(pl.ProcFee)                  AS amount
            FROM od_procedure_logs pl
            JOIN od_patients p ON pl.PatNum = p.PatNum AND p.office_id = pl.office_id
            WHERE {$scopeSql}
              AND pl.ProcStatus IN ({$this->completedIn})
              AND pl.ProcDate BETWEEN ? AND ?
            GROUP BY p.PatNum, pl.office_id, p.LName, p.FName
            ORDER BY p.LName
        ", array_merge($scopeBindings, [$start, $end]));

        return array_map(fn ($r) => [
            'patient_id' => $r->patient_id,
            'office_id' => (int) $r->office_id,
            'patient_name' => $r->patient_name,
            'count' => (int) $r->count,
            'amount' => round((float) $r->amount, 2),
        ], $rows);
    }

    // ── Broken & Cancelled Appointments ────────────────────────────────────────
    private function bkBrokenCancelled(string $start, string $end, array $scopes): array
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $nameExpr = $isSqlite
            ? "COALESCE(p.LName || ', ' || p.FName, '')"
            : "CONCAT(p.LName, ', ', p.FName)";

        [$scopeSql, $scopeBindings] = $this->buildScopeSql($scopes, 'pl');

        $rows = DB::select("
            SELECT
                p.PatNum                         AS patient_id,
                pl.office_id                     AS office_id,
                {$nameExpr}                      AS patient_name,
                pl.ProcDate                      AS dates,
                pc.ProcCode                      AS service_codes
            FROM od_procedure_logs pl
            JOIN od_patients   p  ON pl.PatNum  = p.PatNum AND p.office_id = pl.office_id
            JOIN od_procedures pc ON pl.CodeNum = pc.CodeNum AND pc.office_id = pl.office_id
            WHERE {$scopeSql}
              AND pl.ProcStatus IN ({$this->completedIn})
              AND pc.ProcCode IN ('D9986', 'D9987')
              AND pl.ProcDate BETWEEN ? AND ?
            ORDER BY dates, p.LName
        ", array_merge($scopeBindings, [$start, $end]));

        return array_map(fn ($r) => [
            'patient_id' => $r->patient_id,
            'office_id' => (int) $r->office_id,
            'patient_name' => $r->patient_name,
            'dates' => $r->dates,
            'type' => $r->service_codes === 'D9986' ? 'No-Show' : 'Cancelled',
        ], $rows);
    }
}
