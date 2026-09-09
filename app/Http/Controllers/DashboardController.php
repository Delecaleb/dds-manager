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
use App\Models\Office;
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
    ) {}

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
        $officeId = Office::getActiveOfficeId();

        return response()->json(array_merge(
            $financial->filterAnalysis($start, $end, $officeId),
            $patients->getPatientAnalytics($start, $end, $officeId)
        ));
    }

    public function locationStats(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
        $officeId = Office::getActiveOfficeId();

        $rows = DB::table('od_procedure_logs')
            ->where('office_id', $officeId)
            ->selectRaw(
                'COALESCE(ClinicNum + 0, 0) as ClinicNum, '.
                MetricDefinitions::grossProduction('total_production').', '.
                MetricDefinitions::patientVisits('patient_count')
            )
            ->whereIn('ProcStatus', ProcStatus::completed())
            ->whereBetween('ProcDate', [$start, $end])
            ->groupBy(DB::raw('COALESCE(ClinicNum + 0, 0)'))
            ->orderByDesc('total_production')
            ->get();

        $adjustments = DB::table('od_adjustments')
            ->where('office_id', $officeId)
            ->selectRaw('COALESCE(ClinicNum + 0, 0) as ClinicNum, '.MetricDefinitions::adjustments('val'))
            ->whereBetween('AdjDate', [$start, $end])
            ->groupBy(DB::raw('COALESCE(ClinicNum + 0, 0)'))
            ->pluck('val', 'ClinicNum')
            ->mapWithKeys(fn ($val, $k) => [(int) $k => (float) $val]);

        $writeoffs = DB::table('od_claim_procs')
            ->where('office_id', $officeId)
            ->selectRaw('COALESCE(ClinicNum + 0, 0) as ClinicNum, '.MetricDefinitions::writeOffs('val'))
            ->whereBetween('ProcDate', [$start, $end])
            ->groupBy(DB::raw('COALESCE(ClinicNum + 0, 0)'))
            ->pluck('val', 'ClinicNum')
            ->mapWithKeys(fn ($val, $k) => [(int) $k => (float) $val]);

        $clinicNames = $this->clinics->all($officeId);

        $allClinicNums = collect(array_keys($clinicNames))
            ->merge($rows->pluck('ClinicNum'))
            ->map(fn ($k) => (int) $k)
            ->unique()
            ->values();

        $rowsMap = $rows->keyBy(fn ($r) => (int) $r->ClinicNum);

        $result = $allClinicNums->map(function ($cNum) use ($rowsMap, $adjustments, $writeoffs, $clinicNames) {
            $row = $rowsMap->get((int) $cNum);
            $gross = $row ? (float) $row->total_production : 0.0;
            $adj = (float) ($adjustments->get((int) $cNum, 0));
            $wo = (float) ($writeoffs->get((int) $cNum, 0));
            $patientCount = $row ? (int) $row->patient_count : 0;

            $net = $this->production->netFrom($gross, $adj, $wo);

            $avg = $patientCount > 0
                ? round($net / $patientCount, 2)
                : 0;

            return [
                'clinic_num' => (int) $cNum,
                'location' => $clinicNames[(int) $cNum] ?? 'Location '.$cNum,
                'total_production' => round($gross, 2),
                'net_production' => $net,
                'patient_count' => $patientCount,
                'avg_production' => $avg,
            ];
        })->sortByDesc('total_production')->values()->map(function ($item, $i) {
            $item['rank'] = $i + 1;

            return $item;
        });

        return response()->json($result);
    }

    public function providerDetails(Request $request, $id)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
        $officeId = Office::getActiveOfficeId();

        $provider = DB::table('od_providers')
            ->where('office_id', $officeId)
            ->where('ProvNum', $id)
            ->first();

        if (! $provider) {
            return response()->json(['error' => 'Provider not found'], 404);
        }

        $specialtyMap = $this->specialtyMap;

        /* ── Aggregate stats ─────────────────────────── */
        $gross = DB::table('od_procedure_logs')
            ->where('office_id', $officeId)
            ->where('ProvNum', $id)->whereIn('ProcStatus', ProcStatus::completed())
            ->whereBetween('ProcDate', [$start, $end])->sum('ProcFee');

        $adjustments = DB::table('od_adjustments')
            ->where('office_id', $officeId)
            ->where('ProvNum', $id)->whereBetween('AdjDate', [$start, $end])->sum('AdjAmt');

        $writeoffs = DB::table('od_claim_procs')
            ->where('office_id', $officeId)
            ->where('ProvNum', $id)->whereBetween('ProcDate', [$start, $end])->sum('WriteOff');

        $net = $this->production->netFrom((float) $gross, (float) $adjustments, (float) $writeoffs);

        $patientVisits = $this->patientVisits->patientVisits($start, $end, [], [$id], $officeId);
        $newPatientVisits = $this->patientVisits->newPatientCount($start, $end, [], [$id], $officeId);

        // Avg per work-day (days this provider had completed procedures)
        $workDays = DB::table('od_procedure_logs')
            ->where('office_id', $officeId)
            ->where('ProvNum', $id)->whereIn('ProcStatus', ProcStatus::completed())
            ->whereBetween('ProcDate', [$start, $end])
            ->distinct('ProcDate')->count('ProcDate');

        $avgPerDay = $workDays > 0 ? round($net / $workDays, 2) : 0;
        $perVisit = $patientVisits > 0 ? round($net / $patientVisits, 2) : 0;

        // TX accepted: completed / all (any status) procedures in range
        $txTotal = DB::table('od_procedure_logs')
            ->where('office_id', $officeId)
            ->where('ProvNum', $id)->whereBetween('ProcDate', [$start, $end])->count();
        $txCompleted = DB::table('od_procedure_logs')
            ->where('office_id', $officeId)
            ->where('ProvNum', $id)->whereIn('ProcStatus', ProcStatus::completed())
            ->whereBetween('ProcDate', [$start, $end])->count();
        $txRate = $txTotal > 0 ? round($txCompleted / $txTotal * 100, 2) : 0;

        /* ── Daily production ────────────────────────── */
        $dailyProduction = DB::table('od_procedure_logs')
            ->where('office_id', $officeId)
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
        $dailyVisitStats = $this->patientVisits->dailyStats($start, $end, [], [$id], $officeId);
        $dailyVisits = collect($dailyVisitStats['daily_visits'])->keys()
            ->merge(collect($dailyVisitStats['daily_new_visits'])->keys())
            ->unique()->sort()->values()->map(fn ($dStr) => (object) [
                'date' => $dStr,
                'patient_visits' => (int) ($dailyVisitStats['daily_visits'][$dStr] ?? 0),
                'new_patient_visits' => (int) ($dailyVisitStats['daily_new_visits'][$dStr] ?? 0),
            ])->all();

        /* ── Daily TX accepted rate ──────────────────── */
        $dailyTx = DB::table('od_procedure_logs')
            ->where('office_id', $officeId)
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
        $officeId = Office::getActiveOfficeId();

        $grossSub = OdProcedureLog::select('ProvNum', DB::raw('SUM(ProcFee) AS gross'))
            ->where('office_id', $officeId)
            ->whereIn('ProcStatus', ProcStatus::completed())
            ->whereBetween('ProcDate', [$start, $end])
            ->groupBy('ProvNum');

        $adjSub = OdAdjustment::select('ProvNum', DB::raw('SUM(AdjAmt) AS adjustments'))
            ->where('office_id', $officeId)
            ->whereBetween('AdjDate', [$start, $end])
            ->groupBy('ProvNum');

        $writeoffSub = ClaimProcs::select('ProvNum', DB::raw('SUM(WriteOff) AS writeoffs'))
            ->where('office_id', $officeId)
            ->whereBetween('ProcDate', [$start, $end])
            ->groupBy('ProvNum');

        $patCollSub = DB::table('od_pay_splits')
            ->select('ProvNum', DB::raw('SUM(SplitAmt) AS amt'))
            ->where('office_id', $officeId)
            ->whereBetween('DatePay', [$start, $end])
            ->groupBy('ProvNum');

        $insCollSub = DB::table('od_claim_procs')
            ->select('ProvNum', DB::raw('SUM(InsPayAmt) AS amt'))
            ->where('office_id', $officeId)
            ->whereBetween('DateCP', [$start, $end])
            ->where('Status', '!=', 0)
            ->groupBy('ProvNum');

        $collSub = DB::query()->fromSub(
            $patCollSub->unionAll($insCollSub),
            'c_unioned'
        )->select('ProvNum', DB::raw('SUM(amt) AS collections'))->groupBy('ProvNum');

        $aptsSub = DB::table('od_appointments')
            ->select('ProvNum', DB::raw('COUNT(*) AS appointment_count'))
            ->where('office_id', $officeId)
            ->whereIn('AptStatus', [1, 2])
            ->whereBetween('AptDateTime', [$start.' 00:00:00', $end.' 23:59:59'])
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
            ->where('p.office_id', $officeId)
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
        $unassignedGross = (float) OdProcedureLog::where('office_id', $officeId)->whereIn('ProcStatus', ProcStatus::completed())->whereBetween('ProcDate', [$start, $end])->where(fn ($q) => $q->whereNull('ProvNum')->orWhere('ProvNum', 0))->sum('ProcFee');
        $unassignedAdj = (float) OdAdjustment::where('office_id', $officeId)->whereBetween('AdjDate', [$start, $end])->where(fn ($q) => $q->whereNull('ProvNum')->orWhere('ProvNum', 0))->sum('AdjAmt');
        $unassignedWo = (float) ClaimProcs::where('office_id', $officeId)->whereBetween('ProcDate', [$start, $end])->where(fn ($q) => $q->whereNull('ProvNum')->orWhere('ProvNum', 0))->sum('WriteOff');
        $unassignedPatColl = (float) DB::table('od_pay_splits')->where('office_id', $officeId)->whereBetween('DatePay', [$start, $end])->where(fn ($q) => $q->whereNull('ProvNum')->orWhere('ProvNum', 0))->sum('SplitAmt');
        $unassignedInsColl = (float) DB::table('od_claim_procs')->where('office_id', $officeId)->whereBetween('DateCP', [$start, $end])->where('Status', '!=', 0)->where(fn ($q) => $q->whereNull('ProvNum')->orWhere('ProvNum', 0))->sum('InsPayAmt');
        $unassignedColl = $unassignedPatColl + $unassignedInsColl;
        $unassignedApts = (int) DB::table('od_appointments')->where('office_id', $officeId)->whereIn('AptStatus', [1, 2])->whereBetween('AptDateTime', [$start.' 00:00:00', $end.' 23:59:59'])->where(fn ($q) => $q->whereNull('ProvNum')->orWhere('ProvNum', 0))->count();

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

        $mappedProviders = $providers->map(function ($p) use ($officeId) {
            $p->specialty = $this->specialtyMap[(int) $p->Specialty] ?? 'General Dentistry';
            $p->location = $this->clinics->name((int) ($p->ClinicNum ?? 0), $officeId);
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
        $officeId = Office::getActiveOfficeId();

        $startLastYear = Carbon::parse($start)->subYear()->toDateString();
        $endLastYear = Carbon::parse($end)->subYear()->toDateString();

        $buildLocationStats = function ($s, $e) use ($officeId) {
            $gross = DB::table('od_procedure_logs')
                ->where('office_id', $officeId)
                ->selectRaw('COALESCE(ClinicNum + 0, 0) as ClinicNum, '.MetricDefinitions::grossProduction('val'))
                ->whereIn('ProcStatus', ProcStatus::completed())->whereBetween('ProcDate', [$s, $e])
                ->groupBy(DB::raw('COALESCE(ClinicNum + 0, 0)'))
                ->pluck('val', 'ClinicNum')
                ->mapWithKeys(fn ($val, $k) => [(int) $k => (float) $val]);

            $adj = DB::table('od_adjustments')
                ->where('office_id', $officeId)
                ->selectRaw('COALESCE(ClinicNum + 0, 0) as ClinicNum, '.MetricDefinitions::adjustments('val'))
                ->whereBetween('AdjDate', [$s, $e])
                ->groupBy(DB::raw('COALESCE(ClinicNum + 0, 0)'))
                ->pluck('val', 'ClinicNum')
                ->mapWithKeys(fn ($val, $k) => [(int) $k => (float) $val]);

            $writeoffs = DB::table('od_claim_procs')
                ->where('office_id', $officeId)
                ->selectRaw('COALESCE(ClinicNum + 0, 0) as ClinicNum, '.MetricDefinitions::writeOffs('val'))
                ->whereBetween('ProcDate', [$s, $e])
                ->groupBy(DB::raw('COALESCE(ClinicNum + 0, 0)'))
                ->pluck('val', 'ClinicNum')
                ->mapWithKeys(fn ($val, $k) => [(int) $k => (float) $val]);

            $patColl = DB::table('od_pay_splits')
                ->where('office_id', $officeId)
                ->selectRaw('COALESCE(ClinicNum + 0, 0) as ClinicNum, SUM(SplitAmt) as val')
                ->whereBetween('DatePay', [$s, $e])
                ->groupBy(DB::raw('COALESCE(ClinicNum + 0, 0)'))
                ->pluck('val', 'ClinicNum')
                ->mapWithKeys(fn ($val, $k) => [(int) $k => (float) $val]);

            $insColl = DB::table('od_claim_procs')
                ->where('office_id', $officeId)
                ->selectRaw('COALESCE(ClinicNum + 0, 0) as ClinicNum, SUM(InsPayAmt) as val')
                ->whereBetween('DateCP', [$s, $e])
                ->where('Status', '!=', 0)
                ->groupBy(DB::raw('COALESCE(ClinicNum + 0, 0)'))
                ->pluck('val', 'ClinicNum')
                ->mapWithKeys(fn ($val, $k) => [(int) $k => (float) $val]);

            $coll = collect();
            foreach ($patColl->keys()->merge($insColl->keys())->map(fn ($k) => (int) $k)->unique() as $cNum) {
                $coll->put((int) $cNum, (float) ($patColl->get((int) $cNum, 0) + $insColl->get((int) $cNum, 0)));
            }

            return compact('gross', 'adj', 'writeoffs', 'coll');
        };

        $currentStats = $buildLocationStats($start, $end);
        $lastYearStats = $buildLocationStats($startLastYear, $endLastYear);

        $clinicNames = $this->clinics->all($officeId);

        $allClinicNums = collect(array_keys($clinicNames))
            ->merge($currentStats['gross']->keys())
            ->merge($currentStats['adj']->keys())
            ->merge($currentStats['writeoffs']->keys())
            ->merge($currentStats['coll']->keys())
            ->merge($lastYearStats['gross']->keys())
            ->merge($lastYearStats['adj']->keys())
            ->merge($lastYearStats['writeoffs']->keys())
            ->merge($lastYearStats['coll']->keys())
            ->map(fn ($k) => (int) $k)
            ->unique()
            ->sort()
            ->values();

        $result = [];
        foreach ($allClinicNums as $cNum) {
            $cg = (float) $currentStats['gross']->get((int) $cNum, 0);
            $ca = (float) $currentStats['adj']->get((int) $cNum, 0) - (float) $currentStats['writeoffs']->get((int) $cNum, 0);
            $cc = (float) $currentStats['coll']->get((int) $cNum, 0);
            $cn = $cg + $ca;

            $lg = (float) $lastYearStats['gross']->get((int) $cNum, 0);
            $la = (float) $lastYearStats['adj']->get((int) $cNum, 0) - (float) $lastYearStats['writeoffs']->get((int) $cNum, 0);
            $lc = (float) $lastYearStats['coll']->get((int) $cNum, 0);
            $ln = $lg + $la;

            $result[] = [
                'clinic_num' => (int) $cNum,
                'location' => $clinicNames[(int) $cNum] ?? 'Location '.$cNum,
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
        $officeId = Office::getActiveOfficeId();

        return response()->json(
            $this->patientVisits->visitsPerLocation($start, $end, $this->clinics->all($officeId), $officeId)
        );
    }
}
