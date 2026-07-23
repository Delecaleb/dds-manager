<?php

namespace App\Http\Controllers;

use App\Domain\Production\ProductionService;
use App\Helpers\MetricDefinitions;
use App\Models\ClaimProcs;
use App\Models\OdAdjustment;
use App\Models\OdProcedureLog;
use App\Models\PaySplit;
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
    ) {}

    private array $clinicNames = [
        0 => '8 Mile',
    ];

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
            ->where('ProcStatus', 'C')
            ->whereBetween('ProcDate', [$start, $end])
            ->groupBy('ClinicNum')
            ->orderByDesc('total_production')
            ->get();

        return response()->json(
            $rows->map(function ($row, $i) {
                $avg = $row->patient_count > 0
                    ? round($row->total_production / $row->patient_count, 2)
                    : 0;

                return [
                    'rank' => $i + 1,
                    'clinic_num' => $row->ClinicNum,
                    'location' => $this->clinicNames[(int) $row->ClinicNum] ?? 'Location '.$row->ClinicNum,
                    'total_production' => round($row->total_production, 2),
                    'patient_count' => $row->patient_count,
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
            ->where('ProvNum', $id)->where('ProcStatus', 'C')
            ->whereBetween('ProcDate', [$start, $end])->sum('ProcFee');

        $adjustments = DB::table('od_adjustments')
            ->where('ProvNum', $id)->whereBetween('AdjDate', [$start, $end])->sum('AdjAmt');

        $writeoffs = DB::table('od_claim_procs')
            ->where('ProvNum', $id)->whereBetween('ProcDate', [$start, $end])->sum('WriteOff');

        $net = $this->production->netFrom((float) $gross, (float) $adjustments, (float) $writeoffs);

        $patientVisits = DB::table('od_procedure_logs')
            ->where('ProvNum', $id)->where('ProcStatus', 'C')
            ->whereBetween('ProcDate', [$start, $end])
            ->selectRaw('PatNum, DATE(ProcDate)')
            ->distinct()
            ->get()
            ->count();

        $newPatientVisits = DB::table('od_procedure_logs')
            ->select('PatNum', DB::raw('MIN(ProcDate) as first_visit'))
            ->where('ProvNum', $id)->where('ProcStatus', 'C')
            ->groupBy('PatNum')
            ->havingBetween('first_visit', [$start, $end])
            ->count();

        // Avg per work-day (days this provider had completed procedures)
        $workDays = DB::table('od_procedure_logs')
            ->where('ProvNum', $id)->where('ProcStatus', 'C')
            ->whereBetween('ProcDate', [$start, $end])
            ->distinct('ProcDate')->count('ProcDate');

        $avgPerDay = $workDays > 0 ? round($net / $workDays, 2) : 0;
        $perVisit = $patientVisits > 0 ? round($net / $patientVisits, 2) : 0;

        // TX accepted: completed / all (any status) procedures in range
        $txTotal = DB::table('od_procedure_logs')
            ->where('ProvNum', $id)->whereBetween('ProcDate', [$start, $end])->count();
        $txCompleted = DB::table('od_procedure_logs')
            ->where('ProvNum', $id)->where('ProcStatus', 'C')
            ->whereBetween('ProcDate', [$start, $end])->count();
        $txRate = $txTotal > 0 ? round($txCompleted / $txTotal * 100, 2) : 0;

        /* ── Daily production ────────────────────────── */
        $dailyProduction = DB::table('od_procedure_logs')
            ->selectRaw(
                "DATE_FORMAT(ProcDate, '%Y-%m-%d') AS date, ".
                MetricDefinitions::grossProduction('production').', '.
                MetricDefinitions::patientVisits('patient_count')
            )
            ->where('ProvNum', $id)->where('ProcStatus', 'C')
            ->whereBetween('ProcDate', [$start, $end])
            ->groupBy(DB::raw("DATE_FORMAT(ProcDate, '%Y-%m-%d')"))
            ->orderBy('date')->get()
            ->map(fn ($r) => [
                'date' => $r->date,
                'production' => round($r->production, 2),
                'per_visit' => $r->patient_count > 0 ? round($r->production / $r->patient_count, 2) : 0,
            ]);

        /* ── Daily visits (with new-patient detection) ── */
        $dailyVisits = DB::select("
            SELECT
                DATE_FORMAT(pl.ProcDate, '%Y-%m-%d') AS date,
                COUNT(DISTINCT pl.PatNum) AS patient_visits,
                COUNT(DISTINCT CASE WHEN pl.ProcDate = fv.first_date THEN pl.PatNum END) AS new_patient_visits
            FROM od_procedure_logs pl
            LEFT JOIN (
                SELECT PatNum, MIN(ProcDate) AS first_date
                FROM od_procedure_logs
                WHERE ProcStatus = 'C'
                GROUP BY PatNum
            ) fv ON pl.PatNum = fv.PatNum
            WHERE pl.ProvNum = ? AND pl.ProcStatus = 'C' AND pl.ProcDate BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(pl.ProcDate, '%Y-%m-%d')
            ORDER BY date
        ", [$id, $start, $end]);

        /* ── Daily TX accepted rate ──────────────────── */
        $dailyTx = DB::table('od_procedure_logs')
            ->select(
                DB::raw("DATE_FORMAT(ProcDate, '%Y-%m-%d') AS date"),
                DB::raw("SUM(CASE WHEN ProcStatus = 'C' THEN 1 ELSE 0 END) AS completed"),
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
            ->where('ProcStatus', 'C')
            ->whereBetween('ProcDate', [$start, $end])
            ->groupBy('ProvNum');

        $adjSub = OdAdjustment::select('ProvNum', DB::raw('SUM(AdjAmt) AS adjustments'))
            ->whereBetween('AdjDate', [$start, $end])
            ->groupBy('ProvNum');

        $writeoffSub = ClaimProcs::select('ProvNum', DB::raw('SUM(WriteOff) AS writeoffs'))
            ->whereBetween('ProcDate', [$start, $end])
            ->groupBy('ProvNum');

        $collSub = PaySplit::select('ProvNum', DB::raw('SUM(SplitAmt) AS collections'))
            ->whereBetween('DatePay', [$start, $end])
            ->groupBy('ProvNum');

        $aptsSub = DB::table('od_appointments')
            ->select('ProvNum', DB::raw('COUNT(*) AS appointment_count'))
            ->where('AptStatus', 1)
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
                DB::raw('COALESCE(a.adjustments, 0) AS adjustments'),
                DB::raw('COALESCE(w.writeoffs, 0) AS writeoffs'),
                DB::raw('COALESCE(c.collections, 0) AS collections'),
                DB::raw('COALESCE(apt.appointment_count, 0) AS appointment_count')
            )
            ->leftJoinSub($grossSub, 'g', 'p.ProvNum', '=', 'g.ProvNum')
            ->leftJoinSub($adjSub, 'a', 'p.ProvNum', '=', 'a.ProvNum')
            ->leftJoinSub($writeoffSub, 'w', 'p.ProvNum', '=', 'w.ProvNum')
            ->leftJoinSub($collSub, 'c', 'p.ProvNum', '=', 'c.ProvNum')
            ->leftJoinSub($aptsSub, 'apt', 'p.ProvNum', '=', 'apt.ProvNum')
            ->whereIn('p.IsHidden', ['false', '0', 0, false])
            ->where(function ($q) {
                $q->whereRaw('COALESCE(g.gross, 0) != 0')
                    ->orWhereRaw('COALESCE(a.adjustments, 0) != 0')
                    ->orWhereRaw('COALESCE(w.writeoffs, 0) != 0')
                    ->orWhereRaw('COALESCE(c.collections, 0) != 0');
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

        $mappedProviders = $providers->map(function ($p) {
            $p->specialty = $this->specialtyMap[(int) $p->Specialty] ?? 'General Dentistry';
            $p->location = '8 Mile';
            // Net via the single source of truth (blueprint D3, signed adjustments).
            $p->net_production = $this->production->netFrom(
                (float) $p->gross_production,
                (float) $p->adjustments,
                (float) $p->writeoffs
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
                ->where('ProcStatus', 'C')->whereBetween('ProcDate', [$s, $e])
                ->groupBy('ClinicNum')->pluck('val', 'ClinicNum');

            $adj = DB::table('od_adjustments')
                ->selectRaw('ClinicNum, '.MetricDefinitions::adjustments('val'))
                ->whereBetween('AdjDate', [$s, $e])
                ->groupBy('ClinicNum')->pluck('val', 'ClinicNum');

            $writeoffs = DB::table('od_claim_procs')
                ->selectRaw('ClinicNum, '.MetricDefinitions::writeOffs('val'))
                ->whereBetween('ProcDate', [$s, $e])
                ->groupBy('ClinicNum')->pluck('val', 'ClinicNum');

            $coll = DB::table('od_pay_splits')
                ->selectRaw('ClinicNum, '.MetricDefinitions::collections('val'))
                ->whereBetween('DatePay', [$s, $e])
                ->groupBy('ClinicNum')->pluck('val', 'ClinicNum');

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
            $cg = $currentStats['gross']->get($cNum, 0);
            $ca = $currentStats['adj']->get($cNum, 0);
            $cw = $currentStats['writeoffs']->get($cNum, 0);
            $cc = $currentStats['coll']->get($cNum, 0);
            // $cn = $cg - abs($ca) - abs($cw);
            $cn = $cg + $ca + $cw;

            $lg = $lastYearStats['gross']->get($cNum, 0);
            $la = $lastYearStats['adj']->get($cNum, 0);
            $lw = $lastYearStats['writeoffs']->get($cNum, 0);
            $lc = $lastYearStats['coll']->get($cNum, 0);
            // $ln = $lg - abs($la) - abs($lw);
            $ln = $lg + $la + $lw;

            $result[] = [
                'clinic_num' => $cNum,
                'location' => $this->clinicNames[(int) $cNum] ?? 'Location '.$cNum,
                'gross_production' => round($cg, 2),
                'gross_production_last' => round($lg, 2),
                'adjustments' => round($ca, 2),
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

        $startLastYear = Carbon::parse($start)->subYear()->toDateString();
        $endLastYear = Carbon::parse($end)->subYear()->toDateString();

        $buildVisitStats = function ($s, $e) {
            $patientVisits = DB::table('od_procedure_logs')
                ->where('ProcStatus', 'C')
                ->whereBetween('ProcDate', [$s, $e])
                ->selectRaw('ClinicNum, '.MetricDefinitions::patientVisits('val'))
                ->groupBy('ClinicNum')
                ->pluck('val', 'ClinicNum');

            // Find new patient visits per clinic (patient's first completed procedure at this clinic falls in date range)
            $newPatientVisits = DB::table('od_procedure_logs')
                ->select('PatNum', 'ClinicNum', DB::raw('MIN(ProcDate) as first_visit'))
                ->where('ProcStatus', 'C')
                ->groupBy('PatNum', 'ClinicNum')
                ->havingBetween('first_visit', [$s, $e])
                ->get()
                ->groupBy('ClinicNum')
                ->map->count();

            return compact('patientVisits', 'newPatientVisits');
        };

        $currentStats = $buildVisitStats($start, $end);
        $lastYearStats = $buildVisitStats($startLastYear, $endLastYear);

        $allClinicNums = collect()
            ->merge($currentStats['patientVisits']->keys())
            ->merge($currentStats['newPatientVisits']->keys())
            ->merge($lastYearStats['patientVisits']->keys())
            ->merge($lastYearStats['newPatientVisits']->keys())
            ->unique()
            ->sort();

        $result = [];
        foreach ($allClinicNums as $cNum) {
            $result[] = [
                'clinic_num' => $cNum,
                'location' => $this->clinicNames[(int) $cNum] ?? 'Location '.$cNum,
                'patient_visits' => $currentStats['patientVisits']->get($cNum, 0),
                'patient_visits_last' => $lastYearStats['patientVisits']->get($cNum, 0),
                'new_patient_visits' => $currentStats['newPatientVisits']->get($cNum, 0),
                'new_patient_visits_last' => $lastYearStats['newPatientVisits']->get($cNum, 0),
            ];
        }

        return response()->json($result);
    }
}
