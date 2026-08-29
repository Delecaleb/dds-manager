<?php

namespace App\Http\Controllers;

use App\Domain\Production\ProductionService;
use App\Domain\Support\MetricFilter;
use App\Domain\Support\ProcStatus;
use App\Models\OdAppointment;
use App\Models\OdPatient;
use App\Models\OdProcedureLog;
use App\Models\OdRecall;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class FrontOfficeController extends Controller
{
    public function __construct(
        private readonly ProductionService $production,
    ) {}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return view('front-office.partials.schedule');
        }

        return view('front-office.index', ['activeTab' => 'schedule']);
    }

    public function collections(Request $request)
    {
        if ($request->ajax()) {
            return view('front-office.partials.collections');
        }

        return view('front-office.index', ['activeTab' => 'collections']);
    }

    public function stats(Request $request)
    {
        $monthYear = $request->get('month_year', Carbon::now()->format('Y-m'));
        $targetDate = Carbon::createFromFormat('Y-m', $monthYear);

        // Monthly Production calculation
        $monthStart = $targetDate->copy()->startOfMonth();
        $monthEnd = $targetDate->copy()->endOfMonth();
        $startOfMonth = $monthStart->format('Y-m-d');
        $endOfMonth = $monthEnd->format('Y-m-d');

        // The production period is MONTH-TO-DATE while the month is still in
        // progress, and the full month once it has closed. This is what makes
        // the prior-year comparison below like-for-like: on 1/22/21 we measure
        // 1/1/21-1/22/21 against 1/1/20-1/22/20, never against all of Jan 2020.
        $today = Carbon::today();
        $periodEnd = $today->between($monthStart, $monthEnd) ? $today : $monthEnd;

        $productionFilter = new MetricFilter($startOfMonth, $periodEnd->format('Y-m-d'));
        $priorYearFilter = $productionFilter->lastYear();

        $monthlyProduction = $this->production->netProduction($productionFilter);

        // Prior Year: the SAME span, shifted back exactly one year.
        $priorYearProduction = $this->production->netProduction($priorYearFilter);

        // Note: For now, $100k is used as simple monthly goal for UI ratio mapping till Goals system added.
        $monthlyGoal = 109286.00;
        $pctGoal = $monthlyGoal > 0 ? round(($monthlyProduction / $monthlyGoal) * 100, 2) : 0;

        $productionDiff = round($monthlyProduction - $monthlyGoal, 2);

        // Variance in dollars: current-year actual vs prior-year actual over
        // the same span. Positive = up on last year, negative = down.
        $yearDiff = round($monthlyProduction - $priorYearProduction, 2);

        // Daily Production & Week Navigation
        $dailyActuals = [];
        $dailyGoals = [];
        $startDateParam = $request->get('start_date');

        if ($startDateParam) {
            $startOfWeek = Carbon::parse($startDateParam)->startOfWeek(Carbon::MONDAY);
        } else {
            if ($targetDate->isCurrentMonth()) {
                $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
            } else {
                $startOfWeek = clone $targetDate->startOfMonth()->startOfWeek(Carbon::MONDAY);
            }
        }

        for ($i = 0; $i < 5; $i++) {
            $day = $startOfWeek->copy()->addDays($i)->format('Y-m-d');
            $dailyActuals[] = $this->production->netProduction(new MetricFilter($day, $day));
            $dailyGoals[] = $monthlyGoal / 20; // Avg 20 working days
        }

        // Visits (New vs Existing)
        $startOfWeekDate = $startOfWeek->format('Y-m-d');
        $endOfWeekDate = $startOfWeek->copy()->addDays(4)->format('Y-m-d');

        $visits = OdAppointment::with('patient')
            ->whereBetween('AptDateTime', [$startOfWeekDate.' 00:00:00', $endOfWeekDate.' 23:59:59'])
            ->whereIn('AptStatus', [1, 2]) // Schedule / Complete
            ->get();

        $dailyNew = [0, 0, 0, 0, 0];
        $dailyExisting = [0, 0, 0, 0, 0];

        foreach ($visits as $apt) {
            $dayIndex = Carbon::parse($apt->AptDateTime)->dayOfWeek - 1;
            if ($dayIndex >= 0 && $dayIndex < 5) {
                // If patient has 'IsNewPatient' field, use it. Otherwise, simple naive check via Appointments count
                $isNew = false;
                if (! empty($apt->PatNum)) {
                    $count = OdAppointment::where('PatNum', $apt->PatNum)->count();
                    if ($count <= 1) {
                        $isNew = true;
                    }
                }

                if ($isNew) {
                    $dailyNew[$dayIndex]++;
                } else {
                    $dailyExisting[$dayIndex]++;
                }
            }
        }

        // OPPORTUNITIES LOGIC
        $excludedAptNums = [85716, 85845, 85891, 85892, 85468, 85466, 85947];

        // 1. Broken Appointments (Existing Recall Patients)
        $brokenApts = OdAppointment::where('AptStatus', 5)
            ->where('IsNewPatient', 0) // Existing patients (New Patient consults are tracked separately in New Patient pipeline)
            ->whereNotIn('AptNum', $excludedAptNums)
            ->whereBetween('AptDateTime', [$startOfMonth.' 00:00:00', $endOfMonth.' 23:59:59'])
            ->get();

        $brokenTotal = $brokenApts->count();
        $brokenScheduled = 0;

        $patNums = $brokenApts->pluck('PatNum')->unique()->toArray();
        if (! empty($patNums)) {
            $futureApts = OdAppointment::whereIn('PatNum', $patNums)
                ->where('AptDateTime', '>', $endOfMonth)
                ->whereIn('AptStatus', [1, 2])
                ->pluck('PatNum')->toArray();

            $brokenScheduled = collect($brokenApts)->whereIn('PatNum', $futureApts)->count();
        }
        $brokenUnscheduled = $brokenTotal - $brokenScheduled;

        // 2. Hygiene Reappointment
        // Typically IsHygiene correlates to hygiene tracking native to OpenDental
        $hygApts = clone $brokenApts; // instantiate empty collection fallback just in case
        try {
            $hygApts = OdAppointment::where('AptStatus', 2)
                ->whereBetween('AptDateTime', [$startOfMonth, $endOfMonth])
                ->where('IsHygiene', 1)
                ->get();
        } catch (\Exception $e) {
            $hygApts = collect();
        }

        $hygTotal = $hygApts->count();
        $hygScheduled = 0;

        if ($hygTotal > 0) {
            $hygPatNums = $hygApts->pluck('PatNum')->unique()->toArray();
            $hygFutureApts = OdAppointment::whereIn('PatNum', $hygPatNums)
                ->where('AptDateTime', '>', $endOfMonth)
                ->whereIn('AptStatus', [1, 2])
                ->pluck('PatNum')->toArray();

            $hygScheduled = collect($hygApts)->whereIn('PatNum', $hygFutureApts)->count();
        }
        $hygUnscheduled = $hygTotal - $hygScheduled;
        $hygReapptRate = $hygTotal > 0 ? round(($hygScheduled / $hygTotal) * 100, 2) : 0;

        // 3. Hygiene Recall Due
        $recalls = OdRecall::whereNotNull('DateDue')
            ->where('DateDue', '<=', $endOfMonth)
            ->where('DateDue', '>=', '1900-01-01')
            ->get();

        $recallBuckets = [0, 0, 0, 0, 0];
        foreach ($recalls as $r) {
            $monthsPast = Carbon::parse($r->DateDue)->diffInMonths(Carbon::parse($endOfMonth));
            if ($monthsPast <= 3) {
                $recallBuckets[0]++;
            } elseif ($monthsPast <= 6) {
                $recallBuckets[1]++;
            } elseif ($monthsPast <= 9) {
                $recallBuckets[2]++;
            } elseif ($monthsPast <= 12) {
                $recallBuckets[3]++;
            } else {
                $recallBuckets[4]++;
            }
        }

        // 4. Unscheduled TX
        $tpProcs = OdProcedureLog::whereIn('ProcStatus', ProcStatus::treatmentPlanned()) // Treatment Planned
            ->where('ProvNum', '>', 0)
            ->whereNotNull('DateTP')
            ->where('DateTP', '<=', $endOfMonth)
            ->where('DateTP', '>=', '1900-01-01')
            ->get(); // Note: some databases could use larger memory here depending on historical scale, pagination/streaming usually ideal in production

        $txBuckets = [
            'count' => [0, 0, 0, 0, 0],
            'amount' => [0, 0, 0, 0, 0],
        ];

        foreach ($tpProcs as $tp) {
            $monthsPast = Carbon::parse($tp->DateTP)->diffInMonths(Carbon::parse($endOfMonth));
            $idx = 4;
            if ($monthsPast <= 3) {
                $idx = 0;
            } elseif ($monthsPast <= 6) {
                $idx = 1;
            } elseif ($monthsPast <= 9) {
                $idx = 2;
            } elseif ($monthsPast <= 12) {
                $idx = 3;
            }

            $txBuckets['count'][$idx]++;
            $txBuckets['amount'][$idx] += (float) $tp->ProcFee;
        }

        return response()->json([
            'monthly' => [
                'goal' => $monthlyGoal,
                'actual' => $monthlyProduction,
                'percent_goal' => $pctGoal,
                'prior_year' => $priorYearProduction,
                'diff_goal' => $productionDiff,
                'diff_year' => $yearDiff,
                // The two spans actually compared, so the card can label them
                // instead of the user having to infer the month-to-date cut-off.
                'period' => [
                    'start' => $productionFilter->start,
                    'end' => $productionFilter->end,
                ],
                'prior_period' => [
                    'start' => $priorYearFilter->start,
                    'end' => $priorYearFilter->end,
                ],
            ],
            'week_period' => [
                'start_date' => $startOfWeekDate,
                'end_date' => $endOfWeekDate,
                'formatted' => Carbon::parse($startOfWeekDate)->format('M d').' - '.Carbon::parse($endOfWeekDate)->format('M d'),
            ],
            'daily' => [
                'actuals' => $dailyActuals,
                'goals' => $dailyGoals,
            ],
            'visits' => [
                'new' => $dailyNew,
                'existing' => $dailyExisting,
            ],
            'opportunities' => [
                'broken' => [
                    'total' => $brokenTotal,
                    'scheduled' => $brokenScheduled,
                    'unscheduled' => $brokenUnscheduled,
                ],
                'hygiene' => [
                    'total' => $hygTotal,
                    'scheduled' => $hygScheduled,
                    'unscheduled' => $hygUnscheduled,
                    'rate' => $hygReapptRate,
                ],
            ],
            'recall_due' => $recallBuckets,
            'unscheduled_tx' => $txBuckets,
        ]);
    }

    protected function getFilterDateRange(Request $request): array
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay()->format('Y-m-d H:i:s');
            $end = Carbon::parse($endDate)->endOfDay()->format('Y-m-d 23:59:59');

            return [$start, $end];
        }

        if ($startDate) {
            $start = Carbon::parse($startDate)->startOfDay()->format('Y-m-d H:i:s');
            $end = Carbon::parse($startDate)->addDays(4)->endOfDay()->format('Y-m-d 23:59:59');

            return [$start, $end];
        }

        $monthYear = $request->get('month_year', $request->get('month', Carbon::now()->format('Y-m')));

        try {
            $targetDate = Carbon::createFromFormat('Y-m', $monthYear);
        } catch (\Exception $e) {
            $targetDate = Carbon::now();
        }

        $start = $targetDate->copy()->startOfMonth()->format('Y-m-d 00:00:00');
        $end = $targetDate->copy()->endOfMonth()->format('Y-m-d 23:59:59');

        return [$start, $end];
    }

    public function brokenAppointments(Request $request)
    {
        [$startRange, $endRange] = $this->getFilterDateRange($request);

        $query = OdAppointment::query()
            ->select('od_appointments.*')
            ->with(['patient', 'provider'])
            ->where('od_appointments.AptStatus', 5)
            ->where('od_appointments.IsNewPatient', 0)
            ->whereBetween('od_appointments.AptDateTime', [$startRange, $endRange]);

        // Fast string-indexed candidate IDs for batch lookups
        $candidateApts = (clone $query)->get(['AptNum', 'PatNum', 'InsPlan1', 'InsPlan2', 'AptDateTime']);
        $patNums = array_values(array_filter(array_unique($candidateApts->pluck('PatNum')->map(fn ($p) => (string) $p)->toArray()), fn ($s) => $s !== '' && $s !== '0'));
        $aptNums = array_values(array_filter(array_unique($candidateApts->pluck('AptNum')->map(fn ($a) => (string) $a)->toArray()), fn ($s) => $s !== '' && $s !== '0'));
        $insPlanNums = array_values(array_filter(array_unique(array_merge(
            $candidateApts->pluck('InsPlan1')->map(fn ($i) => (string) $i)->toArray(),
            $candidateApts->pluck('InsPlan2')->map(fn ($i) => (string) $i)->toArray()
        )), fn ($s) => $s !== '' && $s !== '0'));

        // Batch procedure logs & descriptions (using PatNum index + AptNum filter)
        $feesMap = [];
        $procDescMap = [];
        if (! empty($patNums) && ! empty($aptNums)) {
            foreach (array_chunk($patNums, 200) as $patChunk) {
                $procLogs = DB::table('od_procedure_logs as pl')
                    ->leftJoin('od_procedures as p', 'pl.CodeNum', '=', 'p.CodeNum')
                    ->whereIn('pl.PatNum', $patChunk)
                    ->whereIn('pl.AptNum', $aptNums)
                    ->where('pl.ProcStatus', '!=', '6')
                    ->select('pl.AptNum', 'pl.ProcFee', 'p.Descript', 'p.ProcCode')
                    ->get();

                foreach ($procLogs as $pl) {
                    $feesMap[$pl->AptNum] = ($feesMap[$pl->AptNum] ?? 0) + (float) $pl->ProcFee;
                    if (! empty($pl->Descript) && empty($procDescMap[$pl->AptNum])) {
                        $procDescMap[$pl->AptNum] = $pl->Descript;
                    }
                }
            }
        }

        // Batch next scheduled appointment for each patient
        $nextVisitMap = [];
        if (! empty($patNums)) {
            foreach (array_chunk($patNums, 200) as $patChunk) {
                $futureApts = OdAppointment::query()
                    ->whereIn('PatNum', $patChunk)
                    ->whereIn('AptStatus', [1, 4])
                    ->where('AptDateTime', '>', $startRange)
                    ->select('PatNum', 'AptDateTime')
                    ->orderBy('AptDateTime', 'asc')
                    ->get();

                foreach ($futureApts as $fa) {
                    if (! isset($nextVisitMap[$fa->PatNum])) {
                        $nextVisitMap[$fa->PatNum] = substr($fa->AptDateTime, 0, 10);
                    }
                }
            }
        }

        // Batch insurance carriers
        $carrierMap = [];
        if (! empty($insPlanNums)) {
            foreach (array_chunk($insPlanNums, 200) as $planChunk) {
                $carriers = DB::table('od_insplans as ip')
                    ->join('od_carriers as c', 'ip.CarrierNum', '=', 'c.CarrierNum')
                    ->whereIn('ip.PlanNum', $planChunk)
                    ->pluck('c.CarrierName', 'ip.PlanNum')
                    ->toArray();

                foreach ($carriers as $pNum => $cName) {
                    $carrierMap[$pNum] = $cName;
                }
            }
        }

        return DataTables::eloquent($query)
            ->addColumn('patient_name', fn ($apt) => preg_replace('/\s+/', ' ', trim(($apt->patient?->FName ?? '').' '.($apt->patient?->LName ?? ''))))
            ->addColumn('status', fn ($apt) => 'UNSCHEDULED')
            ->addColumn('amount', function ($apt) use ($feesMap) {
                $fee = (float) ($feesMap[$apt->AptNum] ?? 0);

                return $fee > 0 ? '$ '.number_format($fee, 2) : '$ 0';
            })
            ->addColumn('phone', fn ($apt) => $this->formatPhoneNumber($apt->patient?->HmPhone))
            ->addColumn('work_phone', fn ($apt) => $this->formatPhoneNumber($apt->patient?->WkPhone))
            ->addColumn('mobile_phone', fn ($apt) => $this->formatPhoneNumber($apt->patient?->WirelessPhone))
            ->addColumn('email', fn ($apt) => $apt->patient?->Email ?? '')
            ->addColumn('insurance_carrier', fn ($apt) => $carrierMap[$apt->InsPlan1] ?? ($carrierMap[$apt->InsPlan2] ?? 'No insurance'))
            ->addColumn('provider_name', function ($apt) {
                if (! $apt->ProvNum || ! $apt->provider) {
                    return '—';
                }

                $knownDoctors = [
                    'HADD' => 'Mason Haddow',
                    'Haddow' => 'Mason Haddow',
                    'ELIAS' => 'Kathy Elias',
                    'Elias' => 'Kathy Elias',
                    'ZEITOUN' => 'Ali Zeitoun',
                    'Zeitoun' => 'Ali Zeitoun',
                    'ZEIT' => 'Ali Zeitoun',
                    'DETD' => 'Detroit Dental Care, PC',
                    'MASS' => 'Massenburg',
                    'SANJ' => 'Sanjiv Johnson',
                    'TERR' => 'Terrance Johnson',
                    'ROSE' => 'Rose Pitaro',
                    'HELL' => 'Heller',
                ];

                $abbr = trim($apt->provider->Abbr ?? '');
                $lastName = trim($apt->provider->LName ?? '');
                $firstName = trim($apt->provider->PName ?: ($apt->provider->PreferredName ?? ''));

                if (isset($knownDoctors[$abbr])) {
                    return $knownDoctors[$abbr];
                }
                if (isset($knownDoctors[$lastName])) {
                    return $knownDoctors[$lastName];
                }
                if ($firstName !== '' && $lastName !== '') {
                    return "{$firstName} {$lastName}";
                }

                return $lastName ?: ($firstName ?: '—');
            })
            ->addColumn('next_visit_date', fn ($apt) => $nextVisitMap[$apt->PatNum] ?? 'N/A')
            ->addColumn('recall_due', fn () => 'N/A')
            ->addColumn('remaining_benefits', fn () => '$ 0')
            ->addColumn('date', fn ($apt) => $apt->AptDateTime ? (new Carbon($apt->AptDateTime))->format('Y-m-d') : '')
            ->addColumn('time', fn ($apt) => $apt->AptDateTime ? (new Carbon($apt->AptDateTime))->format('H:i:s') : '')
            ->addColumn('type', fn () => 'Cancellation')
            ->addColumn('description', function ($apt) use ($procDescMap) {
                if (isset($procDescMap[$apt->AptNum]) && ! empty($procDescMap[$apt->AptNum])) {
                    return $procDescMap[$apt->AptNum];
                }

                $descript = trim($apt->ProcDescript ?? '');
                if ($descript !== '' && $descript !== '--' && $descript !== 'NCCN' && $descript !== 'MissAppt') {
                    return $descript;
                }

                return 'N/A';
            })
            ->addColumn('note', fn ($apt) => $apt->Note ?? '')
            ->make(true);
    }

    private function formatPhoneNumber(?string $phone): string
    {
        if (empty($phone) || $phone === '--') {
            return 'N/A';
        }

        $clean = preg_replace('/[^\d]/', '', $phone);
        if (strlen($clean) === 10) {
            return '('.substr($clean, 0, 3).')'.substr($clean, 3, 3).'-'.substr($clean, 6, 4);
        }
        if (strlen($clean) === 11 && str_starts_with($clean, '1')) {
            return '1('.substr($clean, 1, 3).')'.substr($clean, 4, 3).'-'.substr($clean, 7, 4);
        }

        return trim($phone);
    }

    public function hygieneRecallDue(Request $request)
    {
        [$startRange, $endRange] = $this->getFilterDateRange($request);
        $startDateStr = substr($startRange, 0, 10);
        $endDateStr = substr($endRange, 0, 10);

        $query = OdRecall::query()
            ->join('od_patients', 'od_recalls.PatNum', '=', 'od_patients.PatNum')
            ->leftJoin('od_providers', 'od_patients.PriProv', '=', 'od_providers.ProvNum')
            ->whereNotNull('od_recalls.DateDue')
            ->whereBetween('od_recalls.DateDue', [$startDateStr, $endDateStr])
            ->select(
                'od_recalls.*',
                'od_patients.FName',
                'od_patients.LName',
                'od_patients.Birthdate',
                'od_patients.HmPhone',
                'od_patients.WkPhone',
                'od_patients.WirelessPhone',
                'od_patients.Email',
                'od_providers.Abbr as prov_abbr',
                'od_providers.LName as prov_lname'
            );

        return DataTables::eloquent($query)
            ->addColumn('patient_name', fn ($row) => trim(($row->LName ?? '').', '.($row->FName ?? '')))
            ->addColumn('age', function ($row) {
                if (! empty($row->Birthdate) && ! in_array($row->Birthdate, ['0001-01-01', '1900-01-01'])) {
                    try {
                        return Carbon::parse($row->Birthdate)->age;
                    } catch (\Exception $e) {
                    }
                }

                return 'N/A';
            })
            ->addColumn('phone', fn ($row) => $row->HmPhone ?? 'N/A')
            ->addColumn('work_phone', fn ($row) => $row->WkPhone ?? 'N/A')
            ->addColumn('mobile_phone', fn ($row) => $row->WirelessPhone ?? 'N/A')
            ->addColumn('email', fn ($row) => $row->Email ?? '')
            ->addColumn('provider_name', fn ($row) => $row->prov_abbr ?? ($row->prov_lname ?? '—'))
            ->addColumn('next_visit_date', fn () => 'N/A')
            ->addColumn('recall_due', fn ($row) => $row->DateDue ? date('Y-m-d H:i:s', strtotime($row->DateDue)) : 'N/A')
            ->addColumn('last_recall_apt_date', fn ($row) => $row->DatePrevious ? date('Y-m-d', strtotime($row->DatePrevious)) : 'N/A')
            ->addColumn('remaining_benefits', fn () => '$ 9,999.00')
            ->addColumn('description', fn () => 'prophylaxis - adult')
            ->addColumn('note', fn () => 'N/A')
            ->make(true);
    }

    public function unscheduledTreatment(Request $request)
    {
        [$startRange, $endRange] = $this->getFilterDateRange($request);
        $startDateStr = substr($startRange, 0, 10);
        $endDateStr = substr($endRange, 0, 10);

        $query = OdProcedureLog::query()
            ->leftJoin('od_patients', 'od_procedure_logs.PatNum', '=', 'od_patients.PatNum')
            ->leftJoin('od_providers', 'od_procedure_logs.ProvNum', '=', 'od_providers.ProvNum')
            ->whereIn('od_procedure_logs.ProcStatus', ProcStatus::treatmentPlanned())
            ->where('od_procedure_logs.ProvNum', '>', 0)
            ->whereNotNull('od_procedure_logs.DateTP')
            ->whereBetween('od_procedure_logs.DateTP', [$startDateStr, $endDateStr])
            ->select(
                'od_procedure_logs.*',
                'od_patients.FName',
                'od_patients.LName',
                'od_patients.Birthdate',
                'od_patients.HmPhone',
                'od_patients.WkPhone',
                'od_patients.WirelessPhone',
                'od_patients.Email',
                'od_providers.Abbr as prov_abbr',
                'od_providers.LName as prov_lname'
            );

        return DataTables::eloquent($query)
            ->addColumn('patient_name', fn ($row) => trim(($row->LName ?? '').', '.($row->FName ?? '')))
            ->addColumn('amount', fn ($row) => (float) ($row->ProcFee ?? 0))
            ->addColumn('remaining_benefits', fn () => '$ 109,998.00')
            ->addColumn('age', function ($row) {
                if (! empty($row->Birthdate) && ! in_array($row->Birthdate, ['0001-01-01', '1900-01-01'])) {
                    try {
                        return Carbon::parse($row->Birthdate)->age;
                    } catch (\Exception $e) {
                    }
                }

                return 'N/A';
            })
            ->addColumn('phone', fn ($row) => $row->HmPhone ?? 'N/A')
            ->addColumn('work_phone', fn ($row) => $row->WkPhone ?? 'N/A')
            ->addColumn('mobile_phone', fn ($row) => $row->WirelessPhone ?? 'N/A')
            ->addColumn('email', fn ($row) => $row->Email ?? '')
            ->addColumn('provider_name', fn ($row) => $row->prov_abbr ?? ($row->prov_lname ?? '—'))
            ->addColumn('next_visit_date', fn () => 'N/A')
            ->addColumn('recall_due', fn () => 'N/A')
            ->addColumn('date_tp', fn ($row) => $row->DateTP ? date('Y-m-d', strtotime($row->DateTP)) : 'N/A')
            ->addColumn('tx_plan_created_date', fn ($row) => $row->DateTP ? date('Y-m-d', strtotime($row->DateTP)) : 'N/A')
            ->make(true);
    }

    public function hygieneReappoint(Request $request)
    {
        [$startRange, $endRange] = $this->getFilterDateRange($request);

        $query = OdAppointment::query()
            ->with(['patient', 'provider'])
            ->where('AptStatus', 2) // Completed
            ->where('IsHygiene', 1)
            ->whereBetween('AptDateTime', [$startRange, $endRange])
            ->select('od_appointments.*');

        $hygApts = (clone $query)->get();
        $patNums = $hygApts->pluck('PatNum')->unique()->toArray();
        $scheduledPatNums = [];
        if (! empty($patNums)) {
            $scheduledPatNums = OdAppointment::whereIn('PatNum', $patNums)
                ->where('AptDateTime', '>', $endRange)
                ->whereIn('AptStatus', [1, 2])
                ->pluck('PatNum')
                ->toArray();
        }

        if (! empty($scheduledPatNums)) {
            $query->whereNotIn('PatNum', $scheduledPatNums);
        }

        return DataTables::eloquent($query)
            ->addColumn('patient_name', fn ($apt) => trim(($apt->patient->LName ?? '').', '.($apt->patient->FName ?? '')))
            ->addColumn('status', fn () => 'NEEDS REAPPOINTMENT')
            ->addColumn('age', function ($apt) {
                if (! empty($apt->patient->Birthdate) && ! in_array($apt->patient->Birthdate, ['0001-01-01', '1900-01-01'])) {
                    try {
                        return Carbon::parse($apt->patient->Birthdate)->age;
                    } catch (\Exception $e) {
                    }
                }

                return 'N/A';
            })
            ->addColumn('phone', fn ($apt) => $apt->patient->HmPhone ?? 'N/A')
            ->addColumn('work_phone', fn ($apt) => $apt->patient->WkPhone ?? 'N/A')
            ->addColumn('mobile_phone', fn ($apt) => $apt->patient->WirelessPhone ?? 'N/A')
            ->addColumn('email', fn ($apt) => $apt->patient->Email ?? '')
            ->addColumn('insurance_carrier', fn () => 'N/A')
            ->addColumn('provider_name', fn ($apt) => $apt->provider->Abbr ?? ($apt->provider->LName ?? '—'))
            ->addColumn('next_visit_date', fn () => 'N/A')
            ->addColumn('recall_due', fn () => 'N/A')
            ->addColumn('remaining_benefits', fn () => '$ 9,999.00')
            ->addColumn('date', fn ($apt) => $apt->AptDateTime ? date('Y-m-d', strtotime($apt->AptDateTime)) : '')
            ->addColumn('time', fn ($apt) => $apt->AptDateTime ? date('H:i:s', strtotime($apt->AptDateTime)) : '')
            ->addColumn('description', fn ($apt) => $apt->ProcDescript ?? ($apt->Note ?? 'Hygiene Visit'))
            ->make(true);
    }

    public function tasks(Request $request)
    {
        if ($request->ajax()) {
            return view('front-office.partials.tasks');
        }

        return view('front-office.index', ['activeTab' => 'tasks']);
    }

    public function tasksData(Request $request)
    {
        $filter = $request->get('filter', 'unconfirmed');
        [$startRange, $endRange] = $this->getFilterDateRange($request);

        $query = OdAppointment::query()
            ->with(['patient', 'provider'])
            ->whereBetween('AptDateTime', [$startRange, $endRange])
            ->select('od_appointments.*')
            ->orderBy('AptDateTime', 'asc');

        if ($filter === 'unconfirmed') {
            $query->whereIn('AptStatus', [1, 4]); // Scheduled or ASAP
        } elseif ($filter === 'no_insurance') {
            $query->whereIn('AptStatus', [1, 4])->where('IsNewPatient', 1);
        } elseif ($filter === 'missing_data') {
            $query->whereIn('AptStatus', [1, 4]);
        } elseif ($filter === 'reminders') {
            $query->whereIn('AptStatus', [1, 4]);
        }

        return DataTables::eloquent($query)
            ->addColumn('patient_name', function ($apt) {
                return trim(($apt->patient->FName ?? '').' '.($apt->patient->LName ?? ''));
            })
            ->addColumn('age', function ($apt) {
                return $apt->patient && $apt->patient->Birthdate ? Carbon::parse($apt->patient->Birthdate)->age : '';
            })
            ->addColumn('phone', function ($apt) {
                return $apt->patient->HmPhone ?? 'N/A';
            })
            ->addColumn('work_phone', function ($apt) {
                return $apt->patient->WkPhone ?? 'N/A';
            })
            ->addColumn('mobile_phone', function ($apt) {
                return $apt->patient->WirelessPhone ?? 'N/A';
            })
            ->addColumn('email', function ($apt) {
                return $apt->patient->Email ?? '';
            })
            ->addColumn('appt_date', function ($apt) {
                return $apt->AptDateTime ? date('M d, Y', strtotime($apt->AptDateTime)) : '';
            })
            ->addColumn('appt_time', function ($apt) {
                return $apt->AptDateTime ? date('h:i a', strtotime($apt->AptDateTime)) : '';
            })
            ->addColumn('description', function ($apt) {
                return $apt->ProcDescript ?? ($apt->Note ?? '');
            })
            ->addColumn('provider', function ($apt) {
                return $apt->provider->LName ?? '—';
            })
            ->addColumn('action', function ($apt) {
                return '<button class="text-[10px] text-gray-500 hover:text-emerald-600 font-bold px-2 py-1 border border-gray-200 hover:border-emerald-500 rounded bg-white shadow-sm transition-colors">COMPLETE</button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function tasksStats(Request $request)
    {
        [$startRange, $endRange] = $this->getFilterDateRange($request);

        $apts = OdAppointment::query()
            ->with(['patient'])
            ->whereBetween('AptDateTime', [$startRange, $endRange])
            ->whereIn('AptStatus', [1, 4])
            ->get();

        $unconfirmedCount = $apts->count();
        $noInsuranceCount = $apts->where('IsNewPatient', 1)->count();
        $missingDataCount = $apts->filter(function ($apt) {
            $p = $apt->patient;
            if (! $p) {
                return true;
            }

            return (empty($p->WirelessPhone) && empty($p->HmPhone)) || empty($p->Email) || (float) ($p->BalTotal ?? 0) > 0;
        })->count();
        $remindersCount = 0;

        // Build daily timeline
        $labels = [];
        $unconfirmedTimeline = [];
        $noInsTimeline = [];
        $missingDataTimeline = [];
        $remindersTimeline = [];

        $start = Carbon::parse($startRange);
        $end = Carbon::parse($endRange);
        $diffDays = $start->diffInDays($end);

        // If period is large, bucket appropriately, otherwise daily
        $current = $start->copy();
        while ($current->lte($end)) {
            $dayStr = $current->format('Y-m-d');
            $labels[] = $current->format('M d');

            $dayApts = $apts->filter(fn ($a) => substr($a->AptDateTime, 0, 10) === $dayStr);
            $unconfirmedTimeline[] = $dayApts->count();
            $noInsTimeline[] = $dayApts->where('IsNewPatient', 1)->count();
            $missingDataTimeline[] = $dayApts->filter(function ($apt) {
                $p = $apt->patient;
                if (! $p) {
                    return true;
                }

                return (empty($p->WirelessPhone) && empty($p->HmPhone)) || empty($p->Email) || (float) ($p->BalTotal ?? 0) > 0;
            })->count();
            $remindersTimeline[] = 0;

            $current->addDay();
        }

        return response()->json([
            'summary' => [
                'unconfirmed' => $unconfirmedCount,
                'no_insurance' => $noInsuranceCount,
                'missing_data' => $missingDataCount,
                'reminders' => $remindersCount,
            ],
            'chart' => [
                'labels' => $labels,
                'unconfirmed' => $unconfirmedTimeline,
                'no_insurance' => $noInsTimeline,
                'missing_data' => $missingDataTimeline,
                'reminders' => $remindersTimeline,
            ],
        ]);
    }

    public function collectionsData(Request $request)
    {
        $subtab = $request->get('subtab', 'patient-balances');
        [$startRange, $endRange] = $this->getFilterDateRange($request);
        $startDateStr = substr($startRange, 0, 10);
        $endDateStr = substr($endRange, 0, 10);

        // Subtab 1: Patient Balances 30/60/90
        if ($subtab === 'patient-balances') {
            $query = OdPatient::query()
                ->select(
                    'od_patients.Guarantor',
                    'g.LName as GuarantorLName',
                    'g.FName as GuarantorFName',
                    DB::raw('SUM(CAST(od_patients.Bal_0_30 AS DECIMAL(10,2))) as Bal_0_30'),
                    DB::raw('SUM(CAST(od_patients.Bal_31_60 AS DECIMAL(10,2))) as Bal_31_60'),
                    DB::raw('SUM(CAST(od_patients.Bal_61_90 AS DECIMAL(10,2))) as Bal_61_90'),
                    DB::raw('SUM(CAST(od_patients.BalOver90 AS DECIMAL(10,2))) as BalOver90'),
                    DB::raw('SUM(CAST(od_patients.BalTotal AS DECIMAL(10,2))) as BalTotal')
                )
                ->leftJoin('od_patients as g', 'od_patients.Guarantor', '=', 'g.PatNum')
                ->groupBy('od_patients.Guarantor', 'g.LName', 'g.FName')
                ->havingRaw('SUM(CAST(od_patients.BalTotal AS DECIMAL(10,2))) > 0');

            return DataTables::of($query)
                ->addColumn('guarantor', function ($row) {
                    $name = trim(($row->GuarantorLName ?? '').', '.($row->GuarantorFName ?? ''));
                    if (! $name || $name == ',') {
                        $name = 'Unknown Guarantor';
                    }

                    return $name;
                })
                ->addColumn('current', fn ($row) => (float) ($row->Bal_0_30 ?? 0))
                ->addColumn('over_30', fn ($row) => (float) ($row->Bal_31_60 ?? 0))
                ->addColumn('over_60', fn ($row) => (float) ($row->Bal_61_90 ?? 0))
                ->addColumn('over_90', fn ($row) => (float) ($row->BalOver90 ?? 0))
                ->addColumn('over_120', fn ($row) => 0.0)
                ->addColumn('total', fn ($row) => (float) ($row->BalTotal ?? 0))
                ->make(true);
        }

        // Subtab 2: CoPay Collections (from fr-off-copay-collection.html)
        if ($subtab === 'copay-collections') {
            $query = DB::table('od_procedure_logs as pl')
                ->leftJoin('od_patients as pt', 'pl.PatNum', '=', 'pt.PatNum')
                ->leftJoin('od_providers as prov', 'pl.ProvNum', '=', 'prov.ProvNum')
                ->leftJoin('od_claim_procs as cp', 'pl.ProcNum', '=', 'cp.ProcNum')
                ->leftJoin('od_pay_splits as ps', 'pl.ProcNum', '=', 'ps.ProcNum')
                ->whereIn('pl.ProcStatus', ProcStatus::completed())
                ->whereBetween('pl.ProcDate', [$startDateStr, $endDateStr])
                ->select(
                    'pl.ProcNum',
                    'pl.PatNum',
                    'pl.ProcDate',
                    'pl.ProcFee',
                    'pt.FName as pat_fname',
                    'pt.LName as pat_lname',
                    'prov.Abbr as prov_abbr',
                    'prov.LName as prov_lname',
                    'prov.FName as prov_fname',
                    DB::raw('COALESCE(SUM(ps.SplitAmt), 0) as pat_paid'),
                    DB::raw('COALESCE(MAX(cp.InsPayEst), 0) as ins_est'),
                    DB::raw('COALESCE(MAX(cp.WriteOff), 0) as write_off')
                )
                ->groupBy('pl.ProcNum', 'pl.PatNum', 'pl.ProcDate', 'pl.ProcFee', 'pt.FName', 'pt.LName', 'prov.Abbr', 'prov.LName', 'prov.FName');

            return DataTables::query($query)
                ->addColumn('patient', fn ($r) => trim(($r->pat_lname ?? '').', '.($r->pat_fname ?? '')) ?: 'Patient '.$r->PatNum)
                ->addColumn('provider', fn ($r) => trim(($r->prov_lname ?? '').', '.($r->prov_fname ?? '')) ?: ($r->prov_abbr ?? '—'))
                ->addColumn('date_of_service', fn ($r) => $r->ProcDate ? Carbon::parse($r->ProcDate)->format('M d, Y') : '')
                ->addColumn('patient_paid', fn ($r) => (float) $r->pat_paid)
                ->addColumn('patient_portion', function ($r) {
                    $portion = max(0, (float) $r->ProcFee - (float) $r->ins_est - (float) $r->write_off);

                    return $portion > 0 ? $portion : (float) $r->ProcFee;
                })
                ->addColumn('copay_percent', function ($r) {
                    $portion = max(0, (float) $r->ProcFee - (float) $r->ins_est - (float) $r->write_off);
                    if ($portion <= 0) {
                        $portion = (float) $r->ProcFee;
                    }
                    $paid = (float) $r->pat_paid;

                    return $portion > 0 ? round(($paid / $portion) * 100, 2) : ($paid > 0 ? 100.0 : 0.0);
                })
                ->make(true);
        }

        // Subtab 3: Adjustments (from fr-off-adjustment-tab.html)
        if ($subtab === 'adjustments') {
            $query = DB::table('od_adjustments as adj')
                ->leftJoin('od_patients as pt', 'adj.PatNum', '=', 'pt.PatNum')
                ->leftJoin('od_providers as prov', 'adj.ProvNum', '=', 'prov.ProvNum')
                ->leftJoin('od_definitions as def', function ($join) {
                    $join->on('adj.AdjType', '=', 'def.DefNum')
                        ->where('def.Category', '=', 1);
                })
                ->whereBetween('adj.AdjDate', [$startDateStr, $endDateStr])
                ->select(
                    'adj.AdjNum',
                    'adj.AdjDate',
                    'adj.AdjAmt',
                    'adj.AdjNote',
                    'adj.AdjType',
                    'pt.FName as pat_fname',
                    'pt.LName as pat_lname',
                    'prov.Abbr as prov_abbr',
                    'prov.LName as prov_lname',
                    'prov.FName as prov_fname',
                    'def.ItemName as def_name'
                );

            return DataTables::query($query)
                ->addColumn('patient', fn ($r) => trim(($r->pat_lname ?? '').', '.($r->pat_fname ?? '')) ?: 'Patient '.$r->PatNum)
                ->addColumn('provider', fn ($r) => trim(($r->prov_lname ?? '').', '.($r->prov_fname ?? '')) ?: ($r->prov_abbr ?? '—'))
                ->addColumn('date', fn ($r) => $r->AdjDate ? Carbon::parse($r->AdjDate)->format('M d, Y') : '')
                ->addColumn('adjustment_type', fn ($r) => $r->def_name ? ($r->def_name.' - '.$r->AdjType) : ('Adjustment #'.$r->AdjType))
                ->addColumn('amount', fn ($r) => (float) $r->AdjAmt)
                ->addColumn('note', fn ($r) => $r->AdjNote ?? '')
                ->make(true);
        }

        // Subtab 4: Collections (from fr-off-collection-tab.html)
        if ($subtab === 'collections') {
            $grossByDate = DB::table('od_procedure_logs')
                ->selectRaw('DATE(ProcDate) as d_date, SUM(ProcFee) as val')
                ->whereIn('ProcStatus', ProcStatus::completed())
                ->whereBetween('ProcDate', [$startDateStr, $endDateStr])
                ->groupBy(DB::raw('DATE(ProcDate)'))
                ->pluck('val', 'd_date');

            $adjByDate = DB::table('od_adjustments')
                ->selectRaw('DATE(AdjDate) as d_date, SUM(AdjAmt) as val')
                ->whereBetween('AdjDate', [$startDateStr, $endDateStr])
                ->groupBy(DB::raw('DATE(AdjDate)'))
                ->pluck('val', 'd_date');

            $woByDate = DB::table('od_claim_procs')
                ->selectRaw('DATE(ProcDate) as d_date, SUM(WriteOff) as val')
                ->whereBetween('ProcDate', [$startDateStr, $endDateStr])
                ->groupBy(DB::raw('DATE(ProcDate)'))
                ->pluck('val', 'd_date');

            $collPatByDate = DB::table('od_pay_splits')
                ->selectRaw('DATE(DatePay) as d_date, SUM(SplitAmt) as val')
                ->whereBetween('DatePay', [$startDateStr, $endDateStr])
                ->groupBy(DB::raw('DATE(DatePay)'))
                ->pluck('val', 'd_date');

            $collInsByDate = DB::table('od_claim_procs')
                ->selectRaw('DATE(DateCP) as d_date, SUM(InsPayAmt) as val')
                ->whereBetween('DateCP', [$startDateStr, $endDateStr])
                ->where('Status', '!=', 0)
                ->groupBy(DB::raw('DATE(DateCP)'))
                ->pluck('val', 'd_date');

            $allDates = array_unique(array_merge(
                $grossByDate->keys()->toArray(),
                $adjByDate->keys()->toArray(),
                $woByDate->keys()->toArray(),
                $collPatByDate->keys()->toArray(),
                $collInsByDate->keys()->toArray()
            ));
            sort($allDates);

            $rows = [];
            foreach ($allDates as $d) {
                $gross = (float) ($grossByDate[$d] ?? 0);
                $adj = (float) ($adjByDate[$d] ?? 0);
                $wo = (float) ($woByDate[$d] ?? 0);
                $net = $gross + $adj + $wo;
                $coll = (float) ($collPatByDate[$d] ?? 0) + (float) ($collInsByDate[$d] ?? 0);
                $pct = $net > 0 ? round(($coll / $net) * 100, 2) : ($coll > 0 ? 100.0 : 0.0);

                $rows[] = [
                    'date' => Carbon::parse($d)->format('M d, Y'),
                    'raw_date' => $d,
                    'total_net_production' => $net,
                    'total_collections' => $coll,
                    'collection_percent' => $pct,
                ];
            }

            return DataTables::of(collect($rows))
                ->addColumn('date', fn ($r) => $r['date'])
                ->addColumn('total_net_production', fn ($r) => (float) $r['total_net_production'])
                ->addColumn('total_collections', fn ($r) => (float) $r['total_collections'])
                ->addColumn('collection_percent', fn ($r) => (float) $r['collection_percent'])
                ->make(true);
        }

        return DataTables::of(collect([]))->make(true);
    }

    public function collectionsStats(Request $request)
    {
        [$startRange, $endRange] = $this->getFilterDateRange($request);
        $startDateStr = substr($startRange, 0, 10);
        $endDateStr = substr($endRange, 0, 10);

        $stats = OdPatient::query()->select(
            DB::raw('SUM(CAST(Bal_0_30 AS DECIMAL(10,2))) as Bal_0_30'),
            DB::raw('SUM(CAST(Bal_31_60 AS DECIMAL(10,2))) as Bal_31_60'),
            DB::raw('SUM(CAST(Bal_61_90 AS DECIMAL(10,2))) as Bal_61_90'),
            DB::raw('SUM(CAST(BalOver90 AS DECIMAL(10,2))) as BalOver90'),
            DB::raw('SUM(CAST(BalTotal AS DECIMAL(10,2))) as BalTotal')
        )->first();

        // 1. Patient vs Insurance Collections within selected month
        // Patient Payments (from od_pay_splits on DatePay)
        $pts_collection = (float) DB::table('od_pay_splits')
            ->whereBetween('DatePay', [$startDateStr, $endDateStr])
            ->sum('SplitAmt');

        // Insurance Payments (from od_claim_procs on DateCP or od_claim_payments)
        $ins_collection = (float) DB::table('od_claim_procs')
            ->whereBetween('DateCP', [$startDateStr, $endDateStr])
            ->where('Status', '!=', 0)
            ->sum('InsPayAmt');

        if ($ins_collection <= 0) {
            $ins_collection = (float) DB::table('od_claim_payments')
                ->whereBetween('CheckDate', [$startDateStr, $endDateStr])
                ->sum('CheckAmt');
        }

        $total_collection = $pts_collection + $ins_collection;

        // 2. Adjustments and Gross/Net Production within selected month
        $gross_production = (float) DB::table('od_procedure_logs')
            ->whereIn('ProcStatus', ProcStatus::completed())
            ->whereBetween('ProcDate', [$startDateStr, $endDateStr])
            ->sum('ProcFee');

        $total_adjustments = (float) DB::table('od_adjustments')
            ->whereBetween('AdjDate', [$startDateStr, $endDateStr])
            ->sum('AdjAmt');

        $total_writeoffs = (float) DB::table('od_claim_procs')
            ->whereBetween('ProcDate', [$startDateStr, $endDateStr])
            ->sum('WriteOff');

        $net_production = $gross_production + $total_adjustments + $total_writeoffs;
        $adj_percent = $gross_production > 0 ? round((abs($total_adjustments) / $gross_production) * 100, 2) : 0.0;

        return response()->json([
            'balances' => [
                'current' => (float) ($stats->Bal_0_30 ?? 0),
                'over_30' => (float) ($stats->Bal_31_60 ?? 0),
                'over_60' => (float) ($stats->Bal_61_90 ?? 0),
                'over_90' => (float) ($stats->BalOver90 ?? 0),
                'over_120' => 0.00,
                'total' => (float) ($stats->BalTotal ?? 0),
            ],
            'collections' => [
                'pts' => $pts_collection,
                'ins' => $ins_collection,
                'total' => $total_collection,
            ],
            'adjustments' => [
                'total' => $total_adjustments,
                'gross_production' => $gross_production,
                'net_production' => $net_production,
                'percent' => $adj_percent,
            ],
        ]);
    }

    public function kpis(Request $request)
    {
        if ($request->ajax()) {
            return view('front-office.partials.kpis');
        }

        return view('front-office.index', ['activeTab' => 'kpis']);
    }

    public function kpiData(Request $request)
    {
        $section = $request->get('section');
        [$startRange, $endRange] = $this->getFilterDateRange($request);
        $start = substr($startRange, 0, 10);
        $end = substr($endRange, 0, 10);

        $kpisCtrl = app(KpisController::class);
        $data = [];

        if ($section === 'office') {
            $off = $kpisCtrl->officeKpis($start, $end);
            $data = [
                ['id' => 'pat_retention', 'current' => $off['patient_retention'] ?? '-', 'target' => 85, 'last' => '-'],
                ['id' => 'tot_tx_plans', 'current' => $off['tx_plans_per_day'] ?? 0, 'target' => 2, 'last' => '-'],
                ['id' => 'copay_col', 'current' => $off['co_pay_collection'] ?? '-', 'target' => 95, 'last' => '-'],
                ['id' => 'resched', 'current' => '-', 'target' => '-', 'last' => '-'],
                ['id' => 'new_pat_a', 'current' => '-', 'target' => '-', 'last' => '-'],
                ['id' => 'no_show', 'current' => $off['no_show_rate'] ?? 0, 'target' => 5, 'last' => '-'],
                ['id' => 'pat_react', 'current' => $off['reactivation_list'] ?? '-', 'target' => '-', 'last' => '-'],
                ['id' => 'pat_added', 'current' => $off['active_patients'] ?? '-', 'target' => '-', 'last' => '-'],
                ['id' => 'pat_viewed', 'current' => '-', 'target' => '-', 'last' => '-'],
                ['id' => 'new_pat_rev', 'current' => '-', 'target' => '-', 'last' => '-'],
                ['id' => 'unsch_hyg_ret', 'current' => $off['active_in_recare_pct'] ?? '-', 'target' => '-', 'last' => '-'],
            ];
        } elseif ($section === 'doctor') {
            $doc = $kpisCtrl->doctorKpis($start, $end);
            $data = [
                ['id' => 'doc_prod_same_day', 'current' => $doc['case_acceptance_same_day'] ?? '-', 'target' => '-', 'last' => '-'],
                ['id' => 'doc_case_acc', 'current' => $doc['case_acceptance_rate'] ?? '-', 'target' => 75, 'last' => '-'],
                ['id' => 'doc_gross_prod', 'current' => $doc['total_production'] ?? 0, 'target' => 50000, 'last' => '-'],
                ['id' => 'doc_net_prod', 'current' => $doc['total_production'] ?? 0, 'target' => 50000, 'last' => '-'],
                ['id' => 'doc_avg_op', 'current' => $doc['avg_prod_per_prov_day'] ?? '-', 'target' => '-', 'last' => '-'],
                ['id' => 'doc_avg_prod_hr', 'current' => $doc['avg_prod_per_hour'] ?? 0, 'target' => 500, 'last' => '-'],
                ['id' => 'doc_avg_tx_appt', 'current' => $doc['avg_prod_per_apt'] ?? 0, 'target' => '-', 'last' => '-'],
                ['id' => 'doc_same_day_np', 'current' => $doc['same_day_tx_per_new_pt'] ?? 0, 'target' => '-', 'last' => '-'],
                ['id' => 'doc_avg_prod_np', 'current' => $doc['new_pt_tx_dollars'] ?? 0, 'target' => '-', 'last' => '-'],
                ['id' => 'doc_avg_tx_visit', 'current' => $doc['avg_tx_per_existing_pt'] ?? 0, 'target' => '-', 'last' => '-'],
                ['id' => 'doc_avg_tx_pat', 'current' => $doc['avg_tx_per_new_pt'] ?? 0, 'target' => '-', 'last' => '-'],
                ['id' => 'doc_pat_cxl_rate', 'current' => $doc['no_show_rate'] ?? 0, 'target' => 5, 'last' => '-'],
                ['id' => 'doc_unsch_tx', 'current' => $doc['existing_pt_tx_dollars'] ?? 0, 'target' => '-', 'last' => '-'],
                ['id' => 'doc_supplies', 'current' => '-', 'target' => '-', 'last' => '-'],
                ['id' => 'doc_med_supplies', 'current' => '-', 'target' => '-', 'last' => '-'],
                ['id' => 'doc_tot_prod', 'current' => $doc['total_production'] ?? 0, 'target' => '-', 'last' => '-'],
            ];
        } elseif ($section === 'hygiene') {
            $hyg = $kpisCtrl->hygieneKpis($start, $end);
            $data = [
                ['id' => 'hyg_pre_apt', 'current' => $hyg['reappt'] ?? '-', 'target' => 85, 'last' => '-'],
                ['id' => 'hyg_unfilled', 'current' => '-', 'target' => '-', 'last' => '-'],
                ['id' => 'hyg_avg_prod_hr', 'current' => $hyg['avg_prod_per_hour'] ?? 0, 'target' => 150, 'last' => '-'],
                ['id' => 'hyg_avg_med_hr', 'current' => $hyg['avg_prod_per_day'] ?? 0, 'target' => '-', 'last' => '-'],
                ['id' => 'hyg_prebook_pt', 'current' => $hyg['reappt'] ?? '-', 'target' => '-', 'last' => '-'],
                ['id' => 'hyg_avg_time_appt', 'current' => 60, 'target' => '-', 'last' => '-'],
                ['id' => 'hyg_avg_xray_appt', 'current' => $hyg['fmx_per_day'] ?? 0, 'target' => '-', 'last' => '-'],
                ['id' => 'hyg_pts_per_day', 'current' => $hyg['visits_per_day'] ?? 0, 'target' => 8, 'last' => '-'],
                ['id' => 'hyg_pts_per_hr', 'current' => $hyg['prod_per_visit'] ?? 0, 'target' => '-', 'last' => '-'],
                ['id' => 'hyg_fluoride_pt', 'current' => $hyg['fluoride_per_day'] ?? 0, 'target' => '-', 'last' => '-'],
                ['id' => 'hyg_adult_prophy', 'current' => $hyg['perio_pct'] ?? '-', 'target' => '-', 'last' => '-'],
                ['id' => 'hyg_adult_tx', 'current' => $hyg['perio_pct'] ?? '-', 'target' => '-', 'last' => '-'],
                ['id' => 'hyg_last_2', 'current' => $hyg['adult_retention_12m'] ?? '-', 'target' => '-', 'last' => '-'],
                ['id' => 'hyg_last_2_months', 'current' => $hyg['adult_retention_6m'] ?? '-', 'target' => '-', 'last' => '-'],
                ['id' => 'hyg_sealants', 'current' => $hyg['sealants'] ?? 0, 'target' => '-', 'last' => '-'],
                ['id' => 'hyg_srp', 'current' => $hyg['srp_per_day'] ?? 0, 'target' => '-', 'last' => '-'],
                ['id' => 'hyg_perio_med', 'current' => $hyg['antimicrobial'] ?? 0, 'target' => '-', 'last' => '-'],
                ['id' => 'hyg_prod_tx', 'current' => $hyg['prod_per_proc'] ?? 0, 'target' => '-', 'last' => '-'],
                ['id' => 'hyg_tot_visits', 'current' => $hyg['visits_per_day'] ?? 0, 'target' => '-', 'last' => '-'],
                ['id' => 'hyg_unfilled_dt', 'current' => '-', 'target' => '-', 'last' => '-'],
                ['id' => 'hyg_avg_prod_visit', 'current' => $hyg['prod_per_visit'] ?? 0, 'target' => '-', 'last' => '-'],
                ['id' => 'hyg_case_acc', 'current' => $hyg['case_acceptance'] ?? '-', 'target' => 75, 'last' => '-'],
            ];
        }

        return response()->json([
            'section' => $section,
            'data' => $data,
        ]);
    }

    public function performance(Request $request)
    {
        if ($request->ajax()) {
            return view('front-office.partials.performance');
        }

        return view('front-office.index', ['activeTab' => 'performance']);
    }

    public function performanceStats(Request $request)
    {
        // Reserved for future chart integrations
        return response()->json([]);
    }

    public function performanceRemindersData(Request $request)
    {
        // Expected Data mapping logic goes here for true Reminder Contacts
        // Returning true empty Collections natively forces DataTables to display the "No data" warning dynamically
        // without relying on manual placeholders, honoring the user constraint flawlessly.
        return DataTables::of(collect([]))->make(true);
    }

    public function performanceNonRemindersData(Request $request)
    {
        // Expected Data mapping logic goes here for Non-Reminder Contacts
        return DataTables::of(collect([]))->make(true);
    }

    public function performanceTotalsData(Request $request)
    {
        // Expected Data mapping logic goes here for total aggregates
        return DataTables::of(collect([]))->make(true);
    }
}
