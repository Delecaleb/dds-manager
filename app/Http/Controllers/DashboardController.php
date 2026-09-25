<?php

namespace App\Http\Controllers;

use App\Domain\Patient\PatientService;
use App\Domain\Patient\PatientVisitService;
use App\Domain\Production\ProductionService;
use App\Domain\Support\ClinicRegistry;
use App\Domain\Support\MetricFilter;
use App\Domain\Support\ProcCode;
use App\Domain\Support\ProcStatus;
use App\Helpers\MetricDefinitions;
use App\Models\OdAppointment;
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

    public function index(Request $request)
    {
        return view('dashboard', [
            'locations' => $this->clinics->locations(),
            'selectedLocations' => $this->clinics->select($request->input('locations'))->keys(),
        ]);
    }

    public function data(Request $request, FinancialAnalyticsService $financial, PatientAnalyticsService $patients)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
        $locations = $this->clinics->select($request->input('locations'));

        $gross = 0.0;
        $adjustments = 0.0;
        $writeoffs = 0.0;
        $collections = 0.0;
        $scheduled = 0;
        $visited = 0;
        $newPatientVisit = 0;
        $newPatientsScheduled = 0;

        foreach ($locations->scopes() as $officeId => $clinics) {
            $filter = new MetricFilter($start, $end, $clinics, [], null, $officeId);
            $s = $this->production->summary($filter);
            $gross += $s->gross;
            $adjustments += $s->adjustments;
            $writeoffs += $s->writeOffs;
            $collections += $s->collection;

            $scheduled += (new OdAppointment)->scheduledPatients($start, $end, $officeId, $clinics);
            $visited += $this->patientVisits->patientVisits($start, $end, $clinics, [], $officeId);
            $newPatientVisit += $this->patientVisits->newPatientCount($start, $end, $clinics, [], $officeId);
            $newPatientsScheduled += (new OdAppointment)->newPatientsScheduled($start, $end, $officeId, $clinics);
        }

        $net = $this->production->netFrom($gross, $adjustments, 0.0);
        $adjRate = $gross > 0 ? round((abs($adjustments) / $gross) * 100, 2) : 0;
        $collRate = $net > 0 ? round(($collections / $net) * 100, 2) : 0;
        $patientAvgProduction = $visited > 0 ? round($net / $visited, 2) : 0;

        return response()->json([
            'gross_production' => round($gross, 2),
            'net_production' => $net,
            'adjustments' => round($adjustments, 2),
            'adjustment' => round($adjustments, 2),
            'writeoffs' => round($writeoffs, 2),
            'collections' => round($collections, 2),
            'collection' => round($collections, 2),
            'adjustment_rate' => $adjRate,
            'collection_rate' => $collRate,
            'patient_scheduled' => $scheduled,
            'patient_visits' => $visited,
            'patient_avg_production' => $patientAvgProduction,
            'new_patient_visit' => $newPatientVisit,
            'new_patients_scheduled' => $newPatientsScheduled,
        ]);
    }

    public function locationStats(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
        $locations = $this->clinics->select($request->input('locations'));

        $allRows = collect();

        foreach ($locations->scopes() as $officeId => $scopedClinics) {
            $query = DB::table('od_procedure_logs')
                ->where('office_id', $officeId)
                ->selectRaw(
                    'COALESCE(ClinicNum + 0, 0) as ClinicNum, '.
                    MetricDefinitions::grossProduction('total_production').', '.
                    MetricDefinitions::patientVisits('patient_count')
                )
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$start, $end]);

            if (! empty($scopedClinics)) {
                $query->whereIn('ClinicNum', $scopedClinics);
            }

            $rows = $query->groupBy(DB::raw('COALESCE(ClinicNum + 0, 0)'))
                ->get();

            $adjQuery = DB::table('od_adjustments')
                ->where('office_id', $officeId)
                ->selectRaw('COALESCE(ClinicNum + 0, 0) as ClinicNum, '.MetricDefinitions::adjustments('val'))
                ->whereBetween('AdjDate', [$start, $end]);

            if (! empty($scopedClinics)) {
                $adjQuery->whereIn('ClinicNum', $scopedClinics);
            }

            $adjustments = $adjQuery->groupBy(DB::raw('COALESCE(ClinicNum + 0, 0)'))
                ->pluck('val', 'ClinicNum')
                ->mapWithKeys(fn ($val, $k) => [(int) $k => (float) $val]);

            $woQuery = DB::table('od_claim_procs')
                ->where('office_id', $officeId)
                ->selectRaw('COALESCE(ClinicNum + 0, 0) as ClinicNum, '.MetricDefinitions::writeOffs('val'))
                ->whereBetween('ProcDate', [$start, $end]);

            if (! empty($scopedClinics)) {
                $woQuery->whereIn('ClinicNum', $scopedClinics);
            }

            $writeoffs = $woQuery->groupBy(DB::raw('COALESCE(ClinicNum + 0, 0)'))
                ->pluck('val', 'ClinicNum')
                ->mapWithKeys(fn ($val, $k) => [(int) $k => (float) $val]);

            $clinicNames = $this->clinics->all($officeId);
            $clinicNumsToInclude = ! empty($scopedClinics)
                ? collect($scopedClinics)
                : collect(array_keys($clinicNames))->merge($rows->pluck('ClinicNum'))->map(fn ($k) => (int) $k)->unique()->values();

            $rowsMap = $rows->keyBy(fn ($r) => (int) $r->ClinicNum);

            foreach ($clinicNumsToInclude as $cNum) {
                $row = $rowsMap->get((int) $cNum);
                $gross = $row ? (float) $row->total_production : 0.0;
                $adj = (float) ($adjustments->get((int) $cNum, 0));
                $wo = (float) ($writeoffs->get((int) $cNum, 0));
                $patientCount = $row ? (int) $row->patient_count : 0;

                $net = $this->production->netFrom($gross, $adj, $wo);
                $avg = $patientCount > 0 ? round($net / $patientCount, 2) : 0;

                $locObj = $this->clinics->locationFor($officeId, (int) $cNum);

                $allRows->push([
                    'office_id' => $officeId,
                    'clinic_num' => (int) $cNum,
                    'location_key' => $locObj->key(),
                    'location' => $locObj->name,
                    'total_production' => round($gross, 2),
                    'net_production' => $net,
                    'patient_count' => $patientCount,
                    'avg_production' => $avg,
                ]);
            }
        }

        $result = $allRows->sortByDesc('total_production')->values()->map(function ($item, $i) {
            $item['rank'] = $i + 1;

            return $item;
        });

        return response()->json($result);
    }

    public function providerDetails(Request $request, $id)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
        $officeId = null;
        if ($request->has('office_id')) {
            $officeId = (int) $request->input('office_id');
        } elseif ($request->has('locations')) {
            $selection = $this->clinics->select($request->input('locations'));
            $scopes = $selection->scopes();
            if (! empty($scopes)) {
                $found = DB::table('od_providers')
                    ->whereIn('office_id', array_keys($scopes))
                    ->where('ProvNum', $id)
                    ->first();
                if ($found) {
                    $officeId = (int) $found->office_id;
                } else {
                    $officeId = array_key_first($scopes);
                }
            }
        }

        if (! $officeId) {
            $officeId = Office::getActiveOfficeId() ?? 1;
        }

        $provider = DB::table('od_providers')
            ->where('office_id', $officeId)
            ->where('ProvNum', $id)
            ->first();

        if (! $provider) {
            $provider = DB::table('od_providers')
                ->where('ProvNum', $id)
                ->first();
            if ($provider) {
                $officeId = (int) $provider->office_id;
            }
        }

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
        $dateExpr = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m-%d', ProcDate)"
            : "DATE_FORMAT(ProcDate, '%Y-%m-%d')";

        $dailyProduction = DB::table('od_procedure_logs')
            ->where('office_id', $officeId)
            ->selectRaw(
                "{$dateExpr} AS date, ".
                MetricDefinitions::grossProduction('production').', '.
                MetricDefinitions::patientVisits('patient_count')
            )
            ->where('ProvNum', $id)->whereIn('ProcStatus', ProcStatus::completed())
            ->whereBetween('ProcDate', [$start, $end])
            ->groupBy(DB::raw($dateExpr))
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
                DB::raw("{$dateExpr} AS date"),
                DB::raw("SUM(CASE WHEN ProcStatus IN ({$completed}) THEN 1 ELSE 0 END) AS completed"),
                DB::raw('COUNT(*) AS total')
            )
            ->where('ProvNum', $id)
            ->whereBetween('ProcDate', [$start, $end])
            ->groupBy(DB::raw($dateExpr))
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
        $locations = $this->clinics->select($request->input('locations'));

        $allProviders = collect();

        foreach ($locations->scopes() as $officeId => $scopedClinics) {
            $grossSub = DB::table('od_procedure_logs')->select('ProvNum', DB::raw('SUM(ProcFee) AS gross'))
                ->where('office_id', $officeId)
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$start, $end]);
            if (! empty($scopedClinics)) {
                $grossSub->whereIn('ClinicNum', $scopedClinics);
            }
            $grossSub->groupBy('ProvNum');

            $adjSub = DB::table('od_adjustments')->select('ProvNum', DB::raw('SUM(AdjAmt) AS adjustments'))
                ->where('office_id', $officeId)
                ->whereBetween('AdjDate', [$start, $end]);
            if (! empty($scopedClinics)) {
                $adjSub->whereIn('ClinicNum', $scopedClinics);
            }
            $adjSub->groupBy('ProvNum');

            $writeoffSub = DB::table('od_claim_procs')->select('ProvNum', DB::raw('SUM(WriteOff) AS writeoffs'))
                ->where('office_id', $officeId)
                ->whereBetween('ProcDate', [$start, $end]);
            if (! empty($scopedClinics)) {
                $writeoffSub->whereIn('ClinicNum', $scopedClinics);
            }
            $writeoffSub->groupBy('ProvNum');

            $patCollSub = DB::table('od_pay_splits')
                ->select('ProvNum', DB::raw('SUM(SplitAmt) AS amt'))
                ->where('office_id', $officeId)
                ->whereBetween('DatePay', [$start, $end]);
            if (! empty($scopedClinics)) {
                $patCollSub->whereIn('ClinicNum', $scopedClinics);
            }
            $patCollSub->groupBy('ProvNum');

            $insCollSub = DB::table('od_claim_procs')
                ->select('ProvNum', DB::raw('SUM(InsPayAmt) AS amt'))
                ->where('office_id', $officeId)
                ->whereBetween('DateCP', [$start, $end])
                ->where('Status', '!=', 0);
            if (! empty($scopedClinics)) {
                $insCollSub->whereIn('ClinicNum', $scopedClinics);
            }
            $insCollSub->groupBy('ProvNum');

            $collSub = DB::query()->fromSub(
                $patCollSub->unionAll($insCollSub),
                'c_unioned'
            )->select('ProvNum', DB::raw('SUM(amt) AS collections'))->groupBy('ProvNum');

            $aptsSub = DB::table('od_appointments')
                ->select('ProvNum', DB::raw('COUNT(*) AS appointment_count'))
                ->where('office_id', $officeId)
                ->whereIn('AptStatus', [1, 2])
                ->whereBetween('AptDateTime', [$start.' 00:00:00', $end.' 23:59:59']);
            if (! empty($scopedClinics)) {
                $aptsSub->whereIn('ClinicNum', $scopedClinics);
            }
            $aptsSub->groupBy('ProvNum');

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
                ->get();

            $officeObj = Office::find($officeId);
            $officeDisplayName = $officeObj?->name ?: 'Office #'.$officeId;

            $mapped = $providers->map(function ($p) use ($officeId, $officeDisplayName) {
                $p->office_id = $officeId;
                $p->specialty = $this->specialtyMap[(int) $p->Specialty] ?? 'General Dentistry';
                $p->location = $this->clinics->name((int) ($p->ClinicNum ?? 0), $officeId) ?: $officeDisplayName;
                $p->net_production = $this->production->netFrom(
                    (float) $p->gross_production,
                    (float) $p->adjustments,
                    0.0
                );

                return $p;
            });

            $allProviders = $allProviders->merge($mapped);
        }

        $sorted = $allProviders->sortByDesc('gross_production')->values();

        return response()->json($sorted);
    }

    public function financialsPerLocationData(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
        $locations = $this->clinics->select($request->input('locations'));

        $startLastYear = Carbon::parse($start)->subYear()->toDateString();
        $endLastYear = Carbon::parse($end)->subYear()->toDateString();

        $result = [];

        foreach ($locations->scopes() as $officeId => $scopedClinics) {
            $buildLocationStats = function ($s, $e) use ($officeId, $scopedClinics) {
                $grossQ = DB::table('od_procedure_logs')
                    ->where('office_id', $officeId)
                    ->selectRaw('COALESCE(ClinicNum + 0, 0) as ClinicNum, '.MetricDefinitions::grossProduction('val'))
                    ->whereIn('ProcStatus', ProcStatus::completed())->whereBetween('ProcDate', [$s, $e]);
                if (! empty($scopedClinics)) {
                    $grossQ->whereIn('ClinicNum', $scopedClinics);
                }
                $gross = $grossQ->groupBy(DB::raw('COALESCE(ClinicNum + 0, 0)'))
                    ->pluck('val', 'ClinicNum')
                    ->mapWithKeys(fn ($val, $k) => [(int) $k => (float) $val]);

                $adjQ = DB::table('od_adjustments')
                    ->where('office_id', $officeId)
                    ->selectRaw('COALESCE(ClinicNum + 0, 0) as ClinicNum, '.MetricDefinitions::adjustments('val'))
                    ->whereBetween('AdjDate', [$s, $e]);
                if (! empty($scopedClinics)) {
                    $adjQ->whereIn('ClinicNum', $scopedClinics);
                }
                $adj = $adjQ->groupBy(DB::raw('COALESCE(ClinicNum + 0, 0)'))
                    ->pluck('val', 'ClinicNum')
                    ->mapWithKeys(fn ($val, $k) => [(int) $k => (float) $val]);

                $woQ = DB::table('od_claim_procs')
                    ->where('office_id', $officeId)
                    ->selectRaw('COALESCE(ClinicNum + 0, 0) as ClinicNum, '.MetricDefinitions::writeOffs('val'))
                    ->whereBetween('ProcDate', [$s, $e]);
                if (! empty($scopedClinics)) {
                    $woQ->whereIn('ClinicNum', $scopedClinics);
                }
                $writeoffs = $woQ->groupBy(DB::raw('COALESCE(ClinicNum + 0, 0)'))
                    ->pluck('val', 'ClinicNum')
                    ->mapWithKeys(fn ($val, $k) => [(int) $k => (float) $val]);

                $patCollQ = DB::table('od_pay_splits')
                    ->where('office_id', $officeId)
                    ->selectRaw('COALESCE(ClinicNum + 0, 0) as ClinicNum, SUM(SplitAmt) as val')
                    ->whereBetween('DatePay', [$s, $e]);
                if (! empty($scopedClinics)) {
                    $patCollQ->whereIn('ClinicNum', $scopedClinics);
                }
                $patColl = $patCollQ->groupBy(DB::raw('COALESCE(ClinicNum + 0, 0)'))
                    ->pluck('val', 'ClinicNum')
                    ->mapWithKeys(fn ($val, $k) => [(int) $k => (float) $val]);

                $insCollQ = DB::table('od_claim_procs')
                    ->where('office_id', $officeId)
                    ->selectRaw('COALESCE(ClinicNum + 0, 0) as ClinicNum, SUM(InsPayAmt) as val')
                    ->whereBetween('DateCP', [$s, $e])
                    ->where('Status', '!=', 0);
                if (! empty($scopedClinics)) {
                    $insCollQ->whereIn('ClinicNum', $scopedClinics);
                }
                $insColl = $insCollQ->groupBy(DB::raw('COALESCE(ClinicNum + 0, 0)'))
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

            $allClinicNums = ! empty($scopedClinics)
                ? collect($scopedClinics)
                : collect(array_keys($clinicNames))
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

            foreach ($allClinicNums as $cNum) {
                $cg = (float) $currentStats['gross']->get((int) $cNum, 0);
                $ca = (float) $currentStats['adj']->get((int) $cNum, 0) - (float) $currentStats['writeoffs']->get((int) $cNum, 0);
                $cc = (float) $currentStats['coll']->get((int) $cNum, 0);
                $cn = $cg + $ca;

                $lg = (float) $lastYearStats['gross']->get((int) $cNum, 0);
                $la = (float) $lastYearStats['adj']->get((int) $cNum, 0) - (float) $lastYearStats['writeoffs']->get((int) $cNum, 0);
                $lc = (float) $lastYearStats['coll']->get((int) $cNum, 0);
                $ln = $lg + $la;

                $locObj = $this->clinics->locationFor($officeId, (int) $cNum);

                $result[] = [
                    'office_id' => $officeId,
                    'clinic_num' => (int) $cNum,
                    'location_key' => $locObj->key(),
                    'location' => $locObj->name,
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
        }

        return response()->json($result);
    }

    public function patientVisitsPerLocationData(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());
        $locations = $this->clinics->select($request->input('locations'));

        $startLastYear = Carbon::parse($start)->subYear()->toDateString();
        $endLastYear = Carbon::parse($end)->subYear()->toDateString();

        $result = [];

        foreach ($locations->scopes() as $officeId => $scopedClinics) {
            $excludedCodes = ProcCode::brokenAppointmentCodeNums($officeId);

            $getStats = function ($s, $e) use ($officeId, $scopedClinics, $excludedCodes) {
                $pvQ = DB::table('od_procedure_logs')
                    ->where('office_id', $officeId)
                    ->whereIn('ProcStatus', ProcStatus::completed())
                    ->when(! empty($excludedCodes), fn ($q) => $q->whereNotIn('CodeNum', $excludedCodes))
                    ->whereBetween('ProcDate', [$s, $e]);

                if (! empty($scopedClinics)) {
                    $pvQ->whereIn('ClinicNum', $scopedClinics);
                }

                $patientVisits = $pvQ->selectRaw('COALESCE(ClinicNum + 0, 0) as ClinicNum, '.MetricDefinitions::patientVisits('val'))
                    ->groupBy(DB::raw('COALESCE(ClinicNum + 0, 0)'))
                    ->pluck('val', 'ClinicNum')
                    ->mapWithKeys(fn ($val, $k) => [(int) $k => (int) $val]);

                $newVisits = collect($this->patientVisits->newPatientVisits($s, $e, $scopedClinics, [], $officeId))
                    ->groupBy(fn ($item) => (int) ($item['clinic_num'] ?? 0))
                    ->map(fn ($g) => $g->count());

                return compact('patientVisits', 'newVisits');
            };

            $currentStats = $getStats($start, $end);
            $lastYearStats = $getStats($startLastYear, $endLastYear);

            $clinicNames = $this->clinics->all($officeId);

            $allClinicNums = ! empty($scopedClinics)
                ? collect($scopedClinics)
                : collect(array_keys($clinicNames))
                    ->merge($currentStats['patientVisits']->keys())
                    ->merge($currentStats['newVisits']->keys())
                    ->merge($lastYearStats['patientVisits']->keys())
                    ->merge($lastYearStats['newVisits']->keys())
                    ->map(fn ($k) => (int) $k)
                    ->unique()
                    ->sort()
                    ->values();

            foreach ($allClinicNums as $cNum) {
                $locObj = $this->clinics->locationFor($officeId, (int) $cNum);

                $result[] = [
                    'office_id' => $officeId,
                    'clinic_num' => (int) $cNum,
                    'location_key' => $locObj->key(),
                    'location' => $locObj->name,
                    'patient_visits' => (int) $currentStats['patientVisits']->get((int) $cNum, 0),
                    'patient_visits_last' => (int) $lastYearStats['patientVisits']->get((int) $cNum, 0),
                    'new_patient_visits' => (int) ($currentStats['newVisits']->get((int) $cNum, 0)),
                    'new_patient_visits_last' => (int) ($lastYearStats['newVisits']->get((int) $cNum, 0)),
                ];
            }
        }

        return response()->json($result);
    }
}
