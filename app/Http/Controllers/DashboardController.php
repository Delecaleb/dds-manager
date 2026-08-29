<?php

namespace App\Http\Controllers;

use App\Domain\Patient\PatientService;
use App\Domain\Patient\PatientVisitService;
use App\Domain\Production\ProductionService;
use App\Domain\Support\ClinicRegistry;
use App\Domain\Support\ProcStatus;
use App\Helpers\MetricDefinitions;
use App\Models\ClaimProcs;
use App\Models\OdAdjustment;
use App\Models\OdProcedureLog;
use App\Services\OpenDental\FinancialAnalyticsService;
use App\Services\OpenDental\PatientAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ProductionService $production,
        private readonly PatientService $patients,
        private readonly ClinicRegistry $clinics,
        private readonly PatientVisitService $patientVisits,
    ) {
        $this->clinicNames = $this->clinics->all();
    }

    /** ClinicNum => display name, sourced from the multi-office ClinicRegistry. */
    private array $clinicNames = [];

    private array $specialtyMap = [
        0 => 'General',
        1 => 'Endodontics',
        2 => 'Orthodontics',
        3 => 'Periodontics',
        4 => 'Prosthetics',
        5 => 'Surgery',
        6 => 'Pediatric',
        7 => 'Denturist',
        8 => 'Hygienist',
        268 => 'Invisalign',
    ];

    public function index()
    {
        return view('dashboard');
    }

    public function data(Request $request, FinancialAnalyticsService $financial, PatientAnalyticsService $patients)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());

        return response()->json(array_merge(
            $financial->filterAnalysis($start, $end),
            $patients->getPatientAnalytics($start, $end)
        ));
    }

    public function locationStats(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());

        $rows = DB::table('od_procedure_logs')
            ->selectRaw(
                'ClinicNum, '.
                MetricDefinitions::grossProduction('total_production').', '.
                MetricDefinitions::patientVisits('patient_count')
            )
            ->whereIn('ProcStatus', ProcStatus::completed())
            ->whereBetween('ProcDate', [$start, $end])
            ->groupBy('ClinicNum')
            ->orderByDesc('total_production')
            ->get();

        $adjustments = DB::table('od_adjustments')
            ->selectRaw('ClinicNum, '.MetricDefinitions::adjustments('val'))
            ->whereBetween('AdjDate', [$start, $end])
            ->groupBy('ClinicNum')
            ->pluck('val', 'ClinicNum');

        $writeoffs = DB::table('od_claim_procs')
            ->selectRaw('ClinicNum, '.MetricDefinitions::writeOffs('val'))
            ->whereBetween('ProcDate', [$start, $end])
            ->groupBy('ClinicNum')
            ->pluck('val', 'ClinicNum');

        return response()->json(
            $rows->map(function ($row, $i) use ($adjustments, $writeoffs) {
                $gross = (float) $row->total_production;
                $adj = (float) ($adjustments[$row->ClinicNum] ?? 0);
                $wo = (float) ($writeoffs[$row->ClinicNum] ?? 0);

                $net = $this->production->netFrom($gross, $adj, $wo);

                $avg = $row->patient_count > 0
                    ? round($net / $row->patient_count, 2)
                    : 0;

                return [
                    'rank' => $i + 1,
                    'clinic_num' => (int) $row->ClinicNum,
                    'location' => $this->clinicNames[(int) $row->ClinicNum] ?? 'Location '.$row->ClinicNum,
                    'total_production' => round($gross, 2),
                    'net_production' => $net,
                    'patient_count' => (int) $row->patient_count,
                    'avg_production' => $avg,
                ];
            })->values()
        );
    }

    public function providerDetails(Request $request, $id)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());

        $provider = DB::table('od_providers')->where('ProvNum', $id)->first();
        if (! $provider) {
            return response()->json(['error' => 'Provider not found'], 404);
        }

        $specialtyMap = $this->specialtyMap;

        /* ── Aggregate stats ─────────────────────────── */
        $gross = DB::table('od_procedure_logs')
            ->where('ProvNum', $id)->whereIn('ProcStatus', ProcStatus::completed())
            ->whereBetween('ProcDate', [$start, $end])->sum('ProcFee');

        $adjustments = DB::table('od_adjustments')
            ->where('ProvNum', $id)->whereBetween('AdjDate', [$start, $end])->sum('AdjAmt');

        $writeoffs = DB::table('od_claim_procs')
            ->where('ProvNum', $id)->whereBetween('ProcDate', [$start, $end])->sum('WriteOff');

        $net = $this->production->netFrom((float) $gross, (float) $adjustments, (float) $writeoffs);

        $patientVisits = $this->patientVisits->patientVisits($start, $end, [], [$id]);
        $newPatientVisits = $this->patientVisits->newPatientCount($start, $end, [], [$id]);

        // Avg per work-day (days this provider had completed procedures)
        $workDays = DB::table('od_procedure_logs')
            ->where('ProvNum', $id)->whereIn('ProcStatus', ProcStatus::completed())
            ->whereBetween('ProcDate', [$start, $end])
            ->distinct('ProcDate')->count('ProcDate');

        $avgPerDay = $workDays > 0 ? round($net / $workDays, 2) : 0;
        $perVisit = $patientVisits > 0 ? round($net / $patientVisits, 2) : 0;

        // TX accepted: completed / all (any status) procedures in range
        $txTotal = DB::table('od_procedure_logs')
            ->where('ProvNum', $id)->whereBetween('ProcDate', [$start, $end])->count();
        $txCompleted = DB::table('od_procedure_logs')
            ->where('ProvNum', $id)->whereIn('ProcStatus', ProcStatus::completed())
            ->whereBetween('ProcDate', [$start, $end])->count();
        $txRate = $txTotal > 0 ? round($txCompleted / $txTotal * 100, 2) : 0;

        /* ── Daily production ────────────────────────── */
        $dailyProduction = DB::table('od_procedure_logs')
            ->selectRaw(
                "DATE_FORMAT(ProcDate, '%Y-%m-%d') AS date, ".
                MetricDefinitions::grossProduction('production').', '.
                MetricDefinitions::patientVisits('patient_count')
            )
            ->where('ProvNum', $id)->whereIn('ProcStatus', ProcStatus::completed())
            ->whereBetween('ProcDate', [$start, $end])
            ->groupBy(DB::raw("DATE_FORMAT(ProcDate, '%Y-%m-%d')"))
            ->orderBy('date')->get()
            ->map(fn ($r) => [
                'date' => $r->date,
                'production' => round($r->production, 2),
                'per_visit' => $r->patient_count > 0 ? round($r->production / $r->patient_count, 2) : 0,
            ]);

        /* ── Daily visits (with new-patient detection) ── */
        $completed = ProcStatus::inList(ProcStatus::completed());
        $dailyVisitStats = $this->patientVisits->dailyStats($start, $end, [], [$id]);
        $dailyVisits = collect($dailyVisitStats['daily_visits'])->keys()
            ->merge(collect($dailyVisitStats['daily_new_visits'])->keys())
            ->unique()->sort()->values()->map(fn ($dStr) => (object) [
                'date' => $dStr,
                'patient_visits' => (int) ($dailyVisitStats['daily_visits'][$dStr] ?? 0),
                'new_patient_visits' => (int) ($dailyVisitStats['daily_new_visits'][$dStr] ?? 0),
            ])->all();

        /* ── Daily TX accepted rate ──────────────────── */
        $dailyTx = DB::table('od_procedure_logs')
            ->select(
                DB::raw("DATE_FORMAT(ProcDate, '%Y-%m-%d') AS date"),
                DB::raw("SUM(CASE WHEN ProcStatus IN ({$completed}) THEN 1 ELSE 0 END) AS completed"),
                DB::raw('COUNT(*) AS total')
            )
            ->where('ProvNum', $id)
            ->whereBetween('ProcDate', [$start, $end])
            ->groupBy(DB::raw("DATE_FORMAT(ProcDate, '%Y-%m-%d')"))
            ->orderBy('date')->get()
            ->map(fn ($r) => [
                'date' => $r->date,
                'rate' => $r->total > 0 ? round($r->completed / $r->total * 100, 2) : 0,
            ]);

        return response()->json([
            'provider' => [
                'ProvNum' => $provider->ProvNum,
                'LName' => $provider->LName,
                'PName' => $provider->PName,
                'Abbr' => $provider->Abbr,
                'Specialty' => $specialtyMap[(int) $provider->Specialty] ?? 'General Dentistry',
            ],
            'stats' => [
                'net_production' => round($net, 2),
                'avg_production_per_day' => $avgPerDay,
                'production_per_visit' => $perVisit,
                'patient_visits' => $patientVisits,
                'new_patient_visits' => $newPatientVisits,
                'tx_accepted_rate' => $txRate,
            ],
            'daily_production' => $dailyProduction,
            'daily_visits' => $dailyVisits,
            'daily_tx' => $dailyTx,
        ]);
    }

    public function providerPerformance(Request $request): JsonResponse
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
        $search = trim($request->input('search', ''));

        $grossSub = OdProcedureLog::select('ProvNum', DB::raw('SUM(ProcFee) AS gross'))
            ->whereIn('ProcStatus', ProcStatus::completed())
            ->whereBetween('ProcDate', [$start, $end])
            ->groupBy('ProvNum');

        $adjSub = OdAdjustment::select('ProvNum', DB::raw('SUM(AdjAmt) AS adjustments'))
            ->whereBetween('AdjDate', [$start, $end])
            ->groupBy('ProvNum');

        $writeoffSub = ClaimProcs::select('ProvNum', DB::raw('SUM(WriteOff) AS writeoffs'))
            ->whereBetween('ProcDate', [$start, $end])
            ->groupBy('ProvNum');

        $patCollSub = DB::table('od_pay_splits')
            ->select('ProvNum', DB::raw('SUM(SplitAmt) AS amt'))
            ->whereBetween('DatePay', [$start, $end])
            ->groupBy('ProvNum');

        $insCollSub = DB::table('od_claim_procs')
            ->select('ProvNum', DB::raw('SUM(InsPayAmt) AS amt'))
            ->whereBetween('DateCP', [$start, $end])
            ->where('Status', '!=', 0)
            ->groupBy('ProvNum');

        $collSub = DB::query()->fromSub(
            $patCollSub->unionAll($insCollSub),
            'c_unioned'
        )->select('ProvNum', DB::raw('SUM(amt) AS collections'))->groupBy('ProvNum');

        $aptsSub = DB::table('od_appointments')
            ->select('ProvNum', DB::raw('COUNT(*) AS appointment_count'))
            ->whereIn('AptStatus', [1, 2])
            ->whereBetween('AptDateTime', [$start, $end])
            ->groupBy('ProvNum');

        $providers = DB::table('od_providers as p')
            ->select(
                'p.ProvNum',
                'p.LName',
                'p.PName',
                'p.Abbr',
                'p.Specialty',
                DB::raw('COALESCE(g.gross, 0) AS gross_production'),
                DB::raw('(COALESCE(a.adjustments, 0) - COALESCE(w.writeoffs, 0)) AS adjustments'),
                DB::raw('COALESCE(w.writeoffs, 0) AS writeoffs'),
                DB::raw('COALESCE(c.collections, 0) AS collections'),
                DB::raw('COALESCE(apt.appointment_count, 0) AS appointment_count')
            )
            ->leftJoinSub($grossSub, 'g', 'p.ProvNum', '=', 'g.ProvNum')
            ->leftJoinSub($adjSub, 'a', 'p.ProvNum', '=', 'a.ProvNum')
            ->leftJoinSub($writeoffSub, 'w', 'p.ProvNum', '=', 'w.ProvNum')
            ->leftJoinSub($collSub, 'c', 'p.ProvNum', '=', 'c.ProvNum')
            ->leftJoinSub($aptsSub, 'apt', 'p.ProvNum', '=', 'apt.ProvNum')
            ->where(function ($q) {
                $q->whereRaw('COALESCE(g.gross, 0) != 0')
                    ->orWhereRaw('COALESCE(a.adjustments, 0) != 0')
                    ->orWhereRaw('COALESCE(w.writeoffs, 0) != 0')
                    ->orWhereRaw('COALESCE(c.collections, 0) != 0')
                    ->orWhereRaw('COALESCE(apt.appointment_count, 0) != 0');
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('p.LName', 'like', "%{$search}%")
                        ->orWhere('p.PName', 'like', "%{$search}%")
                        ->orWhere('p.Abbr', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('gross_production')
            ->get();

        // Check if there are unassigned transactions (ProvNum = 0 or null) not associated with a named provider
        $knownProvNums = $providers->pluck('ProvNum')->map(fn ($id) => (int) $id)->toArray();
        $unassignedGross = (float) OdProcedureLog::whereIn('ProcStatus', ProcStatus::completed())->whereBetween('ProcDate', [$start, $end])->where(fn ($q) => $q->whereNull('ProvNum')->orWhere('ProvNum', 0))->sum('ProcFee');
        $unassignedAdj = (float) OdAdjustment::whereBetween('AdjDate', [$start, $end])->where(fn ($q) => $q->whereNull('ProvNum')->orWhere('ProvNum', 0))->sum('AdjAmt');
        $unassignedWo = (float) ClaimProcs::whereBetween('ProcDate', [$start, $end])->where(fn ($q) => $q->whereNull('ProvNum')->orWhere('ProvNum', 0))->sum('WriteOff');
        $unassignedPatColl = (float) DB::table('od_pay_splits')->whereBetween('DatePay', [$start, $end])->where(fn ($q) => $q->whereNull('ProvNum')->orWhere('ProvNum', 0))->sum('SplitAmt');
        $unassignedInsColl = (float) DB::table('od_claim_procs')->whereBetween('DateCP', [$start, $end])->where('Status', '!=', 0)->where(fn ($q) => $q->whereNull('ProvNum')->orWhere('ProvNum', 0))->sum('InsPayAmt');
        $unassignedColl = $unassignedPatColl + $unassignedInsColl;
        $unassignedApts = (int) DB::table('od_appointments')->whereIn('AptStatus', [1, 2])->whereBetween('AptDateTime', [$start, $end])->where(fn ($q) => $q->whereNull('ProvNum')->orWhere('ProvNum', 0))->count();

        if (! in_array(0, $knownProvNums) && ($unassignedGross != 0 || $unassignedAdj != 0 || $unassignedWo != 0 || $unassignedColl != 0 || $unassignedApts != 0)) {
            $providers->push((object) [
                'ProvNum' => 0,
                'LName' => 'Unassigned',
                'PName' => '',
                'Abbr' => 'UNASSIGNED',
                'Specialty' => 0,
                'gross_production' => $unassignedGross,
                'adjustments' => $unassignedAdj - $unassignedWo,
                'writeoffs' => $unassignedWo,
                'collections' => $unassignedColl,
                'appointment_count' => $unassignedApts,
            ]);
        }

        $mappedProviders = $providers->map(function ($p) {
            $p->specialty = $this->specialtyMap[(int) $p->Specialty] ?? 'General Dentistry';
            $p->location = $this->clinics->name((int) ($p->ClinicNum ?? 0));
            // Net via the single source of truth (blueprint D3, signed adjustments).
            $p->net_production = $this->production->netFrom(
                (float) $p->gross_production,
                (float) $p->adjustments,
                0.0
            );

            return $p;
        });

        return response()->json($mappedProviders->values());
    }

    public function financialsPerLocationData(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());

        $startLastYear = Carbon::parse($start)->subYear()->toDateString();
        $endLastYear = Carbon::parse($end)->subYear()->toDateString();

        $buildLocationStats = function ($s, $e) {
            $gross = DB::table('od_procedure_logs')
                ->selectRaw('ClinicNum, '.MetricDefinitions::grossProduction('val'))
                ->whereIn('ProcStatus', ProcStatus::completed())->whereBetween('ProcDate', [$s, $e])
                ->groupBy('ClinicNum')->pluck('val', 'ClinicNum');

            $adj = DB::table('od_adjustments')
                ->selectRaw('ClinicNum, '.MetricDefinitions::adjustments('val'))
                ->whereBetween('AdjDate', [$s, $e])
                ->groupBy('ClinicNum')->pluck('val', 'ClinicNum');

            $writeoffs = DB::table('od_claim_procs')
                ->selectRaw('ClinicNum, '.MetricDefinitions::writeOffs('val'))
                ->whereBetween('ProcDate', [$s, $e])
                ->groupBy('ClinicNum')->pluck('val', 'ClinicNum');

            $patColl = DB::table('od_pay_splits')
                ->selectRaw('ClinicNum, SUM(SplitAmt) as val')
                ->whereBetween('DatePay', [$s, $e])
                ->groupBy('ClinicNum')->pluck('val', 'ClinicNum');

            $insColl = DB::table('od_claim_procs')
                ->selectRaw('ClinicNum, SUM(InsPayAmt) as val')
                ->whereBetween('DateCP', [$s, $e])
                ->where('Status', '!=', 0)
                ->groupBy('ClinicNum')->pluck('val', 'ClinicNum');

            $coll = collect();
            foreach ($patColl->keys()->merge($insColl->keys())->unique() as $cNum) {
                $coll->put($cNum, (float) ($patColl->get($cNum, 0) + $insColl->get($cNum, 0)));
            }

            return compact('gross', 'adj', 'writeoffs', 'coll');
        };

        $currentStats = $buildLocationStats($start, $end);
        $lastYearStats = $buildLocationStats($startLastYear, $endLastYear);

        $allClinicNums = collect()
            ->merge($currentStats['gross']->keys())
            ->merge($currentStats['adj']->keys())
            ->merge($currentStats['writeoffs']->keys())
            ->merge($currentStats['coll']->keys())
            ->merge($lastYearStats['gross']->keys())
            ->merge($lastYearStats['adj']->keys())
            ->merge($lastYearStats['writeoffs']->keys())
            ->merge($lastYearStats['coll']->keys())
            ->unique()
            ->sort();

        $result = [];
        foreach ($allClinicNums as $cNum) {
            $cg = (float) $currentStats['gross']->get($cNum, 0);
            $ca = (float) $currentStats['adj']->get($cNum, 0) - (float) $currentStats['writeoffs']->get($cNum, 0);
            $cc = (float) $currentStats['coll']->get($cNum, 0);
            $cn = $cg + $ca;

            $lg = (float) $lastYearStats['gross']->get($cNum, 0);
            $la = (float) $lastYearStats['adj']->get($cNum, 0) - (float) $lastYearStats['writeoffs']->get($cNum, 0);
            $lc = (float) $lastYearStats['coll']->get($cNum, 0);
            $ln = $lg + $la;

            $result[] = [
                'clinic_num' => $cNum,
                'location' => $this->clinicNames[(int) $cNum] ?? 'Location '.$cNum,
                'gross_production' => round($cg, 2),
                'gross_production_last' => round($lg, 2),
                'adjustments' => round($ca, 2),
                'adjustments_last' => round($la, 2),
                'collections' => round($cc, 2),
                'collections_last' => round($lc, 2),
                'net_production' => round($cn, 2),
                'net_production_last' => round($ln, 2),
            ];
        }

        return response()->json($result);
    }

    public function patientVisitsPerLocationData(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());

        return response()->json(
            $this->patientVisits->visitsPerLocation($start, $end, $this->clinicNames)
        );
    }
}
